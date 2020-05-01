<?php
/**
 * Парсер для картинок
 */
class CImage extends IPage
  {
  private $m_settings;
  private $m_global_settings;
  /**
   * CModel_ParserImage
   * @var CModel_ParserImage
   */
  private $m_model;
  //--- переводы
  protected static $m_translate = array('ru' => array('main_title'              => 'Парсер картинок',
                                                      'b_language'              => 'Язык парсера',
                                                      'b_path'                  => 'Папка для изображений',
                                                      'b_start'                 => 'Старт',
                                                      'b_add_task'              => 'Добавить задачу',
                                                      'b_settings_save_success' => 'Задача успешно добавлена',
                                                      'b_settings_save_error'   => 'Ошибка добавления задачи',
                                                      'b_threads'               => 'Включить многопоточность',
                                                      'b_google'                => 'Искать в Google Images',
                                                      'b_yandex'                => 'Искать в Яндекс.Картинках',
                                                      'b_task_start'            => 'Запустить',
                                                      'b_are_sure_delete'       => 'Вы действительно хотите удалить все задачи',
                                                      'b_task_delete'           => 'удалить все',
                                                      'b_system'                => 'Откуда',
                                                      'b_keywords'              => 'Ключевые слова',
                                                      'b_path_download'         => 'Путь закачки',
                                                      'b_date'                  => 'Дата создания',
                                                      'b_status'                => 'Статус',
                                                      'b_stop'                  => 'Остановить',
                                                      'b_refresh'               => 'Обновить',
                                                      'b_delete_images'         => 'удалить',
                                                      'b_repeat_images'         => 'повторить задания',
                                                      'b_use_proxy'             => 'Использовать прокси сервера',
                                                      'b_save_file'             => 'Сохранять только ссылки на изображения в файл',
                                                      'b_url_file'              => 'Ссылки в ',
                                                      'b_new_path'              => 'Создать новую папку',
                                                      'b_path_clear'            => 'Очистить папку перед стартом',
                                                      'b_width_image'           => 'Уменьшать большие изображения по ширине до',
                                                      'b_width_image_title'     => 'Уменьшение размера изображений по ширине с сохранением пропорций',
                                                      'b_pause'                 => 'Кол-во секунд между запросами к поисковой системе:',
                                                      'b_count_images'          => 'Максимальное кол-во изображений для парсинга',
                                                      'b_save_keyword'          => 'Сохранять ключевые слова для правильной выборки изображений',
                                                      'b_need_proxy'            => 'Для работы многопоточности необходимы прокси сервера',
                                                      'b_count_threads'=>'Количество потоков',
                                                      'b_count_threads_title'=>'Потоков может быть от 2 до 50'
  ),
    //---
                                        'en' => array('main_title'              => 'Image Parsing',
                                                      'b_language'              => 'Language',
                                                      'b_path'                  => 'Path for images',
                                                      'b_start'                 => 'Start',
                                                      'b_add_task'              => 'Add task',
                                                      'b_settings_save_success' => 'Task add success',
                                                      'b_settings_save_error'   => 'Error adding task',
                                                      'b_threads'               => 'Enable multi-threading',
                                                      'b_google'                => 'Parse from Google Images',
                                                      'b_yandex'                => 'Parse from Yandex Images',
                                                      'b_task_start'            => 'Start',
                                                      'b_are_sure_delete'       => 'Are you sure you want delete all tasks',
                                                      'b_task_delete'           => 'delete all',
                                                      'b_system'                => 'From',
                                                      'b_keywords'              => 'Keywords',
                                                      'b_path_download'         => 'Path download',
                                                      'b_date'                  => 'Date create',
                                                      'b_status'                => 'Status',
                                                      'b_stop'                  => 'Stop',
                                                      'b_refresh'               => 'Refresh',
                                                      'b_delete_images'         => 'delete',
                                                      'b_repeat_images'         => 'repeat',
                                                      'b_use_proxy'             => 'Use proxy from Settings',
                                                      'b_save_file'             => 'Images URLs save in file',
                                                      'b_url_file'              => 'URLs in ',
                                                      'b_new_path'              => 'Create a new folder',
                                                      'b_path_clear'            => 'Clear folder',
                                                      'b_width_image'           => ' px width images',
                                                      'b_width_image_title'     => 'resize width images',
                                                      'b_pause'                 => ' sec pause between request to search',
                                                      'b_count_images'          => 'count images in path',
                                                      'b_save_keyword'          => 'save keyword for image',
                                                      'b_need_proxy'            => 'For threads need proxies',
                                                      'b_count_threads'=>'Number of threads',
                                                      'b_count_threads_title'=>'The number of threads may be between 2 and 50'
                                        ));

  /**
   * Конструктор
   */
  public function __construct()
    {
    //---
    $this->SetTitle(self::GetTranslate('main_title'));
    //---
    $this->m_model = new CModel_ParserImage();
    //---
    $this->m_settings = new CModel_ParseSettings();
    $this->m_settings->Load("image");
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
    include("./inc/pages/image/index.phtml");
    if(isset($_SESSION['image_savesettings'])) unset($_SESSION['image_savesettings']);
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
    //--- сохранить задачи для картинки
    $this->m_settings->Save($_POST, 'image');
    //---
    $this->m_model->SaveTask($_POST, CModel_ParserImage::TYPE_IMAGE);
    //---
    CLogger::write(CLoggerType::DEBUG, CModel_ParserImage::PREFIX_LOG . " task saved");
    //---
    $_SESSION['image_savesettings'] = 1;
    //--- редиректим на страницу со списком задач
    header("location: ?module=image");
    exit;
    }

  /**
   * Остановка пакетной генерации
   */
  private function OnStopTask()
    {
    $this->m_model->StopTask();
    //---
    CLogger::write(CLoggerType::DEBUG, CModel_ParserImage::PREFIX_LOG . " tasks stopped by action OnStopTask");
    //--- редиректим на страницу со списком тасков
    header("location: ?module=image");
    exit;
    }

  /**
   *
   * Отображение всех задач
   */
  private function OnClearTask()
    {
    CLogger::write(CLoggerType::DEBUG, CModel_ParserImage::PREFIX_LOG . " clear task begin");
    $this->m_model->ClearTask();
    CLogger::write(CLoggerType::DEBUG, CModel_ParserImage::PREFIX_LOG . " clear task finished");
    //--- редиректим на страницу со списком тасков
    header("location: ?module=image");
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
    $path     = $urlArray['path'] . '?module=image&' . urlencode('a[starttask]');
    //---
    $fp = fsockopen($host, 80);
    //--- установим тип сокетов (блокируемые сокеты)
    stream_set_blocking($fp, 1);
    //--- установим таймаут на 24 часа
    stream_set_timeout($fp, 86400);
    //--- вызываем скрипт, передавая ему необходимые переменные
    $request = "GET {$path} HTTP/1.1\r\n" . "Host: {$host}\r\n" . "Cookie: {$cookie}\r\n" . "Connection: close\r\n\r\n";
    CLogger::write(CLoggerType::DEBUG, CModel_ParserImage::PREFIX_LOG . " send request for start parsing images: ".$request);
    fwrite($fp, $request);
    //--- ждем 3 секунды, чтобы данные успели отправиться
    sleep(3);
    //--- закрываем коннект, а запущенный скрипт будет продолжать выполняться
    fclose($fp);
    //--- редиректим на страницу со списком тасков
    header("location: ?module=image");
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
    CLogger::write(CLoggerType::DEBUG, CModel_ParserImage::PREFIX_LOG . " task starting");
    //---
    if(empty($list))
      {
      CLogger::write(CLoggerType::DEBUG, CModel_ParserImage::PREFIX_LOG . " no task for start");
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
        CLogger::write(CLoggerType::DEBUG, CModel_ParserImage::PREFIX_LOG . " task not found " . $file_task);
        continue;
        }
      //--- нужно ли запускать задачу
      if($task_info['status'] == CModel_ParserImage::STATUS_FINISH) continue;
      //--- изменить статус на выполнение
      $this->m_model->UpdateStatusStarting($task_info);
      //--- записи в лог
      CLogger::write(CLoggerType::DEBUG, CModel_ParserImage::PREFIX_LOG . ' ' . $file_task . ' - ' . $task_info['number'] . ' - ' . date("d.m.Y H:i", $task_info['date_create']) . ' start');
      //--- вычитаем ключевики
      $keywords = $this->m_model->GetKeywords($task_info['task']);
      //---
      if(empty($keywords))
        {
        CLogger::write(CLoggerType::ERROR, CModel_ParserImage::PREFIX_LOG . ' keywords is empty ' . $file_task . ' - ' . $task_info['number'] . ' - ' . date("d.m.Y H:i", $task_info['date_create']) . ' finished');
        //--- закончили
        $this->m_model->UpdateStatusFinish($task_info);
        CLogger::write(CLoggerType::DEBUG, CModel_ParserImage::PREFIX_LOG . ' ' . $file_task . ' - ' . $task_info['number'] . ' - ' . date("d.m.Y H:i", $task_info['date_create']) . ' finished');
        continue;
        }
      //---
      CLogger::write(CLoggerType::DEBUG, CModel_ParserImage::PREFIX_LOG . ' parsing starting, keywords: ' . count($keywords) . ', threads: ' . (!empty($task_info['task']['threads']) ? "many" : "one") . ', save ulrs: ' . (isset($task_info['task']['save_file']) && $task_info['task']['save_file'] == 'on' ? 'yes' : 'no'));
      //--- запустим парсер в зависимости от многопоточности
      if(isset($task_info['task']['save_file']) && $task_info['task']['save_file'] == 'on')
        {
        //--- только урлы картинок
        $this->m_model->ParseImagesUrls($keywords, $task_info['task']);
        }
      else
        {
        if(!empty($task_info['task']['threads'])) //--- много поточность
        $this->m_model->ParseManyThreads($keywords, $task_info['task']);
        else
          //--- однопоточный
        $this->m_model->ParseOnThread($keywords, $task_info['task']);
        }
      CLogger::write(CLoggerType::DEBUG, CModel_ParserImage::PREFIX_LOG . ' parsing finished');
      //--- закончили
      $this->m_model->UpdateStatusFinish($task_info);
      CLogger::write(CLoggerType::DEBUG, CModel_ParserImage::PREFIX_LOG . ' ' . $file_task . ' - ' . $task_info['number'] . ' - ' . date("d.m.Y H:i", $task_info['date_create']) . ' finished');
      }
    //--- все задачи выполнили
    $this->m_model->FinishedTask();
    //---
    CLogger::write(CLoggerType::DEBUG, CModel_ParserImage::PREFIX_LOG . " task finished");
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
  public function OnDeleteImages($url)
    {
    $this->m_model->GetListTask();
    $ids = explode(',', $_REQUEST['images_ids']);
    //---
    foreach($ids as $id)
      {
      if(empty($id)) continue;
      //---
      if($this->m_model->Delete($id))
        {
        CLogger::write(CLoggerType::DEBUG, "images deleted " . $id);
        }
      }
    //---
    header("location: ?module=image");
    exit;
    }

  /**
   * Повторение задачи
   * @param $url
   */
  public function OnRepeatImages($url)
    {
    $ids = explode(',', $_REQUEST['images_ids']);
    foreach($ids as $id)
      {
      if(empty($id)) continue;
      $text_info           = $this->m_model->GetTaskById($id);
      $text_info['status'] = CModel_ParserImage::STATUS_BEGIN;
      if($this->m_model->UpdateTask($text_info)) CLogger::write(CLoggerType::DEBUG, "image update status BEGIN " . $id);
      }
//---
    header("location: ?module=image");
    exit;
    }
  }

?>