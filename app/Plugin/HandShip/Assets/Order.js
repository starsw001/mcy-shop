!function () {
    let table = null;

    const modal = (title, confirmText = "立即发货", assign = {}) => {

        component.popup({
            submit: route("/order/shipment"),
            tab: [
                {
                    name: title,
                    form: [
                        {
                            name: "contents",
                            type: "editor",
                            placeholder: "请填写您要发货的内容",
                            uploadUrl: assign?.user_id > 0 ? '/user/upload' : '/admin/upload'
                        }
                    ]
                }
            ],
            autoPosition: true,
            width: "660px",
            assign: assign,
            confirmText: confirmText,
            done: () => {
                table.refresh();
            }
        });
    }

    table = new Table(route("/order/get"), "#repertory-order-hand-table");
    table.setPagination(10, [10, 20, 30]);
    table.setColumns([
        {field: 'order.main_trade_no', title: '订单号'},
        {field: 'order.item', title: '商品名称', formatter: format.item},
        {field: 'order.sku', title: 'SKU', formatter: format.item},
        {
            field: 'order.amount', title: '订单金额', formatter: function (amount) {
                if (amount == 0) {
                    return '-';
                }
                return format.money(amount, "#45bf77");
            }
        },
        {field: 'order.quantity', title: '数量'},
        {
            field: 'status', title: '状态', dict: [
                {id: 0, name: format.danger('等待发货')},
                {id: 1, name: format.success('已发货')}
            ]
        },
        {field: 'order.trade_time', title: '交易时间'},
        {field: 'order.trade_ip', title: 'IP地址'},
        {
            field: 'operation', title: '操作', type: 'button', buttons: [
                {
                    icon: 'icon-gongnenglanicon_fahuodaoru',
                    title: "发货",
                    click: (event, value, row, index) => {
                        modal(util.icon("icon-gongnenglanicon_fahuodaoru") + " 请填写要发货的内容", "发货", {
                            id: row.id,
                            contents: row.order.contents,
                            user_id: row?.order?.user_id
                        });
                    }
                }
            ]
        },
    ]);

    table.setState("status", [
        {id: 0, name: "等待发货"},
        {id: 1, name: "已发货"}
    ]);
    table.setSearch([
        {title: "系统/物品/商家订单号", name: "trade_no", type: "input", width: 220},
    ]);
    table.onResponse(data => {
        $('.order_count').html(format.color(data.order_count, "#5185fd"));
        $('.order_unshipped_count').html(format.color(data.order_unshipped_count, "#f2344c"));
        $('.order_shipped_count').html(format.color(data.order_shipped_count, "#1ecb45"));
    });
    table.setDetail([
        {field: 'order.trade_no', title: '系统订单号'},
        {field: 'order.item_trade_no', title: '物品订单号'},
        {field: 'order.main_trade_no', title: '购物订单号'},
        {
            field: 'order', title: '发货内容', formatter: (order) => {
                if (order.contents == "") {
                    return "";
                }
                return `<div class="ship-contents">${order.contents.replaceAll("\n", "<br>")}</div>`;
            }
        },
    ]);
    table.render();
}();