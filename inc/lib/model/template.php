<?php
//+------------------------------------------------------------------+
//|                  Copyright c 2012, RedButton Web Sites Generator |
//|                                      http://www.getredbutton.com |
//+------------------------------------------------------------------+
/**
 * Разбор файла с шаблоном дорвея
 * @author lezzvie
 */
class CModel_template
  {
  //--- путь к папке с шаблонами
  const TEMPLATE_PATH = 'data/templates';
  private $body;
  private $content;
  private $comment1;
  private $comment2;
  private $files = array();
  /**
   * стоп имена файлов для закачки, массив из шаблонов
   * @var array
   */
  private $m_stop_filename = array('page.html',
                                   'index.html',
                                   'category.html',
                                   'sitemap.html',
                                   'robots.txt');
  //--- путь к выбранному шаблону
  private $templateDir = '';
//--- боди для index.html
  private $m_body_index;
//--- контент для index_html
  private $m_content_index;
//--- контент для сайт sitemap
  private $m_content_sitemap;
//--- боди для sitemap.html
  private $m_body_sitemap;
//--- контент для сайт category
  private $m_content_category;
//--- боди для category.html
  private $m_body_category;
//--- массив для хранения данных о doit
  private $m_doit_blocks = array();
//--- массив для хранения данных о doitrel
  private $m_doit_relative_blocks = array();
//--- имя шаблона, используется для замены данных в шаблонах
  private $m_name_template;
  /**
   * случайным образом выбираем шаблон
   * @var bool
   */
  private $m_is_random = false;

  /**
   * Что между <!rb:content для индексной
   *
   */
  public function GetContentIndex()
    {
    //--- проверим, может быть надо вставить блок DOIT
    if($this->HasDoit())
      {
      return $this->ReplaceDoitBlock($this->m_content_index, 'index');
      }
    return $this->m_content_index;
    }

  /**
   * Body для индексной
   *
   */
  public function GetBodyIndex()
    {
    //--- проверим, может быть надо вставить блок DOIT
    if($this->HasDoit())
      {
      return $this->ReplaceDoitBlock($this->m_body_index, 'index');
      }
    return $this->m_body_index;
    }

  /**
   * Что между для sitemap
   *
   */
  public function GetContentSitemap()
    {
    //--- проверим, может быть надо вставить блок DOIT
    if($this->HasDoit())
      {
      return $this->ReplaceDoitBlock($this->m_content_sitemap, 'sitemap');
      }
    return $this->m_content_sitemap;
    }

  /**
   * Body для индексной
   *
   */
  public function GetBodySitemap()
    {
    //--- проверим, может быть надо вставить блок DOIT
    if($this->HasDoit())
      {
      return $this->ReplaceDoitBlock($this->m_body_sitemap, 'sitemap');
      }
    return $this->m_body_sitemap;
    }

  /**
   * Что между <!rb:content для category
   *
   */
  public function GetContentCategory()
    {
    //--- проверим, может быть надо вставить блок DOIT
    if($this->HasDoit())
      {
      return $this->ReplaceDoitBlock($this->m_content_category, 'category');
      }
    return $this->m_content_category;
    }

  /**
   * Body для индексной
   *
   */
  public function GetBodyCategory()
    {
    //--- проверим, может быть надо вставить блок DOIT
    if($this->HasDoit())
      {
      return $this->ReplaceDoitBlock($this->m_body_category, 'category');
      }
    return $this->m_body_category;
    }

  /**
   * Боди для обычной страницы
   * @return mixed
   */
  public function GetBody()
    {
    //--- проверим, может быть надо вставить блок DOIT
    if($this->HasDoit())
      {
      return $this->ReplaceDoitBlock($this->body, 'page');
      }
    return $this->body;
    }

  /**
   * контент для обычной страницы
   * @return mixed
   */
  public function GetContent()
    {
    if($this->HasDoit())
      {
      return $this->ReplaceDoitBlock($this->content, 'page');
      }
    return $this->content;
    }

  public function GetComment1()
    {
    return $this->comment1;
    }

  public function GetComment2()
    {
    return $this->comment2;
    }

  public function GetFiles()
    {
    return $this->files;
    }

  /**
   * Получение случайного пути для шаблона
   * @param string $dir
   * @return string
   */
  private function GetRandomPath($dirname)
    {
    if(file_exists($dirname . '/page.html') || file_exists($dirname . '/index.html')) ;
      {
      //--- если выбран какой-то конкретный шаблон, то нужно из parent взять все и случайным образом
      $dirname = dirname($dirname);
      }
    //--- список папок
    $dirs = CModel_helper::ListArraySubDirs($dirname, '', '', array('index.html',
                                                                    'page.html'), 0);
    $d    = $dirs[array_rand($dirs)];
    //$d = $dirs[4];
    if($d['count'] > 0)
      {
      $d   = $d['child'][array_rand($d['child'])];
      $dir = $d['name'];
      }
    else $dir = $d['name'];
    //---
    return $dirname . '/' . $dir;
    }

  /**
   * Инициализация и разбор файла с шаблоном дорвея
   */
  public function __construct($dirName, $is_random)
    {
    if(empty($dirName)) return;
    //---
    $this->m_is_random = $is_random;
    $this->templateDir = self::TEMPLATE_PATH . '/' . $dirName;
    CLogger::write(CLoggerType::DEBUG, 'template: init path: ' . $this->templateDir);
    //--- проверим может быть случайный нужно выбрать
    if($this->m_is_random)
      {
      $this->templateDir = $this->GetRandomPath($this->templateDir);
      CLogger::write(CLoggerType::DEBUG, 'template: random path ' . $this->templateDir);
      }
    //--- получаем путь к указанному шаблону
    else
      {
      $this->templateDir = self::TEMPLATE_PATH . '/' . $dirName;
      }
    //--- проверим существование папки с шаблоном
    if(!is_dir($this->templateDir))
      {
      CLogger::write(CLoggerType::ERROR, 'template: path not found ' . $this->templateDir);
      return;
      }
    //--- пробуем загрузить старую версию шаблона
    if(!$this->TryToLoadOldTemplate($this->templateDir))
      {
      //--- загружаем шаблон нового образца
      $this->LoadTemplate($this->templateDir);
      }
    }

  /**
   *
   * Установка папки к шаблону
   * @param string $dirName
   */
  public function setTemplateDir($dirName)
    {
    //---
    $this->templateDir = self::TEMPLATE_PATH . '/' . $dirName;
    //--- проверим существование папки с шаблоном
    if(!is_dir($this->templateDir))
      {
      CLogger::write(CLoggerType::ERROR, 'template: path not found ' . $this->templateDir);
      return;
      }
    //--- пробуем загрузить старую версию шаблона
    if(!$this->TryToLoadOldTemplate($this->templateDir))
      {
      //--- загружаем шаблон нового образца
      $this->LoadTemplate($this->templateDir);
      }
    }

  /**
   * Стоп имена файлов
   *
   * @param array $files
   */
  public function setStopFiles($files)
    {
    $this->m_stop_filename = $files;
    }

  /**
   *
   * Получение пути к файлу со стилями
   */
  public function getStopFiles()
    {
    return $this->m_stop_filename;
    }

  /**
   * Загрузка шаблона нового образца
   * @param string $templateDir
   */
  private function LoadTemplate($templateDir)
    {
    $pageFileName     = $templateDir . '/page.html';
    $indexFileName    = $templateDir . '/index.html';
    $sitemapFileName  = $templateDir . '/sitemap.html';
    $categoryFileName = $templateDir . '/category.html';
    //---
    if(!file_exists($pageFileName))
      {
      CLogger::write(CLoggerType::ERROR, 'template: index file not found ' . $pageFileName);
      return false;
      }
    CLogger::write(CLoggerType::ERROR, 'template: page file loaded ' . $pageFileName);
    //--- получаем содержимое шаблона
    $template = file_get_contents($pageFileName);
    //--- меняем старые макросы на новые
    $template = $this->ReplaceOldMacroses($template);
    //--- замена повторящихся блоков
    $this->GetMacrosDoit($template, 'page');
    //--- пробуем получить основной контент шаблона
    $this->content = $this->ParseText($template, '<!-- rb:content -->', '<!-- /rb:content -->');
    //--- пробуем получить контент для нечетных комментариев
    $this->comment1 = $this->ParseText($template, '<!-- rb:comment1 -->', '<!-- /rb:comment1 -->');
    //--- пробуем получить контент для четных комментариев
    $this->comment2 = $this->ParseText($template, '<!-- rb:comment2 -->', '<!-- /rb:comment2 -->');
    //--- вырезаем из шаблона блоки с основным текстом и комментариями, заменяем их на макросы
    $this->body = $this->CutTextFromTags($template, '[RB:CONTENT]', '<!-- rb:content -->', '<!-- /rb:content -->');
    $this->body = $this->CutTextFromTags($this->body, '[RB:COMMENTS]', '<!-- rb:comment1 -->', '<!-- /rb:comment1 -->');
    $this->body = $this->CutTextFromTags($this->body, '', '<!-- rb:comment2 -->', '<!-- /rb:comment2 -->');
    $this->InitIndex($indexFileName);
    $this->InitSitemap($sitemapFileName);
    $this->InitCategory($categoryFileName);
    }

  /**
   * Инцилизация для категорий
   * @param $categoryFileName
   */
  private function InitCategory($categoryFileName)
    {
    //--- если в папке с шаблоном есть файл sitemap.html
    if(file_exists($categoryFileName))
      {
      //--- получаем контент индексного файла
      $template_index = file_get_contents($categoryFileName);
      $template_index = $this->ReplaceOldMacroses($template_index);
      //--- замена повторящихся блоков
      $this->GetMacrosDoit($template_index, 'sitemap');
      $this->m_content_category = $this->ParseText($template_index, '<!-- rb:content -->', '<!-- /rb:content -->');
      //--- меняем старые макросы на новые
      //--- вырезаем из шаблона блоки с основным текстом и комментариями, заменяем их на макросы
      $this->m_body_category = $this->CutTextFromTags($template_index, '[RB:CONTENT]', '<!-- rb:content -->', '<!-- /rb:content -->');
      $this->m_body_category = $this->CutTextFromTags($this->m_body_category, '[RB:COMMENTS]', '<!-- rb:comment1 -->', '<!-- /rb:comment1 -->');
      $this->m_body_category = $this->CutTextFromTags($this->m_body_category, '', '<!-- rb:comment2 -->', '<!-- /rb:comment2 -->');
      CLogger::write(CLoggerType::ERROR, 'template: catergory file loaded ' . $categoryFileName);
      }
    }

  /**
   * инцилизация шаблона сайтмапа
   * @param $sitemapFileName
   */
  private function InitSitemap($sitemapFileName)
    {
    //--- если в папке с шаблоном есть файл sitemap.html
    if(file_exists($sitemapFileName))
      {
      //--- получаем контент индексного файла
      $template_index = file_get_contents($sitemapFileName);
      $template_index = $this->ReplaceOldMacroses($template_index);
      //--- замена повторящихся блоков
      $this->GetMacrosDoit($template_index, 'sitemap');
      $this->m_content_sitemap = $this->ParseText($template_index, '<!-- rb:content -->', '<!-- /rb:content -->');
      //--- меняем старые макросы на новые
      //--- вырезаем из шаблона блоки с основным текстом и комментариями, заменяем их на макросы
      $this->m_body_sitemap = $this->CutTextFromTags($template_index, '[RB:CONTENT]', '<!-- rb:content -->', '<!-- /rb:content -->');
      $this->m_body_sitemap = $this->CutTextFromTags($this->m_body_sitemap, '[RB:COMMENTS]', '<!-- rb:comment1 -->', '<!-- /rb:comment1 -->');
      $this->m_body_sitemap = $this->CutTextFromTags($this->m_body_sitemap, '', '<!-- rb:comment2 -->', '<!-- /rb:comment2 -->');
      CLogger::write(CLoggerType::DEBUG, 'template: sitemap file loaded ' . $sitemapFileName);
      }
    }

  /**
   * Инцилизация index шаблона
   * @param $indexFileName
   */
  private function InitIndex($indexFileName)
    {
    //--- если в папке с шаблоном есть файл index.html
    if(file_exists($indexFileName))
      {
      //--- получаем контент индексного файла
      $template_index = file_get_contents($indexFileName);
//--- меняем старые макросы на новые
      $template_index = $this->ReplaceOldMacroses($template_index);
      $this->GetMacrosDoit($template_index, 'index');
//---
      $this->m_content_index = $this->ParseText($template_index, '<!-- rb:content -->', '<!-- /rb:content -->');
      //--- вырезаем из шаблона блоки с основным текстом и комментариями, заменяем их на макросы
      $this->m_body_index = $this->CutTextFromTags($template_index, '[RB:CONTENT]', '<!-- rb:content -->', '<!-- /rb:content -->');
      $this->m_body_index = $this->CutTextFromTags($this->m_body_index, '[RB:COMMENTS]', '<!-- rb:comment1 -->', '<!-- /rb:comment1 -->');
      $this->m_body_index = $this->CutTextFromTags($this->m_body_index, '', '<!-- rb:comment2 -->', '<!-- /rb:comment2 -->');
      CLogger::write(CLoggerType::DEBUG, 'template: index file loaded ' . $indexFileName);
      }
    }

  /**
   * Получение текста шаблона
   * @param string $dirName
   */
  public function GetTextTemplate($dirName, $page = 'page.html')
    {
    $templateDir = self::TEMPLATE_PATH . '/' . $dirName;
    //--- проверим существование папки с шаблоном
    if(!is_dir($templateDir))
      {
      CLogger::write(CLoggerType::ERROR, 'template: path not found ' . $templateDir);
      return;
      }
    $pageFileName = $templateDir . '/' . $page;
    //---
    if(!file_exists($pageFileName))
      {
      CLogger::write(CLoggerType::ERROR, 'template: index file not found ' . $pageFileName);
      return false;
      }
    //--- получаем содержимое шаблона
    return file_get_contents($pageFileName);
    }

  /**
   * получим содержание любого файла из шаблона
   *
   * @param $dirName
   * @param $filename
   * @param $size
   * @return null|string
   */
  public function GetTextFileTemplate($dirName, $filename, &$size)
    {
    $pageFileName = self::TEMPLATE_PATH . '/' . $dirName . '/' . $filename;
    if(!file_exists($pageFileName))
      {
      CLogger::write(CLoggerType::ERROR, 'template: file not found ' . $pageFileName);
      return null;
      }
    $size = filesize($pageFileName);
    //--- получаем содержимое файла
    return file_get_contents($pageFileName);
    }

  /**
   * Получение текста шаблона
   * @param string $dirName
   */
  public function SaveTextTemplate($dirName, $page, $text)
    {
    $templateDir = self::TEMPLATE_PATH . '/' . $dirName;
    //--- проверим существование папки с шаблоном
    if(!is_dir($templateDir))
      {
      CLogger::write(CLoggerType::ERROR, 'template: path not found ' . $templateDir);
      return false;
      }
    //---
    $pageFileName = $templateDir . '/' . $page;
    //---
    if(!file_exists($pageFileName))
      {
      CLogger::write(CLoggerType::ERROR, 'template: create index file: ' . $pageFileName);
      return file_put_contents($pageFileName, $text);
      }
    //--- получаем содержимое шаблона
    return file_put_contents($pageFileName, $text);
    }

  /**
   * Парсим текст между двумя тэгами
   * TODO: проверить парсинг с кириллицей в UTF-8
   * @param string $text Текст
   * @param string $startTag Начальный тэг
   * @param string $endTag Конечный тэг
   */
  private function ParseText($text, $startTag, $endTag)
    {
    //--- проверяем наличие текста и тэгов
    if(empty($text) || empty($startTag) || empty($endTag)) return NULL;
    //--- вычисляем позицию начального тэга
    $startPos = stripos($text, $startTag);
    //--- вычисляем позицию конечного тэга
    $endPos = stripos($text, $endTag);
    //--- вычисляем длину начального тэга
    $startTagLen = strlen($startTag);
    //--- если найдены оба вхождения
    return ($startPos !== FALSE && $endPos !== FALSE && $endPos > 0) //--- вырезаем и отдаем текст, который был между двумя тэгами
     ? substr($text, ($startPos + $startTagLen), ($endPos - $startPos - $startTagLen)) : NULL;
    }

  /**
   * ищем макрос doit-2-3 и заменяем его на doits-1, doits-2
   * ищем макрос doitrel-2-3 и заменяем его на doitrels-1, doitrels-2
   * @param $text
   * @return null
   */
  private function GetMacrosDoit(&$text, $index)
    {
    if(empty($text)) return null;
    //---
    $startPos = 0;
    $id       = 0;
    $idrel    = 0;
    $noblock  = 0;
    while(true)
      {
      $startPos = stripos($text, '[DOITREL-', $startPos);
      //--- блок нашли обработаем
      if($startPos !== FALSE)
        {
        if(!$this->fillDoitBlockRelative($text, $startPos, $idrel, $index)) break;
        }
      else $noblock++;
      //--- ищем другие блоки
      $startPos = stripos($text, '[DOIT-', $startPos);
      if($startPos !== FALSE)
        {
        if(!$this->fillDoitBlock($text, $startPos, $id, $index)) break;
        }
      else $noblock++;
//--- ни одного блока не найдено, выходим
      if($noblock == 2) break;
      else $noblock = 0;
      }
    }

  /**
   * Обработка блока DOIT
   * @param $text текст
   * @param $startPos откуда начинаем обрабатывать текст
   * @param $id id записи
   * @param $index
   * @return bool
   */
  private function fillDoitBlock(&$text, &$startPos, &$id, $index)
    {
    $i   = $startPos + 5;
    $min = '';
    $max = '-1';
    $len = strlen($text);
    while($i < $len)
      {
      $i++;
      //--- нашли конец начального макроса
      if($text[$i] == ']') break;
      if($max == '-1' && $text[$i] != '-')
        {
        $min .= $text[$i];
        }
      //--- начнем парсить max
      elseif($text[$i] == '-')
        {
        $max = '';
        }
      else
        {
        $max .= $text[$i];
        }
      }
    //---
    if($min > $max) $min = $max;
    //---
    $endPos = stripos($text, "[/DOIT]", $i);
    if($endPos !== FALSE)
      {
      $this->m_doit_blocks[$index][$id] = array('id'      => $id,
                                                'min'     => $min,
                                                'max'     => $max,
                                                'content' => substr($text, $i + 1, $endPos - $i - 1));
      $text                             = substr($text, 0, $startPos) . '[DOITS-' . $id . ']' . substr($text, $endPos + 7); // +7 это длина строки [/DOIT]
      $id++;
      return true;
      }
//---
    return false;
    }

  /**
   * Обработка блока DOITREL
   * @param $text
   * @param $startPos
   * @param $id
   * @param $index
   * @return bool
   */
  private function fillDoitBlockRelative(&$text, &$startPos, &$id, $index)
    {
    //--- 8 [DOITSREL
    $i   = $startPos + 8;
    $min = '';
    $max = '-1';
    $len = strlen($text);
    while($i < $len)
      {
      $i++;
      //--- нашли конец начального макроса
      if($text[$i] == ']') break;
      if($max == '-1' && $text[$i] != '-')
        {
        $min .= $text[$i];
        }
      //--- начнем парсить max
      elseif($text[$i] == '-')
        {
        $max = '';
        }
      else
        {
        $max .= $text[$i];
        }
      }
    //---
    if($min > $max) $min = $max;
    //---
    $endPos = stripos($text, "[/DOITREL]", $i);
    if($endPos !== FALSE)
      {
      $content = substr($text, $i + 1, $endPos - $i - 1);
      //$this->ReplaceKeywordsMacros($content);
      $this->m_doit_relative_blocks[$index][$id] = array('id'      => $id,
                                                         'min'     => $min,
                                                         'max'     => $max,
                                                         'content' => $content);
      //---
      $text = substr($text, 0, $startPos) . '[DOITSREL-' . $index . '-' . $id . ']' . substr($text, $endPos + 10); // +10 это длина строки [/DOITREL]
      $id++;
      return true;
      }
//---
    return false;
    }

  /**
   * Что нужно вставлять вместо блока DOITSREL
   * @param $index
   * @return null
   */
  public function GetDoitsRelBlock($index, $id)
    {
    return isset($this->m_doit_relative_blocks[$index]) ? $this->m_doit_relative_blocks[$index][$id] : null;
    }

  private function ReplaceKeywordsMacros(&$content)
    {
    $macros     = '[KEYWORD]';
    $len_macros = strlen($macros);
    //---
    $i     = 0;
    $index = 0;
    //--- пока найдем макрос заменяем с индексом
    while(($pos = strpos($macros, $content, $i)) !== FALSE)
      {
      $content = substr($content, 0, $pos) . '[KEYWORD-' . $index . ']' . substr($content, 0, $pos + $len_macros);
      $i       = $pos + 1;
      $index++;
      }
    //---
    }

  /**
   * Вырезаем текст между тэгов и вставляем туда указанный макрос
   * @param string $text
   * @param string $replacement
   * @param string $startTag
   * @param string $endTag
   */
  private function CutTextFromTags($text, $replacement, $startTag, $endTag)
    {
    //--- проверяем наличие текста и тэгов
    if(empty($text) || empty($startTag) || empty($endTag)) return $text;
    //--- вычисляем позицию начального тэга
    $startPos = stripos($text, $startTag);
    //--- вычисляем позицию конечного тжга
    $endPos = stripos($text, $endTag);
    //--- если найдены оба вхождения
    return ($startPos !== FALSE && $endPos !== FALSE && $endPos > 0) //--- заменяем текст между тэгами на указанный заменитель
     ? substr($text, 0, $startPos) . $replacement . substr($text, $endPos + strlen($endTag)) : $text;
    }

  /**
   * Загрузка шаблона старого типа для Forum Generator или redButton 1.x/2.x
   * @param string $fileName
   */
  private function TryToLoadOldTemplate($templateDir)
    {
    $fileName = $templateDir . '/theme.html';
    //--- если файл не найден - возвращаем false
    if(!file_exists($fileName))
      {
      //--- TODO: пишем в лог о том, что шаблон не может быть загружен
      return false;
      }
    //---
    $templateParts = array();
    $contentParts  = array();
    //--- получаем содержимое шаблона
    $template = file_get_contents($fileName);
    //--- меняем старые макросы на новые
    $template = $this->ReplaceOldMacroses($template);
    //--- делим шаблон на части
    $templateParts = explode('<hr size=1 id=8888>', $template, 4);
    //--- если получилось разделить на 4 логических части
    if(sizeOf($templateParts) == 4)
      {
      $this->body     = $templateParts[0];
      $this->comment1 = $templateParts[1];
      $this->comment2 = $templateParts[2];
      $this->body .= '[RB:COMMENTS]' . $templateParts[3];
      } //--- если шаблон не разделили, то все в body
    else
      {
      $this->body = $template;
      }
    //--- если шаблон не пустой, то ищем блок с контентом
    if(!empty($this->body))
      {
      $contentParts = explode('<!-- hesoyam rb -->', $this->body, 3);
      if(sizeof($contentParts) == 3) $this->content = $contentParts[1];
      //--- заменяем контент на макрос
      $this->body = str_replace($this->content, '[RB:CONTENT]', $this->body);
      //--- убираем лишние тэги (NOTE: можно было-бы красивее сделать)
      $this->body = str_replace('<!-- hesoyam rb -->', '', $this->body);
      }
    //--- очищаем
    unset($template, $templateParts, $contentParts);
    //---
    return true;
    }

  /**
   * Замена старых макросов на новые в тексте шаблона,
   * для поддержки обоих типов макросов и обоих типов шаблонов
   * @param string $text
   */
  private function ReplaceOldMacroses($text)
    {
    if(empty($text)) return '';
    //--- заменяем и возвращаем
    return str_replace( //--- старые макросы
      array('[TEXT]',
            '[RANDLIST]',
            '[RANDKEYWORD]',
            '[BKEYWORD',
            '[RANDBKEYWORDURL',
            '[RANDKEYWORDURL',
            '[OLD-RANDKEYWORDURL]',
            '[OLD-RANDURL]',
            '[SITE]',
            '[PLUSKEYWORD]',
            '[UNDERSCOREKEYWORD]',
            '[HYPHENKEYWORD]'), //--- новые макросы
      array('[RAND-LINE]',
            '[RAND-LINE]',
            '[RAND-KEYWORD]',
            '[UC-KEYWORD',
            '[RAND-UC-LINK',
            '[RAND-LINK',
            '[OLD-RAND-LINK]',
            '[OLD-RAND-URL]',
            '[HOME-URL]',
            '[ENCODE-KEYWORD+]',
            '[ENCODE-KEYWORD_]',
            '[ENCODE-KEYWORD-]'), $text);
    }

  /**
   *
   * Получение пути к шаблону
   */
  public function getTemplatePath()
    {
    return $this->templateDir;
    }

  /**
   * замена блоков DOINT-id на нужные значения
   * @param $text
   * @return mixed
   */
  private function ReplaceDoitBlock($text, $index)
    {
    if(empty($this->m_doit_blocks[$index])) return $text;
    //---
    foreach($this->m_doit_blocks[$index] as $info)
      {
      $count = rand($info['min'], $info['max']);
      $block = str_repeat($info['content'], $count);
      $text  = str_replace('[DOITS-' . $info['id'] . ']', $block, $text);
      }
    return $text;
    }

  /**
   * проверка наличия блоков DOIT
   * @return bool
   */
  public function HasDoit()
    {
    return !empty($this->m_doit_blocks);
    }

  /**
   * Зачистим все макросы
   * @param string $input
   * @return string
   */
  private function ClearMacros($input)
    {
    return preg_replace('/\[(.)*\]/', '', $input);
    }

  /**
   * Парсим все урлы и заменяем ссылки на нужные
   * @param string $input
   * @return string
   */
  private function parseTagsRecursive($input)
    {
    if($input[2] == '#' || Cmodel_helper::IsExistHttp($input[2])) return $input[0];
    //---
    return str_replace($input[2], "?module=templates&amp;a[getfile]&amp;template=" . $this->m_name_template . '&amp;file=' . urlencode($this->ClearMacros($input[2])), $input[0]);
    }

  /**
   * замена данных для шаблона, src и href
   * @param $template_name
   * @param $text
   */
  public function ReplaceTemplate($template_name, &$text)
    {
    $this->m_name_template = $template_name;
    $text                  = preg_replace_callback('/<[^<>]*href=(\"\'??)([^\"\' >]*)[^<>]*>/', array($this,
                                                                                                      'parseTagsRecursive'), $text);
    $text                  = preg_replace_callback('/<[^<>]*src=(\"\'??)([^\"\' >]*)[^<>]*>/', array($this,
                                                                                                     'parseTagsRecursive'), $text);
//preg_match_all('<[^<>]*src=(.*)[^<>]*>');
//preg_match_all('<[^<>]*href=(.*)[^<>]*>');
    }

  /**
   * Замена макросов
   */
  public function ReplaceMacrocesViewTemplate()
    {
    }
  }

?>