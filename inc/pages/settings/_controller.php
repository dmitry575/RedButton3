<?php
class CSettings extends IPage
  {
  /**
   * CModel_settings
   * @var CModel_settings
   */
  private $m_settings;
  //--- переводы
  protected static $m_translate = array('ru' => array('main'                    => 'Настройки',
                                                      'b_proxies'               => 'Список HTTP прокси серверов',
                                                      'b_socks_proxies'         => 'Список SOCKS5 прокси серверов',
                                                      'b_useragents'            => 'Список user agent',
                                                      'b_menu'                  => 'Формат вывода строки меню для макроса [MENU-CATEGORY]',
                                                      'b_links'                 => 'Формат вывода строки для макроса [RAND-UC-LINK-0-9]',
                                                      'b_tags'                  => 'Формат вывода строки для макроса [TAGS]',
                                                      'b_sitemap'               => 'Формат вывода строки для Sitemap',
                                                      'b_save'                  => 'Сохранить',
                                                      'b_ip_searchers'          => 'IP поисковиков (google,yandex,bing) для клоакинга',
                                                      'b_settings_save_success' => 'Настройки успешно сохранены',
                                                      'b_settings_save_error'   => 'При сохранении возникла ошибка',
                                                      'b_subcategory_links'     => 'Формат вывода ссылок для макроса [CATEGORY-LINKS], [CATEGORY-RAND-LINKS-0-9]',
                                                      'b_links_numbers'         => 'Формат вывода ссылок для макросов [NEXT-UC-LINK-10], [PREV-UC-LINK-10] и т.д.'),
//---
                                        'en' => array('main'                    => 'Settings',
                                                      'b_proxies'               => 'List HTTP proxies',
                                                      'b_socks_proxies'         => 'List SOCKS5 proxies',
                                                      'b_useragents'            => 'List of user agents',
                                                      'b_menu'                  => 'Format for item of macros [MENU-CATEGORY]',
                                                      'b_links'                 => 'Format for item of macros [RAND-UC-LINK-0-9]',
                                                      'b_tags'                  => 'Format for item of macros [TAGS]',
                                                      'b_sitemap'               => 'Format for item of Sitemaps',
                                                      'b_save'                  => 'Save',
                                                      'b_ip_searchers'          => 'IP searchers (google,yandex,bing) for cloaking',
                                                      'b_settings_save_success' => 'Settings saved',
                                                      'b_settings_save_error'   => 'Saving settings failed',
                                                      'b_subcategory_links'     => 'Format for item of macros [CATEGORY-LINKS], [CATEGORY-RAND-LINKS-0-9]',
                                                      'b_links_numbers'         => 'Format for item of macros [NEXT-UC-LINK-10], [PREV-UC-LINK-10] etc'));

  /**
   * Конструктор
   *
   */
  public function __construct()
    {
    $this->m_settings = new CModel_settings();
    $this->SetTitle(CHome::GetTranslate('main'));
    }

  /**
   * Отображение админки доргена
   * @see IPage::Show()
   */
  public function Show($path = null)
    {
    //---
    include("./inc/pages/settings/index.phtml");
    }

  /**
   * @param array $url
   * @param string $action
   */
  public function Action($url, $action)
    {
    $method_name = 'on' . $action;
    //---
    if(method_exists($this, $method_name)) $this->$method_name($url);
    }

  /**
   * Сохранение данных
   * @param $url
   */
  public function OnSave($url)
    {
    $this->m_settings->SaveProxies($_POST['proxies']);
    $this->m_settings->SaveSocksProxies($_POST['socks_proxies']);
    $this->m_settings->SaveUseragents($_POST['useragents']);
    //---
    unset($_POST['proxies']);
    unset($_POST['useragents']);
    //---
    $_POST['ip_searchers'] = $this->m_settings->GetIPSearchersFromForm($_POST['ip_searchers']);
    $this->m_settings->SaveGlobalSetting($_POST);
    CLogger::write(CLoggerType::DEBUG, "global settings saved");
    //---
    $_SESSION['settings_savesettings'] = 1;
    //---
    header("location: ?module=settings");
    //---
    exit;
    }

  /**
   *
   * ПОлучение настроек
   */
  private function GetSettings()
    {
    return $this->m_settings;
    }

  /**
   * Получение перевода
   *
   * @param string $name
   * @return string
   */
  public static function GetTranslate($name)
    {
    global $LNG;
    //---
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
