<?php
class CXrumer extends IPage
{
   private $m_settings;
   //--- переводы
   protected static $m_translate=array('ru'=>array('main_title'=>'Проекты для хрумера', 'b_setting_project'=>'Настройки проекта', 'b_project_forum'=>'Под пиар форумы', 'b_project_profile'=>'Под другие профиля', 'b_project_topics'=>'Под топики', 'b_email_server'=>'Почтовый сервер', 'b_pop_server'=>'POP3 сервер', 'b_redbutton_url'=>'Ссылка на генератор', 'b_setting_sheduler'=>'Настройки расписания', 'b_sheduler_base'=>'Количество прогонов для пиар базы', 'b_sheduler_default'=>'Количество прогонов для обычных', 'b_sheduler_topics'=>'Количество прогонов для топиков', 'b_setting_linker'=>'Настройки ссылочника', 'b_linker_count_signature'=>'Количество ссылок в подписе', 'b_linker_count_text'=>'Количество предложений текста', 'b_linker_count_links'=>'Количество ссылок в тексте', 'b_linker_number_trast'=>'Номер трастовой базы', 'b_linker_number_normal'=>'Номер обычной базы', 'b_linker_number_topics'=>'Номер базы топиков', 'b_save_settings'=>'Сохранить', 'b_save_success'=>'Настройки успешно сохранены', 'b_save_error'=>'При сохранении произошла ошибка', 'b_email_file'=>'Файлы с емайлами', 'b_email_file_description'=>'емайлы должны быть в формате: email|пароль',), //---
      'en'=>array('main_title'=>'Projects for Xrumer', 'b_setting_project'=>'Settings xrumer', 'b_project_forum'=>'Pear forum', 'b_project_profile'=>'Another profiles', 'b_project_topics'=>'Topics', 'b_email_server'=>'Email server', 'b_pop_server'=>'POP3 server', 'b_redbutton_url'=>'Url generator', 'b_setting_sheduler'=>'Settings sheduler', 'b_sheduler_base'=>'Count repeat registration for pear base', 'b_sheduler_default'=>'Count repeat registration for default', 'b_sheduler_topics'=>'Count repeat registration for topics', 'b_setting_linker'=>'Settings linker', 'b_linker_count_signature'=>'Counts links in signature', 'b_linker_count_text'=>'Count sentences in text', 'b_linker_count_links'=>'Counts links in text', 'b_linker_number_trast'=>'Number trast database', 'b_linker_number_normal'=>'Number normal database', 'b_linker_number_topics'=>'Number topics database', 'b_save_settings'=>'Save', 'b_save_success'=>'Settings saved', 'b_save_error'=>'Saving settings failed', 'b_email_file'=>'Emails file', 'b_email_file_description'=>'files format: email|password',));
   //---
   private $m_model;
   /**
    * Конструктор
    */
   public function __construct()
   {
      global $IS_CRYPT;
      $this->SetTitle(CXrumer::GetTranslate('main_title'));
      $this->m_model=new CModel_xrumer();
   }
   /**
    * Отображение доверя
    * @see IPage::Show()
    */
   public function Show($path=null)
   {
      $this->m_settings=$this->m_model->ReadSettings();
      //---
      include("./inc/pages/xrumer/index.phtml");
      if(isset($_SESSION['xrumer_save'])) unset($_SESSION['xrumer_save']);
   }
   /**
    * Обработка запросов
    * @see IPage::Action()
    */
   public function Action($url, $action)
   {
      $method_name='on' . $action;
      //---
      if(method_exists($this, $method_name)) $this->$method_name($url);
   }
   /**
    *
    * Сохранение задачи
    * @param array $url
    */
   private function OnSaveSettings($url)
   {
      if($this->m_model->SaveSettings($_POST))
         {
         $_SESSION['xrumer_save']=1;
         CLogger::write(CLoggerType::DEBUG, "xrumer: settings saved");
         }
      else
         {
         $_SESSION['xrumer_save']=-1;
         CLogger::write(CLoggerType::ERROR, "xrumer: settings not save");
         }
      //---
      header("Location: ?module=xrumer");
      exit;
   }
   /**
    * ПОлучение настроек
    */
   private function GetSettings($name, $default='')
   {
      return isset($this->m_settings[$name]) ? htmlspecialchars($this->m_settings[$name]) : $default;
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
