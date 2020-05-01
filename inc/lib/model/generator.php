<?php
//+------------------------------------------------------------------+
//|                  Copyright c 2012, RedButton Web Sites Generator |
//|                                      http://www.getredbutton.com |
//+------------------------------------------------------------------+
/**
 * Основной класс для генерации
 */
class CModel_generator
  {
  const PATH_TEMP = './data/tmp/';
  /**
   * возможные пути для картинок
   * @var array
   */
  private $m_images_paths = array('images',
                                  'image',
                                  'i',
                                  'pics',
                                  'img');
  /**
   * возможные пути для картинок
   * @var array
   */
  private $m_extension_replace = array('html',
                                       'htm',
                                       'phtml');
  /**
   * параметры генерации из админки
   * @var array
   */
  private $m_params = array();
  //--- файлы
  private $files = array();
  //--- результат тегов
  private $m_is_rss_generated = false;
  /**
   * шаблон
   * @var CModel_template
   */
  private $template;
  //--- путь к папке с дорвеем
  private $localPath;
  //--- уникальный id для генерации
  private $uniqId;
  /**
   * модуль для управления rss
   * @var CModel_rss
   */
  private $m_model_rss;
  /**
   * модуль для работы с текстами
   * @var CModel_text
   */
  private $m_model_text;
  /**
   * для сохранения ссылок
   * @var CModel_links
   */
  private $m_model_links;
  /**
   * для работы с урлами для картинок
   * @var CModel_imageslinks
   */
  private $m_model_images;
  /**
   * настройки
   * @var CModel_settings
   */
  private $m_model_settings;
  /**
   * модуль для работы с ссылками
   * @var CModel_plugins
   */
  private $m_model_plugins = null;
  /**
   * нужно ли флашить вывод (для пакетной генерации не нужно)
   * @var bool
   */
  private $m_need_flush = true;
  /**
   * модель управления кейвордами
   * @var CModel_keywords
   */
  private $m_model_keywords;
  /**
   * модуль для клоакинга
   * @var CModel_cloaking
   */
  private $m_model_cloaking;
  /**
   * данные о категории
   * @var Category_keywords
   */
  private $m_category_info = null;
  /**
   * текущая категория
   * @var string
   */
  private $m_current_category = '';
  /**
   * текущий id ключевика
   * @var int
   */
  private $m_keyword_id = 0;
  /**
   * текущий ключевик
   * @var string
   */
  private $m_current_keyword = '';
  /**
   * Нужно создавать проект для хрумаре
   * @var bool
   */
  private $m_is_xrumer = false;
  /**
   * модуль для генерации sitemap.xml
   * @var CModel_sitemap
   */
  private $m_model_sitemap;
  /**
   * модуль с макросами
   * @var CModel_macros
   */
  private $m_model_macros;
  /**
   * Модель для работы с динамикой
   * @var CModel_dynamic
   */
  private $m_model_dynamic;
  /**
   * Модель для работы с хрумером. Сохранение проектов
   * @var CModel_xrumer
   */
  private $m_model_xrumer;
  /**
   * Модуль для управления редиректами
   * @var CModel_redirect
   */
  private $m_redirect;
  /**
   * Модуль для работы с одностраничными дорами
   * @var CModel_onepage
   */
  private $m_model_onepage;
  /**
   * модель для управления картинками
   * @var CModel_Video
   */
  private $m_model_video;
  /**
   * Включен ли редирект
   * @var bool
   */
  private $m_is_redirect = false;
  /**
   * Включен клоакинг
   * @var bool
   */
  private $m_is_cloacking = false;

  /**
   * @param array $paramsArray
   * @param array $filesArray
   * @param bool $isNeedFlush
   */
  public function __construct($paramsArray, $filesArray, $isNeedFlush = true)
    {
    set_time_limit(0);
    ini_set('max_execution_time', 0);
    ini_set('set_time_limit', 0);
    mb_internal_encoding('UTF-8');
    //--- можно ли флашить
    $this->m_need_flush = $isNeedFlush;
    //---
    $this->m_params = $paramsArray;
    $this->files    = $filesArray;
    //--- по фтп или в папку
    if($this->m_params['uploadTo'] == 'ftp')
      {
      //--- если по фтп, то сохраняем уникальный id
      $this->uniqId = uniqid() . rand(0, 1000000);
      //--- путь в темповую папку
      $this->localPath = self::PATH_TEMP . $this->uniqId;
      }
    else
      {
      $this->localPath = $this->m_params['localPath'];
      //--- очистим от последнего слежа
      if(substr($this->localPath, -1, 1) == '/') $this->localPath = substr($this->localPath, 0, strlen($this->localPath) - 1);
      }
    //---
    if(!is_dir($this->localPath))
      {
      if(!mkdir($this->localPath, 0777, true))
        {
        CLogger::write(CLoggerType::ERROR, 'not create path ' . $this->localPath);
        }
      else
      CLogger::write(CLoggerType::DEBUG, 'path created ' . $this->localPath);
      }
    }

  /*
  СТАРТ ГЕНЕРАЦИИ ДОРВЕЯ

  1. подготовка текста и кейвордов
  2. подготовка гиперссылок
  3. подготовка шаблона
  4. выбор кейворда
  5. генерация текста
  6. в зависимости от вида дорвея:
  - создаем файл по кейворду
  - добавляем текст к единому файлу для одностраничного дорвея
  - добавляем текст в текстовую базу для дин. дорвея
  */
  public function Start()
    {
    /* NOTE: подготовки шаблонов, текстов и т.п. возможно
    * стоит вынести в _construction()
    */
    CModel_helper::PrintStartHeader();
    CModel_helper::PrintInfo('Start...', $this->m_need_flush);
    $time_start = time();
    CLogger::write(CLoggerType::DEBUG, 'start... init modules');
    //---
    $this->InitModules();
    //--- начало генерации
    $this->m_model_plugins->OnStartGenerate($this->localPath, $this->m_params);
    //--- если нужно делаем sitemap.xml
    if((isset($this->m_params['createSiteMap']) && $this->m_params['createSiteMap']) || (isset($this->m_params['createSiteMapHtml']) && $this->m_params['createSiteMapHtml'])) $this->m_model_sitemap->Init();
    //--- если нужно создаем rss.xml
    if(isset($this->m_params['createRss']) && $this->m_params['createRss'])
      {
      $this->m_model_rss->RSSGenerateFile();
      $this->m_is_rss_generated = true;
      }
    //--- текущая категория для ключевика пустая
    $this->m_current_category = '';
    //--- если динамический, то создадим файл index.php
    if($this->m_params['type'] == 'dynamic')
      {
      //--- для динамического дорвея посчитаем какие шаблоны и сколько раз встрчаются
      //--- нужно подсчитать сколько различных ссылко нужно для шаблона
      $pg = $this->template->GetBodyIndex();
      if(empty($pg)) $pg = $this->template->GetBody();
      else
        {
        //--- только если есть index.html в шаблоне, создаем файл page.php
        $this->m_model_dynamic->CreatePagePhp($this->template->GetBody(), 'page.php');
        }
      //--- создаем index.php
      $this->m_model_dynamic->CreateIndexPhp($pg);
      //--- создаем category.php
      $this->m_model_dynamic->CreatePagePhp($this->template->GetBodyCategory(), 'category.php');
      }
    CLogger::write(CLoggerType::DEBUG, 'begin work keywords, total: ' . $this->m_model_keywords->GetCountKeywords() . ' keywords');
    //---
    CLogger::write(CLoggerType::DEBUG, 'total memory used: ' . memory_get_usage(true));
    //--- пройдемся по всем ключевым словам
    $flushCounter = 0;
    $d            = array();
    $temp_str     = '';
    //---
    for($i = 0; $i < $this->m_model_keywords->GetCountKeywords(); $i++)
      {
      $this->m_keyword_id    = $i;
      $this->m_category_info = $this->m_model_keywords->GetCategoryByNumKey($this->m_keyword_id);
      //--- получаем кейворд
      $this->m_current_keyword = $this->m_model_keywords->GetKeywordByNum($i);
      //--- событие начала обработки ключевика
      $k = $this->m_current_keyword->getKeyword();
      $this->m_model_plugins->OnBeginKeyword($k, $temp_str, $d);
      //---
      //--- для текста нужно знать текущий кейворд
      $this->m_model_text->setCurrentKeyword($this->m_current_keyword);
      //--- пропускаем пустые кейворды
      if(empty($this->m_current_keyword)) continue;
      //--- получим текущую категорию
      $category = $this->m_model_keywords->GetCategoryByNumKey($i);
      //--- вывод на экран новой категории
      if(!empty($category))
        {
        if($this->m_current_category != $category->getName())
          {
          $this->m_current_category = $category->getName();
          //---
          CModel_helper::PrintInfo('New category: ' . $this->m_current_category, $this->m_need_flush);
          }
        }
      //--- выводим кейворд на экран
      echo $i + 1, '. ', $this->m_current_keyword->getKeyword() . "<br>\n";
      //--- попробуем флашить через каждые 5 кейвордов
      $this->FlushPrint($this->m_need_flush, $flushCounter);
      //---
      $url_current_keyword = $this->m_current_keyword->getUrl(); //$this->m_model_keywords->GetPageNameKey($this->m_current_keyword, $i);
      //---
      CLogger::write(CLoggerType::DEBUG, 'work with key: ' . $this->m_current_keyword->getKeyword() . ', current category: ' . $this->m_current_category . ', url: ' . $url_current_keyword);
      //--- если нужно делаем sitemap.xml
      if(isset($this->m_params['createSiteMap']) && $this->m_params['createSiteMap'])
        {
        $this->m_model_sitemap->AddUrl($url_current_keyword);
        CLogger::write(CLoggerType::DEBUG, 'add key: ' . $this->m_keyword_id . ' : ' . $this->m_current_keyword->getKeyword() . ' to sitemap, url: ' . $url_current_keyword);
        }
      //--- заменяем общие макросы
      if($this->m_params['type'] == 'dynamic')
        {
        //--- вызовим событие у всех макросов
        $page_name = '';
        $content   = $this->GetContentForDynamic($i, $page_name);
        //---
        $key_temp = $this->m_current_keyword->getKeyword();
        $this->m_model_plugins->OnBeginMacros($key_temp, $content, $d);
        //---
        $this->GenetateDynamicPage($i, $content, $page_name);
        if(isset($this->m_params['onepage_create']) && $this->m_params['onepage_create'] == 'on') $this->CopyFilesFromTemplatesPathTo($cur_path = $this->m_model_onepage->GetPath($i));
        }
      else
        {
        CLogger::write(CLoggerType::DEBUG, 'static page');
        $page = $this->GetPageForStatic($i);
        //---
        $key_temp = $this->m_current_keyword->getKeyword();
        $this->m_model_plugins->OnBeginMacros($key_temp, $page, $d);
        //--- проверим установлен ли редирект и запустим замену, если это необходимо
        if($this->m_is_redirect)
          {
          //--- если установлен редирект, то и клоакинг совместо будет работать
          $this->m_redirect->Redirect($page, $this->m_current_keyword->GetKeyword());
          }
        elseif($this->m_is_cloacking)
          {
          //--- если пользователь установил только клоакинг, без редиректа.
          $this->m_redirect->CloackingSet($page, $this->m_current_keyword);
          }
        //---
        $this->GenerateStaticPage($i, $page);
        }
      //--- страница сгенерирована, значит нужно добавить линк
      $this->m_model_links->SaveLinkToFile($this->m_current_keyword, $url_current_keyword);
      //---
      $temp_str = $this->m_params['type'] == 'dynamic' ? $content : $page;
      $key_temp = $this->m_current_keyword->getKeyword();
      $this->m_model_plugins->OnEndKeyword($key_temp, $temp_str, $d);
      unset($temp_str);
      //---
      //CLogger::write(CLoggerType::DEBUG, 'memory used: ' . memory_get_usage(true));
      }
    //--- все сгенерировали
    $this->m_model_plugins->OnEndGenerate($this->localPath, $this->m_params);
    CLogger::write(CLoggerType::DEBUG, 'memory used: ' . memory_get_usage(true));
    //--- создадим роботс.тхт
    $this->CreateRobotsTxt();
    //--- скопируем нужные файлы для редиректа
    if($this->m_is_redirect) $this->m_redirect->CopyFiles();
    //--- создание сайтмапов
    $this->CreateSitemaps();
    //--- закроем все, в том числе и файл со ссылками на дорвеи
    $this->m_model_text->Close();
    //--- закроем все файлы
    $this->m_model_links->Close();
    //--- если нужно сохранить адрес дорвея
    $urls = $this->m_model_links->SaveDoorwayLinkToFile($this->m_is_xrumer);
    //--- в конце обновим данные для .htaccess, особенно касается динамических дорвеев
    $this->UpdatedHtaccess();
    //--- если нужно, то создадим проект для хрумера
    if($this->m_is_xrumer) $this->m_model_xrumer->CreateProject($urls);
    //--- нужно скопировать все файлы из шаблона
    CLogger::write(CLoggerType::DEBUG, 'begin copy template files: ' . $this->template->getTemplatePath());
    if($this->CopyFilesFromTemplatesPath()) CLogger::write(CLoggerType::DEBUG, 'template files copied');
    //---
    $this->ObfuscatorJs();
    //--- нужно заархивировать, остальное удалить
    $zip_file      = '';
    $zip_file_full = '';
    if(isset($this->m_params['archive_zip']))
      {
      $zips          = $this->CreateZipArchive();
      $zip_file      = $zips['name'];
      $zip_file_full = $zips['fullname'];
      }
    //--- нужно заархивировать, остальное удалить
    $unzip_filename = '';
    if(isset($this->m_params['un_archive_zip'])) $unzip_filename = $this->CreateUnZippedFile();
    //--- проверим, может по фтп все передать нужно
    if($this->m_params['uploadTo'] == 'ftp')
      {
      //--- проверка, возможно это отложенная загрузка по фтп
      if(isset($this->m_params['ftp_delayed']))
        {
        CLogger::write(CLoggerType::DEBUG, 'delay upload on ftp');
        $upload = new CModel_UploadTask();
        //--- если все успешно поставили в очередь, то зачистим
        if($upload->SaveSiteToTask($this->localPath, $this->m_params))
          {
          if(CTools_files::DeleteAll($this->localPath))
            {
            CLogger::write(CLoggerType::DEBUG, "path deleted " . $this->localPath);
            $zip_file_full = '';
            }
          }
        }
      else
        {
        CLogger::write(CLoggerType::DEBUG, 'begin copy to ftp');
        $ftp_model = $this->sendToFtp();
        //--- нужно ли распаковывать
        if(isset($this->m_params['un_archive_zip']))
          {
          //--- пытаемся распаковать сайт
          if($this->UnzipWebsiteOnFtp($unzip_filename, $zip_file))
            {
            //--- удаляем файл для распаковки
            $this->DeleteUnZippedFile($unzip_filename, $zip_file);
            }
          }
        //ump(isset($this->m_params['ftp_delete_always']) && $this->m_params['ftp_delete_always'] == 'on'); exit;
        //--- удаление папки
        if(isset($this->m_params['ftp_delete_always']) && $this->m_params['ftp_delete_always'] == 'on')
          {
          CLogger::write(CLoggerType::DEBUG, "ftp_delete_always - ON - path deleted " . $this->localPath);
          if($ftp_model != null)
            {
            $p = $ftp_model->GetTempPath();
            if(CTools_files::DeleteAll($p)) CLogger::write(CLoggerType::DEBUG, "path deleted " . $p);
            }
          }
        $ftp_model = null;
        }
      }
    else
      {
      //--- если кто-то локально пытается распокавать
      //--- нужно ли распаковывать
      if(isset($this->m_params['un_archive_zip']))
        {
        //--- пытаемся распаковать сайт
        if($this->UnzipWebsiteOnFtp($unzip_filename, $zip_file))
          {
          //--- удаляем файл для распаковки
          //$this->DeleteUnZippedFile($unzip_filename, $zip_file);
          $fname = $this->localPath . '/' . $unzip_filename;
          if(unlink($fname)) CLogger::write(CLoggerType::DEBUG, 'local delete file: ' . $fname);
          //---
          $fname = $this->localPath . '/' . $zip_file;
          if(unlink($fname))
            {
            CLogger::write(CLoggerType::DEBUG, 'local delete file: ' . $fname);
            $zip_file_full = '';
            }
          }
        }
      }
    //--- проверка нужно ли добавлять задачу в пингатор
    $this->PingerAdd();
    //---
    CLogger::write(CLoggerType::DEBUG, 'generate finished, total: ' . (time() - $time_start) . ' seconds');
    //---
    CModel_helper::PrintInfo('Ready <a href="' . $this->m_params['nextUrl'] . '" target="_blank">' . $this->m_params['nextUrl'] . '</a>', $this->m_need_flush);
    if(isset($this->m_params['archive_zip']) && $this->m_params['archive_zip'] == 'on' && !empty($zip_file_full))
      {
      CModel_helper::PrintInfo('<a href="?a[download]&f=' . $zip_file_full . '" target="_blank">Download zip</a>', $this->m_need_flush);
      }
    }

  /**
   * Шаблон для статики
   * @param $i
   *
   * @return mixed
   */
  private function GetPageForStatic($i)
    {
//--- первый ключевик, значит нужно попробывать взять главный шаблон
    if($i == 0)
      {
      $page = $this->template->GetBodyIndex();
      if(empty($page)) $page = $this->template->GetBody();
      }
    else
      {
      $res  = true;
      $page = $this->template->GetBodyCategory();
      if(empty($page)) $res = false;
      if($res)
        {
        //--- проверяем этот ключ находится в какой-нибудь категории
        $category_info = $this->m_model_keywords->GetCategoryByNumKey($i);
        if($category_info != null && $category_info->getKeywordBegin() == $i)
          { //--- все ок, нужно загружать категорию
          }
        else
          {
//--- обычная страница
          $res = false;
          }
        }
      if(!$res) $page = $this->template->GetBody();
      }
    return $page;
    }

  /**
   * Получение контента для динамики
   * @param $i
   * @param $page_name
   *
   * @return mixed
   */
  private function GetContentForDynamic($i, &$page_name)
    {
//--- первый ключевик, значит нужно попробывать взять главный шаблон
    if($i == 0)
      {
      $page      = $this->template->GetContentIndex();
      $page_name = 'index';
      if(empty($page))
        {
        $page      = $this->template->GetContent();
        $page_name = 'page';
        }
      }
    else
      {
      $res  = true;
      $page = $this->template->GetContentCategory();
      if(empty($page)) $res = false;
      if($res)
        {
//--- проверяем этот ключ находится в какой-нибудь категории
        $category_info = $this->m_model_keywords->GetCategoryByNumKey($i);
        if($category_info != null && $category_info->getKeywordBegin() == $i)
          { //--- все ок, нужно загружать категорию
          $page_name = 'category';
          }
        else
          {
//--- обычная страница
          $res = false;
          }
        }
      if(!$res)
        {
        $page      = $this->template->GetContent();
        $page_name = 'page';
        }
      }
    return $page;
    }

  /**
   * Добавим задачу в пингатор, и если еще не стартовало, попытаемся стартонуть
   */
  private function PingerAdd()
    {
    //--- проверим а нужно ли добавлять
    if(!isset($this->m_params['pinger_task']) || $this->m_params['pinger_task'] != 'on') return;
    //--- добавим новую задачу
    $module_pinger = new CModel_pinger();
    if($module_pinger->SaveTaskPings(array('url' => $this->m_params['nextUrl']))
    ) CLogger::write(CLoggerType::DEBUG, CModel_pinger::PREFIX_LOG . " pings task added " . $this->m_params['nextUrl']);
//---
    $i        = 0;
    $keyword  = $this->m_model_keywords->GetKeywordByNum($i);
    $url_post = $keyword->getUrl();
    if($module_pinger->SaveTaskXml(array('url'       => $this->m_params['nextUrl'],
                                         'url_post'  => $url_post,
                                         'url_rss'   => rtrim($this->m_params['nextUrl'], '/') . '/rss.xml',
                                         'url_title' => $keyword->getKeywordIndex(0)))
    ) CLogger::write(CLoggerType::DEBUG, CModel_pinger::PREFIX_LOG . " pings xml task added " . $this->m_params['nextUrl'] . ', title: ' . $keyword->getKeywordIndex(0) . ', url post: ' . $url_post);
    if(!$module_pinger->IsStopTaskPings()) $module_pinger->StartTaskPings();
    }

  /**
   * создание файла .htaccess
   */
  private function CreateStaticHtaccess($path)
    {
    $s = '';
    $this->m_model_cloaking->GetForHtaccess($s);
    $s .= $this->m_model_macros->CreateStaticHtaccess($path);
    //---
    if(!empty($s))
      {
      $fname = $path . '/.htaccess';
      //--- редиректа еще нет, то добавим
      $data = '';
      if(strpos($s, 'RewriteEngine') === FALSE) $data = 'RewriteEngine on' . "\r\n";
      $data .= $s;
      //---
      file_put_contents($fname, $data);
      //---
      CLogger::write(CLoggerType::DEBUG, '.htaccess created: ' . $fname);
      return $data;
      }
    return '';
    }

  /**
   * Создание сайтмапов
   */
  private function CreateSitemaps()
    {
    //--- создадим sitemap.xml
    if(isset($this->m_params['createSiteMap']) && $this->m_params['createSiteMap'])
      {
      $this->m_model_sitemap->Finals();
      $this->m_model_sitemap->CreateFile();
      }
    //--- нужно ли создавать sitemaps
    if(isset($this->m_params['createSiteMapHtml']) && $this->m_params['createSiteMapHtml'] == 'on')
      {
      //--- шаблон для sitemap
      $page = $this->template->GetBodySitemap();
      //--- шаблона нет, обычный
      if(empty($page)) $page = $this->template->GetBody();
      //---
      $page = str_replace('[RSS]', $this->GetRSS(), $page);
      $page = str_replace('[ARSS]', $this->GetARSS(), $page);
      //---
      $this->m_model_sitemap->CreateHtml($page, 'sitemap.' . $this->m_model_keywords->GetExtension());
      //---
      $keyInfo = $this->m_model_keywords->GetKeywordByNum(0);
      //---
      //$keyInfo->setKeyword();
      //$keyInfo->setUrl();
      $key = new CKeywordInfo(0, 'Sitemap ' . $keyInfo->GetKeywordIndex(0), array('Sitemap ' . $keyInfo->GetKeywordIndex(0)), $this->m_params['nextUrl'] . 'sitemap.' . $this->m_model_keywords->GetExtension());
      $this->m_model_links->SaveLinkToFile($key);
      }
    }

  /**
   * Инцилизация модулей, первоначальная загрузка данных
   */
  private function InitModules()
    {
    CLogger::write(CLoggerType::DEBUG, 'init all modules begin...');
    //--- настройки для сохранения ссылок
    if(!isset($this->m_params['saveLink'])) $this->m_params['saveLink'] = null;
    if(!isset($this->m_params['saveLinkOne'])) $this->m_params['saveLinkOne'] = null;
    //--- нужен ли слэш в конце
    if(!empty($this->m_params['nextUrl'])) $this->m_params['nextUrl'] = trim($this->m_params['nextUrl']);
    if(!empty($this->m_params['nextUrl']) && substr($this->m_params['nextUrl'], -1, 1) != '/') $this->m_params['nextUrl'] .= '/';
    //--- http в начале
    if(!empty($this->m_params['nextUrl']) && !CModel_helper::IsExistHttp($this->m_params['nextUrl']) && $this->m_params['nextUrl'][0] != '/') $this->m_params['nextUrl'] = 'http://' . $this->m_params['nextUrl'];
    //---  для одностраничников
    if(CModel_helper::IsExistHttp($this->m_params['nextUrl'])) $this->m_params['clearnextUrl'] = substr($this->m_params['nextUrl'], 7);
    else $this->m_params['clearnextUrl'] = $this->m_params['nextUrl'];
    //---
    $this->m_params['clearnextUrl'] = rtrim($this->m_params['clearnextUrl'], '/\\');
    //--- активируем плагины
    $this->m_model_plugins = new CModel_plugins();
    $this->m_model_plugins->ActivateAll();
    //--- найстройки
    $this->m_model_settings = new CModel_settings();
    //--- подготовим шаблон
    $this->template = new CModel_template($this->m_params['template'], (isset($this->m_params['random_template']) && $this->m_params['random_template'] == 'on') ? 1 : 0);
    //--- загрузим ключевые слова
    $this->m_model_keywords = new CModel_keywords();
    $this->m_model_keywords->Init($this->m_params, $this->m_model_plugins);
    //---
    $this->m_model_links = new CModel_links();
    $this->m_model_links->Init($this->m_params, $this->m_model_keywords);
//---
    $this->m_model_images = new CModel_imageslinks();
    //--- нужен ли будет рерайтер и соотвественно настройки для него
    $this->m_model_text = new CModel_text();
    $this->m_model_text->Init($this->m_params, $this->m_model_keywords, $this->m_model_plugins, $this->m_model_links, $this->m_model_images);
    //--- коакинг
    $this->m_model_cloaking = new CModel_cloaking($this->m_params);
    //---
    $this->m_model_rss        = new CModel_rss($this->localPath, $this->m_params, $this->m_model_keywords);
    $this->m_is_rss_generated = false;
    //--- видео
    $this->m_model_video = new CModel_video();
    //--- модуль с макросами
    $currentImagePath     = $this->GetCurrentImagePath();
    $this->m_model_macros = new CModel_macros($this->m_params, $this->m_model_keywords, $this->m_model_text, $this->m_model_links, $this->m_model_settings, $this->m_model_video, $this->template, $currentImagePath, $this->localPath);
    //--- модуль для sitemap
    $this->m_model_sitemap = new CModel_sitemap($this->localPath, $this->m_params, $this->m_model_keywords, $this->m_model_macros, $this->m_model_settings);
    //--- модуль с динамикой
    $this->m_model_dynamic = new CModel_dynamic($this->m_params, $this->m_model_macros, $this->m_model_rss, $this->m_model_keywords, $this->m_model_text, $this->m_model_links, $this->m_model_cloaking, $this->localPath);
    //--- хрумер
    $this->m_is_xrumer    = isset($this->m_params['projectXrumer']) && $this->m_params['projectXrumer'];
    $this->m_model_xrumer = new CModel_xrumer();
    if($this->m_is_xrumer) $this->m_model_xrumer->Init($this->m_params, $this->m_model_text);
    //--- редиректы
    $this->m_redirect = new CModel_redirect($this->m_params, $this->m_model_cloaking, $this->localPath);
    $this->m_redirect->Init();
    $this->m_is_redirect  = !empty($this->m_params['redirectType']);
    $this->m_is_cloacking = !empty($this->m_params['cloakingType']);
    //--- в динамическом установим модуль редирект
    $this->m_model_dynamic->SetRedirect($this->m_redirect);
    //---
    $ftpPath = $this->m_params['ftpPath'];
    if(empty($ftpPath)) $ftpPath = './';
    else
      {
      if($ftpPath[0] != '.' && $ftpPath[0] != '/') $ftpPath = './' . $ftpPath;
      }
    $this->m_params['ftpPath'] = $ftpPath;
    //--- если чпу ссылки то дополнительный пхп код не нужен
    $this->CreateHtaccess();
    //---
    if(isset($this->m_params['onepage_create']))
      {
        $this->m_model_onepage = new CModel_onepage($this->m_params, $this->m_model_keywords, $this->localPath);
      }
    //---
    CLogger::write(CLoggerType::DEBUG, 'all modules ready');
    }

  /**
   * Пытаемся разархивировать zip файл на серваке
   *
   * @param string $unzip_filename
   * @param string $zip_name
   *
   * @return bool
   */
  private function UnzipWebsiteOnFtp($unzip_filename, $zip_name)
    {
    $url_dor = $this->m_params['nextUrl'] . $unzip_filename . "?fz=" . $zip_name;
    $result  = file_get_contents($url_dor);
    //--- если накосячили и передали UTF8 файл
    $result = CModel_tools::RemoveBom($result);
    if($result == 'OK')
      {
      CLogger::write(CLoggerType::DEBUG, 'unzip file ' . $zip_name . ', url: ' . $url_dor);
      return true;
      }
    //---
    CLogger::write(CLoggerType::ERROR, 'error unzip file ' . $zip_name . ', url: ' . $url_dor . ', error: ' . $result);
    return false;
    }

  /**
   * Создание .htaccess файла
   */
  private function CreateHtaccess()
    {
    //--- если динамический, то создадим файл .
    if($this->m_params['type'] == 'dynamic')
      {
      //--- если чпу ссылки то дополнительный пхп код не нужен
      //--- в динамике уже формируются урлы на клоакинг
      $data = $this->m_model_dynamic->CreateDynamicHtaccess();
      return;
      }
    else
      {
      //--- для статики создадим .htaccess
      $data = $this->CreateStaticHtaccess($this->localPath);
      }
    /*
    //---
    $this->m_model_cloaking->GetForHtaccess($data);
    //--- создание файла только для не пустых данных
    if(!empty($data))
      {
      $fname = $this->localPath . '/.htaccess';
      file_put_contents($fname, $data);
      //--- ставим права 777 для каждого файла
      chmod($fname, 0777);
      }
    */
    }

  /**
   * Создание .htaccess файла
   */
  private function UpdatedHtaccess()
    {
    //--- если динамический, то создадим файл .
    if($this->m_params['type'] == 'dynamic')
      {
      //--- если чпу ссылки то дополнительный пхп код не нужен
      //--- в динамике уже формируются урлы на клоакинг
      $this->m_model_dynamic->UpdateDynamicHtaccess();
      return;
      }
    else
      { //--- если чпу ссылки то дополнительный пхп код не нужен
      $this->CreateHtaccess();
      }
    }

  /**
   * Пытаем удалить zip файл
   *
   * @param string $unzip_filename
   * @param string $zip_name
   */
  private function DeleteUnZippedFile($unzip_filename, $zip_name)
    {
    $ftpPath = $this->m_params['ftpPath'];
    $ftp     = $this->GetFtpModel($ftpPath);
    //--- если нет ничего то просто по фтп
    if($ftp == null) $ftp = new CModel_ftp($this->uniqId, $this->m_params['ftpServer'], !empty($this->m_params['ftpPort']) ? $this->m_params['ftpPort'] : 21, $this->m_params['ftpLogin'], $this->m_params['ftpPassword'], $ftpPath, $this->localPath, true);
    //---
    $ftp->DeleteListFile(array($unzip_filename,
                               $zip_name));
    }

  /**
   * Получение текущего пути для картинок
   */
  private function GetCurrentImagePath()
    {
    $path = $this->template->getTemplatePath();
    if(substr($path, -1, 1) != '/') $path .= '/';
    //---
    foreach($this->m_images_paths as $p)
      {
      if(is_dir($path . $p))
        {
        return $p;
        }
      }
    //--- по умолчанию первую папку
    return $this->m_images_paths[0];
    }

  /**
   * Упакуем весь сайт в архив, а остальные файлы удалим
   */
  private function CreateZipArchive()
    {
    $zip          = new CModel_zip();
    $zip_filename = uniqid() . ".zip";
    $fname        = $this->localPath . '/' . $zip_filename;
    //---
    if($zip->CreateZipFile($this->localPath, $fname))
      {
      CLogger::write(CLoggerType::DEBUG, "zip archive created " . $fname);
      }
    if(CTools_files::DeleteAll($this->localPath, false, array($fname)))
      {
      CLogger::write(CLoggerType::DEBUG, "deleted all files " . $this->localPath);
      }
    return array('name'     => $zip_filename,
                 'fullname' => $fname);
    }

  /**
   * СОздание файла для распаковки на сервере
   */
  private function CreateUnZippedFile()
    {
    $filename = uniqid() . ".php";
    $fname    = $this->localPath . '/' . $filename;
    //---
    $unzip_file = './inc/public/unzip.php';
    if(!file_exists($unzip_file))
      {
      CLogger::write(CLoggerType::ERROR, "unzip public file not exists " . $unzip_file);
      return '';
      }
    //---
    $content = file_get_contents($unzip_file);
    if(file_put_contents($fname, $content))
      {
      CLogger::write(CLoggerType::DEBUG, "unzip file created " . $fname);
      }
    return $filename;
    }

  /**
   * Генерация статической страницы
   * @param int $i
   * @param string $page
   */
  private function GenerateStaticPage($i, $page)
    {
    //--- для статики
    $page = str_replace('[RSS]', $this->GetRSS(), $page);
    $page = str_replace('[ARSS]', $this->GetARSS(), $page);
    $page = str_replace('[RB:CONTENT]', $this->template->GetContent(), $page);
    //--- заменяем остальные макросы
    CLogger::write(CLoggerType::DEBUG, 'replace many macros');
    $page = $this->m_model_macros->ReplaceManyMacros($page, $i);
    //---
    CLogger::write(CLoggerType::DEBUG, 'replace many macros end');
    //--- вызовим функцию по окончанию замены всех макросов
    $key_temp = $this->m_current_keyword->getKeyword();
    $this->m_model_plugins->OnEndMacros($key_temp, $page, $i);
//---
    if(isset($this->m_params['onepage_create']) && $this->m_params['onepage_create'] == 'on' && $i > 0)
      {
      //--- папка куда будем сохранять
      $cur_path = $this->m_model_onepage->GetPath($i);
      //--- проверка, может вернуться null, т.к. на поддомен наложены ограничения
      if(!empty($cur_path))
        {
        if(!file_exists($cur_path))
          {
          mkdir($cur_path, 0777, true);
          chmod($cur_path, 0777);
          CLogger::write(CLoggerType::DEBUG, 'static: create path: ' . $cur_path);
          }
        $file_content_name = $cur_path . '/index.' . $this->m_model_keywords->GetExtension();
        //---
        if(isset($this->m_params['is_commpress']) && $this->m_params['is_commpress'] == 'on')
          {
          $page = CModel_tools::HtmlCompress($page);
          CLogger::write(CLoggerType::DEBUG, 'compressed html ' . $file_content_name);
          }
        //---
        file_put_contents($file_content_name, $page);
        CLogger::write(CLoggerType::DEBUG, 'file create ' . $file_content_name . ', length data: ' . strlen($page));
        //--- скопируем дизайн
        $this->CopyFilesFromTemplatesPathTo($cur_path);
        //--- проксирование картинок
        $this->CreateStaticHtaccess($cur_path);
        }
      //---
      unset($page);
      return;
      }
    //--- формируем данные для статического дорвея
    //--- укажем текущую папку для сохранения файлов
    $cur_path = $this->localPath;
    //--- если есть категория, то пишем туда
    if(!empty($this->m_category_info))
      {
      //--- новая папка
      $cur_path = $this->localPath . '/' . $this->m_category_info->getUrl();
      if(isset($this->m_params['urlsOnLanguage']) && $this->m_params['urlsOnLanguage'] == 'on' && substr(PHP_OS, 0, 3) == "WIN")
        {
        $cur_path = iconv('UTF-8', 'windows-1251', $cur_path);
        }
      //---
      if(!file_exists($cur_path))
        {
        mkdir($cur_path);
        chmod($cur_path, 0777);
        }
      }
    //--- получаем название страницы
    if($this->m_category_info != null && $this->m_category_info->GetKeywordBegin() == $i)
      {
      $pageName = 'index.' . $this->m_model_keywords->GetExtension();
      }
    else
      {
      $pageName = $this->m_current_keyword->getFilename(); //$this->m_model_keywords->GetPageName($i);
      }
    //--- разрешаем плагинам обработать название файла
    $key_temp = $this->m_current_keyword->getKeywordIndex(0);
    $this->m_model_plugins->OnSavePage($key_temp, $pageName);
    //--- данные пишем в файл
    if(isset($this->m_params['urlsOnLanguage']) && $this->m_params['urlsOnLanguage'] == 'on' && substr(PHP_OS, 0, 3) == "WIN")
      {
      //$pageName = utf8_decode($pageName);
      $pageName = mb_convert_encoding($pageName, 'windows-1251', 'UTF-8');
//$pageName = $this->autfw_sanitize_file_name_for_windows($pageName,true);
      //$pageName = iconv('UTF-8', 'ASCII', $pageName);
      }
    //---
    $file_content_name = $cur_path . '/' . $pageName;
    if(isset($this->m_params['is_commpress']) && $this->m_params['is_commpress'] == 'on')
      {
      $page = CModel_tools::HtmlCompress($page);
      CLogger::write(CLoggerType::DEBUG, 'compressed html ' . $file_content_name);
      }
    file_put_contents($file_content_name, $page);
    CLogger::write(CLoggerType::DEBUG, 'file create ' . $file_content_name . ', length data: ' . strlen($page));
    //--- ставим права 777 для каждого файла
    chmod($file_content_name, 0777);
    unset($page);
    }

  private function autfw_sanitize_file_name_for_windows($filename, $utf8 = false)
    {
    // On Windows platforms, PHP will mangle non-ASCII characters, see http://bugs.php.net/bug.php?id=47096
    if('WIN' == substr(PHP_OS, 0, 3))
      {
      if(setlocale(LC_CTYPE, 0) == 'C')
        { // Locale has not been set and the default is being used, according to answer by Colin Morelli at http://stackoverflow.com/questions/13788415/how-to-retrieve-the-current-windows-codepage-in-php
        // thus, we force the locale to be explicitly set to the default system locale
        $codepage = 'Windows-' . trim(strstr(setlocale(LC_CTYPE, ''), '.'), '.');
        }
      else
        {
        $codepage = 'Windows-' . trim(strstr(setlocale(LC_CTYPE, 0), '.'), '.');
        }
      $filename = mb_convert_encoding($filename, $codepage, 'UTF-8');
      }
    return $filename;
    }

  /**
   * Выводим на экран, если это нужно
   * @param bool $need_flush
   * @param ште $flushCounter
   */
  private function FlushPrint($need_flush, &$flushCounter)
    {
    if($need_flush && ++$flushCounter > 5)
      {
      $flushCounter = 0;
      flush();
      @ob_flush();
      }
    }

  /**
   * Получаем строку для rss и если надо генерируем файл
   */
  private function GetARSS()
    {
    if(!$this->m_is_rss_generated)
      {
      $this->m_model_rss->RSSGenerateFile();
      $this->m_is_rss_generated = true;
      }
    //---
    return '<a href="' . $this->m_params['nextUrl'] . 'rss.xml">rss</a>';
    }

  /**
   * Получаем строку для rss и если надо генерируем файл
   */
  private function GetRSS()
    {
    if(!$this->m_is_rss_generated)
      {
      $this->m_model_rss->RSSGenerateFile();
      $this->m_is_rss_generated = true;
      }
    return '<link href="' . $this->m_params['nextUrl'] . 'rss.xml" rel="alternate" type="application/rss+xml" title="' . htmlspecialchars($this->m_params['pageTitle']) . '">';
    }

  /**
   * Получение заголовка страницы

   */
  private function GetTitle()
    {
    return $this->m_params['pageTitle'];
    }

  /**
   * Получение данных для описания страницы
   */
  private function GetDescription()
    {
    return $this->m_params['metaDescription'];
    }

  /**
   * Копируем все файлы из папки с шаблоном,
   * кроме "стоп файлов"
   */
  private function CopyFilesFromTemplatesPath()
    {
    return $this->CopyFilesFromTemplatesPathTo($this->localPath);
    }

  /**
   * Копируем все файлы из папки с шаблоном,
   * кроме "стоп файлов"
   */
  private function CopyFilesFromTemplatesPathTo($path)
    {
    if(empty($path))
      {
      CLogger::write(CLoggerType::DEBUG, 'path is null, no copied files');
      return;
      }
    return $this->CopyPathReplaced($this->template->getTemplatePath(), $path, $this->template->getStopFiles());
    }

  /**
   *
   * @param $source
   * @param $dest
   * @param array $stop_files
   * @return bool
   */
  private function CopyPathReplaced($source, $dest, $stop_files = array())
    {
    //--- Simple copy for a file
    if(is_file($source))
      {
      if(copy($source, $dest))
        {
        //--- проверим хтмл, значит нужно сделать замены макросов
        $ext = pathinfo($dest, PATHINFO_EXTENSION);
        if(in_array($ext, $this->m_extension_replace))
          {
          CLogger::write(CLoggerType::DEBUG, 'try copy and replace macros: ' . $dest);
          //---
          $page = file_get_contents($dest);
          //--- для статики
          $page = str_replace('[RSS]', $this->GetRSS(), $page);
          $page = str_replace('[ARSS]', $this->GetARSS(), $page);
          $page = str_replace('[RB:CONTENT]', $this->template->GetContent(), $page);
          //--- заменяем остальные макросы
          $page = $this->m_model_macros->ReplaceManyMacros($page, 0);
          //---
          CLogger::write(CLoggerType::DEBUG, 'copied replace many macros: ' . $dest);
          //---
          file_put_contents($dest, $page);
          }
        //---
        return true;
        }
      else
        {
        //---
        CLogger::write(CLoggerType::ERROR, 'copy file failed: ' . $dest);
        }
      return false;
      }
    // Make destination directory
    if(!is_dir($dest))
      {
      if(!file_exists($dest)) mkdir($dest, 0777, true);
      }
    // If the source is a symlink
    if(is_link($source))
      {
      $link_dest = readlink($source);
      return symlink($link_dest, $dest);
      }
    //--- Loop through the folder
    $dir = dir($source);
    //---
    if(!$dir) return false;
    while(false !== $entry = $dir->read())
      {
      // Skip pointers
      if($entry == '.' || $entry == '..' || $entry == '.svn' || in_array($entry, $stop_files))
        {
        continue;
        }
      // Deep copy directories
      $src_path = $source . '/' . $entry;
      if($dest !== $src_path)
        {
        $this->CopyPathReplaced($src_path, $dest . '/' . $entry);
        }
      }
    // Clean up
    $dir->close();
    }

  /**
   * Если нужно то делаем офускацию js файлов.
   * Бегаем по всем папкам и ищем js файлы
   */
  private function ObfuscatorJs()
    {
    if(isset($this->m_params['is_js_obfuscator']) && $this->m_params['is_js_obfuscator'] == 'on')
      {
      include_once('./inc/lib/osrc/Compress/javascriptpacker.php');
      include_once('./inc/lib/osrc/Compress/javasciptobfus.php');
      $this->ObfuscationJs($this->localPath);
      CLogger::write(CLoggerType::DEBUG, 'js obfuscator finished for path: ' . $this->localPath);
      }
    }

  /**
   * Если нужно то делаем офускацию js файлов.
   * Бегаем по всем папкам и ищем js файлы
   */
  private function ObfuscationJs($path)
    {
    $d = dir($path);
    while(false !== ($entry = $d->read()))
      {
      if($entry == '.' || $entry == '..') continue;
      if(is_dir($path . '/' . $entry))
        {
        $this->ObfuscationJs($path . '/' . $entry);
        continue;
        }
      $ext = pathinfo($entry, PATHINFO_EXTENSION);
      if($ext != 'js') continue;
      //---
      $filejs = $path . '/' . $entry;
      $script = file_get_contents($filejs);
      if($this->m_params['obfuscatorType'] == 'pr2')
        {
        $obfuscator = new JavaScriptObfus($script);
        $script     = $obfuscator->pack($script);
        }
      else
        {
        $obfuscator = new JavaScriptPacker($script);
        $script     = $obfuscator->pack();
        }
      //---
      file_put_contents($filejs, $script);
      CLogger::write(CLoggerType::DEBUG, 'js file obfuscation finished: ' . $filejs);
      }
    $d->close();
    }

  /**
   * Модели фтп
   * @param string $ftpPath
   *
   * @return CModel_sftp|null
   */
  private function GetFtpModel($ftpPath)
    {
    //$ftp = new CModel_ftpCurl($this->uniqId,$this->m_params['ftpServer'],$this->m_params['ftpLogin'],$this->m_params['ftpPassword'],$ftpPath,$this->localPath,true);
    //$ftp = new CModel_sftp($this->uniqId,$this->m_params['ftpServer'],$this->m_params['ftpLogin'],$this->m_params['ftpPassword'],$ftpPath,$this->localPath,true);
    if(isset($this->m_params['ftpAdvanced']) && isset($this->m_params['ftpMode']))
      {
      if(strtolower($this->m_params['ftpMode']) == 'sftp') return new CModel_sftp($this->uniqId, $this->m_params['ftpServer'], !empty($this->m_params['ftpPort']) ? $this->m_params['ftpPort'] : 22, $this->m_params['ftpLogin'], $this->m_params['ftpPassword'], $ftpPath, $this->localPath, true);
      }
    elseif(isset($this->m_params['ftp_proxy']) && $this->m_params['ftp_proxy'] == 'on') return new CModel_ftpCurl($this->uniqId, $this->m_params['ftpServer'], !empty($this->m_params['ftpPort']) ? $this->m_params['ftpPort'] : 21, $this->m_params['ftpLogin'], $this->m_params['ftpPassword'], $ftpPath, $this->localPath, true);
    return null;
    }

  /**
   * Отправка файлов на фтп сервер
   */
  private function sendToFtp()
    {
    $ftpPath = $this->m_params['ftpPath'];
    //---
    if(empty($this->m_params['ftpServer']))
      {
      CLogger::write(CLoggerType::ERROR, 'ftp not upload, not ftp server');
      return null;
      }
    //---
    $ftp = $this->GetFtpModel($ftpPath);
    //--- если нет ничего то просто по фтп
    if($ftp == null) $ftp = new CModel_ftp($this->uniqId, $this->m_params['ftpServer'], !empty($this->m_params['ftpPort']) ? $this->m_params['ftpPort'] : 21, $this->m_params['ftpLogin'], $this->m_params['ftpPassword'], $ftpPath, $this->localPath, true);
    //---
    $ftp->Start();
    return $ftp;
    }

  /**
   * создадим файл robots.txt
   */
  private function CreateRobotsTxt()
    {
    $robots = new CModel_robots();
    $robots->CreateRobotsTxt($this->m_params['nextUrl'], $this->localPath, $this->template->getTemplatePath(),isset($this->m_params['createSiteMap']) && $this->m_params['createSiteMap']);
    }

  /**
   * Генерация динамической страницы
   * @param int $i
   * @param string $content
   */
  private function GenetateDynamicPage($i, $content, $page_name)
    {
    //--- получение всех нужных данных
    CLogger::write(CLoggerType::DEBUG, 'get data for dynamic: ' . $i);
    //---
    $data              = $this->m_model_dynamic->DynamicGetData($i, $this->m_current_keyword, $content);
    $data['page_name'] = $page_name;
    //--- вызовим функцию по окончанию замены всех макросов
    $key_temp = $this->m_current_keyword->getKeyword();
    $this->m_model_plugins->OnEndMacros($key_temp, $data, $i);
    //---
    CLogger::write(CLoggerType::DEBUG, 'get serialize data: ' . $i);
    //--- сохранение данных в файл
    $ser_data = serialize($data);
    CLogger::write(CLoggerType::DEBUG, 'try write data: ' . $i);
    //--- сохранение данных в файл
    $this->m_model_dynamic->DynamicWriteFile($i, $ser_data);
    //---
    CLogger::write(CLoggerType::DEBUG, 'create dynamic file ' . $i . ', length data: ' . strlen($ser_data));
    }
  }

?>