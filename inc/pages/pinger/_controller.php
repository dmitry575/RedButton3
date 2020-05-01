<?php
/**
 * Pinger
 */
class CPinger extends IPage
  {
  /**
   * Настройки
   * @var CModel_settings
   */
  private $m_settings;
  /**
   * CModel_Pinger
   * @var CModel_Pinger
   */
  private $m_model;
  //--- переводы
  protected static $m_translate = array('ru' => array('main_title'                      => 'Пингер сервисов',
                                                      'b_tasks_add'                     => 'Добавить задачи',
                                                      'b_view_servers'                  => 'Ping сервисы',
                                                      'b_view_xmlrpc_servers'           => 'XML-RPC сервисы',
                                                      'b_url'                           => 'Url',
                                                      'b_date_create'                   => 'Дата добавления',
                                                      'b_date_last'                     => 'Последняя активность',
                                                      'b_count_invalids'                => 'Количество ошибок',
                                                      'b_services_add'                  => 'Добавить сервисы',
                                                      'b_task_add_description'          => 'Добавление урлов, которые необходимо пинговать. На одной строчке один адрес сайта',
                                                      'b_task_add_list'                 => 'Добавить список',
                                                      'b_services_add_description'      => 'Добавляем список сервисов. Один адрес в строчке.<br>Возможные макросы:<br>[url] - будет подставляться адрес дорвея вида http://getredbutton.com<br>[host] -  будет подставляться хост дорвея вида getredbutton.com',
                                                      'b_services_add_list'             => 'Добавить список',
                                                      'b_servers_save_success'          => 'Сервера сохранены успешно',
                                                      'b_servers_save_error'            => 'При сохранении произошла ошибка',
                                                      'b_task_list_empty'               => 'Список заданий пуст',
                                                      'b_date'                          => 'Дата создания',
                                                      'b_status'                        => 'Стаутс',
                                                      'b_stop'                          => 'Остановить',
                                                      'b_refresh'                       => 'Обновить',
                                                      'b_delete_images'                 => 'удалить',
                                                      'b_repeat_images'                 => 'повторить задания',
                                                      'b_task_start'                    => 'Запустить',
                                                      'b_tasks_pings'                   => 'Список задач для Ping',
                                                      'b_tasks_xml'                     => 'Список задач для XML-RPC (пинг блогов)',
                                                      'b_task_delete'                   => 'Удалить все задачи',
                                                      'b_are_sure_delete'               => 'Точно хотите удалить все задачи?',
                                                      'b_delete_servers'                => 'Удалить сервисы',
                                                      'b_are_sure_delete_servers'       => 'Точно хотите удалить сервисы?',
                                                      'b_total'                         => 'Всего',
                                                      'b_repeat_tasks'                  => 'повторить',
                                                      'b_delete_tasks_selected'         => 'Удалить выделенные',
                                                      'b_xmlrpc_add_description'        => 'Сервисы для XML-RPC пинга',
                                                      'b_view_servers_title'            => 'Показать все пинг сервера',
                                                      'b_view_xmlrpc_servers_title'     => 'Показать все XML-RPC сервера',
                                                      'b_services_add_title'            => 'Добавление новых сервисов для пинга',
                                                      'b_settings'                      => 'Настройки',
                                                      'b_settings_description'          => 'Настройки для пингера серверов',
                                                      'b_settings_threads'              => 'Максимальное количество потоков отправки запросов к пинг серверам',
                                                      'b_settings_pause'                => 'Пауза между запросами в секундах',
                                                      'b_settings_min_pause'            => 'от',
                                                      'b_settings_max_pause'            => 'до',
                                                      'b_settings_save'                 => 'Сохранить',
                                                      'b_task_add_urls'                 => 'Добавить урлы для пингатора',
                                                      'b_task_send_xml'                 => 'Отправлять урлы в XML-RPC сервисы (для ускорения индексации)',
                                                      'b_task_send_get'                 => 'Отправлять урлы в Ping сервисы (для получения обратных ссылок)',
                                                      'b_task_add_list_to'              => 'Добавить в задачи',
                                                      'b_task_pings_repeat'             => 'После завершения повторить',
                                                      'b_task_pings_random'             => 'Урлы пинговать рандомно',
                                                      'b_task_add_file_description'     => 'Загрузки файл со списком урлов для пингования',
                                                      'b_working_url'                   => 'В работе урл',
                                                      'b_settings_delete_title'         => 'Зачистка списка сервисов, в зависимости от количества ошибок, 40x - все HTTP ошибки больше 400 и меньше 500, 50x - все HTTP ошибки больше 500',
                                                      'b_settings_delete_40x'           => 'Удалять если 40х ошибок больше: ',
                                                      'b_settings_delete_50x'           => 'Удалять если 50х ошибок больше: ',
                                                      'b_settings_proxy_title'          => 'Использование прокси серверов при отправке ping запросов, прокси сервера задаются на <a href="?module=settings">странице настроек</a>',
                                                      'b_pings_proxy'                   => 'Использовать прокси при обычных pings запросах',
                                                      'b_xml_proxy'                     => 'Использовать прокси при отправки XML-RPC запросов',
                                                      'b_settings_save_success'         => 'Настройки сохранены',
                                                      'b_settings_save_error'           => 'При сохранении настроек произошла ошибка',
                                                      'b_count_invalids_40x'            => '40x ошибки',
                                                      'b_count_invalids_50x'            => '50x ошибки',
                                                      'b_settings_delete_40x_more'      => 'ошибок должно быть больше 10',
                                                      'b_settings_delete_50x_more'      => 'ошибок должно быть больше 10',
                                                      'b_ping_add_description'          => 'Сервисы для ping',
                                                      'b_task_add_urls_pings'           => 'Урлы для ping',
                                                      'b_task_add_urls_xml'             => 'Урлы для XML-RPC ping',
                                                      'b_task_add_file_description_xml' => 'Загрузки файл со списком урлов для пингования, формат: url | url на страницу | url на rss | название блога. <br>Название блога может быть в формате [keywords.txt] - название берется из файла. <br>Пример: http://www.example.com/blog/ | http://www.example.com/blog/new-post.html | http://www.example.com/blog/feed.xml | Example Blog ',
                                                      'b_task_add_description_xml'      => 'Добавление урлов, которые необходимо пинговать. Формат: url | url на страницу | url на rss | название блога. <br>Название блога может быть в формате [keywords.txt] - название берется из файла. <br>Пример: http://www.example.com/blog/ | http://www.example.com/blog/new-post.html | http://www.example.com/blog/feed.xml | Example Blog',
                                                      'b_settings_delete_day'           => 'Удалять, если прошло дней: ',
                                                      'b_settings_delete_day_more'      => '  с последний успешной отправки. Должно выполняться хотя бы одно из условий по ошибкам, т.е. если установить только количество дней, без указания ошибок, то ничего удаляться не будет',
                                                      'b_date_last_success'             => 'Дата успешной отправки',
                                                      'b_working'                       => 'Процесс пингования запущен',
                                                      'b_url_rss'                       => 'Url на rss',
                                                      'b_url_post'                      => 'Url на пост',
                                                      'b_url_title'                     => 'Название блога',
                                                      'b_pingers_task_added'            => 'Добавлено урлов: ',),
    //---
                                        'en' => array('main_title'                      => 'Pinger',
                                                      'b_tasks_add'                     => 'Tasks add',
                                                      'b_view_servers'                  => 'Ping services',
                                                      'b_view_xmlrpc_servers'           => 'XML-RPC services',
                                                      'b_url'                           => 'Url',
                                                      'b_date_create'                   => 'Date create',
                                                      'b_date_last'                     => 'Date last',
                                                      'b_count_invalids'                => 'Count errors',
                                                      'b_services_add'                  => 'Add services',
                                                      'b_task_add_description'          => 'You can add many sites wich need send to pingers servrices. One address website in wich line',
                                                      'b_task_add_list'                 => 'Add list',
                                                      'b_services_add_description'      => 'You can add list of services for ping. One url in wich line.<br>Macros:<br>[url] - url website, for example "http://getredbutton.com"<br>[host] -  host website, for example "getredbutton.com"',
                                                      'b_services_add_list'             => 'Add list',
                                                      'b_servers_save_success'          => 'Services saved success',
                                                      'b_servers_save_error'            => 'Saving services error',
                                                      'b_date'                          => 'Date create',
                                                      'b_status'                        => 'Status',
                                                      'b_stop'                          => 'Stop',
                                                      'b_refresh'                       => 'Refresh',
                                                      'b_delete_images'                 => 'delete',
                                                      'b_repeat_images'                 => 'repeat',
                                                      'b_task_start'                    => 'Start',
                                                      'b_tasks_pinger'                  => 'List tasks Pings',
                                                      'b_tasks_xml'                     => 'List tasks XML-RPC (ping blogs)',
                                                      'b_task_delete'                   => 'Delete all tasks',
                                                      'b_are_sure_delete'               => 'Are you sure you want to delete all tasks?',
                                                      'b_delete_servers'                => 'Services delete',
                                                      'b_are_sure_delete_servers'       => 'Are you sure you want delete services?',
                                                      'b_total'                         => 'Total',
                                                      'b_repeat_tasks'                  => 'repeat',
                                                      'b_delete_tasks_selected'         => 'Delete selected',
                                                      'b_xmlrpc_add_description'        => 'Websites for XML-RPC ping',
                                                      'b_view_servers_title'            => 'View all servers for ping',
                                                      'b_view_xmlrpc_servers_title'     => 'View all servers for ping XML-RPC',
                                                      'b_services_add_title'            => 'Add new servers for ping',
                                                      'b_settings'                      => 'Settings',
                                                      'b_settings_description'          => 'Settings for pinger services',
                                                      'b_settings_threads'              => 'Max count threads',
                                                      'b_settings_pause'                => 'Pause between request, seconds',
                                                      'b_settings_min_pause'            => 'min',
                                                      'b_settings_max_pause'            => 'max',
                                                      'b_settings_save'                 => 'Save',
                                                      'b_task_add_urls'                 => 'Add urls to pinger',
                                                      'b_tasks_pings'                   => 'List task for Ping',
                                                      'b_task_send_xml'                 => 'Send urls to XML-RPC services',
                                                      'b_task_send_get'                 => 'Send urls to Ping services (for getting back links)',
                                                      'b_task_add_list_to'              => 'Add to task',
                                                      'b_task_pings_repeat'             => 'After finish repeat again',
                                                      'b_task_pings_random'             => 'Get url for ping random',
                                                      'b_task_add_file_description'     => 'Upload file with list of urls',
                                                      'b_working_url'                   => 'Now url is sending',
                                                      'b_settings_delete_title'         => 'Clear list of services,depending on the number of errors HTTP',
                                                      'b_settings_delete_40x'           => 'Service delete if HTTP error 40х more than: ',
                                                      'b_settings_delete_50x'           => 'Service delete if HTTP error 50х more than: ',
                                                      'b_settings_proxy_title'          => 'Using proxy services using, list proxies to <a href="?module=settings">settings page</a>',
                                                      'b_pings_proxy'                   => 'Using proxy send ping request',
                                                      'b_xml_proxy'                     => 'Using proxy send XML-RPC request',
                                                      'b_settings_save_success'         => 'Settings saved',
                                                      'b_settings_save_error'           => 'Saving settings failed',
                                                      'b_count_invalids_40x'            => '40x errors',
                                                      'b_count_invalids_50x'            => '50x errors',
                                                      'b_settings_delete_40x_more'      => 'errors must be more than 10',
                                                      'b_settings_delete_50x_more'      => 'errors must be more than 10',
                                                      'b_ping_add_description'          => 'Services fro simple ping',
                                                      'b_task_add_urls_pings'           => 'Urls for ping',
                                                      'b_task_add_urls_xml'             => 'Urls for XML-RPC ping',
                                                      'b_task_add_file_description_xml' => 'Upload file with list of urls, format : url | url post | url rss | title site. <br>Title site can format: [keywords.txt] - title site get from file. Examle: http://www.example.com/blog/ | http://www.example.com/blog/new-post.html | http://www.example.com/blog/feed.xml | Example Blog',
                                                      'b_task_add_description_xml'      => 'You can add many sites wich need send to pingers servrices. Format : url | url post | url rss | title site. <br>Title site can format: [keywords.txt] - title site get from file. Examle: http://www.example.com/blog/ | http://www.example.com/blog/new-post.html | http://www.example.com/blog/feed.xml | Example Blog',
                                                      'b_settings_delete_day'           => 'Service delete if days error: ',
                                                      'b_settings_delete_day_more'      => '  after last success sent date. Must be carried out at least one of the conditions for the errors (40x or 50x).  If you set only the number of days without errors, then nothing will not be removed',
                                                      'b_date_last_success'             => 'Last success date',
                                                      'b_working'                       => 'Process starting',
                                                      'b_url_rss'                       => 'Rss url',
                                                      'b_url_post'                      => 'Post url',
                                                      'b_url_title'                     => 'Blogs title',
                                                      'b_pingers_task_added'            => 'Urls added: ',));

  //---
  /**
   * Конструктор
   */
  public function __construct()
    {
    $this->SetTitle(self::GetTranslate('main_title'));
    //---
    $this->m_model = new CModel_Pinger();
    $this->m_model->Init();
    }

  /**
   * Зачистка сессий
   */
  private function ClearSession()
    {
//--- данные в сессиях
    if(isset($_SESSION['pinger_saveservers'])) unset($_SESSION['pinger_saveservers']);
    if(isset($_SESSION['pinger_savesettings'])) unset($_SESSION['pinger_savesettings']);
    if(isset($_SESSION['pings_task_added'])) unset($_SESSION['pings_task_added']);
    }

  /**
   * Отображение pingers
   * @see IPage::Show()
   */
  public function Show($path = null)
    {
    global $LNG;
    //--- c какой страницей работать ping или xml
    $name = "ping";
    if(isset($_REQUEST['name'])) $name = $_REQUEST['name'];
    //---
    if(!empty($_REQUEST['act']))
      {
      switch($_REQUEST['act'])
      {
        case 'viewservers':
          //---
          $this->m_model->Init();
          $this->m_list_servers = $this->m_model->GetServers();
          include("./inc/pages/pinger/header.phtml");
          include("./inc/pages/pinger/list.phtml");
          $this->ClearSession();
          return;
        case 'rpcviewservers':
          $this->m_model->Init();
          $this->m_list_servers = $this->m_model->GetXmlrpcServers();
          include("./inc/pages/pinger/header.phtml");
          include("./inc/pages/pinger/list_xmlrpc.phtml");
          $this->ClearSession();
          return;
        //---
        case 'tasksadd':
          include("./inc/pages/pinger/header.phtml");
          include("./inc/pages/pinger/tasksadd.phtml");
          $this->ClearSession();
          return;
        //---
        case 'serversadd':
          include("./inc/pages/pinger/header.phtml");
          include("./inc/pages/pinger/serversadd.phtml");
//--- данные в сессиях
          $this->ClearSession();
          return;
        //---
        case 'settings':
          include("./inc/pages/pinger/header.phtml");
          include("./inc/pages/pinger/settings.phtml");
//--- данные в сессиях
          $this->ClearSession();
          return;
      }
      }
    //---
    include("./inc/pages/pinger/header.phtml");
    if($name == 'xml')
      {
      //--- получим список текущих задач
      $this->m_list_task = $this->m_model->GetListTaskXML();
      include("./inc/pages/pinger/index_xml.phtml");
      }
    else
      {
      $this->m_list_task = $this->m_model->GetListTaskPings();
      include("./inc/pages/pinger/index.phtml");
      }
    $this->ClearSession();
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
    return '[' . $name . ']';
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
  private function OnServersAdd($url)
    {
    $this->m_model->Init();
    if($this->m_model->AddMany($_POST['urls'], $_POST['xmlprc_urls']))
      {
      CLogger::write(CLoggerType::DEBUG, CModel_Pinger::PREFIX_LOG . " servers saved");
      //---
      $_SESSION['pinger_saveservers'] = 1;
      }
    else
      {
      //---
      $_SESSION['pinger_saveservers'] = -1;
      }
    //---
    if($_SESSION['pinger_saveservers'] == 1)
      {
      if(!empty($_POST['urls']))
        {
        header("location: ?module=pinger&act=viewservers");
        exit;
        }
      //---
      header("location: ?module=pinger&act=rpcviewservers");
      exit;
      }
    //--- редиректим на страницу со списком задач
    header("location: ?module=pinger&act=serversadd");
    exit;
    }

  /**
   *
   * Удаление серверов
   * @param array $url
   */
  private function OnDeleteServers($url)
    {
    if(!empty($_POST['servers_ids']))
      {
      if($this->m_model->DeleteMany($_POST['servers_ids']))
        {
//--- сбросим данные на диск
        CLogger::write(CLoggerType::DEBUG, CModel_Pinger::PREFIX_LOG . " servers deleted");
        }
      }
    //---
    $_SESSION['pinger_saveservers'] = 1;
    //--- редиректим на страницу со списком задач
    header("location: ?module=pinger&act=viewservers");
    exit;
    }

  /**
   *
   * Удаление серверов
   * @param array $url
   */
  private function OnDeleteXmlrpcServers($url)
    {
    if(!empty($_POST['servers_ids'])) if($this->m_model->DeleteXmlrpcMany($_POST['servers_ids']))
      {
//--- сбросим данные на диск
      CLogger::write(CLoggerType::DEBUG, CModel_Pinger::PREFIX_LOG . " servers deleted");
      }
    //---
    $_SESSION['pinger_saveservers'] = 1;
    //--- редиректим на страницу со списком серверов
    header("location: ?module=pinger&act=rpcviewservers");
    exit;
    }

  /**
   * Пакетное добавление задач
   */
  private function OnTaskPacketAdd()
    {
    ini_set('max_execution_time', 0);
    ini_set('set_time_limit', 0);
    //---
    $add_pings   = false;
    $count_added = 0;
    CLogger::write(CLoggerType::DEBUG, CModel_pinger::PREFIX_LOG . " pings: task packet adding");
    //---
    if(file_exists($_FILES['file_pings']['tmp_name']))
      {
      $lines = explode("\n", file_get_contents($_FILES['file_pings']['tmp_name']));
      //---
      $this->TaskPaketAddPings($lines, $count_added);
      CLogger::write(CLoggerType::DEBUG, CModel_pinger::PREFIX_LOG . " pings: task packet added from file");
      }
    //---
    if(!empty($_POST['taskListPings']))
      {
      $lines = explode("\n", $_POST['taskListPings']);
      //---
      $this->TaskPaketAddPings($lines, $count_added);
      }
    if($count_added > 0) $add_pings = true;
//---
    CLogger::write(CLoggerType::DEBUG, CModel_pinger::PREFIX_LOG . " pings: task packet added");
//---
    CLogger::write(CLoggerType::DEBUG, CModel_pinger::PREFIX_LOG . " xml: task packet adding");
    if(file_exists($_FILES['file_xml']['tmp_name']))
      {
      $lines = explode("\n", file_get_contents($_FILES['file_pings']['tmp_name']));
      //---
      $this->TaskPaketAddXml($lines, $count_added);
      CLogger::write(CLoggerType::DEBUG, CModel_pinger::PREFIX_LOG . " xml: task packet added from file");
      }
    if(!empty($_POST['taskListXml']))
      {
      $lines = explode("\n", $_POST['taskListXml']);
      //---
      $this->TaskPaketAddXml($lines, $count_added);
      }
//---
    CLogger::write(CLoggerType::DEBUG, CModel_pinger::PREFIX_LOG . " xml: task packet added");
    $_SESSION['pings_task_added'] = $count_added;
    //---
    header("location: ?module=pinger" . ((!$add_pings && $count_added > 0) ? "&name=xml" : ""));
    exit;
    }

  /**
   * Добавление урлов в задачи
   * @param $lines
   */
  private function TaskPaketAddPings($lines, &$count_added)
    {
    foreach($lines as $line_info)
      {
      $url = trim($line_info);
      if(empty($url)) continue;
      //--- может это комментарий
      if($url[0] == ';')
        {
        CLogger::write(CLoggerType::DEBUG, CModel_pinger::PREFIX_LOG . " pings: task packet comment: " . $url);
        continue;
        }
      //--- готово, данные можно добавлять в новое задание
      $this->m_model->SaveTaskPings(array('url' => $url));
      $count_added++;
      //---
      CLogger::write(CLoggerType::DEBUG, CModel_pinger::PREFIX_LOG . " pings: task added " . $url);
      }
    }

  /**
   * Добавление урлов в задачи
   * @param $lines
   */
  private function TaskPaketAddXml($lines, &$count_added)
    {
    foreach($lines as $line_info)
      {
      $url = trim($line_info);
      if(empty($url)) continue;
      //--- может это комментарий
      if($url[0] == ';')
        {
        CLogger::write(CLoggerType::DEBUG, CModel_pinger::PREFIX_LOG . " pings: task packet comment: " . $url);
        continue;
        }
      //---
      $data = explode('|', $url, 4);
      if(count($data) > 3)
        {
        //--- готово, данные можно добавлять в новое задание
        $this->m_model->SaveTaskXml(array('url'       => $data[0],
                                          'url_post'  => $data[1],
                                          'url_rss'   => $data[2],
                                          'url_title' => $data[3]));
        $count_added++;
        //---
        CLogger::write(CLoggerType::DEBUG, CModel_pinger::PREFIX_LOG . " pings: task added " . $url);
        }
      }
    }

  /**
   * Остановка пакетной генерации
   */
  private function OnStopTask()
    {
    $name = "ping";
    if(isset($_REQUEST['name']) && $_REQUEST['name'] == 'xml') $name = $_REQUEST['name'];
    //---
    if($name == 'xml') $this->m_model->StopTaskXML();
    else               $this->m_model->StopTaskPings();
    //---
    CLogger::write(CLoggerType::DEBUG, CModel_pinger::PREFIX_LOG . " tasks stopped by action OnStopTask");
    //--- редиректим на страницу со списком тасков
    header("location: ?module=pinger&name=" . ($name == 'xml' ? 'xml' : 'pings'));
    exit;
    }

  /**
   *
   * Отображение всех задач
   */
  private function OnClearTask()
    {
    CLogger::write(CLoggerType::DEBUG, CModel_pinger::PREFIX_LOG . " clear task pingers begin");
    //--- c какой страницей работать ping или xml
    $name = "ping";
    if(isset($_REQUEST['name']) && $_REQUEST['name'] == 'xml') $name = $_REQUEST['name'];
    if($name == 'xml') $this->m_model->ClearTaskXml();
    else            $this->m_model->ClearTaskPings();
    //---;
    CLogger::write(CLoggerType::DEBUG, CModel_pinger::PREFIX_LOG . " clear task pingers finished");
    //--- редиректим на страницу со списком тасков
    header("location: ?module=pinger&name=" . $name);
    exit;
    }

  /**
   * Запуск выполения тасков в фоновом режиме
   * через сокеты
   */
  private function OnRunTasks()
    {
//--- c какой страницей работать ping или xml
    $name = "ping";
    if(isset($_REQUEST['name']) && $_REQUEST['name'] == 'xml') $name = $_REQUEST['name'];
    //--- запускаем поток
    if($name == 'xml') $this->m_model->StartThreadTaskXml();
    else               $this->m_model->StartThreadTaskPings();
    //--- редиректим на страницу со списком тасков
    header("location: ?module=pinger&name=" . ($name == 'xml' ? 'xml' : 'pings'));
    exit;
    }

  /**
   * Запуск задач по пингованию XML RPC
   */
  private function StartTaskXML()
    {
    CLogger::write(CLoggerType::DEBUG, CModel_pinger::PREFIX_LOG . " task starting, XML-RPC");
    //---
    $this->m_model->Init();
    $list = $this->m_model->GetListTaskXML();
    if(empty($list))
      {
      CLogger::write(CLoggerType::DEBUG, CModel_pinger::PREFIX_LOG . " no task for start, XML-RPC");
      return;
      }
    //--- старт задач
    $this->m_model->StartTaskXML();
    //---
    foreach($list as $file_task)
      {
      if($this->m_model->IsStopTaskXML())
        {
        //--- Остановка скрипта, работающего в фоновом режиме
        break;
        }
      //---
      $task_info = $this->m_model->GetTask($file_task);
      if(empty($task_info))
        {
        CLogger::write(CLoggerType::DEBUG, CModel_pinger::PREFIX_LOG . "xml: task not found " . $file_task);
        continue;
        }
      CLogger::write(CLoggerType::DEBUG, CModel_pinger::PREFIX_LOG . 'xml: ' . $file_task . ' - ' . $task_info['number'] . ' - ' . date("d.m.Y H:i", $task_info['date_create']) . ' checking');
      //--- обновим файл старт, в нем будем писать урл и время начала
      $this->m_model->UpdateStartFileXML($task_info['task']['url']);
      //--- нужно ли запускать задачу
      if($task_info['status'] == CModel_pinger::STATUS_FINISH)
        {
        CLogger::write(CLoggerType::DEBUG, CModel_pinger::PREFIX_LOG . 'xml: ' . $file_task . ' - ' . $task_info['number'] . ' - ' . date("d.m.Y H:i", $task_info['date_create']) . ' already finished');
        continue;
        }
      //---
      CLogger::write(CLoggerType::DEBUG, CModel_pinger::PREFIX_LOG . 'xml: ' . $file_task . ' - ' . $task_info['number'] . ' - ' . date("d.m.Y H:i", $task_info['date_create']) . ' change status to starting');
      //--- изменить статус на выполнение
      $this->m_model->UpdateStatusStartingXML($task_info);
      //--- записи в лог
      CLogger::write(CLoggerType::DEBUG, CModel_pinger::PREFIX_LOG . 'xml: ' . $file_task . ' - ' . $task_info['number'] . ' - ' . date("d.m.Y H:i", $task_info['date_create']) . ' start');
      $this->m_model->SendTaskXML($task_info);
      CLogger::write(CLoggerType::DEBUG, CModel_pinger::PREFIX_LOG . 'xml: parsing finished');
      //--- закончили
      $this->m_model->UpdateStatusFinishXML($task_info);
      CLogger::write(CLoggerType::DEBUG, CModel_pinger::PREFIX_LOG . 'xml: ' . $file_task . ' - ' . $task_info['number'] . ' - ' . date("d.m.Y H:i", $task_info['date_create']) . ' finished');
      //--- вот нужна пауза, не нужно торопиться
      sleep(rand($this->m_model->GetSettings('minPause', 3), $this->m_model->GetSettings('maxPause', 15)));
      CLogger::write(CLoggerType::DEBUG, CModel_pinger::PREFIX_LOG . 'xml: pause');
      }
    //--- все задачи выполнили
    $this->m_model->FinishedTaskXML();
    $this->m_model->ClearServersXML($this->m_model->GetSettings('delete40x', 0), $this->m_model->GetSettings('delete50x', 0), $this->m_model->GetSettings('deleteday', 0));
    //---
    CLogger::write(CLoggerType::DEBUG, CModel_pinger::PREFIX_LOG . "xml: task finished");
    }

  /**
   * Запускаем пингатор для обычных сайтов
   */
  private function StartTaskPings()
    {
    $is_repeat = isset($_REQUEST['pingsRepeat']) && $_REQUEST['pingsRepeat'] == 'on';
    $is_random = isset($_REQUEST['pingsRandom']) && $_REQUEST['pingsRandom'] == 'on';
    //---
    //---
    CLogger::write(CLoggerType::DEBUG, CModel_pinger::PREFIX_LOG . "pings: task starting, repeat: " . ($is_repeat ? "true" : "false") . ', random: ' . ($is_random ? "true" : "false"));
    //---
    $list = $this->m_model->GetListTaskPings();
    if(empty($list))
      {
      CLogger::write(CLoggerType::DEBUG, CModel_pinger::PREFIX_LOG . "pings: no task for start");
      return;
      }
    $this->m_model->Init();
    //--- старт задач
    $this->m_model->StartTaskPings();
    //---
    foreach($list as $file_task)
      {
      if($this->m_model->IsStopTaskPings())
        {
        //--- Остановка скрипта, работающего в фоновом режиме
        break;
        }
      //--- получим задачу
      if($is_random) $task_info = $this->m_model->GetTaskRandom($list, $file_task);
      else $task_info = $this->m_model->GetTask($file_task);
      //---
      if(empty($task_info))
        {
        CLogger::write(CLoggerType::DEBUG, CModel_pinger::PREFIX_LOG . "pings: task not found " . $file_task);
        continue;
        }
      //--- обновим файл старт, в нем будем писать урл и время начала
      $this->m_model->UpdateStartFilePings($task_info['task']['url']);
//---
      CLogger::write(CLoggerType::DEBUG, CModel_pinger::PREFIX_LOG . 'pings: ' . $file_task . ' - ' . $task_info['number'] . ' - ' . date("d.m.Y H:i", $task_info['date_create']) . ' checking');
      //--- нужно ли запускать задачу
      if($task_info['status'] == CModel_pinger::STATUS_FINISH)
        {
        CLogger::write(CLoggerType::DEBUG, CModel_pinger::PREFIX_LOG . 'pings: ' . $file_task . ' - ' . $task_info['number'] . ' - ' . date("d.m.Y H:i", $task_info['date_create']) . ' already finished');
        continue;
        }
      //---
      CLogger::write(CLoggerType::DEBUG, CModel_pinger::PREFIX_LOG . 'pings: ' . $file_task . ' - ' . $task_info['number'] . ' - ' . date("d.m.Y H:i", $task_info['date_create']) . ' change status to starting');
      //--- изменить статус на выполнение
      $this->m_model->UpdateStatusStartingPings($task_info);
      //--- записи в лог
      CLogger::write(CLoggerType::DEBUG, CModel_pinger::PREFIX_LOG . 'pings: ' . $file_task . ' - ' . $task_info['number'] . ' - ' . date("d.m.Y H:i", $task_info['date_create']) . ' start');
      $this->m_model->SendTaskPings($task_info);
      CLogger::write(CLoggerType::DEBUG, CModel_pinger::PREFIX_LOG . 'pings: parsing finished');
      //--- закончили
      $this->m_model->UpdateStatusFinishPings($task_info);
      CLogger::write(CLoggerType::DEBUG, CModel_pinger::PREFIX_LOG . 'pings: ' . $file_task . ' - ' . $task_info['number'] . ' - ' . date("d.m.Y H:i", $task_info['date_create']) . ' finished');
      //--- вот нужна пауза, не нужно торопиться
      if($this->m_model->IsStopTaskPings()) break;
      sleep(rand($this->m_model->GetSettings('minPause', 3), $this->m_model->GetSettings('maxPause', 15)));
      CLogger::write(CLoggerType::DEBUG, CModel_pinger::PREFIX_LOG . 'pings: pause');
      }
    //--- все задачи выполнили
    $this->m_model->FinishedTaskPings();
//--- зачистим не рабочие сервера
    $this->m_model->ClearServersPings($this->m_model->GetSettings('delete40x', 0), $this->m_model->GetSettings('delete50x', 0), $this->m_model->GetSettings('deleteday', 0));
    //--- если нужно все повторять
    if($is_repeat)
      {
      reset($list);
      foreach($list as $id => $file_task)
        {
        $text_info           = $this->m_model->GetTask($file_task);
        $text_info['status'] = CModel_pinger::STATUS_BEGIN;
        if($this->m_model->UpdateTaskPings($text_info)) CLogger::write(CLoggerType::DEBUG, CModel_pinger::PREFIX_LOG . "pings: task update status BEGIN " . $id . ', ' . $text_info['task']['url']);
        }
      //---
      CLogger::write(CLoggerType::DEBUG, CModel_pinger::PREFIX_LOG . "pings: repeat all");
      //---
      if(!$this->m_model->IsStopTaskPings())
         $this->StartTaskPings();
      }
    CLogger::write(CLoggerType::DEBUG, CModel_pinger::PREFIX_LOG . "pings: task finished");
    }

  /**
   * Фоновое выполнение скрипта на PHP без crontab
   */
  private function OnStartTask()
    {
    //--- http://veselov.sumy.ua/blog/php/page/4/
    ignore_user_abort(1); // Игнорировать обрыв связи с браузером
    set_time_limit(0); // Время работы скрипта неограниченно
    //--- c какой страницей работать ping или xml
    $name = "ping";
    if(isset($_REQUEST['name']) && $_REQUEST['name'] == 'xml') $name = $_REQUEST['name'];
    //---
    if($name == 'xml') $this->StartTaskXML();
    else $this->StartTaskPings();
    }

  /**
   * ПОлучение настроек
   */
  private function GetSettings()
    {
    return $this->m_settings;
    }

  /**
   * Удаление задач
   * @param $url
   */
  public function OnDeleteTasks($url)
    {
    //--- c какой страницей работать ping или xml
    $name = "ping";
    if(isset($_REQUEST['name']) && $_REQUEST['name'] == 'xml') $name = $_REQUEST['name'];
    $ids = explode(',', $_REQUEST['tasks_ids']);
    //---
    if($name == 'xml') $this->DeleteTasksXML($ids);
    else               $this->DeleteTasksPings($ids);
    header("location: ?module=pinger&name=" . $name);
    exit;
    }

  /**
   * Удаление xml задач
   * @param $ids
   */
  private function DeleteTasksXML($ids)
    {
    //---
    foreach($ids as $id)
      {
      if(empty($id)) continue;
      //---
      if($this->m_model->DeleteTaskXML($id))
        {
        CLogger::write(CLoggerType::DEBUG, CModel_pinger::PREFIX_LOG . " xml: task deleted " . $id);
        }
      }
    }

  /**
   * Удаление xml задач
   * @param $ids
   */
  private function DeleteTasksPings($ids)
    {
    //---
    foreach($ids as $id)
      {
      if(empty($id)) continue;
      //---
      if($this->m_model->DeleteTaskPings($id))
        {
        CLogger::write(CLoggerType::DEBUG, CModel_pinger::PREFIX_LOG . " pings: task deleted " . $id);
        }
      }
    }

  /**
   * Повторение задачи
   * @param $url
   */
  public function OnRepeatTasks($url)
    {
    //--- c какой страницей работать ping или xml
    $name = "ping";
    if(isset($_REQUEST['name']) && $_REQUEST['name'] == 'xml') $name = $_REQUEST['name'];
    //---
    $ids = explode(',', $_REQUEST['tasks_ids']);
    //---
    if($name == 'xml') $this->RepeatTasksXML($ids);
    else               $this->RepeatTasksPings($ids);
    header("location: ?module=pinger&name=" . $name);
    exit;
    }

  /**
   * повторение задач
   * @param $ids
   */
  private function RepeatTasksXML($ids)
    {
    foreach($ids as $id)
      {
      if(empty($id)) continue;
      $text_info = $this->m_model->GetTaskXMLById($id);
      if(empty($text_info)) continue;
      $text_info['status'] = CModel_pinger::STATUS_BEGIN;
      if($this->m_model->UpdateTaskXml($text_info)) CLogger::write(CLoggerType::DEBUG, CModel_pinger::PREFIX_LOG . " xml: task update status BEGIN " . $id . ', ' . $text_info['task']['url']);
      }
    }

  /**
   * повторение задач
   * @param $ids
   */
  private function RepeatTasksPings($ids)
    {
    foreach($ids as $id)
      {
      if(empty($id)) continue;
      $text_info = $this->m_model->GetTaskPingById($id);
      if(empty($text_info)) continue;
      $text_info['status'] = CModel_pinger::STATUS_BEGIN;
      if($this->m_model->UpdateTaskPings($text_info)) CLogger::write(CLoggerType::DEBUG, CModel_pinger::PREFIX_LOG . " pings: task update status BEGIN " . $id . ', ' . $text_info['task']['url']);
      }
    }

  /**
   * Сохранение настроек
   * @param $url
   */
  public function OnSaveSettings($url)
    {
    if(isset($_POST['delete40x']) && $_POST['delete40x'] < 10) $_POST['delete40x'] = 0;
    if(isset($_POST['delete50x']) && $_POST['delete50x'] < 10) $_POST['delete50x'] = 0;
    //---
    $this->m_model->SaveSettings($_POST);
    $_SESSION['pinger_savesettings'] = 1;
    //---
    header("location: ?module=pinger&act=settings");
    //---
    exit;
    }
  }

?>