/**
 * Работа с задачами
 */
(function ()
   {
   var API=window.API={};
   /**
    *
    */
   API.swapPageApi=function (name)
      {
      var all_blocks=["textgenerate", "randline", "textparser","randkeywords"];
      console.debug(name);
      for(var i in all_blocks)
         {
         var block=document.getElementById(all_blocks[i]);
         if(!block) continue;
         console.debug(name == all_blocks[i]);
         if(name == all_blocks[i]) block.style.display="";
         else block.style.display="none";
         }
      }
   API.SendForm=function ()
      {
      var form=document.forms["api_form"];
      form.action='?module=api&a[savesettings]&' + Math.random();
      form['a[changetoken]'].name="change";
      //---
      Ajax.form(form, {
         onrequestready: SaveTextLoaded,
         onrequesterror: SaveRequestError
      });
      };
   //--- все ок
   function SaveTextLoaded(text, xml, status, headers)
      {
      if(text != 'error')
         {
         var res=document.getElementById("api_result_url");
         if(res) res.innerHTML=text;
         }
      else
         {
         alert("Error");
         }
      }

   //--- ошибка
   function SaveRequestError(status, text, xml, status_text, headers)
      {
      alert("Error get url");
      }
   })();
