!function () {
    let table = new Table(route("/card/get"), "#repertory-virtual-card-table");

    table.setColumns([
        {checkbox: true},
        {
            field: 'item', title: '商品/SKU', formatter: function (item, all) {
                return item.name + "(ID: " + item.id + ")";
            }
        },
        {
            field: 'sku', title: 'SKU', formatter: function (sku, all) {
                return sku.name;
            }
        },
        {field: 'card', title: '卡号/卡密'},
        {
            field: 'status', title: '状态', dict: [
                {id: 0, name: format.danger('未售出')},
                {id: 1, name: format.success('已售')},
                {id: 2, name: format.dark('冻结')}
            ]
        },
        {
            field: 'remark', title: '备注', formatter: function (remark, all) {
                if (remark) {
                    return remark;
                }
                return "-";
            }
        },

        {
            field: 'create_time', title: '创建时间'
        },
        {
            field: 'order', title: '订单号', formatter: function (order, all) {
                if (!order) {
                    return '-';
                }
                return order.trade_no;
            }
        },
        {
            field: 'purchase_time', title: '交易时间', formatter: function (purchaseTime, all) {
                if (!purchaseTime) {
                    return '-';
                }
                return purchaseTime;
            }
        }
    ]);
    table.setPagination(15, [15, 20, 30, 50, 100, 500, 1000, 5000]);
    table.onResponse(data => {
        $('.card_count').html(format.color(data.card_count, "#5185fd"));
        $('.card_used_count').html(format.color(data.card_used_count, "#f2344c"));
        $('.card_usable_count').html(format.color(data.card_usable_count, "#1ecb45"));
        $('.card_frozen_count').html(format.color(data.card_frozen_count, "#1ecb45"));
    });
    table.setSearch([
        {title: "订单号", name: "equal-trade_no", type: "input", width: 220},
        {title: "备注", name: "equal-remark", type: "input"},
        {title: "卡号/卡密(较慢)", name: "equal-card", type: "input"},
        {
            title: "选择商品",
            name: "equal-item_id",
            type: "select",
            search: true,
            dict: "repertoryItem?plugin=VirtualCardShip",
            change: (select, value) => {
                if (value == "") {
                    select.selectClearOption("equal-sku_id");
                    select.hide("equal-sku_id");
                    return;
                }
                _Dict.advanced("repertoryItemSku?itemId=" + value, res => {
                    select.selectClearOption("equal-sku_id");
                    select.show("equal-sku_id");
                    res.forEach(s => {
                        select.selectAddOption("equal-sku_id", s.id, s.name);
                    });
                })
            }
        },
        {
            title: "SKU",
            name: "equal-sku_id",
            type: "select",
            hide: true
        },
        {title: "交易时间", name: "between-purchase_time", type: "date"}
    ]);
    table.setState("status", [
        {id: 0, name: "未出售"},
        {id: 1, name: "已出售"},
        {id: 2, name: "冻结"}
    ]);

    table.render();

    $('.upload-card').click(function () {
        component.popup({
            submit: route("/card/add"),
            tab: [
                {
                    name: util.icon("icon-tianjia") + "<space></space>" + "上传卡密",
                    form: [
                        {
                            title: "选择商品",
                            name: "item_id",
                            type: "select",
                            search: true,
                            required: true,
                            dict: "repertoryItem?plugin=VirtualCardShip",
                            change: (form, value) => {
                                if (value == "") {
                                    form.clearOption("sku_id");
                                    form.hide("sku_id");
                                    form.hide("upload_type");
                                    form.hide("card");
                                    form.hide("card_file");
                                    return;
                                }
                                form.clearOption("sku_id");
                                _Dict.advanced("repertoryItemSku?itemId=" + value, res => {
                                    res.forEach(s => {
                                        form.addOption("sku_id", s.id, s.name);
                                    });

                                    if (res.length > 0) {
                                        form.show("sku_id");
                                        form.show("upload_type");
                                        form.show("card");
                                        form.hide("card_file");
                                        form.setRadio("upload_type", 0, true);
                                    }
                                })
                            }
                        },
                        {
                            title: "商品SKU",
                            name: "sku_id",
                            type: "select",
                            search: true,
                            hide: true,
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
                            hide: true,
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
                            hide: true,
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
                            uploadUrl: route("/card/upload"),
                            css: {
                                textAlign: "center"
                            },
                            change: (url, data) => {

                            }
                        }
                    ]
                }
            ],
            width: "680px",
            assign: {},
            confirmText: `${util.icon('icon-update')} 上传卡密`,
            autoPosition: true,
            content: {
                css: {
                    height: "auto",
                    overflow: "inherit"
                }
            },
            maxmin: false,
            done: res => {
                table.refresh();
            }
        });
    });

    let statusUpdate = (status) => {
        let list = table.getSelectionIds();
        if (list.length <= 0) {
            message.error("请选择1个卡密后再进行操作");
            return;
        }
        util.post(route("/card/status"), {
            status: status,
            list: list
        }, res => {
            message.alert(res.msg);
            table.refresh();
        });
    }

    $('.frozen-card').click(function () {
        statusUpdate(2);
    });

    $('.unfreeze-card').click(function () {
        statusUpdate(0);
    });

    $('.export-card').click(function () {
        component.popup({
            tab: [
                {
                    name: util.icon("icon-wenjiandaochu") + " 导出卡密",
                    form: [
                        {
                            name: "custom",
                            type: "custom",
                            complete: (obj, dom) => {
                                dom.html('<div style="margin-bottom: 25px;color: #27bd27;font-weight: bolder;">导出程序将根据您通过查询功能筛选出的卡密进行导出。如果您填写了导出数量，将导出指定数量的卡密；如果您未填写数量，则将导出您筛选的全部卡密。</div>');
                            }
                        }, {
                            title: "导出数量",
                            name: "num",
                            type: "input",
                            placeholder: "导出数量，填写0或不填表示全部导出。"
                        },
                        {
                            title: "导出后执行",
                            name: "handle",
                            type: "radio",
                            dict: [
                                {id: 0, name: "不执行任何操作"},
                                {id: 1, name: "锁定导出的卡密"},
                                {id: 2, name: "删除导出的卡密（高危）"},
                                {id: 3, name: "将卡密状态改【已售】"},
                            ]
                        }
                    ]
                }
            ],
            height: "auto",
            width: "480px",
            assign: {},
            confirmText: "开始导出",
            autoPosition: true,
            content: {
                css: {
                    height: "auto",
                    overflow: "inherit"
                }
            },
            submit: data => {
                let searchData = table.getSearchData();
                let state = table.getState();
                let query = util.objectToQueryString(Object.assign(searchData, data));
                let url = route("/card/export?" + query + "&equal-" + state.field + "=" + state.value + "&hash=" + util.generateRandStr(32));
                if (data.handle == 2) {
                    message.dangerPrompt("您正在执行高风险的卡密导出操作，需要注意此操作无法恢复数据。如果您只是希望卡密不再可见，我们建议您选择锁定导出的卡密。", "我确认导出并删除卡密", () => {
                        window.open(url);
                    });
                } else {
                    window.open(url);
                }
            },
        });
    });

    $('.del-card').click(function () {
        let list = table.getSelectionIds();
        if (list.length <= 0) {
            message.error("请选择1个卡密后再进行操作");
            return;
        }
        message.dangerPrompt("您确定要永久删除这些卡密吗？请注意，删除后将无法恢复。如果您只是暂时不想出售这些卡密，我们建议将其冻结而非删除。", "我确认删除卡密", () => {
            statusUpdate(99);
        });
    });
}();