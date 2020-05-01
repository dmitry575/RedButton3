<?php
//+------------------------------------------------------------------+
//|                  Copyright c 2012, RedButton Web Sites Generator |
//|                                      http://www.getredbutton.com |
//+------------------------------------------------------------------+
/**
 * КАК ХРАНИТЬ ССЫЛКИ ДЛЯ ЛИНКОВКИ:
 *
 * В каждой тематической папке (data/links/music, data/links/soft и т.п.)
 * будет 2 файла:
 *
 * Файл "data/links/music/domains.data.php" со сссылками на домены, вот в
 * таком виде:
 *
 * таймстамп|кол-во страниц|sveta12.narod.ru|имя_файла|группа света скачать
 * таймстамп|кол-во страниц|rock666.narod.ru|имя_файла|рок музыка скачать
 * таймстамп|кол-во страниц|bravo87.narod.ru|имя_файла|группа браво mp3
 *
 *
 * Файл "data/links/music/pages.data.php" со ссылками на страницы для
 * всех доменов, вот в таком виде:
 *
 * sveta12.narod.ru/page2.html|скачать группу света
 * sveta12.narod.ru/page2.html|света mp3
 * sveta12.narod.ru/page3.html|скачать бесплатно света
 * rock666.narod.ru/page2.html|король и шут
 * rock666.narod.ru/page3.html|скачать гражданскую оборону
 * bravo87.narod.ru/page2.html|скачать группу браво
 * bravo87.narod.ru/page3.html|браво альбомы
 * bravo87.narod.ru/page4.html|группа браво клипы
 *
 *
 * Когда поступит команда на удаление определенного домена или на удаление
 * ссылок старше 1-2 месяцев - мы сможем легко это сделать.
 */
/**
 *
 * Класс для управления ссылками
 * @author User
 *
 */
