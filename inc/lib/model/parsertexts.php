<?php
//+------------------------------------------------------------------+
//|                  Copyright c 2012, RedButton Web Sites Generator |
//|                                      http://www.getredbutton.com |
//+------------------------------------------------------------------+
include_once 'inc/lib/osrc/Html/Readability.php';
/**
 * Created by JetBrains PhpStorm.
 * User: User
 * Date: 21.10.12
 * Time: 22:16
 * Парсер для картинок
 */
class CModel_ParserTexts
  {
  /**
   * Тип текст
   */
  const TYPE_TEXT  = 1;
  const PREFIX_LOG = "parser: texts: ";
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
  const TASK_PATH = 'data/parsers/texts/task/';
  /**
   * урл для поиска по гуглу
   */
  const URL_GOOGLE = 'https://www.google.com/search';
  /**
   * Урл для поиска по яндексу
   */
  const URL_YANDEX = 'http://yandex.ru/yandsearch';
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
   * @var CModel_HtmlCleaner|null
   */
  private $m_text_clear = null;
  /**
   * список урлов которые нужно будет парсить
   * @var array
   */
  private $m_urls_text;
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
   * имя темпового файла
   * @var string
   */
  private $m_temp_filename = '';
  /**
   * Указатель темповый файл
   * @var handle
   */
  private $m_htemp_file;
  /**
   * Темповая папка
   * @var string
   */
  private $m_temp_path;
  /**
   * Нужна ли проверка на дубликаты
   * @var bool
   */
  private $m_check_dublicate;

  /**
   * Конструктор
   * @param string $temp_path
   */
  public function __construct($temp_path = './tmp')
    {
    $this->m_text_clear = new CModel_HtmlCleaner();
    $this->m_temp_path  = rtrim($temp_path, '/\\');
    $this->m_temp_filename .= $this->m_temp_path . '/temp_text_' . md5(uniqid());
    }

  /**
   *
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
   *
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
   * @param array $task
   * @param int $type
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
    $task['filename'] = CModel_text::PATH_TEXTS . '/' . $task['filename'];
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
   *
   * Обновим задачу
   * @param array $task
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
   *
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
  public function GetFilename($id)
    {
    return self::TASK_PATH . $id . ".data.php";
    }

  /**
   *
   * Получение данных о задаче
   * @param int $id
   * @return array
   */
  public function GetTaskById($id)
    {
    $id = (int)$id;
    return $this->GetTask($this->GetFilename($id));
    }

  /**
   *
   * Получение имени статуса
   * @param int $status
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
   *
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
    //$url = self::URL_GOOGLE . '?hl=' . urlencode($this->m_settings['language']) . '&q=' . urlencode(trim($keyword)) . '&sa=N&start=' . $page * self::DEFAULT_COUNTS . '&ndsp=' . self::DEFAULT_COUNTS;
    $url = self::URL_GOOGLE . '?q=' . urlencode(trim($keyword)) . '&newwindow=1&hl=' . urlencode($this->m_settings['language']) . '&lr=lang_' . urlencode($this->m_settings['language']) . '&start=' . $page * self::DEFAULT_COUNTS . '&num=' . self::DEFAULT_COUNTS;
    CLogger::write(CLoggerType::DEBUG, self::PREFIX_LOG . " google: send request " . $url);
    $result = file_get_contents($url);
    //---
    preg_match_all('/<h3.*>.*<a href="\/url\?q=http:\/\/(.*)\&/iU', $result, $urls);
    CLogger::write(CLoggerType::DEBUG, self::PREFIX_LOG . " google: find urls: " . count($urls[1]));
    //--- проверим количество
    if(count($urls[1]) <= 0)
      {
      CLogger::write(CLoggerType::DEBUG, self::PREFIX_LOG . " google: return page: " . $result);
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
      CLogger::write(CLoggerType::DEBUG, self::PREFIX_LOG . " google: download text: " . $img_url);
      //---
      $result_req = CModel_http::openHttp($img_url, "get", "", $url, CModel_helper::GetUserAgent());
      if($result_req[2]['Status-Line']['Status-Code'] != '200')
        {
        CLogger::write(CLoggerType::DEBUG, self::PREFIX_LOG . " google: get error code: " . $result_req[2]['Status-Line']['Status-Code'] . ", url: " . $img_url);
        continue;
        }
      //---
      //---
      $charset    = '';
      $clear_text = $this->ClearText($result_req[0], isset($result_req[2]['Content-Type'])?$result_req[2]['Content-Type']:'', $charset);
      //---
      if(empty($clear_text))
        {
        CLogger::write(CLoggerType::DEBUG, self::PREFIX_LOG . " google: text is empty url: " . $img_url);
        continue;
        }
      //--- делаем разные проверки и если что не сохраняем
      if($this->SaveTextFromSearch($filename, $clear_text)) CLogger::write(CLoggerType::DEBUG, self::PREFIX_LOG . " google: save text to " . $filename . ', charset: ' . $charset);
      }
    }

  /**
   * Сохранение текста из поисковика с проверками на дубликат
   * @param $filename
   * @param $text
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
   * Парсим данные от яндекса и сохраняем
   * @param $keyword
   * @param $page
   */
  private function ParseYandex($keyword, $page)
    {
    //---
    $filename = $this->m_settings['filename'];
    //---
    $url = self::URL_YANDEX . '?text=' . urlencode(trim($keyword)) . '&p=' . $page . '&numdoc=' . self::DEFAULT_COUNTS;
    CLogger::write(CLoggerType::DEBUG, self::PREFIX_LOG . " yandex: send request " . $url);
    $result = file_get_contents($url);
    //---
    preg_match_all('/<h2[^>]*>.*<a[^>]*\_blank[^>]*href="([^<>"]+)"[^>]*>.*<\/a>/iU', $result, $urls);
    //---
    CLogger::write(CLoggerType::DEBUG, self::PREFIX_LOG . " yandex: find urls: " . count($urls[1]));
    //--- проверим количество
    if(count($urls[1]) <= 0)
      {
      CLogger::write(CLoggerType::DEBUG, self::PREFIX_LOG . " yandex: return page: " . $result);
      return;
      }
    //---
    for($c = 0; $c < count($urls[1]); $c++)
      {
      if($this->IsStopTask())
        {
        CLogger::write(CLoggerType::DEBUG, self::PREFIX_LOG . " yandex: find stop.php ");
        return;
        }
      //--- ссылки на сам яндекс проигнорируем
      if(stripos($urls[1][$c], "yandex.ru") !== FALSE) continue;
        //var_dump($urls[1][$c]);
      //---
      $img_url = html_entity_decode(urldecode($urls[1][$c]));
      if(!CModel_helper::IsExistHttp($img_url)) $img_url = "http://" . $img_url;
      //---
      CLogger::write(CLoggerType::DEBUG, self::PREFIX_LOG . " yandex: download text: " . $img_url);
      //---
      $result_req = CModel_http::openHttp($img_url, "get", "", $url, CModel_helper::GetUserAgent());
      $is_code    = !isset($result_req[2]) || !isset($result_req[2]['Status-Line']) || !isset($result_req[2]['Status-Line']['Status-Code']);
      //---
      if($is_code || !isset($result_req[2]) || !isset($result_req[2]['Status-Line']) || !isset($result_req[2]['Status-Line']['Status-Code']) || $result_req[2]['Status-Line']['Status-Code'] != '200')
        {
        CLogger::write(CLoggerType::DEBUG, self::PREFIX_LOG . " yandex: get error code: " . ($is_code ? 'unknown' : $result_req[2]['Status-Line']['Status-Code']) . ", url: " . $img_url);
        continue;
        }
      //---
      $charset    = '';
      $clear_text = $this->ClearText($result_req[0], $result_req[2]['Content-Type'], $charset);
      //---
      if(empty($clear_text))
        {
        CLogger::write(CLoggerType::DEBUG, self::PREFIX_LOG . " yandex: text is empty url: " . $img_url);
        continue;
        }
      //---
      if($this->SaveTextFromSearch($filename, $clear_text)) CLogger::write(CLoggerType::DEBUG, self::PREFIX_LOG . " yandex: save text to " . $filename . ', charset: ' . $charset);
      }
    }

  /**
   * Парсер текста для гугля в одном потоке
   * @param array $keywords
   * @param array $settings
   * @return null
   */
  public function ParseOneThread($keywords, $settings)
    {
    if(empty($keywords)) return null;
    $this->m_settings = $settings;
    //--- что парсить
    $is_google = !empty($this->m_settings['google']);
    $is_yandex = !empty($this->m_settings['yandex']);
    //---
    $this->m_text_parser = new Readability();
    //--- проверка папки
    $finfo = pathinfo($this->m_settings['filename']);
    $path  = rtrim($finfo['dirname'], "\\/");
    if(!file_exists($path))
      {
      if(mkdir($path, 0777, true)) CLogger::write(CLoggerType::DEBUG, self::PREFIX_LOG . " created directory: " . $path);
      }
    //---
    CLogger::write(CLoggerType::DEBUG, self::PREFIX_LOG . " work file " . $this->m_settings['filename'] . ' parsing one thread ' . ($is_google ? "google " : "") . ($is_yandex ? "yandex " : ""));
    //--- зачистим файл
    file_put_contents($this->m_settings['filename'], chr(0xEF) . chr(0xBB) . chr(0xBF));
    //---
    foreach($keywords as $key)
      {
      for($i = 0; $i < self::DEFAULT_PAGES; $i++)
        {
        if($this->IsStopTask())
          {
          CLogger::write(CLoggerType::DEBUG, self::PREFIX_LOG . " find stop.php ");
          return;
          }
        if($is_google) $this->ParseGoogle($key, $i);
        if($is_yandex) $this->ParseYandex($key, $i);
        if($this->m_settings['pause'] > 0) sleep($this->m_settings['pause']);
        else sleep(1);
        }
      }
    }

  /**
   * Парсер текстов для гугля в многопоточном режиме
   * @param array $keywords
   * @param array $settings
   * @return null
   */
  public function ParseManyThreads($keywords, $settings)
    {
    if(empty($keywords)) return null;
    $this->m_settings = $settings;
    if(isset($this->m_settings['threads_count']))
      {
      $this->m_max_threads = (int)$this->m_settings['threads_count'];
      if($this->m_max_threads < 2) $this->m_max_threads = 2;
      if($this->m_max_threads > 50) $this->m_max_threads = 50;
      }
    //--- что парсить
    $is_google = !empty($this->m_settings['google']);
    $is_yandex = !empty($this->m_settings['yandex']);
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
    CLogger::write(CLoggerType::DEBUG, self::PREFIX_LOG . " work file " . $this->m_settings['filename'] . ', parsing many threads ' . ($is_google ? "google " : "") . ($is_yandex ? "yandex " : "") . ' threads: ' . $this->m_max_threads);
    //--- зачистим файл
    file_put_contents($this->m_settings['filename'], chr(0xEF) . chr(0xBB) . chr(0xBF));
    //--- будем давать запросы пачками
    for($i = 0; $i < count($keywords); $i += $this->m_max_threads /*self::MAX_THREADS_SEARCH*/)
      {
      for($page = 0; $page < self::DEFAULT_PAGES; $page++)
        {
        if($is_google) $this->ParsingUrlsGoogle($keywords, $i, $page);
        if($is_yandex) $this->ParsingUrlsYandex($keywords, $i, $page);
        //--- парсим много поточно тексты
        $this->ParsingTextFromUrls();
        if($this->m_settings['pause'] > 0)
          {
          sleep($this->m_settings['pause']);
          CLogger::write(CLoggerType::DEBUG, self::PREFIX_LOG . " sleep sec: " . $this->m_settings['pause']);
          }
        }
      }
    }

  /**
   * По текущему списку урлов парсим тексты и добавляем в файл
   */
  private function ParsingTextFromUrls()
    {
    if(empty($this->m_urls_text)) return;
    foreach($this->m_urls_text as $url => $n)
      {
      CLogger::write(CLoggerType::DEBUG, self::PREFIX_LOG . " many threads: add to parsing " . $url);
      $this->m_curl_web->get($url);
      }
    $this->m_curl_web->execute($this->m_max_threads /*self::MAX_THREADS_WEBSITES*/);
    }

  /**
   * загрузка прокси серверов
   * @param AngryCurl $curl
   */
  private function LoadProxies()
    {
    //--- load proxy
    $loaded_http = false;
    if(file_exists(CModel_settings::PROXY_FILE) && filesize(CModel_settings::PROXY_FILE) > 5)
      {
      CLogger::write(CLoggerType::DEBUG, self::PREFIX_LOG . ' http proxies loading');
      //--- прокси для
      $res = $this->m_curl_web->load_proxy_list(CModel_settings::PROXY_FILE, # optional: number of threads
        100, # optional: proxy type
        'http', # optional: target url to check
        'http://google.com', # optional: target regexp to check
        'title>G[o]{2}gle');
      //---
      CLogger::write(CLoggerType::DEBUG, self::PREFIX_LOG . ' HTTP web proxies loaded: ' . $res);
      $res         = $this->m_curl_search->load_proxy_list(CModel_settings::PROXY_FILE, # optional: number of threads
        100, # optional: proxy type
        'http', # optional: target url to check
        'http://google.com', # optional: target regexp to check
        'title>G[o]{2}gle');
      $loaded_http = $res > 0;
      //---
      CLogger::write(CLoggerType::DEBUG, self::PREFIX_LOG . ' HTTP search proxies loaded: ' . $res);
      }
    //--- socks 5 прокси
    if(!$loaded_http && file_exists(CModel_settings::PROXY_SOCKS_FILE) && filesize(CModel_settings::PROXY_SOCKS_FILE) > 5)
      {
      //---
      $res = $this->m_curl_web->load_proxy_list(CModel_settings::PROXY_SOCKS_FILE, # optional: number of threads
        100, # optional: proxy type
        'socks5', # optional: target url to check
        'http://google.com', # optional: target regexp to check
        'title>G[o]{2}gle');
      CLogger::write(CLoggerType::DEBUG, self::PREFIX_LOG . ' socks web proxies loaded: ' . $res);
      $res = $this->m_curl_search->load_proxy_list(CModel_settings::PROXY_SOCKS_FILE, # optional: number of threads
        100, # optional: proxy type
        'socks5', # optional: target url to check
        'http://google.com', # optional: target regexp to check
        'title>G[o]{2}gle');
      //---
      CLogger::write(CLoggerType::DEBUG, self::PREFIX_LOG . ' socks search proxies loaded: ' . $res);
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
                                               'CallbackUrlsParsing'));
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
  private function ParsingUrlsGoogle(&$keywords, $begin, $page)
    {
    $lang = urlencode($this->m_settings['language']);
    //--- урл для получения картинок
    for($i = $begin; ($i < count($keywords)) && ($i < $begin + $this->m_max_threads /*self::MAX_THREADS_SEARCH*/); $i++)
      {
      $url = self::URL_GOOGLE . '?q=' . urlencode(trim($keywords[$i])) . '&newwindow=1&hl=' . $lang . '&lr=lang_' . $lang . '&start=' . $page * self::DEFAULT_COUNTS . '&num=' . self::DEFAULT_COUNTS;
      //$url = self::URL_GOOGLE . '?q=' . urlencode(trim($keywords[$i])) . '&hl=' . $lang . '&lr=lang_' . $lang . '&cr=countryUS&newwindow=1&start=' . $page * self::DEFAULT_COUNTS . '&num=' . self::DEFAULT_COUNTS . '&as_qdr=all&prmd=vs&filter=0';
      //https://www.google.ru/search?q=%D1%83%D1%80%D0%BD%D1%8B+%D0%B4%D0%BB%D1%8F+%D0%BC%D1%83%D1%81%D0%BE%D1%80%D0%B0+%D1%83%D0%BB%D0%B8%D1%87%D0%BD%D1%8B%D0%B5+%D0%B2+%D0%B5%D0%BA%D0%B0%D1%82%D0%B5%D1%80%D0%B8%D0%BD%D0%B1%D1%83%D1%80%D0%B3%D0%B5&hl=ru&lr=lang_ru&cr=countryUS&newwindow=1&start=0&num=20&as_qdr=all&tbs=lr:lang_1ru,ctr:countryUS&prmd=vs&filter=0&gws_rd=cr&ei=kzvWVdCUDcensAH9zaewDg
      CLogger::write(CLoggerType::DEBUG, self::PREFIX_LOG . " google: many threads: send request " . $url);
      $this->m_curl_search->Get($url);
      }
    $this->m_curl_search->execute($this->m_max_threads /*self::MAX_THREADS_SEARCH*/);
    }

  /**
   * Парсим выдачу гугла
   * @param $keywords
   * @param $begin
   */
  private function ParsingUrlsYandex(&$keywords, $begin, $page)
    {
    //--- урл для получения картинок
    for($i = $begin; ($i < count($keywords)) && ($i < $begin + $this->m_max_threads /*self::MAX_THREADS_SEARCH*/); $i++)
      {
      $url = self::URL_YANDEX . '?text=' . urlencode(trim($keywords[$i])) . '&p=' . $page . '&numdoc=' . self::DEFAULT_COUNTS;
      CLogger::write(CLoggerType::DEBUG, self::PREFIX_LOG . " yandex: many threads: send request " . $url);
      $this->m_curl_search->Get($url);
      }
    $this->m_curl_search->execute($this->m_max_threads /*self::MAX_THREADS_SEARCH*/);
    }

  /**
   * Callback функция, когда данные успешно отправлены
   * @param $response
   * @param $info
   * @param $request
   */
  public function CallbackUrlsParsing($response, $info, $request)
    {
    if($this->IsStopTask()) return;
    if(empty($info))
      {
      CLogger::write(CLoggerType::DEBUG, self::PREFIX_LOG . ' empty information about sending: ' . (var_export($request, true)));
      return;
      }
    //CLogger::write(CLoggerType::DEBUG,  self::PREFIX_LOG .' empty information about sending: ' . (var_export($request, true).var_export($response, true).var_export($info, true)));
    //var_dump($response, $info, $request); exit;
    //---
    if($info['http_code'] != 200)
      {
      CLogger::write(CLoggerType::DEBUG, self::PREFIX_LOG . ' request failed, HTTP answer: ' . $info['http_code'] . ', send: ' . $info['url'] . ', proxy: ' . (isset($request->options[CURLOPT_PROXY]) ? $request->options[CURLOPT_PROXY] : 'none') . ',time: ' . round($info['total_time'], 2));
      return;
      }
    else
      {
      if(strpos($info['url'], self::URL_YANDEX) !== false)
        {
        //--- нужно парсить данные яндекса
        $this->ParseUrlsFromYandex($response);
        }
      elseif(strpos($info['url'], self::URL_GOOGLE) !== false)
        {
        //--- нужно парсить данные яндекса
        $this->ParseUrlsFromGoogle($response);
        }
      else
      CLogger::write(CLoggerType::ERROR, self::PREFIX_LOG . ' request success but unknown search website, sent: ' . $request->url . ', proxy: ' . (isset($request->options[CURLOPT_PROXY]) ? $request->options[CURLOPT_PROXY] : 'none') . ',time: ' . round($info['total_time'], 2));
      }
    }

  /**
   * Callback функция, когда данные успешно отправлены
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
    //CLogger::write(CLoggerType::DEBUG,  self::PREFIX_LOG .' empty information about sending: ' . (var_export($request, true).var_export($response, true).var_export($info, true)));
    //var_dump($response, $info, $request); exit;
    //---
    if($info['http_code'] != 200)
      {
      CLogger::write(CLoggerType::DEBUG, self::PREFIX_LOG . ' request failed, HTTP answer: ' . $info['http_code'] . ', send: ' . $request->url . ', proxy: ' . (isset($request->options[CURLOPT_PROXY]) ? $request->options[CURLOPT_PROXY] : 'none') . ',time: ' . round($info['total_time'], 2));
      }
    else
      {
      $chartset   = 'UTF-8';
      $clear_text = $this->ClearText($response, "", $chartset);
      //---
      if(empty($clear_text))
        {
        CLogger::write(CLoggerType::DEBUG, self::PREFIX_LOG . " text is empty url: " . $info['url']);
        return;
        }
      //---
      if($this->SaveTextFromSearch($this->m_settings['filename'], $clear_text)) CLogger::write(CLoggerType::DEBUG, self::PREFIX_LOG . ' text added from url: ' . $request->url . ', proxy: ' . (isset($request->options[CURLOPT_PROXY]) ? $request->options[CURLOPT_PROXY] : 'none') . ',time: ' . round($info['total_time'], 2));
      }
    }

  /**
   * Страничку от яндекса парсим
   * @param $text
   */
  private function ParseUrlsFromYandex($text)
    {
    //--- список
    //preg_match_all('/<a.*class="b\-serp\-item__title\-link".*href="(.*)"/iU', $text, $urls);
    preg_match_all('/<h2[^>]*>.*<a[^>]*\_blank[^>]*href="([^<>"]+)"[^>]*>.*<\/a>/iU', $text, $urls);
    CLogger::write(CLoggerType::DEBUG, self::PREFIX_LOG . " yandex: find urls: " . count($urls[1]));
    //--- проверим количество
    if(count($urls[1]) <= 0)
      {
      CLogger::write(CLoggerType::DEBUG, self::PREFIX_LOG . " yandex: return page with no urls");
      return;
      }
    //---
    for($c = 0; $c < count($urls[1]); $c++)
      {
      if($this->IsStopTask())
        {
        CLogger::write(CLoggerType::DEBUG, self::PREFIX_LOG . " yandex: find stop.php ");
        return;
        }
      if(stripos($urls[1][$c], "yandex.ru") !== false) continue;
      //---
      $img_url = html_entity_decode(urldecode($urls[1][$c]));
      if(!CModel_helper::IsExistHttp($img_url)) $img_url = "http://" . $img_url;
      if(!isset($this->m_urls_text[$img_url])) $this->m_urls_text[$img_url] = 1;
      }
    }

  /**
   * Парсинг урлов со страницы гугла
   * @param $text
   */
  private function ParseUrlsFromGoogle($text)
    {
    //--- список
    preg_match_all('/<h3.*>.*<a href="\/url\?q=http:\/\/(.*)\&/iU', $text, $urls);
    CLogger::write(CLoggerType::DEBUG, self::PREFIX_LOG . " google: find urls: " . count($urls[1]));
    //--- проверим количество
    if(count($urls[1]) <= 0)
      {
      CLogger::write(CLoggerType::DEBUG, self::PREFIX_LOG . " google: return page with no urls");
      return;
      }
    //---
    $count = 0;
//var_dump($urls[1]);
    for($c = 0; $c < count($urls[1]); $c++)
      {
      if($this->IsStopTask())
        {
        CLogger::write(CLoggerType::DEBUG, self::PREFIX_LOG . " google: find stop.php ");
        return;
        }
      //---
      $img_url = html_entity_decode(urldecode($urls[1][$c]));
      if(!CModel_helper::IsExistHttp($img_url)) $img_url = "http://" . $img_url;
//var_dump($img_url);
      if(!isset($this->m_urls_text[$img_url]))
        {
        $this->m_urls_text[$img_url] = 1;
        $count++;
        }
      }
    CLogger::write(CLoggerType::DEBUG, self::PREFIX_LOG . " google: parsing " . $count . " urls");
    }

  /**
   * Получение ключевиков
   * @param array $task_info
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

  /**
   * Открытие темпового файла
   * @return bool
   */
  private function OpenTempFile()
    {
    if(!$this->m_htemp_file)
      {
      if(!file_exists($this->m_temp_path))
        {
        mkdir($this->m_temp_path, 0777);
        CLogger::write(CLoggerType::DEBUG, CModel_ParserImage::PREFIX_LOG . ' temp dir created ' . $this->m_temp_path);
        }
      //---
      $this->m_htemp_file = fopen($this->m_temp_filename, 'w+');
      if(!$this->m_htemp_file)
        {
        CLogger::write(CLoggerType::ERROR, CModel_ParserImage::PREFIX_LOG . ' temp file not open ' . $this->m_temp_filename);
        return false;
        }
      }
    return true;
    }

  /**
   * Сохраним чистый текст
   * @param $text
   */
  private function SaveClearTempText($text)
    {
    if(!$this->OpenTempFile()) return;
    //--- перейдем в конец и запишем текст
    fseek($this->m_htemp_file, 0, SEEK_END);
    fwrite($this->m_temp_filename, $text . "\r\n");
    }

  /**
   * Проверка дубликатов
   * @param $src
   */
  private function CheckDublicate($src)
    {
    $text = CModel_tools::ClearSentense($src);
    return $this->IsFindTemp($text);
    }

  /**
   * Поиск дубля в темповом файле
   * @param $text
   * @return bool
   */
  private function IsFindTemp($text)
    {
    $cur_pos = 0;
    fseek($this->m_htemp_file, 0);
    //---
    while(!feof($this->m_htemp_file))
      {
      $buffer = fgets($this->m_htemp_file, self::CLEAR_CACHE_SIZE);
      if(strpos($buffer, $text) !== FALSE)
        {
        return true;
        }
      }
    return false;
    }
  }
