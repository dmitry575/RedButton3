<?php
/**
 * Парсер для картинок
 */
class CVideo extends IPage
  {
  private $m_settings;
  private $m_global_settings;
  private $m_model;
  //--- переводы
  protected static $m_translate = array('ru' => array('main_title'              => 'Парсер Youtube',
                                                      'b_language'              => 'Язык',
                                                      'b_name'                  => 'Имя файла с текстом',
                                                      'b_start'                 => 'Старт',
                                                      'b_add_task'              => 'Добавить задачу',
                                                      'b_settings_save_success' => 'Задача успешно добавлена',
                                                      'b_settings_save_error'   => 'Ошибка добавления задачи',
                                                      'b_threads'               => 'Включить многопоточность',
                                                      'b_google'                => 'Парсить с Google',
                                                      'b_yandex'                => 'Парсить с Яндекс',
                                                      'b_task_start'            => 'Запустить',
                                                      'b_are_sure_delete'       => 'Вы точно хотите удалить все задачи',
                                                      'b_task_delete'           => 'Удалить',
                                                      'b_system'                => 'Откуда',
                                                      'b_keywords'              => 'Ключевые слова',
                                                      'b_path_download'         => 'Путь закачки',
                                                      'b_date'                  => 'Дата создания',
                                                      'b_status'                => 'Статус',
                                                      'b_stop'                  => 'Остановить',
                                                      'b_refresh'               => 'Обновить',
                                                      'b_method'                => 'Метод парсинга',
                                                      'b_delete_texts'          => 'удалить',
                                                      'b_repeat_texts'          => 'повторить задания',
                                                      'b_total'                 => 'Всего',
                                                      'b_min_symbols'           => 'минимум символов в тексте',
                                                      'b_check_dublicate'       => 'убирать дубликаты предложений (замедляет работу)',
                                                      'b_need_proxy'            => 'Для работы многопоточности необходимы прокси сервера',
                                                      'b_count_threads'         => 'Количество потоков',
                                                      'b_count_threads_title'   => 'Потоков может быть от 2 до 50',
                                                      'b_pause'                 => 'Кол-во секунд между запросами к поисковой системе:',
                                                      'b_count_video'           => 'Максимальное кол-во видео для парсинга'),
    //---
                                        'en' => array('main_title'              => 'Youtube Parser',
                                                      'b_language'              => 'Language',
                                                      'b_name'                  => 'New file name',
                                                      'b_start'                 => 'Start',
                                                      'b_add_task'              => 'Add task',
                                                      'b_settings_save_success' => 'Task add success',
                                                      'b_settings_save_error'   => 'Error adding task',
                                                      'b_threads'               => 'Enable multi-threading',
                                                      'b_google'                => 'Parse from Google.com',
                                                      'b_yandex'                => 'Parse from Yandex.ru',
                                                      'b_task_start'            => 'Start',
                                                      'b_are_sure_delete'       => 'Are you sure you want delete all tasks',
                                                      'b_task_delete'           => 'Delete',
                                                      'b_system'                => 'From',
                                                      'b_keywords'              => 'Keywords',
                                                      'b_path_download'         => 'Path download',
                                                      'b_date'                  => 'Date create',
                                                      'b_status'                => 'Status',
                                                      'b_stop'                  => 'Stop',
                                                      'b_refresh'               => 'Refresh',
                                                      'b_delete_texts'          => 'delete',
                                                      'b_repeat_texts'          => 'repeat tasks',
                                                      'b_total'                 => 'Total',
                                                      'b_min_symbols'           => 'min symbols in text',
                                                      'b_check_dublicate'       => 'remove duplicate sentence (work slowly)',
                                                      'b_need_proxy'            => 'For threads need proxies',
                                                      'b_count_threads'         => 'Number of threads',
                                                      'b_count_threads_title'   => 'The number of threads may be between 2 and 50',
                                                      'b_pause'                 => ' sec pause between request to search',
                                                      'b_count_video'           => 'Сount video in file'));

  //---
  /**
   * Конструктор
   */
  public function __construct()
    {
    //---
    $this->SetTitle(self::GetTranslate('main_title'));
    //---
    $this->m_model = new CModel_ParserVideo();
    //---
    $this->m_settings = new CModel_ParseSettings();
    $this->m_settings->Load("video");
    //---
    $this->m_global_settings = new CModel_settings();
    //---    
    $this->m_lang = $this->m_settings->Get('language', 'en');
    }

  /**
   * Отображение доверя
   * @see IPage::Show()
   */
  public function Show($path = null)
    {
    global $LNG;
    //---
    $this->m_list_task = $this->m_model->GetListTask();
    //---
    include("./inc/pages/video/index.phtml");
    if(isset($_SESSION['video_savesettings'])) unset($_SESSION['video_savesettings']);
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
  private function OnSaveTask($url)
    {
    //--- обработаем имя файла
    $filename = isset($_POST['filename']) ? $_POST['filename'] : '';
    if(empty($filename)) $filename = 'video-' . uniqid() . '.txt';
    $filename = CModel_helper::generate_file_name(CModel_tools::Translit($filename));
    if($filename[0] == '.') $filename = 'random_' . uniqid() . $filename;
    //--- расширение
    if(strlen($filename) < 4) $filename .= '.txt';
    else if(substr($filename, strlen($filename) - 4) != '.txt') $filename .= '.txt';
    //---
    $_POST['filename'] = $filename;
    //---
    $this->m_settings->Save($_POST, 'video');
    //---
    $this->m_model->SaveTask($_POST, CModel_ParserVideo::TYPE_TEXT);
    //---
    CLogger::write(CLoggerType::DEBUG, CModel_ParserVideo::PREFIX_LOG . " task saved");
    //---
    $_SESSION['video_savesettings'] = 1;
    //--- редиректим на страницу со списком задач
    header("location: ?module=video");
    exit;
    }

  /**
   * Остановка пакетной генерации
   */
  private function OnStopTask()
    {
    $this->m_model->StopTask();
    //---
    CLogger::write(CLoggerType::DEBUG, CModel_ParserVideo::PREFIX_LOG . " tasks stopped by action OnStopTask");
    //--- редиректим на страницу со списком тасков
    header("location: ?module=video");
    exit;
    }

  /**
   *
   * Отображение всех задач
   */
  private function OnClearTask()
    {
    CLogger::write(CLoggerType::DEBUG, CModel_ParserVideo::PREFIX_LOG . " clear task begin");
    $this->m_model->ClearTask();
    CLogger::write(CLoggerType::DEBUG, CModel_ParserVideo::PREFIX_LOG . " clear task finished");
    //--- редиректим на страницу со списком тасков
    header("location: ?module=video");
    exit;
    }

  /**
   * Запуск выполения тасков в фоновом режиме
   * через сокеты
   */
  private function OnRunTasks()
    {
    //--- заранее создадим файл start.php
    $this->m_model->StartTask();
    //---
    $host     = $_SERVER['HTTP_HOST'];
    $cookie   = trim(preg_replace("/PHPSESSID=[a-z0-9]{1,}/", '', $_SERVER['HTTP_COOKIE']), '; ');
    $urlArray = parse_url($_SERVER['REQUEST_URI']);
    $path     = $urlArray['path'] . '?module=video&' . urlencode('a[starttask]');
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
    //--- редиректим на страницу со списком тасков
    header("location: ?module=video");
    exit;
    }

  /**
   * Фоновое выполнение скрипта на PHP без crontab
   */
  private function OnStartTask()
    {
    $list = $this->m_model->GetListTask();
    //--- http://veselov.sumy.ua/blog/php/page/4/
    ignore_user_abort(1); // Игнорировать обрыв связи с браузером
    set_time_limit(0); // Время работы скрипта неограниченно
    //---
    CLogger::write(CLoggerType::DEBUG, CModel_ParserVideo::PREFIX_LOG . " task starting");
    //---
    if(empty($list))
      {
      CLogger::write(CLoggerType::DEBUG, CModel_ParserVideo::PREFIX_LOG . " no task for start");
      return;
      }
    //--- старт задач
    $this->m_model->StartTask();
    //---
    foreach($list as $file_task)
      {
      if($this->m_model->IsStopTask())
        {
        //--- Остановка скрипта, работающего в фоновом режиме
        break;
        }
      $task_info = $this->m_model->GetTask($file_task);
      if(empty($task_info))
        {
        CLogger::write(CLoggerType::DEBUG, CModel_ParserVideo::PREFIX_LOG . " task not found " . $file_task);
        continue;
        }
      CLogger::write(CLoggerType::DEBUG, CModel_ParserVideo::PREFIX_LOG . ' ' . $file_task . ' - ' . $task_info['number'] . ' - ' . date("d.m.Y H:i", $task_info['date_create']) . ' checking');
      //--- нужно ли запускать задачу
      if($task_info['status'] == CModel_ParserVideo::STATUS_FINISH) continue;
      //---
      CLogger::write(CLoggerType::DEBUG, CModel_ParserVideo::PREFIX_LOG . ' ' . $file_task . ' - ' . $task_info['number'] . ' - ' . date("d.m.Y H:i", $task_info['date_create']) . ' starting');
      //--- изменить статус на выполнение
      $this->m_model->UpdateStatusStarting($task_info);
      //--- записи в лог
      CLogger::write(CLoggerType::DEBUG, CModel_ParserVideo::PREFIX_LOG . ' ' . $file_task . ' - ' . $task_info['number'] . ' - ' . date("d.m.Y H:i", $task_info['date_create']) . ' start');
      //--- вычитаем ключевики
      $keywords = $this->m_model->GetKeywords($task_info['task']);
      //---
      if(empty($keywords))
        {
        CLogger::write(CLoggerType::ERROR, CModel_ParserVideo::PREFIX_LOG . ' keywords is empty ' . $file_task . ' - ' . $task_info['number'] . ' - ' . date("d.m.Y H:i", $task_info['date_create']) . ' finished');
        //--- закончили
        $this->m_model->UpdateStatusFinish($task_info);
        CLogger::write(CLoggerType::DEBUG, CModel_ParserVideo::PREFIX_LOG . ' ' . $file_task . ' - ' . $task_info['number'] . ' - ' . date("d.m.Y H:i", $task_info['date_create']) . ' finished');
        continue;
        }
      //---
      CLogger::write(CLoggerType::DEBUG, CModel_ParserVideo::PREFIX_LOG . ' parsing starting, keywords: ' . count($keywords) . ', threads: ' . (!empty($task_info['task']['threads']) ? "many" : "one"));
      //--- запустим парсер в зависимости от многопоточности
      if(!empty($task_info['task']['threads']))
        {
        //--- много поточность
        $this->m_model->ParseManyThreads($keywords, $task_info['task']);
        }
      else
        {
        //--- однопоточный
        $this->m_model->ParseOneThread($keywords, $task_info['task']);
        }
      CLogger::write(CLoggerType::DEBUG, CModel_ParserVideo::PREFIX_LOG . ' parsing finished');
      //--- закончили
      $this->m_model->UpdateStatusFinish($task_info);
      CLogger::write(CLoggerType::DEBUG, CModel_ParserVideo::PREFIX_LOG . ' ' . $file_task . ' - ' . $task_info['number'] . ' - ' . date("d.m.Y H:i", $task_info['date_create']) . ' finished');
      }
    //--- все задачи выполнили
    $this->m_model->FinishedTask();
    //---
    CLogger::write(CLoggerType::DEBUG, CModel_ParserVideo::PREFIX_LOG . " task finished");
    }

  /**
   * ПОлучение настроек
   */
  private function GetSettings()
    {
    return $this->m_settings;
    }

  /**
   * ПОлучение настроек
   */
  private function GetGlobalSettings()
    {
    return $this->m_global_settings;
    }

  /**
   * Удаление задач
   * @param $url
   */
  public function OnDeleteVideo($url)
    {
    $this->m_model->GetListTask();
    $ids = explode(',', $_REQUEST['video_ids']);
    //---
    foreach($ids as $id)
      {
      if(empty($id)) continue;
      //---
      if($this->m_model->Delete($id))
        {
        CLogger::write(CLoggerType::DEBUG, "video deleted " . $id);
        }
      }
    //---
    header("location: ?module=video");
    exit;
    }

  /**
   * Повторение задачи
   * @param $url
   */
  public function OnRepeatVideo($url)
    {
    $ids = explode(',', $_REQUEST['video_ids']);
    foreach($ids as $id)
      {
      if(empty($id)) continue;
      $text_info           = $this->m_model->GetTaskById($id);
      $text_info['status'] = CModel_ParserVideo::STATUS_BEGIN;
      if($this->m_model->UpdateTask($text_info)) CLogger::write(CLoggerType::DEBUG, "video update status BEGIN " . $id);
      }
    //---
    header("location: ?module=video");
    exit;
    }
  }

?>