<?php
//+------------------------------------------------------------------+
//|                  Copyright c 2012, RedButton Web Sites Generator |
//|                                      http://www.getredbutton.com |
//+------------------------------------------------------------------+
class CModel_dynamic
  {
  //--- максимальное количество блоко в данных для динамического сайта
  const MAX_BLOCK_IN_FILE = 10;
  /**
   * параметры
   * @var array
   */
  private $m_params;
  /**
   * @var string
   */
  private $localPath;
  /**
   *  модуль с макросами
   * @var CModel_macros
   */
  private $m_model_macros;
  /**
   *  модуль с rss
   * @var CModel_rss
   */
  private $m_model_rss;
  /**
   *  модуль с ключивиками
   * @var CModel_keywords
   */
  private $m_model_keywords;
  /**
   * параметры для динамического дорвея
   * @var array
   */
  private $m_dynamic_param;
  /**
   * Модуль для редиректа
   * @var CModel_redirect
   */
  private $m_model_redirect;
  /**
   * для работы с текстом
   * @var CModel_text
   */
  private $m_model_text;
  /**
   * генерировали уже rss
   * @var bool
   */
  private $m_is_rss_generated = false;
  /**
   * Включен ли редирект
   * @var bool
   */
  private $m_is_redirect = false;
  /**
   * Отложенная публикация
   * @var bool
   */
  private $m_delayted_published = false;
  /**
   * Наименование урлов
   * @var stirng
   */
  private $m_dynamic_pagename;
  /**
   * Это ЧПУ урлы?
   * @var bool
   */
  private $m_is_chpu;
  /**
   * Для httacce возможно добавить доп строчки
   * @var string
   */
  private $m_htaccess_string = '';
  /**
   * урл к дорвею, первая часть  parse_url(newxUrl)['path']
   * @var string
   */
  private $m_url_path = '';

  /**
   * конструктор
   * @param array $params
   * @param CModel_macros $model_macros
   * @param CModel_rss $model_rss
   * @param CModel_keywords $model_keywords
   * @param CModel_text $model_text
   * @param CModel_links $model_links
   * @param CModel_cloaking $model_cloaking
   * @param CModel_redirect $model_redirect
   * @param string $localPath
   */
  public function __construct(&$params, &$model_macros, &$model_rss, &$model_keywords, &$model_text, &$model_links, &$model_cloaking, &$localPath)
    {
    $this->m_params         = $params;
    $this->m_model_macros   = $model_macros;
    $this->m_model_rss      = $model_rss;
    $this->m_model_keywords = $model_keywords;
    $this->m_model_text     = $model_text;
    $this->m_model_links    = $model_links;
    $this->m_model_cloaking = $model_cloaking;
    $this->localPath        = $localPath;
    $this->m_dynamic_param  = array();
    //--- проверка нужно ли включать отложенную публикацию
    if(isset($this->m_params['delayedPublication']) && $this->m_params['delayedPublication'] == 'on') $this->m_delayted_published = true;
    $this->m_is_redirect = !empty($this->m_params['redirectType']);
    //--- урлы
    $this->m_dynamic_pagename = $this->m_params['dynamicPageNamesFrom'] == 'list' ? $this->m_params['dynamicPageName'] : $this->m_params['dynamicPageNameCustom'];
    $this->m_is_chpu          = strpos($this->m_dynamic_pagename, '?') === false;
    }

  /**
   * Создание index.php файла для динамики

   */
  public function CreateIndexPhp($body)
    {
    //--- выбрали какой вид будет у урлов
    $this->m_dynamic_param   = array();
    $this->m_htaccess_string = '';
    //--- path для урла
    $url_info         = parse_url($this->m_params['nextUrl']);
    $this->m_url_path = empty($url_info['path']) ? '/' : $url_info['path'];
    //--- если в запросе есть знак ? значит это не ЧПУ
    if($this->m_is_chpu)
      {
      //--- page - номер, name - кейворд
      $php_code = ' $page = isset($_REQUEST["page"]) ? (int)$_REQUEST["page"]:0; $name = isset($_REQUEST["name"])?$_REQUEST["name"]:"";';
      }
    else
      {
      $php_code = $this->GeneratePhpCodePageName($this->m_dynamic_pagename);
      }
    //--- заменяем общие макросы
    $page = '<?php
      header("Content-Type:text/html; charset=UTF-8");' . "\r\n";
    //--- сохраним данные о ip адресах поисковиков
    $page .= $this->GetIpSearchers();
    $page .= 'include("./data/funcs.php"); ' . "\r\n" . 'define("MAX_BLOCK_IN_FILE",' . self::MAX_BLOCK_IN_FILE . ');' . "\r\n";
    $page .= $php_code . "\r\n";
    $page .= '$info=GetInfo($page);' . "\r\n";
    $page .= $this->GetPhpCodeSelectPage();
    $page .= '?' . '>';
    //--- проверим установлен ли редирект и запустим замену, если это необходимо
    if($this->m_is_redirect) $this->m_model_redirect->Redirect($body, '');
    //--- вставим пхп код
    $page .= $body;
    //---
    $page     = $this->GetMacroces($page);
    $filename = $this->localPath . '/index.php';
    file_put_contents($filename, $page);
    //--- ставим права 777 для каждого файла
    chmod($filename, 0777);
    //---
    $this->DynamicCreatePaths();
    }

  /**
   * Получени php кода для подключения шаблона
   * @return string
   */
  private function GetPhpCodeSelectPage()
    {
    return '
if(isset($info["page_name"]))
  {
   if($info["page_name"]=="page" && file_exists("page.php")){include_once("page.php"); exit;}
   if($info["page_name"]=="category" && file_exists("category.php")){include_once("category.php"); exit;}
  }
';
    }

  /**
   * Создание index.php файла для динамики

   */
  public function CreatePagePhp($body, $filename)
    {
    if(empty($body)) return;
    //--- выбрали какой вид будет у урлов
    $this->m_dynamic_param   = array();
    $this->m_htaccess_string = '';
    //--- path для урла
    $url_info         = parse_url($this->m_params['nextUrl']);
    $this->m_url_path = empty($url_info['path']) ? '/' : $url_info['path'];
    //--- проверим установлен ли редирект и запустим замену, если это необходимо
    if($this->m_is_redirect) $this->m_model_redirect->Redirect($body, '');
    //--- вставим пхп код
    $page = $body;
    //---
    $page     = $this->GetMacroces($page);
    $filename = $this->localPath . '/' . $filename;
    file_put_contents($filename, $page);
    //--- ставим права 777 для каждого файла
    chmod($filename, 0777);
    //---
    $this->DynamicCreatePaths();
    }

  /**
   * Получаем страницу с заменной всех макросов
   * @param string $page
   *
   * @return string
   */
  private function GetMacroces($page)
    {
    $page = str_replace('[HOME-URL]', $this->m_params['nextUrl'], $page);
//---
    $page                           = str_replace('[SITE-URL]', $this->m_params['clearnextUrl'], $page);
    $page                           = str_replace('[TITLE]', '<?php echo $info["title"]?>', $page);
    $this->m_dynamic_param['title'] = '';
    //--- sitemap генерация
    $page = str_replace('[SITEMAP-CONTENT]', '', $page);
    //---
    $page                              = str_replace('[KEYWORDS]', '<?php echo $info["keywords"]?>', $page);
    $this->m_dynamic_param['keywords'] = '';
    //---
    $page = str_replace('[ARSS]', $this->GetARSS(), $page);
    $page = str_replace('[RSS]', $this->GetRSS(), $page);
    //---
    $page                                 = str_replace('[DESCRIPTION]', '<?php echo $info["description"]?>', $page);
    $this->m_dynamic_param['description'] = '';
    //if(!empty($this->template->content))
    $page                             = str_replace('[RB:CONTENT]', '<?php echo $info["content"]?>', $page);
    $this->m_dynamic_param['content'] = '';
    //--- макрос случайных строчек
    $page = preg_replace_callback('/\[RANDLINE\]/i', array($this,
                                                           'GetRandLineDynamic'), $page);
    //--- еще один макрос случайных строчек
    $page = preg_replace_callback('/\[RANDLINE\-([a-z0-9\-]{1,64})\]/i', array($this,
                                                                               'GetRandLineDynamic'), $page);
    //---
    $page = preg_replace_callback('/\[RANDLINE\-([a-z0-9\-\/]{1,64}\.[a-z0-9]{1,64})\]/i', array($this,
                                                                                                 'GetRandLineDynamicFile'), $page);
    //--- еще макрос случайных строчек, берем из файла
    $page = preg_replace_callback('/\[NEXTLINE\-([a-z0-9\-\/]{1,64}\.[a-z0-9]{1,64})\]/i', array($this,
                                                                                                 'GetNextLineDynamicFile'), $page);
    //--- заменяем остальные макросы
    $page                             = str_replace('[KEYWORD]', '<?php echo $info["keyword"]?>', $page);
    $this->m_dynamic_param['keyword'] = '';
    //---
    $page                                = str_replace('[UC-KEYWORD]', '<?php echo $info["uc-keyword"]?>', $page);
    $this->m_dynamic_param['uc-keyword'] = '';
    //---
    $page                                 = str_replace('[UCW-KEYWORD]', '<?php echo $info["ucw-keyword"]?>', $page);
    $this->m_dynamic_param['ucw-keyword'] = '';
    //---
    //--- заменяем на такие массивы $info["text-number-какоето-число"] и добавляем в параметры
    $page = preg_replace_callback('/\[TEXT\-([0-9]{1,})\-([0-9]{1,})\]/', array($this,
                                                                                'GetTextDynamicParam'), $page);
    //--- заменяем на такие массивы $info["text-nokey-number-какоето-число"] и добавляем в параметры
    $page = preg_replace_callback('/\[TEXT\-NOKEY\-([0-9]{1,})\-([0-9]{1,})\]/', array($this,
                                                                                       'GetTextNoKeyDynamicParam'), $page);
    //--- заменяем на такие массивы $info["text-clear-bokey-number-какоето-число"] и добавляем в параметры
    $page = preg_replace_callback('/\[TEXT\-CLEAR\-([0-9]{1,})\-([0-9]{1,})\]/', array($this,
                                                                                       'GetTextClearDynamicParam'), $page);
    //--- заменяем на такие массивы $info["text-clear-bokey-number-какоето-число"] и добавляем в параметры
    $page = preg_replace_callback('/\[TEXT\-CLEAR\-NOKEY\-([0-9]{1,})\-([0-9]{1,})\]/', array($this,
                                                                                              'GetTextClearNoKeyDynamicParam'), $page);
    //--- сохраняем данные для дальнейшей генерации и сохранения в пхп файл
    $page = preg_replace_callback('/\[RAND\-KEYWORD\]/', array($this,
                                                               'GetRandKeywordDynamicParam'), $page);
    //---
    $page = preg_replace_callback('/\[RAND\-UC\-KEYWORD\]/', array($this,
                                                                   'GetRandUcKeywordDynamicParam'), $page);
    //---
    $page = preg_replace_callback('/\[RAND\-URL\]/', array($this,
                                                           'GetRandUrlDynamicParam'), $page);
    //---
    $page = preg_replace_callback('/\[RAND\-LINK\]/', array($this,
                                                            'GetRandLinkDynamicParam'), $page);
    //---
    $page = preg_replace_callback('/\[RAND\-UC\-LINK\]/', array($this,
                                                                'GetRandUcLinkDynamicParam'), $page);
    //---
    $page = preg_replace_callback('/\[RAND\-UC\-LINK\-([0-9]{1,})\-([0-9]{1,})\]/', array($this,
                                                                                          'GetRandUcLinksDynamicParam'), $page);
    //---
    $page = preg_replace_callback('/\[UC\-LINK\-([0-9]{1,})\]/', array($this,
                                                                       'GetUcLinkDynamicParam'), $page);
    //---  гиперссылка с кейвордом на след. страницу
    $page = preg_replace_callback('/\[NEXT\-LINK\]/', array($this,
                                                            'GetNextLinkDynamicParam'), $page);
    //--- гиперссылка с кейвордом на пред. страницу
    $page = preg_replace_callback('/\[PREV\-LINK\]/', array($this,
                                                            'GetPrevLinkDynamicParam'), $page);
    //--- гиперссылка с кейвордом с большой буквы на след. страницу
    $page = preg_replace_callback('/\[NEXT\-UC\-LINK\]/', array($this,
                                                                'GetNextUcLinkDynamicParam'), $page);
    //---гиперссылка с кейвордом с большой буквы на пред. страницу
    $page = preg_replace_callback('/\[PREV\-UC\-LINK\]/', array($this,
                                                                'GetPrevUcLinkDynamicParam'), $page);
    //--- гиперссылка с кейвордом, все слова которого  начинаются с большой буквы, на след.  страницу
    $page = preg_replace_callback('/\[NEXT\-UCW\-LINK\]/', array($this,
                                                                 'GetNextUcwLinkDynamicParam'), $page);
    //--- гиперссылка с кейвордом, все слова которого начинаются с большой буквы, на пред. страницу
    $page = preg_replace_callback('/\[PREV\-UCW\-LINK\]/', array($this,
                                                                 'GetPrevUcwLinkDynamicParam'), $page);
    //--- гиперссылка с кейвордом с большой буквы на след. страницу
    $page = preg_replace_callback('/\[NEXT\-UC\-LINK\-([0-9]{1,})]/', array($this,
                                                                            'GetNextUcLinksNumbersDynamicParam'), $page);
    //---гиперссылка с кейвордом с большой буквы на пред. страницу
    $page = preg_replace_callback('/\[PREV\-UC\-LINK\-([0-9]{1,})]/', array($this,
                                                                            'GetPrevUcLinksNumbersDynamicParam'), $page);
    //--- гиперссылка с кейвордом, все слова которого  начинаются с большой буквы, на след.  страницу
    $page = preg_replace_callback('/\[NEXT\-UCW\-LINK\-([0-9]{1,})]/', array($this,
                                                                             'GetNextUcwLinksNumbersDynamicParam'), $page);
    //--- гиперссылка с кейвордом, все слова которого начинаются с большой буквы, на пред. страницу
    $page = preg_replace_callback('/\[PREV\-UCW\-LINK\-([0-9]{1,})]/', array($this,
                                                                             'GetPrevUcwLinksNumbersDynamicParam'), $page);
    //--- просто ссылка на след. страницу
    $page = preg_replace_callback('/\[NEXT\-URL\]/', array($this,
                                                           'GetNextUrlDynamicParam'), $page);
    //--- просто ссылка на пред. страницу
    $page = preg_replace_callback('/\[PREV\-URL\]/', array($this,
                                                           'GetPrevUrlDynamicParam'), $page);
    //--- картинки
    $page = preg_replace_callback('/\[RAND\-IMG\]/', array($this,
                                                           'GetRandImgDynamic'), $page);
    //---
    $page = preg_replace_callback('/\[RAND\-IMG\-([0-9]{1,})\-([0-9]{1,})\]/', array($this,
                                                                                     'GetRandImgResizeDynamic'), $page);
    //---
    $page = preg_replace_callback('/\[RAND\-IMG\-([0-9a-zA-Z]{1,})\]/', array($this,
                                                                              'GetRandImgFromDynamic'), $page);
    //---
    $page = preg_replace_callback('/\[RAND\-IMG\-([0-9a-zA-Z]{1,})\-([0-9]{1,})\-([0-9]{1,})\]/', array($this,
                                                                                                        'GetRandImgResizeFromDynamic'), $page);
    //---
    $page = preg_replace_callback('/\[RAND\-IMG\-URL\-([0-9a-zA-Z\-\.]{1,})\]/', array($this,
                                                                                       'GetRandImgUrlFromFileurl'), $page);
    //---
    $page = preg_replace_callback('/\[GEN\-IMG\]/', array($this,
                                                          'GetGenerateImgDynamic'), $page);
    //---
    $page = preg_replace_callback('/\[GEN\-IMG\-([0-9]{1,})\-([0-9]{1,})\]/', array($this,
                                                                                    'GetGenerateImgResizeDynamic'), $page);
    //---
    $page = preg_replace_callback('/\[GEN\-IMG\-([0-9a-zA-Z]{1,})\]/', array($this,
                                                                             'GetGenerateImgFromDynamic'), $page);
    //---
    $page = preg_replace_callback('/\[GEN\-IMG\-([0-9a-zA-Z]{1,})\-([0-9]{1,})\-([0-9]{1,})\]/', array($this,
                                                                                                       'GetGenerateImgResizeFromDynamic'), $page);
    //--- видо
    $page = preg_replace_callback('/\[RAND\-VIDEO\-([0-9a-zA-Z]{1,})\-([0-9]{1,})\-([0-9]{1,})\]/', array($this,
                                                                                                          'GetRandVideoSize'), $page);
    //--- ссылка на видео
    $page = preg_replace_callback('/\[RAND\-VIDEO\-URL\-([0-9a-zA-Z]{1,})\]/', array($this,
                                                                                     'GetRandVideoUrl'), $page);
    //---
    $page = preg_replace_callback('/\[NICK\]/', array($this,
                                                      'GetNickDynamic'), $page);
    //--- меню, можно не вызывать каждый раз, а только один раз
    $page = preg_replace_callback('/\[MENU-CATEGORY\]/', array($this,
                                                               'GetMenuCategoryDynamic'), $page);
    //--- теги
    $page = preg_replace_callback('/\[TAGS\]/', array($this,
                                                      'GetTagsDynamic'), $page);
    //---
    $page                        = preg_replace('/\[N\]/', '<?php echo $info["nn"]?>', $page);
    $this->m_dynamic_param['nn'] = '';
    //---
    $page = preg_replace_callback('/\[RAND\-([0-9]{1,})\-([0-9]{1,})\]/', array($this,
                                                                                'GetRandInt'), $page);
    //---
    $page = preg_replace_callback('/\[ENCODE\-RAND\-KEY\]/', array($this,
                                                                   'GetEcodeRandKey'), $page);
    //---
    $page                                        = preg_replace('/\[ENCODE\-CURRENT\-KEY\]/', '<?php echo $info["encode-current-key"]?>', $page);
    $this->m_dynamic_param['encode-current-key'] = '';
    //--- случайная ссылка (просто ссылка) для спама на сгенеренную страницу дорвея текущей тематики
    $page = preg_replace_callback('/\[RAND\-SPAM\-URL\]/', array($this,
                                                                 'GetMacrosSpamUrl'), $page);
    //--- случайная гиперссылка для спама
    $page = preg_replace_callback('/\[RAND\-SPAM\-LINK\]/', array($this,
                                                                  'GetMacrosSpamLink'), $page);
    //--- случайная гиперссылка для спама, где кейворд с большой буквы
    $page = preg_replace_callback('/\[RAND\-SPAM\-UC\-LINK\]/', array($this,
                                                                      'GetMacrosSpamUCLink'), $page);
    //--- список (<ul>) случайных гиперссылок для спама, где кейворд с большой буквы
    $page = preg_replace_callback('/\[RAND\-SPAM\-UC\-LINK\-([0-9]{1,})\-([0-9]{1,})\]/', array($this,
                                                                                                'GetMacrosSpamUCManyLinks'), $page);
    //--- Обработка текстового макроса вида [утро|вечер|день]
    //$page = preg_replace_callback('/\[([\w|\s]{1,})\]/', array($this, 'GetTextMacros'), $page); // NOTE: Как я мог такое написать?
    $page = preg_replace_callback('/\[([^\[\]]{1,}\|[^\[\]]{1,})\]/', array($this,
                                                                            'GetTextMacros'), $page);
    return $page;
    }

  /**
   * Запись данных для динамического доврея
   * @param номер $numbet
   * @param контент $content
   */
  public function DynamicWriteFile($number, $content)
    {
    $file_number = (int)($number / self::MAX_BLOCK_IN_FILE);
    //--- полный путь к файлу с данными
    $filename     = $this->localPath . '/data/' . $file_number . '.dat.php';
    $numberInFile = (int)(fmod($number, self::MAX_BLOCK_IN_FILE));
    //---
    if(file_exists($filename))
      {
      $fhandle = fopen($filename, "r+");
      }
    else
      {
      $fhandle = fopen($filename, "w");
      }
    //--- получим информацию о файле
    $file_info = fstat($fhandle);
    //--- размер файоа
    $filesize = $file_info['size'];
    CLogger::write(CLoggerType::DEBUG, $file_number . ' file write: ' . $filename);
    //--- считыаем заголовок, или если файл пустой, то дефолтный заголовок
    $header = $this->DynamicReadHeaderFile($fhandle, $filesize);
    $pos    = $filesize > 8 * self::MAX_BLOCK_IN_FILE ? $filesize : (8 * self::MAX_BLOCK_IN_FILE + 1);
    //--- для текущего файла запишем заголовок
    $header[$numberInFile] = array($pos,
                                   strlen($content));
    //--- запись заголовка
    $this->DynamicWriteHeaderFile($fhandle, $header);
    //--- переходим в конец файла, а точнее на pos
    if(fseek($fhandle, $pos, SEEK_SET) < 0)
      {
      CLogger::write(CLoggerType::ERROR, $file_number . ' file write: ' . $filename . ' can not to seek on position: ' . $pos);
      }
    CLogger::write(CLoggerType::DEBUG, $file_number . ' file write: ' . $filename . ' write content ');
    //--- после перехода пишем
    fwrite($fhandle, $content);
    //---
    fclose($fhandle);
    //--- ставим права 777 для каждого файла
    chmod($filename, 0777);
    }

  /**
   * Заголовок файла 4 байта откуда брать контент, 4 байт длина контента
   * Сам заголовок занимает 8 * MAX_BLOCK_IN_FILE байт
   *
   * @param object $fhandle
   */
  private function DynamicReadHeaderFile($fhandle, $filesize)
    {
    if($filesize < 8 * self::MAX_BLOCK_IN_FILE)
      {
      $result = array();
      //---
      for($i = 0; $i < self::MAX_BLOCK_IN_FILE; $i++)
        {
        $result[$i] = array(0,
                            0);
        }
      return $result;
      }
    fseek($fhandle, 0);
    $data = fread($fhandle, 8 * self::MAX_BLOCK_IN_FILE);
    return $this->DynamicUnpackHeader($data);
    }

  /**
   * Распакуем данные о заголовке в обычный массив
   * @param binary $data
   */
  private function DynamicUnpackHeader($data)
    {
    if(empty($data)) return array();
    //---
    $len = strlen($data);
    //---
    $result = array();
    //---
    for($i = 0; $i < self::MAX_BLOCK_IN_FILE; $i++)
      {
      if($len >= (($i + 1) * 8))
        {
        $r = unpack("Inum/Lcount", substr($data, $i * 8, 8));
        if(empty($r)) $result[$i] = array(0,
                                          0);
        else          $result[$i] = array($r['num'],
                                          $r['count']);
        }
      else
        {
        $result[$i] = array(0,
                            0);
        }
      }
    return $result;
    }

  /**
   * Запоковка данных в бинарный вид
   * @param binary $data
   */
  private function DynamicPackHeader($info)
    {
    $header_len = self::MAX_BLOCK_IN_FILE * 8;
    if(empty($info)) return str_repeat("\0", $header_len);
    //---
    $data = '';
    //---
    foreach($info as $d)
      {
      $data .= pack("IL", $d[0], $d[1]);
      }
    //---
    return $data;
    }

  /**
   * Запись заголовка в файл
   * @param object $fhandle
   * @param array $data
   */
  private function DynamicWriteHeaderFile($fhandle, $data)
    {
    //--- упакуем заголовок
    $header = $this->DynamicPackHeader($data);
    CLogger::write(CLoggerType::DEBUG, 'header length: ' . strlen($header));
    //--- на начала файла
    fseek($fhandle, 0);
    //--- запись
    fwrite($fhandle, $header);
    }

  /**
   * Для динмамик заменяем шаблон и записываем в параметры
   * @param array $matches
   *
   * @return string
   */
  private function GetTextDynamicParam($matches)
    {
    return $this->GenerateDynamicParamMatches('text-number', $matches);
    }

  /**
   * Получаем текст без ключевиков
   * @param $matches
   *
   * @return string
   */
  private function GetTextNoKeyDynamicParam($matches)
    {
    return $this->GenerateDynamicParamMatches('text-nokey-number', $matches);
    }

  /**
   * Получаем текст без ключевиков и параграфов
   * @param $matches
   *
   * @return string
   */
  private function GetTextClearNoKeyDynamicParam($matches)
    {
    return $this->GenerateDynamicParamMatches('text-clear-nokey-number', $matches);
    }

  /**
   * Получаем текст без параграфов
   * @param $matches
   *
   * @return string
   */
  private function GetTextClearDynamicParam($matches)
    {
    return $this->GenerateDynamicParamMatches('text-clear-number', $matches);
    }

  /**
   * Получение параметров для rand-keyword
   * @return string
   */
  private function GetRandKeywordDynamicParam()
    {
    return $this->GenerateDynamicParam('rand-keyword');
    }

  /**
   * Получение параметров для rand-uc-keyword
   * @return string
   */
  private function GetRandUcKeywordDynamicParam()
    {
    return $this->GenerateDynamicParam('rand-uc-keyword');
    }

  /**
   * получение параметров для rand-link
   * @return string
   */
  private function GetRandLinkDynamicParam()
    {
    return $this->GenerateDynamicParam('rand-link');
    }

  /**
   * получение параметров для rand-url
   * @return string
   */
  private function GetRandUrlDynamicParam()
    {
    return $this->GenerateDynamicParam('rand-url');
    }

  /**
   * Получение параметров для rand-uc-link
   * @return string
   */
  private function GetRandUcLinkDynamicParam()
    {
    return $this->GenerateDynamicParam('rand-uc-link');
    }

  /**
   * Получение параметров для rand-uc-links-number
   *
   * @param array $matches
   *
   * @return string
   */
  private function GetRandUcLinksDynamicParam($matches)
    {
    return $this->GenerateDynamicParamMatches('rand-uc-links', $matches);
    }

  /**
   * Получение параметров для uc-link
   *
   * @param array $matches
   *
   * @return string
   */
  private function GetUcLinkDynamicParam($matches)
    {
    return $this->GenerateDynamicParamMatches('uc-link', $matches);
    }

  /**
   * Для динмамик заменяем шаблон и записываем в параметры
   * @return string
   */
  private function GetRandLineDynamic($matches)
    {
    return $this->GenerateDynamicParamMatches('rand-line', $matches);
    }

  /**
   * Для динмамик заменяем шаблон и записываем в параметры
   * @return string
   */
  private function GetRandLineDynamicFile($matches)
    {
    return $this->GenerateDynamicParamMatches('rand-line-file', $matches);
    }

  /**
   * Следующая сточка из файла
   * @param $matches
   *
   * @return string
   */
  private function GetNextLineDynamicFile($matches)
    {
    return $this->GenerateDynamicParamMatches('next-line-file', $matches);
    }

  /**
   * Получение случайная картинка с размерами
   * rand-img-resize
   *
   * @param array $matches
   *
   * @return string
   */
  private function GetRandImgResizeDynamic($matches)
    {
    return $this->GenerateDynamicParamMatches('rand-img-resize', $matches);
    }

  /**
   * из какой папки брать случайную картинку
   * @param $matches
   *
   * @return string
   */
  private function GetRandImgFromDynamic($matches)
    {
    return $this->GenerateDynamicParamMatches('rand-img-from', $matches);
    }

  /**
   * из какой папки брать и ресайзить картинку
   * @param array $matches
   *
   * @return string
   */
  private function GetRandImgResizeFromDynamic($matches)
    {
    return $this->GenerateDynamicParamMatches('rand-img-from-resize', $matches);
    }

  /**
   * Обработка макроса [RAND-IMG-URL-([0-9a-zA-Z\-\.]{1,})\]
   *
   * @param $matches
   *
   * @return string
   */
  private function GetRandImgUrlFromFileurl($matches)
    {
    return $this->GenerateDynamicParamMatches('rand-img-url-from-file', $matches);
    }

  /**
   * Получаем случайную картинку
   * rand-img
   * @return string
   */
  private function GetRandImgDynamic()
    {
    return $this->GenerateDynamicParam('rand-img');
    }

  /**
   * генерация случайной картинки
   * gen-img
   * @return string
   */
  private function GetGenerateImgDynamic()
    {
    return $this->GenerateDynamicParam('gen-img');
    }

  /**
   * генерация картинки с размерами
   * gen-img-resize
   *
   * @param array $matches
   *
   * @return string
   */
  private function GetGenerateImgResizeDynamic($matches)
    {
    return $this->GenerateDynamicParamMatches('gen-img-resize', $matches);
    }

  /**
   * генерация картинки из папки
   * gen-img-from
   *
   * @param array $matches
   *
   * @return string
   */
  private function GetGenerateImgFromDynamic($matches)
    {
    return $this->GenerateDynamicParamMatches('gen-img-from', $matches);
    }

  /**
   * генерация картинки из папки c нужными размерами
   * gen-img-from
   *
   * @param array $matches
   *
   * @return string
   */
  private function GetGenerateImgResizeFromDynamic($matches)
    {
    return $this->GenerateDynamicParamMatches('gen-img-resize-from', $matches);
    }

  /**
   * Получение видео
   * rand-video-size
   *
   * @param array $matches
   *
   * @return string
   */
  private function GetRandVideoSize($matches)
    {
    return $this->GenerateDynamicParamMatches('rand-video-size', $matches);
    }

  /**
   * Получение видео
   * rand-video-url
   *
   * @param array $matches
   *
   * @return string
   */
  private function GetRandVideoUrl($matches)
    {
    return $this->GenerateDynamicParamMatches('rand-video-url', $matches);
    }

  /**
   * Получаем имя
   * @return string
   */
  private function GetNickDynamic()
    {
    return $this->GenerateDynamicParam('nick');
    }

  /**
   * Получаем меню категорий
   * @return string
   */
  private function GetMenuCategoryDynamic()
    {
    return $this->GenerateDynamicParam('menu-category');
    }

  /**
   * Получаем список тегов
   * @return string
   */
  private function GetTagsDynamic()
    {
    return $this->GenerateDynamicParam('tags');
    }

  /**
   * генерация случайного числа
   * [RAND-([0-9]{1,})-([0-9]{1,})]
   *
   * @param $matches
   *
   * @return string
   */
  private function GetRandInt($matches)
    {
    return $this->GenerateDynamicParamMatches('rand-int', $matches);
    }

  /**
   * генерация макроса [ENCODE-RAND-KEY]
   * @return string
   */
  private function GetEcodeRandKey()
    {
    return $this->GenerateDynamicParam('encode-rand-key');
    }

  /**
   * генерация макроса [RAND-SPAM-URL]
   * @return string
   */
  private function GetMacrosSpamUrl()
    {
    return $this->GenerateDynamicParam('rand-spam-url');
    }

  /**
   * генерация макроса [RAND-SPAM-LINK]
   * @return string
   */
  private function GetMacrosSpamLink()
    {
    return $this->GenerateDynamicParam('rand-spam-link');
    }

  /**
   * генерация макроса [RAND-SPAM-UC-LINK\]
   * @return string
   */
  private function GetMacrosSpamUCLink()
    {
    return $this->GenerateDynamicParam('rand-spam-uc-link');
    }

  /**
   * генерация макроса [RAND-SPAM-UC-LINK]
   *
   * @param array $matches
   *
   * @return string
   */
  private function GetMacrosSpamUCManyLinks($matches)
    {
    return $this->GenerateDynamicParamMatches('rand-spam-uc-links-many', $matches);
    }

  /**
   * генерация макроса [утро|вечер|день]
   * @param array $matches
   *
   * @return string
   */
  private function GetTextMacros($matches)
    {
    return $this->GenerateDynamicParamMatches('text-macros', $matches);
    }

  /**
   * Обработка и добавление динамического параметра
   * @param string $name_dynamic
   *
   * @return string
   */
  private function GenerateDynamicParam($name_dynamic)
    {
    if(isset($this->m_dynamic_param[$name_dynamic])) $this->m_dynamic_param[$name_dynamic]++;
    else $this->m_dynamic_param[$name_dynamic] = 0;
    //---
    return '<?php echo $info["' . $name_dynamic . '"][' . $this->m_dynamic_param[$name_dynamic] . ']?>';
    }

  /**
   * гиперссылка с кейвордом на след. страницу
   */
  private function GetNextLinkDynamicParam()
    {
    return $this->GenerateDynamicParam('next-link');
    }

  /**
   * гиперссылка с кейвордом на след. страницу
   */
  private function GetPrevLinkDynamicParam()
    {
    return $this->GenerateDynamicParam('prev-link');
    }

  /**
   *  гиперссылка с кейвордом с большой буквы на след. страницу
   */
  private function GetNextUcLinkDynamicParam()
    {
    return $this->GenerateDynamicParam('next-uc-link');
    }

  /**
   * гиперссылка с кейвордом с большой буквы на пред. страницу
   */
  private function GetPrevUcLinkDynamicParam()
    {
    return $this->GenerateDynamicParam('prev-uc-link');
    }

  /**
   *   гиперссылка с кейвордом, все слова которого  начинаются с большой буквы, на след.  страницу
   */
  private function GetNextUcwLinkDynamicParam()
    {
    return $this->GenerateDynamicParam('next-ucw-link');
    }

  /**
   * гиперссылка с кейвордом с большой буквы на пред. страницу
   */
  private function GetPrevUcwLinkDynamicParam()
    {
    return $this->GenerateDynamicParam('prev-ucw-link');
    }

  /**
   *  гиперссылка с кейвордом с большой буквы на след. страницу
   */
  private function GetNextUcLinksNumbersDynamicParam($matches)
    {
    return $this->GenerateDynamicParamMatches('next-uc-links-numbers', $matches);
    }

  /**
   * гиперссылка с кейвордом с большой буквы на пред. страницу
   */
  private function GetPrevUcLinksNumbersDynamicParam($matches)
    {
    return $this->GenerateDynamicParamMatches('prev-uc-links-numbers', $matches);
    }

  /**
   *   гиперссылка с кейвордом, все слова которого  начинаются с большой буквы, на след.  страницу
   */
  private function GetNextUcwLinksNumbersDynamicParam($matches)
    {
    return $this->GenerateDynamicParamMatches('next-ucw-links-numbers', $matches);
    }

  /**
   * гиперссылка с кейвордом с большой буквы на пред. страницу
   */
  private function GetPrevUcwLinksNumbersDynamicParam($matches)
    {
    return $this->GenerateDynamicParamMatches('prev-ucw-links-numbers', $matches);
    }

  /**
   * просто ссылка на след. страницу
   */
  private function GetNextUrlDynamicParam()
    {
    return $this->GenerateDynamicParam('next-url');
    }

  /**
   * гиперссылка с кейвордом на след. страницу
   */
  private function GetPrevUrlDynamicParam()
    {
    return $this->GenerateDynamicParam('prev-url');
    }

  /**
   * Обработка и добавление динамического параметра
   * @param string $name_dynamic
   * @param array $matches
   *
   * @return string
   */
  private function GenerateDynamicParamMatches($name_dynamic, &$matches)
    {
    if(isset($this->m_dynamic_param[$name_dynamic . '-count'])) $this->m_dynamic_param[$name_dynamic . '-count']++;
    else $this->m_dynamic_param[$name_dynamic . '-count'] = 0;
    //---
    if(!isset($this->m_dynamic_param[$name_dynamic])) $this->m_dynamic_param[$name_dynamic] = array();
    //---
    $this->m_dynamic_param[$name_dynamic][$this->m_dynamic_param[$name_dynamic . '-count']] = $matches;
    return '<?php echo $info["' . $name_dynamic . '"][' . $this->m_dynamic_param[$name_dynamic . '-count'] . ']?>';
    }

  /**
   * Создание .htaccess файла
   */
  public function CreateDynamicHtaccess()
    {
    //--- выбрали какой вид будет у урлов
    $dynamicPageName = $this->m_params['dynamicPageNamesFrom'] == 'list' ? $this->m_params['dynamicPageName'] : $this->m_params['dynamicPageNameCustom'];
    $fname           = $this->localPath . '/.htaccess';
    //--- если в запросе есть знак ? значит это не ЧПУ
    $images = $this->m_model_macros->CreateStaticHtaccess($this->localPath);
    if(strpos($dynamicPageName, '?') !== false)
      {
      $data = '';
      //--- если все таки данные о картинках есть, нужно создать htaccess
      if(!empty($images))
        {
        $data = 'RewriteEngine on
         ' . $images;
        if(file_put_contents($fname, $data))
          {
          CLogger::write(CLoggerType::DEBUG, 'htaccess file created: ' . $fname);
          //--- ставим права 777 для каждого файла
          chmod($fname, 0777);
          }
        }
      return $data;
      }
//--- т.к. $this->m_htaccess_string - вначале пусто, то просто вставим заглушку в файл. В конце обновим данные
    $data = 'RewriteEngine on
    ' . $images . '
RewriteCond %{SCRIPT_FILENAME} !-f
RewriteCond %{SCRIPT_FILENAME} !-d
[HTACCESS_URLS_DYNAMICS]
RewriteRule ^([0-9]+)$ ' . $this->m_url_path . 'index.php?page=$1 [L,QSA]
RewriteRule ^([0-9]+)/([A-Za-z0-9\\-]+)$ ' . $this->m_url_path . 'index.php?page=$1&name=$2 [L,QSA]
RewriteRule ^([A-Za-z0-9\\-]+)/([0-9]+)$ ' . $this->m_url_path . 'index.php?page=$2&name=$1 [L,QSA]
RewriteRule ^([A-Za-z0-9\\-]+)/([A-Za-z0-9\\-]+)$ ' . $this->m_url_path . 'index.php?page=$2&name=$1 [L,QSA]
RewriteRule ^([A-Za-z0-9\\-]+)$ ' . $this->m_url_path . 'index.php?name=$1 [L,QSA]
      ';
    $this->m_model_cloaking->GetForHtaccess($data);
    //---
    if(file_put_contents($fname, $data))
      {
      CLogger::write(CLoggerType::DEBUG, 'htaccess file created: ' . $fname);
      //--- ставим права 777 для каждого файла
      chmod($fname, 0777);
      }
    return $data;
    }

  /**
   * Обновление .htaccess
   */
  public function UpdateDynamicHtaccess()
    {
    $fname = $this->localPath . '/.htaccess';
    if(file_exists($fname))
      {
      $data = file_get_contents($fname);
      $data = str_replace('[HTACCESS_URLS_DYNAMICS]', $this->m_htaccess_string, $data);
      file_put_contents($fname, $data);
      }
    }

  private function GenerateDefaultFunctionPhp()
    {
    $filename = './inc/functions_php.php';
    if(file_exists($filename)) return file_get_contents($filename);
    CLogger::write(CLoggerType::ERROR, 'file not found ' . $filename);
    return '';
    }

  /**
   * Создание папки для динамического дорвея и копирование базовых файлов
   */
  private function DynamicCreatePaths()
    {
    //--- создадим папку дата
    $dataPath = $this->localPath . '/data';
    if(!is_dir($dataPath))
      {
      mkdir($dataPath);
      chmod($dataPath, 0777);
      }
    //--- скопируем файл с функциями
    $file_funcs = './inc/public/functions_php.php';
    if(file_exists($file_funcs))
      {
      copy($file_funcs, $dataPath . '/funcs.php');
      }
    else
      {
      CLogger::write(CLoggerType::ERROR, 'file not found ' . $file_funcs);
      }
    }

  /**
   * Получение пхп кода, для определения как вычитывать данные. Не для ЧПУ
   * @param string $pageName
   *
   * @return string
   */
  private function GeneratePhpCodePageName($pageName)
    {
    if($this->m_is_chpu) return '';
    //--- попробуем распарсить вид ?d=[N]&page=[KEYWORD]
    $url = parse_url($pageName);
    parse_str($url["query"], $output);
    //---
    $result = '';
    foreach($output as $name => $value)
      {
      $req = '$_REQUEST["' . str_replace('"', '', $name) . '"]';
      switch(strtoupper($value))
      {
        case '[N]':
          $result .= '$page=isset(' . $req . ') ? (int)' . $req . ':0;' . "\r\n";
          break;
        case '[KEYWORD]':
          $result .= '$name=isset(' . $req . ') ? ' . $req . ':"";' . "\r\n";
          break;
      }
      }
    //---
    $result .= '$page=isset($_REQUEST["page"]) ? (int)$_REQUEST["page"]:$page;' . "\r\n";
    return $result;
    }

  /**
   * Добавление строки с .htaccess
   *
   * @param $key
   * @param $i
   */
  private function AddUrlToHtaccess($key, $i)
    {
    $url = $key->GetUrl(); //$this->m_model_keywords->GetPageNameKey($key, $i);
    $url = str_replace($this->m_params['nextUrl'], '', $url);
    //---
    if(!empty($url)) $this->m_htaccess_string = 'RewriteRule ' . $url . ' ' . $this->m_url_path . 'index.php?page=' . $i . ' [L,QSA]' . "\r\n" . $this->m_htaccess_string;
    }

  /**
   * Получение данных для текущего кейворда в звависимости от $m_dynamic_param
   * @param int $i
   * @param CKeywordInfo $keyword_info
   * @param string $content
   *
   * @return array
   */
  public function DynamicGetData($i, $keyword_info, $content)
    {
    $data    = array();
    $num     = 0;
    $keyword = $keyword_info->getKeywordIndex(0);
    //--- если ЧПУ, то нужно добавить урл в .htaccess
    if($this->m_is_chpu && stripos($this->m_dynamic_pagename, '[topic]') !== false) $this->AddUrlToHtaccess($keyword_info, $i);
    foreach($this->m_dynamic_param as $param_name => $param_value)
      {
      CLogger::write(CLoggerType::DEBUG, 'dynamic params: ' . $param_name);
      switch($param_name)
      {
        case 'title':
          //--- у тайтла пробежимся по всем макросам
          $data['title'] = $this->m_model_macros->ReplaceManyMacros($this->m_params['pageTitle'], $i);
          break;
        case 'description':
          //--- у тайтла пробежимся по всем макросам
          $data['description'] = $this->m_model_macros->ReplaceManyMacros($this->m_params['metaDescription'], $i);
          break;
        case 'content':
          $data['content'] = $this->m_model_macros->ReplaceManyMacros($content, $i);
          break;
        case 'keyword':
          $data['keyword'] = $keyword;
          break;
        case 'keywords':
          $data['keywords'] = $this->m_model_keywords->GetRandKeywords($num, -1);
          break;
        case 'uc-keyword':
          $data['uc-keyword'] = CModel_helper::GetUcFirst($keyword);
          break;
        case 'ucw-keyword':
          $data['ucw-keyword'] = CModel_helper::GetUcWords($keyword);
          break;
        case 'text-number':
          //--- для каждого сделаем текст
          foreach($param_value as $id => $val)
            {
            $text = $this->m_model_text->GetText($val);
            if(isset($this->m_params['is_commpress']) && $this->m_params['is_commpress'] == 'on') $text = CModel_tools::HtmlCompress($text);
            $data['text-number'][$id] = $text;
            }
          break;
        case 'text-nokey-number':
          //--- для каждого сделаем текст
          foreach($param_value as $id => $val)
            {
            $text = $this->m_model_text->GetTextNoKey($val);
            if(isset($this->m_params['is_commpress']) && $this->m_params['is_commpress'] == 'on') $text = CModel_tools::HtmlCompress($text);
            $data['text-nokey-number'][$id] = $text;
            }
          break;
        case 'text-clear-number':
          //--- для каждого сделаем текст
          foreach($param_value as $id => $val) $data['text-clear-number'][$id] = $this->m_model_text->GetTextClear($val);
          break;
        case 'text-clear-nokey-number':
          //--- для каждого сделаем текст
          foreach($param_value as $id => $val) $data['text-clear-nokey-number'][$id] = $this->m_model_text->GetTextClearNoKey($val);
          break;
        case 'rand-keyword':
          for($j = 0; $j <= (int)$param_value; $j++) $data['rand-keyword'][$j] = $this->m_model_keywords->GetRandKeyword($num, -1)->getKeywordRand();
          break;
        case 'rand-uc-keyword':
          for($j = 0; $j <= (int)$param_value; $j++) $data['rand-uc-keyword'][$j] = $this->m_model_macros->GetRandUcKeyword($i);
          break;
        case 'rand-link':
          for($j = 0; $j <= (int)$param_value; $j++) $data['rand-link'][$j] = $this->m_model_macros->GetRandLink($i);
          break;
        case 'rand-url':
          for($j = 0; $j <= (int)$param_value; $j++) $data['rand-url'][$j] = $this->m_model_macros->GetRandUrl($i);
          break;
        case 'rand-uc-link':
          for($j = 0; $j <= (int)$param_value; $j++) $data['rand-uc-link'][$j] = $this->m_model_macros->GetRandUcLink($i);
          break;
        case 'rand-uc-links':
          //--- для каждого сделаем текст
          foreach($param_value as $id => $val) $data['rand-uc-links'][$id] = $this->m_model_macros->GetRandUcLinks($val, $i);
          break;
        case 'uc-link':
          foreach($param_value as $id => $val) $data['uc-link'][$id] = $this->m_model_macros->GetUcLink($val);
          break;
        case 'next-link':
          for($j = 0; $j <= (int)$param_value; $j++) $data['next-link'][$j] = $this->m_model_macros->GetNextLink();
          break;
        case 'prev-link':
          for($j = 0; $j <= (int)$param_value; $j++) $data['prev-link'][$j] = $this->m_model_macros->GetPrevLink();
          break;
        case 'next-uc-link':
          for($j = 0; $j <= (int)$param_value; $j++) $data['next-uc-link'][$j] = $this->m_model_macros->GetNextUcLink();
          break;
        case 'prev-uc-link':
          for($j = 0; $j <= (int)$param_value; $j++) $data['prev-uc-link'][$j] = $this->m_model_macros->GetPrevUcLink();
          break;
        case 'next-ucw-link':
          for($j = 0; $j <= (int)$param_value; $j++) $data['next-ucw-link'][$j] = $this->m_model_macros->GetNextUcwLink();
          break;
        case 'prev-ucw-link':
          for($j = 0; $j <= (int)$param_value; $j++) $data['prev-ucw-link'][$j] = $this->m_model_macros->GetPrevUcwLink();
          break;
        case 'next-uc-links-numbers':
          foreach($param_value as $id => $val) $data['next-uc-links-numbers'][$id] = $this->m_model_macros->GetNextUcLinksNumbers($val);
          break;
        case 'prev-uc-links-numbers':
          foreach($param_value as $id => $val) $data['next-uc-links-numbers'][$id] = $this->m_model_macros->GetPrevUcLinksNumbers($val);
          break;
        case 'next-ucw-links-numbers':
          foreach($param_value as $id => $val) $data['next-ucw-links-numbers'][$id] = $this->m_model_macros->GetNextUcwLinksNumbers($val);
          break;
        case 'prev-ucw-links-numbers':
          foreach($param_value as $id => $val) $data['prev-ucw-links-numbers'][$id] = $this->m_model_macros->GetPrevUcwLinksNumbers($val);
          break;
        case 'next-url':
          for($j = 0; $j <= (int)$param_value; $j++) $data['next-url'][$j] = $this->m_model_macros->GetNextUrl();
          break;
        case 'prev-url':
          for($j = 0; $j <= (int)$param_value; $j++) $data['prev-url'][$j] = $this->m_model_macros->GetPrevUrl();
          break;
        case 'nick':
          for($j = 0; $j <= (int)$param_value; $j++) $data['nick'][$j] = CModel_tools::GetNick();
          break;
        case 'rand-line':
          foreach($param_value as $id => $val) $data['rand-line'][$id] = $this->m_model_text->GetRandLine($val);
          break;
        case 'rand-line-file':
          foreach($param_value as $id => $val) $data['rand-line-file'][$id] = $this->m_model_text->GetRandLineFile($val);
          break;
        case 'next-line-file':
          foreach($param_value as $id => $val) $data['next-line-file'][$id] = $this->m_model_text->GetNextLineFile($val);
          break;
        //---
        case 'rand-img-resize':
          foreach($param_value as $id => $val) $data['rand-img-resize'][$id] = $this->m_model_macros->GetRandImgResize($val);
          break;
        //---
        case 'rand-img-from':
          foreach($param_value as $id => $val) $data['rand-img-from'][$id] = $this->m_model_macros->GetRandImgFrom($val);
          break;
        //---
        case 'rand-img':
          for($i = 0; $i <= (int)$param_value; $i++) $data['nick'][$i] = $this->m_model_macros->GetRandImg();
          break;
        case 'rand-img-from-resize':
          foreach($param_value as $id => $val) $data['rand-img-from-resize'][$id] = $this->m_model_macros->GetRandImgResizeFrom($val);
          break;
        //---
        case
        'rand-img-url-from-file':
          foreach($param_value as $id => $val) $data['rand-img-url-from-file'][$id] = $this->m_model_macros->GetRandImgUrlFromFileurl($val);
          break;
        //---
        case 'gen-img':
          for($j = 0; $j <= (int)$param_value; $j++) $data['gen-img'][$j] = $this->m_model_macros->GetGenerateImg();
          break;
        case 'gen-img-resize':
          foreach($param_value as $id => $val) $data['gen-img-resize'][$id] = $this->m_model_macros->GetGenerateImgResize($val);
          break;
        case 'gen-img-from':
          foreach($param_value as $id => $val) $data['gen-img-from'][$id] = $this->m_model_macros->GetGenerateImgFrom($val);
          break;
        case 'gen-img-resize-from':
          foreach($param_value as $id => $val) $data['gen-img-resize-from'][$id] = $this->m_model_macros->GetGenerateImgResizeFrom($val);
          break;
        case 'rand-video-size':
          foreach($param_value as $id => $val) $data['rand-video-size'][$id] = $this->m_model_macros->GetRandVideoSize($val);
          break;
        case 'rand-video-url':
          foreach($param_value as $id => $val) $data['rand-video-url'][$id] = $this->m_model_macros->GetRandVideoUrl($val);
          break;
        case 'menu-category':
          for($j = 0; $j <= (int)$param_value; $j++) $data['menu-category'][$j] = $this->m_model_macros->GetMenuCategory();
          break;
        case 'tags':
          for($j = 0; $j <= (int)$param_value; $j++)
            {
            $tags = $this->m_model_macros->GetTags();
            if(isset($this->m_params['is_commpress']) && $this->m_params['is_commpress'] == 'on') $tags = CModel_tools::HtmlCompress($tags);
            $data['tags'][$j] = $tags;
            }
          break;
        case 'rand-int':
          foreach($param_value as $id => $val) $data['rand-int'][$id] = $this->m_model_macros->GetRandInt($val);
          break;
        case 'nn':
          $data['nn'] = $i;
          break;
        case 'encode-rand-key':
          for($j = 0; $j <= (int)$param_value; $j++) $data['encode-rand-key'][$j] = $this->m_model_macros->GetEcodeRandKey();
          break;
        case 'encode-current-key':
          $data['encode-current-key'] = urlencode($keyword);
          break;
        case 'rand-spam-url':
          for($j = 0; $j <= (int)$param_value; $j++) $data['rand-spam-url'][$j] = $this->m_model_links->MacrosSpamUrl();
          break;
        case 'rand-spam-link':
          for($j = 0; $j <= (int)$param_value; $j++) $data['rand-spam-link'][$j] = $this->m_model_links->MacrosSpamLink();
          break;
        case 'rand-spam-uc-link':
          for($i = 0; $i <= (int)$param_value; $i++) $data['rand-spam-uc-link'][$i] = $this->m_model_links->MacrosSpamUCLink();
          break;
        case 'rand-spam-uc-links-many':
          foreach($param_value as $id => $val) $data['rand-spam-uc-links-many'][$id] = $this->m_model_links->MacrosSpamUCManyLinks($val);
          break;
        case 'text-macros':
          foreach($param_value as $id => $val)
            {
            $text = $this->m_model_macros->GetTextMacros($val);
            if(isset($this->m_params['is_commpress']) && $this->m_params['is_commpress'] == 'on') $text = CModel_tools::HtmlCompress($text);
            $data['text-macros'][$id] = $text;
            }
          break;
      }
      }
    return $data;
    }

  /**
   * Получаем строку для rss и если надо генерируем файл
   * @return string
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
   * @return string
   */
  private function GetRSS()
    {
    if(!$this->m_is_rss_generated)
      {
      $this->m_model_rss->RSSGenerateFile();
      $this->m_is_rss_generated = true;
      }
    //---
    return '<link href="' . $this->m_params['nextUrl'] . 'rss.xml" rel="alternate" type="application/rss+xml" title="' . htmlspecialchars($this->m_params['pageTitle']) . '">';
    }

  /**
   * Установка модуля редиректа
   * @param $model_redirect
   */
  public function SetRedirect(&$model_redirect)
    {
    $this->m_model_redirect = $model_redirect;
    }

  /**
   * ПОлучим список ip адресов
   */
  private function GetIpSearchers()
    {
    $php = '';
    //---
    $settings = new CModel_settings();
    $address  = $settings->GetGlobal('ip_searchers', '');
    if(!empty($address))
      {
      $php .= '$BOTS_IP=array(' . "\r\n";
      //---
      foreach($address as $ip)
        {
        if(empty($ip[0]) || empty($ip[1])) continue;
        //---
        $php .= 'array(' . $ip[0] . ',' . $ip[1] . '),' . "\r\n";
        }
      $php .= ');' . "\r\n";
      }
    //---
    return $php;
    }
  }

?>