[
    {
        name: "插件配置",
        form: [
            {
                title: false,
                name: "safe_tips",
                type: "custom",
                complete: (form, dom) => {
                    dom.html(`
<div class="block-tips">
${i18n(`<p>自定义后台入口地址：你可以为后台管理设置一个自定义入口地址。例如，如果你将入口地址设置为 <b>admin123</b>，那么你需要通过访问 <b>你的域名/admin123</b> 来进入后台登录页面，而访问原来的 <b>你的域名/admin</b> 将会显示404错误页面。</p>`)}
${i18n(`<p>白名单IP设置：你可以将你的电脑IP地址添加到白名单中，只有在白名单中的IP地址才能访问后台的所有功能。建议使用IP段，以防止IP频繁变动导致无法访问后台。例如，如果你的IP地址是 <b>39.14.1.9</b>，可以设置为 <b>39.14.*.*</b>，这样可以更好地应对IP地址的变动。</p>`)}
${i18n(`<p class="text-danger fw-bold">新手防呆说明：为了防止未经授权的访问，当你尝试进入后台时，如果入口地址不正确或你的IP地址不在白名单中，系统会返回一个404页面。这不是程序出错，而是表示你的访问被阻止了。请确认你使用了正确的入口地址和IP地址。</p>`)}
</div>
                                    `);
                }
            },
            {
                title: "后台入口地址",
                name: "url",
                type: "input",
                placeholder: "入口地址",
                tips: "入口地址，不能有任何特殊符号，正确例子：" + util.generateRandStr(10).toLocaleLowerCase(),
                default: util.generateRandStr(10).toLocaleLowerCase(),
                regex: {
                    value: /^[a-zA-Z0-9]+$/,
                    message: "入口格式不正确"
                }
            },
            {
                title: "白名单IP",
                name: "whitelist",
                type: "html",
                language: "ini",
                tips: "IP一行一个<br><b>推荐使用IP段（IP段仅支持：<b class='text-danger'>39.14.*.*</b>、<b class='text-danger'>39.14.12.*</b> 这两个格式）</b>",
                height: 90
            }
        ]
    }
]