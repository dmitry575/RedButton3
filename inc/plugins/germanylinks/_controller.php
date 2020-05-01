<?php
class CPlugin_Germanylinks extends IPlugin
{
   /**
    * Автор
    */
   protected $m_author="Dmitry";
   /**
    * Название
    */
   protected $m_name="Germamy links";
   /**
    * Версия
    */
   protected $m_version="1.00";
   /**
    * Описание
    */
   protected $m_description="Замена в урлах немецских символов специальным образом";
   /*
    * Заголовок
    */
   protected static $m_translate=array('ru'=>array('main_title'=>'Germamy links',), 
'en'=>array('main_title'=>'Germamy links',));

   private static $m_translit_alpha = array('ä'=>'ae',
                                      'ö'=>'oe', 
                                      'ß'=>'ss',
                                      'ü'=>'ue',
                                      'Ä'=>'ae',
                                      'Ö'=>'oe',
                                      'ß'=>'ss',
                                      'Ü'=>'ue',
                                      ' '=>'-',
                                      'А' => 'A',
                                           'Б' => 'B',
                                           'В' => 'V',
                                           'Г' => 'G',
                                           'Д' => 'D',
                                           'Е' => 'E',
                                           'Ж' => 'ZH',
                                           'З' => 'Z',
                                           'И' => 'I',
                                           'Й' => 'Y',
                                           'К' => 'K',
                                           'Л' => 'L',
                                           'М' => 'M',
                                           'Н' => 'N',
                                           'О' => 'O',
                                           'П' => 'P',
                                           'Р' => 'R',
                                           'С' => 'S',
                                           'Т' => 'T',
                                           'У' => 'U',
                                           'Ф' => 'F',
                                           'Х' => 'H',
                                           'Ц' => 'C',
                                           'Ч' => 'CH',
                                           'Ш' => 'SH',
                                           'Щ' => 'SCH',
                                           'Ъ' => '',
                                           'Ы' => 'Y',
                                           'Ь' => '',
                                           'Э' => 'E',
                                           'Ю' => 'Y',
                                           'Я' => 'YA',
                                           'а' => 'a',
                                           'б' => 'b',
                                           'в' => 'v',
                                           'г' => 'g',
                                           'д' => 'd',
                                           'е' => 'e',
                                           'ж' => 'zh',
                                           'з' => 'z',
                                           'и' => 'i',
                                           'й' => 'y',
                                           'к' => 'k',
                                           'л' => 'l',
                                           'м' => 'm',
                                           'н' => 'n',
                                           'о' => 'o',
                                           'п' => 'p',
                                           'р' => 'r',
                                           'с' => 's',
                                           'т' => 't',
                                           'у' => 'u',
                                           'ф' => 'f',
                                           'х' => 'h',
                                           'ц' => 'c',
                                           'ч' => 'ch',
                                           'ш' => 'sh',
                                           'щ' => 'sch',
                                           'ъ' => '',
                                           'ы' => 'y',
                                           'ь' => '',
                                           'э' => 'e',
                                           'ю' => 'u',
                                           'я' => 'ya',
                                           ' ' => '-');
   /**
    * Выполение функции до начала работы с ключевиками
    * @param string $keyword
    * @param string $text
    * @param array $data
    */
   public function OnBeginKeyword(&$keyword, &$text, &$data)
   {
   }
   /**
    * Выполение функции до начала обработки макросов
    * @param string $keyword
    * @param string $text
    * @param array $data
    */
   public function OnBeginMacros(&$keyword, &$text, &$data)
   {
   }
   /**
    *
    * Выполение функции после обработки всех макросов
    * @param string $keyword
    * @param string $text
    * @param array $data
    */
   public function OnEndMacros(&$keyword, &$text, &$data)
   {
   }
   /**
    *
    * Выполение функции после обработки всех макросов
    * @param string $keyword
    * @param string $text
    * @param array $data
    */
   public function OnEndKeyword(&$keyword, &$text, &$data)
   {
   }
  /**
   *
   * Выполение функции получения имени страницы
   * @param string $keyword Кейворд
   * @param string $pageName Название урла будущей страницы
   * @param string $pageNum Номер страницы (кейворда)
   */
  public function OnGetPageName(&$keyword,&$pageName, $pageNum)
    {
    }

  /**
   *
   * Выполение функции сохранения имени страницы
   * @param string $keyword Кейворд
   * @param string $pageName Название урла будущей страницы
   */
  public function OnTranslitUrl(&$keyword, &$url)
    {
    $cyr_str = $keyword;
    //---
    $text = strtr($cyr_str, self::$m_translit_alpha);
    $text = preg_replace("/[^a-zа-Я0-9\-]*/i", '', $text);
    $text = str_replace('--', '-', $text);
    $text = str_replace('--', '-', $text);
    $url= trim($text, '- ');
    }

}

?>
