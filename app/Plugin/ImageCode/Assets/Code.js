const ImageCode = new class ImageCode {
    update(dom, key) {
        $(dom).click(function () {
            $(this).attr("src", "/plugin/image-code/code?key=" + key + "&v=" + util.generateRandStr(16));
        });
    }
}