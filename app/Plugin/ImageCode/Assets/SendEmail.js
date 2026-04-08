ready(function () {
    $('.send-email-code').off("click").addClass('send-email-code-advanced').removeClass('send-email-code');

    $('.send-email-code-advanced').click(function () {
        let email = $('input[name=email]').val();
        const $this = this;
        message.prompt({
            title: "请输入验证码",
            html: `<img src="/plugin/image-code/code?key=send_email&v=${util.generateRandStr(16)}" alt="更换验证码" class="image-code-send-email" style="cursor:pointer;">`,
            inputAttributes: {
                style: 'text-align: center;'
            },
            inputValidator: function (value) {
                return util.post("/sendEmail?type=" + getVar("imageCodeType"), {
                    email: email,
                    image_code: value
                }, res => {
                    util.countDown($this, 60);
                    message.alert("验证码发送成功", "success");
                });
            },
            didOpen: () => {
                ImageCode.update(".image-code-send-email", "send_email");
            }
        });
    });
});