class CModel_links
  {
  /**
   *
   * имя файла для хранения всех ссылок на довреи
   * @var string
   */
  const FILE_DOMAINS = "url.data.php";
  /**
   *
   * имя файла для хранения всех ссылок
   * @var string
   */
  const FILE_LINKS = "pages.data.php";
  /**
   *
   * имя файла для хранения всех ссылок в формате
   * @var string
   */
  const FILE_ALL_PAGES = "links.data.php";
  /**
   *
   * размер блока под урл
   * @var int
   */
  const SIZE_URL_BLOCK = 256;
  /**
   *
   * размер блока под кейворд
   * @var int
   */
  const SIZE_KEYWORD_BLOCK = 256;
  /**
   *
   * путь к папке с урлами
   * @var string
   */
  const PATH = "data/links";
  /**
   *
   * файл куда записываем абсолютно все ссылки
   * @var string
   */
  const DEFAULT_NAME_ALL_LINKS = 'all.txt';
  /**
   * все ссылки
   */
  const FILTER_ALL = 0;
  /**
   * ссылки на главную страницу и sitemap
   */
  const FILTER_INDEX = 1;
  /**
   *
   * Путь к ссылкам
   * @var string
   */
  private $m_path;
  /**
   *
   * Файл к списку ссылок
   * @var string
   */
  private $m_file_links;
  /**
   *
   * Открытый файл с ссылками
   * @var object
   */
  private $m_handle_file_links;
  /**
   *
   * Размер файла ссылками
   * @var int
   */
  private $m_size_file_links;
  /**
   *
   * Файл к списку доменов
   * @var string
   */
  private $m_file_domains;
  /**
   *
   * Нужно ли сохранять данные о ссылках
   * @var string
   */
  private $m_save_links;
  /**
   *
   * Нужно ли сохранять данные о ссылках в один файл
   * @var string
   */
  private $m_save_links_one;
  /**
   *
   * Имя файла куда будем скидывать абсолютно все ссылки
   * @var string
   */
  private $m_filename_links_one;
  /**
   *
   * ссылка на дорвей
   * @var string
   */
  private $m_url;
  /**
   *
   * первый кейворд
   * @var string
   */
  private $m_first_keyword = '';
  /**
   *
   * Параметры
   * @var array
   */
  private $m_params;
  /**
   *
   * Темповое имя файла
   * @var string
   */
  private $m_temp_filename = '';
  /**
   *
   * открытый темповый файл
   * @var object
   */
  private $m_handel_temp_file;
  /**
   *
   * контент для сохранения
   * @var string
   */
  private $m_content_save = '';
  /**
   *
   * Темповая папка
   * @var string
   */
  private $m_temp_path = '/data/tmp/';
  /**
   * Модуль для работы с кейвордами
   * @var CModel_keywords
   */
  private $m_model_keywords;
  /**
   * Все открытые файлы
   * @var array
   */
  private $m_list_files = array();
  /**
   * Все ссылки из разных файлов
   * @var array
   */
  private $m_list_links = array();
  /**
   * Все урлы
   * @var array
   */
  private $m_list_urls = array();
  /**
   * Все анкоры
   * @var array
   */
  private $m_list_anchors = array();

  /**
   * Пустой конструктор
   */
  public function __construct()
    {
    }

  /**
   * Конструктор
   *
   * @param array $params
   * @param $model_keywords
   * @param string $temp_dir
   */
  public function Init(&$params, &$model_keywords, $temp_dir = './data/tmp/')
    {
    $this->m_params         = $params;
    $this->m_model_keywords = $model_keywords;
    //--- а нужно ли вообще что либо сохранять
    $this->m_save_links     = $this->m_params['saveLink'];
    $this->m_save_links_one = $this->m_params['saveLinkOne'];
    $this->m_url            = $this->m_params['nextUrl'];
    //--- путь
    $subdir       = !empty($this->m_params['saveLinkPath']) ? $this->m_params['saveLinkPath'] : '';
    $this->m_path = self::PATH . '/' . trim($subdir, '/');
    //--- имя файла, если нужно скидывать все данные в один файл
    $this->m_filename_links_one = self::DEFAULT_NAME_ALL_LINKS; //!empty($this->m_params['saveLinkPathOne']) ? $this->m_params['saveLinkPathOne'] : 'all.txt';
    //--- полное имя к файлу со ссылками для данного дорвея
    $this->m_file_links = $this->m_path . '/' . self::FILE_LINKS; //$this->GetLinksFilename();
    //--- полное имя файла с информацией о всех сгенерированных дорвеях
    $this->m_file_domains = $this->m_path . '/' . self::FILE_DOMAINS;
    //--- установим темповую папку
    $this->m_temp_path = $temp_dir;
    if(!file_exists($this->m_temp_path))
      {
      if(mkdir($this->m_temp_path, true, 0777)) CLogger::write(CLoggerType::DEBUG, 'links: temp path created: ' . $this->m_temp_path);
      }
    //--- создание папки
    if(!file_exists($this->m_path))
      {
      if(mkdir($this->m_path, true, 0777)) CLogger::write(CLoggerType::DEBUG, 'links: links path created: ' . $this->m_path);
      }
    }

  /**
   * загрузка урлов из файлов
   * @param $files_urls
   * @return array
   */
  public static function LoadUrls($files_urls, $is_cache = 1)
    {
    if(empty($files_urls)) return array();
//---
    $is_cache = $is_cache && extension_loaded('apc');
//---
    $result = array();
    foreach($files_urls as $filename)
      {
      $fname = self::PATH . '/' . trim($filename, '/');
      if(file_exists($fname))
        {
        if($is_cache)
          {
          $data = apc_fetch($fname);
          //--- наличие кеша
          if(empty($data))
            {
            $data = file($fname);
            array_walk($data, create_function('&$val', '$val = trim($val);'));
            apc_store($fname, $data, 1200);
            }
          else
            {
            CLogger::write(CLoggerType::DEBUG, 'links: loading urls from apc_store: ' . $fname);
            }
          if(!empty($data)) $result = array_merge($result, $data);
          }
        else
          {
          $data = file($fname);
          array_walk($data, create_function('&$val', '$val = trim($val);'));
          if(!empty($data)) $result = array_merge($result, $data);
          CLogger::write(CLoggerType::DEBUG, 'links: loading urls from file: ' . $fname);
          }
        }
      }
    CLogger::write(CLoggerType::DEBUG, 'links: loaded urls ' . count($result));
    return $result;
    }

  /**
   * загрузка анкоров
   * @param $files_anchors
   * @return array
   */
  public static function LoadAnchors($files_anchors, $is_cache = 1)
    {
    if(empty($files_anchors)) return array();
//---
    $is_cache = $is_cache && extension_loaded('apc');
//---
    $result = array();
    foreach($files_anchors as $filename)
      {
      $fname = CModel_keywords::PATH_KEYWORDS . '/' . trim($filename, '/');
      //---
      if(file_exists($fname))
        {
        if($is_cache)
          {
          $data = apc_fetch($fname);
          //--- наличие кеша
          if(empty($data))
            {
            $data = file($fname);
            array_walk($data, create_function('&$val', '$val = trim($val);'));
            if(!empty($data)) apc_store($fname, $data, 2400);
            }
          else
            {
            CLogger::write(CLoggerType::DEBUG, 'links: loading anchors from apc_store: ' . $fname);
            }
          if(!empty($data)) $result = array_merge($result, $data);
          }
        else
          {
          $data = file($fname);
          array_walk($data, create_function('&$val', '$val = trim($val);'));
          if(!empty($data)) $result = array_merge($result, $data);
          CLogger::write(CLoggerType::DEBUG, 'links: loading anchors from file: ' . $fname);
          }
        }
      }
    CLogger::write(CLoggerType::DEBUG, 'links: loaded anchors ' . count($result));
    return $result;
    }

  /**
   *
   * Получение имени темпового файла для ссылок
   */
  private function GetTempLinksFilename()
    {
    $temp_name     = 'links_temp_' . md5(uniqid());
    $temp_filename = $this->m_temp_path . $temp_name;
    $i             = 0;
    while(file_exists($temp_filename))
      {
      $temp_filename = $this->m_temp_path . $temp_name . '_' . $i;
      $i++;
      }
    return $temp_filename;
    }

  /**
   *
   * Получение имени темпового файла для доменов
   */
  private function GetTempDomainsFilename()
    {
    $temp_name     = 'domains_temp_' . md5(uniqid());
    $temp_filename = $this->m_temp_path . $temp_name;
    $i             = 0;
    while(file_exists($temp_filename))
      {
      $temp_filename = $this->m_temp_path . $temp_name . '_' . $i;
      $i++;
      }
    return $temp_filename;
    }

  /**
   * Сохраним ссылку на доврей в локальный файл
   * @param bool $is_get_list
   * @return array|null
   */
  public function SaveDoorwayLinkToFile($is_get_list = false)
    {
    if(empty($this->m_save_links) && empty($this->m_filename_links_one)) return null;
    //---
    $new_link = rtrim($this->m_url, '/');
    //--- закроем файл
    if($this->m_handel_temp_file)
      {
      //--- сбросим данные
      fwrite($this->m_handel_temp_file, $this->m_content_save);
      $this->m_content_save = '';
      fclose($this->m_handel_temp_file);
      }
    //---
    if(!file_exists($this->m_temp_filename))
      {
      CLogger::write(CLoggerType::ERROR, 'links: temp file not found: ' . $this->m_temp_filename . ', not save anything links');
      return null;
      }
    //--- возьмем весь контент из темпового файла
    $temp_data = file_get_contents($this->m_temp_filename);
    //--- вычислим количество страниц в файле
    $blok_size = self::SIZE_URL_BLOCK + self::SIZE_KEYWORD_BLOCK;
    $count     = (int)strlen($temp_data) / $blok_size;
    //---
    if(!empty($this->m_save_links) && $this->m_save_links)
      {
      //--- создадим или перекопируем файл с доменнами
      $handle_file_domains = null;
      if(file_exists($this->m_file_domains))
        {
        $lines = file($this->m_file_domains);
        //--- зачистим файл
        $handle_file_domains = fopen($this->m_file_domains, 'w');
        if(!$handle_file_domains)
          {
          CLogger::write(CLoggerType::ERROR, 'links: can not open file domains: ' . $this->m_file_domains);
          return null;
          }
        file_put_contents($this->m_file_domains, '');
        //---
        foreach($lines as $i => $link)
          {
          $l = explode('|', $link);
          if(count($l) < 4) continue;
          if(trim($l[3]) == $new_link)
            {
            continue;
            }
          fwrite($handle_file_domains, $link);
          }
        }
      else
        {
        $handle_file_domains = fopen($this->m_file_domains, 'w');
        if(!$handle_file_domains)
          {
          CLogger::write(CLoggerType::ERROR, 'links: can not open file domains: ' . $this->m_file_domains);
          return null;
          }
        }
      //--- откроем файл со списком страниц
      $handle_file_links = fopen($this->m_file_links, 'a+');
      if(!$handle_file_links)
        {
        CLogger::write(CLoggerType::ERROR, 'links: links file not found: ' . $this->m_file_links);
        return null;
        }
      //--- размер файла
      fseek($handle_file_links, 0, SEEK_END);
      $size = ftell($handle_file_links);
      //--- дата | откуда считывать из основного файла | количество блоков
      $newLine = time() . '|' . $size . '|' . $count . '|' . $new_link . '|' . $this->m_first_keyword->GetKeywordIndex(0) . '|' . ($this->m_params['type'] == 'dynamic' ? 1 : 0);
      //--- сохраним новые данные
      fwrite($handle_file_domains, $newLine . "\r\n");
      //--- запишем новые данные
      fwrite($handle_file_links, $temp_data);
      //--- все записали закроем файл
      fclose($handle_file_domains);
      fclose($handle_file_links);
      //---
      CLogger::write(CLoggerType::DEBUG, 'links: link ' . $newLine . ' save in file ' . $this->m_file_domains);
      CLogger::write(CLoggerType::DEBUG, 'links: links add to ' . $this->m_file_links . ', ' . strlen($temp_data) . 'bytes saved');
      }
    //---
    $urls = array();
    //--- нужно ли данные сохранять в общие данные
    if(!empty($this->m_filename_links_one) && $this->m_save_links_one)
      {
      $allUrlFilename = $this->m_path . '/' . self::FILE_ALL_PAGES;
      //--- проверка а нужно ли все ссылки записать в мега большой файл all.txt
      $handel_oneFile = null;
      //---
      $oneFile        = self::PATH . '/' . $this->m_filename_links_one;
      $handel_oneFile = fopen($oneFile, 'a+');
      //---
      if(($handel_all_url = fopen($allUrlFilename, 'a+')))
        {
        //--- теперь все ссылки запишем в еще один файл, в формате урл|кейворд. Запись ведем по блоком, по 100 записей
        $text_urls = '';
        for($i = 0; $i < $count; $i++)
          {
          $r = unpack("a" . self::SIZE_URL_BLOCK . "url/a" . self::SIZE_KEYWORD_BLOCK . "keyword", substr($temp_data, $i * $blok_size, $blok_size));
          $text_urls .= $r['url'] . '|' . $r['keyword'] . "\r\n";
          //--- сохраним в массив, если это нужно возвращать
          if($is_get_list)
            {
            $urls[$r['url']] = $r['keyword'];
            }
          //--- может пора записать в файл
          if(($i % 100) == 0)
            {
            fwrite($handel_all_url, $text_urls);
            if($handel_oneFile) fwrite($handel_oneFile, $text_urls);
            $text_urls = '';
            }
          }
        //---
        fwrite($handel_all_url, $text_urls);
        if($handel_oneFile) fwrite($handel_oneFile, $text_urls);
        fclose($handel_all_url);
        //---
        if($handel_oneFile)
          {
          fclose($handel_oneFile);
          CLogger::write(CLoggerType::DEBUG, 'links: links ' . $i . ' added to save in file ' . $oneFile);
          }
        CLogger::write(CLoggerType::DEBUG, 'links: links ' . $i . ' added to save in file ' . $allUrlFilename);
        //--- проверим нужно ли возвращать список
        }
      }
    //--- удалим темповый файл
    if(file_exists($this->m_temp_filename))
      {
      if(unlink($this->m_temp_filename)) CLogger::write(CLoggerType::DEBUG, 'links: temp file deleted ' . $this->m_temp_filename);
      else
      CLogger::write(CLoggerType::ERROR, 'links: temp file not delete ' . $this->m_temp_filename);
      }
    //---
    if($is_get_list) return $urls;
    return null;
    }

  /**
   * Пишем линк в файл
   *
   * @param string $keyword
   * @param string $link
   */
  public function SaveLinkToFile($keyword)
    {
    if(empty($this->m_save_links) && empty($this->m_filename_links_one)) return null;
    $link = $keyword->GetUrl();
    //--- темповый файл должен быть открыт
    if(!$this->m_handel_temp_file)
      {
      //--- темповое имя, куда будем складывать ссылки
      $this->m_temp_filename = $this->GetTempLinksFilename();
      //--- создадим темповый файл
      if($this->m_handel_temp_file = fopen($this->m_temp_filename, 'w'))
        {
        CLogger::write(CLoggerType::DEBUG, 'links: temp file created: ' . $this->m_temp_filename);
        }
      else
        {
        CLogger::write(CLoggerType::ERROR, 'links: temp file not create: ' . $this->m_temp_filename);
        }
      }
    //--- сохраним первый кейворд
    if(empty($this->m_first_keyword)) $this->m_first_keyword = $keyword;
    //--- длина строки
    $len = strlen($link);
    if($len > 7)
      {
      //--- динамический дорвей или статика
      if($this->m_params['type'] == 'dynamic')
        {
        if($len > 10 && substr($link, $len - 10) == '/index.php') $link = substr($link, 0, $len - 10);
        }
      else
        {
        //--- для статики определим имя индексного файла
        $index_name = '/index.' . $this->m_model_keywords->GetExtension();
        $index_len  = strlen($index_name);
        //---
        if($len > $index_len && substr($link, $len - $index_len) == $index_name) $link = substr($link, 0, $len - $index_len);
        }
      }
    $this->m_content_save .= pack("a" . self::SIZE_URL_BLOCK . "a" . self::SIZE_KEYWORD_BLOCK, $link, $keyword->GetKeywordIndex(0));
    //--- если контента больше чем на 100 ссылок, то сбросим в файл
    if(strlen($this->m_content_save) > 102400)
      {
      fwrite($this->m_handel_temp_file, $this->m_content_save);
      $this->m_content_save = '';
      }
    //---
    CLogger::write(CLoggerType::DEBUG, 'links: ' . $link . '|' . $keyword->GetKeywordIndex(0) . ' added to temp file');
    }

  /**
   * Получение имени файла для сохранения всех ссылок
   */
  private function GetLinksFilename()
    {
    if(empty($this->m_save_links) || !$this->m_save_links) return '';
    $filename = $this->m_path;
    if(!file_exists($filename))
      {
      mkdir($filename, 0777, true);
      CLogger::write(CLoggerType::DEBUG, 'links: create path ' . $filename);
      }
    //---
    if(!CModel_helper::IsExistHttp($this->m_url))
      {
      $n = $this->m_url;
      }
    else
      {
      //--- уберем в начале http://
      $n = CModel_helper::DeleteHttp($this->m_url);
      }
    $n = trim($n, '/');
    $filename .= '/links_' . CModel_helper::GenerateFileName($n) . '.php';
    //--- зачистим файла
    if(file_exists($filename)) file_put_contents($filename, '');
    return $filename;
    }

  /**
   * Папка с ссылками
   */
  function GetPath()
    {
    return self::PATH;
    }

  /**
   * Получение списка всех категорий
   * (папок, которые в папке /data/links)
   */
  function GetFirstCategory()
    {
    return CModel_helper::FirstFromDirs(self::PATH);
    }

  /**
   * Получение списка доменов из
   * папки для указанной категории
   */
  function GetListDomains($catDir)
    {
    $list = array();
    //---
    $fileName = self::PATH . '/' . $catDir . '/' . self::FILE_DOMAINS;
    //---
    if(!file_exists($fileName)) return $list;
    //---
    $domains = array_reverse(explode("\n", file_get_contents($fileName)));
    //---
    for($i = 0, $sz = sizeof($domains); $i < $sz; $i++)
      {
      $item = trim($domains[$i]);
      if(empty($item)) continue;
      //---
      $list[] = explode('|', $item);
      }
    //---
    return $list;
    }

  /**
   * Получение главных ссылок
   * @param $domain
   * @param $type
   * @param $pages
   * @return array
   */
  public function GetMainsLinks($domain, $type, $pages)
    {
    $list   = array();
    $domain = rtrim($domain, '/\\');
    foreach($pages as $r)
      {
      $r['url'] = $domain . $r['url'];
      $key      = trim($r['keyword']);
      //---
      switch($type)
      {
        case 'text':
        default:
          $list[] = $r['url'] . '|' . $key . "\n";
          break;
        //---
        case 'html':
          $list[] = "<a href=\"" . $r['url'] . "\">" . $key . "</a>\n";
          break;
        //---
        case 'bbcode':
          $list[] = "[url=" . $r['url'] . "]" . $key . "[/url]\n";
          break;
      }
      }
    return $list;
    }

  /**
   * Получение списка доменов из
   * папки для указанной категории
   */
  public function GetListLinks($catDir, $domain, $keyword, $position, $links_cnt, $type = 'text', $filter = 0)
    {
    $list = array();
    //---
    if(empty($domain)) return $list;
    if($filter == self::FILTER_INDEX) return $this->GetMainsLinks($domain, $type, array(array('url'     => '/',
                                                                                              'keyword' => $keyword),
                                                                                        array('url'     => '/sitemap.html',
                                                                                              'keyword' => 'Sitemap ' . $keyword)));
    //---
    $fileName = self::PATH . '/' . $catDir . '/' . self::FILE_LINKS;
    //---
    if(!file_exists($fileName))
      {
      CLogger::write(CLoggerType::ERROR, 'links: file links not found: ' . $fileName);
      return $list;
      }
    //--- открываем файл
    $fp = fopen($fileName, "r");
    if(!$fp)
      {
      CLogger::write(CLoggerType::ERROR, 'links: file links not open: ' . $fileName);
      return $list;
      }
    //--- переход на нужную позицию
    fseek($fp, $position);
    if(ftell($fp) != $position)
      {
      CLogger::write(CLoggerType::ERROR, 'links: file links not go to position ' . $position . ' in  ' . $fileName);
      return $list;
      }
    //--- вычитываем данные
    $blok_size = self::SIZE_KEYWORD_BLOCK + self::SIZE_URL_BLOCK;
    //--- вычитаем весь блок данныех
    $data = fread($fp, $links_cnt * $blok_size);
    if(empty($data))
      {
      CLogger::write(CLoggerType::ERROR, 'links: not read links position ' . $position . ' cnt:' . $links_cnt . ' in  ' . $fileName);
      return $list;
      }
    for($i = 0; $i < $links_cnt; $i++)
      {
      $r = unpack("a" . self::SIZE_URL_BLOCK . "url/a" . self::SIZE_KEYWORD_BLOCK . "keyword", substr($data, $i * $blok_size, $blok_size));
      switch($type)
      {
        case 'text':
        default:
          $list[] = trim($r['url']) . '|' . trim($r['keyword']) . "\n";
          break;
        //---
        case 'html':
          $list[] = "<a href=\"" . trim($r['url']) . "\">" . trim($r['keyword']) . "</a>\n";
          break;
        //---
        case 'bbcode':
          $list[] = "[url=" . trim($r['url']) . "]" . trim($r['keyword']) . "[/url]\n";
          break;
      }
      }
    //---
    return $list;
    }

  /**
   *
   * удаление информации о конкретном домене
   * @param string $catDir
   * @param string $domain
   */
  public function DeleteDomain($catDir, $domain)
    {
    if(empty($domain) || empty($catDir)) return false;
    //--- проверка файла с доменом
    $domainFileName = self::PATH . '/' . $catDir . '/' . self::FILE_DOMAINS;
    //---
    if(!file_exists($domainFileName))
      {
      CLogger::write(CLoggerType::ERROR, 'links: domains file not exists: ' . $domainFileName);
      return false;
      }
    //--- проверка файла
    $linksFileName = self::PATH . '/' . $catDir . '/' . self::FILE_LINKS;
    if(!file_exists($linksFileName))
      {
      CLogger::write(CLoggerType::ERROR, 'links: links file not exists: ' . $linksFileName);
      return false;
      }
    //---
    $handel_domain = fopen($domainFileName, "r");
    if(!$handel_domain)
      {
      CLogger::write(CLoggerType::ERROR, 'links: can not open file: ' . $domainFileName);
      return false;
      }
    //---
    $handel_links = fopen($linksFileName, "r");
    if(!$handel_links)
      {
      CLogger::write(CLoggerType::ERROR, 'links: can not open file: ' . $linksFileName);
      return false;
      }
    //---
    $temp_domain = $this->GetTempDomainsFilename();
    //---
    $handel_temp_domain = fopen($temp_domain, 'w');
    if(!$handel_temp_domain)
      {
      CLogger::write(CLoggerType::ERROR, 'links: can not open temp domain file: ' . $temp_domain);
      return false;
      }
    //---
    $temp_links = $this->GetTempLinksFilename();
    //---
    $handel_temp_links = fopen($temp_links, 'w');
    if(!$handel_temp_links)
      {
      CLogger::write(CLoggerType::ERROR, 'links: can not open temp links file: ' . $temp_links);
      return false;
      }
    //---
    $block_size = self::SIZE_KEYWORD_BLOCK + self::SIZE_URL_BLOCK;
    //--- вычитываем домены
    while(!feof($handel_domain))
      {
      //---
      $buffer = fgets($handel_domain, 4096);
      $ar     = explode('|', $buffer);
      if(empty($ar[LinskFormat::DOMAIN])) continue;
      //---
      if($ar[LinskFormat::DOMAIN] == $domain)
        {
        CLogger::write(CLoggerType::DEBUG, 'links: delete domain: ' . $domain);
        continue;
        }
      //--- вся эта фигня нужно, чтобы не образовывались дыры в файле со ссылками
      //--- переходим на нужную позицию
      fseek($handel_links, $ar[LinskFormat::POSITION], SEEK_SET);
      //--- вычитываем в память все линки
      $count_links = (int)$ar[LinskFormat::COUNT];
      $buf_links   = fread($handel_links, $block_size * $count_links);
      //--- текущая позиция
      $position = ftell($handel_temp_links);
      //---
      fwrite($handel_temp_links, $buf_links);
      //---
      $newLine = $ar[LinskFormat::TIME] . '|' . $position . '|' . $count_links . '|' . $ar[LinskFormat::DOMAIN] . '|' . $ar[LinskFormat::KEYWORD];
      //--- запишем в темповый файл информацию о домене
      fwrite($handel_temp_domain, $newLine . "\r\n");
      }
    //--- все файлы закроем
    fclose($handel_temp_domain);
    fclose($handel_temp_links);
    fclose($handel_domain);
    fclose($handel_links);
    //--- теперь просто копируем
    if(copy($temp_links, $linksFileName))
      {
      CLogger::write(CLoggerType::DEBUG, 'links: links copy: from: ' . $temp_links . ' to ' . $linksFileName);
      }
    if(copy($temp_domain, $domainFileName))
      {
      CLogger::write(CLoggerType::DEBUG, 'links: links copy: from: ' . $temp_domain . ' to ' . $domainFileName);
      }
    //--- удаление файлов
    if(file_exists($temp_links))
      {
      unlink($temp_links);
      CLogger::write(CLoggerType::DEBUG, 'links: delete temp links file: ' . $temp_links);
      }
    if(file_exists($temp_domain))
      {
      unlink($temp_domain);
      CLogger::write(CLoggerType::DEBUG, 'links: delete temp domain file: ' . $temp_domain);
      }
    }

  /**
   *
   * удаление информации о конкретном домене
   * @param string $catDir
   * @param string $domain
   */
  public function DeleteAllDomain($catDir)
    {
    if(empty($catDir)) return false;
    //--- проверка файла с доменом
    $domainFileName = self::PATH . '/' . $catDir . '/' . self::FILE_DOMAINS;
    //---
    if(!file_exists($domainFileName))
      {
      CLogger::write(CLoggerType::ERROR, 'links: domains file not exists: ' . $domainFileName);
      }
    else
      {
      //--- удаление фала
      if(unlink($domainFileName))
        {
        CLogger::write(CLoggerType::DEBUG, 'links: domains file deleted: ' . $domainFileName);
        }
      }
    //--- проверка файла
    $linksFileName = self::PATH . '/' . $catDir . '/' . self::FILE_LINKS;
    if(!file_exists($linksFileName))
      {
      CLogger::write(CLoggerType::ERROR, 'links: links file not exists: ' . $linksFileName);
      }
    else
      {
      //--- удаление фала
      if(unlink($linksFileName))
        {
        CLogger::write(CLoggerType::DEBUG, 'links: links file deleted: ' . $linksFileName);
        }
      }
    //---
    $allUrlFilename = self::PATH . '/' . $catDir . '/' . self::FILE_ALL_PAGES;
    if(!file_exists($allUrlFilename))
      {
      CLogger::write(CLoggerType::ERROR, 'links: all urls file not exists: ' . $allUrlFilename);
      }
    else
      {
      //--- удаление фала
      if(unlink($allUrlFilename))
        {
        CLogger::write(CLoggerType::DEBUG, 'links: all urls file deleted: ' . $allUrlFilename);
        }
      }
    //---
    return true;
    }

  /**
   *
   * Открытие файла с сылками на чтение
   */
  private function OpenLinksFile()
    {
    if($this->m_handle_file_links == null)
      {
      if(!file_exists($this->m_file_links))
        {
        CLogger::write(CLoggerType::DEBUG, 'links: links file not found: ' . $this->m_file_links);
        return false;
        }
      //--- открываем файл на чтение
      $this->m_handle_file_links = fopen($this->m_file_links, 'r');
      if(!$this->m_handle_file_links)
        {
        CLogger::write(CLoggerType::ERROR, 'links: links file not open: ' . $this->m_file_links);
        return false;
        }
      $this->m_size_file_links = filesize($this->m_file_links);
      }
    return true;
    }

  /**
   *
   * Получаем размер блока для записи в файл
   */
  private function GetBlockSize()
    {
    return self::SIZE_KEYWORD_BLOCK + self::SIZE_URL_BLOCK;
    }

  /**
   *
   * Получение случайного блока ссылкой и кейвордом
   */
  private function GetRandBlockLinks()
    {
    //--- попытка открыть файл или просто проверка на открытость
    if(!$this->OpenLinksFile()) return null;
    //--- размер блока данных
    $block_size = $this->GetBlockSize();
    //--- случайным образом находим какой блок считывать
    $pos = rand(0, (int)$this->m_size_file_links / $block_size);
    //--- переход на нужную позицию
    fseek($this->m_handle_file_links, $pos * $block_size, SEEK_SET);
    //--- вычитка данных из файла
    $d = fread($this->m_handle_file_links, $block_size);
    if(empty($d))
      {
      CLogger::write(CLoggerType::ERROR, 'links: not read block data from: ' . $this->m_file_links);
      return null;
      }
    //--- распаковываем данные в массив
    return unpack("a" . self::SIZE_URL_BLOCK . "url/a" . self::SIZE_KEYWORD_BLOCK . "keyword", $d);
    }

  /**
   * [RAND-SPAM-URL] - случайная ссылка (просто ссылка) для спама на сгенеренную страницу дорвея текущей тематики
   */
  public function MacrosSpamUrl()
    {
    $data = $this->GetRandBlockLinks();
    if(empty($data)) return '';
    //---
    return $data['url'];
    }

  /**
   * [RAND-SPAM-LINK] - случайная гиперссылка для спама
   */
  public function MacrosSpamLink()
    {
    $data = $this->GetRandBlockLinks();
    if(empty($data)) return '';
    return '<a href="' . $data['url'] . '">' . $data['keyword'] . '</a>';
    }

  /**
   * [RAND-SPAM-UC-LINK] - случайная гиперссылка для спама, где кейворд с большой буквы
   */
  public function MacrosSpamUCLink()
    {
    $data = $this->GetRandBlockLinks();
    if(empty($data)) return '';
    return '<a href="' . $data['url'] . '">' . CModel_helper::GetUcWords($data['keyword']) . '</a>';
    }

  /**
   * Получение случайной ссылки
   * @param $matches
   */
  public function GetRandUcLinkFromFile($files)
    {
    if(is_array($files))
      {
      $filename = $files[rand(0, count($files) - 1)];
      }
    else
    $filename = $files;
    //---
    $fullname = self::PATH . '/' . (!empty($path) ? (trim($path, '/') . '/') : "") . $filename;
    if(isset($this->m_list_links[$fullname]))
      {
      $ar_links = $this->m_list_links[$fullname];
      }
    else
      {
      $ar_links = $this->LoadLinksFile($fullname);
      if($ar_links == null) return null;
      }
    //---
    $link = $ar_links[rand(0, count($ar_links) - 1)];
    $data = explode('|', $link, 2);
    //---
    return '<a href="' . $data[0] . '">' . CModel_helper::GetUcWords(!empty($data[1]) ? trim($data[1]) : trim($data[0])) . '</a>';
    }

  /**
   * Получение случайной ссылки
   * @param $matches
   */
  public function GetObjRandLinkFromFile($files)
    {
    if(is_array($files))
      {
      $filename = $files[rand(0, count($files) - 1)];
      }
    else
    $filename = $files;
    //---
    $fullname = self::PATH . '/' . (!empty($path) ? (trim($path, '/') . '/') : "") . $filename;
    if(isset($this->m_list_links[$fullname]))
      {
      $ar_links = $this->m_list_links[$fullname];
      }
    else
      {
      $ar_links = $this->LoadLinksFile($fullname);
      if($ar_links == null) return null;
      }
    //---
    $link = $ar_links[rand(0, count($ar_links) - 1)];
    $data = explode('|', $link, 2);
    //---
    return array('url' => $data[0],
                 'key' => (!empty($data[1]) ? trim($data[1]) : trim($data[0])));
    }

  /**
   * Получение случайной ссылки
   * @param $files
   * @return null|string
   */
  public function GetRandLinkFromFile($files)
    {
    if(is_array($files))
      {
      $filename = $files[rand(0, count($files) - 1)];
      }
    else
    $filename = $files;
    //---
    $fullname = self::PATH . '/' . (!empty($path) ? (trim($path, '/') . '/') : "") . $filename;
    //---
    if(isset($this->m_list_links[$fullname]))
      {
      $ar_links = $this->m_list_links[$fullname];
      }
    else
      {
      $ar_links = $this->LoadLinksFile($fullname);
      if($ar_links == null) return null;
      }
    //---
    $link = $ar_links[rand(0, count($ar_links) - 1)];
    $data = explode('|', $link, 2);
    //---
    return '<a href="' . $data[0] . '">' . (!empty($data[1]) ? trim($data[1]) : trim($data[0])) . '</a>';
    }

  /**
   * Получение урла из файла
   * @param $files
   * @return null
   */
  public function GetRandUrlFromFiles($files)
    {
    if(is_array($files))
      {
      $filename = $files[rand(0, count($files) - 1)];
      }
    else
    $filename = $files;
    //---
    $fullname = self::PATH . '/' . (!empty($path) ? (trim($path, '/') . '/') : "") . $filename;
    if(isset($this->m_list_links[$fullname]))
      {
      $ar_links = $this->m_list_links[$fullname];
      }
    else
      {
      $ar_links = $this->LoadLinksFile($fullname);
      if($ar_links == null) return null;
      }
    //---
    $link = $ar_links[rand(0, count($ar_links) - 1)];
    $ar   = explode('|', $link, 2);
    return $ar[0];
    }

  /**
   * [RAND-SPAM-UC-LINK-5-10] - список (<ul>) случайных гиперссылок для спама, где кейворд с большой буквы

   */
  public function MacrosSpamUCManyLinks($matches)
    {
    $min    = (int)$matches[1];
    $max    = (int)$matches[2];
    $count  = rand($min, $max);
    $result = '';
    for($i = 0; $i < $count; $i++)
      {
      $data = $this->MacrosSpamUCLink();
      //--- ничего нет выходим
      if(empty($data)) break;
      //---
      $result .= '<li>' . $data . '</li>';
      }
    return !empty($result) ? ('<ul>' . $result . '</ul>') : '';
    }

  /**
   * Вывод в output ссылок на карту сайта и на главную страницу
   * @param $download_type
   * @param $fname
   */
  public function PrintMainsLinks($download_type, $fname, $format = '')
    {
    //---
    if(!file_exists($fname))
      {
      CLogger::write(CLoggerType::ERROR, 'links: error open: ' . $fname . ' not exists');
      C404::Show404();
      exit;
      }
    //---
    $fp = fopen($fname, 'r');
    if(!$fp)
      {
      CLogger::write(CLoggerType::ERROR, 'links: error open: ' . $fname . ' not opened');
      C404::Show404();
      exit;
      }
    //--- какое форматирование использовать
    $self_format = !empty($format);
    while(($buffer = fgets($fp, 4096)) !== false)
      {
      $ar = explode('|', $buffer);
      if(empty($ar[LinskFormat::DOMAIN])) continue;
      //---
      $domain = trim($ar[LinskFormat::DOMAIN], '/');
      $key    = trim($ar[LinskFormat::KEYWORD]);
      //---
      if($self_format) $this->EchoSitemapSelfFormatedLink($key, $domain, $format);
      else $this->EchoSitemapFromatLink($download_type, $key, $domain);
      }
    }

//--- вывод свой формат ссылок
  private function EchoSitemapSelfFormatedLink(&$key, &$domain, $format)
    {
    echo str_replace(array('{url}',
                           '{title}'), array($domain,
                                             $key), $format) . "\r\n";
    }

//--- стандартные форматы ссылок
  private function EchoSitemapFromatLink($download_type, &$key, &$domain)
    {
    switch($download_type)
    {
      //---
      case 'html':
        echo '<a href="', $domain, '">', $key, "</a>\r\n";
        echo '<a href="', $domain, '/sitemap.html">Sitemap ', $key, "</a>\r\n";
        break;
      //---
      case 'bbcode':
        echo '[url=', $domain, ']', $key, "[/url]\r\n";
        echo '[url=', $domain, '/sitemap.html]Sitemap ', $key, "[/url]\r\n";
        break;
      //---
      default:
        echo $domain, '|', $key, "\r\n";
        echo $domain, '/sitemap.html|Sitemap ', $key, "\r\n";
    }
    }

  /**
   * распаковка данных
   * @param $data
   * @return array
   */
  private function UnPackPageData($data)
    {
    return unpack("a" . self::SIZE_URL_BLOCK . "url/a" . self::SIZE_KEYWORD_BLOCK . "keyword", $data);
    }

  /**
   * ПОлучение списка случайных ссылок
   * @param $path
   * @param int $count
   * @param $files
   * @param $handel
   * @param $size
   * @return array|null
   */
  public function GetRandOldLinks($path, $count, $files, &$handel, &$size)
    {
    //--- если нужны ссылки на старые дорвеи из указанного файла
    if(!empty($files)) //--- файл открыть не смогли, попробуем открыть указанные файлы
    return array($this->GetObjRandLinkFromFile($files));
    if($handel == null)
      {
      $handel = $this->OpenLinksFileName($path, $size);
      if($handel == null) return null;
      //---
      }
    $ret = array();
    //---
    $block_size = self::SIZE_URL_BLOCK + self::SIZE_KEYWORD_BLOCK;
//--- количество блоков
    $all = $size / $block_size;
    for($i = 0; $i < $count; $i++)
      {
      $rand = rand(0, $all);
      fseek($handel, $rand * $block_size, 0);
      $data = fread($handel, $block_size);
      //---
      if(empty($data)) continue;
      $r     = $this->UnPackPageData($data);
      $ret[] = array('key' => $r['keyword'],
                     'url' => $r['url']);
      }
    return $ret;
    }

  /**
   * ПОлучение списка случайных ссылок из файла
   *
   * @param $path
   * @param $filename
   * @param int $count
   *
   * @return array|null
   */
  public function GetRandLinksFromFile($path, $filename, $count)
    {
    $ret = array();
    //---
    $fullname = self::PATH . '/' . (!empty($path) ? (trim($path, '/') . '/') : "") . $filename;
    if(isset($this->m_list_links[$fullname]))
      {
      $ar_links = $this->m_list_links[$fullname];
      }
    else
      {
      $ar_links = $this->LoadLinksFile($fullname);
      if($ar_links == null) return null;
      }
    //---
    shuffle($ar_links);
    //---
    $ar_links = array_slice($ar_links, 0, $count);
    //---
    foreach($ar_links as $link)
      {
      $ar    = explode('|', $link, 2);
      $ret[] = array('key' => !empty($ar[1]) ? trim($ar[1]) : trim($ar[0]),
                     'url' => trim($ar[0]));
      }
    //--- количество блоков
    return $ret;
    }

  /**
   * ПОлучение списка случайных ссылок из файла
   *
   * @param $path
   * @param $filename
   * @param int $count
   *
   * @return array|null
   */
  public function GetRandLinksFromUrlAnchors($anchor, $url_file, $urls_counts, $no_anchor_count)
    {
    $ret = array();
    //---
    $fullname = self::PATH . '/' . (!empty($path) ? (trim($path, '/') . '/') : "") . $url_file;
    if(isset($this->m_list_links[$fullname]))
      {
      $ar_urls = $this->m_list_links[$fullname];
      }
    else
      {
      $ar_urls = $this->LoadLinksFile($fullname);
      if($ar_urls == null) return null;
      }
    //---
    shuffle($ar_urls);
    //---
    $ar_urls  = array_slice($ar_urls, 0, $urls_counts);
    $fullname = CModel_keywords::PATH_KEYWORDS . '/' . (!empty($path) ? (trim($path, '/') . '/') : "") . $anchor;
    if(isset($this->m_list_anchors[$fullname]))
      {
      $ar_anchors = $this->m_list_anchors[$fullname];
      }
    else
      {
      $ar_anchors = $this->LoadAnchorsFile($fullname);
      if($ar_anchors == null) return null;
      }
    //---
    foreach($ar_urls as $url)
      {
      $percent = rand(1, 100);
      if($percent < $no_anchor_count) $key = $url;
      else
      $key = $ar_anchors[array_rand($ar_anchors)];
      //
      $ret[] = array('key' => $key,
                     'url' => trim($url));
      }
    //--- количество блоков
    return $ret;
    }

  /**
   * ПОлучение списка случайных ссылок из файла
   *
   * @param $path
   * @param $filename
   * @param int $count
   *
   * @return array|null
   */
  public function GetNextLinksFromFile($path, $filename, $count)
    {
    $ret = array();
    //---
    $fullname      = self::PATH . '/' . (!empty($path) ? (trim($path, '/') . '/') : "") . $filename;
    $fullname_next = self::PATH . '/' . (!empty($path) ? (trim($path, '/') . '/') : "") . $filename . '.next';
    if(isset($this->m_list_links[$fullname]))
      {
      $ar_links = $this->m_list_links[$fullname];
      }
    else
      {
      $ar_links = $this->LoadLinksFile($fullname);
      if($ar_links == null) return null;
      }
    //---
    $from_links = 0;
    if(file_exists($fullname_next))
      {
      $from_links = (int)file_get_contents($fullname_next);
      if($from_links > count($ar_links)) $from_links = 0;
      }
    //---
    $ar_links = array_slice($ar_links, $from_links, $count);
    file_put_contents($fullname_next, $from_links + $count);
    //---
    foreach($ar_links as $link)
      {
      $ar    = explode('|', $link, 2);
      $ret[] = array('key' => !empty($ar[1]) ? trim($ar[1]) : trim($ar[0]),
                     'url' => trim($ar[0]));
      }
    //--- количество блоков
    return $ret;
    }

  /**
   * Загрузка ссылок
   * @param $filename
   * @return array|null
   */
  private function LoadLinksFile($filename)
    {
    if(!file_exists($filename))
      {
      CLogger::write(CLoggerType::ERROR, 'links: load links from file: ' . $filename);
      return null;
      }
    $data = file($filename);
    array_walk($data, create_function('&$val', '$val = trim($val);'));
    $this->m_list_links[$filename] = $data;
    CLogger::write(CLoggerType::DEBUG, 'links: load links from file: ' . $filename);
    //--- количество блоков
    return $data;
    }

  /**
   * Загрузка ссылок
   * @param $filename
   * @return array|null
   */
  private function LoadAnchorsFile($filename)
    {
    if(!file_exists($filename))
      {
      CLogger::write(CLoggerType::ERROR, 'links: load anchors from file: ' . $filename);
      return null;
      }
    $data = file($filename);
    array_walk($data, create_function('&$val', '$val = trim($val);'));
    $this->m_list_anchors[$filename] = $data;
    CLogger::write(CLoggerType::DEBUG, 'links: load anchors from file: ' . $filename);
    //--- количество блоков
    return $data;
    }

  /**
   * Попытка открыть файл со ссылками
   * @param $path
   * @return bool
   */
  private function OpenLinksFileName($path, &$size)
    {
    $fullname = self::PATH . '/' . trim($path, '/');
    $size     = 0;
    //--- имя файла, если нужно скидывать все данные в один файл
    //--- полное имя к файлу со ссылками для данного дорвея
    $fullname = $fullname . '/' . self::FILE_LINKS;
    if(!file_exists($fullname))
      {
      CLogger::write(CLoggerType::DEBUG, 'links: open links file not found: ' . $fullname);
      return null;
      }
    //---
    $size = filesize($fullname);
    //--- открываем файл на чтение
    $handel = fopen($fullname, 'r');
    if(!$handel)
      {
      CLogger::write(CLoggerType::ERROR, 'links: open links file not open: ' . $fullname);
      return false;
      }
    return $handel;
    }

  /**
   * Закрытие всех файлов
   */
  public function Close()
    {
    foreach($this->m_list_files as $handle)
      {
      fclose($handle);
      }
    }
  }
class LinskFormat
  {
  /**
   *
   * дата создания строчки
   * @var int
   */
  const TIME = 0;
  /**
   *
   * данные с какой позиции в файле со страницами, начинается нужный блок
   * @var int
   */
  const POSITION = 1;
  /**
   *
   * Количество ссылок
   * @var int
   */
  const COUNT = 2;
  /**
   *
   * Домен
   * @var int
   */
  const DOMAIN = 3;
  /**
   *
   * Кейворд по умолчанию
   * @var int
   */
  const KEYWORD = 4;
  /**
   *
   * Тип дорвея 0 - статический, 1 - динамический,
   * @var int
   */
  const TYPE = 5;
  }