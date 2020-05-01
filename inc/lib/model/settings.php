<?php
if(!file_exists("data/settings_list.php"))
  {
  file_put_contents("data/settings_list.php", '<?php
   global $CONFIG_LIST_SETTINGS;
   $CONFIG_LIST_SETTINGS = array();
?>');
  }
include_once("data/settings_list.php");
/**
 *
 * Класс для работы с настройками
 * @author User
 *
 */
class CModel_settings
  {
  /**
   * массив с настройками
   * @var array
   */
  private $settingsArray = array();
  /**
   * глобальные настройки
   * @var array
   */
  private $m_settings_global = array();
  /**
   *
   * Папка где храняться настройки
   * @var string
   */
  const SETTINGS_PATH = 'data/settings/';
  /**
   *
   * Файл где храняться последние использованные настройки
   * @var string
   */
  const LAST_SET_FILENAME = '_last_settings.data.php';
  /**
   *
   * Файл где храняться глобальные настройки
   * @var string
   */
  const GLOBAL_FILENAME = '_global_settings.data.php';
  /**
   * Файл со списком прокси серверов
   */
  const PROXY_FILE = 'data/networks/proxies.txt';
  /**
   * Файл со списком прокси серверов
   */
  const PROXY_SOCKS_FILE = 'data/networks/proxies_socks.txt';
  /**
   * Файл со списком юзер агентов
   */
  const USER_AGENT_FILE = 'data/networks/useragents.txt';
  /**
   * список со стоп словами
   * @var array
   */
  private static $m_stop_words = array('_last_settings',
                                       '_global_settings');

  /**
   * Инициализация
   */
  public function __construct()
    {
    global $CONFIG_LIST_SETTINGS;
    //--- загружаем настройки
    $this->settingsArray = $this->Load('default');
    }

  /**
   * Установка массива настроек
   * @param array $settingsArray
   */
  public function SetSettingsArray($settingsArray)
    {
    $this->settingsArray = $settingsArray;
    }

  /**
   * Получение настроек
   * @param <string> $param Название параметра
   */
  public function Get($param, $default = NULL)
    {
    if(empty($param)) return NULL;
    //---
    if(!is_array($this->settingsArray))
      {
      $this->settingsArray = array();
      CLogger::write(CLoggerType::DEBUG, 'settings: empty settings data');
      }
    //--- возвращаем значение ключа, если оно есть в массиве
    return array_key_exists($param, $this->settingsArray) ? $this->settingsArray[$param] : $default;
    }

  /**
   * Сохранение настроек из POST-запроса
   * @param $settings
   * @param string $name
   * @param string $newname
   * @return bool|mixed|string
   */
  public function Save($settings, $name = 'default', $newname = '')
    {
    global $CONFIG_LIST_SETTINGS;
    //--- обработаем некоторые строчки
    if(!empty($settings['goLinkHtml'])) $settings['goLinkHtml'] = stripslashes($settings['goLinkHtml']);
    if(!empty($settings['linksGo'])) $settings['linksGo'] = stripslashes($settings['linksGo']);
    if(!empty($settings['metaDescription'])) $settings['metaDescription'] = stripslashes($settings['metaDescription']);
    if(!empty($settings['pageTitle'])) $settings['pageTitle'] = stripslashes($settings['pageTitle']);
    //---
    $name = trim(strtolower($name));
    if(empty($name)) return false;
    //--- проверка папки
    if(!file_exists(self::SETTINGS_PATH)) mkdir(self::SETTINGS_PATH, 0777, true);
    //--- сохраняем сериализованные настройки
    if(!empty($newname))
      {
      $fname                           = CModel_helper::generate_file_name(CModel_tools::Translit($newname));
      $this->settingsArray['settings'] = $newname;
      }
    else
      {
      $fname = CModel_helper::generate_file_name(CModel_tools::Translit($name));
      }
    if(in_array($fname, self::$m_stop_words)) $fname .= '_1';
    //--- сериализуем настройки
    $this->settingsArray = serialize($settings);
    //---
    file_put_contents(self::SETTINGS_PATH . $fname . ".data.php", $this->settingsArray);
    //---
    if(!empty($newname))
      {
      $CONFIG_LIST_SETTINGS[$fname] = $newname;
      $this->SaveConfigList($CONFIG_LIST_SETTINGS);
      }
    CLogger::write(CLoggerType::DEBUG, 'settings save to file ' . self::SETTINGS_PATH . $fname . ".data.php, name: " . $name);
    //---
    return $fname;
    }

  /**
   * Удаление настроеек
   * @param $name
   */
  public function DeleteSettings($name)
    {
    $filename = self::SETTINGS_PATH . $name . ".data.php";
    if(file_exists($filename))
      {
      unlink($filename);
      CLogger::write(CLoggerType::DEBUG, 'settings file deleted' . $filename);
      }
    }

  /**
   *
   * Сохранение текущей настройки
   * @param string $filename
   */
  public function SaveCurrentSettings($filename)
    {
    //--- проверка папки
    if(!file_exists(self::SETTINGS_PATH)) mkdir(self::SETTINGS_PATH, 0777, true);
    //---
    file_put_contents(self::SETTINGS_PATH . self::LAST_SET_FILENAME, $filename);
    }

  /**
   *
   * Сохранение текущей настройки
   * @param string $filename
   */
  public function LoadCurrentSettings()
    {
    //---
    $filename = self::SETTINGS_PATH . self::LAST_SET_FILENAME;
    if(!file_exists($filename)) return '';
    //---
    return file_get_contents($filename);
    }

  /**
   *
   * Сохраним данные для конфига
   * @param array $list
   */
  private function SaveConfigList($list)
    {
    $data = '<?php
      global $CONFIG_LIST_SETTINGS;
      $CONFIG_LIST_SETTINGS = array(';
    foreach($list as $id => $name)
      {
      $data .= "'" . $id . "'=>'" . $name . "',\r\n";
      }
    $data .= ');
      ?>';
    file_put_contents("data/settings_list.php", $data);
    }

  /**
   * Загрузка настроек
   * @param <string> $name Имя файла с настройками
   * @return <array> Массив с настройками
   */
  public function Load($name = '')
    {
    global $CONFIG_LIST_SETTINGS;
    if(empty($name)) $name = 'default';
    //---
    if(file_exists(self::SETTINGS_PATH . $name . ".data.php"))
      {
      $c = file_get_contents(self::SETTINGS_PATH . $name . ".data.php");
      return unserialize($c);
      }
    return array();
    }

  /**
   * Возвращаем все настройки
   * @return array
   */
  public function GetParams()
    {
    return $this->settingsArray;
    }

  /**
   * Замена в новым настройках основных параметров о домене и количестве слов, фтп данные и т.д.
   * @param $old_settings
   * @param $settings
   */
  public function ChangeMainSettingsFrom($old_settings, &$settings)
    {
    $settings['keysFromList']  = $old_settings['keysFromList'];
    $settings['keysRandom']    = $old_settings['keysRandom'];
    $settings['keysRandomMin'] = $old_settings['keysRandomMin'];
    $settings['keysRandomMax'] = $old_settings['keysRandomMax'];
    $settings['nextUrl']       = $old_settings['nextUrl'];
    $settings['localPath']     = $old_settings['localPath'];
    if(isset($old_settings['archive_zip'])) $settings['archive_zip'] = $old_settings['archive_zip'];
    if(isset($old_settings['un_archive_zip'])) $settings['un_archive_zip'] = $old_settings['un_archive_zip'];
    if(isset($old_settings['onepage_create'])) $settings['onepage_create'] = $old_settings['onepage_create'];
    $settings['ftpServer'] = $old_settings['ftpServer'];
    if(isset($old_settings['ftpAdvanced'])) $settings['ftpAdvanced'] = $old_settings['ftpAdvanced'];
    $settings['ftpMode']     = $old_settings['ftpMode'];
    $settings['ftpLogin']    = $old_settings['ftpLogin'];
    $settings['ftpPassword'] = $old_settings['ftpPassword'];
    $settings['ftpPath']     = $old_settings['ftpPath'];
    }

  /**
   *
   * Получение данных об установленых настройках
   */
  public function GetListConfigs()
    {
    global $CONFIG_LIST_SETTINGS;
    //---
    return $CONFIG_LIST_SETTINGS;
    }

  /**
   *
   * Получение данных об установленых настройках
   */
  public function GetListConfigsCheck()
    {
    global $CONFIG_LIST_SETTINGS;
    //---
    $files = CModel_helper::ListFilesArray(self::SETTINGS_PATH, null);
    //---
    if(empty($files)) return array();
    foreach($CONFIG_LIST_SETTINGS as $name => $val)
      {
      $fname = $name . '.data.php';
      if(!in_array($fname, $files)) unset($CONFIG_LIST_SETTINGS[$name]);
      }
    //--- сортировка
    natsort($CONFIG_LIST_SETTINGS);
    //---
    return $CONFIG_LIST_SETTINGS;
    }

  /**
   * Получение прокси
   * @return string
   */
  public function GetProxies()
    {
    if(file_exists(self::PROXY_FILE)) return file_get_contents(self::PROXY_FILE);
    }

  /**
   * Получение прокси
   * @return string
   */
  public function GetSocksProxies()
    {
    if(file_exists(self::PROXY_SOCKS_FILE)) return file_get_contents(self::PROXY_SOCKS_FILE);
    }

  /**
   * Получение юзер агентов
   * @return string
   */
  public function GetUseragents()
    {
    if(file_exists(self::USER_AGENT_FILE)) return file_get_contents(self::USER_AGENT_FILE);
    }

  /**
   * Сохранение прокси
   * @return string
   */
  public function SaveProxies($proxies)
    {
    return file_put_contents(self::PROXY_FILE, $proxies);
    }

  /**
   * Сохранение socks прокси
   * @return string
   */
  public function SaveSocksProxies($proxies)
    {
    return file_put_contents(self::PROXY_SOCKS_FILE, $proxies);
    }

  /**
   * Получение юзер агентов
   * @return string
   */
  public function SaveUseragents($useragents)
    {
    return file_put_contents(self::USER_AGENT_FILE, $useragents);
    }

  /**
   * Получение глобальных настроек
   * @param $name
   * @param string $default_value
   * @return string
   */
  public function GetGlobal($name, $default_value = '')
    {
    if(empty($this->m_global_settings)) $this->LoadGlobals();
//---
    if(isset($this->m_global_settings[$name])) return $this->m_global_settings[$name];
    return $default_value;
    }

  /**
   * Загрузка глобальных настроек
   */
  public function LoadGlobals()
    {
    $filename = self::SETTINGS_PATH . self::GLOBAL_FILENAME;
    if(file_exists($filename)) $this->m_global_settings = unserialize(file_get_contents($filename));
    }

  /**
   * Сохранение глобальлных настроек
   * @param array $data
   * @return int
   */
  public function SaveGlobalSetting($data)
    {
    if(!file_exists(self::SETTINGS_PATH)) mkdir(self::SETTINGS_PATH);
    //---
    $filename = self::SETTINGS_PATH . self::GLOBAL_FILENAME;
    return file_put_contents($filename, serialize($data));
    }

  /**
   * Из массива получаем в стоку айпи адреса поисковиков
   * @param array $address
   * @return string
   */
  public function GetIpSearchersStr($address)
    {
    if(!is_array($address)) return '';
    //---
    $ret = '';
    foreach($address as $ip)
      {
      if(empty($ip[0]) || empty($ip[1])) continue;
      //---
      $ret .= long2ip($ip[0]) . ($ip[0] != $ip[1] ? '-' . long2ip($ip[1]) : '') . "\r\n";
      }
    return $ret;
    }

  /**
   * из данных формы получим правильный массив айпи адресов
   * @param string $address
   * @return array
   */
  public function GetIPSearchersFromForm($address)
    {
    if(empty($address)) return null;
    //---
    $ar     = explode("\n", $address);
    $result = array();
    foreach($ar as $addr)
      {
      $ip = trim($addr);
      if(empty($ip)) continue;
      //--- проверим задан промежуток или один айпи адрес
      if(strpos($ip, '-') !== false)
        {
        //--- получим оба айпи адреса
        $ip_range    = explode('-', $ip, 2);
        $ip_range[0] = trim($ip_range[0]);
        $ip_range[1] = trim($ip_range[1]);
        if(CModel_tools::ValidateIP($ip_range[0]) && CModel_tools::ValidateIP($ip_range[1])) $result[] = array(ip2long($ip_range[0]),
                                                                                                               ip2long($ip_range[1]));
        }
      //--- диапозон айпи адресов в формате 77.88.0.0/18
      else if(strpos($ip, '/') !== false)
        {
        $ip_range = CModel_tools::CidrToRange($ip);
        if(!empty($ip_range) && count($ip_range) == 2) $result[] = $ip_range;
        }
      else
        {
        //--- проверим ip адрес
        if(CModel_tools::ValidateIP($ip))
          {
          //if(ip2long($ip) === false) var_dump($ip);
          $result[] = array(ip2long($ip),
                            ip2long($ip));
          }
        }
      }
    //---
    return $result;
    }
  }

?>