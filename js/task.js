/**
 * Работа с задачами
 */
(function ()
   {
   var Task=window.Task={};
   /**
    * Отправка данных о сохранении задачи
    */
   var form_name="";
   Task.Save=function (formname, type)
      {
      form_name=formname;
      var form=document.forms[formname];
      form.action='?module=tasks&a[savetask]&' + Math.random();
      //---
      Ajax.form(form, {
            onrequestready: SaveTextLoaded,
            onrequesterror: SaveRequestError
         });
      };
   //--- просмотр задач
   Task.View=function ()
      {
      Ajax.post("?module=tasks&a[viewtask]", null, false, {
            onrequestready: ViewTextLoaded,
            onrequesterror: ViewRequestError
         });
      };
   //--- загрузка фтп файлов
   Task.UploadFtpFiles=function ()
      {
      var form=document.forms["upload_ftps"];
      form.action='?module=tasks&a[uploadftps]&' + Math.random();
      
      Ajax.form(form, {
            onrequestready: UploadFtpsTextLoaded,
            onrequesterror: UploadFtpsRequestError
         });
      };
   //--- ошибка
   function UploadFtpsRequestError(status, text, xml, status_text, headers)
      {
      alert("Ошибка обработки данных. Сообщите об этом пожалуйста разработчикам!");
      }

   //--- все ок при просмотре
   function UploadFtpsTextLoaded(text, xml, status, headers)
      {
      var div_content=document.getElementById("taskList");
      if(div_content)
         {
         div_content.value=text;
         }
      else
         {
         alert("Ошибка при получении данных")
         }
      }

   //--- запуск задач (ТЕПЕРЬ ЗАПУСКАЕТСЯ БЕЗ АЯКСА, ЧЕРЕЗ СОКЕТЫ)
   /*
    Task.Start = function()
    {
    //---
    Ajax.post("?module=tasks&a[starttask]", null, false,
    {
    onrequestready : StartTextLoaded,
    onrequesterror : StartRequestError
    });
    alert("Запрос на запуск задач отправлен. Можете закрыть браузер, либо дождаться ответа от сервера");
    };
    */
   //--- запуск задач
   Task.ViewLogs=function ()
      {
      //---
      Ajax.post("?module=tasks&a[viewlogs]", null, false, {
            onrequestready: ViewLogsTextLoaded,
            onrequesterror: ViewLogsRequestError
         });
      };
   //--- запуск задач
   Task.ClearTask=function ()
      {
      Ajax.post("?module=tasks&a[cleartask]", null, false, {
            onrequestready: ClearTaskTextLoaded,
            onrequesterror: ClearTaskRequestError
         });
      };
   Task.ViewPacketTasksAdd=function ()
      {
      $('task-packet').style.display=$('task-packet').style.display == 'none' ? 'block' : 'none';
      }
   Task.ChangeUploadTo=function (t)
      {
      if(t != undefined)
         {
         $('taskFtpField').style.visibility=t.value == 'ftp' ? 'visible' : 'hidden';
         $('taskFtpField').style.display=t.value == 'ftp' ? '' : 'none';
         }
      }
   /**
    * Добавить новую строку с заданием
    * в список заданий (textarea)
    */
   Task.AddNew=function ()
      {
      var stringBuilder=[];
      //---
      stringBuilder.push($('taskNextUrl').value);
      //---
      if($('taskUploadTo').value == 'ftp')
         stringBuilder.push($('taskFtpServer').value);
      //---
      stringBuilder.push($('taskPath').value);
      stringBuilder.push($('taskKeys').value);
      stringBuilder.push($('taskTexts').value);
      stringBuilder.push($('taskSettings').value);
      //---
      var newLine=($('taskList').value == '' || ($('taskList').value).substr(-1) == '\n') ? '' : '\n';
      $('taskList').value+=(newLine + stringBuilder.join('|'));
      }
   //--- все ок
   function SaveTextLoaded(text, xml, status, headers)
      {
      document.forms[form_name].action="";
      if(text == 'success')
         {
         alert("Задача сохранена успешно");
         }
      else
         {
         alert(text);
         }
      }

   //--- ошибка
   function SaveRequestError(status, text, xml, status_text, headers)
      {
      document.forms[form_name].action="";
      alert("Ошибка сохранения данных");
      }

   //--- все ок при просмотре
   function ViewTextLoaded(text, xml, status, headers)
      {
      var div_content=document.getElementById("div_content");
      if(div_content)
         {
         div_content.innerHTML=text;
         div_content.style.display="";
         }
      else
         {
         alert("Ошибка при получении данных")
         }
      }

   //--- ошибка
   function ViewRequestError(status, text, xml, status_text, headers)
      {
      alert("Ошибка сохранения данных");
      }

   //--- все ок при старте задач
   function StartTextLoaded(text, xml, status, headers)
      {
      alert(text);
      }

   //--- ошибка при старте задач
   function StartRequestError(status, text, xml, status_text, headers)
      {
      alert("Ошибка запуска задач");
      }

   //--- все ок при старте задач
   function ViewLogsTextLoaded(text, xml, status, headers)
      {
      var div_content=document.getElementById("div_content");
      if(div_content)
         {
         div_content.innerHTML=text;
         div_content.style.display="";
         }
      else
         {
         alert("Ошибка при получении данных")
         }
      }

   //--- ошибка при старте задач
   function ViewLogsRequestError(status, text, xml, status_text, headers)
      {
      alert("Ошибка при получении данных");
      }

   //--- ошибка
   function ClearTaskRequestError(status, text, xml, status_text, headers)
      {
      alert("Ошибка сохранения данных");
      }

   //--- все ок при старте задач
   function ClearTaskTextLoaded(text, xml, status, headers)
      {
      window.location="?module=tasks";
      }
   })();

