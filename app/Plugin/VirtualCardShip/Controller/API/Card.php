<?php
declare (strict_types=1);

namespace App\Plugin\VirtualCardShip\Controller\API;

use App\Entity\Query\Get;
use App\Interceptor\PostDecrypt;
use App\Model\RepertoryItem;
use App\Model\RepertoryItemSku;
use App\Model\RepertoryOrder;
use App\Plugin\VirtualCardShip\Model\RepertoryVirtualCard as Model;
use App\Service\Common\Query;
use Hyperf\Database\Model\Builder;
use Kernel\Annotation\Inject;
use Kernel\Annotation\Interceptor;
use Kernel\Annotation\Validator;
use Kernel\Context\App;
use Kernel\Context\Interface\Response;
use Kernel\Context\Upload;
use Kernel\Database\Db;
use Kernel\Exception\JSONException;
use Kernel\Exception\NotFoundException;
use Kernel\Exception\RuntimeException;
use Kernel\Plugin\Abstract\Controller;
use Kernel\Util\Call;
use Kernel\Util\Date;
use Kernel\Util\File;

#[Interceptor(class: \App\Interceptor\Plugin::class, type: Interceptor::API)]
class Card extends Controller
{
    #[Inject]
    private Query $query;

    #[Inject]
    private \App\Service\Common\RepertoryItemSku $sku;

    /**
     * @return Response
     * @throws JSONException
     * @throws NotFoundException
     * @throws \ReflectionException
     */
    public function upload(): Response
    {
        $upload = new Upload("file");
        $path = $this->getPlugin()->path;
        $fileName = $upload->save(path: "/Upload/", ext: ["txt"], size: 1024 * 100, dir: $path);
        return $this->response->json(data: ["url" => $fileName]);
    }

    /**
     * @return Response
     * @throws RuntimeException
     */
    #[Interceptor(class: PostDecrypt::class, type: Interceptor::API)]
    public function get(): Response
    {
        $map = $this->request->post();
        $query = Model::query();
        $get = new Get(Model::class);
        $get->setWhere($map);
        $get->setPaginate((int)$this->request->post("page"), (int)$this->request->post("limit"));
        $get->setWhereLeftJoin(RepertoryOrder::class, "id", "order_id", ["trade_no" => "trade_no"]);

        $data = $this->query->get($get, function (Builder $builder) {
            return $builder->with(["order", "item", "sku"]);
        });

        return $this->json(data: $data, ext: [
            "card_count" => (clone $query)->count(),
            "card_used_count" => (clone $query)->where("status", 1)->count(),
            "card_usable_count" => (clone $query)->where("status", 0)->count(),
            "card_frozen_count" => (clone $query)->where("status", 2)->count(),
        ]);
    }


