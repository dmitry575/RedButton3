/**
 * Работа с задачами
 */
(function () {
    var Pinger = window.Pinger = {};
    Pinger.ViewPacketTasksAdd = function () {
        $('task-packet').style.display
            = $('task-packet').style.display == 'none'
            ? 'block'
            : 'none';
    }
    Pinger.ViewServicesAdd = function () {
        $('services-packet').style.display
            = $('services-packet').style.display == 'none'
            ? 'block'
            : 'none';
    }

    /**
     * Добавить новую строку с заданием
     * в список заданий (textarea)
     */
    Task.AddNew = function () {
        var stringBuilder = [];
        //---
        stringBuilder.push($('taskNextUrl').value);
        //---
        if ($('taskUploadTo').value == 'ftp')
            stringBuilder.push($('taskFtpServer').value);
        //---
        stringBuilder.push($('taskPath').value);
        stringBuilder.push($('taskKeys').value);
        stringBuilder.push($('taskTexts').value);
        stringBuilder.push($('taskSettings').value);
        //---
        var newLine = ($('taskList').value == '' || ($('taskList').value).substr(-1) == '\n')
            ? ''
            : '\n';
        $('taskList').value += (newLine + stringBuilder.join('|'));
    }
    //--- все ок
    function SaveTextLoaded(text, xml, status, headers) {
        document.forms[form_name].action = "";
        if (text == 'success') {
            alert("Задача сохранена успешно");
        }
        else {
            alert(text);
        }
    }

    //--- ошибка
    function SaveRequestError(status, text, xml, status_text, headers) {
        document.forms[form_name].action = "";
        alert("Ошибка сохранения данных");
    }

    //--- все ок при просмотре
    function ViewTextLoaded(text, xml, status, headers) {
        var div_content = document.getElementById("div_content");
        if (div_content) {
            div_content.innerHTML = text;
            div_content.style.display = "";
        }
        else {
            alert("Ошибка при получении данных")
        }
    }

    //--- ошибка
    function ViewRequestError(status, text, xml, status_text, headers) {
        alert("Ошибка сохранения данных");
    }

    //--- все ок при старте задач
    function StartTextLoaded(text, xml, status, headers) {
        alert(text);
    }

    //--- ошибка при старте задач
    function StartRequestError(status, text, xml, status_text, headers) {
        alert("Ошибка запуска задач");
    }

    //--- все ок при старте задач
    function ViewLogsTextLoaded(text, xml, status, headers) {
        var div_content = document.getElementById("div_content");
        if (div_content) {
            div_content.innerHTML = text;
            div_content.style.display = "";
        }
        else {
            alert("Ошибка при получении данных")
        }
    }

    //--- ошибка при старте задач
    function ViewLogsRequestError(status, text, xml, status_text, headers) {
        alert("Ошибка при получении данных");
    }

    //--- ошибка
    function ClearTaskRequestError(status, text, xml, status_text, headers) {
        alert("Ошибка сохранения данных");
    }

    //--- все ок при старте задач
    function ClearTaskTextLoaded(text, xml, status, headers) {
        window.location = "?module=tasks";
    }

})();

