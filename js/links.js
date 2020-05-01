/**
 * Работа с ссылками (временно не используется с 21 июля 2012 г.)
 */
(function () {
    var Linkst = window.Linkst = {};
    Linkst.ChangeLinkType = function (link) {
        if (!link)
            return;
        // ---
        var text = '';
        // ---
        if (link.value == '1') {
            var imageLink = document.getElementById("imagesGoLink");
            var image = "test";
            if (imageLink)
                image = imageLink.value;
            text = '<a href="[GO-URL]"><img [GEN-IMG-' + image + '-200-300]></a>';

        }
        // ---
        if (link.value == '2') {
            var link = document.getElementById("linkgohtmlText");
            var linkText = "";
            if (link)
                linkText = link.value;
            text = '<a href="[GO-URL]">' + linkText + '</a>';
        }
        // ---
        var textarea = document.getElementById("linkgohtmlResult");
        if (textarea) textarea.value = text;
    }
})();
