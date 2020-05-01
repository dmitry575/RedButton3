<?php
//+------------------------------------------------------------------+
//|                  Copyright c 2012, RedButton Web Sites Generator |
//|                                      http://www.getredbutton.com |
//+------------------------------------------------------------------+
/**
 * Created by JetBrains PhpStorm.
 * User: User
 * Date: 21.10.12
 * Time: 22:16
 * Парсер для картинок
 */
class CModel_ParserVideo
  {
  /**
   * Тип текст
   */
  const TYPE_TEXT  = 1;
  const PREFIX_LOG = "parser: video: ";
  /**
   * максимальное количество потоков
   */
  //const MAX_THREADS_WEBSITES = 25;
  /**
   * максимальное количество потоков к поисковикам
   */
  //const MAX_THREADS_SEARCH = 4;
  /**
   * Язык по умолчанию
   */
  const DEFAULT_LANGUAGE = 'en';
  /**
   * Количество элементов на страниц
   */
  const DEFAULT_COUNTS = 20;
  /**
   * Количество скачиваемых страниц
   */
  const DEFAULT_PAGES = 2;
  /**
   * Количество потоков для скачивания текстов
   */
  const DEFAULT_THREADS = 7;
  /**
   * Размер кеша для поиска дублей,
   */
  const CLEAR_CACHE_SIZE = 1048576;
  /**
   * Путь где лежат настройки и другие данные
   */
  const TASK_PATH = 'data/parsers/video/task/';
  /**
   * урл для поиска по youtube
   */
  const URL_YOUTUBE = 'http://www.youtube.com/results?search_type=videos&search_query={query}&page={page}';
  /**
   * для просмотра ютуба
   */
  const URL_YOUTUBE_WATCH = 'http://www.youtube.com/watch?v=';
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
   * максимальное количество потоков для парсинга
   * @var int
   */
  private $m_max_threads = 5;
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
   * Список поддерживаемых языков
   * @var array
   */
  private static $m_languages = array('en',
                                      'ru',
                                      'es',
                                      'it',
                                      'de',
                                      'fr');
  /**
   * список урлов которые нужно будет парсить
   * @var array
   */
  private $m_urls_text;
  /**
   * сколько уже ссылок на видео роликов напаршено
   * @var int
   */
  private $m_urls_parsing_count = 0;
  /**
   * для парсинга выдачи поисковика
   * @var AngryCurl
   */
  private $m_curl_search;
  /**
   * для парсинга выдачи вебсайтов
   * @var AngryCurl
   */
  private $m_curl_web;
  /**
   * Настройки
   * @var array
   */
  private $m_settings;
  /**
   * Темповая папка
   * @var string
   */
  private $m_temp_path;
  /**
   * Темповый файл
   * @var string
   */
  private $m_temp_filename;


  /**
   * Конструктор
   * @param string $temp_path
   */
  public function __construct($temp_path = './tmp')
    {
    $this->m_text_clear = new CModel_HtmlCleaner();
    $this->m_temp_path  = rtrim($temp_path, '/\\');
    $this->m_temp_filename .= $this->m_temp_path . '/temp_text_' . md5(uniqid());
    $this->CheckPathTasks();
    }

  /**
   * Получить следующий номер задачи
   */
  private function GetNextTaskNumber()
    {
    $max = 0;
    $this->CheckPathTasks();
    $dir = dir(self::TASK_PATH);
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

  /**
   * Проверка папки
   */
  private function CheckPathTasks()
    {
    if(!file_exists(self::TASK_PATH))
      {
      if(mkdir(self::TASK_PATH, 0777, true)) CLogger::write(CLoggerType::DEBUG, self::PREFIX_LOG . " directory " . self::TASK_PATH . ' created');
      else
      CLogger::write(CLoggerType::ERROR, self::PREFIX_LOG . " directory " . self::TASK_PATH . ' not create');
      }
    }

  /**
   * Сохранение задачи из POST-запроса, и добавление из настроеек
   *
   * @param array $task
   * @param int $type
   *
   * @return bool
   */
  public function SaveTask($task, $type)
    {
    //--- получим имя файла
    if($task['keysFrom'] != 'list')
      {
      $fileName = $_FILES['keysFromFile']['tmp_name'];
      if(file_exists($fileName))
        {
        $task['keywords'] = explode("\n", file_get_contents($fileName));
        }
      else
        {
        CLogger::write(CLoggerType::ERROR, self::PREFIX_LOG . " file keywords not found " . $fileName);
        }
      }
    //---
    $task['filename'] = CModel_video::PATH_VIDEO . '/' . $task['filename'];
    //--- сериализуем настройки
    $number = $this->GetNextTaskNumber();
    //---
    $result = array('task'        => $task,
                    'date_create' => time(),
                    'status'      => self::STATUS_BEGIN,
                    'number'      => $number,
                    'type'        => $type);
    //--- проверка папки
    $this->CheckPathTasks();
    //--- получаем имя файла
    $fileName = $this->GetFilename($number);
    //--- сохраняем сериализованные настройки
    file_put_contents($fileName, serialize($result));
    //--- выставляем права 777 на файл
    chmod($fileName, 0777);
    //---
    return true;
    }

  /**
   * Обновим задачу
   * @param array $task
   *
   * @return bool
   */
  public function UpdateTask($task)
    {
    //--- проверка папки
    $this->CheckPathTasks();
    //--- сохраняем сериализованные настройки
    $filename = $this->GetFilename($task['number']);
    file_put_contents($filename, serialize($task));
    chmod($filename, 0777);
    //---
    return true;
    }

  /**
   * Список стоп файлов
   */
  private function GetStopFilesname()
    {
    return array($this->m_start_file,
                 $this->m_stop_file);
    }

  /**
   * Обновление статуса начать
   * @param array $task
   */
  public function UpdateStatusStarting($task)
    {
    $task['status'] = self::STATUS_START;
    $this->UpdateTask($task);
    }

  /**
   * Обновление статуса финиш
   * @param array $task
   */
  public function UpdateStatusFinish($task)
    {
    $task['status'] = self::STATUS_FINISH;
    $this->UpdateTask($task);
    }

  /**
   * Получение списка задач
   */
  public function GetListTask()
    {
    $list_files = array();
    CTools_files::GetAllFiles(rtrim(self::TASK_PATH, '/'), $list_files, $this->GetStopFilesname());
    $this->SortByNumber($list_files);
    //---
    return $list_files;
    }

  /**
   * Само упорядочивание
   * @param string $a
   * @param string $b
   *
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
   * Получение данных о задаче
   * @param string $filename
   *
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
  public function GetFilename($id)
    {
    return self::TASK_PATH . $id . ".data.php";
    }

  /**
   * Получение данных о задаче
   * @param int $id
   *
   * @return array
   */
  public function GetTaskById($id)
    {
    $id = (int)$id;
    return $this->GetTask($this->GetFilename($id));
    }

  /**
   * Получение имени статуса
   * @param int $status
   *
   * @return string
   */
  public static function GetStatusName($status)
    {
    global $TRANSLATE;
    switch($status)
    {
      case self::STATUS_BEGIN:
        return "<span style='...'>" . $TRANSLATE['wait_status'] . "</span>";
      case self::STATUS_START:
        return $TRANSLATE['starting_status'];
      case self::STATUS_FINISH:
        return "<span style='...'>" . $TRANSLATE['finish_status'] . "</span>";
    }
    return "<span style='...'>" . $TRANSLATE['error_status'] . "</span>";
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
  public function IsStopTask()
    {
    return file_exists(self::TASK_PATH . 'stop.php');
    }

  /**
   * Запущены ли сейчас таски?
   */
  public function IsStartTask()
    {
    return file_exists(self::TASK_PATH . 'start.php');
    }

  /**
   * Закончили работу
   */
  public function FinishedTask()
    {
    //--- удаление start.php
    if(file_exists(self::TASK_PATH . 'stop.php')) unlink(self::TASK_PATH . 'stop.php');
    //--- удаление stop.php
    if(file_exists(self::TASK_PATH . 'start.php')) unlink(self::TASK_PATH . 'start.php');
    }

  /**
   * Начали работау
   */
  public function StartTask()
    {
    $this->CheckPathTasks();
    //---
    file_put_contents(self::TASK_PATH . 'start.php', time());
    chmod(self::TASK_PATH . 'start.php', 0777);
    //---
    if(file_exists(self::TASK_PATH . 'stop.php')) unlink(self::TASK_PATH . 'stop.php');
    }

  /**
   * Очистка всех задач

   */
  public function ClearTask()
    {
    CTools_files::DeleteAll(self::TASK_PATH, false);
    }

  /**
   * Удаление по номеру файла
   * @param $id
   */
  public function Delete($id)
    {
    $id       = (int)$id;
    $filename = $this->GetFilename($id);
    if(file_exists($filename))
      {
      if(unlink($filename)) CLogger::write(CLoggerType::DEBUG, self::PREFIX_LOG . " file deleted: " . $filename);
      }
    }

  /**
   * Останавливаем работу задач
   */
  public function StopTask()
    {
    $this->CheckPathTasks();
    //---
    if(file_exists(self::TASK_PATH . 'start.php')) unlink(self::TASK_PATH . 'start.php');
    //---
    $isCreate = file_put_contents(self::TASK_PATH . 'stop.php', '<??>');
    chmod(self::TASK_PATH . 'stop.php', 0777);
    //---
    return $isCreate;
    }

  /**
   * Список поддерживаемых языков
   * @return array
   */
  public static function GetLanguages()
    {
    return self::$m_languages;
    }

  /**
   * Парсим данных от гугла и сохраняем картинки в папку
   * @param string $keyword
   * @param int $page
   * @param array $settings
   */
  private function ParseGoogle($keyword, $page)
    {
    //---
    $filename = $this->m_settings['filename'];
    //---
    $url = 'http://www.google.com/search?hl=' . urlencode($this->m_settings['language']) . '&q=' . urlencode(trim($keyword)) . '&sa=N&start=' . $page * self::DEFAULT_COUNTS . '&ndsp=' . self::DEFAULT_COUNTS;
    CLogger::write(CLoggerType::DEBUG, self::PREFIX_LOG . " youtube: send request " . $url);
    $result = file_get_contents($url);
    //---
    preg_match_all('/<h3.*>.*<a href="\/url\?q=http:\/\/(.*)\&/iU', $result, $urls);
    CLogger::write(CLoggerType::DEBUG, self::PREFIX_LOG . " youtube: find urls: " . count($urls[1]));
    //--- проверим количество
    if(count($urls[1]) <= 0)
      {
      CLogger::write(CLoggerType::DEBUG, self::PREFIX_LOG . " youtube: return page: " . $result);
      return;
      }
    //---
    for($c = 0; $c < count($urls[1]); $c++)
      {
      if($this->IsStopTask())
        {
        CLogger::write(CLoggerType::DEBUG, self::PREFIX_LOG . " find stop.php ");
        return;
        }
      //---
      $img_url = html_entity_decode(urldecode($urls[1][$c]));
      if(!CModel_helper::IsExistHttp($img_url)) $img_url = "http://" . $img_url;
      //---
      CLogger::write(CLoggerType::DEBUG, self::PREFIX_LOG . " youtube: download text: " . $img_url);
      //---
      $result_req = CModel_http::openHttp($img_url, "get", "", $url, CModel_helper::GetUserAgent());
      if($result_req[2]['Status-Line']['Status-Code'] != '200')
        {
        CLogger::write(CLoggerType::DEBUG, self::PREFIX_LOG . " youtube: get error code: " . $result_req[2]['Status-Line']['Status-Code'] . ", url: " . $img_url);
        continue;
        }
      //---
      //---
      $charset    = '';
      $clear_text = $this->ClearText($result_req[0], $result_req[2]['Content-Type'], $charset);
      //---
      if(empty($clear_text))
        {
        CLogger::write(CLoggerType::DEBUG, self::PREFIX_LOG . " youtube: text is empty url: " . $img_url);
        continue;
        }
      //--- делаем разные проверки и если что не сохраняем
      if($this->SaveTextFromSearch($filename, $clear_text)) CLogger::write(CLoggerType::DEBUG, self::PREFIX_LOG . " youtube: save text to " . $filename . ', charset: ' . $charset);
      }
    }

  /**
   * Сохранение текста из поисковика с проверками на дубликат
   * @param $filename
   * @param $text
   *
   * @return bool
   */
  private function SaveTextFromSearch($filename, $text)
    {
    $res = true;
    if($this->m_check_dublicate)
      {
      if($this->CheckDublicate($text)) $res = false;
      }
    if($res && $this->m_settings['min_symbols'] > 0)
      {
      if(strlen($text) < $this->m_settings['min_symbols']) $res = false;
      }
    //---
    if($res)
      {
      file_put_contents($filename, $text . "\r\n\r\n", FILE_APPEND);
      //---
      return true;
      }
    return false;
    }

  /**
   * Зачистка текста
   * @param string $text
   * @param string $content_type
   * @param string $charset
   *
   * @return string
   */
  private function ClearText($text, $content_type, &$charset)
    {
    //--- из текста берем charset
    preg_match("/charset=([\w|\-]+);?/", $text, $match);
    if(isset($match[1])) $charset = $match[1];
    else
      {
      //--- пытаемся из content-type распарсить
      preg_match('/charset\=([\w|\-]+)+?/i', $content_type, $matches);
      if(isset($matches[1])) $charset = $matches[1];
      else
        {
//--- ничего не получилось оставили utf8
        $charset = 'UTF-8';
        }
      }
    CLogger::write(CLoggerType::DEBUG, self::PREFIX_LOG . "convert to " . $charset . ' content-type: ' . $content_type);
    //--- меняем кодировку текста на UTF-8
    $text       = CModel_tools::CharsetConvertFromCharset($text, $charset);
    $clear_text = $this->m_text_clear->Clear($text);
    //---
    return $clear_text;
    }

  /**
   * Парсер текста для гугля в одном потоке
   * @param array $keywords
   * @param array $settings
   *
   * @return null
   */
  public function ParseOneThread($keywords, $settings)
    {
    if(empty($keywords)) return null;
    $this->m_settings = $settings;
    //--- проверка папки
    $finfo = pathinfo($this->m_settings['filename']);
    $path  = rtrim($finfo['dirname'], "\\/");
    if(!file_exists($path))
      {
      if(mkdir($path, 0777, true)) CLogger::write(CLoggerType::DEBUG, self::PREFIX_LOG . " created directory: " . $path);
      }
    //---
    CLogger::write(CLoggerType::DEBUG, self::PREFIX_LOG . " work file " . $this->m_settings['filename'] . ' parsing one thread');
    //--- зачистим файл
    file_put_contents($this->m_settings['filename'], chr(0xEF) . chr(0xBB) . chr(0xBF));
    //---
    $current_count = 0;
    foreach($keywords as $key)
      {
      for($i = 0; $i < self::DEFAULT_PAGES; $i++)
        {
        if($this->IsStopTask())
          {
          CLogger::write(CLoggerType::DEBUG, self::PREFIX_LOG . " find stop.php ");
          return;
          }
        $k = trim($key);
        if(empty($k)) continue;
        //---
        $this->ParseYoutube($k, $i + 1);
        //--- проверим количество
        $current_count = count($this->m_urls_text);
        if(($this->m_urls_parsing_count + $current_count) > $this->m_settings['count_video'])
          {
          CLogger::write(CLoggerType::DEBUG, self::PREFIX_LOG . " parsing " . count($this->m_urls_text) . ' videos, need ' . $this->m_settings['count_video']);
          break;
          }
        if($current_count > 100)
          {
          $this->SaveUrlsToFile($this->m_settings['filename']);
          //--- зачистим список
          $this->m_urls_text = array();
          $this->m_urls_parsing_count += $current_count;
          }
        //---
        if($this->m_settings['pause'] > 0) sleep($this->m_settings['pause']);
        else sleep(1);
        }
      //---
      if(($this->m_urls_parsing_count + $current_count) > $this->m_settings['count_video']) break;
      }
    $this->SaveUrlsToFile($this->m_settings['filename']);
    }

  /**
   * Запрос и парсинг к ютубу
   * @param $key
   * @param $i
   */
  private function ParseYoutube($key, $i)
    {
    $url = str_replace(array('{query}',
                             '{page}'), array(urlencode(trim($key)),
                                              $i * self::DEFAULT_COUNTS), self::URL_YOUTUBE);
    CLogger::write(CLoggerType::DEBUG, self::PREFIX_LOG . " youtube: one thread: send request " . $url);
    $page = $this->GetPage($url);
    //---
    $this->ParseUrlsFromYoutube($page);
    }

  /**
   * Скидываем данные в файл
   * @param $filename
   */
  private function SaveUrlsToFile($filename)
    {
    if(empty($this->m_urls_text)) return;
    $text  = '';
    $count = 0;
    foreach($this->m_urls_text as $url => $num)
      {
      $text .= $url . "\r\n";
      $count++;
      }
    file_put_contents($filename, $text,FILE_APPEND);
    CLogger::write(CLoggerType::DEBUG, self::PREFIX_LOG . " urls " . $count . ' saved to file ' . $filename);
    }

  /**
   * Получаем страничку
   * @param $url
   */
  private function GetPage($url)
    {
    $options = array('http' => array('method' => "GET",
                                     'header' => "Accept-language: en,ru\r\n" . "User-Agent: " . (CModel_UserAgents::GetRandom()) . "\r\n"));
    $context = stream_context_create($options);
    return file_get_contents($url, false, $context);
    }

  /**
   * Парсер картинок для гугля в многопоточном режиме
   * @param array $keywords
   * @param array $settings
   *
   * @return null
   */
  public function ParseManyThreads($keywords, $settings)
    {
    if(empty($keywords)) return null;
    $this->m_settings = $settings;
    //--- ицилизация курлов
    $this->InitCurl();
    //--- создадим папку
    $finfo = pathinfo($this->m_settings['filename']);
    $path  = rtrim($finfo['dirname'], "\\/");
    if(!file_exists($path))
      {
      if(mkdir($path, 0777, true)) CLogger::write(CLoggerType::DEBUG, self::PREFIX_LOG . " created directory: " . $path);
      }
    //---
    CLogger::write(CLoggerType::DEBUG, self::PREFIX_LOG . " work file " . $this->m_settings['filename'] . ', parsing many threads');
    //--- зачистим файл
    file_put_contents($this->m_settings['filename'], chr(0xEF) . chr(0xBB) . chr(0xBF));
    //--- будем давать запросы пачками
    for($i = 0; $i < count($keywords); $i += $this->m_max_threads)
      {
      $this->ParsingUrlsYoutube($keywords, $i);
      //--- парсим много поточно тексты
      if($settings['pause'] > 0) sleep($settings['pause']);
      $this->SaveUrlsToFile($this->m_settings['filename']);
      //--- зачистим список
      $this->m_urls_text = array();
      }
     $this->SaveUrlsToFile($this->m_settings['filename']);
    }

  /**
   * загрузка прокси серверов
   * @param AngryCurl $curl
   */
  private function LoadProxies()
    {
//--- load proxy
    if(file_exists(CModel_settings::PROXY_FILE))
      {
      CLogger::write(CLoggerType::DEBUG, self::PREFIX_LOG . ' http proxies loading');
      //----
      $this->m_curl_web->load_proxy_list(CModel_settings::PROXY_FILE, # optional: number of threads
        100, # optional: proxy type
        'http', # optional: target url to check
        'http://google.com', # optional: target regexp to check
        'title>G[o]{2}gle');
      //---
      $this->m_curl_search->load_proxy_list(CModel_settings::PROXY_FILE, # optional: number of threads
        100, # optional: proxy type
        'http', # optional: target url to check
        'http://google.com', # optional: target regexp to check
        'title>G[o]{2}gle');
      CLogger::write(CLoggerType::DEBUG, self::PREFIX_LOG . ' HTTP proxies loaded');
      }
    elseif(file_exists(CModel_settings::PROXY_SOCKS_FILE))
      {
      CLogger::write(CLoggerType::DEBUG, self::PREFIX_LOG . ' socks proxies loading');
      //---
      $this->m_curl_web->load_proxy_list(CModel_settings::PROXY_SOCKS_FILE, # optional: number of threads
        100, # optional: proxy type
        'socks5', # optional: target url to check
        'http://google.com', # optional: target regexp to check
        'title>G[o]{2}gle');
      //---
      $this->m_curl_search->load_proxy_list(CModel_settings::PROXY_SOCKS_FILE, # optional: number of threads
        100, # optional: proxy type
        'socks5', # optional: target url to check
        'http://google.com', # optional: target regexp to check
        'title>G[o]{2}gle');
      CLogger::write(CLoggerType::DEBUG, self::PREFIX_LOG . ' socks proxies loaded');
      }
    }

  /**
   * Загрузка юзер агентов
   */
  private function LoadUsersAgents()
    {
    if(file_exists(CModel_settings::USER_AGENT_FILE))
      {
      CLogger::write(CLoggerType::DEBUG, self::PREFIX_LOG . ' parser: search user agents loading');
      $this->m_curl_search->load_useragent_list(CModel_settings::USER_AGENT_FILE);
      $this->m_curl_web->load_useragent_list(CModel_settings::USER_AGENT_FILE);
      CLogger::write(CLoggerType::DEBUG, self::PREFIX_LOG . ' parser: search user agents loaded');
      }
    }

  /**
   * Инцилизация курлов для работы с сайтами и поисковиками
   */
  private function InitCurl()
    {
//--- init
    $this->m_curl_search = new AngryCurl(array($this,
                                               'CallbackSearchParsing'));
    //--- init
    $this->m_curl_web = new AngryCurl(array($this,
                                            'CallbackWebsitesParsing'));
    //--- load proxy
    $this->LoadProxies();
    //--- загрузка юзер агентов
    $this->LoadUsersAgents();
    }

  /**
   * Парсим выдачу гугла
   * @param $keywords
   * @param $begin
   */
  private function ParsingUrlsYoutube(&$keywords, $begin)
    {
    //--- урл для получения картинок
    for($i = $begin; ($i < count($keywords)) && ($i < $begin + $this->m_max_threads); $i++)
      {
      for($page = 0; $page < self::DEFAULT_PAGES; $page++)
        {
        $url = str_replace(array('{query}',
                                 '{page}'), array(urlencode(trim($keywords[$i])),
                                                  $page * self::DEFAULT_COUNTS), self::URL_YOUTUBE);
        CLogger::write(CLoggerType::DEBUG, self::PREFIX_LOG . " youtube: many threads: send request " . $url);
        $this->m_curl_search->Get($url);
        }
      }
    $this->m_curl_search->execute($this->m_max_threads);
    }

  /**
   * Callback функция, когда данные успешно отправлены
   *
   * @param $response
   * @param $info
   * @param $request
   */
  public function CallbackWebsitesParsing($response, $info, $request)
    {
    if($this->IsStopTask()) return;
    if(empty($info))
      {
      CLogger::write(CLoggerType::DEBUG, self::PREFIX_LOG . ' empty information about sending: ' . (var_export($request, true)));
      return;
      }
    //---
    if($info['http_code'] != 200)
      {
      CLogger::write(CLoggerType::DEBUG, self::PREFIX_LOG . ' request failed, HTTP answer: ' . $info['http_code'] . ', send: ' . $info['url'] . ', proxy: ' . (isset($request->options[CURLOPT_PROXY]) ? $request->options[CURLOPT_PROXY] : 'none') . ',time: ' . $info['total_time']);
      return;
      }
    else
      {
      //--- нужно парсить данные
      $this->ParseUrlsFromYoutube($response);
      }
    }

  /**
   * Парсинг урлов со страницы гугла
   * @param $text
   */
  private function ParseUrlsFromYoutube($text)
    {
    if(empty($text)) return;
    //--- список
    preg_match_all('/href=\"\/watch\?v=([^\"]*)\"/sU', $text, $urls);
    CLogger::write(CLoggerType::DEBUG, self::PREFIX_LOG . " youtube: find urls: " . count($urls[1]));
    //--- проверим количество
    if(count($urls[1]) <= 0)
      {
      CLogger::write(CLoggerType::DEBUG, self::PREFIX_LOG . " youtube: return page with no urls");
      return;
      }
    //---
    for($c = 0; $c < count($urls[1]); $c++)
      {
      if($this->IsStopTask())
        {
        CLogger::write(CLoggerType::DEBUG, self::PREFIX_LOG . " youtube: find stop.php ");
        return;
        }
      //---
      $img_url = self::URL_YOUTUBE_WATCH . $urls[1][$c];
      if(!isset($this->m_urls_text[$img_url]))
        {
        $this->m_urls_text[$img_url] = 1;
        }
      }
    }

  /**
   * Получение ключевиков
   * @param array $task_info
   *
   * @return array|null
   */
  public function GetKeywords(&$task_info)
    {
    if($task_info['keysFrom'] == 'list')
      {
      $filename = CModel_keywords::PATH_KEYWORDS . '/' . $task_info['keysFromList'];
      if(!file_exists($filename))
        {
        CLogger::write(CLoggerType::ERROR, CModel_ParserImage::PREFIX_LOG . ' error file not found ' . $filename . ', ' . $task_info['number']);
        return null;
        }
      $keywords = explode("\n", file_get_contents($filename));
      return $keywords;
      }
    //---
    return $task_info['keywords'];
    }
  }

?>
