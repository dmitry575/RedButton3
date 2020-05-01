function $(id)
   {
   return document.getElementById(id);
   }
/**
 * ПЕРЕКЛЮЧАТЕЛЬ ГИПЕРССЫЛОК
 */
function aswap(listId, itemId)
   {
   var newList=[];
   var swapList=$('aswap-' + listId);
   var hiddenInput=$('aswap-input-' + listId);
   var selectedLinkId='aswap-' + listId + '-' + itemId;
   var sz=0;
   // --- проверим наличие блока с гиперссылками
   if(swapList == undefined)
      {
      alert('JS ERROR: aswap(): swapList is undefined');
      return;
      }
   // --- проверим наличие скрытого текстового поля
   if(hiddenInput == undefined)
      {
      alert('JS ERROR: aswap(): hiddenInput is undefined');
      return;
      }
   // --- получим все гиперссылки из списка
   var listAnkers=swapList.getElementsByTagName('A');
   sz=listAnkers.length;
   // --- пройдемся по всем полученным гиперссылкам
   for(var i=0; i<sz; i++)
      {
      // --- пропускаем элементы, которые не являются гиперссылками
      if(listAnkers[i] == undefined || listAnkers[i].tagName != 'A')
         continue;
      //---
      var link=document.createElement("a");
      link.setAttribute("href", listAnkers[i].href);
      link.setAttribute("id", listAnkers[i].id);
      link.setAttribute("className", listAnkers[i].className);
      link.appendChild(document.createTextNode(listAnkers[i].innerHTML));
      // link=aNode;
      // --- добавляем в массив все неосновные гиперссылки
      if(link.id != selectedLinkId)
         {
         link.className='';
         newList.push(link);
         }
      // --- добавляем в начало массива главную (выбранную) гиперссылку
      else
         {
         link.className='selected';
         newList.unshift(link);
         }
      }
   // --- очищаем старый блок с гиперссылками
   swapList.innerHTML='';
   // --- получим длину массива с новыми гиперссылками
   sz=newList.length;
   // --- по-очереди добавляем гиперссылки в блок
   for(var ii=0; ii<sz; ii++)
      {
      swapList.appendChild(newList[ii]);
      // --- добавляем разделитель, если это не последняя гиперссылка
      if((ii + 1)<sz)
         swapList.appendChild(document.createTextNode(' / '));
      }
   // --- вставляем выбранное значение в скрытый INPUT
   hiddenInput.value=itemId;
   // --- очищаем переменные
   swapList=newList=listAnkers=hiddenInput=null;
   }
function swapUploadTo(id)
   {
   if(id == 'ftp')
      {
      $('uploadToLocal').style.display='none';
      $('uploadToFtp').style.display='block';
      }
   else
      {
      $('uploadToLocal').style.display='block';
      $('uploadToFtp').style.display='none';
      }
   }

function ChangeUploadTo(select)
{
if(select.value== 'ftp')
      {
      $('uploadToLocal').style.display='none';
      $('uploadToFtp').style.display='block';
      }
   else
      {
      $('uploadToLocal').style.display='block';
      $('uploadToFtp').style.display='none';
      }

}
function swapKeysFrom(id)
   {
   if(id == 'file')
      {
      $('keysFromFile').style.display='block';
      $('keysFromList').style.display='none';
      }
   else
      {
      $('keysFromFile').style.display='none';
      $('keysFromList').style.display='block';
      }
   }
function swapTextFrom(id)
   {
   if(id == 'file')
      {
      $('textFromFile').style.display='block';
      $('textFromList').style.display='none';
      }
   else
      {
      $('textFromFile').style.display='none';
      $('textFromList').style.display='block';
      }
   }
function swapRandLinesFrom(id)
   {
   if(id == 'file')
      {
      $('randLinesFromFile').style.display='block';
      $('randLinesFromList').style.display='none';
      }
   else
      {
      $('randLinesFromFile').style.display='none';
      $('randLinesFromList').style.display='block';
      }
   }
function swapPageType(type)
   {
   if(type == 'static')
      {
      $('staticPage').style.display='block';
      $('dynamicPage').style.display='none';
      }
   else
      {
      $('staticPage').style.display='none';
      $('dynamicPage').style.display='block';
      }
   }
function swapDynamicPageNamesFrom(type)
   {
   if(type == 'list')
      {
      $('dynamicPageNamesBlock').style.display='inline';
      $('dynamicPageNameBlock').style.display='none';
      }
   else
      {
      $('dynamicPageNamesBlock').style.display='none';
      $('dynamicPageNameBlock').style.display='block';
      }
   }
function swapStaticPageNamesFrom(type)
   {
   if(type == 'list')
      {
      $('staticPageNamesBlock').style.display='inline';
      $('staticPageNameBlock').style.display='none';
      }
   else
      {
      $('staticPageNamesBlock').style.display='none';
      $('staticPageNameBlock').style.display='block';
      }
   }
/**
 * Переключение выбора рандомных кейвордов
 */
function swapKeysRandom(t)
   {
   $('keysRandomBox').style.display=t.checked == true ? 'block' : 'none';
   }
/**
 * Переключение выбора расширенного режима для FTP
 */
function swapFtpAdvanced(t)
   {
   $('ftpAdvancedBox').style.display=t.checked == true ? 'block' : 'none';
   }
