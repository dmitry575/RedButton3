<?php
//+------------------------------------------------------------------+
//|                  Copyright c 2012, RedButton Web Sites Generator |
//|                                      http://www.getredbutton.com |
//+------------------------------------------------------------------+
/**
 * Обработка языков
 */
class CModel_lng
{
    /**
     * Возвращаем текущий язык
     */
    public static function GetLanguage()
    {
		$lng='en';
		//--- получаем язык из GET-запроса
		if(isset($_GET['lng']) && !empty($_GET['lng']))
         {
         $lng = $_GET['lng'];
         }
		//--- получаем язык из Cookies
		else 
			if (isset($_COOKIE['lang']) && !empty($_COOKIE['lang']))
            {
            $lng = $_COOKIE['lang'];
            }
		//--- получаем язык из заголовка Accept-Language браузера
			else 
				if (!empty($_SERVER['HTTP_ACCEPT_LANGUAGE'])) 
               {
               $array_lng = explode(';', $_SERVER['HTTP_ACCEPT_LANGUAGE'], 2);
               $lng = stripos($array_lng[0], 'ru') > -1
                  ? 'ru'
                  : 'en';
               }
		//--- убираем другие языки, кроме ru/en
		if($lng!='ru') 
         $lng='en';
		//--- добавляем полученный язык в Cookies
		setcookie('lang', $lng, time() + 315360000);
		//--- возвращаем язык
		return $lng;
    }

    /**
     * Работа с языками
     */
    public static function GetMainMenu()
    {
        global $LNG;
        //---
        print $LNG == 'en'
            ? '<strong>en</strong> | <a href="?lng=ru">ru</a>'
            : '<strong>ru</strong> | <a href="?lng=en">en</a>';
    }

}