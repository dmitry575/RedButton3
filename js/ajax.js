/**
 * @copyright 2008
 */
(function () {
    var Ajax = window.Ajax = {}, connections = [], defaultTimeout = 240000;

    function request() {
        var ajax = connections.shift();
        if (ajax)
            return ajax;
        try {
            if (window.XMLHttpRequest)
                ajax = new XMLHttpRequest();
            else if (window.ActiveXObject) {
                try {
                    ajax = new ActiveXObject("Msxml2.XMLHTTP");
                } catch (e) {
                    ajax = new ActiveXObject("Microsoft.XMLHTTP");
                }
            }
        } catch (e) {
            ajax = null;
        }
        return ajax;
    }

    function return_connection(ajax) {
        ajax.abort();
        connections.push(ajax);
    }

    function headers(str) {
        var i, d, res = {};
        if (!str)
            return res;
        str = str.split('\n');
        for (i = str.length - 1; i >= 0; --i) {
            d = str[i].split(':');
            if (d.length != 2)
                continue;
            res[d[0].toLowerCase().replace(/^\s+|\s+$/g, "")] = d[1]
                .replace(/^\s+|\s+$/g, "")
        }
        return res;
    }

    function call_onready(callback, text, xml, status, header) {
        var f;
        (f = callback.onrequestready) || (f = callback.onready);
        try {
            if (f)
                f.call(callback, text, xml, status, headers(header));
        } catch (e) {
            alert(e.message);
        }
    }

    function call_onerror(callback, status, text, xml, status_text, header) {
        var f;
        (f = callback.onrequesterror) || (f = callback.onerror);
        try {
            if (f)
                f.call(callback, status, text, xml, status_text,
                    headers(header));
        } catch (e) {
            alert(e.message);
        }
    }

    function response(ajax, callback) {
        if (!callback)
            return;
        var status, text, xml, status_text, head;
        try {
            status = ajax.status
        } catch (e) {
        }
        try {
            text = ajax.responseText
        } catch (e) {
        }
        try {
            xml = ajax.responseXML
        } catch (e) {
        }
        try {
            status_text = ajax.statusText
        } catch (e) {
        }
        try {
            head = ajax.getAllResponseHeaders()
        } catch (e) {
        }

        switch (status) {
            case 200:
                if (head) {
                    call_onready(callback, text, xml, status_text, head);
                    break;
                }
                else {
                    status = 0;
                    status_text = "";
                }
            default:
                call_onerror(callback, status, text, xml, status_text, head);
        }
    }

    function get_params(params) {
        var name, i, l;
        param = [];
        if (params instanceof Array)
            for (i = 0, l = params.length; i < l; ++i)
                param.push([ params[i][0], encodeURIComponent(params[i][1]) ]
                    .join('='))
        else
            for (name in params)
                param.push([ name, encodeURIComponent(params[name]) ]
                    .join('='))

        return param.join('&');
    }

    function multipart_params(params, boundary) {
        var param = [], name, i, l;

        function addParam(name, value) {
            param.push('--');
            param.push(boundary);
            param.push('\r\nContent-Disposition: form-data; name="');
            param.push(name);
            param.push('"');
            if (value.filename) {
                param.push(';filename="')
                param.push(value.filename);
                param.push('"');
            }
            param.push('\r\n\r\n');
            if (value.value)
                param.push(value.value);
            else
                param.push(value);
            param.push('\r\n');
        }

        if (params instanceof Array)
            for (i = 0, l = params.length; i < l; ++i)
                addParam(params[i][0], params[i][1]);
        else
            for (name in params)
                addParam(name, params[name]);

        param.push('--');
        param.push(boundary);
        param.push('--\r\n');
        return param.join('');
    }

    function sendOverFrame(form, callback) {
        var doc = form.ownerDocument, frame = doc.createElement('iframe'), s, name = [
            "upload_frame", Math.random() ].join('_');

        (s = frame.style).position = "absolute";
        s.left = s.top = "-30000px";
        frame.name = name;
        frame.id = name;
        form.parentNode.insertBefore(frame, form);
        form.target = name;
        (s = form.ownerDocument.parentWindow)
        || (s = form.ownerDocument.defaultView);
        if (s.frames[name].name != name)
            s.frames[name].name = name;

        setTimeout(
            function () {
                var interval = window
                    .setInterval(
                    function () {
                        var a;
                        try {
                            (a = frame.contentWindow)
                                && (a = a.document)
                            && (a.getElementById(""));
                        } catch (e) {
                            call_onerror(callback, 0, "",
                                null, "", "");
                            clearInterval(interval);

                            frame.onload = frame.onreadystatechange = null;

                            setTimeout(function () {
                                frame.parentNode
                                    .removeChild(frame);
                            }, 0);
                        }
                    }, 2000);

                frame.onload = function () {
                    clearInterval(interval);
                    try {
                        var m = this.contentWindow.document
                            .getElementsByTagName('meta'), error = false;

                        for (var i = 0, len = m.length; i < len; ++i)
                            if (m[i].name == 'TW_RESULT'
                                && m[i].content.toUpperCase() == 'ERROR') {
                                error = true;
                                break;
                            }

                        if (!error)
                            call_onready(
                                callback,
                                this.contentWindow.document.body.innerHTML,
                                this.contentWindow.document, null, "");
                        else
                            call_onerror(
                                callback,
                                0,
                                this.contentWindow.document.body.innerHTML,
                                this.contentWindow.document, "", "");
                    } catch (e) {
                        call_onerror(callback, 0, "", null, "", "");
                    }

                    frame.onload = frame.onreadystatechange = null;
                    setTimeout(function () {
                        try {
                            frame.parentNode.removeChild(frame);
                            form.target = '';
                        } catch (e) {
                        }
                    }, 0);
                }

                frame.onreadystatechange = function () {
                    if (this.readyState == 'complete') {
                        clearInterval(interval);
                        try {
                            var m = this.contentWindow.document
                                .getElementsByTagName('meta'), error = false;

                            for (var i = 0, len = m.length; i < len; ++i)
                                if (m[i].name == 'TW_RESULT'
                                    && m[i].content.toUpperCase() == 'ERROR') {
                                    error = true;
                                    break;
                                }

                            if (!error)
                                call_onready(
                                    callback,
                                    this.contentWindow.document.body.innerHTML,
                                    this.contentWindow.document, null,
                                    "");
                            else
                                call_onerror(
                                    callback,
                                    0,
                                    this.contentWindow.document.body.innerHTML,
                                    this.contentWindow.document, "",
                                    "");
                        } catch (e) {
                            call_onerror(callback, 0, "", null, "", "");
                        }
                        frame.onload = frame.onreadystatechange = null;

                        setTimeout(function () {
                            try {
                                frame.parentNode.removeChild(frame);
                                form.target = '';
                            } catch (e) {
                            }
                        }, 0);
                    }
                }

                form.submit();
            }, 100);
    }

    function stop(ajax) {
        ajax.abort();
    }

    function stopper(ajax) {
        return
        {
            ajax : ajax
            /*timeout : setTimeout(
             function()
             {
             stop(ajax);
             }, defaultTimeout),
             */

        }
        ;
    }

    Ajax.get = function (url, params, callback) {
        var ajax = request(), sep, stop = stopper(ajax);
        if (!ajax) {
            if (onerror)
                onerror();
            return;
        }
        // ---
        params = get_params(params);
        sep = url.indexOf('?') == -1 ? '?' : '&';
        ajax.open('get', params ? [ url, params ].join(sep) : url, true);
        ajax.setRequestHeader("X-Requested-With", "XMLHttpRequest");
        ajax.onreadystatechange = function () {
            switch (ajax.readyState) {
                case 4:
                    if (stop) {
                        clearTimeout(stop.timeout);
                        stop.ajax = null;
                        stop = null;
                    }
                    // ---
                    response(ajax, callback);
                    return_connection(ajax);
                    break;
            }
        }
        try {
            ajax.send('');
        } catch (e) {
            return (false);
        }
        return stop;
    }
    Ajax.post = function (url, params, multipart, callback) {
        var ajax = request(), stop = stopper(ajax);
        ajax.open('post', url, true);
        ajax.setRequestHeader("X-Requested-With", "XMLHttpRequest");
        if (!multipart) {
            ajax.setRequestHeader('Content-Type',
                'application/x-www-form-urlencoded');
            params = get_params(params);
        }
        else {
            ajax.setRequestHeader('Content-Type',
                'multipart/form-data; boundary=AJAX----FORM');
            params = multipart_params(params, 'AJAX----FORM');
        }

        ajax.onreadystatechange = function () {
            switch (ajax.readyState) {
                case 4:
                    if (stop) {
                        clearTimeout(stop.timeout);
                        stop.ajax = null;
                        stop = null;
                    }
                    // ---
                    response(ajax, callback);
                    return_connection(ajax);
                    break;
            }
        }

        try {
            ajax.send(params);
        } catch (e) {
            return (false);
        }
        return stop;
    }
    Ajax.form = function (form, callback) {
        var params = [], elems = form.elements;

        for (var i = 0, l = elems.length; i < l; ++i) {
            var elem = elems[i];
            switch (elem.type.toLowerCase()) {
                case 'file':
                    sendOverFrame(form, callback);
                    return false;
                case 'text':
                case 'password':
                case 'hidden':
                case 'submit':
                case 'image':
                    if (elem.name)
                        params.push([ elem.name, elem.value ]);
                    continue;
                case 'checkbox':
                case 'radio':
                    if (elem.name && elem.checked)
                        params
                            .push([ elem.name, elem.value ? elem.value : 'on' ]);
                    continue;
            }
            if (elem.nodeName == 'TEXTAREA' && elem.name)
                params.push([ elem.name, elem.value ]);
            else if (elem.nodeName == 'SELECT' && elem.name)
                params.push([ elem.name, elem.value ]);
        }
        if (form.method.toLowerCase() == 'post')
            Ajax.post(form.action, params,
                (form.enctype == 'multipart/form-data'), callback);
        else
            Ajax.get(form.action, params, callback);
    }
    Ajax.stop = function (brakes) {
        var a;
        if (!brakes || !(a = brakes.ajax))
            return;
        clearTimeout(brakes.timeout);
        a.onreadystatechange = null;
        stop(a);
    }

    Ajax.url = function (url, params) {
        var p = url.split('?');
        url = p[0];
        params = get_params(params);
        /*
         * if(p[1]) { p = p[1].split('&'); for(var i in p) { var c =
         * p[i].split('='); if(! params[c[0]]) } }
         */
        return [ url, params ].join('?');
    }
})();