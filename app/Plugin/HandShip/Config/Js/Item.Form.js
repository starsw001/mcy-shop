[
    {
        title: "发货方式",
        name: "hand_delivery_method",
        type: "radio",
        dict: [
            {id: 0, name: format.danger("固定发货信息(自动发货)")},
            {id: 1, name: format.success("人工发货")},
        ],
        change: (popup, val) => {
            cache.set("hand_delivery_method", val);
        },
        complete: (popup, val) => {
            cache.set("hand_delivery_method", val);
        }
    }
]
