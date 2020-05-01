<?php
//+------------------------------------------------------------------+
//|                    Copyright 2013, RedButton Web Sites Generator |
//|                                      http://www.getredbutton.com |
//+------------------------------------------------------------------+
/**
 * Класс для API
 * @author User LeZZvie
 */
class CModel_api
  {
  /**
   * Где хранится токен
   */
  const TOKEN_PATH = 'data/settings/api';
  /**
   * Префикс для логов
   */
  const LOG_PREFIX = 'api:';
  /**
   * разделитель для блоков текста
   */
  const DELIMITER_TEXT = "\n.\n";
  /**
   *
   * Папка где храняться настройки
   * @var string
   */
  const SETTINGS_PATH = 'data/settings/api';
  /**
   *
   * Файл где храняться последние использованные настройки
   * @var string
   */
  const LAST_SET_FILENAME = '_last_settings.data.php';
  /**
   * список со стоп словами
   * @var array
   */
  private static $m_stop_words = array('_last_settings',
                                       '_global_settings');
  /**
   * Список настроек
   * @var array
   */
  private $m_settings = array();
  /**
   * модуль для управления плагинами
   * @var CModel_plugins
   */
  private $m_model_plugins;
  /**
   * управление ключами
   * @var CModel_keywords
   */
  private $m_model_keywords;
  /**
   * Управление текстами
   * @var CModel_text
   */
  private $m_model_text;
  /**
   * Модуль управления ссылками
   * @var CModel_links
   */
  private $m_model_links;
  /**
   * Модуль управления урлами на картинки
   * @var CModel_imageslinks
   */
  private $m_model_images;
  /**
   * Массив с параметрами от пользователя
   * @var array
   */
  private $m_params;
  private $m_file_texts_list = array();
  /**
   * цветом выделять ключевики
   * @var bool
   */
  private $m_is_keyword_color = false;
  /**
   * Выделять ключевики случайным цветом
   * @var bool
   */
  private $m_key_color_rand = false;
  /**
   * Выделять ключевики установленными цветами
   * @var array
   */
  private $m_key_colors = array();
  /**
   * Выделять фон у ключевиков случайным цветом
   * @var bool
   */
  private $m_key_color_background_rand = false;
  /**
   * Выделять фон у ключевиков установленными цветами
   * @var array
   */
  private $m_key_background_colors = array();
  /**
   * Минимальное количество выделяемых ключевиков
   * @var int
   */
  private $m_keys_min_color = 0;
  /**
   * Максимальное количество выделяемых ключевиков
   * @var int
   */
  private $m_keys_max_color = 0;
  /**
   * Минимальное количество выделяемых ключевиков в процентном соотношении
   * @var int
   */
  private $m_keys_min_color_percent = 0;
  /**
   * Максимальное количество выделяемых ключевиков в процентном соотношении
   * @var int
   */
  private $m_keys_max_color_percent = 0;
  /**
   * если значение percent, значит нужно выделять в процентном соотношении
   * @var string
   */
  private $m_keys_color_type = '';
  /**
   * шрифты менять у ключевиков
   * @var bool
   */
  private $m_is_keyword_font = false;
  /**
   * Минимальное количество изменять шрифт
   * @var int
   */
  private $m_keys_min_font = 0;
  /**
   * Максимальное количество выделяемых ключевиков
   * @var int
   */
  private $m_keys_max_font = 0;
  /**
   * Минимальное количество изменяемых шрифтов у ключевиков в процентном соотношении
   * @var int
   */
  private $m_keys_min_font_percent = 0;
  /**
   * Максимальное количество изменяемых шрифтов у ключевиков в процентном соотношении
   * @var int
   */
  private $m_keys_max_font_percent = 0;
  /**
   * если значение percent, значит нужно выделять в процентном соотношении
   * @var string
   */
  private $m_keys_font_type = '';
  /**
   * Список шрифтов
   * @var array
   */
  private $m_key_fonts = array();
  /**
   * Минимальный размер шрифта
   * @var int
   */
  private $m_keys_min_fontsize = 0;
  /**
   * Максимальный  размер шрифта
   * @var int
   */
  private $m_keys_max_fontsize = 0;
  /**
   * цветом выделять предложения
   * @var bool
   */
  private $m_is_sentence_color = false;
  /**
   * Выделять предложения случайным цветом
   * @var bool
   */
  private $m_sentence_color_rand = false;
  /**
   * Выделять предложения установленными цветами
   * @var array
   */
  private $m_sentence_colors = array();
  /**
   * Выделять фон у предложений случайным цветом
   * @var bool
   */
  private $m_sentence_color_background_rand = false;
  /**
   * Выделять фон у предложений установленными цветами
   * @var array
   */
  private $m_sentence_background_colors = array();
  /**
   * Минимальное количество выделяемых предложений
   * @var int
   */
  private $m_sentence_min_color = 0;
  /**
   * Максимальное количество выделяемых предложений
   * @var int
   */
  private $m_sentence_max_color = 0;
  /**
   * массив тэгов для выделения кейвордов
   * @var array
   */
  private $m_tags_keywords_selection = array();
  /**
   * массив из мин. и макс. значения плотности выделение кейвордов
   * @var array
   */
  private $m_density_keywords_selection = array(0,
                                                0);

  /**
   * Нужно ли вставлять в текст картинки
   * @var bool
   */
  /**
   * Конструктор
   */
  public function __construct()
    {
    }

  /**
   * Получение токена
   */
  public function GetToken()
    {
    if(!file_exists(self::TOKEN_PATH . '/token.data.php')) $this->ChangeToken();
    //---
    return file_get_contents(self::TOKEN_PATH . '/token.data.php');
    }

  /**
   * Смена токена
   */
  public function ChangeToken()
    {
    $token = substr(md5('200ok' . uniqid()), 1, 12);
    file_put_contents(self::TOKEN_PATH . '/token.data.php', $token);
    }

  /**
   * Парсер текст с урла
   * @return string
   */
  public function TextParserByUrl($url)
    {
    if(empty($url)) return null;
    //---
    $toolsModel       = new CModel_tools();
    $htmlCleanerModel = new CModel_HtmlCleaner();
    //--- парсинг текста с указанного URL
    $url  = $_GET['url'];
    $text = file_get_contents($url);
    $text = $toolsModel->CharsetConvert($text, 'UTF-8');
    return $htmlCleanerModel->Clear($text);
    }

  /**
   * Проверка входных параметров
   * @return bool
   */
  private function CheckTextGenerate()
    {
    if(!isset($_GET['filename']))
      {
      CLogger::write(CLoggerType::ERROR, self::LOG_PREFIX . ' no get params "filename"');
      return false;
      }
    //---
    if(!isset($_GET['settings']))
      {
      CLogger::write(CLoggerType::ERROR, self::LOG_PREFIX . ' no get params "settings"');
      return false;
      }
    //---
    return true;
    }

  /**
   * Генерация текста из файла
   *
   * @param array $get
   * @return null|string
   */
  public function TextGenerate($get)
    {
    if(empty($get)) return '';
    //---
    if(!$this->CheckTextGenerate()) return null;
    //---
    $filename      = $get['filename'];
    $settings_name = $get['settings'];
    //---
    $settings       = new CModel_settings();
    $this->m_params = $settings->Load($settings_name);
    if(empty($this->m_params))
      {
      CLogger::write(CLoggerType::ERROR, self::LOG_PREFIX . ' not load settings: ' . $settings_name);
      return null;
      }
    //---
    $this->m_params['textFrom']     = 'list';
    $this->m_params['textFromList'] = $filename;
    $this->m_params['is_cache']     = 1;
    //---
    if(CModel_helper::IsExistHttp($this->m_params['nextUrl'])) $this->m_params['clearnextUrl'] = substr($this->m_params['nextUrl'], 7);
    else $this->m_params['clearnextUrl'] = $this->m_params['nextUrl'];
    //---
    $this->m_model_text     = new CModel_text();
    $this->m_model_keywords = new CModel_keywords();
    $this->m_model_links    = new CModel_links();
    $this->m_model_images   = new CModel_imageslinks();
    //--- активируем плагины
    $this->m_model_plugins = new CModel_plugins();
    $this->m_model_plugins->ActivateAll();
    //--- надо ли добавлять ссылки из разных файлов и добавим нужные параметры
    $this->GetLinksParams($get);
    //--- нужно ли в текс вставлять кейворд
    $need_keyword = !(isset($_REQUEST['nokeyword']) && $_REQUEST['nokeyword'] == 1);
    //--- если пустой кейворд
    if(empty($get['keyword']))
      {
      //--- если заданные links
      if(isset($get['links']))
        {
        $this->LoadLinksParams($get);
        $files_url    = $this->LoadUrlsFile($get);
        $files_anchor = $this->LoadAnchorsFile($get);
        //---
        $this->m_model_keywords->InitAnchorUrls($this->m_params, $this->m_model_plugins, $files_url, $files_anchor);
        //--- инцилизация текста
        $this->m_model_text->Init($this->m_params, $this->m_model_keywords, $this->m_model_plugins, $this->m_model_links, $this->m_model_images, false);
        $key_info = $this->m_model_keywords->GetRandKeyword($n);
        if(empty($key_info)) return null;
        $this->m_model_text->setCurrentKeyword($key_info);
        //---
        CLogger::write(CLoggerType::DEBUG, self::LOG_PREFIX . ' set keyword: ' . $key_info->getKeyword());
        //---
        return $this->m_model_text->GetText(array(0,
                                                  (int)$get['from'],
                                                  (int)$get['to']), $need_keyword, !isset($get["noparagraph"]));
        }
      else
        {
        //--- links не задан, то просто все из параметров
        $this->m_model_keywords->Init($this->m_params, $this->m_model_plugins);
        $key_info = $this->m_model_keywords->GetRandKeyword($n);
        if(empty($key_info)) return null;
        //---
        $this->m_model_text->Init($this->m_params, $this->m_model_keywords, $this->m_model_plugins, $this->m_model_links, $this->m_model_images, false);
        //$key_info = new CKeywordInfo(0, $key_info->getKeywordRand(), $key_info->$m_list_key, '');
        $this->m_model_text->setCurrentKeyword($key_info);
        return $this->m_model_text->GetTextClearNoKey(array(0,
                                                            (int)$get['from'],
                                                            (int)$get['to']), $need_keyword);
        }
      }
    else
      {
      //--- с ключевыми словами надо определиться сначала.
      $keys = $this->GetKeywords($get['keyword']);
      //---
      if(isset($get['links']))
        {
        $this->LoadLinksParams($get);
        $files_url    = $this->LoadUrlsFile($get);
        $files_anchor = $this->LoadAnchorsFile($get);
        //---
        $this->m_model_keywords->InitAnchorUrls($this->m_params, $this->m_model_plugins, $files_url, $files_anchor);
        }
      //--- функция GetKeywords - меняет $this->m_params
      $this->m_model_text->Init($this->m_params, $this->m_model_keywords, $this->m_model_plugins, $this->m_model_links, $this->m_model_images, false);
      $result = '';
      //---
      foreach($keys as $k)
        {
        if(is_array($k)) $key_info = new CKeywordInfo(0, empty($k[0]) ? '' : $k[0], $k, '');
        else
        $key_info = new CKeywordInfo(0, $k, array($k), '');
        $this->m_model_text->setCurrentKeyword($key_info);
        if(!empty($result)) $result .= self::DELIMITER_TEXT;
        //---
        $result .= $this->m_model_text->GetText(array(0,
                                                      (int)$get['from'],
                                                      (int)$get['to']), $need_keyword, !isset($get["noparagraph"]));
        }
      return $result;
      }
    return '';
    }

  /**
   * Список файлов с линками
   * @param $get
   */
  private function LoadLinksParams($get)
    {
    $links                          = trim($get['links'], '[]');
    $l                              = explode('-', $links, 2);
    $this->m_params['linksDorFrom'] = $l[0];
    $this->m_params['linksDorTo']   = $l[1];
    //--- ссылки на ссылки доргена. Т.к. все данные загружены как кейворды
    $this->m_params['linksDor'] = 'on';
    //--- сколько ссылок нужно без анкора
    if(!empty($get['noanchor']))
      {
      $noanchor = trim($get['noanchor'], '[]');
      $l        = explode('-', $noanchor, 2);
      if(count($l) < 2) $l[1] = (int)$l[0];
      $this->m_params['linksNoanchor'] = rand((int)$l[0], (int)$l[1]);
      }
    //--- обработаем нужны ли картинки
    if(!empty($get['images']))
      {
      if(preg_match_all('/\[([0-9]{1,})\-([0-9]{1,})\-([a-z0-9\-\/]{1,64}\.[a-z0-9]{1,6})\]/', $get['images'], $matches, PREG_SET_ORDER))
        {
        foreach($matches as $match)
          {
          $this->m_params['imagesFromFile']     = 'on';
          $this->m_params['imagesFromFileFrom'] = $match[1];
          $this->m_params['imagesFromFileTo']   = $match[2];
          $this->m_params['imagesFromFileName'] = $match[3];
          break;
          }
        }
      //--- ссылки на картинки
      if(isset($get['images_urls']))
        {
        $this->m_params['imagesUrlsFromFile'] = trim($get['images_urls'], '[]');
        }
      if(isset($get['images_intext']) && $get['images_intext'] == '1') $this->m_params['imagesInText'] = 'on';
      //--- где должна быть картинка в тексте
      if(isset($get['images_where']))
        {
        $where       = trim($get['images_where'], '[]');
        $ar          = explode('|', $where);
        $where_value = 0;
        foreach($ar as $v)
          {
          switch($v)
          {
            case 'rand':
              $where_value |= CModel_imageslinks::RAND;
              break;
            case 'up':
              $where_value |= CModel_imageslinks::TEXT_BEFORE;
              break;
            case 'down':
              $where_value |= CModel_imageslinks::TEXT_AFTER;
              break;
            case 'center':
              $where_value |= CModel_imageslinks::TEXT_CENTER;
              break;
          }
          }
        $this->m_params['imagesWhere'] = $where_value;
        }
      //--- align у картинки
      if(isset($get['images_pos']))
        {
        $where       = trim($get['images_pos'], '[]');
        $ar          = explode('|', $where);
        $where_value = 0;
        foreach($ar as $v)
          {
          switch($v)
          {
            case 'rand':
              $where_value |= CModel_imageslinks::RAND;
              break;
            case 'left':
              $where_value |= CModel_imageslinks::ALIGN_LEFT;
              break;
            case 'right':
              $where_value |= CModel_imageslinks::ALIGN_RIGHT;
              break;
            case 'center':
              $where_value |= CModel_imageslinks::ALIGN_CENTER;
              break;
          }
          }
        $this->m_params['imagesAlign'] = $where_value;
        }
      //--- обработаем нужны ли случайные строчки
      if(!empty($get['randlines']))
        {
        $this->m_params['randLines'] = 'on';
        if(preg_match_all('/\[([0-9]{1,})\-([0-9]{1,})\-([a-z0-9\-\/]{1,64}\.[a-z0-9]{1,6})\]/', $get['randlines'], $matches, PREG_SET_ORDER))
          {
          foreach($matches as $match)
            {
            $this->m_params['randLinesFrom']         = 'list';
            $this->m_params['randLinesFromFileFrom'] = $match[1];
            $this->m_params['randLinesFromFileTo']   = $match[2];
            $this->m_params['randLinesFromList']     = $match[3];
            break;
            }
          }
        }
      }
    }

  /**
   * список файлов с урлами
   * @param $get
   * @return array
   */
  private function LoadUrlsFile($get)
    {
    if(empty($get['urls'])) return array();
//---
    $files_str = trim($get['urls'], '[]');
    return explode("|", $files_str);
    }

  /**
   * список файлов с анкорами
   * @param $get
   * @return array
   */
  private function LoadAnchorsFile($get)
    {
    if(empty($get['anchors'])) return array();
//---
    $files_str = trim($get['anchors'], '[]');
    return explode("|", $files_str);
    }

  /**
   * Данные о ссылках
   * @param $get
   */
  private function GetLinksParams($get)
    {
    if(empty($get['links'])) return;
//---
    if(preg_match_all('/\[([0-9]{1,})\-([0-9]{1,})\-([a-z0-9\-\/]{1,64}\.[a-z0-9]{1,6})\]/', $get['links'], $matches, PREG_SET_ORDER))
      {
      foreach($matches as $match)
        {
        $this->m_params['linksFromFile']     = 'on';
        $this->m_params['linksFromFileFrom'] = $match[1];
        $this->m_params['linksFromFileTo']   = $match[2];
        $this->m_params["linksFromFileName"] = $match[3];
        return;
        }
      }
    }

  /**
   * сколько раз добавить один кейворд
   * @param $count
   * @param $keys
   */
  private function AddRandomKey($count, &$keys)
    {
    $count_keys = count($keys[0]);
    if($count_keys <= 0) return;
    //---
    $r   = rand(0, $count_keys - 1);
    $key = $keys[0][$r];
    //---
    for($i = 0; $i < $count; $i++) array_splice($keys[0], rand(0, $count_keys - 1), 0, $key);
    }

  /**
   * 1) keyword=[3] - в текст вставляются 3 ключевика из файла со словами в настройках
   * 2) keyword=[3-5] - в текст вставляются случайно между 3 и 5 ключевика из файла со словами в настройках
   * 3) keyword=[3-5-keyword.txt] - в текст вставляются случайно между 3 и 5 ключевика из указанного файла
   * 4) keyword=[3-5-keyword.txt]|[3-5-keyword1.txt]|[3-5-keyword2.txt] - генерируются 3 блока текстов в которые вставляются случайно между 3
   *
   * @param $keys
   *
   * @return array
   */
  private function GetKeywords($keys)
    {
    //--- если начинается с [ значит дальше нужно разруливать все регулярными выражениями
    if($keys[0] == '[')
      {
      $add_key = 0;
      //--- сколько еще раз нужно добавить один кейворд
      if(isset($_REQUEST['keyadd']) && $_REQUEST['keyadd'] > 0)
        {
        $add_key = (int)$_REQUEST['keyadd'];
        }
//---
      $matches = array();
      //--- [3] - в текст вставляются 3 ключевика из файла со словами в настройках
      if(preg_match('/\[([0-9]{1,})\]/i', $keys, $matches))
        {
        $this->m_params['keysDensityMin'] = $matches[1] + $add_key;
        $this->m_params['keysDensityMax'] = $matches[1] + $add_key;
        $this->m_params["keysRandomMin"]  = $matches[1] + $add_key;
        $this->m_params["keysRandomMax"]  = $matches[1] + $add_key;
        $keys                             = array($this->GetKeywordsRand($matches[1]));
        //--- если нужно будет добавить
        $this->AddRandomKey($add_key, $keys);
        return $keys;
        }
      if(preg_match('/\[([0-9]{1,})\-([0-9]{1,})\]/i', $keys, $matches))
        {
        $this->m_params['keysDensityMin'] = $matches[1] + $add_key;
        $this->m_params['keysDensityMax'] = $matches[2] + $add_key;
        $this->m_params["keysRandomMin"]  = $matches[1] + $add_key;
        $this->m_params["keysRandomMax"]  = $matches[2] + $add_key;
        $keys                             = array($this->GetKeywordsRandBetween($matches[1], $matches[2]));
        //--- если нужно будет добавить
        $this->AddRandomKey($add_key, $keys);
        return $keys;
        }
      if(preg_match_all('/\[([0-9]{1,})\-([0-9]{1,})\-([a-z0-9\-\/]{1,64}\.[a-z0-9]{1,6})\]/', $keys, $matches, PREG_SET_ORDER))
        {
        $res = array();
        foreach($matches as $match)
          {
          $this->m_params['keysDensityMin'] = $match[1] + $add_key;
          $this->m_params['keysDensityMax'] = $match[2] + $add_key;
          $this->m_params["keysRandomMin"]  = $match[1] + $add_key;
          $this->m_params["keysRandomMax"]  = $match[2] + $add_key;
          //---
          $res[] = $this->GetKeywordsFromFile($match[2], $match[3]);
          }
        //--- если нужно будет добавить
        $this->AddRandomKey($add_key, $res);
        return $res;
        }
      }
    //--- тут все просто, разделиль |
    return explode('|', $keys);
    }

  /**
   * Получение случайных ключевиков
   *
   * @param $count
   *
   * @return array
   */
  private function GetKeywordsRand($count)
    {
    $res = array();
    $this->m_model_keywords->Init($this->m_params, $this->m_model_plugins);
    for($i = 0; $i < $count; $i++)
      {
      $n        = 0;
      $key_info = $this->m_model_keywords->GetRandKeyword($n);
      if(empty($key_info)) continue;
      $res[] = $key_info->getKeywordRand();
      }
    return $res;
    }

  /**
   * Получение случайных ключевиков
   *
   * @param $from
   * @param $to
   * @internal param $count
   *
   * @return array
   */
  private function GetKeywordsRandBetween($from, $to)
    {
    return $this->GetKeywordsRand($to);
    }

  /**
   * Получение кейвород из файла
   * @param $to
   * @param $filename
   * @return array
   */
  private function GetKeywordsFromFile($to, $filename)
    {
    $this->m_params['keysFrom']     = 'list';
    $this->m_params['keysFromList'] = $filename;
    $this->m_model_keywords->Init($this->m_params, $this->m_model_plugins);
    $res = array();
    for($i = 0; $i < $to; $i++)
      {
      $n        = 0;
      $key_info = $this->m_model_keywords->GetRandKeyword($n);
      if(empty($key_info)) continue;
      $res[] = $key_info->getKeywordRand();
      }
    return $res;
    }

  /**
   * ПОлучение случайно строки из указанного файла и если нужно разделеление на параграфы
   * @param $get
   * @return string
   */
  public function GetRandline($get)
    {
    $min                = 0;
    $max                = 0;
    $this->m_model_text = new CModel_text();
    if(empty($get['filename'])) return '';
    if(isset($get['paragraph']))
      {
      if(preg_match('/\[([0-9]{1,})\-([0-9]{1,})\]/i', $get['paragraph'], $matches))
        {
        $min = $matches[1];
        $max = $matches[2];
        }
      }
//---
    $data = array(1 => str_replace(array('/',
                                         '\\'), '', $get['filename']));
    $line = $this->m_model_text->GetRandLineFile($data);
    if($min > 0 && $max > 0)
      {
      $line = $this->m_model_text->GetParagraphText($line, (int)$min, (int)$max);
      }
    echo $line;
    }

  /**
   * Список ключей
   * @param $get
   *
   * @return string
   */
  public function GetRandKeywords($get)
    {
    $time            = microtime(true);
    $settings_name   = $get['settings'];
    $settings        = new CModel_settings();
    $is_alpha_big    = isset($get['aplhabig']) ? (int)$get['aplhabig'] : 0;
    $is_paragraphtag = isset($get['paragraphtag']) ? (int)$get['paragraphtag'] : 0;
    //--- по порядку ключевые слова брать
    $is_next_links = isset($get['next_links']) ? (int)$get['next_links'] : 0;
    //---
    $this->m_params                 = $settings->Load($settings_name);
    $this->m_params['is_cache']     = 1;
    $this->m_params['clearnextUrl'] = '';
    //--- для ключевиков урлы не нужны
    $this->m_params['no_urls_keyword'] = 1;
    //if($this->m_params['keysRandomMax'] < 1000000) $this->m_params['keysRandomMax'] = 1000000;
    //--- настройка расцветки
    $this->SetFontsColors();
    $this->SettingsSentences();
    $this->LoadKeywodsSelectionParams();
    //---
    $this->m_model_keywords = new CModel_keywords();
    $this->m_model_links    = new CModel_links();
    $this->m_model_plugins  = new CModel_plugins();
    //--- получаем кейворды
    $file_keywords  = '';
    $keywords_count = 0;
    if(!empty($get['keyword']))
      {
      if(preg_match_all('/\[([0-9]{1,})\-([0-9]{1,})\-([\w]{1,64}\.[a-z0-9]{1,6})\]/', $get['keyword'], $matches, PREG_SET_ORDER))
        {
        $fromKeywordsCount              = (int)$matches[0][1];
        $toKeywordsCount                = (int)$matches[0][2];
        $file_keywords                  = $matches[0][3];
        $this->m_params['keysFrom']     = 'list';
        $this->m_params['keysFromList'] = $file_keywords;
        $keywords_count                 = rand($fromKeywordsCount, $toKeywordsCount);
        }
      }
//--- загружаем ключи
    $this->m_model_keywords->Init($this->m_params, $this->m_model_plugins);
    //--- ссылки
    $urls          = array();
    $fromUrlsCount = 0;
    $toUrlsCount   = 0;
    $file_urls     = '';
    $need_urls     = false;
    //--- отдельно урлы и ссылки
    $need_urls_anchors = false;
    if(!empty($get['links']))
      {
      if(preg_match_all('/\[([0-9]{1,})\-([0-9]{1,})\-([a-z0-9\-\/]{1,64}\.[a-z0-9]{1,6})\]/', $get['links'], $matches, PREG_SET_ORDER))
        {
        foreach($matches as $match)
          {
          $file_urls     = $match[3];
          $fromUrlsCount = (int)$match[1];
          $toUrlsCount   = (int)$match[2];
          $need_urls     = true;
          break;
          }
        }
      }
    // урлы отдельно, ссылки отдельно
    if(!empty($get['linksurls']))
      {
      if(preg_match_all('/\[([0-9]{1,})\-([0-9]{1,})]/', $get['linksurls'], $matches, PREG_SET_ORDER))
        {
        $anchor = '';
        //--- получаем файлы с анкорами
        if(isset($get['anchors']))
          {
          $ar = explode('|', trim($get['anchors'], '[]'));
          foreach($ar as $u)
            {
            $anchors[] = trim(trim($u), '[]');
            }
          $anchor = $anchors[array_rand($anchors)];
          }
        //--- получаем файл с урлами
        $url_file = array();
        if(isset($get['urls']))
          {
          $ar = explode('|', trim($get['urls'], '[]'));
          foreach($ar as $u)
            {
            $urls[] = trim(trim($u), '[]');
            }
          $url_file = $urls[array_rand($urls)];
          }
        //--- получаем сколько нужно ссылок
        $fromUrlsCount = (int)$matches[0][1];
        $toUrlsCount   = (int)$matches[0][2];
        $fromNoAnchors = 0;
        $toNoAnchors   = 0;
        //--- получаем сколько нужно без анкорных
        if(preg_match_all('/\[([0-9]{1,})\-([0-9]{1,})]/', $get['noanchor'], $matches, PREG_SET_ORDER))
          {
          $fromNoAnchors = (int)$matches[0][1];
          $toNoAnchors   = (int)$matches[0][2];
          }
        $need_urls_anchors = true;
        }
      }
    $urls_count = 0;
    //--- урлы
    if($need_urls)
      {
      if($is_next_links) $urls = $this->m_model_links->GetNextLinksFromFile(null, $file_urls, rand($fromUrlsCount, $toUrlsCount));
      else
      $urls = $this->m_model_links->GetRandLinksFromFile(null, $file_urls, rand($fromUrlsCount, $toUrlsCount));
      $urls_count = count($urls);
      $need_urls  = $urls_count > 0;
      }
    //--- отдельно ссылки, отдельно урлы и вместе получаются ссылки
    elseif($need_urls_anchors)
      {
      $urls       = $this->m_model_links->GetRandLinksFromUrlAnchors($anchor, $url_file, rand($fromUrlsCount, $toUrlsCount), rand($fromNoAnchors, $toNoAnchors));
      $urls_count = count($urls);
      $need_urls  = $urls_count > 0;
      }
    //--- разделитель между словами
    $delimiter = !empty($get['delimiter']) ? $get['delimiter'] : ',';
    $dl        = explode('|', $delimiter);
    if(count($dl) > 1) $delimiter = $dl[rand(0, count($dl) - 1)];
    //---
    $text = '';
    //---
    $count_sentences  = 1;
    $count_paragraphs = 1;
    if($is_alpha_big)
      {
      if(!empty($get['sentences']))
        {
        if(preg_match_all('/\[([0-9]{1,})\-([0-9]{1,})\]/', $get['sentences'], $matches, PREG_SET_ORDER))
          {
          $count_sentences = rand((int)$matches[0][1], (int)$matches[0][2]);
          }
        if(isset($get['paragraphs']))
          {
          if(preg_match_all('/\[([0-9]{1,})\-([0-9]{1,})\]/', $get['paragraphs'], $matches, PREG_SET_ORDER))
            {
            $count_paragraphs = rand((int)$matches[0][1], (int)$matches[0][2]);
            }
          }
        }
      }
    //--- провери на мунус
    if($count_sentences <= 0) $count_sentences = 1;
    if($count_paragraphs <= 0) $count_paragraphs = 0;
    //--- сколько уже параграфов
    $counts_pr = array();
    if($count_paragraphs > 0)
      {
      if($count_sentences < $count_paragraphs) $count_paragraphs = $count_sentences;
      //--- количество предложений в одном параграфе
      $paragraph_sentense = ceil((float)$count_sentences / $count_paragraphs);
      //--- для каждого параграфа расчитаем количество предложений
      $all_senences = $count_sentences;
      for($p = 0; $p < $count_paragraphs; $p++)
        {
        if(($p + 1) == $count_paragraphs) $counts_pr[$p] = $all_senences > 0 ? $all_senences : 1;
        else
        $counts_pr[$p] = rand(1, $paragraph_sentense);
        $all_senences -= $counts_pr[$p];
        }
      }
    else
      {
      $counts_pr[0] = 1;
      }
    //--- сколько ключевых слов надо выделять
    if($this->m_keys_color_type == 'percent')
      {
//--- выделение ключевиков в процентах
      $keyColorPercent          = rand($this->m_keys_min_color_percent, $this->m_keys_max_color_percent);
      $keywordsNeedColoredCount = round($keywords_count * $keyColorPercent / 100);
      }
    else
      {
      $keywordsNeedColoredCount = rand($this->m_keys_min_color, $this->m_keys_max_color);
      }
    //--- сколько ключевых слов надо шрифтом
    if($this->m_keys_font_type == 'percent')
      {
//--- выделение ключевиков в процентах
      $keyColorPercent        = rand($this->m_keys_min_font_percent, $this->m_keys_max_font_percent);
      $keywordsNeedFontsCount = round($keywords_count * $keyColorPercent / 100);
      }
    else
      {
      $keywordsNeedFontsCount = rand($this->m_keys_min_font, $this->m_keys_max_font);
      }
    //--- нужно ли выделять кейворды тэгами
    $isNeedKeySelect = count($this->m_tags_keywords_selection) > 0 && $this->m_density_keywords_selection[1] > 0;
    //--- сколько процентов ключевых слов надо выделять
    $keywordsSelectionPercent = rand($this->m_density_keywords_selection[0], $this->m_density_keywords_selection[1]);
    $count_tags               = count($this->m_tags_keywords_selection);
    //--- сколько нужно выделить предложений
    $sentenceNeedColoredCount = rand($this->m_sentence_min_color, $this->m_sentence_max_color);
    //--- сколько предложений уже разукрашено
    $countSentencesColored = 0;
    //--- сколько слов уже разукрашено
    $countKeywordsColored = 0;
    //--- у скольки слов уже поменяли шрифт
    $countKeywordsFonts = 0;
    //--- у скольки слов уже разукрашены фоны
    $countKeywordsBackColored = 0;
    //--- нужно расчитать сколько ключевиков в каждом предложении
    $keywords_sentences     = ceil((float)$keywords_count / $count_sentences);
    $all_keywords_sentences = $keywords_count;
    $all_urls               = $urls_count;
    //---
    $keyword_i = 0;
    for($parg = 0; $parg < $count_paragraphs; $parg++)
      {
      //---
      $text_paragraph = '';
      $text_parag     = '';
      for($count = 0; $count < $counts_pr[$parg]; $count++)
        {
        $j = 0;
        //---
        if(($parg + 1) == $count_paragraphs && ($count + 1) == $counts_pr[$parg] && $all_keywords_sentences > 1) $current_keyords_count = $all_keywords_sentences;
        else
        $current_keyords_count = rand(max($keywords_sentences / 2, 1) + 1, $keywords_sentences);
        //---
        $keywords = $this->m_model_keywords->GetRandKeywordsListFromFile($current_keyords_count, $file_keywords);
        $all_keywords_sentences -= $current_keyords_count;
        $i = 0;
        if(!empty($text) && $count > 0) $text .= ' ';
        //--- получаем количество из файла
        foreach($keywords as $key)
          {
          $openedTag = '';
          $closedTag = '';
//---
          $keyword_i++;
          //--- нужно ли выделять цветом текущий ключ
          $isColorKey = $this->IsColorKeyword($countKeywordsColored, $keyword_i, $keywords_count, $keywordsNeedColoredCount);
          //--- нужно ли выделять фон у ключевика
          $isColorBackground = $this->IsColorBackgroundKeyword($countKeywordsBackColored, $keyword_i, $keywords_count, $keywordsNeedColoredCount);
          //--- нужно ли менять шрифты
          $isChangeFont = $this->IsChangeFontsKeyword($countKeywordsFonts, $keyword_i, $keywords_count, $keywordsNeedFontsCount);
          $k            = $key;
          //---
          if($i == 0 && $is_alpha_big)
            {
            $k = CModel_helper::GetUcFirst($k);
            }
          if($isNeedKeySelect && (rand(0, 100) < $keywordsSelectionPercent))
            {
            $tag = $this->m_tags_keywords_selection[rand(0, $count_tags - 1)];
            //---
            $openedTag = "<{$tag}>";
            $closedTag = "</{$tag}>";
            }
          //--- выделяем цветом
          if($isColorKey || $isColorBackground || $isChangeFont)
            {
            $this->GetStyleColorsKeyword($openedTag, $closedTag, $isColorKey, $isColorBackground, $isChangeFont);
            $k = ($i > 0 || $parg != 0 ? ' ' : '') . $openedTag . $k . $closedTag;
            }
          //--- если нужно только выделить жирностью
          else if($isNeedKeySelect && !empty($openedTag))
            {
            $k = ($i > 0 || $parg != 0 ? ' ' : '') . $openedTag . $k . $closedTag;
            }
          //---
          if($i > 0)
            {
            if(count($dl) > 1) $delimiter = $dl[rand(0, count($dl) - 1)];
            $text_parag .= $delimiter;
            }
          if($need_urls && $all_urls > 0 &&$i>0&& $j == 0 && rand(0, $count_paragraphs - 1) == $parg)
            {
            $rand = rand(0, $urls_count - 1);
              $rand_url_gen = (is_array($urls[$rand]) && !empty($urls[$rand]['url']) ? $urls[$rand]['url'] : $urls[$rand]);
              $rand_anchor_gen = (!empty($urls[$rand]['key']) ? $urls[$rand]['key'] :$rand_url_gen);
              $rand_link_gen = '<a href="' . $rand_url_gen . '">' . $rand_anchor_gen . '</a>';
              //---
            $text_parag .= ($i > 0 || $parg != 0 ? ' ' : '') . $rand_link_gen.' '.$k;
            $all_urls--;
            $j++;
            }
          else
          $text_parag .= ($i > 0 || $parg != 0 ? ' ' : '') . $k;
          $i++;
          }
        //---
        if($is_alpha_big)
          {
          $isSentenceSelect = $this->IsColorSentence($countSentencesColored, $keyword_i, $keywords_count, $sentenceNeedColoredCount);
          if($isSentenceSelect)
            {
            $style = $this->GetSentenceStyleSelect();
            $tag   = 'span';
            //--- выделяем стилями предложение, в которое вставили ключевое слово
            $text_parag = ' <' . $tag . ' style="' . $style . '">' . trim($text_parag) . '.</' . $tag . '>';
            }
          $text_paragraph .= $text_parag . '.';
          }
        else
        $text_paragraph .= $text_parag;
        //--- Зачистка
        $text_parag = '';
        }
      //--- что будем вставлять
      if($is_paragraphtag) $text .= "<p>" . $text_paragraph . "</p>";
      else $text .= $text_paragraph;
      //---
      if(!empty($text) && $count_paragraphs > 1 && ($parg + 1) != $count_paragraphs)
        {
        $text = trim($text) . "\r\n";
        }
      }
    $time = microtime(true) - $time;
    CLogger::write(CLoggerType::DEBUG, self::LOG_PREFIX . 'api: keywords generated ' . strlen($text) . ' bytes, ' . (int)($time * 1000) . ' ms');
    return $text;
    }

  /**
   * стили для разукрашивания предложения
   */
  private function GetSentenceStyleSelect()
    {
    $style = '';
    //--- цвет предложения
    if($this->m_sentence_color_rand || !empty($this->m_sentence_colors)) $style .= 'color:' . $this->GetSentenceColor() . ';';
    //--- цвет фона предложения
    if($this->m_sentence_color_background_rand || !empty($this->m_sentence_background_colors))
      {
      $style = rand(0, 1) ? ($style . 'background-color:' . $this->GetBackgroundSentenceColor() . ';') : ('background-color:' . $this->GetBackgroundKeywordColor() . ';' . $style);
      }
    return $style;
    }

  /**
   * ПОлучаем случайный цвет для бекграунда
   * @return string
   */
  private function GetBackgroundSentenceColor()
    {
    if($this->m_sentence_color_background_rand || empty($this->m_sentence_background_colors))
      {
      return '#' . dechex(rand(0, 255)) . dechex(rand(0, 255)) . dechex(rand(0, 255));
      }
    $rand_keys = array_rand($this->m_sentence_background_colors);
    return $this->m_sentence_background_colors[$rand_keys];
    }

  /**
   * ПОлучаем случайный цвет для бекграунда
   * @return string
   */
  private function GetSentenceColor()
    {
    if($this->m_sentence_color_rand || empty($this->m_sentence_colors))
      {
      return '#' . dechex(rand(0, 255)) . dechex(rand(0, 255)) . dechex(rand(0, 255));
      }
    $rand_keys = array_rand($this->m_sentence_colors);
    return $this->m_sentence_colors[$rand_keys];
    }

  /**
   * Нужно ли текущее предложение выделять цветом
   *
   * @param int $countSentencesColored сколько уже раскрашено предложений
   * @param int $current номер текущего ключа
   * @param int $keywordsCount сколько всего ключей будет вставлять в текст
   * @param int $sentenceNeedColoredCount сколько всего нужно раскрасить предложений
   *
   * @return bool
   */
  private function IsColorSentence(&$countSentencesColored, $current, $keywordsCount, $sentenceNeedColoredCount)
    {
    //--- ничего не нужно выделять то и не выделяем
    if(!$this->m_is_sentence_color) return false;
    //--- если не нужно выделять
    if(!$this->m_sentence_color_rand && empty($this->m_sentence_colors) && !$this->m_sentence_color_background_rand && empty($this->m_sentence_background_colors)) return false;
    //--- может уже все раскрасили
    if($countSentencesColored >= $sentenceNeedColoredCount) return false;
    //--- если осталось ключевиков, столько же сколько нужно выделить, то выделяем все
    $needInsertKey = $keywordsCount - $current - 1;
    $needColorKey  = $sentenceNeedColoredCount - $countSentencesColored;
    if($needColorKey > $needInsertKey)
      {
      $countSentencesColored++;
      return true;
      }
    //---
    $is_color = rand(0, 1);
    if($is_color)
      {
      $countSentencesColored++;
      return true;
      }
    return false;
    }

  /**
   * В открывающийся тег добавим стили заграшивания
   * @param $openedTag
   * @param $closedTag
   * @param $isColorKey
   * @param $isColorBackground
   *
   * @param $isChangeFont
   */
  private function GetStyleColorsKeyword(&$openedTag, &$closedTag, $isColorKey, $isColorBackground, $isChangeFont)
    {
    if(empty($openedTag))
      {
      $openedTag = "<span>";
      $closedTag = "</span>";
      }
    //---
    $style = '';
    if($isColorKey) $style .= 'color:' . $this->GetKeywordColor() . ';';
    if($isColorBackground) $style = rand(0, 1) ? ($style . 'background-color:' . $this->GetBackgroundKeywordColor() . ';') : ('background-color:' . $this->GetBackgroundKeywordColor() . ';' . $style);
    if($isChangeFont) $style = (rand(0, 1) ? ($style . $this->GetFontsStyles()) : $this->GetFontsStyles() . $style);
    //--- все делаем вставку стилей
    $openedTag = str_replace('>', ' style="' . $style . '">', $openedTag);
    }

  /**
   * Получение стилей для выделение шрифта
   */
  private function GetFontsStyles()
    {
    $styles = '';
    if(!empty($this->m_key_fonts))
      {
      $rand_keys = array_rand($this->m_key_fonts);
      $styles .= "font-family:" . $this->m_key_fonts[$rand_keys] . ';';
      }
    //---
    $size   = rand($this->m_keys_min_fontsize, $this->m_keys_max_fontsize);
    $styles = rand(0, 1) ? ("font-size:" . $size . 'px;' . $styles) : ($styles . "font-size:" . $size . 'px;');
    return $styles;
    }

  /**
   * ПОлучаем случайный цвет для бекграунда
   * @return string
   */
  private function GetBackgroundKeywordColor()
    {
    if($this->m_key_color_background_rand || empty($this->m_key_background_colors))
      {
      return '#' . dechex(rand(0, 255)) . dechex(rand(0, 255)) . dechex(rand(0, 255));
      }
    $rand_keys = array_rand($this->m_key_background_colors);
    return $this->m_key_background_colors[$rand_keys];
    }

  /**
   * ПОлучаем цвет для выделения ключа
   * @return string
   */
  private function GetKeywordColor()
    {
    if($this->m_key_color_rand || empty($this->m_key_colors))
      {
      return '#' . dechex(rand(0, 255)) . dechex(rand(0, 255)) . dechex(rand(0, 255));
      }
    $rand_keys = array_rand($this->m_key_colors);
    return $this->m_key_colors[$rand_keys];
    }

  /**
   * Нужно ли текущий кейворд выделять шрифтом
   * @param int $countKeywordsFonts сколько уже раскрашено ключей
   * @param int $current номер текущего ключа
   * @param int $keywordsCount сколько всего ключей будет вставлять в текст
   * @param int $keywordsNeedFontsCount сколько всего нужно раскрасить ключей
   *
   * @return bool
   */
  private function IsChangeFontsKeyword(&$countKeywordsFonts, $current, $keywordsCount, $keywordsNeedFontsCount)
    {
    //--- ничего не нужно выделять то и не выделяем
    if(!$this->m_is_keyword_font) return false;
    //--- может уже все раскрасили
    if($countKeywordsFonts >= $keywordsNeedFontsCount) return false;
    //--- если осталось ключевиков, столько же сколько нужно выделить, то выделяем все
    $needInsertKey = $keywordsCount - $current - 1;
    $needColorKey  = $keywordsNeedFontsCount - $countKeywordsFonts;
    if($needColorKey > $needInsertKey)
      {
      $countKeywordsFonts++;
      return true;
      }
    //---
    $is_color = rand(0, $keywordsNeedFontsCount);
    if($is_color == 0)
      {
      $countKeywordsFonts++;
      return true;
      }
    return false;
    }

  /**
   * Нужно ли текущий кейворд выделять цветом
   * @param int $countBackgroundColored сколько уже раскрашено фонов у ключей
   * @param int $current номер текущего ключа
   * @param int $keywordsCount сколько всего ключей будет вставлять в текст
   * @param int $keywordsNeedColoredCount сколько всего нужно раскрасить ключей
   *
   * @return bool
   */
  private function IsColorBackgroundKeyword(&$countBackgroundColored, $current, $keywordsCount, $keywordsNeedColoredCount)
    {
    //--- ничего не нужно выделять то и не выделяем
    if(!$this->m_is_keyword_color) return false;
    if(!$this->m_key_color_background_rand && empty($this->m_key_background_colors)) return false;
    //--- может уже все раскрасили
    if($countBackgroundColored >= $keywordsNeedColoredCount) return false;
    //--- если осталось ключевиков, столько же сколько нужно выделить, то выделяем все
    $needInsertKey = $keywordsCount - $current - 1;
    $needColorKey  = $keywordsNeedColoredCount - $countBackgroundColored;
    if($needColorKey > $needInsertKey)
      {
      $countBackgroundColored++;
      return true;
      }
    //---
    $is_color = rand(0, $keywordsNeedColoredCount);
    if($is_color == 0)
      {
      $countBackgroundColored++;
      return true;
      }
    return false;
    }

  /**
   * Задаем настройки для управлением цветом предложений
   */
  private function SettingsSentences()
    {
    //--- управление шрифтом
    if(!empty($this->m_params['sentenceSelect']) && $this->m_params['sentenceSelect'] == 'on')
      {
      //--- нужно выделять
      $this->m_is_sentence_color = true;
      //--- в количественном соотношении выделяемых ключей
      $this->m_sentence_min_color = $this->m_params['sentencesMin'];
      $this->m_sentence_max_color = $this->m_params['sentencesMax'];
      //--- рандом
      $this->m_sentence_color_rand            = isset($this->m_params['colorSentenceRandom']) && $this->m_params['colorSentenceRandom'] == 'on';
      $this->m_sentence_color_background_rand = isset($this->m_params['colorBackgroundSentenceRandom']) && $this->m_params['colorBackgroundSentenceRandom'] == 'on';
      $this->m_sentence_strong                = isset($this->m_params['strongSentence']) && $this->m_params['strongSentence'] == 'on';
      //--- списки цветов
      if(!empty($this->m_params['colorSentenceSet'])) $this->m_sentence_colors = $this->GetArrayColors($this->m_params['colorSentenceSet']);
      if(!empty($this->m_params['colorBackSentenceSet'])) $this->m_sentence_background_colors = $this->GetArrayColors($this->m_params['colorBackSentenceSet']);
      }
    }

  /**
   * Установка настроек для управлением цветов ключевиков
   */
  private function SetFontsColors()
    {
    //--- управление цветом
    if(!empty($this->m_params['keywordsColored']) && $this->m_params['keywordsColored'] == 'on')
      {
      //--- нужно выделять
      $this->m_is_keyword_color          = true;
      $this->m_key_color_rand            = isset($this->m_params['colorKeywordRandom']) && $this->m_params['colorKeywordRandom'] == 'on';
      $this->m_key_color_background_rand = isset($this->m_params['colorBackgroundKeywordRandom']) && $this->m_params['colorBackgroundKeywordRandom'] == 'on';
      $this->m_keys_color_type           = $this->m_params['countKeyColorType'];
      //--- в количественном соотношении выделяемых ключей
      $this->m_keys_min_color = $this->m_params['keysColorMin'];
      $this->m_keys_max_color = $this->m_params['keysColorMax'];
//--- процентное количество выделяемых цветом ключей
      $this->m_keys_min_color_percent = $this->m_params['keysColorMinPercent'];
      $this->m_keys_max_color_percent = $this->m_params['keysColorMaxPercent'];
      //--- списки цветов
      if(!empty($this->m_params['colorKeywordSet'])) $this->m_key_colors = $this->GetArrayColors($this->m_params['colorKeywordSet']);
      if(!empty($this->m_params['colorBackKeywordSet'])) $this->m_key_background_colors = $this->GetArrayColors($this->m_params['colorBackKeywordSet']);
      }
    //--- управление шрифтом
    if(!empty($this->m_params['keywordsFont']) && $this->m_params['keywordsFont'] == 'on')
      {
      //--- нужно выделять
      $this->m_is_keyword_font = true;
      $this->m_keys_font_type  = $this->m_params['countKeyFontType'];
      //--- в количественном соотношении выделяемых ключей
      $this->m_keys_min_font = $this->m_params['keysFontMin'];
      $this->m_keys_max_font = $this->m_params['keysColorMax'];
//--- процентное количество выделяемых цветом ключей
      $this->m_keys_min_font_percent = $this->m_params['keysFontMinPercent'];
      $this->m_keys_max_font_percent = $this->m_params['keysColorMaxPercent'];
      //--- размеры шрифта
      $this->m_keys_min_fontsize = $this->m_params['keysSizeMinChange'];
      $this->m_keys_max_fontsize = $this->m_params['keysSizeMaxChange'];
      //--- список шрифтов
      if(!empty($this->m_params['fontKeywordSet'])) $this->m_key_fonts = $this->GetArrayFonts($this->m_params['fontKeywordSet']);
      }
    }

  /**
   * Из текста получаем список цветов
   * @param $text
   *
   * @return array
   */
  private function GetArrayColors($text)
    {
    if(empty($text)) return array();
    //---
    $ar = explode(",", $text);
    if(empty($ar)) return array();
    //---
    $result = array();
    foreach($ar as $color)
      {
      $c = trim($color);
      if(empty($c)) continue;
      if($c[0] != '#') $c = '#' . $color;
      $result[] = $c;
      }
    return $result;
    }

  /**
   * Из текста получаем список шрифтов
   * @param $text
   *
   * @return array
   */
  private function GetArrayFonts($text)
    {
    if(empty($text)) return array();
    //---
    $ar = explode(",", $text);
    if(empty($ar)) return array();
    //---
    $result = array();
    foreach($ar as $color)
      {
      $c = trim($color);
      if(empty($c)) continue;
      $result[] = $c;
      }
    return $result;
    }

  /**
   * Нужно ли текущий кейворд выделять цветом
   * @param int $countKeywordsColored сколько уже раскрашено ключей
   * @param int $current номер текущего ключа
   * @param int $keywordsCount сколько всего ключей будет вставлять в текст
   * @param int $keywordsNeedColoredCount сколько всего нужно раскрасить ключей
   *
   * @return bool
   */
  private function IsColorKeyword(&$countKeywordsColored, $current, $keywordsCount, $keywordsNeedColoredCount)
    {
    //--- ничего не нужно выделять то и не выделяем
    if(!$this->m_is_keyword_color) return false;
    if(!$this->m_key_color_rand && empty($this->m_key_colors)) return false;
    //--- может уже все раскрасили
    if($countKeywordsColored >= $keywordsNeedColoredCount) return false;
    //--- если осталось ключевиков, столько же сколько нужно выделить, то выделяем все
    $needInsertKey = $keywordsCount - $current - 1;
    $needColorKey  = $keywordsNeedColoredCount - $countKeywordsColored;
    if($needColorKey > $needInsertKey)
      {
      $countKeywordsColored++;
      return true;
      }
    //---
    $is_color = rand(0, $keywordsNeedColoredCount);
    if($is_color == 0)
      {
      $countKeywordsColored++;
      return true;
      }
    return false;
    }

  /**
   * Получение настроек
   * @param <string> $param Название параметра
   */
  public function Get($param, $default = NULL)
    {
    if(empty($param)) return NULL;
    //---
    if(!is_array($this->m_settings))
      {
      $this->m_settings = array();
      CLogger::write(CLoggerType::DEBUG, self::LOG_PREFIX . 'settings: empty settings data');
      }
    //--- возвращаем значение ключа, если оно есть в массиве
    return isset($this->m_settings[$param]) ? $this->m_settings[$param] : $default;
    }

  /**
   * Сохранение настроек из POST-запроса
   * @param $settings
   * @param string $name
   * @param string $newname
   * @return bool|mixed|string
   */
  public function Save($settings, $name = 'default', $newname = '')
    {
    //---
    $name = trim(strtolower($name));
    if(empty($name)) return false;
    //--- проверка папки
    if(!file_exists(self::SETTINGS_PATH)) mkdir(self::SETTINGS_PATH, 0777, true);
    //--- сохраняем сериализованные настройки
    if(!empty($newname))
      {
      $fname                        = CModel_helper::generate_file_name(CModel_tools::Translit($newname));
      $this->m_settings['settings'] = $newname;
      }
    else
      {
      $fname = CModel_helper::generate_file_name(CModel_tools::Translit($name));
      }
    if(in_array($fname, self::$m_stop_words)) $fname .= '_1';
    //---
    file_put_contents(self::SETTINGS_PATH . $fname . ".data.php", serialize($settings));
    //---
    CLogger::write(CLoggerType::DEBUG, self::LOG_PREFIX . 'settings save to file ' . self::SETTINGS_PATH . $fname . ".data.php, name: " . $name);
    //---
    return $fname;
    }

  /**
   * Сохранение настроек из POST-запроса
   * @param $settings
   * @param string $name
   * @param string $newname
   * @return bool|mixed|string
   */
  public function SaveCurrent($settings)
    {
    //--- проверка папки
    if(!file_exists(self::SETTINGS_PATH)) mkdir(self::SETTINGS_PATH, 0777, true);
    //--- сохраняем сериализованные настройки
    file_put_contents(self::SETTINGS_PATH . '/' . self::LAST_SET_FILENAME, serialize($settings));
    //---
    CLogger::write(CLoggerType::DEBUG, self::LOG_PREFIX . 'settings save to file ' . self::SETTINGS_PATH . self::LAST_SET_FILENAME);
    //---
    return true;
    }

  /**
   *
   * Сохранение текущей настройки
   * @param string $filename
   */
  public function SaveCurrentSettings($filename)
    {
    //--- проверка папки
    if(!file_exists(self::SETTINGS_PATH)) mkdir(self::SETTINGS_PATH, 0777, true);
    //---
    file_put_contents(self::SETTINGS_PATH . self::LAST_SET_FILENAME, $filename);
    }

  /**
   *
   * Сохранение текущей настройки
   * @param string $filename
   */
  public function LoadCurrentSettings()
    {
    //---
    $filename = self::SETTINGS_PATH . '/' . self::LAST_SET_FILENAME;
    if(!file_exists($filename)) return '';
    //---
    return unserialize(file_get_contents($filename));
    }

  /**
   * Загрузка настроек
   * @param <string> $name Имя файла с настройками
   * @return <array> Массив с настройками
   */
  public function Load($name = '')
    {
    if(empty($name)) $name = 'default';
    //---
    $filename = self::SETTINGS_PATH . $name . ".data.php";
    if(file_exists($filename))
      {
      $c = file_get_contents($filename);
      return unserialize($c);
      }
    return array();
    }

  /**
   * Установка массива настроек
   * @param array $settingsArray
   */
  public function SetSettingsArray($settingsArray)
    {
    $this->m_settings = $settingsArray;
    }

  /**
   * Загрузка параметров для выделения кейвордов
   */
  private function LoadKeywodsSelectionParams()
    {
    //--- нужно ли выделять тэгом <em>
    if(isset($this->m_params['selectionEm']) && $this->m_params['selectionEm'] == 'on') $this->m_tags_keywords_selection[] = 'em';
    //--- нужно ли выделять тэгом <strong>
    if(isset($this->m_params['selectionStrong']) && $this->m_params['selectionStrong'] == 'on') $this->m_tags_keywords_selection[] = 'strong';
    //--- получим мин. и макс. плотность выделения кейвордов
    $this->m_density_keywords_selection = array((int)$this->m_params['densitySelectionMin'],
                                                (int)$this->m_params['densitySelectionMax']);
    //--- если мин. плотность больше максимальной
    if($this->m_density_keywords_selection[0] > $this->m_density_keywords_selection[1])
      {
      //--- меняем значения местами
      $this->m_density_keywords_selection = array_reverse($this->m_density_keywords_selection);
      }
    }
  }

?>