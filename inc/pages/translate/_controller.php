<?php
class CTranslate extends IPage
  {
  private $m_settings;
  //--- список языков для перевода
  private static $m_languages = array('en',
                                      'ru',
                                      'es',
                                      'it',
                                      'de',
                                      'fr',
                                      'pl');
  //--- с какого языка переводим
  private $m_lang_from = 'en';
  //--- на какой язык переводим
  private $m_lang_to = 'ru';
  //--- имя файла для перевода
  private $m_filename = '';
  //--- через какие переводчики переводить
  private $m_translate_system = 2;
  //--- переводы
  protected static $m_translate = array('ru' => array('main_title'          => 'Перевод текстов',
                                                      'en'                  => 'Английский',
                                                      'ru'                  => 'Русский',
                                                      'es'                  => 'Испанский',
                                                      'it'                  => 'Итальянский',
                                                      'de'                  => 'Немецкий',
                                                      'fr'                  => 'Французкий',
                                                      'pl'                  => 'Польский',
                                                      'b_translate'         => 'Перевод текстов',
                                                      'b_start'             => 'Перевести',
                                                      'b_language_from'     => 'Перевести с',
                                                      'b_language_to'       => 'на',
                                                      'b_filename'          => 'Файл, который будем переводить',
                                                      'b_translate_google'  => 'Перевести с помощью translate.googl.com',
                                                      'b_translate_yandex'  => 'Перевести с помощью translate.yandex.ru, только с русскими текстами',
                                                      'b_translate_success' => 'Перевод успешно осуществлен',
                                                      'b_translate_error'   => 'Возникли ошибки при переводе',
                                                      'add_task'            => 'Добавить в задания'),
    //---
                                        'en' => array('main_title'          => 'Translate',
                                                      'en'                  => 'English',
                                                      'ru'                  => 'Russian',
                                                      'es'                  => 'Spanish',
                                                      'it'                  => 'Italian',
                                                      'de'                  => 'German',
                                                      'fr'                  => 'French',
                                                      'pl'                  => 'Polish',
                                                      'b_translate'         => 'Translate',
                                                      'b_start'             => 'Translate',
                                                      'b_language_from'     => 'Translate from',
                                                      'b_language_to'       => 'to',
                                                      'b_filename'          => 'File for translate',
                                                      'b_translate_google'  => 'From translate.google.com',
                                                      'b_translate_yandex'  => 'From translate.yandex.ru, only russian texts',
                                                      'b_translate_success' => 'Translate success',
                                                      'b_translate_error'   => 'Translate error',
                                                      'add_task'            => 'Add to task'));
  //---
  private $m_list_tasks;
  private $m_model;

  /**
   * Конструктор
   */
  public function __construct()
    {
    $this->m_settings = new CModel_settings();
    $this->SetTitle(CTasks::GetTranslate('main_title'));
    $this->m_model = new CModel_translate();
    //---
    $this->SetSettings();
    }

  /**
   *
   * Текущие настройки
   */
  private function SetSettings()
    {
    if(isset($_REQUEST['language_from']))
      {
      $this->m_lang_from               = $_REQUEST['language_from'];
      $_SESSION['translate_lang_from'] = $this->m_lang_from;
      }
    elseif(isset($_SESSION['translate_lang_from'])) $this->m_lang_from = $_SESSION['translate_lang_from'];
    //---
    if(isset($_REQUEST['language_to']))
      {
      $this->m_lang_to               = $_REQUEST['language_to'];
      $_SESSION['translate_lang_to'] = $this->m_lang_to;
      }
    elseif(isset($_SESSION['translate_lang_to'])) $this->m_lang_to = $_SESSION['translate_lang_to'];
    //---
    if(isset($_REQUEST['filename']))
      {
      $this->m_filename               = $_REQUEST['filename'];
      $_SESSION['translate_filename'] = $this->m_filename;
      }
    elseif(isset($_SESSION['translate_filename'])) $this->m_filename = $_SESSION['translate_filename'];
    //---
    if(isset($_REQUEST['translate_yandex']) || isset($_REQUEST['translate_gogole']))
      {
      $this->m_translate_system = 0;
      if(isset($_REQUEST['translate_gogole'])) $this->m_translate_system |= CModel_translate::TRANSLATE_GOOGLE;
      if(isset($_REQUEST['translate_yandex'])) $this->m_translate_system |= CModel_translate::TRANSLATE_YANDEX;
      //--- сохраним в сессии настройки перевода серверов
      $_SESSION['translate_system'] = $this->m_translate_system;
      }
    elseif(isset($_SESSION['translate_system'])) $this->m_translate_system = $_SESSION['translate_system'];
    }

  /**
   * Отображение переводов
   * @see IPage::Show()
   */
  public function Show($path = null)
    {
    //---
    include("./inc/pages/translate/index.phtml");
    if(isset($_SESSION['translated'])) unset($_SESSION['translated']);
    }

  /**
   *
   * Список задач
   */
  public function GetListTask()
    {
    return $this->m_list_tasks;
    }

  /**
   *
   * Текущая модель
   */
  public function GetModel()
    {
    return $this->m_model;
    }

  /**
   * Обработка запросов
   * @see IPage::Action()
   */
  public function Action($url, $action)
    {
    $method_name = 'on' . $action;
    //---
    if(method_exists($this, $method_name)) $this->$method_name($url);
    }

  /**
   *
   * Сохранение задачи
   * @param array $url
   */
  private function OnTranslate($url)
    {
    //--- Время работы скрипта неограниченно
    set_time_limit(0);
    //---
    CLogger::write(CLoggerType::DEBUG, "translate: start from: " . $this->m_lang_from . ', to: ' . $this->m_lang_to . ', file name: ' . $this->m_filename . ', system: ' . $this->m_translate_system);
    //---
    if(!file_exists(CModel_text::PATH_TEXTS . '/' . $this->m_filename))
      {
      $_SESSION['translated'] = -1;
      }
    //--- Работа с большими текстами
    $bigText = CModel_tools::RemoveBom(file_get_contents(CModel_text::PATH_TEXTS . '/' . $this->m_filename));
    //--- перевод
    $text = $this->m_model->Translate($bigText, $this->m_lang_from, $this->m_lang_to, $this->m_translate_system);
    //--- сохранение данных
    $new_filename = CModel_text::PATH_TEXTS . '/' . $this->m_lang_to . '_' . $this->m_filename;
    file_put_contents($new_filename, $text);
    CLogger::write(CLoggerType::DEBUG, "translate: save to file  " . $new_filename . ' ' . strlen($text) . ' bytes');
    //--- все ок
    $_SESSION['translated'] = 1;
    //---
    header("Location: ?module=translate");
    exit;
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

  /**
   *
   * Список возможных языков
   */
  public static function GetLanguages()
    {
    return self::$m_languages;
    }
  }

?>
