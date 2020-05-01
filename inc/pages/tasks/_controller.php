<?php
class CTasks extends IPage
  {
  private $m_settings;
  //--- переводы
  protected static $m_translate = array('ru' => array('main_title'             => 'Задания для пакетной генерации дорвеев',
                                                      'b_task_generate'        => 'Задания для пакетной генерации дорвеев',
                                                      'b_task_list_empty'      => 'Список заданий пуст',
                                                      'b_task_start'           => 'Запустить',
                                                      'b_task_description_add' => 'Описание вида<br>
            <b>global_settings1.txt | text1.txt | keys1.txt | ./local/path | http://site.com/pharma/viagra</b> <br>
            для фтп строчка будет такая:<br>
            <b>global_settings1.txt | text1.txt | keys1.txt | ftp://user:password@server.ru/path | http://site.com/pharma/viagra </b>',
                                                      'b_settings'             => 'Настройки:',
                                                      'b_setting_standart'     => 'Стандартные',
                                                      'b_task_add_list'        => 'Добавить в список задач',
                                                      'b_task_delete'          => 'Удалить все задания',
                                                      'b_tasks_add'            => 'Пакетное добавление',
                                                      'b_translate'            => 'Перевод',
                                                      'b_delete_tasks'         => 'удалить',
                                                      'b_repeat_tasks'         => 'повторить задания',
                                                      'b_begin_generating'     => 'Выполняется генерация дорвеев...',
                                                      'b_update'               => 'Обновить',
                                                      'b_are_sure'             => 'Вы уверены?',
                                                      'b_create_site'          => 'Создать сайт',
                                                      'b_ftp_server'           => 'на FTP-сервере',
                                                      'b_this_server'          => 'на этом сервере',
                                                      'b_ftp_server_name'      => 'FTP-сервер',
                                                      'b_path_name'            => 'Папка',
                                                      'b_keywords_name'        => 'Кейворды',
                                                      'b_text_name'            => 'Тексты',
                                                      'b_settings_name'        => 'Настройки',
                                                      'b_add_name'             => 'Добавить',
                                                      'b_server_name'          => 'Сервер',
                                                      'b_date_name'            => 'Дата',
                                                      'b_status_name'          => 'Статус',
                                                      'b_total'                => 'Всего',
                                                      'b_sure_delete_tasks'    => 'Вы точно хотите удалить задачи?',
                                                      'b_date_end_name'        => 'Окончание',
                                                      'b_load_ftps'            => 'Загрузка файла с доступом по ftp',
                                                      'b_load_ftps_title'      => 'Формат каждой строчки в файле: url|ftp://login:password@ftp.example.com'),
    //---
                                        'en' => array('main_title'             => 'Tasks for batch generation of doorways',
                                                      'b_task_generate'        => 'Tasks for batch generation of doorways',
                                                      'b_task_list_empty'      => 'List of task is empty',
                                                      'b_task_start'           => 'Run',
                                                      'b_task_description_add' => 'Following descriptions<br>
            <b>global_settings1.txt | text1.txt | keys1.txt | ./local/path | http://site.com/pharma/viagra</b> <br>
            if you want upload by ftp:<br>
            <b>global_settings1.txt | text1.txt | keys1.txt | ftp://user:password@server.ru/path | http://site.com/pharma/viagra </b>',
                                                      'b_settings'             => 'Settings:',
                                                      'b_setting_standart'     => 'Standarts',
                                                      'b_task_add_list'        => 'Add to task list',
                                                      'b_task_delete'          => 'Task delete',
                                                      'b_tasks_add'            => 'Add package of tasks',
                                                      'b_translate'            => 'Translate',
                                                      'b_delete_tasks'         => 'delete',
                                                      'b_repeat_tasks'         => 'repeat',
                                                      'b_begin_generating'     => 'Websites are creating...',
                                                      'b_update'               => 'Update',
                                                      'b_are_sure'             => 'Are you sure?',
                                                      'b_create_site'          => 'Create website',
                                                      'b_ftp_server'           => 'on FTP-server',
                                                      'b_this_server'          => 'on current server',
                                                      'b_ftp_server_name'      => 'FTP-server',
                                                      'b_path_name'            => 'Path',
                                                      'b_keywords_name'        => 'Keywords',
                                                      'b_text_name'            => 'Texts',
                                                      'b_settings_name'        => 'Settings',
                                                      'b_add_name'             => 'Add',
                                                      'b_server_name'          => 'Server',
                                                      'b_date_name'            => 'Date add',
                                                      'b_status_name'          => 'Status',
                                                      'b_total'                => 'Total',
                                                      'b_sure_delete_tasks'    => 'Are you sure you want to delete task?',
                                                      'b_date_end_name'        => 'Date end',
                                                      'b_load_ftps'            => 'Upload file with ftp servers',
                                                      'b_load_ftps_title'      => 'Format each line: url|ftp://login:password@ftp.example.com'));
  //---
  private $m_list_tasks;
  private $m_model;

  /**
   * Конструктор
   */
  public function __construct()
    {
    global $IS_CRYPT;
    $this->m_settings         = new CModel_settings();
    $this->m_current_settings = $this->m_settings->LoadCurrentSettings();
    $this->SetTitle(CTasks::GetTranslate('main_title'));
    $this->m_model = new CModel_task();
    }

  /**
   * Отображение доверя
   * @see IPage::Show()
   */
  public function Show($path = null)
    {
    $this->m_list_tasks = $this->m_model->GetListTask();
    //---
    include("./inc/pages/tasks/index.phtml");
    }

  /**
   *
   * Список задач
   */
  public function GetListTask()
    {
    return $this->m_list_tasks;
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
    header("location: ?module=tasks");
    exit;
    }

  /**
   *
   * Отображение всех задач
   */
  private function OnViewTask()
    {
    $list = $this->m_model->GetListTask();
    echo '<h2>Tasks</h2>';
    if(empty($list))
      {
      echo '<strong>No tasks</strong>';
      return;
      }
    //---
    foreach($list as $fname)
      {
      $info = $this->m_model->GetTask($fname);
      if(empty($info)) continue;
      //---
      echo '<div class="task-info">', $info['number'], ' - ', date("d.m.Y H:i", $info['date_create']) . ' - ', CModel_task::GetStatusName($info['status']), '</div>';
      }
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
    echo 'Задачи удалены';
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
    $path = $urlArray['path'] . '?module=tasks&' . urlencode('a[starttask]');
    //---
    $fp = fsockopen($host, 80);
    //--- установим тип сокетов (блокируемые сокеты)
    stream_set_blocking($fp, 1);
    //--- установим таймаут на 24 часа
    stream_set_timeout($fp, 86400);
    CLogger::write(CLoggerType::DEBUG, $path . ' ' . $cookie);
    //--- вызываем скрипт, передавая ему необходимые переменные
    fwrite($fp, "GET {$path} HTTP/1.1\r\n" . "Host: {$host}\r\n" . "Cookie: {$cookie}\r\n" . "Connection: close\r\n\r\n");
    //--- ждем 3 секунды, чтобы данные успели отправиться
    sleep(3);
    //--- закрываем коннект, а запущенный скрипт будет продолжать выполняться
    fclose($fp);
    //--- редиректим на страницу со списком тасков
    header("location: ?module=tasks");
    exit;
    }

  /**
   * Фоновое выполнение скрипта на PHP без crontab
   */
  private function OnStartTask()
    {
    global $IS_CRYPT;
    $IS_CRYPT = false;
    $list     = $this->m_model->GetListTask();
    //--- http://veselov.sumy.ua/blog/php/page/4/
    ignore_user_abort(1); // Игнорировать обрыв связи с браузером
    set_time_limit(0); // Время работы скрипта неограниченно
    //ob_start();
    //---
    //echo 'Выполенение задач началось. Можете закрыть браузер.';
    //ob_end_flush();
    //ob_flush();
    //---
    CLogger::write(CLoggerType::DEBUG, "task starting");
    //---
    if(empty($list))
      {
      CLogger::write(CLoggerType::DEBUG, "no task for start");
      return;
      }
    //--- старт задач
    $this->m_model->StartTask();
    $model_translate = new CModel_translate();
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
        //$this->m_model->WriteLog('not found task: ' . $file_task);
        CLogger::write(CLoggerType::DEBUG, "task not found " . $file_task);
        continue;
        }
      //--- нужно ли запускать задачу
      if($task_info['status'] == CModel_task::STATUS_FINISH)
        {
        continue;
        }
      //--- изменить статус на выполнение
      $this->m_model->UpdateStatusStarting($task_info);
      //--- записи в лог
      CLogger::write(CLoggerType::DEBUG, 'task: ' . $file_task . ' - ' . $task_info['number'] . ' - ' . date("d.m.Y H:i", $task_info['date_create']) . ' start');
      //$this->m_model->WriteLog($file_task . ' - ' . $task_info['number'] . ' - ' . date("d.m.Y H:i", $task_info['date_create']) . ' start');
      //--- создаем модель, один раз, т.к. в конструкторе загрузка default
      $settings_model = new CModel_settings();
      //--- сама генерация
      CLogger::write(CLoggerType::DEBUG, 'task: generate... #' . $task_info['number']);
      //---
      if(empty($task_info['settings']) || (isset($task_info['task']['settingsFromFile']) && $task_info['task']['settingsFromFile'] == 'on'))
        {
        CLogger::write(CLoggerType::DEBUG, 'task: ' . $file_task . ' - ' . $task_info['number'] . ' - ' . date("d.m.Y H:i", $task_info['date_create']) . ' Loaded settings');
        $settings = $task_info['task'];
        }
      else
        {
        //--- пытаемся загрузить настройки
        CLogger::write(CLoggerType::DEBUG, 'task: ' . $file_task . ' - ' . $task_info['number'] . ' - ' . date("d.m.Y H:i", $task_info['date_create']) . ' Loaded settings from ' . $task_info['settings']);
        $settings = $settings_model->Load($task_info['settings']);
        //--- проверка
        if(empty($settings))
          {
          //--- файла с настройками не существует, то загружаем те настройки, которые есть
          CLogger::write(CLoggerType::DEBUG, 'task: no settings in ' . $task_info['settings'] . ' loaded ' . $file_task . ' - ' . $task_info['number'] . ' - ' . date("d.m.Y H:i", $task_info['date_create']));
          $settings = $task_info['task'];
          }
        //--- настройки из верхней части формы берем из сохраненных в задачу
        $settings_model->ChangeMainSettingsFrom($task_info['task'], $settings);
        }
      $generator = new CModel_generator($settings, array(), false);
      $generator->Start();
      //--- закончили
      $this->m_model->UpdateStatusFinish($task_info);
      CLogger::write(CLoggerType::DEBUG, 'task: ' . $file_task . ' - ' . $task_info['number'] . ' - ' . date("d.m.Y H:i", $task_info['date_create']) . ' finished');
      //$this->m_model->WriteLog('task: ' . $file_task . ' - ' . $task_info['number'] . ' - ' . date("d.m.Y H:i", $task_info['date_create']) . ' finished');
      }
    //--- все задачи выполнили
    $this->m_model->FinishedTask();
    //---
    CLogger::write(CLoggerType::DEBUG, "task finished");
    }

  /**
   * ПОлучение настроек
   */
  private function GetSettings()
    {
    return $this->m_settings;
    }

  /**
   * Пакетное добавление задач
   */
  private function OnPacketAdd()
    {
    ini_set('max_execution_time', 0);
    ini_set('set_time_limit', 0);
    CLogger::write(CLoggerType::DEBUG, "task packet adding");
    //---
    $lines = explode("\n", $_POST['taskList']);
    //--- по строчно будет обрабатывать строчки
    foreach($lines as $line_info)
      {
      if(empty($line_info)) continue;
      //--- может это комментарий
      if($line_info[0] == ';')
        {
        CLogger::write(CLoggerType::DEBUG, "task packet comment: " . $line_info);
        continue;
        }
      //---  обработка строки
      $d = $this->m_model->GetDataFromString($line_info);
      if(empty($d))
        {
        CLogger::write(CLoggerType::ERROR, "task packet invalid line: " . $line_info);
        continue;
        }
      //--- загружаем настройки
      $settings = $this->m_settings->Load($d['settings']);
      //---
      if(empty($settings))
        {
        CLogger::write(CLoggerType::DEBUG, "failed task add, settings not found: " . $d['settings']);
        continue;
        }
      //--- сайт
      $settings['nextUrl'] = $d['nextUrl'];
      //--- ключи брать только из файлов который находится в PATH_KEYWORDS
      $settings['keysFrom'] = 'list';
      $settings['textFrom'] = 'list';
      //--- имена файлов откуда брать данные
      $settings['keysFromList'] = $d['keywords'];
      $settings['textFromList'] = $d['text'];
      //--- поменяем настройки в зависимости от распарсенных данных
      $settings['uploadTo'] = $d['uploadTo'];
      //--- данные для фтп
      if($settings['uploadTo'] == 'ftp')
        {
        $settings['ftpServer']   = isset($d['ftpServer']) ? $d['ftpServer'] : null;
        $settings['ftpLogin']    = isset($d['ftpLogin']) ? $d['ftpLogin'] : null;
        $settings['ftpPassword'] = isset($d['ftpPassword']) ? $d['ftpPassword'] : null;
        $settings['ftpPath']     = isset($d['ftpPath']) ? $d['ftpPath'] : null;
        } //--- для локального дорвея
      else
        {
        $settings['localPath'] = $d['path'];
        }
      $settings['settingsFromFile'] = 'on';
      //--- готово, данные можно добавлять в новое задание
      $this->m_model->SaveTask($settings, CModel_task::TYPE_GENERATE, $d['settings']);
      CLogger::write(CLoggerType::DEBUG, "task added " . $settings['nextUrl'] . ', ' . $settings['uploadTo'] . ', ' . $settings['keysFromList'] . ', ' . $settings['textFrom']);
      }
    //---
    header("location: ?module=tasks");
    exit;
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
      if($model->Delete($id))
        {
        CLogger::write(CLoggerType::DEBUG, "task deleted " . $id);
        }
      }
    //---
    header("location: ?module=tasks");
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
      $task_info = $model->GetTaskById($id);
      if(empty($task_info))
        {
        CLogger::write(CLoggerType::ERROR, "task not found by id " . $id);
        continue;
        }
      //---
      $task_info['status'] = CMOdel_task::STATUS_BEGIN;
      if($this->m_model->UpdateTask($task_info)) CLogger::write(CLoggerType::DEBUG, "task update status BEGIN " . $id . ' ' . $task_info['task']['nextUrl']);
      }
    //---
    header("location: ?module=tasks");
    exit;
    }

  /**
   * @param $url
   */
  public function OnUploadFtps($url)
    {
    $fname = $_FILES['ftps']['tmp_name'];
    if(!file_exists($fname)) return;
    //---
    $fp = fopen($fname, 'r');
    if(!$fp) return;
    //---
    while(!feof($fp))
      {
      $buffer = fgets($fp, 4096);
      if(empty($buffer)) continue;
      //---
      $ar = explode('|', trim($buffer), 2);
      echo $ar[0] . '|' . $ar[1] . '|./|' . $_REQUEST['taskKeys'] . '|' . $_REQUEST['taskTexts'] . '|' . $_REQUEST['taskSettings'] . "\r\n";
      }
    //---
    fclose($fp);
    unlink($fname);
    exit;
    }
  }

?>
