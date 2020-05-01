<?php
class CLinks extends IPage
  {
  //--- переводы
  protected static $m_translate = array('ru' => array('main_title'                => 'Ссылки на созданные дорвеи',
                                                      'category'                  => 'Категория',
                                                      'format'                    => 'Формат',
                                                      'delete'                    => 'удалить',
                                                      'add'                       => 'добавить новую',
                                                      'delete_sure_domain'        => 'Точно хотите удалить данные об урле %s?',
                                                      'delete_sure_all'           => 'Точно хотите удалить данные о всех доменах?',
                                                      'download_domains'          => 'cкачать все URL',
                                                      'download_all_links'        => 'cкачать файл',
                                                      'download_all_links_html'   => 'HTML',
                                                      'download_all_links_bbcode' => 'bbCode',
                                                      'download_all_links_text'   => 'Text',
                                                      'download_all_links_clear'  => 'удалить all.txt',
                                                      'clear_all_links'           => 'Вы точно хотите удалить все ссылки из файла all.txt?',
                                                      'filter'                    => 'Фильтр',
                                                      'all'                       => 'Все',
                                                      'mains'                     => 'Главная и sitemap',
                                                      'download_button'           => 'Скачать',
                                                      'links_format'              => 'Свой формат'),
    //---
                                        'en' => array('main_title'                => 'Links',
                                                      'category'                  => 'Category',
                                                      'format'                    => 'Format',
                                                      'delete'                    => 'delete',
                                                      'add'                       => 'add new',
                                                      'delete_sure_domain'        => 'Are you sure you want delete %s?',
                                                      'delete_sure_all'           => 'Are you sure you want delete all domains?',
                                                      'download_domains'          => 'dowload all URL',
                                                      'download_all_links'        => 'download file',
                                                      'download_all_links_html'   => 'HTML',
                                                      'download_all_links_bbcode' => 'bbCode',
                                                      'download_all_links_text'   => 'Text',
                                                      'download_all_links_clear'  => 'delete all.txt',
                                                      'clear_all_links'           => 'Are you sure you want to delete all links from all.txt?',
                                                      'filter'                    => 'Filter',
                                                      'all'                       => 'All',
                                                      'mains'                     => 'Mains',
                                                      'download_button'           => 'Download',
                                                      'links_format'              => 'Your fromat links'));
  /**
   * @var CModel_links
   */
  private $m_model = null;
  private $m_filter = 0;
  /**
   *
   * путь
   * @var string
   */
  private $m_path;
  /**
   *
   * текущая категория
   * @var string
   */
  private $m_carrent_category;
  /**
   *
   * текущий домен
   * @var string
   */
  private $m_current_domain;
  /**
   *
   * текущий тип
   * @var string
   */
  private $m_current_type;
  /**
   *
   * список доменов для данной категории
   * @var array
   */
  private $m_list_domains;
  /**
   *
   * Позиция в файле откуда считывать блок данных
   * @var int
   */
  private $m_position = 0;
  /**
   *
   * Количество ссылок
   * @var int
   */
  private $m_links_cnt = 0;
//--- включить свой формат
  private $m_check_format = false;
//--- формат
  private $m_format = "[url={url}]{title}[/url]";

  /**
   * Конструктор
   */
  public function __construct()
    {
    $this->SetTitle(CLinks::GetTranslate('main_title'));
    //--- TODO: лучше конечно тут сразу указывать subdir, но пока он определяется только в Show (m_current_category)
    $params         = null;
    $model_keywords = null;
    $this->m_model  = new CModel_links();
    $this->m_model->Init($params, $model_keywords);
    //---
    $this->m_current_category = isset($_GET['cat']) ? $_GET['cat'] : $this->m_model->GetFirstCategory();
    //--- определение фильтра
    if(isset($_GET['filter'])) $this->m_filter = (int)$_GET['filter'];
    }

  /**
   * Отображение
   * @see IPage::Show()
   */
  public function Show($path = null)
    {
    global $GLOBAL_ACTIVE_PLUGINS;
    //---
    $this->m_path           = $this->m_model->GetPath();
    $this->m_current_domain = isset($_GET['domain']) ? $_GET['domain'] : null;
    $this->m_current_type   = isset($_GET['type']) ? preg_replace("/[^a-zA-Z0-9\-_\.]/", '', $_GET['type']) : 'text';
    $this->m_position       = isset($_GET['position']) ? (int)$_GET['position'] : 0;
    $this->m_links_cnt      = isset($_GET['cnt']) ? (int)$_GET['cnt'] : 0;
    //---
    $this->m_list_domains = $this->m_model->GetListDomains($this->m_current_category);
    //---
    $keyword = '';
    if(empty($this->m_current_domain) && sizeof($this->m_list_domains) > 0)
      {
      $this->m_current_domain = $this->m_list_domains[0][LinskFormat::DOMAIN];
      $this->m_position       = $this->m_list_domains[0][LinskFormat::POSITION];
      $this->m_links_cnt      = $this->m_list_domains[0][LinskFormat::COUNT];
      $keyword                = $this->m_list_domains[0][LinskFormat::KEYWORD];
      }
    else
      {
//--- найдем кейворд
      if(!empty($this->m_list_domains))
        {
        foreach($this->m_list_domains as $dm) if($dm[LinskFormat::DOMAIN] == $this->m_current_domain) $keyword = $dm[LinskFormat::KEYWORD];
        }
      }
    //---
    $this->m_list_links = $this->m_model->GetListLinks($this->m_current_category, $this->m_current_domain, $keyword, $this->m_position, $this->m_links_cnt, $this->m_current_type, $this->m_filter);
    //---
    include("./inc/pages/links/index.phtml");
    }

  /**
   * Список ссылок
   */
  public function GetListDomains()
    {
    return $this->m_list_domains;
    }

  /**
   * Текущая модель
   */
  public function GetModel()
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
   * Удалить ссылки для домена
   * @param array $url
   */
  public function OnDelete($url)
    {
    if(empty($_REQUEST['cat']) || empty($_REQUEST['domain']))
      {
      CLogger::write(CLoggerType::ERROR, 'links: delete failed, category: ' . $_REQUEST['cat'] . ', domain: ' . $_REQUEST['domain']);
      C404::Show404();
      }
    $catDir = $_REQUEST['cat'];
    $domain = $_REQUEST['domain'];
    //---
    if($this->m_model->DeleteDomain($catDir, $domain))
      {
      CLogger::write(CLoggerType::DEBUG, 'links: delete category: ' . $_REQUEST['cat'] . ', domain: ' . $_REQUEST['domain']);
      }
    else CLogger::write(CLoggerType::ERROR, 'links: NOT delete, category: ' . $_REQUEST['cat'] . ', domain: ' . $_REQUEST['domain']);
    //---
    header("location: ?module=links&cat=" . $catDir . "&type=" . $_REQUEST['type']);
    exit;
    }

  /**
   * Удалить ссылки для домена
   * @param array $url
   */
  public function OnDeleteAll($url)
    {
    if(empty($_REQUEST['cat']))
      {
      CLogger::write(CLoggerType::ERROR, 'links: delete failed, category: ' . $_REQUEST['cat']);
      C404::Show404();
      }
    $catDir = $_REQUEST['cat'];
    //--- удаление
    if($this->m_model->DeleteAllDomain($catDir))
      {
      CLogger::write(CLoggerType::DEBUG, 'links: delete category: ' . $catDir);
      }
    else CLogger::write(CLoggerType::ERROR, 'links: NOT delete, category: ' . $catDir);
    //---
    header("location: ?module=links&cat=" . $catDir);
    exit;
    }

  /**
   * Удалить ссылки для домена
   * @param array $url
   */
  public function OnDownloadDomains($url)
    {
    $list = $this->m_model->GetListDomains($this->m_current_category);
    header('Content-Type: text/txt');
    header('Content-Disposition: attachment; filename="all_domains.txt"');
    header('Cache-Control:');
    foreach($list as $key => &$value)
      {
      echo $value[LinskFormat::DOMAIN], "\r\n";
      }
    exit;
    }

  /**
   * Удалить ссылки all.txt
   * @param array $url
   */
  public function OnClearAllLinks($url)
    {
    $format = !empty($_REQUEST['format']) ? strtolower($_REQUEST['format']) : 'text';
    //--- проверка наличия файла
    $filename = CModel_links::PATH . '/' . CModel_links::DEFAULT_NAME_ALL_LINKS;
    if(!file_exists($filename))
      {
      CLogger::write(CLoggerType::ERROR, 'links: error delete: ' . $filename . ' not exists');
      C404::Show404();
      exit;
      }
    unlink($filename);
    CLogger::write(CLoggerType::DEBUG, 'links: deleted: ' . $filename);
    header("location: ?module=links");
    }

  /**
   * Удалить ссылки для домена
   * @param array $url
   */
  public function OnDownloadAllLinks($url)
    {
    $download_category     = '-1';
    $download_type         = 'text';
    $download_filter       = 'all';
    $download_check_format = false;
    $download_format       = '';
    //---
    if(isset($_REQUEST['download_category'])) $download_category = strtolower($_REQUEST['download_category']);
    //--- тип
    if(isset($_REQUEST['download_type']))
      {
      $download_type = strtolower($_REQUEST['download_type']);
      //--- проверка на правильно типа
      if($download_type != 'html' && $download_type != 'bbcode') $download_type = 'text';
      }
    //---
    if(isset($_REQUEST['download_filter'])) $download_filter = strtolower($_REQUEST['download_filter']);
    //---
    if(isset($_REQUEST['download_self_format']) && $_REQUEST['download_self_format'] == 'on') $download_check_format = true;
    if(isset($_REQUEST['download_format'])) $download_format = $_REQUEST['download_format'];
    //---
    header('Content-Type: text/txt');
    header('Content-Disposition: attachment; filename="' . ($download_filter == CModel_links::FILTER_ALL ? 'all' : 'mains') . '_' . $download_type . '.txt"');
    header('Cache-Control:');
    //--- считываем по строчно
    if($download_filter == CModel_links::FILTER_ALL)
      {
      $this->ReadAllLinks($download_category, $download_type, $download_check_format ? $download_format : '');
      }
    else
      {
      $this->ReadMainsLinks($download_category, $download_type, $download_check_format ? $download_format : '');
      }
    exit;
    }

  /**
   * Скачивание только главных страниц
   * @param $download_category
   * @param $download_type
   */
  private function ReadMainsLinks($download_category, $download_type, $format = '')
    {
    $list_files = array();
    if($download_category == '-1')
      {
      $dirs = CTools_dir::GetDirs(CModel_links::PATH);
      if($dirs == null)
        {
        CLogger::write(CLoggerType::DEBUG, 'links: path is empty: ' . CModel_links::PATH);
        return;
        }
      //---
      foreach($dirs as $dir)
        {
        $fname = CModel_links::PATH . '/' . trim($dir, '/') . '/' . CModel_links::FILE_DOMAINS;
        if(file_exists($fname)) $list_files[] = $fname;
        }
      }
    else
    $list_files[] = CModel_links::PATH . '/' . $download_category . '/' . CModel_links::FILE_DOMAINS;
    //---
    foreach($list_files as $fname)
      {
      $this->m_model->PrintMainsLinks($download_type, $fname, $format);
      }
    }

  /**
   * Считываение и отдача ссылок из файла all.txt
   * @param $download_category
   * @param $download_type
   */
  private function ReadAllLinks($download_category, $download_type, $format = '')
    {
    //--- какой файл будем считывать all.txt или из категории
    if($download_category == '-1') $filename = CModel_links::PATH . '/' . CModel_links::DEFAULT_NAME_ALL_LINKS;
    else
    $filename = CModel_links::PATH . '/' . $download_category . '/' . CModel_links::FILE_ALL_PAGES;
    //---
    if(!file_exists($filename))
      {
      CLogger::write(CLoggerType::ERROR, 'links: error download: ' . $filename . ' not exists');
      return '';
      }
    //---
    $fp = fopen($filename, 'r');
    if(!$fp)
      {
      CLogger::write(CLoggerType::ERROR, 'links: error download: ' . $filename . ' not opened');
      return '';
      }
    //---
    $self_format = !empty($format);
    while(($buffer = fgets($fp, 4096)) !== false)
      {
      $buffer = trim($buffer);
      if(empty($buffer)) continue;
      if($self_format) $this->EchoSelfFormatedLink($buffer, $format);
      else $this->EchoFromatLink($download_type, $buffer);
      }
    //---
    if(!feof($fp))
      {
      CLogger::write(CLoggerType::ERROR, 'links: error download: ' . $filename . ', not read line');
      }
    fclose($fp);
    }

  /**
   * Замена строки
   * @param $buffer
   * @param $format
   */
  private function EchoSelfFormatedLink(&$buffer, $format)
    {
    $ar = explode('|', $buffer, 2);
    echo str_replace(array('{url}',
                           '{title}'), array($ar[0],
                                             $ar[1]), $format) . "\r\n";
    }

  /**
   * Замена строчек
   * @param $download_type
   * @param $buffer
   */
  private function EchoFromatLink($download_type, &$buffer)
    {
    switch($download_type)
    {
      //---
      case 'html':
        $ar = explode('|', $buffer, 2);
        echo '<a href="', $ar[0], '">', $ar[1], "</a>\r\n";
        break;
      //---
      case 'bbcode':
        $ar = explode('|', $buffer, 2);
        echo '[url=', $ar[0], ']', $ar[1], "[/url]\r\n";
        break;
      //---
      default:
        echo $buffer . "\r\n";
    }
    }

  /**
   * Получение перевода
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
  }

?>
