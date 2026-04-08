[
    {
        name: "插件配置",
        form: [
            {
                title: "库存显示方案",
                name: "stock_mode",
                type: "radio",
                dict: [
                    {id: 0, name: "直观"},
                    {id: 1, name: "保守"},
                    {id: 2, name: "激进"},
                    {id: 3, name: "自定义"},
                ],
                change: (form, val) => {
                    if (val == 3) {
                        form.show("custom");
                    } else {
                        form.hide("custom");
                    }
                },

            },
            {
                title: "配置文件",
                name: "custom",
                type: "html",
                language: "ini",
                tips: "通过配置文件，你可以轻松DIY你的库存显示方案\n\n当库存小于指定的数量，则会显示你自定义的文字".replaceAll("\n", "<br>"),
                default: "0-售罄\n" +
                    "5-即将售罄\n" +
                    "10-较少\n" +
                    "30-一般\n" +
                    "50-充足",
                hide: row?.config?.stock_mode != 3
            }
        ]
    }
]