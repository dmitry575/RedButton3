<?php
class CPlugin_cloacking extends IPlugin
{
   /**
    * Автор
    */
   protected $m_author="LeZZvie";
   /**
    * Название
    */
   protected $m_name="Клоакинг";
   /**
    * Версия
    */
   protected $m_version="1.00";
   /**
    * Описание
    */
   protected $m_description="Генерация для клоакинга";
   /*
    * Заголовок
    */
   protected static $m_translate=array('ru'=>array('main_title'=>'Клоакинг',), 'en'=>array('main_title'=>'Cloacking',));
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
      $text=str_replace('[TEST-MACROS]', 'Testing macros - ' . $keyword . ' [NEW-TEST-MACROS]', $text);
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
      $text=$this->Compress($text);
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
    * Сжатие и "шифрование" текста через base64
    */
   private function Compress($text)
   {
      return base64_encode(str_rot13(gzdeflate($text)));
   }
}

?>
