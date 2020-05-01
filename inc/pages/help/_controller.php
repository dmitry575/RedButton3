<?php
class CHelp extends IPage
{
   private $m_settings;
   //--- переводы
   protected static $m_translate=array('ru'=>array('main_title'=>'Макросы, которые можно использовать в шаблонах',), //---
      'en'=>array('main_title'=>'Help',));
   //---
   /**
    * Конструктор
    */
   public function __construct()
   {
      $this->SetTitle(CHelp::GetTranslate('main_title'));
   }
   /**
    * Отображение доверя
    * @see IPage::Show()
    */
   public function Show($path=null)
   {
      global $LNG;
      //---
      include("./inc/pages/help/index.phtml");
   }
   /**
    * Получение перевода
    *
    * @param string $name
    */
   public static function GetTranslate($name)
   {
      global $LNG;
      if(isset(self::$m_translate[$LNG]) && isset(self::$m_translate[$LNG][$name]))
         {
         return self::$m_translate[$LNG][$name];
         }
      //--- если языка нет, то может английский подойдет?
      if($LNG != 'en' && isset(self::$m_translate['en']) && isset(self::$m_translate[$LNG][$name]))
         {
         return self::$m_translate[$LNG][$name];
         }
      //----
      return '';
   }
}

?>