/**
 * Переключение выбора расширенного режима для FTP
 */
function swapOnePage(t)
   {
   $('onePageBox').style.display=t.checked == true ? 'block' : 'none';
   }
/**
 * Переключение выбора расширенного режима
 */
function swapOnePageAdv(id, obj, style)
   {
   var div=$(id);
   if(div) div.style.display=obj.checked == true ? style : 'none';
   }
function CheckDeleayAlways(check, id_ftp)
   {
   if(check.checked)
      {
      var d=$(id_ftp);
      if(d) d.disabled='disabled';
      }
   else
      {
      var d=$(id_ftp);
      if(d) d.disabled=false;
      }
   }
/**
 * Класс для работы с примерами заполенения полей в доргене
 */
var myExamples={};
// --- список примеров
myExamples.examples={
   // --- статические
   'staticPageNameCustom': [ '[KEYWORD].html', '[N].html', 'page[N].html', '[KEYWORD]-[N].html' ],
   // --- динамические
   'dynamicPageNameCustom': [ '?topic=[N]', '?item=[N]', '?topic=[KEYWORD]&id=[N]', '?item=[KEYWORD]&page=[N]', '?view=[KEYWORD]&page=[N]&status=1', '[KEYWORD]/[N]', '[N]/[KEYWORD]' ],
   'testPage2': []
};
/**
 * Получить пример заполнения поля
 */
myExamples.Get=function (id)
   {
   // --- инициализация переменных
   var input=$(id), pos=0;
   // --- проверяем наличие поля и примера
   if(input == undefined || myExamples.examples[id] == undefined)
      return;
   // --- если у поля есть значение
   if(input.value != '')
      {
      for(var i in myExamples.examples[id])
         {
         // --- находим позицию текущего значения поля
         if(myExamples.examples[id][i] == input.value)
            {
            // --- вычисляем позицию для следующего значения поля
            pos=++i>=myExamples.examples[id].length ? 0 : i;
            break;
            }
         }
      }
   // --- присваиваем значение к полю
   $(id).value=myExamples.examples[id][pos];
   // --- очищаем память
   input=pos=i=null;
   };
/**
 * Работа с задачами
 */
(function ()
   {
   var News=window.News={};
   /**
    * Получение данных о последнем билде
    */
   News.GetLast=function ()
      {
      Ajax.post("?a[getlastnews]&" + Math.random(), null, false, {
         onrequestready: ViewNewsLoaded,
         onrequesterror: ViewNewsError
      });
      };
   //--- ошибка
   function ViewNewsError(status, text, xml, status_text, headers)
      {
      }

   //--- все ок при просмотре
   function ViewNewsLoaded(text, xml, status, headers)
      {
      }
   })();
/**
 Тримим строку
 */
function trim(str)
   {
   return str.replace(/^\s\s*/, '').replace(/\s\s*$/, '');
   };
/**
 * Добавление всех чекбоксов к главному
 */
function CheckedCheckbox(form, begin_name, val, input_name)
   {
   if(!form) return;
   var input=$(input_name);
   if(!input) return;
   //---
   input.value='';
   var elems=form.elements;
   for(var i=0, l=elems.length; i<l; ++i)
      {
      var elem=elems[i];
      //--- находим только нужные нам checkbox
      if(elem.type.toLowerCase() == "checkbox")
         {
         if(elem.id.indexOf(begin_name) == 0)
            {
            elem.checked=val;
            input.value+=elem.value + ",";
            }
         }
      }
   }
/**
 * Добавление всех чекбоксов к главному
 */

function UpdateCheckedCheckbox(input_name, check, val)
   {
   var input=$(input_name);
   if(!input) return;
   //---
   var arr=input.value.split(",");
   input.value='';
   //---
   var is_add=false;
   for(var i in arr)
      {
      if(arr[i] == undefined || arr[i] == '') continue;
      if(trim(arr[i]) == val && !check)
         {
         continue;
         }
      else input.value+=trim(arr[i]) + ",";
      }
   if(check)
      {
      input.value+=val + ",";
      }
   }
(function ()
   {
   var Cloaking=window.Cloaking={};
   Cloaking.CheckingParams=function (radio)
      {
      if(radio.value == "")
         {
         EnableRedirect(true);
         return;
         }
      if(radio.value == "htaccess")
         {
         EnableRedirect(false);
         return;
         }
      //---
      EnableRedirect(true);
      var node=$('redirect_list');
      if(!node) return;
      list=node.getElementsByTagName('input');
      length=list.length;
      for(i=0; i<length; i++)
         {
         if(list[i].value == '')
            {
            if(list[i].checked)
               {
               if(i + 1>length)
                  list[i - 1].checked=true;
               else list[i + 1].checked=true;
               }
            //---
            list[i].disabled="disabled";
            break;
            }
         }
      }
   function EnableRedirect(data)
      {
      var node=$('redirect_list');
      if(!node) return;
      list=node.getElementsByTagName('input');
      length=list.length;
      for(i=0; i<length; i++)
         {
         list[i].disabled=data ? "" : "disabled";
         }
      }
   })();
/**
 * ОБРАБОТКА ПОСЛЕ ЗАГРУЗКИ СТРАНИЦЫ
 */
window.onload=function ()
   {
   return;
   }