<?
/**
 * Класс для пингации
 * Class CModel_pinger
 */
class CModel_pinger
  {
  /**
   * основной путь к данным для пингера
   */
  const PATH = "data/pingers/";
  /**
   * Путь где лежат задачи другие данные
   */
  const TASK_PATH_XML = 'data/pingers/task/xmlrpc/';
  /**
   * Путь где лежат задачи другие данные
   */
  const TASK_PATH = 'data/pingers/task/pings/';
  /**
   * куда сохраняем список серверов
   */
  const FILE_SERVERS = "servers.dat.php";
  /**
   * куда сохраняем список серверов
   */
  const FILE_XMLRPC_SERVERS = "servers_xmlrpc.dat.php";
  /**
   * максимальное количество потоков
   */
  const MAX_THREADS = 100;
  /**
   * префикс для логов
   */
  const PREFIX_LOG = "pinger: ";
  /**
   * Запрос на ping back
   */
  const PING_BACK_RPC_REQUEST = "<?xml version=\"1.0\" encoding=\"utf-8\"?>
<methodCall>
<methodName>pingback.ping</methodName>
<params>
 <param>
  <value>
   <string>{url}</string>
  </value>
 </param>
 <param>
  <value>
   <string>{page}</string>
  </value>
 </param>
</params>
</methodCall>
";
  /**
   * Запрос на ping обновление блога
   */
  const PING_RPC_REQUEST = "<?xml version=\"1.0\"?>
<methodCall>
<methodName>weblogUpdates.ping</methodName>
<params>
 <param>
  <value>
   <string>{title}</string>
  </value>
 </param>
 <param>
  <value>
   <string>{url}</string>
  </value>
 </param>
</params>
</methodCall>
";
  /**
   * Запрос на ping обновление блога
   */
  const PING_RPC_REQUEST_EXTENDED = "<?xml version=\"1.0\"?>
<methodCall>
<methodName>weblogUpdates.extendedPing</methodName>
<params>
<param><value><string>{title}</string></value></param>
<param><value><string>{url}</string></value></param>
<param><value><string>{url_post}</string></value></param>
<param><value><string>{url_rss}</string></value></param>
</params>
</methodCall>

";
  /*
   * начало работы над задачей
   */
  const STATUS_BEGIN = 1;
  const STATUS_START = 2;
  /**
   * окончание работы над задачей
   */
  const STATUS_FINISH = 3;
  /**
   * имя файла, что выполнение началось
   * @var string
   */
  private $m_start_file = "start.php";
  /**
   * имя файла, что выполнение задач нужно прервать
   * @var string
   */
  private $m_stop_file = "stop.php";
  /**
   * инцилизация модуля прошла
   * @var bool
   */
  private $m_init = false;
  /**
   * список серверов загружается из файла
   * @var array
   */
  private $m_servers = array();
  /**
   * список серверов загружается из файла
   * @var array
   */
  private $m_xmlrpc_servers = array();
  /**
   * сохраняем на какие урлы будем отправлять запросы
   * @var array
   */
  private $m_urls_send;
  /**
   * сохраняем на какие урлы будем отправлять запросы для серверов XML-RPC
   * @var array
   */
  private $m_xmlrpc_urls_send;
  /**
   * Список настроек
   * @var array
   */
  private $m_settingsArray;

  /**
   * Контструктор
   */
  public function __contructor()
    {
    }

  /**
   * Инцилизация данных, загрузка всего в память
   * @return bool
   */
  public function Init()
    {
    $this->m_init = true;
    $this->LoadServices();
    $this->LoadXmlrpcServices();
    $this->LoadSettings();
    //---
    CLogger::write(CLoggerType::DEBUG, 'pinger: all servers loaded: ' . count($this->m_servers) . ', xmlrpc: ' . count($this->m_xmlrpc_servers));
    return true;
    }

  /**
   * Загрузка всех сервисов
   * @return bool
   */
  private function LoadServices()
    {
    $filename = self::PATH . self::FILE_SERVERS;
    if(!file_exists($filename)) return false;
    //---
    $content = file_get_contents($filename);
    if(empty($content)) return false;
    //---
    $arr = explode("\n", $content);
    foreach($arr as $line)
      {
      $l = trim($line);
      if(empty($l)) continue;
      //---
      $a                      = explode("|", $l);
      $this->m_servers[$a[0]] = array('date_create'       => isset($a[1]) ? $a[1] : 0,
                                      'date_last'         => isset($a[2]) ? $a[2] : 0,
                                      'invalid_40x'       => isset($a[3]) ? $a[3] : 0,
                                      'invalid_50x'       => isset($a[4]) ? $a[4] : 0,
                                      'date_last_success' => isset($a[5]) ? $a[5] : 0,);
      }
    }

  /**
   * Загрузка xml-rpc сервисов
   * @return bool
   */
  private function LoadXmlrpcServices()
    {
    $filename = self::PATH . self::FILE_XMLRPC_SERVERS;
    if(!file_exists($filename)) return false;
//---
    $content = file_get_contents($filename);
    if(empty($content)) return false;
//---
    $arr = explode("\n", $content);
    foreach($arr as $line)
      {
      $l = trim($line);
      if(empty($l)) continue;
      //---
      $a                             = explode("|", $l);
      $this->m_xmlrpc_servers[$a[0]] = array('date_create' => isset($a[1]) ? $a[1] : 0,
                                             'date_last'   => isset($a[2]) ? $a[2] : 0,
                                             'invalid_40x' => isset($a[3]) ? $a[3] : 0,
                                             'invalid_50x' => isset($a[4]) ? $a[4] : 0,);
      }
    }

  /**
   * Получение списка серверов
   * @return array
   */
  public function GetServers()
    {
    return $this->m_servers;
    }

  /**
   * Получение RPC списка серверов
   * @return array
   */
  public function GetXmlrpcServers()
    {
    return $this->m_xmlrpc_servers;
    }

  /**
   * Сохранение обычных get сервисов
   */
  private function SaveServers()
    {
    if(!$this->m_init) return false;
    //--- проверим папку
    $this->CheckPath();
    $filename = self::PATH . self::FILE_SERVERS;
//---
    $content = '';
    $i       = 0;
    foreach($this->m_servers as $url => $info)
      {
      $this->ClearData($url);
      if(empty($url)) continue;
      $content .= $url . '|' . $info['date_create'] . '|' . $info['date_last'] . '|' . $info['invalid_40x'] . '|' . $info['invalid_50x'] . '|' . (isset($info['date_last_success']) ? $info['date_last_success'] : 0) . "\r\n";
      $i++;
      }
    file_put_contents($filename, $content);
    CLogger::write(CLoggerType::DEBUG, 'pinger: save servers to file: ' . $filename . ', count: ' . $i . ', size: ' . strlen($content) . ' bytes');
    }

  /**
   * СОхранение xmlrpc сервисов
   */
  private function SaveXmlrpcServers()
    {
    if(!$this->m_init) return false;
    //--- проверим папку
    $this->CheckPath();
    $filename = self::PATH . self::FILE_XMLRPC_SERVERS;
//---
    $content = '';
    $i       = 0;
    foreach($this->m_xmlrpc_servers as $url => $info)
      {
      $this->ClearData($url);
      if(empty($url)) continue;
      $content .= $url . '|' . $info['date_create'] . '|' . $info['date_last'] . '|' . $info['invalid_40x'] . '|' . $info['invalid_50x'] . '|' . (isset($info['date_last_success']) ? $info['date_last_success'] : 0) . "\r\n";
      $i++;
      }
    //---
    file_put_contents($filename, $content);
    CLogger::write(CLoggerType::DEBUG, 'pinger: save xml-rpc servers to file: ' . $filename . ', count: ' . $i . ', size: ' . strlen($content) . ' bytes');
    }

  /**
   * Зачистка данных
   * @param $url
   */
  private function ClearData(&$url)
    {
    $url = str_replace('|', '', $url);
    }

  /**
   * Обновление данных по урлу
   * @param string $url
   * @param array $info
   */
  public function Update($url, $info)
    {
    //CLogger::write(CLoggerType::DEBUG, 'pinger: update: ' . $url . ', data: ' . var_export($info, true));
    $this->m_servers[$url] = $info;
    }

  /**
   * Обновление данных по урлу
   * @param string $url
   * @param array $info
   */
  public function UpdateXmlrpc($url, $info)
    {
    //CLogger::write(CLoggerType::DEBUG, 'pinger: update: ' . $url . ', data: ' . var_export($info, true));
    $this->m_xmlrpc_servers[$url] = $info;
    }

  /**
   * удаление данных по урлу, только из кеша
   * @param string $url
   * @return bool
   */
  public function Delete($url)
    {
    if(isset($this->m_servers[$url]))
      {
      unset($this->m_servers[$url]);
      return true;
      }
    return false;
    }

  /**
   * удаление данных по урлу, только из кеша
   * @param string $url
   * @return bool
   */
  public function DeleteXmlrpc($url)
    {
    if(isset($this->m_xmlrpc_servers[$url]))
      {
      unset($this->m_xmlrpc_servers[$url]);
      return true;
      }
    return false;
    }

  /**
   * удаление данных по урлу, только из кеша
   * @param string $url
   * @return bool
   */
  public function DeleteMany($urls)
    {
    if(empty($urls)) return false;
    foreach(explode(',', $urls) as $url)
      {
      if($this->Delete(trim($url))) CLogger::write(CLoggerType::DEBUG, CModel_Pinger::PREFIX_LOG . " server deleted: " . $url);
      }
    $this->SaveServers();
    return true;
    }

  /**
   * удаление данных по урлу, только из кеша
   * @param string $url
   * @return bool
   */
  public function DeleteXmlrpcMany($urls)
    {
    if(empty($urls)) return false;
    foreach(explode(',', $urls) as $url)
      {
      if($this->DeleteXmlrpc(trim($url)))
        {
        CLogger::write(CLoggerType::DEBUG, CModel_Pinger::PREFIX_LOG . " XML-RPC server deleted: " . $url);
        }
      }
    $this->SaveXmlrpcServers();
    return true;
    }

  /**
   * Множественное добавление урлов
   * @param string $urls
   * @return bool
   */
  public function AddMany($urls, $xmlrpc_urls)
    {
    if(empty($urls) && empty($xmlrpc_urls)) return false;
    if(!empty($urls))
      {
      $ar = explode("\n", $urls);
//---
      foreach($ar as $url)
        {
        $this->AddUrl($url);
        }
      //---
      $this->SaveServers();
      }
    //--- xml rpc
    if(!empty($xmlrpc_urls))
      {
      $ar = explode("\n", $xmlrpc_urls);
      //---
      foreach($ar as $url)
        {
        $u = trim($url);
        if(empty($url)) continue;
        if(!CModel_helper::IsExistHttp($u)) $u = "http://" . $u;
        $this->AddXmlrpcUrl($u);
        }
      //---
      $this->SaveXmlrpcServers();
      }
    return true;
    }

  /**
   * Добавление одного урла
   * @param string $urls
   * @return bool
   */
  public function AddUrl($url)
    {
    $u = trim($url);
    if(empty($u)) return false;
//--- проверим существование такого сервера
    if(!empty($this->m_servers[$u])) return false;
    $info = array('date_create' => time(),
                  'date_last'   => 0,
                  'invalid_40x' => 0,
                  'invalid_50x' => 0);
    $this->Update($u, $info);
    return true;
    }

  /**
   * Добавление одного урла
   * @param string $urls
   * @return bool
   */
  public function AddXmlrpcUrl($url)
    {
    $u = trim($url);
    if(empty($u)) return false;
//--- проверим существование такого сервера
    if(!empty($this->m_xmlrpc_servers[$u])) return false;
    $info = array('date_create' => time(),
                  'date_last'   => 0,
                  'invalid_40x' => 0,
                  'invalid_50x' => 0);
    $this->UpdateXmlrpc($u, $info);
    return true;
    }

  /**
   * Начать отправлять задачу
   * @param $task
   * @return bool
   */
  public function SendTaskPings($task)
    {
    if(empty($task))
      {
      CLogger::write(CLoggerType::ERROR, 'pinger: not send, task is empty ');
      return false;
      }
    return $this->SendPings($task['task']);
    }

  /**
   * Начать отправлять задачу
   * @param $task
   * @return bool
   */
  public function SendTaskXML($task)
    {
    if(empty($task))
      {
      CLogger::write(CLoggerType::ERROR, 'pinger: not send, task is empty ');
      return false;
      }
    return $this->SendXML($task['task']);
    }

  /**
   * загрузка прокси серверов
   * @param AngryCurl $curl
   */
  private function LoadProxies($curl)
    {
//--- load proxy
    if(file_exists(CModel_settings::PROXY_SOCKS_FILE) && filesize(CModel_settings::PROXY_SOCKS_FILE) > 5)
      {
      CLogger::write(CLoggerType::DEBUG, 'pinger: socks proxies loading');
      $curl->load_proxy_list(CModel_settings::PROXY_SOCKS_FILE, # optional: number of threads
        100, # optional: proxy type
        'socks5', # optional: target url to check
        'http://google.com', # optional: target regexp to check
        'title>G[o]{2}gle');
      CLogger::write(CLoggerType::DEBUG, 'pinger: socks proxies loaded ' . $curl->GetCountProxy());
      }
    elseif(file_exists(CModel_settings::PROXY_FILE) && filesize(CModel_settings::PROXY_FILE) > 5)
      {
      CLogger::write(CLoggerType::DEBUG, 'pinger: http proxies loading');
      $curl->load_proxy_list(CModel_settings::PROXY_FILE, # optional: number of threads
        100, # optional: proxy type
        'http', # optional: target url to check
        'http://google.com', # optional: target regexp to check
        'title>G[o]{2}gle');
      CLogger::write(CLoggerType::DEBUG, 'pinger: HTTP proxies loaded ' . $curl->GetCountProxy());
      }
    }

  /**
   * Отправка данных
   * @param $url
   */
  /*public function Send($info)
    {
    if(empty($info))
      {
      CLogger::write(CLoggerType::ERROR, 'pinger: not send, url is empty ');
      return;
      }
    //---
    $url = trim($info['url']);
    //$url               = CModel_helper::DeleteHttp($url);
    $url = rtrim($url, '/');
    //--- нужно ли на пинг сервисы данный урл отправлять
    if(isset($info['send_get']) && $info['send_get'])
      {
      $this->SendToServers($url);
//--- все изменения сохраним в файл
      $this->SaveServers();
      }
    //---
    if(isset($info['send_xml']) && $info['send_xml'])
      {
      $this->SendToXmlrpcServers($url);
//--- все изменения сохраним в файл
      $this->SaveXmlrpcServers();
      }
    CLogger::write(CLoggerType::DEBUG, 'pinger: save changed');
    return true;
    }
*/
  /**
   * Отправка данных
   * @param $url
   */
  public function SendPings($info)
    {
    if(empty($info))
      {
      CLogger::write(CLoggerType::ERROR, 'pinger: not send, url is empty ');
      return;
      }
    //---
    $url = trim($info['url']);
    //$url               = CModel_helper::DeleteHttp($url);
    $url = rtrim($url, '/');
    //--- нужно ли на пинг сервисы данный урл отправлять
    $this->SendToServers($url);
    //--- все изменения сохраним в файл
    $this->SaveServers();
    CLogger::write(CLoggerType::DEBUG, 'pinger: save changed');
    return true;
    }

  /**
   * Отправка данных
   * @param $url
   */
  public function SendXML($info)
    {
    if(empty($info))
      {
      CLogger::write(CLoggerType::ERROR, 'pinger: not send, url is empty ');
      return false;
      }
    //---
    if($info['url_title'][0] == '[')
      {
      $url_title = $this->GetTileFromFile(trim($info['url_title'], '[]'));
      }
    else
    $url_title = $info['url_title'];
    //---
    $url      = trim($info['url']);
    $url_post = trim($info['url_post']);
    $url_rss  = trim($info['url_rss']);
    //---
    $this->SendToXmlrpcServers($url, $url_post, $url_rss, $url_title);
    //--- все изменения сохраним в файл
    $this->SaveXmlrpcServers();
    CLogger::write(CLoggerType::DEBUG, 'pinger: save changed');
    return true;
    }

  /**
   * Получение случайной строки из файла
   * @param $filename
   * @return string
   */
  private function GetTileFromFile($filename)
    {
//--- чуть чуть подчистим имя файла
    $filename = str_replace(array('..',
                                  '/',
                                  '\\'), '', $filename);
    $fname    = CModel_keywords::PATH_KEYWORDS . '/' . $filename;
    if(!file_exists($fname))
      {
      CLogger::write(CLoggerType::ERROR, 'pinger: not found file for title blog: ' . $filename);
      return '';
      }
    $fp = fopen($fname, "r");
    if(!$fp) return '';
    $size    = filesize($fname);
    $content = fread($fp, $size);
    $c       = substr_count($content, "\n");
//---
    $rand = rand(0, $c - 1);
//---
    $last = 0;
    $str  = '';
    for($i = 0; $i <= $rand; $i++)
      {
      if($i == $rand)
        {
        $n = strpos($content, "\n", $last);
        if($n !== false) $str = trim(substr($content, $last, $n - $last));
        break;
        }
      //--- переходим к следующему
      $last = strpos($content, "\n", $last);
      }
    //---
    fclose($fp);
    return $str;
    }

  /**
   * обычный get запрос
   * @param $url
   */
  private function SendToServers($url)
    {
    //---
    $host              = CModel_helper::DeleteHttp($url);
    $this->m_urls_send = array();
//--- init
    $curl = new AngryCurl(array($this,
                                'CallbackSend'));
    //--- загружаем прокси, если только пользователь указал
    if($this->GetSettings('pingsProxy') == 'on') $this->LoadProxies($curl);
//--- load user agents
    if(file_exists(CModel_settings::USER_AGENT_FILE))
      {
      CLogger::write(CLoggerType::DEBUG, 'pinger: user agents loading');
      $curl->load_useragent_list(CModel_settings::USER_AGENT_FILE);
      CLogger::write(CLoggerType::DEBUG, 'pinger: user agents loaded');
      }
    //---
    foreach($this->m_servers as $server => $info)
      {
      if(empty($server)) continue;
      //--- если нет ?
      $name = trim(str_ireplace("[url]", urlencode($url), $server));
      $name = str_ireplace("[host]", urlencode($host), $name);
      $curl->post($name);
      $curl->get($name);
      //---
      $this->m_urls_send[$name] = $server;
      }
    //---
    CLogger::write(CLoggerType::DEBUG, 'pinger: begin sending to servers ' . (count($this->m_urls_send)) . ' ...');
    $curl->execute((int)$this->GetSettings('maxThreads', self::MAX_THREADS));
    }

  /**
   * Пост запрос xml rpc
   * @param $url
   */
  private function SendToXmlrpcServers($url, $url_post = '', $url_rss = '', $title = '')
    {
    //---
    $host                 = CModel_helper::DeleteHttp($url);
    $this->m_xmlurls_send = array();
    //--- init
    $curl = new AngryCurl(array($this,
                                'CallbackXmlrpcSend'));
    //--- загрузка прокси, только если нужно
    if($this->GetSettings('xmlProxy') == 'on') $this->LoadProxies($curl);
    //--- load user agents
    if(file_exists(CModel_settings::USER_AGENT_FILE))
      {
      CLogger::write(CLoggerType::DEBUG, 'pinger: user agents loading');
      $user_agents = $this->GetUserAgentsForXmlRpc();
      $curl->load_useragent_list($user_agents);
      CLogger::write(CLoggerType::DEBUG, 'pinger: user agents loaded');
      }
    //---
    foreach($this->m_xmlrpc_servers as $server => $info)
      {
      if(empty($server)) continue;
      //--- для пинга нужно отправлять rss.xml
      $post_data = str_replace(array('{url}',
                                     '{url_post}',
                                     '{url_rss}',
                                     '{title}'), array($url,
                                                       $url_post,
                                                       $url_rss,
                                                       $title), self::PING_RPC_REQUEST_EXTENDED);
      CLogger::write(CLoggerType::DEBUG, 'pinger: begin sending to XML-RPC servers to ' . $server . ', ' . $url . ' | ' . $url_post . ' | ' . $url_rss . ' | ' . $title);
      $curl->post($server, $post_data, array('Content-Type: text/xml'));
      $this->m_xmlrpc_urls_send[$server] = $server;
      }
    //---
    CLogger::write(CLoggerType::DEBUG, 'pinger: begin sending to XML-RPC servers ' . (count($this->m_xmlrpc_urls_send)) . ' ...');
    $curl->execute((int)$this->GetSettings('maxThreads', self::MAX_THREADS));
    }

  /**
   * Получение специального юзер агента
   * @return array
   */
  private function GetUserAgentsForXmlRpc()
    {
    $filename = CModel_settings::USER_AGENT_FILE;
    if(!file_exists($filename))
      {
      CLogger::write(CLoggerType::ERROR, 'pinger: xml: not found file with user agents ' . $filename);
      return array("The Incutio XML-RPC PHP Library -- WordPress/3." . rand(8, 9) . "." . rand(1, 12));
      }
    //---
    $result = array();
    for($i = 0; $i < rand(10, 30); $i++)
      {
      $useragent = "The Incutio XML-RPC PHP Library -- WordPress/";
      $ver       = rand(3, 4);
      if($ver == 3) $useragent .= "3." . rand(5, 9) . "." . rand(1, 12);
      else         $useragent .= "4." . rand(1, 5) . "." . rand(1, 8);
      $result[] = $useragent;
      }
    /*$fp     = fopen($filename, "r");
    $result = array();
    while(($buffer = fgets($fp, 4096)) !== false)
      {
      $useragent = trim($buffer);
      if(empty($useragent)) continue;
//---
      if(rand(0, 1))
        {
        $useragent .= " XML-RPC PHP Library";
        if(rand(0, 1))
          {
          $ver = rand(3, 4);
          if($ver == 3) $useragent .= " -- WordPress/3." . rand(5, 9) . "." . rand(1, 12);
          else
          $useragent .= " -- WordPress/4." . rand(1, 5) . "." . rand(1, 8);
          }
        }
      $result[] = $useragent;
      }
    fclose($fp);
    */
    return $result;
    }

  /**
   * Callback функция, когда данные успешно отправлены
   * @param $response
   * @param $info
   * @param $request
   */
  public function CallbackSend($response, $info, $request)
    {
    $server_info = null;
    if(empty($info))
      {
      CLogger::write(CLoggerType::DEBUG, 'pinger: empty information about sending: ' . (var_export($request, true)));
      return;
      }
//---
    //var_dump($this->m_urls_send[$info['url']],isset($this->m_urls_send[$info['url']]));
    //exit;
    if(isset($this->m_urls_send[$request->url]) && isset($this->m_servers[$this->m_urls_send[$request->url]]))
      {
      $server_info = & $this->m_servers[$this->m_urls_send[$request->url]];
      if($server_info != null)
        {
        $server_info['date_last'] = time();
        if($info['http_code'] != 200)
          {
          if($info['http_code'] >= 400 && $info['http_code'] < 500) $server_info['invalid_40x']++;
          else                                                      $server_info['invalid_50x']++;
          }
        else
          {
          $server_info['date_last_success'] = time();
          $server_info['invalid_40x']       = 0;
          $server_info['invalid_50x']       = 0;
          }
        }
      }
//---
    if($info['http_code'] != 200)
      {
      CLogger::write(CLoggerType::DEBUG, 'pinger: request failed, HTTP answer: ' . $info['http_code'] . ', send to: ' . $request->url . ', proxy: ' . (isset($request->options[CURLOPT_PROXY]) ? $request->options[CURLOPT_PROXY] : 'none') . ',time: ' . $info['total_time']);
      }
    else
      {
      CLogger::write(CLoggerType::DEBUG, 'pinger: request success, sent: ' . $request->url . ', proxy: ' . (isset($request->options[CURLOPT_PROXY]) ? $request->options[CURLOPT_PROXY] : 'none') . ', time: ' . $info['total_time']);
      }
    //---
    if($server_info != null)
      {
      $this->Update($this->m_urls_send[$request->url], $server_info);
      //CLogger::write(CLoggerType::DEBUG, 'pinger: pings: update: ' . $this->m_urls_send[$request['url']] . ', ' . var_export($request, true));
      }
    else
      {
      CLogger::write(CLoggerType::DEBUG, 'pinger: pings: update url NOT: ' . $request->url . ', ' . var_export($request, true));
      }
    return;
    }

  /**
   * Callback функция, когда данные успешно отправлены пинг сервису
   * @param $response
   * @param $info
   * @param $request
   */
  public function CallbackXmlrpcSend($response, $info, $request)
    {
    $server_info = null;
    if(empty($info))
      {
      CLogger::write(CLoggerType::DEBUG, 'pinger: Xmlrpc: empty information about sending: ' . (var_export($request, true)));
      return;
      }
//---
    //CLogger::write(CLoggerType::DEBUG, 'pinger: Xmlrpc: ' . (var_export($response, true).(var_export($info, true))));
    //var_dump($this->m_urls_send[$info['url']],isset($this->m_urls_send[$info['url']]));
    //exit;
    if(isset($this->m_xmlrpc_urls_send[$request->url]) && isset($this->m_xmlrpc_servers[$this->m_xmlrpc_urls_send[$request->url]]))
      {
      $server_info = & $this->m_xmlrpc_servers[$this->m_xmlrpc_urls_send[$request->url]];
      if($server_info != null)
        {
        $server_info['date_last'] = time();
        if($info['http_code'] != 200)
          {
          if($info['http_code'] >= 400 && $info['http_code'] < 500) $server_info['invalid_40x']++;
          else                                                      $server_info['invalid_50x']++;
          }
        else
          {
          $server_info['date_last_success'] = time();
          $server_info['invalid_40x']       = 0;
          $server_info['invalid_50x']       = 0;
          }
        }
      }
    //---
    if($info['http_code'] != 200)
      {
      CLogger::write(CLoggerType::DEBUG, 'pinger: request xml-rpc failed, HTTP answer: ' . $info['http_code'] . ', send to: ' . $request->url . ', proxy: ' . (isset($request->options[CURLOPT_PROXY]) ? $request->options[CURLOPT_PROXY] : 'none') . ',time: ' . $info['total_time'] . ', content: ' . $response);
      }
    else
      {
      CLogger::write(CLoggerType::DEBUG, 'pinger: request xml-rpc success, sent: ' . $request->url . ', proxy: ' . (isset($request->options[CURLOPT_PROXY]) ? $request->options[CURLOPT_PROXY] : 'none') . ', time: ' . $info['total_time']);
      }
    //---
    if($server_info != null)
      {
      $this->Update($this->m_xmlrpc_urls_send[$request->url], $server_info);
      }
    else
      {
      //CLogger::write(CLoggerType::DEBUG, 'pinger: server_info not found: ' . $info['url'] );
      }
    return;
    }

  /**
   * Очистка всех задач
   *
   */
  public function ClearTask($path)
    {
    CTools_files::DeleteAll($path, false);
    }

  /**
   * Зачистка задач для xml пингатора
   */
  public function ClearTaskXml()
    {
    $this->ClearTask(self::TASK_PATH_XML);
    }

  /**
   * Зачистка задач для пингатора
   */
  public function ClearTaskPings()
    {
    $this->ClearTask(self::TASK_PATH);
    }

  /**
   *
   * Получить следующий номер задачи
   */
  private function GetNextTaskNumber($path)
    {
    $max = 0;
    $this->CheckPathTasks($path);
    $dir = dir($path);
    //---
    while(false !== $fname = $dir->read())
      {
      //--- Skip pointers
      if($fname == '.' || $fname == '..') continue;
      $pos = strpos($fname, '.');
      //---
      if($pos > 0)
        {
        $num = (int)substr($fname, 0, $pos);
        if($num > $max) $max = $num;
        }
      }
    return $max + 1;
    }

  private function CheckPathTasksXml()
    {
    $this->CheckPathTasks(self::TASK_PATH_XML);
    }

  private function CheckPathTasksPings()
    {
    $this->CheckPathTasks(self::TASK_PATH);
    }

  /**
   *
   * Проверка папки
   */
  private function CheckPathTasks($path)
    {
    if(!file_exists($path))
      {
      if(mkdir($path, 0777, true)) CLogger::write(CLoggerType::DEBUG, self::PREFIX_LOG . " directory " . $path . ' created');
      else
      CLogger::write(CLoggerType::ERROR, self::PREFIX_LOG . " directory " . $path . ' not create');
      }
    }

  /**
   *
   * Проверка папки
   */
  private function CheckPath()
    {
    if(!file_exists(self::PATH))
      {
      if(mkdir(self::PATH, 0777, true)) CLogger::write(CLoggerType::DEBUG, self::PREFIX_LOG . " directory " . self::PATH . ' created');
      else
      CLogger::write(CLoggerType::ERROR, self::PREFIX_LOG . " directory " . self::PATH . ' not create');
      }
    }

  /**
   * Сохранение задачи из POST-запроса, и добавление из настроеек
   * @param array $task
   * @return bool
   */
  public function SaveTaskXml($task)
    {
    return $this->SaveTask(self::TASK_PATH_XML, $task);
    }

  /**
   * Сохранение задачи из POST-запроса, и добавление из настроеек
   * @param array $task
   * @return bool
   */
  public function SaveTaskPings($task)
    {
    return $this->SaveTask(self::TASK_PATH, $task);
    }

  /**
   * Сохранение задачи из POST-запроса, и добавление из настроеек
   * @param array $task
   * @return bool
   */
  private function SaveTask($path, $task)
    {
    //--- сериализуем настройки
    $number = $this->GetNextTaskNumber($path);
    //---
    $result = array('task'        => $task,
                    'date_create' => time(),
                    'status'      => self::STATUS_BEGIN,
                    'number'      => $number,);
    //--- проверка папки
    $this->CheckPathTasks($path);
    //--- получаем имя файла
    $fileName = $this->GetFilename($path, $number);
    //--- сохраняем сериализованные настройки
    file_put_contents($fileName, serialize($result));
    //--- выставляем права 777 на файл
    chmod($fileName, 0777);
    //---
    return true;
    }

  /**
   *
   * Обновим задачу
   * @param array $task
   * @return bool
   */
  private function UpdateTask($path, $task)
    {
    //--- проверка папки
    $this->CheckPathTasks($path);
    //--- сохраняем сериализованные настройки
    $filename = $this->GetFilename($path, $task['number']);
    file_put_contents($filename, serialize($task));
    chmod($filename, 0777);
    //---
    return true;
    }

  /**
   *
   * Обновим задачу для xml пингатора
   * @param array $task
   * @return bool
   */
  public function UpdateTaskXml($task)
    {
    return $this->UpdateTask(self::TASK_PATH_XML, $task);
    }

  /**
   *
   * Обновим задачу для xml пингатора
   * @param array $task
   * @return bool
   */
  public function UpdateTaskPings($task)
    {
    return $this->UpdateTask(self::TASK_PATH, $task);
    }

  /**
   * Список стоп файлов
   */
  private function GetStopFilesname()
    {
    return array($this->m_start_file,
                 $this->m_stop_file,
                 '.data.php');
    }

  /**
   *
   * Обновление статуса начать
   * @param array $task
   */
  private function UpdateStatusStarting($path, $task)
    {
    $task['status'] = self::STATUS_START;
    $this->UpdateTask($path, $task);
    }

  /**
   *
   * Обновление статуса начать
   * @param array $task
   */
  public function UpdateStatusStartingPings($task)
    {
    $this->UpdateStatusStarting(self::TASK_PATH, $task);
    }

  /**
   *
   * Обновление статуса начать
   * @param array $task
   */
  public function UpdateStatusStartingXML($task)
    {
    $this->UpdateStatusStarting(self::TASK_PATH_XML, $task);
    }

  /**
   * Обновление статуса финиш
   * @param array $task
   */
  private function UpdateStatusFinish($path, $task)
    {
    $task['status'] = self::STATUS_FINISH;
    $this->UpdateTask($path, $task);
    }

  /**
   * Обновление статуса финиш
   * @param array $task
   */
  public function UpdateStatusFinishXML($task)
    {
    $this->UpdateStatusFinish(self::TASK_PATH_XML, $task);
    }

  /**
   * Обновление статуса финиш
   * @param array $task
   */
  public function UpdateStatusFinishPings($task)
    {
    $this->UpdateStatusFinish(self::TASK_PATH, $task);
    }

  /**
   * Получение списка задач
   */
  private function GetListTask($path)
    {
    $list_files = array();
    CTools_files::GetAllFiles(rtrim($path, '/'), $list_files, $this->GetStopFilesname());
    $this->SortByNumber($list_files);
    //---
    return $list_files;
    }

  /**
   * Получение списка задач
   */
  public function GetListTaskPings()
    {
    return $this->GetListTask(self::TASK_PATH);
    }

  /**
   * Получение списка задач
   */
  public function GetListTaskXML()
    {
    return $this->GetListTask(self::TASK_PATH_XML);
    }

  /**
   * Само упорядочивание
   * @param string $a
   * @param string $b
   * @return int
   */
  private function SortByFilename($a, $b)
    {
    if($a == $b)
      {
      return 0;
      }
    if(!preg_match_all("|([0-9]*)\.data\.php|U", $a, $out_a, PREG_PATTERN_ORDER)) return 1;
    if(!preg_match_all("|([0-9]*)\.data\.php|U", $b, $out_b, PREG_PATTERN_ORDER)) return -1;
    return ((int)$out_a[1][0] < (int)$out_b[1][0]) ? -1 : 1;
    }

  /**
   *
   * Получение данных о задаче
   * @param string $filename
   * @return array
   */
  public function GetTask($filename)
    {
    if(!file_exists($filename)) return null;
    //---
    return unserialize(file_get_contents($filename));
    }

  /**
   * Получение имени файла
   * @param $id
   */
  private function GetFilename($path, $id)
    {
    return $path . $id . ".data.php";
    }

  /**
   *
   * Получение данных о задаче
   * @param int $id
   * @return array
   */
  private function GetTaskById($path, $id)
    {
    $id = (int)$id;
    return $this->GetTask($this->GetFilename($path, $id));
    }

  /**
   *
   * Получение данных о задаче
   * @param int $id
   * @return array
   */
  public function GetTaskXMLById($id)
    {
    return $this->GetTaskById(self::TASK_PATH_XML, $id);
    }

  /**
   *
   * Получение данных о задаче
   * @param int $id
   * @return array
   */
  public function GetTaskPingById($id)
    {
    return $this->GetTaskById(self::TASK_PATH, $id);
    }

  /**
   *
   * Получение имени статуса
   * @param int $status
   * @return string
   */
  public static function GetStatusName($status)
    {
    global $LNG, $TRANSLATE;
    switch($status)
    {
      case self::STATUS_BEGIN:
        return "<span>" . $TRANSLATE[$LNG]['wait_status'] . "</span>";
      case self::STATUS_START:
        return "<span>" . $TRANSLATE[$LNG]['starting_status'] . "</span>";
      case self::STATUS_FINISH:
        return "<span>" . $TRANSLATE[$LNG]['finish_status'] . "</span>";
    }
    return "<span style='color: #AF2020'>" . $TRANSLATE[$LNG]['error_status'] . "</span>";
    }

  /**
   * Сортировка данных по номеру
   * @param array $list_files
   */
  private function SortByNumber(&$list_files)
    {
    usort($list_files, array($this,
                             'SortByFilename'));
    }

  /**
   * Нужно ли останавливать выполнение задачи
   */
  private function IsStopTask($path)
    {
    return file_exists($path . 'stop.php') || (!file_exists($path . 'stop.php') && !file_exists($path . 'start.php'));
    }

  /**
   * Запущены ли сейчас таски?
   */
  public function IsStopTaskPings()
    {
    return $this->IsStopTask(self::TASK_PATH);
    }

  /**
   * Запущены ли сейчас таски?
   */
  public function IsStopTaskXML()
    {
    return $this->IsStopTask(self::TASK_PATH_XML);
    }

  /**
   * Запущены ли сейчас таски?
   */
  public function IsStartTaskXML(&$text)
    {
    return $this->IsStartTask(self::TASK_PATH_XML, $text);
    }

  /**
   * Запущены ли сейчас таски?
   */
  public function IsStartTaskPings(&$text)
    {
    return $this->IsStartTask(self::TASK_PATH, $text);
    }

  /**
   * Запущены ли сейчас таски?
   */
  public function IsStartTask($path, &$text)
    {
    if(file_exists($path . 'start.php'))
      {
      $text = file_get_contents($path . 'start.php');
      return true;
      }
    return false;
    }

  /**
   * Запуск задач
   * @param $path
   */
  private function StartTask($path)
    {
    $this->CheckPathTasks($path);
    //---
    file_put_contents($path . 'start.php', '<??>');
    chmod($path . 'start.php', 0777);
    //---
    if(file_exists($path . 'stop.php')) unlink($path . 'stop.php');
    }

  /**
   * Начали работау
   */
  public function StartTaskPings()
    {
    $this->StartTask(self::TASK_PATH);
    }

  /**
   * Начали работау
   */
  public function StartTaskXML()
    {
    $this->StartTask(self::TASK_PATH_XML);
    }

  /**
   * Стартуем поток с задачами
   */
  public function StartThreadTaskPings()
    {
    //---
    $host     = $_SERVER['HTTP_HOST'];
    $cookie   = trim(preg_replace("/PHPSESSID=[a-z0-9]{1,}/", '', $_SERVER['HTTP_COOKIE']), '; ');
    $urlArray = parse_url($_SERVER['REQUEST_URI']);
    $path     = $urlArray['path'] . '?module=pinger&name=pings' . (isset($_REQUEST['pingsRepeat']) && $_REQUEST['pingsRepeat'] == 'on' ? '&pingsRepeat=on' : '') . (isset($_REQUEST['pingsRandom']) && $_REQUEST['pingsRandom'] == 'on' ? '&pingsRandom=on' : '') . '&' . urlencode('a[starttask]');
    //---
    $fp = fsockopen($host, 80);
    //--- установим тип сокетов (блокируемые сокеты)
    stream_set_blocking($fp, 1);
    //--- установим таймаут на 24 часа
    stream_set_timeout($fp, 86400);
    //--- вызываем скрипт, передавая ему необходимые переменные
    fwrite($fp, "GET {$path} HTTP/1.1\r\n" . "Host: {$host}\r\n" . "Cookie: {$cookie}\r\n" . "Connection: close\r\n\r\n");
    //--- ждем 3 секунды, чтобы данные успели отправиться
    sleep(3);
    //--- закрываем коннект, а запущенный скрипт будет продолжать выполняться
    fclose($fp);
    }

  /**
   * Стартуем поток с задачами
   */
  public function StartThreadTaskXML()
    {
    //---
    $host     = $_SERVER['HTTP_HOST'];
    $cookie   = trim(preg_replace("/PHPSESSID=[a-z0-9]{1,}/", '', $_SERVER['HTTP_COOKIE']), '; ');
    $urlArray = parse_url($_SERVER['REQUEST_URI']);
    $path     = $urlArray['path'] . '?module=pinger&name=xml&' . urlencode('a[starttask]');
    //---
    $fp = fsockopen($host, 80);
    //--- установим тип сокетов (блокируемые сокеты)
    stream_set_blocking($fp, 1);
    //--- установим таймаут на 24 часа
    stream_set_timeout($fp, 86400);
    //--- вызываем скрипт, передавая ему необходимые переменные
    fwrite($fp, "GET {$path} HTTP/1.1\r\n" . "Host: {$host}\r\n" . "Cookie: {$cookie}\r\n" . "Connection: close\r\n\r\n");
    //--- ждем 3 секунды, чтобы данные успели отправиться
    sleep(3);
    //--- закрываем коннект, а запущенный скрипт будет продолжать выполняться
    fclose($fp);
    }

  /**
   * Останавливаем работу задач
   */
  private function StopTask($path)
    {
    $this->CheckPathTasks($path);
    //---
    if(file_exists($path . 'start.php')) unlink($path . 'start.php');
    //---
    $isCreate = file_put_contents($path . 'stop.php', '<??>');
    chmod($path . 'stop.php', 0777);
    //---
    return $isCreate;
    }

  public function StopTaskPings()
    {
    return $this->StopTask(self::TASK_PATH);
    }

  /**
   * Останавливаем работу задач
   */
  public function StopTaskXML()
    {
    return $this->StopTask(self::TASK_PATH_XML);
    }

  /**
   * Удаление задачи
   * @param int $id
   * @return bool
   */
  private function DeleteTask($path, $id)
    {
    $id    = (int)$id;
    $fname = $path . $id . ".data.php";
    if(file_exists($fname))
      {
      unlink($fname);
      return true;
      }
    return false;
    }

  /**
   * Удаление задачи
   * @param int $id
   * @return bool
   */
  public function DeleteTaskPings($id)
    {
    return $this->DeleteTask(self::TASK_PATH, $id);
    }

  /**
   * Удаление задачи
   * @param int $id
   * @return bool
   */
  public function DeleteTaskXML($id)
    {
    return $this->DeleteTask(self::TASK_PATH_XML, $id);
    }

  /**
   * Закончили работу
   */
  private function FinishedTask($path)
    {
    //--- удаление stop.php
    if(file_exists($path . 'stop.php')) unlink($path . 'stop.php');
    //--- удаление start.php
    if(file_exists($path . 'start.php')) unlink($path . 'start.php');
    }

  /**
   * Закончили работу
   */
  public function FinishedTaskPings()
    {
    $this->FinishedTask(self::TASK_PATH);
    }

  /**
   * Закончили работу
   */
  public function FinishedTaskXML()
    {
    $this->FinishedTask(self::TASK_PATH_XML);
    }

  /**
   * Получение настроек
   * @param <string> $param Название параметра
   */
  public function GetSettings($param, $default = NULL)
    {
    if(empty($param)) return NULL;
    //---
    if(!is_array($this->m_settingsArray))
      {
      $this->m_settingsArray = array();
      CLogger::write(CLoggerType::DEBUG, 'pinger: empty settings data');
      }
    //--- возвращаем значение ключа, если оно есть в массиве
    return array_key_exists($param, $this->m_settingsArray) ? $this->m_settingsArray[$param] : $default;
    }

  /**
   * Загрузка настроек
   * @param <string> $name Имя файла с настройками
   */
  public function LoadSettings()
    {
    //---
    if(file_exists(self::PATH . "pinger_settings.data.php"))
      {
      $c                     = file_get_contents(self::PATH . "pinger_settings.data.php");
      $this->m_settingsArray = unserialize($c);
      }
    }

  /**
   *
   * Сохранение текущей настройки
   * @param string $filename
   */
  public function SaveSettings($settings)
    {
    //--- проверка папки
    if(!file_exists(self::PATH)) mkdir(self::PATH, 0777, true);
    //--- сериализуем настройки
    $this->m_settingsArray = $settings;
    //---
    file_put_contents(self::PATH . "pinger_settings.data.php", serialize($settings));
    $this->settingsArray = serialize($settings);
    CLogger::write(CLoggerType::DEBUG, 'pinger: settings save to file ' . self::PATH . "pinger_settings.data.php");
    }

  /**
   * В файл со стартом напишем текущее время и урл
   * @param $path
   * @param $url
   */
  private function UpdateStartFile($path, $url)
    {
    file_put_contents($path . 'start.php', time() . '|' . $url);
    }

  /**
   * Обновление текущего выполняемого урла
   * @param $url
   */
  public function UpdateStartFilePings($url)
    {
    $this->UpdateStartFile(self::TASK_PATH, $url);
    }

  /**
   * Обновление текущего выполняемого урла
   * @param $url
   */
  public function UpdateStartFileXML($url)
    {
    $this->UpdateStartFile(self::TASK_PATH_XML, $url);
    }

  /**
   * ПОлучение информации о текущем пингуемом урле
   * @param $text
   */
  public function GetStartStatusValue($text)
    {
    if(empty($text)) return null;
    //---
    $ar = explode('|', $text);
    if(count($ar) >= 2)
      {
      $info = array('url'  => $ar[1],
                    'time' => date('Y.m.d H:i:s', $ar[0]));
      return $info;
      }
    //---
    return null;
    }

  /**
   * Получим случайную не выполненную задачу, либо текущую
   * @param $list
   * @param $current_task
   * @return array
   */
  public function GetTaskRandom($list, &$current_task)
    {
    $c = count($list);
    for($i = 0; $i < 10; $i++)
      {
      $r         = rand(0, $c - 1);
      $file_name = $list[$r];
      //--- данные получены
      $data = $this->GetTask($file_name);
      //--- если задача не выполнены, то возвращаем ее
      if($data['status'] != self::STATUS_FINISH)
        {
        CLogger::write(CLoggerType::DEBUG, 'pinger: random: ' . $file_name . ' current: ' . $current_task . ', list: ' . $c);
        $current_task = $file_name;
        return $data;
        }
      }
    CLogger::write(CLoggerType::DEBUG, 'pinger: random: not found, get current: ' . $current_task);
    //--- случайные не получилось, по этому отправляем текущий
    return $this->GetTask($current_task);
    }

  /**
   * Зачистка xml rpc серверов
   * @param $count40x
   * @param $count50x
   * @param $days
   */
  public function ClearServersXML($count40x, $count50x, $days)
    {
    if($count40x > 9 || $count50x > 9)
      {
      CLogger::write(CLoggerType::DEBUG, 'pinger: xml: clear servers: 40x > ' . $count40x . ',50x > ' . $count50x);
      $this->LoadXmlrpcServices();
      if(!empty($this->m_xmlrpc_servers))
        {
        $servers = array();
        foreach($this->m_xmlrpc_servers as $url => $info)
          {
          //--- если установлены дни, то сначала проверим их, а уже потом сами ошибки
          if($days > 0)
            {
            $last = max($info['date_create'], $info['date_last_success']);
            if($last != 0)
              {
              $seconds = time() - $last;
              if($seconds < $days * 24 * 3600)
                {
                //--- оставляем
                $servers[$url] = $info;
                continue;
                }
              }
            }
          if($count40x > 9 && $info['invalid_40x'] > $count40x)
            {
            CLogger::write(CLoggerType::DEBUG, 'pinger: xml: delete server: ' . $url . ', 40x errors > ' . $count40x);
            }
          elseif($count50x > 9 && $info['invalid_50x'] > $count50x)
            {
            CLogger::write(CLoggerType::DEBUG, 'pinger: xml: delete server: ' . $url . ', 50x errors > ' . $count50x);
            }
          else $servers[$url] = $info;
          }
//--- посмотрим, кто остался и сохраним в файл
        $this->m_xmlrpc_servers = $servers;
        $this->SaveXmlrpcServers();
        }
      }
    }

  /**
   * Зачистка pings серверов
   * @param $count40x
   * @param $count50x
   * @param $days
   */
  public function ClearServersPings($count40x, $count50x, $days)
    {
    if($count40x > 9 || $count50x > 9)
      {
      CLogger::write(CLoggerType::DEBUG, 'pinger: pings: clear servers: 40x > ' . $count40x . ',50x > ' . $count50x);
      $this->LoadServices();
      if(!empty($this->m_servers))
        {
        $servers = array();
        foreach($this->m_servers as $url => $info)
          {
          //--- если установлены дни, то сначала проверим их, а уже потом сами ошибки
          if($days > 0)
            {
            $last = max($info['date_create'], $info['date_last_success']);
            if($last != 0)
              {
              $seconds = time() - $last;
              if($seconds < $days * 24 * 3600)
                {
                //--- оставляем
                $servers[$url] = $info;
                continue;
                }
              }
            }
          //--- проверку по дням прошли, значит удалять не надо
          if($count40x > 9 && $info['invalid_40x'] > $count40x)
            {
            CLogger::write(CLoggerType::DEBUG, 'pinger: pings: delete server: ' . $url . ', 40x errors > ' . $count40x);
            }
          elseif($count50x > 9 && $info['invalid_50x'] > $count50x)
            {
            CLogger::write(CLoggerType::DEBUG, 'pinger: xml: delete server: ' . $url . ', 50x errors > ' . $count50x);
            }
          else $servers[$url] = $info;
          }
//--- посмотрим, кто остался и сохраним в файл
        $this->m_servers = $servers;
        $this->SaveServers();
        }
      }
    }
  }

?>