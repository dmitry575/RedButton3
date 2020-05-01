<?php
//+------------------------------------------------------------------+
//|                  Copyright c 2012, RedButton Web Sites Generator |
//|                                      http://www.getredbutton.com |
//+------------------------------------------------------------------+
/**
 * Парсер для картинок
 */
class CModel_ParserImage
  {
  const TYPE_IMAGE = 1;
  const PREFIX_LOG = "parser: image:";
  /**
   * максимальное количество потоков
   */
  //const MAX_THREADS_WEBSITES = 25;
  /**
   * максимальное количество потоков к поисковикам
   */
  //const MAX_THREADS_SEARCH = 5;
  /**
   * Имя файла где будут хранится ключевики и название картинок
   */
  const KEYWORD_FILENAME = "keywords.dat.php";
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
   * Количество потоков для скачивания картинок
   */
  const DEFAULT_THREADS = 7;
  /**
   * Путь где лежат настройки и другие данные
   */
  const TASK_PATH = 'data/parsers/images/task/';
  /**
   * Путь для скачивания ссылок
   */
  const IMAGES_FILE_PATH = 'data/parsers/images/urls/';
  /**
   * Урл для парсинга картинок яндекса
   */
  const URL_YANDEX = 'http://images.yandex.ru/yandsearch';
  /**
   * Урл для парсинга картинок гугла
   */
  //const URL_GOOGLE = 'http://images.google.com/images';
  const URL_GOOGLE = 'http://ajax.googleapis.com/ajax/services/search/images';
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
   * список урлов для сохранения
   * @var array
   */
  private $m_urls_images;
  /**
   * список урлов для сохранения в файл
   * @var string
   */
  private $m_urls_images_string = array();
  /**
   * Количество картинок в папке
   * @var int
   */
  private $m_count_images_path = 0;
  /**
   * Разрешенные расширения картинок
   * @var array
   */
  private static $m_images_ext = array('png',
                                       'jpg',
                                       'jpeg',
                                       'gif');
  /**
   * Текущая папка для работы с картинками
   * @var string
   */
  private $m_current_path = '';
  /**
   * Соотношение картинки и кейворда
   * @var array
   */
  //private $m_images_keywords = array();
  /**
   * Настройки
   * @var array
   */
  private $m_settings;
  /**
   * Парсинг поисковиков
   * @var AngryCurl
   */
  private $m_curl_search;
  /**
   * Парсинг самих картинок
   * @var AngryCurl
   */
  private $m_curl_web;
  /**
   * Соотношение урлов и кейвордов
   * @var array
   */
  private $m_keys_urls;

  /**
   * Пустой конструктор
   */
  public function __construct()
    {
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
    //--- путь потом подставляем
    //$task['path'] = CModel_macros::PATH_IMAGES . '/' . $task['path'];
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
    $fileName = self::TASK_PATH . $number . ".data.php";
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
    file_put_contents(self::TASK_PATH . 'start.php', '<??>');
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
    $filename = self::TASK_PATH . $id . '.data.php';
    if(file_exists($filename))
      {
      if(unlink($filename)) CLogger::write(CLoggerType::DEBUG, self::PREFIX_LOG . " file deleted: " . $filename);
      }
    }

  /**
   * Получение имени файла
   * @param $id
   *
   * @return string
   */
  public function GetFilename($id)
    {
    return self::TASK_PATH . $id . ".data.php";
    }

  /**
   * Получение данных о задаче
   * @param string $filename
   *
   * @return array
   */
  public function GetTaskById($id)
    {
    return $this->GetTask($this->GetFilename($id));
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
   * Получение имени файла для сохранения
   * @param string $translit_keyword
   * @param string $ext
   *
   * @return string
   */
  private static function GenerateFileName($translit_keyword, $ext)
    {
    if(empty($ext)) $ext = 'jpg';
    //---
    return CModel_tools::generate_file_name($translit_keyword . '-' . uniqid() . '.' . $ext);
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
   * сохраняем картинки
   * @param string $filename
   * @param string $img_url
   */
  private function SaveImage($filename, $img_url, $system, $keyword)
    {
    //--- если просто нужно закачать картинку
    if(isset($this->m_settings['save_file']) && $this->m_settings['save_file'] == 'on')
      {
      $this->m_urls_images_string[$img_url] = 1;
      }
    else
      {
      CLogger::write(CLoggerType::DEBUG, self::PREFIX_LOG . $system . " download file: " . $img_url);
      //--- сделаем нужные проверки и сохраним
      $img_src = $this->GetPage($img_url);
      if($this->SaveImageContent($filename, $img_url, $img_src))
        {
        CLogger::write(CLoggerType::DEBUG, self::PREFIX_LOG . $system . " save image to " . $filename);
        //if(isset($this->m_settings['save_keyword']) && $this->m_settings['save_keyword'] == 'on') $this->m_images_keywords[$filename] = $keyword;
        }
      }
    }

  /**
   * Сохранение картинки в файл
   * @param $filename
   * @param $img_url
   *
   * @return bool
   */
  private function SaveImageContent($filename, $img_url, $img_src)
    {
    $ext = pathinfo($img_url, PATHINFO_EXTENSION);
    //--- если нужно изменять размер
    if($this->m_settings['images_width'] > 0)
      {
      //--- объект картинки
      $src = imagecreatefromstring($img_src);
      if(!$src)
        {
        CLogger::write(CLoggerType::DEBUG, self::PREFIX_LOG . "invalid image: " . $img_url);
        //--- картинки нет
        return false;
        }
      $width  = imagesx($src);
      $height = imagesy($src);
      //--- коффециенты
      $new_width  = $this->m_settings['images_width'];
      $new_height = $height;
      $k_width    = $width / $new_width;
      if($k_width != 0) $new_height = $height / $k_width;
      //---
      $src = CModel_ImageChange::ImageResize($src, $width, $height, $new_width, $new_height);
      if(CModel_ImageChange::SaveToFile($src, $this->GetImageType($ext), $filename)) CLogger::write(CLoggerType::DEBUG, self::PREFIX_LOG . "from image: " . $img_url . ' to ' . $filename . ' saved, new width: ' . $new_width . ', new heigth: ' . $new_height);
      }
    //--- просто сохраним файл
    else
      {
      if(file_put_contents($filename, $img_src)) CLogger::write(CLoggerType::DEBUG, self::PREFIX_LOG . "from image: " . $img_url . ' to ' . $filename . ' saved');
      }
    //--- количество скачиваемых картинок увеличено
    $this->m_count_images_path++;
    return true;
    }

  /**
   * Получение имени картинки по расширению
   * @param $ext
   *
   * @return int
   */
  private function GetImageType($ext)
    {
    $ext = strtolower($ext);
    switch($ext)
    {
      case 'png':
        return IMAGETYPE_PNG;
      case 'gif':
        return IMAGETYPE_GIF;
      default:
        return IMAGETYPE_JPEG;
    }
    }

  /**
   * сохраняем в файл
   * @param $filename
   */
  private function SaveUrlsImagesToFile($filename)
    {
    $fname = self::IMAGES_FILE_PATH . $filename;
    $data  = '';
    if(!empty($this->m_urls_images_string))
      {
      foreach($this->m_urls_images_string as $url => $num)
        {
        $data .= $url . "\r\n";
        }
      }
    file_put_contents($fname, $data, FILE_APPEND);
    //---
    CLogger::write(CLoggerType::DEBUG, self::PREFIX_LOG . " urls append to file " . $fname . ', bytes ' . strlen($data) . ', count images: ' . count($this->m_urls_images_string));
    unset($this->m_urls_images_string);
    $this->m_urls_images_string = array();
    }

  /**
   * Парсим данных от гугла и сохраняем картинки в папку
   * @param string $keyword
   * @param int $page
   * @param string $path
   * @param array $settings
   */
  private function ParseGoogle($keyword, $page)
    {
    $url = self::URL_GOOGLE . '?v=1.0&rsz=8&hl=' . urlencode($this->m_settings['language']) . '&q=' . urlencode($keyword) . '&start=' . $page;
    CLogger::write(CLoggerType::DEBUG, self::PREFIX_LOG . " google: keyword: " . $keyword . ", send request " . $url);
    //---
    $result = $this->GetPage($url);
    //---
    $translit_keyword = CModel_tools::Translit($keyword);
    //---
    $urls = json_decode($result); //$this->ParseGooglePage($result);
    if(empty($urls))
      {
      CLogger::write(CLoggerType::DEBUG, self::PREFIX_LOG . " google: keyword: " . $keyword . " not urls on search page");
      return;
      }
    foreach($urls->responseData->results as $google_images)
      {
      if($this->IsStopTask())
        {
        CLogger::write(CLoggerType::DEBUG, self::PREFIX_LOG . " find stop.php ");
        return;
        }
      //---
      $url_info = pathinfo($google_images->unescapedUrl);
      //---
      $fname    = self::GenerateFileName($translit_keyword, $url_info['extension']);
      $filename = $this->m_current_path . "/" . $fname;
      //---
      $this->SaveImage($filename, $google_images->unescapedUrl, 'google:', $keyword);
      //--- проверка может быть хватит
      if($this->m_count_images_path >= $this->m_settings['count_images'])
        {
        CLogger::write(CLoggerType::DEBUG, self::PREFIX_LOG . " count images download: " . $this->m_count_images_path . ' in settings ' . $this->m_settings['count_images']);
        return;
        }
      }
    }

  /**
   * Парсим данные по урлам из поисковика гугла
   * @param $content
   *
   * @return array
   */
  /*  private function ParseGooglePage($content)
      {
      $check_url = array();
      preg_match_all("|src=\"(.*)\"|iU", $content, $urls);
      if(!empty($urls))
        {
        for($c = 0; $c < count($urls[1]); $c++)
          {
          //---
          $img_url = $urls[1][$c];
          if($img)
          $img_url = str_replace("\\x3d", '', $img_url);
          $img_url = str_replace("\\", '', $img_url);
          if(!CModel_helper::IsExistHttp($img_url)) $img_url = "http://" . $img_url;
          //--- дубли не нужны
          if(isset($check_url[$img_url])) continue;
          $check_url[$img_url] = 1;
          }
        }
      return $check_url;
      }
   */
  /**
   * Парсим данные по урлам из поисковика яндекса
   * @param $content
   *
   * @return array
   */
  private function ParseYandexPage($content)
    {
    $content = str_replace("&amp;", "&", $content);
    preg_match_all("|img_url=(.*?)&|", $content, $urls);
    if(!empty($urls))
      {
      for($c = 0; $c < count($urls[1]); $c++)
        {
        //---
        $img_url = $urls[1][$c];
        $img_url = str_replace("\\x3d", '', $img_url);
        $img_url = str_replace("\\", '', $img_url);
        $img_url = urldecode($img_url);
        //---
        if(!CModel_helper::IsExistHttp($img_url)) $img_url = "http://" . $img_url;
        //--- дубли не нужны
        if(isset($check_url[$img_url])) continue;
        //---
        $check_url[$img_url] = 1;
        }
      }
    return $check_url;
    }

  /**
   * Парсим данные от яндекса и сохраняем
   * @param $keyword
   * @param $page
   * @param $path
   * @param $settings
   */
  private function ParseYandex($keyword, $page)
    {
    $url = self::URL_YANDEX . '?p=' . $page . '&ed=1&text=' . urlencode($keyword) . '&nl=1&stype=image';
    CLogger::write(CLoggerType::DEBUG, self::PREFIX_LOG . " yandex: keyword: " . $keyword . ", send request " . $url);
    //---
    $result = $this->GetPage($url);
    //---
    $translit_keyword = CModel_tools::Translit($keyword);
//---
    $urls = $this->ParseYandexPage($result);
    if(empty($urls) || !is_array($urls))
      {
      CLogger::write(CLoggerType::DEBUG, self::PREFIX_LOG . " google: keyword: " . $keyword . " not urls on search page");
      return;
      }
    foreach($urls as $img_url => $val)
      {
      if($this->IsStopTask())
        {
        CLogger::write(CLoggerType::DEBUG, self::PREFIX_LOG . " find stop.php ");
        return;
        }
      //---
      $fname    = self::GenerateFileName($translit_keyword, !isset($url_info['extension']) ? 'jpg' : $url_info['extension']);
      $filename = $this->m_current_path . "/" . $fname;
      //---
      $this->SaveImage($filename, $img_url, 'yandex:', $keyword);
      //---
      if($this->m_count_images_path >= $this->m_settings['count_images'])
        {
        CLogger::write(CLoggerType::DEBUG, self::PREFIX_LOG . " count images download: " . $this->m_count_images_path . ' in settings ' . $this->m_settings['count_images']);
        return;
        }
      }
    }

  /**
   * Перед начало парсинга
   * @param $settings
   * @param $path
   */
  private function BeforParsing($settings)
    {
    $this->m_settings = $settings;
    //--- потоки
    if(isset($this->m_settings['threads_count']))
      {
      $this->m_max_threads = (int)$this->m_settings['threads_count'];
      if($this->m_max_threads < 2) $this->m_max_threads = 5;
      if($this->m_max_threads > 50) $this->m_max_threads = 50;
      }
    //--- проверка папки
    if(empty($this->m_settings['images_path'])) $this->m_current_path = rtrim($this->m_settings['path'], "\\/");
    else    $this->m_current_path = rtrim($this->m_settings['images_path'], "\\/");
    //---
    $this->m_current_path = CModel_macros::PATH_IMAGES . '/' . $this->m_current_path;
    CLogger::write(CLoggerType::DEBUG, self::PREFIX_LOG . " set current directory: " . $this->m_current_path);
    if(!file_exists($this->m_current_path))
      {
      if(mkdir($this->m_current_path, 0777, true)) CLogger::write(CLoggerType::DEBUG, self::PREFIX_LOG . " created directory: " . $this->m_current_path);
      }
    //--- зачистка папки если нужно
    if(isset($settings['path_clear']) && $this->m_settings['path_clear'] == 'on') CTools_files::DeleteAll($this->m_current_path, false);
//---
    $this->m_count_images_path = 0; //CModel_files::GetCountFiles($path,self::m_extentions);
    $this->keywords_images     = array();
    }

  /**
   * Парсер картинок для гугля в одном потоке
   * @param array $keywords
   * @param array $settings
   *
   * @return null
   */
  public function ParseOnThread($keywords, $settings)
    {
    if(empty($keywords)) return null;
    //--- что парсить
    $is_google = !empty($settings['google']);
    $is_yandex = !empty($settings['yandex']);
    //---
    $this->BeforParsing($settings);
    //---
    CLogger::write(CLoggerType::DEBUG, self::PREFIX_LOG . " work path " . $this->m_current_path . ' parsing ' . ($is_google ? "google " : "") . ($is_yandex ? "yandex " : ""));
    //---
    $res = false;
    foreach($keywords as $key)
      {
      if($res) break;
      for($i = 0; $i < self::DEFAULT_PAGES; $i++)
        {
        if($this->IsStopTask())
          {
          CLogger::write(CLoggerType::DEBUG, self::PREFIX_LOG . " find stop.php ");
          break;
          }
        //--- запрос к гуглу
        if($is_google) $this->ParseGoogle($key, $i);
        //---
        if($this->IsStopTask())
          {
          CLogger::write(CLoggerType::DEBUG, self::PREFIX_LOG . " find stop.php ");
          break;
          }
        //--- запрос к яндексу
        if($is_yandex) $this->ParseYandex($key, $i);
        if($this->m_count_images_path >= $this->m_settings['count_images'])
          {
          CLogger::write(CLoggerType::DEBUG, self::PREFIX_LOG . " count images download: " . $this->m_count_images_path . ' in settings ' . $this->m_settings['count_images']);
          $res = true;
          break;
          }
        if($settings['pause'] > 0) sleep($settings['pause']);
        //---
        }
      }
    //--- выполняем действия после парсинга
    $this->AfterParsing();
    }

  /**
   * Выполняем функцию после парсинга
   */
  private function AfterParsing()
    {
    if(isset($this->m_settings['save_keyword']) && $this->m_settings['save_keyword'] == 'on') $this->SaveImagesKeywordsToFile();
    }

  /**
   * Сохранение всех файлов и их картинок
   */
  /*private function SaveImagesKeywordsToFile()
    {
    if(empty($this->m_images_keywords))
      {
      CLogger::write(CLoggerType::ERROR, self::PREFIX_LOG . " images keywords not save, list is empty");
      return false;
      }
    //---
    $filename = $this->m_current_path . '/' . self::KEYWORD_FILENAME;
    if(file_exists($filename))
      {
      $data = file_get_contents($filename);
      $d    = unserialize($data);
      //--- копию сделаем
      if(!empty($d) && is_array($d))
        {
        foreach($d as $id => $val) $this->m_images_keywords[$id] = $val;
        unset($d);
        }
      }
//---
    file_put_contents($filename, serialize($this->m_images_keywords));
    CLogger::write(CLoggerType::DEBUG, self::PREFIX_LOG . " images keywords saved: " . $filename);
    }
*/
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
      //---
      $keys     = explode("\n", file_get_contents($filename));
      $keywords = array();
      //--- проверим кодировку
      $isUtf8 = CModel_tools::IsUTF8($keys[0]) ? 1 : 0;
      //--- загрузим в зависимости от категории
      foreach($keys as $key)
        {
        //--- пытаемся сконвертировать из windows-1251
        if($isUtf8 < 1) $key = mb_convert_encoding($key, 'UTF-8', 'WINDOWS-1251');
        //---
        $key = trim($key);
        $key = CModel_tools::RemoveBom($key);
        //--- не берем пустые ключи
        if(empty($key)) continue;
        //--- если это не кейворд, а название категории и название категории добавлнем в кейвод
        if($key[0] == '[' && $key[strlen($key) - 1] == ']') continue;
        //---
        $keywords[] = $key;
        }
      //---
      return $keywords;
      }
    //---
    return $task_info['keywords'];
    }

  /**
   * Парсер урлов картинок
   * @param array $keywords
   * @param array $settings
   *
   * @return null
   */
  public function ParseImagesUrls($keywords, $settings)
    {
    if(empty($keywords)) return null;
    $this->BeforParsing($settings);
    //--- что парсить
    $is_google = !empty($settings['google']);
    $is_yandex = !empty($settings['yandex']);
    //--- проверка папки
    $path = rtrim(self::IMAGES_FILE_PATH, "\\/");
    if(!file_exists($path))
      {
      if(mkdir($path, 0777, true)) CLogger::write(CLoggerType::DEBUG, self::PREFIX_LOG . " created directory: " . $path);
      }
    //---
    CLogger::write(CLoggerType::DEBUG, self::PREFIX_LOG . " work path " . $path . ' parsing urls ' . ($is_google ? "google " : "") . ($is_yandex ? "yandex " : ""));
    //---
    $this->m_urls_images = '';
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
        //--- сбросим данные в файл
        if(count($this->m_urls_images_string) > 100) $this->SaveUrlsImagesToFile($settings['images_file']);
        }
      }
    //--- скиним данные в файл
    $this->SaveUrlsImagesToFile($settings['images_file']);
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
    $this->BeforParsing($settings);
    //--- что парсить
    $is_google = !empty($settings['google']);
    $is_yandex = !empty($settings['yandex']);
    //--- ицилизация курлов
    $this->InitCurl();
    //---
    CLogger::write(CLoggerType::DEBUG, self::PREFIX_LOG . " work path " . $this->m_current_path . ', parsing many threads ' . ($is_google ? "google " : "") . ($is_yandex ? "yandex " : ""));
    //--- будем давать запросы пачками
    for($i = 0; $i < count($keywords); $i += $this->m_max_threads) //self::MAX_THREADS_SEARCH)
      {
      if($this->IsStopTask())
        {
        CLogger::write(CLoggerType::DEBUG, self::PREFIX_LOG . " parser: find stop.php ");
        return;
        }
      if($is_google) $this->ParsingUrlsGoogle($keywords, $i);
      if($is_yandex) $this->ParsingUrlsYandex($keywords, $i);
      //--- парсим много поточно картинки
      $this->ParsingImagesFromUrls();
      if($this->m_count_images_path >= $this->m_settings['count_images'])
        {
        CLogger::write(CLoggerType::DEBUG, self::PREFIX_LOG . " count images download: " . $this->m_count_images_path . ' in settings ' . $this->m_settings['count_images']);
        break;
        }
      //---
      if($settings['pause'] > 0) sleep($settings['pause']);
      }
    //---
    $this->AfterParsing($settings);
    }

  /**
   * По текущему списку урлов парсим картинки и добавляем в файл
   */
  private function ParsingImagesFromUrls()
    {
    if(empty($this->m_urls_images))
      {
      CLogger::write(CLoggerType::DEBUG, self::PREFIX_LOG . " many threads: list of images empty");
      return;
      }
    //---
    foreach($this->m_urls_images as $url)
      {
      CLogger::write(CLoggerType::DEBUG, self::PREFIX_LOG . " many threads: add to download " . $url);
      $this->m_curl_web->get($url);
      }
    $this->m_curl_web->execute($this->m_max_threads);
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
    $this->m_curl_web    = new AngryCurl(array($this,
                                               'CallbackImagesDownload'));
    //--- load proxy
    $this->LoadProxies();
    //--- load user agents
    $this->LoadUsersAgents();
    }

  /**
   * Парсим выдачу гугла
   * @param $keywords
   * @param $begin
   */
  private function ParsingUrlsGoogle(&$keywords, $begin)
    {
    //--- урл для получения картинок
    for($i = $begin; ($i < count($keywords)) && ($i < $begin + $this->m_max_threads /*self::MAX_THREADS_SEARCH*/); $i++)
      {
      for($page = 0; $page < self::DEFAULT_PAGES; $page++)
        {
        //$url = self::URL_GOOGLE . '?hl=' . urlencode($this->m_settings['language']) . '&imgsz=l&imgtbs=z&as_st=y&q=' . urlencode(trim($keywords[$i])) . '&sa=N&start=' . ($page * self::DEFAULT_COUNTS) . '&ndsp=' . self::DEFAULT_COUNTS;
        $url = self::URL_GOOGLE . '?v=1.0&rsz=8&hl=' . urlencode($this->m_settings['language']) . '&q=' . urlencode($keywords[$i]) . '&start=' . $page;
        CLogger::write(CLoggerType::DEBUG, self::PREFIX_LOG . " google: many threads: send request " . $url);
        $this->m_curl_search->Get($url);
        }
      }
    $this->m_curl_search->execute($this->m_max_threads /*self::MAX_THREADS_SEARCH*/);
    }

  /**
   * Парсим выдачу гугла
   * @param $keywords
   * @param $begin
   */
  private function ParsingUrlsYandex(&$keywords, $begin)
    {
    //--- урл для получения картинок
    for($i = $begin; ($i < count($keywords)) && ($i < $begin + $this->m_max_threads /*self::MAX_THREADS_SEARCH*/); $i++)
      {
      for($page = 0; $page < self::DEFAULT_PAGES; $page++)
        {
        $url = self::URL_YANDEX . '?p=' . $page . '&ed=1&text=' . urlencode(trim($keywords[$i])) . '&nl=1&stype=image';
        //$url = self::URL_YANDEX . '?text=' . urlencode(trim($keywords[$i])) . '&p=' . $page . '&numdoc=' . self::DEFAULT_COUNTS;
        CLogger::write(CLoggerType::DEBUG, self::PREFIX_LOG . " yandex: many threads: send request '" . $keywords[$i] . "' url: " . $url);
        $this->m_curl_search->Get($url);
        }
      }
    $this->m_curl_search->execute($this->m_max_threads /*self::MAX_THREADS_SEARCH*/);
    }

  /**
   * Callback функция, для парсинга выдачи поисковиков
   *
   * @param $response
   * @param $info
   * @param $request
   */
  public function CallbackSearchParsing($response, $info, $request)
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
      if(strpos($request->url, self::URL_YANDEX) !== false)
        {
        //--- нужно парсить данные яндекса
        $urls = $this->ParseYandexPage($response);
        //--- из запроса к яндексу получаем кейворд
        $keyword = $this->GetKeywordFromYandex($info['url']);
        $this->AddQueueUrls($urls, $keyword);
        CLogger::write(CLoggerType::DEBUG, self::PREFIX_LOG . ' send: ' . $info['url'] . ', proxy: ' . (isset($request->options[CURLOPT_PROXY]) ? $request->options[CURLOPT_PROXY] : 'none'));
        }
      elseif(strpos($info['url'], self::URL_GOOGLE) !== false)
        {
        //--- нужно парсить данные гугла
        $urls = $this->ParseGooglePage($response);
        //--- из запроса к google получаем кейворд
        $keyword = $this->GetKeywordFromGoogle($info['url']);
        $this->AddQueueUrls($urls, $keyword);
        }
      CLogger::write(CLoggerType::ERROR, self::PREFIX_LOG . ' request success but unknown search website, sent: ' . $request->url . ', proxy: ' . (isset($request->options[CURLOPT_PROXY]) ? $request->options[CURLOPT_PROXY] : 'none') . ',time: ' . $info['total_time']);
      }
    }

  /**
   * Из запроса к яндексу получим ключевое слово, которое и является запросом
   * @param $url
   */
  private function GetKeywordFromYandex($url)
    {
    $url_info = parse_url($url);
    if(empty($url_info) || empty($url_info['query'])) return '';
    //---
    parse_str($url_info['query'], $q);
    if(isset($q['text'])) return urldecode($q['text']);
    //---
    return '';
    }

  /**
   * Из запроса к яндексу получим ключевое слово, которое и является запросом
   * @param $url
   */
  private function GetKeywordFromGoogle($url)
    {
    $url_info = parse_url($url);
    if(empty($url_info) || empty($url_info['query'])) return '';
    //---
    parse_str($url_info['query'], $q);
    if(isset($q['q'])) return urldecode($q['q']);
    //---
    return '';
    }

  /**
   * Добавление в очередь урлов для парсинга
   * @param $urls
   * @param $keyword
   */
  private function AddQueueUrls($urls, $keyword)
    {
    if(!empty($urls))
      {
      //--- добавляем список урлов для парсинга
      foreach($urls as $u => $n) $this->m_urls_images[] = $u;
      //--- соотвествие кейворд и урл
      if(!empty($keyword))
        {
        foreach($urls as $u => $n)
          {
          $this->m_keys_urls[$u] = $keyword;
          }
        }
      }
    }

  /**
   * Callback функция, когда данные успешно отправлены
   *
   * @param $response
   * @param $info
   * @param $request
   */
  public function CallbackImagesDownload($response, $info, $request)
    {
    if($this->IsStopTask()) return;
    if(empty($info))
      {
      CLogger::write(CLoggerType::DEBUG, self::PREFIX_LOG . ' empty information about sending: ' . (var_export($request, true)));
      return;
      }
    //CLogger::write(CLoggerType::DEBUG, self::PREFIX_LOG . ' empty information about sending: ' . (var_export($request, true) . var_export($response, true) . var_export($info, true)));
    //---
    if($info['http_code'] != 200)
      {
      CLogger::write(CLoggerType::DEBUG, self::PREFIX_LOG . ' request failed, HTTP answer: ' . $info['http_code'] . ', send: ' . $request->url . ', proxy: ' . (isset($request->options[CURLOPT_PROXY]) ? $request->options[CURLOPT_PROXY] : 'none') . ',time: ' . $info['total_time']);
      return;
      }
    else
      {
      if($this->m_count_images_path >= $this->m_settings['count_images'])
        {
        CLogger::write(CLoggerType::DEBUG, self::PREFIX_LOG . " count images download: " . $this->m_count_images_path . ' in settings ' . $this->m_settings['count_images']);
        return;
        }
      if(!isset($this->m_keys_urls[$request->url]))
        {
        CLogger::write(CLoggerType::DEBUG, self::PREFIX_LOG . " not found keyword for url: " . $request->url);
        return;
        }
      //--- получим ключевик
      $keyword = $this->m_keys_urls[$request->url];
      unset($this->m_keys_urls[$request->url]);
      //---
      $url_info = pathinfo($request->url);
      //---
      $fname    = self::GenerateFileName(CModel_tools::Translit($keyword), $url_info['extension']);
      $filename = $this->m_current_path . "/" . $fname;
      //---
      $this->SaveImageContent($filename, $request->url, $response);
      //--- проверка может быть хватит
      if($this->m_count_images_path >= $this->m_settings['count_images'])
        {
        CLogger::write(CLoggerType::DEBUG, self::PREFIX_LOG . " count images download: " . $this->m_count_images_path . ' in settings ' . $this->m_settings['count_images']);
        return;
        }
      }
    }
  }
