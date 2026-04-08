[
    {
        name: cache.get("hand_delivery_method") == 1 ? util.icon("icon-biaoqian") + " 手动发货提醒" : util.icon("icon-biaoqian") + " 固定发货内容",
        form: [
            {
                name: "hand_delivery_contents",
                type: "editor",
                placeholder: "这里的信息，SKU被购买后，将会自动发送该信息给会员..",
                height: 360,
                uploadUrl: window.location.pathname.startsWith("/admin") ? '/admin/upload' : '/user/upload'
            }
        ]
    }
]