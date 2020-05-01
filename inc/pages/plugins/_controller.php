<?php
class CPlugins extends IPage
  {
  //--- переводы
  protected static $m_translate = array('ru' => array('main_title'     => 'Плагины',
                                                      'b_save'         => 'Сохранить',
                                                      'activate'       => 'Включить',
                                                      'deactivate'     => 'Отключить',
                                                      'b_save_success' => 'Успешно сохранено',
                                                      'number'         => 'Номер',
                                                      'name'           => 'Название',
                                                      'author'         => 'Автор',
                                                      'description'    => 'Описание',
                                                      'action'         => 'Вкл/откл',),
    //---
                                        'en' => array('main_title'     => 'Plugins',
                                                      'b_save'         => 'Save',
                                                      'activate'       => 'Activate',
                                                      'deactivate'     => 'Deactivate',
                                                      'b_save_success' => 'Save success',
                                                      'number'         => 'Number',
                                                      'name'           => 'Name',
                                                      'author'         => 'Author',
                                                      'description'    => 'Description',
                                                      'action'         => 'On/off',));
  //---
  private $m_model;
  private $m_list_modules;

  /**
   * Конструктор
   */
  public function __construct()
    {
    $this->SetTitle(CPlugins::GetTranslate('main_title'));
    $this->m_model = new CModel_plugins();
    }

  /**
   * Отображение доверя
   * @see IPage::Show()
   */
  public function Show($path = null)
    {
    global $GLOBAL_ACTIVE_PLUGINS;
    //---
    $this->m_list_modules = $this->m_model->GetList();
    //---
    include("./inc/pages/plugins/index.phtml");
    }

  /**
   *
   * Список задач
   */
  public function GetListPlugins()
    {
    return $this->m_list_modules;
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
   * Активировать плагин
   * @param array $url
   */
  public function OnActivate($url)
    {
    $name = $_REQUEST['name'];
    $this->m_model->Activate($name);
    CLogger::write(CLoggerType::DEBUG, 'activate plugin: ' . $name);
    header('Location: ?module=plugins&save=1');
    }

  /**
   *
   * Отключить плагин
   * @param array $url
   */
  public function OnDeActivate($url)
    {
    $name = $_REQUEST['name'];
    $this->m_model->DeActivate($name);
    CLogger::write(CLoggerType::DEBUG, 'deactivate plugin: ' . $name);
    header('Location: ?module=plugins&save=1');
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
