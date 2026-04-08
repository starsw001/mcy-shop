<?php
declare (strict_types=1);

namespace App\Plugin\VirtualCardShip\Hook;

use Kernel\Annotation\Hook;
use Kernel\Context\Interface\Route;
use Kernel\Plugin\Abstract\Plugin;
use Kernel\Plugin\Const\Point;
use Kernel\Plugin\Entity\Column;
use Kernel\Util\Context;

class Table extends Plugin
{

    #[Hook(point: Point::HACK_ROUTE_TABLE_COLUMNS)]
    public function HACK_ROUTE_TABLE_COLUMNS(): ?array
    {
        /**
         * @var Route $var
         */
        $var = Context::get(Route::class);

        if (!str_starts_with($var->route(), "/admin") && !str_starts_with($var->route(), "/user")) {
            return null;
        }



        $code = <<<JS
{
            field: '_virtual_card_ship_button_hook', title: '', type: 'button', buttons: [
                {
                    icon: 'icon-gongnenglanicon_fahuodaoru',
                    class: 'no-btn',
                    tips : '快速上传虚拟卡密',
                    click: (event, value, row, index) => {
                           if (row?.plugin != "VirtualCardShip"){
                               return;
                           }
                           
                           component.popup({
            submit: "{$this->plugin->routeUrl}/card/add",
            tab: [
                {
                    name: util.icon("icon-tianjia") + "<space></space>" + "上传卡密",
                    form: [
                        {
                      
                            name: "item_id",
                            type: "input",
                            hide: true,
                            default : row.id
                        },
                        {
                            title: "商品SKU",
                            name: "sku_id",
                            type: "select",
                            dict : "repertoryItemSku?itemId=" + row.id,
                            search: true,
                            required: true
                        },
                        {
                            title: "备注信息",
                            name: "remark",
                            type: "input",
                            placeholder: "备注信息，方便查询那一次上传的卡密"
                        },
                        {
                            title: "重复检查",
                            name: "unique",
                            type: "switch",
                            tips: "启用后，将会自动过滤重复卡密（卡密数量超过1000行，该功能无效）"
                        },
                        {
                            title: "上传方法",
                            name: "upload_type",
                            type: "radio",
                            dict: [
                                {id: 0, name: "文本粘贴"},
                                {id: 1, name: "TXT文件上传"}
                            ],
                            change: (form, value) => {
                                if (value == 0) {
                                    form.show("card");
                                    form.hide("card_file");
                                } else {
                                    form.hide("card");
                                    form.show("card_file");
                                }
                            }
                        },
                        {
                            name: "card",
                            type: "textarea",
                            placeholder: "请将你的卡密粘贴至此，一行一个哟",
                            height: 480
                        },
                        {
                            name: "card_file",
                            type: "file",
                            placeholder: "请上传TXT文件（支持拖拽文件至此）",
                            setting: {
                                drag: true,
                                acceptMime: 'text/plain',
                                exts: 'txt'
                            },
                            hide: true,
                            uploadUrl: "{$this->plugin->routeUrl}/card/upload", 
                            css: {
                                textAlign: "center"
                            }
                        }
                    ]
                }
            ],
            width: "680px",
            assign: {},
            confirmText: util.icon('icon-update') + " 上传卡密",
            autoPosition: true,
            content: {
                css: {
                    height: "auto",
                    overflow: "inherit"
                }
            },
            maxmin: false,
            done: res => {
                 $("#repertory-item-table").bootstrapTable('refresh', {silent: true}); 
            }
        });
                           
                    },
                    show: row => row?.plugin == "VirtualCardShip"
                }
            ]
        }
JS;

        return (new Column(str_starts_with($var->route(), "/admin") ? "/admin/repertory/item/get" : "/user/repertory/item/get", $code, "sku", "after"))->toArray();
    }
}