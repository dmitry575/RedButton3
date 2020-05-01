<?php
//+------------------------------------------------------------------+
//|                  Copyright 2013, RedButton Web Sites Generator |
//|                                      http://www.getredbutton.com |
//+------------------------------------------------------------------+
/**
 *
 * Класс для выбора текста из html
 * @author User
 *
 */
class CModel_HtmlCleaner
  {
  public function __construct()
    {
    }

  /**
   * Зачистка текста.
   * NOTE: Порядок зачистки имеет большое значение, просьба его не менять.
   * @param string $text
   * @return string
   */
  public function Clear($text)
    {
    //$text=iconv('WINDOWS-1251', 'UTF-8', $text);
    //--- очищаем текст от переводов строк и табов
    $text = preg_replace("/(\r\n|\n|\t|<br>|<\/br>|<br[\s]*\/>|&nbsp;)/", ' ', $text);
    //--- убираем двойные пробелы
    $text = str_replace('  ', ' ', $text);
    $text = html_entity_decode($text);
    //--- очищаем текст от незначительных тегов
    $text = preg_replace("/<[\/]*(i|em|b|strong|span|font|u|a|font|wbr|hr)(|\s+[^<>]*)>/", '', $text);
    //--- удаляем заголовки и ненужные теги
    $text = preg_replace("/<(h1|h2|h3|h4|center|noscript|iframe|canvas)(|\s+[^<>]*)>[^<>]*<\/(h1|h2|h3|h4|center|noscript|iframe|canvas)>/", '', $text);
    //--- удаляем картинки
    $text = preg_replace("/<img[^<>]*>/", ' ', $text);
    //print $text;
    //--- удаляем скобки и их содержимое
    $text = preg_replace("/(\(|\[)[^<>]*(\)|\])/", '', $text);
    //--- получаем все тексты из <p>, <div> и <article>
    preg_match_all("/<(p|div|article)[^<>]*>([^<>\[\{\*\@\/\;]{25,})<\/(p|div|article)>/", $text, $m);
    //---
    $content = '';
    for($i = 0, $sz = sizeof($m[2]); $i < $sz; $i++)
      {
      $t = trim($m[2][$i]);
      if(empty($t) || mb_strpos($t, '&copy;') !== FALSE || mb_strpos($t, '...') !== FALSE || mb_strpos($t, '+') !== FALSE || mb_strpos($t, '©') !== FALSE || mb_strpos($t, '®') !== FALSE || mb_strpos($t, '(c)') !== FALSE || mb_strpos($t, '(C)') !== FALSE || mb_strpos($t, 'Copyright') !== FALSE || mb_strpos($t, 'rights reserved') !== FALSE || mb_strpos($t, 'Forgot password') !== FALSE || mb_strpos($t, 'powered by') !== FALSE || mb_strpos($t, 'javascript') !== FALSE || mb_strpos($t, 'iframe') !== FALSE || mb_strpos($t, 'cookies') !== FALSE || (mb_substr($t, -1) != '.' && mb_substr($t, -1) != '!' && mb_substr($t, -1) != '?')
      ) continue;
      //---
      $content .= $t . ' ';
      }
    //--- зачищаем от всех лишних пробелов
    $content = trim(preg_replace('/[\s]{2,}/', ' ', $content));
    //--- зачистим от двойных знаков вопросов
    $content = trim(preg_replace('/[\?]{2,}/', '', $content));
    //---
    $content = str_replace(chr(0xEF) . chr(0xBF) . chr(0xBD), '', $content);
    return $content;
    }
  }

?>