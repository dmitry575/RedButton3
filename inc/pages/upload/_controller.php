<?php
class CUpload extends IPage
  {
  private $m_settings;
  //--- переводы
  protected static $m_translate = array('ru' => array('main_title'           => 'Отложенная загрузка',
                                                      'b_upload_description' => '<p>Вы можете видеть процесс загрузки на фтп сервера. Чтобы дорвеи попали в очередь на загрузку, нужно поставить галочку: "Отложенная загрузка по ftp", на <a href="">главной странице</a>.</p>
                                                      <p>При загрузке будут использовать прокси сервера, если они будут указаны в настройках. Каждый дорвей будет загружаться только по одному соединению, чтобы избежать блокировок хостинга</p> ',
                                                      'b_upload_generate'    => 'Отложенная загрузка файлов по ftp',
                                                      'b_task_list_empty'    => 'Список заданий пуст',
                                                      'b_task_start'         => 'Запустить',
                                                      'b_settings'           => 'Настройки:',
                                                      'b_setting_standart'   => 'Стандартные',
                                                      'b_task_add_list'      => 'Добавить в список задач',
                                                      'b_task_delete'        => 'Удалить все задания',
                                                      'b_tasks_add'          => 'Пакетное добавление',
                                                      'b_translate'          => 'Перевод',
                                                      'b_delete_tasks'       => 'удалить',
                                                      'b_repeat_tasks'       => 'повторить задания',
                                                      'b_begin_generating'   => 'Выполняется генерация дорвеев...',
                                                      'b_update'             => 'Обновить',
                                                      'b_are_sure'           => 'Вы уверены?',
                                                      'b_create_site'        => 'Создать сайт',
                                                      'b_ftp_server'         => 'на FTP-сервере',
                                                      'b_this_server'        => 'на этом сервере',
                                                      'b_ftp_server_name'    => 'FTP-сервер',
                                                      'b_path_name'          => 'Папка',
                                                      'b_keywords_name'      => 'Кейворды',
                                                      'b_text_name'          => 'Тексты',
                                                      'b_settings_name'      => 'Настройки',
                                                      'b_add_name'           => 'Добавить',
                                                      'b_server_name'        => 'Сервер',
                                                      'b_date_name'          => 'Дата',
                                                      'b_status_name'        => 'Статус',
                                                      'b_total'              => 'Всего',
                                                      'b_sure_delete_tasks'  => 'Вы точно хотите удалить задачи?',
                                                      'b_date_end_name'      => 'Окончание'),
    //---
                                        'en' => array('main_title'           => 'Delayed upload files on ftp',
                                                      'b_upload_description' => '<p>Вы можете видеть процесс загрузки на фтп сервера. Чтобы дорвеи попали в очередь на загрузку, нужно поставить галочку: "Отложенная загрузка по ftp", на <a href="">главной странице</a>.</p>
                                                      <p>При загрузке будут использовать прокси сервера, если они будут указаны в настройках. Каждый дорвей будет загружаться только по одному соединению, чтобы избежать блокировок хостинга</p> ',
                                                      'b_upload_generate'    => 'Delayed upload files on ftp',
                                                      'b_task_generate'      => 'Tasks for batch generation of doorways',
                                                      'b_task_list_empty'    => 'List of task is empty',
                                                      'b_task_start'         => 'Run',
                                                      'b_settings'           => 'Settings:',
                                                      'b_setting_standart'   => 'Standarts',
                                                      'b_task_add_list'      => 'Add to task list',
                                                      'b_task_delete'        => 'Task delete',
                                                      'b_tasks_add'          => 'Add package of tasks',
                                                      'b_translate'          => 'Translate',
                                                      'b_delete_tasks'       => 'delete',
                                                      'b_repeat_tasks'       => 'repeat',
                                                      'b_begin_generating'   => 'Websites are creating...',
                                                      'b_update'             => 'Update',
                                                      'b_are_sure'           => 'Are you sure?',
                                                      'b_create_site'        => 'Create website',
                                                      'b_ftp_server'         => 'on FTP-server',
                                                      'b_this_server'        => 'on current server',
                                                      'b_ftp_server_name'    => 'FTP-server',
                                                      'b_path_name'          => 'Path',
                                                      'b_keywords_name'      => 'Keywords',
                                                      'b_text_name'          => 'Texts',
                                                      'b_settings_name'      => 'Settings',
                                                      'b_add_name'           => 'Add',
                                                      'b_server_name'        => 'Server',
                                                      'b_date_name'          => 'Date add',
                                                      'b_status_name'        => 'Status',
                                                      'b_total'              => 'Total',
                                                      'b_sure_delete_tasks'  => 'Are you sure you want to delete task?',
                                                      'b_date_end_name'      => 'Date end'));
  //---
  private $m_list_tasks;
  private $m_model;

  /**
   * Конструктор
   */
  public function __construct()
    {
    $this->m_settings         = new CModel_settings();
    $this->m_current_settings = $this->m_settings->LoadCurrentSettings();
    $this->SetTitle(CTasks::GetTranslate('main_title'));
    $this->m_model = new CModel_UploadTask();
    //--- проверка активации
    }

  /**
   * Отображение доверя
   * @see IPage::Show()
   */
  public function Show($path = null)
    {
    //---
    include("./inc/pages/upload/index.phtml");
    }

  /**
   *
   * Текущая модель
   */
  public function GetModelTask()
    {
    return $this->m_model;
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
    $this->m_model->SaveTask($_POST, isset($_REQUEST['type_task']) ? (int)$_REQUEST['type_task'] : CModel_task::TYPE_GENERATE, $_POST['settings']);
    //---
    CLogger::write(CLoggerType::DEBUG, "task saved");
    //---
    echo "success";
    exit;
    }

  /**
   * Остановка пакетной генерации
   */
  private function OnStopTask()
    {
    $this->m_model->StopTask();
    //---
    CLogger::write(CLoggerType::DEBUG, "tasks stopped by action OnStopTask");
    //--- редиректим на страницу со списком тасков
    header("location: ?module=upload");
    exit;
    }

  /**
   *
   * Отображение всех задач
   */
  private function OnClearTask()
    {
    CLogger::write(CLoggerType::DEBUG, "clear task begin");
    $this->m_model->ClearTask();
    CLogger::write(CLoggerType::DEBUG, "clear task finished");
    //--- редиректим на страницу со списком тасков
    header("location: ?module=upload");
    exit;
    }

  /**
   * Запуск выполения тасков в фоновом режиме
   * через сокеты
   */
  private function OnRunTasks()
    {
    CLogger::write(CLoggerType::DEBUG, "try task starting");
    //--- заранее создадим файл start.php
    $this->m_model->StartTask();
    //---
    $host   = $_SERVER['HTTP_HOST'];
    $cookie = trim(preg_replace("/PHPSESSID=[a-z0-9]{1,}/", '', $_SERVER['HTTP_COOKIE']), '; ');
    //--- для iis
    if(!empty($_SERVER['REQUEST_URI']))
      {
      $urlArray = parse_url($_SERVER['REQUEST_URI']);
      }
    else
      {
      $dorgen_url = trim($_SERVER['HTTP_HOST'], "/") . '/' . trim($_SERVER["SCRIPT_NAME"], "/");
      if(!CModel_helper::IsExistHttp($dorgen_url)) $dorgen_url = 'http://' . $dorgen_url;
      $urlArray = parse_url($dorgen_url);
      }
    for($i = 0; $i < CModel_UploadTask::THREADS_COUNT; $i++)
      {
      $path = $urlArray['path'] . '?module=upload&id=' . $i . '&' . urlencode('a[starttask]');
      //---
      $fp = fsockopen($host, 80);
      //--- установим тип сокетов (блокируемые сокеты)
      stream_set_blocking($fp, 1);
      //--- установим таймаут на 24 часа
      stream_set_timeout($fp, 86400);
      CLogger::write(CLoggerType::DEBUG, 'start upload id: ' . $i . ', path: ' . $path . ' ' . $cookie);
      //--- вызываем скрипт, передавая ему необходимые переменные
      fwrite($fp, "GET {$path} HTTP/1.1\r\n" . "Host: {$host}\r\n" . "Cookie: {$cookie}\r\n" . "Connection: close\r\n\r\n");
      //--- ждем 3 секунды, чтобы данные успели отправиться
      sleep(3);
      //--- закрываем коннект, а запущенный скрипт будет продолжать выполняться
      fclose($fp);
      }
    //--- редиректим на страницу со списком тасков
    header("location: ?module=upload");
    exit;
    }

  /**
   * Фоновое выполнение скрипта на PHP без crontab
   */
  public function OnStartTask()
    {
    if(!isset($_REQUEST['id']))
      {
      CLogger::write(CLoggerType::ERROR, "no thread for upload task");
      return;
      }
    $id   = (int)$_REQUEST['id'];
    $list = $this->m_model->GetListTask($id);
    //--- http://veselov.sumy.ua/blog/php/page/4/
    ignore_user_abort(1); // Игнорировать обрыв связи с браузером
    set_time_limit(0); // Время работы скрипта неограниченно
    //ob_start();
    //---
    //echo 'Выполенение задач началось. Можете закрыть браузер.';
    //ob_end_flush();
    //ob_flush();
    //---
    CLogger::write(CLoggerType::DEBUG, "upload task starting");
    //---
    if(empty($list))
      {
      CLogger::write(CLoggerType::ERROR, "no task for start");
      return;
      }
    //---
    //--- старт задач
    $this->m_model->StartTask($id);
    $model_translate = new CModel_translate();
    //---
    foreach($list as $file_task)
      {
      if($this->m_model->IsStopTask())
        {
        //--- Остановка скрипта, работающего в фоновом режиме
        break;
        }
      //---
      $full_file_task = CModel_UploadTask::PATH . $id . '/' . $file_task;
      $task_info      = $this->m_model->GetTask($full_file_task);
      if(empty($task_info))
        {
        //$this->m_model->WriteLog('not found task: ' . $file_task);
        CLogger::write(CLoggerType::DEBUG, "task not found " . $file_task);
        continue;
        }
      //--- нужно ли запускать задачу
      if($task_info['status'] == CModel_UploadTask::STATUS_FINISH)
        {
        continue;
        }
      //--- изменить статус на выполнение
      $this->m_model->UpdateStatusStarting($id, $task_info);
      //--- записи в лог
      CLogger::write(CLoggerType::DEBUG, 'upload task: ' . $id . ':' . $file_task . ' - ' . $task_info['number'] . ' - ' . date("d.m.Y H:i", $task_info['date_create']) . ' start');
      //$this->m_model->WriteLog($file_task . ' - ' . $task_info['number'] . ' - ' . date("d.m.Y H:i", $task_info['date_create']) . ' start');
      //--- создаем модель, один раз, т.к. в конструкторе загрузка default
      //---
      CLogger::write(CLoggerType::DEBUG, 'task: ' . $id . ':' . $file_task . ' - ' . $task_info['number'] . ' - ' . date("d.m.Y H:i", $task_info['date_create']) . ' Loaded settings');
      //---
      $settings = $task_info['task'];
      $curl     = new CModel_ftpCurl('', $settings['ftpServer'],!empty($settings['ftpPort'])?$settings['ftpPort']:21, $settings['ftpLogin'], $settings['ftpPassword'], $settings['ftpPath'], CModel_UploadTask::PATH . $id . '/' . $task_info['pathname'], true);
      if($curl->Start(true))
        {
        //--- закончили
        $this->m_model->UpdateStatusFinish($id, $task_info);
        CLogger::write(CLoggerType::DEBUG, 'task: ' . $id . ':' . $file_task . ' - ' . $task_info['number'] . ' - ' . date("d.m.Y H:i", $task_info['date_create']) . ' finished');
        }
      else
        {
        //--- закончили, но не все удачно залилось
        $this->m_model->UpdateStatusFailedFinish($id, $task_info);
        CLogger::write(CLoggerType::DEBUG, 'task: ' . $id . ':' . $file_task . ' - ' . $task_info['number'] . ' - ' . date("d.m.Y H:i", $task_info['date_create']) . ' failed finished');
        }
      }
    //--- все задачи выполнили
    $this->m_model->FinishedTask($id);
    //---
    CLogger::write(CLoggerType::DEBUG, 'upload task: ' . $id . ': finished');
    }

  /**
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
   * Удаление задач
   * @param $url
   */
  public function OnDeleteTasks($url)
    {
    $model = $this->GetModelTask();
    $ids   = explode(',', $_REQUEST['task_ids']);
    foreach($ids as $id)
      {
      if(empty($id)) continue;
      //---
      $ar = explode('_', $id);
      if($model->Delete((int)$ar[0], (int)$ar[1]))
        {
        CLogger::write(CLoggerType::DEBUG, "task deleted " . $id);
        }
      }
    //---
    header("location: ?module=upload");
    exit;
    }

  /**
   * Повторение задачи
   * @param $url
   */
  public function OnRepeatTasks($url)
    {
    $model = $this->GetModelTask();
    $ids   = explode(',', $_REQUEST['task_ids']);
    foreach($ids as $id)
      {
      if(empty($id)) continue;
      //---
      $ar = explode('_', $id);
      $task_info = $model->GetTaskById((int)$ar[0], (int)$ar[1]);
      if(empty($task_info))
        {
        CLogger::write(CLoggerType::ERROR, "task not found by id " . $id);
        continue;
        }
      //---
      $task_info['status'] = CMOdel_task::STATUS_BEGIN;
      if($this->m_model->UpdateTask((int)$ar[0],$task_info)) CLogger::write(CLoggerType::DEBUG, "task update status BEGIN " . $id . ' ' . $task_info['task']['nextUrl']);
      }
    //---
    header("location: ?module=upload");
    exit;
    }
  }

?>
