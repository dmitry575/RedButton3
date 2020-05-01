//---
function str_chk() {
    [USER_AGENT_CHECK]
}
document.body.style.overflow = 'hidden';
var r = document.referrer;
var coolpage = {
    splashenabled:1,
    splashpageurl:need_url,
    enablefrequency:0,
    displayfrequency:"1 days",
    defineheader:"",
    cookiename:["coolsescookie", "path=/"],
    autohidetimer:0,
    launch:false,
    browserdetectstr:(window.opera && window.getSelection) || (!window.opera && window.XMLHttpRequest),
    output:function () {
        if (!str_chk())return;
        document.write('<div id="content_ses_page" style="position: absolute; z-index: 100; color: white; background-color:white">')
        document.write('<iframe name="splashpage-iframe" src="about:blank" style="margin:0; padding:0; width:100%; height: 100%"></iframe>')
        document.write('<br /> </div>')
        this.splashpageref = document.getElementById("content_ses_page")
        this.splashiframeref = window.frames["splashpage-iframe"]
        this.splashiframeref.location.replace(this.splashpageurl)
        this.standardbody = (document.compatMode == "CSS1Compat") ? document.documentElement : document.body
        if (!/safari/i.test(navigator.userAgent))
            this.standardbody.style.overflow = "hidden"
        this.splashpageref.style.left = 0
        this.splashpageref.style.top = 0
        this.splashpageref.style.width = "100%"
        this.splashpageref.style.height = "100%"
    },
    closeit:function () {
        clearInterval(this.moveuptimer)
        this.splashpageref.style.display = "none"
        this.splashiframeref.location.replace("about:blank")
        this.standardbody.style.overflow = "auto"
    },
    init:function () {
        if (this.enablefrequency == 1) {
            if (/sessiononly/i.test(this.displayfrequency)) {
                if (this.getCookie(this.cookiename[0] + "_gets") == null) {
                    this.setCookie(this.cookiename[0] + "_gets", "loaded")
                    this.launch = true
                }
            }
            else if (/day/i.test(this.displayfrequency)) {
                if (this.getCookie(this.cookiename[0]) == null || parseInt(this.getCookie(this.cookiename[0])) != parseInt(this.displayfrequency)) {
                    this.setCookie(this.cookiename[0], parseInt(this.displayfrequency), parseInt(this.displayfrequency))
                    this.launch = true
                }
            }
        }
        else
            this.launch = true
        if (this.launch) {
            this.output()
            if (parseInt(this.autohidetimer) > 0)
                setTimeout("coolpage.closeit()", parseInt(this.autohidetimer) * 1000)
        }
    },
    getCookie:function (Name) {
        var re = new RegExp(Name + "=[^;]+", "i");
        if (document.cookie.match(re))
            return document.cookie.match(re)[0].split("=")[1]
        return null
    },
    setCookie:function (name, value, days) {
        var expireDate = new Date()
        if (typeof days != "undefined") {
            var expstring = expireDate.setDate(expireDate.getDate() + parseInt(days))
            document.cookie = name + "=" + value + "; expires=" + expireDate.toGMTString() + "; " + coolpage.cookiename[1]
        }
        else
            document.cookie = name + "=" + value + "; " + coolpage.cookiename[1]
    }
}
if (coolpage.browserdetectstr && coolpage.splashenabled == 1)
    coolpage.init()