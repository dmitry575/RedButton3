<?php
class CNews extends IPage
  {
  private $m_settings;
  //--- переводы
  protected static $m_translate = array('ru' => array('main_title' => 'Новости',),
    //---
                                        'en' => array('main_title' => 'News',));
  //---
  private $m_list_tasks;
  private $m_model;

  /**
   * Конструктор
   */
  public function __construct()
    {
    $this->SetTitle(CTasks::GetTranslate('main_title'));
    $this->m_model = new CModel_news();
    }

  /**
   * Отображение доверя
   * @see IPage::Show()
   */
  public function Show($path = null)
    {
    global $LNG;
    $this->m_list_news = $this->m_model->GetNews();
    //---
    include("./inc/pages/news/index.phtml");
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
