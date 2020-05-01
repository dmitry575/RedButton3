<?php
//+------------------------------------------------------------------+
//|                  Copyright c 2012, RedButton Web Sites Generator |
//|                                      http://www.getredbutton.com |
//+------------------------------------------------------------------+
class CModel_ftpCurl
  {
  /**
   * Уникальный ID закачки
   * Дорген его заранее генерирут через uniqid()
   * С этим ID можно будет приостанавливать и возобновлять закачку
   * Например: 4b3403665fea6
   */
  private $uploadID;
  /**
   * Адрес FTP-сервера
   * Например: ftp.lezzvie.ru
   */
  private $ftpServer;
  /**
   * Логин к FTP-серверу
   * Например: lezzvie@lezzvie.ru или lezzvie
   */
  private $login;
  /**
   * Пароль к FTP-серверу
   * Например: sjs73olsiPs
   */
  private $password;
  /**
   * Путь к папке, содержимое которой нужно закачать по FTP
   * Дорген сначала создает весь дорвей в эту папку, а потом уже начинается медленнный
   * процесс закачки файлов. После закачки - папку очищаем.
   * Например: ../data/temp/ftp-4b3403665fea6
   */
  private $tempPath;
  /**
   * Проверять наличие файла для остановки
   * в секундах
   * @var int
   */
  private $checkStop = 10;
  /**
   * Папка которая находится на фтп
   * @var string
   */
  private $descPath = '';
  /**
   * спирсок прокси серверов HTTP
   * @var array
   */
  private $m_proxies_http;
  /**
   * список прокси серверо socks
   * @var array
   */
  private $m_proxies_socks;
  /**
   * Список юзерагентов
   * @var array
   */
  private $m_useragents;
  /**
   * тип прокси сервера http или socks
   * @var string
   */
  private $m_proxy_type;
  /**
   * После успешной закачки исходный файл нужно удалять?
   * @var bool
   */
  private $m_delete_file = true;

  /**
   *
   * Конструктор для загрузки по фтп
   * @param string $uploadID Уникальный ID закачки
   * @param string $ftpServer фтп сервер
   * @param string $login логин на фтп
   * @param string $password пароль на фтп
   * @param string $descPath удаленная папка
   * @param string $tempPath темповая папка
   * @param bool $isPassiveMode пассивный режим
   */
  public function __construct($uploadID, $ftpServer, $login, $password, $descPath = '.', $tempPath = './data/tmp/', $isPassiveMode = true)
    {
    $this->uploadID  = $uploadID;
    $this->ftpServer = $ftpServer;
    $this->login     = $login;
    $this->password  = $password;
    $this->tempPath  = $tempPath;
    //---
    $this->descPath = ltrim($descPath, '.');
    $this->descPath = ltrim($this->descPath, '/');
    //--- первоначальные настройки
    if(empty($this->tempPath) || $this->tempPath[strlen($this->tempPath) - 1] != '/') $this->tempPath .= '/';
    if(empty($this->descPath) || $this->descPath[strlen($this->descPath) - 1] != '/') $this->descPath .= '/';
    //--- нужен только хост, иначе коннект не работает, если вначале указать ftp://
    $url_info = parse_url($this->ftpServer);
    if(!empty($url_info) && !empty($url_info['host'])) $this->ftpServer = $url_info['host'];
    CLogger::write(CLoggerType::DEBUG, "FTP: upload id " . $uploadID);
    }

  /**
   * Начать закачку файлов
   * Если указан $uploadID, то значит это ручное возобновление старой закачки и все настройки
   * берем из заранее созданного файла $tempPath/.settings
   */
  public function Start($is_delete_file = true)
    {
    $this->m_delete_file = true;
    //--- при старте закачки - проверяем наличие файла .stop и удаляем его
    //--- в дальнейшем, в цикле нужно проверять наличие файла $tempPath/.stop, который создается при вызове метода Stop()
    $file_stop = $this->tempPath . '.stop_' . $this->uploadID;
    if(file_exists($file_stop)) unlink($file_stop);
    //--- нужно пробежаться по всем папкам и получить список файлов
    $list_files  = array();
    $count_files = $size_files = 0;
    $this->getListFiles($this->tempPath, '', $list_files, $count_files, $size_files);
    //---
    CLogger::write(CLoggerType::DEBUG, "files to load " . $count_files . ", size " . $size_files . " bytes");
    $starttime = microtime(TRUE);
    $pid_arr   = array();
    $this->LoadProxies();
    $this->LoadUserAgents();
    //--- запускаем закачку
    $this->SendList($list_files);
    //--- удаляем все за собой
    return $this->DeleteEmptyDirectory($this->tempPath);
    }

  /**
   * Получение списка файлов в массиве.
   * В каждом массиве должно быть файлов на одинаковый размер
   */
  private function getListFiles($fullpath, $basepath, &$paths, &$count_files, &$size_files)
    {
    if($handle = opendir($fullpath))
      {
      /* Именно этот способ чтения элементов каталога является правильным. */
      while(false !== ($fl = readdir($handle)))
        {
        if($fl == '.' || $fl == '..') continue;
        //---
        $new_f = $fullpath . $fl;
        if(is_dir($new_f))
          {
          $this->getListFiles($new_f . '/', $basepath . $fl . '/', $paths, $count_files, $size_files);
          }
        else
          {
          $size = filesize($new_f);
          $size_files += $size;
          $count_files++;
          $paths[] = new DirInfo($basepath . $fl, $size);
          }
        }
      closedir($handle);
      }
    }

  /**
   * Загрузка прокси серверов
   */
  private function LoadProxies()
    {
    $this->m_proxies_http = $this->LoadFromFile(CModel_settings::PROXY_FILE);
    //---
    if(!empty($this->m_proxies_http))
      {
      $this->m_proxies_http = array_values(array_unique($this->m_proxies_http));
      $this->m_proxy_type   = 'http';
      }
    else
      {
      $this->m_proxies_socks = $this->LoadFromFile(CModel_settings::PROXY_SOCKS_FILE);
      if(!empty($this->m_proxies_socks))
        {
        $this->m_proxies_socks = array_values(array_unique($this->m_proxies_socks));
        $this->m_proxy_type    = 'socks5';
        }
      }
    }

  /**
   * Загрузка юзер агентов
   */
  private function LoadUserAgents()
    {
    $this->m_user_agents = $this->LoadFromFile(CModel_settings::USER_AGENT_FILE);
    if(!empty($this->m_user_agents)) $this->m_user_agents = array_values(array_unique($this->m_user_agents));
    }

  /**
   * Loading info from external files
   *
   * @access private
   * @param string $filename
   * @param string $delim
   * @return array
   */
  private function loadFromFile($filename, $delim = "\n")
    {
    if(!file_exists($filename)) return array();
    $fp = fopen($filename, "r");
    if(!$fp)
      {
      //self::add_debug_msg("(!) Failed to open file: $filename");
      return array();
      }
    $size = filesize($filename);
    if($size <= 0)
      {
      return array();
      }
    $data = fread($fp, $size);
    fclose($fp);
    if(strlen($data) < 1)
      {
      //self::add_debug_msg("(!) Empty file: $filename");
      return array();
      }
    $array = explode($delim, $data);
    if(is_array($array) && count($array) > 0)
      {
      foreach($array as $k => $v)
        {
        if(strlen(trim($v)) > 0) $array[$k] = trim($v);
        }
      return $array;
      }
    else
      {
      //self::add_debug_msg("(!) Empty data array in file: $filename");
      return array();
      }
    }

  /**
   * Отправка по фтп списка файлов
   * @param array $list_files
   */
  private function SendList($list_files)
    {
    $ch = curl_init(); //инициализируем curl сессию
    curl_setopt($ch, CURLOPT_TIMEOUT, 30);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1); //непосредственно возвращаем трансфер
    curl_setopt($ch, CURLOPT_UPLOAD, 1); // подготавливаем файл к «выгрузке»
    curl_setopt($ch, CURLOPT_TRANSFERTEXT, 1); // режим ASCII для FTP
    curl_setopt($ch, CURLOPT_FTPAPPEND, TRUE);
    curl_setopt($ch, CURLOPT_HEADER, TRUE);
    curl_setopt($ch, CURLOPT_FTP_CREATE_MISSING_DIRS, TRUE); // создание папки на фтп, если ее не существует
    curl_setopt($ch, CURLOPT_BINARYTRANSFER, true);
    //--- установим прокси для закачки
    if($this->m_proxy_type == 'http')
      {
      $n     = count($this->m_proxies_http);
      $proxy = $this->m_proxies_http[mt_rand(0, $n - 1)];
      $auth  = false;
      //--- нашли логин и пароль попытаемся авторизоваться
      if(($pos = strpos($proxy, '@')) !== FALSE)
        {
        //--- установим авторизацию
        curl_setopt($ch, CURLOPT_PROXYUSERPWD, substr($proxy, $pos + 1));
        curl_setopt($ch, CURLOPT_PROXY, substr($proxy, 0, $pos));
        }
      else curl_setopt($ch, CURLOPT_PROXY, $proxy);
      CLogger::write(CLoggerType::DEBUG, 'using proxy HTTP ' . $proxy . ($auth ? ', auth turn on' : ''));
      }
    elseif($this->m_proxy_type == 'socks5')
      {
      $n     = count($this->m_proxies_socks);
      $proxy = $this->m_proxies_socks[mt_rand(0, $n - 1)];
      curl_setopt($ch, CURLOPT_PROXY, $proxy);
      curl_setopt($ch, CURLOPT_PROXYTYPE, CURLPROXY_SOCKS5);
      //---
      CLogger::write(CLoggerType::DEBUG, 'using proxy SOCKS5 ' . $proxy);
      }
    //--- установим юрезагента
    if(!empty($this->m_useragents))
      {
      $n = count($this->m_useragents);
      curl_setopt($ch, CURLOPT_USERAGENT, $this->m_useragents[mt_rand(0, $n - 1)]);
      }
    //--- логин и пароль к закачке по фтп
    curl_setopt($ch, CURLOPT_USERPWD, str_replace(':', '\\:', $this->login) . ':' . str_replace(':', '\\:', $this->password));
    //--- пытаем закачать файл
    $last_time_stop = time();
    //---
    CModel_helper::PrintInfo('FTP: загружаем файлы на сервер...', true);
    //---
    foreach($list_files as $file_info)
      {
      //--- проверка текущей папки
      $file_name = $this->tempPath . $file_info->Name;
      //--- удаленное имя файла
      $dst_file_name = $this->descPath . $file_info->Name;
      //--- отправка файла на фтп сервере
      CLogger::write(CLoggerType::DEBUG, 'try file copy from ' . $file_name . ' to ' . $dst_file_name . ' on ftp (' . $file_info->Name . ')');
      //--- искомый URL(ftp)
      $url = 'ftp://' . $this->ftpServer . '/' . $dst_file_name;
      //---
      curl_setopt($ch, CURLOPT_URL, $url);
      //---
      $fp = fopen($file_name, 'r'); //открываем файл
      //---
      curl_setopt($ch, CURLOPT_INFILE, $fp);
      curl_setopt($ch, CURLOPT_INFILESIZE, filesize($file_name));
      //--- поехали
      curl_exec($ch);
      //---
      fclose($fp);
      $error_no = curl_errno($ch);
      if($error_no == 0)
        {
        CLogger::write(CLoggerType::DEBUG, 'file uploaded ' . $file_name . ' ' . $url . ' success');
        //--- исходный файл удалим
        if($this->m_delete_file)
          {
          if(unlink($file_name)) CLogger::write(CLoggerType::DEBUG, 'file deleted ' . $file_name);
          }
        }
      else
        {
        CLogger::write(CLoggerType::ERROR, 'failed upload file ' . $file_name . ' ' . $url . ' error:[' . $error_no . '] ' . curl_error($ch));
        //--- если нет коннекта, то выходим
        if($error_no == 7)
          {
          curl_close($ch);
          CModel_helper::PrintInfo('FTP: upload finished, failed. Connect to host error', true);
          CLogger::write(CLoggerType::ERROR, 'ftp: upload finished, failed. Connect to host error');
          return;
          }
        }
      //--- нужно ли проверять наличие файла
      if((time() - $last_time_stop) > $this->checkStop)
        {
        if($this->IsStop())
          {
          CLogger::write(CLoggerType::DEBUG, "ftp connection closed, find stop file");
          //--- закрытие фтп коннекта
          return;
          }
        $last_time_stop = time();
        }
      }
    curl_close($ch);
    //---
    CModel_helper::PrintInfo('FTP: upload finished', true);
    CLogger::write(CLoggerType::DEBUG, "ftp connection closed");
    }

  /**
   * Удаление всех пустых папок
   */
  private function DeleteEmptyDirectory($dir)
    {
    CTools_files::GetAllFiles($dir, $list);
    if(empty($list))
      {
      if(CTools_files::DeleteAll($dir))
        {
        CLogger::write(CLoggerType::DEBUG, "ftp: path deleted " . $dir);
        return true;
        }
      }
    else
      {
      CLogger::write(CLoggerType::DEBUG, "ftp: path not empty " . $dir);
      }
    //---
    return false;
    }

  /**
   * Принудительная остановка закачки файлов
   * При остановке создается файл $tempPath/.stop, наличие которого потом
   * будет проверяться в цикле при закачке каждого файла
   */
  public function Stop()
    {
    $file_stop = $this->tempPath . '.stop_' . $this->uploadID;
    file_put_contents($file_stop, "stop");
    }

  /**
   * Нужно ли прекращать закачку файлов
   */
  private function IsStop()
    {
    $file_stop = $this->tempPath . '.stop_' . $this->uploadID;
    return file_exists($file_stop);
    }
  }
class DirInfo
  {
  public $Name;
  public $Size;

  public function __construct($name, $size)
    {
    $this->Name = $name;
    $this->Size = $size;
    }
  }

?>