    /**
     * @return Response
     * @throws JSONException
     * @throws NotFoundException
     * @throws RuntimeException
     */
    #[Interceptor(class: PostDecrypt::class, type: Interceptor::API)]
    #[Validator([
        ['key' => 'item_id', 'rule' => 'require', 'message' => ['require' => '请选择商品']],
        ['key' => 'sku_id', 'rule' => 'require', 'message' => ['require' => '请选择SKU']],
    ])]
    public function add(): Response
    {
        $startTime = Date::timestamp();
        $elapsedTime = 0;

        $itemId = (int)$this->request->post("item_id");
        $skuId = (int)$this->request->post("sku_id");
        $remark = (string)$this->request->post("remark");
        $uploadType = (int)$this->request->post("upload_type");
        $card = (string)$this->request->post("card");
        $cardFile = $this->request->post("card_file");
        $unique = (int)$this->request->post("unique");

        $item = RepertoryItem::query();

        $sku = RepertoryItemSku::query();

        if ($this->isUsr()) {
            $item = $item->where("user_id", $this->getUser()->id);
            $sku = $sku->where("user_id", $this->getUser()->id);
        } else {
            $item = $item->whereNull("user_id");
            $sku = $sku->whereNull("user_id");
        }

        $item = $item->find($itemId);
        /**
         * @var RepertoryItemSku $sku
         */
        $sku = $sku->find($skuId);

        if (!$item) {
            throw new JSONException("该商品不存在");
        }

        if (!$sku) {
            throw new JSONException("该SKU不存在");
        }

        if ($item->id != $sku->repertory_item_id) {
            throw new JSONException("SKU/商品异常");
        }


        if ($uploadType == 1) {
            $filePath = $this->getPlugin()->path . $cardFile;
            if (!file_exists($filePath)) {
                throw new JSONException("请上传TXT");
            }
            $card = (string)file_get_contents($filePath);
        }


        $explode = explode(PHP_EOL, $card);
        $plugin = $this->getPlugin();
        $inserts = [];
        $table = Model::make()->getTable();
        $success = 0;

        $allCount = count($explode);

        foreach ($explode as $index => $key) {
            $now = Date::current();
            $key = trim($key);
            if ($key == "") {
                continue;
            }
            if ($unique == 1 && $allCount < 1000) {
                if (Model::query()->where("card", $key)->exists()) {
                    continue;
                }
            }

            $inserts[] = [
                "item_id" => $itemId,
                "sku_id" => $skuId,
                "remark" => $remark,
                "card" => $key,
                "create_time" => $now,
                "status" => 0
            ];

            if (count($inserts) >= 1000) {
                $temp = $inserts;
                $inserts = [];
                Call::create(function () use (&$success, $table, $temp) {
                    $success += 1000;
                    Db::table($table)->insert($temp);
                });
            }

        }

        if (count($inserts) > 0) {
            Call::create(function () use (&$success, $table, $inserts) {
                $success += count($inserts);
                Db::table($table)->insert($inserts);
            });
        }

        Call::defer(function () use ($sku, $startTime, &$success, $plugin, &$elapsedTime) {
            $elapsedTime = Date::timestamp() - $startTime;
            $plugin->log("卡密导入完成，数量：{$success}，耗时：{$elapsedTime}ms");
            $this->sku->syncCache($sku->id);
        });

        if (App::$cli) {
            return $this->json(200, "卡密导入正在后台执行，关闭浏览器不影响后台自动导入，详细导入情况可以查看插件日志哦");
        }

        return $this->json(200, "卡密导入完成，成功数量：{$success}，耗时：{$elapsedTime}ms");
    }


    /**
     * @return Response
     * @throws RuntimeException
     */
    #[Interceptor(class: PostDecrypt::class, type: Interceptor::API)]
    public function status(): Response
    {
        $list = (array)$this->request->post("list");
        $status = (int)$this->request->post("status");
        if ($status == 99) {
            Model::query()->whereIn("id", $list)->delete();
        } else {
            Model::query()->whereIn("id", $list)->where("status", "!=", 1)->update(["status" => $status]);
        }

        return $this->json(200, "卡密状态更新成功");
    }


    /**
     * @return Response
     * @throws JSONException
     */
    #[Interceptor(class: PostDecrypt::class, type: Interceptor::API)]
    public function export(): Response
    {
        $map = $this->request->get();
        $handle = (int)$map['handle'];
        $hash = (string)$map['hash'];
        $cache = $this->plugin->path . "/Cache/{$hash}.txt";

        if (strlen($hash) != 32) {
            throw new JSONException("hash 不能为空");
        }

        if (!is_file($cache)) {
            $get = new Get(Model::class);
            $get->setWhere($map);
            $data = $this->query->get($get, function (Builder $builder) use ($map) {
                if ($map['num'] > 0) {
                    $builder = $builder->limit((int)$map['num']);
                }
                return $builder;
            });
            $output = '';
            $ids = [];

            foreach ($data as $datum) {
                $output .= $datum['card'] . PHP_EOL;
                $ids[] = $datum['id'];
            }
            try {
                if ($handle == 1) {
                    //锁定
                    Model::query()->whereIn('id', $ids)->whereRaw("status!=1")->update(['status' => 2]);
                } elseif ($handle == 2) {
                    Model::query()->whereIn('id', $ids)->delete();
                } elseif ($handle == 3) {
                    Model::query()->whereIn('id', $ids)->whereRaw("status!=1")->update(['status' => 1, 'purchase_time' => Date::current()]);
                }

                File::write($cache, $output);
            } catch (\Throwable $e) {
            }
        } else {
            $file = fopen($cache, 'rb');
            if (!$file) {
                throw new JSONException("无法读取文件");
            }
            $output = stream_get_contents($file);
            fclose($file);
        }

        return $this->response
            ->withHeader("Content-Type", "application/octet-stream")
            ->withHeader("Content-Transfer-Encoding", "binary")
            ->withHeader("Content-Disposition", 'attachment; filename=卡密导出-' . Date::current() . '.txt')
            ->raw($output);
    }

}