[
    {
        name: util.icon("icon-waibuduijie") + " 支付宝配置",
        form: [
            {
                title: "应用ID",
                name: "app_id",
                type: "input",
                placeholder: "请输入应用ID"
            },
            {
                title: "支付宝公钥",
                name: "alipay_public_key",
                type: "textarea",
                placeholder: "请输入支付宝公钥",
                tips: "请注意，此公钥是'支付宝公钥'，而不是自己生成的那个应用公钥",
                height: 180
            },
            {
                title: "应用私钥",
                name: "private_key",
                type: "textarea",
                placeholder: "请输入应用私钥",
                tips: "请注意，这里是自己生成的那个'应用私钥'，而不是'公钥'",
                height: 180
            },
        ]
    }
]