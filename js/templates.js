/**
 * Работа с шаблонами
 */
(function () {
    var Templates = window.Templates = {};
    /**
     * запрос по ajax текст для шаблона
     */
    Templates.ChangeSelect = function (select) {
        // ---
    var template = document.getElementById('template');
    var page= document.getElementById('page');
        Ajax.post("?module=templates&a[viewtemplate]",
            {
                tmpl:(template ? template.value : ""),
                page:(page ? page.value : "")
            }, false,
            {
                onrequestready:ViewTemplateLoaded,
                onrequesterror:ViewTemplateError
            });
    var page_form=document.getElementById('page_form');
    if(page_form) page_form.value = page.value;
    //---
    var template_form=document.getElementById('template_form');
    if(template_form) template_form.value = template.value;

    }
    /*
     * установка текста
     */
    Templates.SetKeyword = function (text) {
        var txtarea = document.getElementById("template_text");
        if (!txtarea)
            return;
        // ---
        var scrollPos = txtarea.scrollTop;
        var strPos = 0;
        var br = ((txtarea.selectionStart || txtarea.selectionStart == '0') ? "ff"
            : (document.selection ? "ie" : false));
        if (br == "ie") {
            txtarea.focus();
            var range = document.selection.createRange();
            range.moveStart('character', -txtarea.value.length);
            strPos = range.text.length;
        }
        else if (br == "ff")
            strPos = txtarea.selectionStart;
        var front = (txtarea.value).substring(0, strPos);
        var back = (txtarea.value).substring(strPos, txtarea.value.length);
        txtarea.value = front + text + back;
        strPos = strPos + text.length;
        if (br == "ie") {
            txtarea.focus();
            var range = document.selection.createRange();
            range.moveStart('character', -txtarea.value.length);
            range.moveStart('character', strPos);
            range.moveEnd('character', 0);
            range.select();
        }
        else if (br == "ff") {
            txtarea.selectionStart = strPos;
            txtarea.selectionEnd = strPos;
            txtarea.focus();
        }
        txtarea.scrollTop = scrollPos;
        //---
        return false;
    }
    /*
     * получили данные о шаблоне
     */
    function ViewTemplateLoaded(text, xml, status, headers) {
        var template_text = document.getElementById("template_text");
        if (template_text) {
            template_text.value = text;
        }
        else {
            alert(text);
        }
    }

    /*
     * ошибка получения данных о шаблоне
     */
    function ViewTemplateError(status, text, xml, status_text, headers) {
        alert(text);
    }
})();
