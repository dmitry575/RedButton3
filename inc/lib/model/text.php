<?php
//+------------------------------------------------------------------+
//|                  Copyright c 2012, RedButton Web Sites Generator |
//|                                      http://www.getredbutton.com |
//+------------------------------------------------------------------+
/**
 * Класс для работы с текстами

 */
class CModel_text
  {
  /**
   * Основной путь к картинкам
   */
  const PATH_TEXTS = 'data/texts';
  /**
   * Путь к случайным строкам
   */
  const PATH_RANDLINES = 'data/randlines';
  /**
   * Разделитель для пробела
   */
  const SPACE_REPLACE = '/|||/';
  /**
   * Параметры
   * @var array
   */
  private $m_params;
  /**
   * класс для генерации по маркову
   * @var CModel_TextMarkov
   */
  private $m_text_generator_markov;
  /**
   * класс для генерации по карлу маркса
   * @var CModel_TextKarlMarsk
   */
  private $m_text_generator_karl;
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
   * количество предложений в абзаце
   * @var int
   */
  private $m_count_sentenses = 3;
  /**
   * какие словари подключать
   * @var int
   */
  private $m_synonimazer_langs = 0;
  /**
   * синонимайзер
   * @var CModel_synonimazer
   */
  private $m_synonimazer = null;
  /**
   * рерайтер
   * @var CModel_Rewriter
   */
  private $m_rewrite = null;
  /**
   * текущий кейворд
   * @var CKeywordInfo
   */
  private $m_current_keyword;
  /**
   * Управление ссылками
   * @var CModel_links
   */
  private $m_model_links;
  /**
   * Управление ссылками
   * @var CModel_imageslinks
   */
  private $m_model_images;
  /**
   * список файлов
   * @var array
   */
  private $m_files_index;
  /**
   * список файлов для макроса nextline
   * @var array
   */
  private $m_nextfiles_index;
  /**
   * Массив предложений
   * @var array
   */
  private $texts;
  /**
   * случайные строки
   * @var array
   */
  private $randLinesIndex;
  /**
   * Общее количество загруженных предложений
   * @var int
   */
  private $textsCount;
  /**
   * Нужно ли добавлять ссылки в текст
   * @var bool
   */
  private $m_is_add_links;
  /**
   * Модель управления ключевиками
   * @var CModel_keywords
   */
  private $m_model_keywords;
  /**
   * локальный модуль для получения ссылок на старый дорвей
   * @var CModel_links
   */
  private $m_loc_model_links;
  /**
   * размер файла
   * @var int
   */
  private $m_loc_links_file_size;
  /**
   * хендел файла со ссылками
   * @var handle
   */
  private $m_loc_links_fhandle;
  /**
   * модуль для работы с ссылками
   * @var CModel_plugins
   */
  private $m_model_plugins = null;
  /**
   * Перемешивать слова к ключевике
   * @var bool
   */
  private $m_mix_keyword = false;
  /**
   * список текстов для вставки
   * @var array
   */
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
   * Выделять предложения тегом strong
   * @var bool
   */
  private $m_sentence_strong = false;
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
   * Нужно ли вставлять в текст картинки
   * @var bool
   */
  private $m_need_images = false;
  /**
   * Нужно ли вставлять в текст картинки c урлами
   * @var bool
   */
  private $m_need_urls_image = false;
  /**
   * Количество вставляемых картинок
   * @var int
   */
  private $m_images_need_add = 0;
  /**
   * Нужно ли вставлять случайные строки
   * @var int
   */
  private $m_need_randlines = false;
  /**
   * Количество вставляемых картинок
   * @var int
   */
  private $m_randlines_need_add = 0;
  /**
   * Случайные строки
   * @var array
   */
  private $randLines = array();
  /**
   * Список файлов на ссылки
   * @var array
   */
  private $m_files_links = array();

  /**
   * конструктор
   */
  public function __construct()
    {
    }

  /**
   * Иницилизация класса, передача параметров
   * @param array $params
   */
  public function Init(&$params, &$model_keywords, &$model_plugins, &$model_links, &$model_images, $is_need_print = true)
    {
    CLogger::write(CLoggerType::DEBUG, 'init text models ');
    //---
    $this->m_params         = $params;
    $this->m_model_keywords = $model_keywords;
    $this->m_model_plugins  = $model_plugins;
    $this->m_model_links    = $model_links;
    $this->m_model_images   = $model_images;
    //---
    if(!empty($this->m_params['synonimizerRu']) && $this->m_params['synonimizerRu']) $this->m_synonimazer_langs |= CModel_synonimazer::SYNC_RU;
    if(!empty($this->m_params['synonimizerEn']) && $this->m_params['synonimizerEn']) $this->m_synonimazer_langs |= CModel_synonimazer::SYNC_EN;
//--- вставка картинок в текст
    if(!empty($this->m_params['imagesFromFile']) && $this->m_params['imagesFromFile'] == 'on')
      {
      $this->m_need_images     = true;
      $this->m_need_urls_image = !empty($this->m_params['imagesUrlsFromFile']);
      }
//--- вставка случайных строк в текстовку
    if(!empty($this->m_params['randLines']) && $this->m_params['randLines'] == 'on') $this->m_need_randlines = true;
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
    //--- управление предложениями
    $this->SettingsSentences();
    //--- нужно ли перемешивать слова в ключевиках
    $this->m_mix_keyword = isset($this->m_params['keysMix']) && $this->m_params['keysMix'];
    //--- загрузим тексты
    $this->LoadText($is_need_print);
    //--- загрузим строки
    $this->LoadRandLines();
    //--- загрузим настройки
    $this->LoadKeywodsSelectionParams();
    //--- настройки для рерайта
    $this->SettingsRewrite();
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
   * установка текущего кейворда
   * @param string $keyword
   */
  public function setCurrentKeyword($keyword)
    {
    $this->m_current_keyword = $keyword;
    }

  /**
   * Получаем текст
   * Мин. кол-во предложений и Макс. кол-во предложений
   * @param $matches
   *
   * @param bool $need_keyword
   * @param bool $need_paragraph
   * @return string
   */
  public function GetText($matches, $need_keyword = true, $need_paragraph = true)
    {
    return $this->GetDataText($matches, $need_keyword, $need_paragraph);
    }

  /**
   * Получаем текст
   * Мин. кол-во предложений и Макс. кол-во предложений
   * @param $matches
   * @param bool $need_keyword
   * @param bool $need_paragraph
   * @return string
   */
  public function GetTextParams($matches, $need_keyword = true, $need_paragraph = true)
    {
    $ar = explode(';', $matches[3]);
    //---
    foreach($ar as $name)
      {
      $value = explode('=', $name);
      if(trim($value[0]) == 'links')
        {
        $this->m_files_links = explode('|', $value[1]);
        }
      }
//---
    return $this->GetDataText($matches, $need_keyword, $need_paragraph);
    }

  /**
   * Получаем текст без вставки ключевых слов
   * Мин. кол-во предложений и Макс. кол-во предложений
   * @param $matches
   *
   * @return string
   */
  public function GetTextNoKey($matches)
    {
    return $this->GetDataText($matches, false);
    }

  /**
   * Получаем текст без вставки ключевых слов и без вставки тега
   * Мин. кол-во предложений и Макс. кол-во предложений
   * @param $matches
   *
   * @return string
   */
  public function GetTextClearNoKey($matches)
    {
    return $this->GetDataText($matches, false, false);
    }

  /**
   * Получаем текст без вставки тега <p>
   * Мин. кол-во предложений и Макс. кол-во предложений
   * @param $matches
   *
   * @return string
   */
  public function GetTextClear($matches)
    {
    return $this->GetDataText($matches, true, false);
    }

  /**
   * Загрузка текстов из другого файла
   * @param string $filename
   * @param CModel_keywords $model_keyword
   * @return bool
   */
  private function LoadListFileTexts($filename, $model_keyword = null)
    {
//---
    $fname = self::PATH_TEXTS . '/' . trim($filename);
    if(!file_exists($fname))
      {
      CLogger::write(CLoggerType::ERROR, 'loading: text file ' . $filename . ' not found');
      return false;
      }
    //---
    $params                 = $this->m_params;
    $params['keysFrom']     = 'list';
    $params['keysFromList'] = $filename;
    $model_text             = new CModel_text();
    //---
    if($model_keyword == null) $model_keyword = $this->m_model_keywords;
    $model_text->Init($params, $model_keyword, $this->m_model_plugins, $this->m_model_links, $this->m_model_images, false);
    //---
    $this->m_file_texts_list[$filename] = $model_text;
//---
    return true;
    }

  /**
   * Получаем текст
   * Мин. кол-во предложений и Макс. кол-во предложений
   * @param $matches
   *
   * @return string
   */
  public function GetTextFromFile($matches)
    {
    $file_text       = $keyword_file = $urls_file = null;
    $urls_count_from = $urls_count_to = 1;
    //---
    if(!empty($matches[3]))
      {
      $ar = explode(';', $matches[3]);
      foreach($ar as $names)
        {
        if(empty($names)) continue;
        //---
        $values = explode('=', $names);
        $name   = strtolower(trim($values[0]));
        switch($name)
        {
          case 'texts':
            $file_text = trim($values[1]);
            break;
          case 'files':
            $file_text = trim($values[1]);
            break;
          case 'keywords':
            $keyword_file = trim($values[1]);
            break;
          case 'urls':
            $urls_file = trim($values[1]);
            break;
          case 'urls_count':
            if(strpos($values[1], '-') !== false)
              {
              $ar = explode('-', $values[1], 2);
              //---
              $urls_count_from = (int)$ar[0];
              if($urls_count_from <= 0) $urls_count_from = 1;
              //---
              $urls_count_to = (int)$ar[1];
              if($urls_count_to <= 0) $urls_count_to = 1;
              }
            break;
        }
        }
      }
    //---
    $model_keywords_new = null;
    //---
    if($keyword_file != null)
      {
      $model_keywords_new = $this->m_model_keywords->GetLoadedModules($keyword_file);
      if($model_keywords_new == null) return '';
      }
    //---
    if(!isset($this->m_file_texts_list[$file_text]))
      {
      if(!$this->LoadListFileTexts($file_text, $model_keywords_new)) return '';
      }
    //--- загрузка параметров
    $this->m_file_texts_list[$file_text]->SetParam($this->m_params);
    //--- получение ключевика
    if($keyword_file != null)
      {
      $this->m_file_texts_list[$file_text]->setCurrentKeyword($model_keywords_new->GetRandKeywordFromFile($keyword_file));
      }
    else                    $this->m_file_texts_list[$file_text]->setCurrentKeyword($this->m_current_keyword);
    //---
    if($urls_file != null)
      {
      $u = explode('|', $urls_file);
      //--- изменим параметр
      $this->m_file_texts_list[$file_text]->SetParams('linksFromFile', 'on');
      $this->m_file_texts_list[$file_text]->SetParams('linksFromFileName', $u[rand(0, count($u) - 1)]);
      //---
      $this->m_file_texts_list[$file_text]->SetParams('linksFromFileFrom', $urls_count_from);
      $this->m_file_texts_list[$file_text]->SetParams('linksFromFileTo', $urls_count_to);
      }
    //---
    /*$matches_new = array(0 => '',
                         1 => $matches[1],
                         2 => $matches[2],);
    */
    //---
    return $this->m_file_texts_list[$file_text]->GetDataText($matches, true);
    }

  /**
   * Установка параметров
   * @param $name
   * @param $value
   */
  public function SetParams($name, $value)
    {
    $this->m_params[$name] = $value;
    }

  /**
   * Установка параметров
   * @param $params
   */
  public function SetParam($params)
    {
    $this->m_params = $params;
    //---
    if(!empty($this->m_params['synonimizerRu']) && $this->m_params['synonimizerRu']) $this->m_synonimazer_langs |= CModel_synonimazer::SYNC_RU;
    if(!empty($this->m_params['synonimizerEn']) && $this->m_params['synonimizerEn']) $this->m_synonimazer_langs |= CModel_synonimazer::SYNC_EN;
    //--- нужно ли перемешивать слова в ключевиках
    $this->m_mix_keyword = isset($this->m_params['keysMix']) && $this->m_params['keysMix'];
    //--- загрузим настройки
    $this->LoadKeywodsSelectionParams();
    //--- настройки для рерайта
    $this->SettingsRewrite();
    }

  /**
   * Получаем текст без вставки тега <p>
   * Мин. кол-во предложений и Макс. кол-во предложений
   * @param $matches
   *
   * @return string
   */
  public function GetTextClearFromFile($matches)
    {
    $file_text       = $keyword_file = $urls_file = null;
    $urls_count_from = $urls_count_to = 1;
    //---
    if(!empty($matches[3]))
      {
      $ar = explode(';', $matches[3]);
      foreach($ar as $names)
        {
        $values = explode('=', $names);
        $name   = strtolower(trim($values[0]));
        switch($name)
        {
          case 'texts':
            $file_text = trim($values[1]);
            break;
          case 'files':
            $file_text = trim($values[1]);
            break;
          case 'keywords':
            $keyword_file = trim($values[1]);
            break;
          case 'urls':
            $urls_file = trim($values[1]);
            break;
          case 'urls_count':
            if(strpos($values[1], '-') !== false)
              {
              $ar = explode('-', $values[1], 2);
              //---
              $urls_count_from = (int)$ar[0];
              if($urls_count_from <= 0) $urls_count_from = 1;
              //---
              $urls_count_to = (int)$ar[1];
              if($urls_count_to <= 0) $urls_count_to = 1;
              }
            break;
        }
        }
      }
    //---
    $model_keywords_new = null;
    //---
    if($keyword_file != null)
      {
      $model_keywords_new = $this->m_model_keywords->GetLoadedModules($keyword_file);
      if($model_keywords_new == null) return '';
      }
    //---
    if(!isset($this->m_file_texts_list[$file_text]))
      {
      if(!$this->LoadListFileTexts($file_text, $model_keywords_new)) return '';
      }
//--- получение ключевика
    if($keyword_file != null)
      {
      $this->m_file_texts_list[$file_text]->setCurrentKeyword($model_keywords_new->GetRandKeywordFromFile($keyword_file));
      }
    else                    $this->m_file_texts_list[$file_text]->setCurrentKeyword($this->m_current_keyword);
    //---
    if($urls_file != null)
      {
      $u = explode('|', $urls_file);
      //--- изменим параметр
      $this->m_file_texts_list[$file_text]->SetParams('linksFromFile', 'on');
      $this->m_file_texts_list[$file_text]->SetParams('linksFromFileName', $u[rand(0, count($u) - 1)]);
      //---
      $this->m_file_texts_list[$file_text]->SetParams('linksFromFileFrom', $urls_count_from);
      $this->m_file_texts_list[$file_text]->SetParams('linksFromFileTo', $urls_count_to);
      }
    //---
    return $this->m_file_texts_list[$file_text]->GetDataText($matches, true, false);
    }

  /**
   * Получаем текст без вставки ключевых слов
   * Мин. кол-во предложений и Макс. кол-во предложений
   * @param $matches
   *
   * @return string
   */
  public function GetTextNoKeyFromFile($matches)
    {
    $file_text       = $keyword_file = $urls_file = null;
    $urls_count_from = $urls_count_to = 1;
    //---
    if(!empty($matches[3]))
      {
      $ar = explode(';', $matches[3]);
      foreach($ar as $names)
        {
        $values = explode('=', $names);
        $name   = strtolower(trim($values[0]));
        switch($name)
        {
          case 'texts':
            $file_text = trim($values[1]);
            break;
          case 'files':
            $file_text = trim($values[1]);
            break;
          case 'urls':
            $urls_file = trim($values[1]);
            break;
          case 'urls_count':
            if(strpos($values[1], '-') !== false)
              {
              $ar = explode('-', $values[1], 2);
              //---
              $urls_count_from = (int)$ar[0];
              if($urls_count_from <= 0) $urls_count_from = 1;
              //---
              $urls_count_to = (int)$ar[1];
              if($urls_count_to <= 0) $urls_count_to = 1;
              }
            break;
        }
        }
      }
    //---
    $model_keywords_new = null;
    //---
    if($keyword_file != null)
      {
      $model_keywords_new = $this->m_model_keywords->GetLoadedModules($keyword_file);
      if($model_keywords_new == null) return '';
      }
    //---
    if(!isset($this->m_file_texts_list[$file_text]))
      {
      if(!$this->LoadListFileTexts($file_text, $model_keywords_new)) return '';
      }
//--- получение ключевика
    $this->m_file_texts_list[$file_text]->setCurrentKeyword($this->m_current_keyword);
    //---
    if($urls_file != null)
      {
      $u = explode('|', $urls_file);
      //--- изменим параметр
      $this->m_file_texts_list[$file_text]->SetParams('linksFromFile', 'on');
      $this->m_file_texts_list[$file_text]->SetParams('linksFromFileName', $u[rand(0, count($u) - 1)]);
      //---
      $this->m_file_texts_list[$file_text]->SetParams('linksFromFileFrom', $urls_count_from);
      $this->m_file_texts_list[$file_text]->SetParams('linksFromFileTo', $urls_count_to);
      }
    //---
    return $this->m_file_texts_list[$file_text]->GetDataText($matches, false);
    }

  /**
   * Получаем текст без вставки ключевых слов и без вставки тега
   * Мин. кол-во предложений и Макс. кол-во предложений
   * @param $matches
   *
   * @return string
   */
  public function GetTextClearNoKeyFromFile($matches)
    {
    $file_text       = $keyword_file = $urls_file = null;
    $urls_count_from = $urls_count_to = 1;
    //---
    if(!empty($matches[3]))
      {
      $ar = explode(';', $matches[3]);
      foreach($ar as $names)
        {
        $values = explode('=', $names);
        $name   = strtolower(trim($values[0]));
        switch($name)
        {
          case 'texts':
            $file_text = trim($values[1]);
            break;
          case 'files':
            $file_text = trim($values[1]);
            break;
          case 'urls':
            $urls_file = trim($values[1]);
            break;
          case 'urls_count':
            if(strpos($values[1], '-') !== false)
              {
              $ar = explode('-', $values[1], 2);
              //---
              $urls_count_from = (int)$ar[0];
              if($urls_count_from <= 0) $urls_count_from = 1;
              //---
              $urls_count_to = (int)$ar[1];
              if($urls_count_to <= 0) $urls_count_to = 1;
              }
            break;
        }
        }
      }
    //---
    $model_keywords_new = null;
    //---
    if($keyword_file != null)
      {
      $model_keywords_new = $this->m_model_keywords->GetLoadedModules($keyword_file);
      if($model_keywords_new == null) return '';
      }
    //---
    if(!isset($this->m_file_texts_list[$file_text]))
      {
      if(!$this->LoadListFileTexts($file_text, $model_keywords_new)) return '';
      }
//--- получение ключевика
    $this->m_file_texts_list[$file_text]->setCurrentKeyword($this->m_current_keyword);
    //---
    if($urls_file != null)
      {
      $u = explode('|', $urls_file);
      //--- изменим параметр
      $this->m_file_texts_list[$file_text]->SetParams('linksFromFile', 'on');
      $this->m_file_texts_list[$file_text]->SetParams('linksFromFileName', $u[rand(0, count($u) - 1)]);
      //---
      $this->m_file_texts_list[$file_text]->SetParams('linksFromFileFrom', $urls_count_from);
      $this->m_file_texts_list[$file_text]->SetParams('linksFromFileTo', $urls_count_to);
      }
    //---
    return $this->m_file_texts_list[$file_text]->GetDataText($matches, false, false);
    }

  /**
   * Нужно ли добавлять ссылки
   * @return bool
   */
  private function NeedAddLinks()
    {
    return (isset($this->m_params['linksDor']) && $this->m_params['linksDor'] == 'on') || (isset($this->m_params['linksOldDor']) && $this->m_params['linksOldDor'] == 'on') || (isset($this->m_params['linksFromFile']) && $this->m_params['linksFromFile'] == 'on');
    }

  /**
   * Добавление текстов
   * @param      $matches
   * @param bool $is_add_key
   * @param bool $is_add_parag
   *
   * @return string
   */
  private function GetDataText($matches, $is_add_key = true, $is_add_parag = true)
    {
    //--- минимальное кол-во предложений
    $min = (int)$matches[1];
    //--- максимальное кол-во предложений
    $max = (int)$matches[2];
    //--- получаем рандомное кол-во предложений
    $length = rand($min, $max);
    //--- получим количество предложений
    $this->m_count_sentenses = rand($this->m_params['minSentensesParagraph'], $this->m_params['maxSentensesParagraph']);
    $this->m_is_add_links    = $this->NeedAddLinks();
    //--- если в тексте нужны картинки определим количество
    if($this->m_need_images) $this->m_images_need_add = rand($this->m_params['imagesFromFileFrom'], $this->m_params['imagesFromFileTo']);
//--- если в тексте нужны случайные строки, то определим количество
    if($this->m_need_randlines) $this->m_randlines_need_add = rand($this->m_params['randLinesFromFileFrom'], $this->m_params['randLinesFromFileTo']);
    //---
    if($this->m_params['algorithm'] == 'markov')
      {
      $textArray = array();
      //--- достаем тексты по алгоритму маркова
      for($i = 0; $i < $length; $i++)
        {
        $textArray[] = trim($this->m_text_generator_markov->GetSentence(), '.?! ');
        }
      //---
      return $this->GetFormattedText($textArray, $is_add_key, $is_add_parag);
      }
    //---
    if($this->m_params['algorithm'] == 'karlmarks')
      {
      if($this->m_text_generator_karl == null) $this->m_text_generator_karl = new CModel_TextKarlMarsk();
      //--- настройки
      $settings = array( //--- минимальное и максимальное количество кейвордов в тексте
        'numkeys'  => rand(1, 2),
        //--- минимальное и максимальное число параграфов в тексте
        'numpar'   => $length,
        //--- максимальное кол-во слов в предложении (случайное число в диапазоне указанных чисел)
        'numwords' => array(35,
                            40));
      //--- получаем текст по карлу марксу
      $keyword = $this->m_current_keyword->getKeywordRand();
      if($this->m_mix_keyword) $keyword = CModel_keywords::MixWordsInKey($keyword);
      $texts = $this->m_text_generator_karl->GetText($this->texts, $keyword, $settings);
      //---
      return $this->GetFormattedText($texts, $is_add_key, $is_add_parag);
      }
    //--- простой алгоритм
    /** @var $offset int узнаем, сколько предложений остается */
    $offset = $this->textsCount - $length;
    //--- если предложений не останется
    if($offset < 1)
      {
      $offset = 0;
      //--- значит будем брать весь текст
      $length = $this->textsCount;
      }
    else
      {
      $offset = rand(0, $offset);
      }
    //--- форматируем выбранный текст
    return $this->GetFormattedText(array_slice($this->texts, $offset, $length), $is_add_key, $is_add_parag);
    }

  /**
   * Получаем текст
   * Мин. кол-во предложений и Макс. кол-во предложений
   * @param $matches
   *
   * @return array
   */
  public function GetTextArraySententes($matches)
    {
    //--- минимальное кол-во предложений
    $min = (int)$matches[1];
    //--- максимальное кол-во предложений
    $max = (int)$matches[2];
    //--- получаем рандомное кол-во предложений
    $length = rand($min, $max);
    //--- получим количество предложений
    $this->m_count_sentenses = rand($this->m_params['minSentensesParagraph'], $this->m_params['maxSentensesParagraph']);
    //---
    if($this->m_params['algorithm'] == 'markov')
      {
      $textArray = array();
      //--- достаем тексты по алгоритму маркова
      for($i = 0; $i < $length; $i++)
        {
        $textArray[] = trim($this->m_text_generator_markov->GetSentence(), '.?!');
        }
      return $textArray;
      }
    //---
    if($this->m_params['algorithm'] == 'karlmarks')
      {
      if($this->m_text_generator_karl == null) $this->m_text_generator_karl = new CModel_TextKarlMarsk();
      //--- настройки
      $settings = array( //--- минимальное и максимальное количество кейвордов в тексте
        'numkeys'  => rand(1, 2),
        //--- минимальное и максимальное число параграфов в тексте
        'numpar'   => $length,
        //--- максимальное кол-во слов в предложении (случайное число в диапазоне указанных чисел)
        'numwords' => array(35,
                            40));
      //--- получаем текст по карлу марксу
      $keyword = $this->m_current_keyword->getKeywordRand();
      if($this->m_mix_keyword) $keyword = CModel_keywords::MixWordsInKey($keyword);
      return $this->m_text_generator_karl->GetText($this->texts, $keyword, $settings);
      }
    //--- простой алгоритм
    /** @var $offset int узнаем, сколько предложений остается */
    $offset = $this->textsCount - $length;
    //--- если предложений не останется
    if($offset < 1)
      {
      $offset = 0;
      //--- значит будем брать весь текст
      $length = $this->textsCount;
      }
    else
      {
      $offset = rand(0, $offset);
      }
    //--- форматируем выбранный текст
    return array_slice($this->texts, $offset, $length);
    }

  /**
   * Добавление ссылок
   * @param $textArray
   * @param $urls
   *
   * @return mixed
   */
  private function AddLinks($textArray, $urls)
    {
    if(empty($urls)) return $textArray;
    //--- нужно учитывать параметр linksNoanchor, т.е. вставка в текст не ссылки а урл
    $noanchor = 0;
    if(!empty($this->m_params['linksNoanchor'])) $noanchor = (int)$this->m_params['linksNoanchor'];
    //---
    $sentenceCount = sizeof($textArray);
    //--- получаем кол-во слов в тексте поделил по полам, т.к. половину это лишние пробелы. Более точно искать количество слов затратно
    $wordsCount = (int)(mb_substr_count(implode(' ', $textArray), ' ') / 1.6);
    //---
    $keywordsCount = count($urls);
    //--- счетчик выделенных ключевых слов
    $keywordsSelected = 0;
    //--- итератор для выбора предложения для вставки ссылки
    $currentSentence = 0;
    //--- получим шаг для равномерного распределения ссылок по предложениям
    $step = $sentenceCount / $keywordsCount;
    if($step < 1) $step = 1;
    //---
    for($i = 0; $i < $keywordsCount && $i < count($urls); $i++)
      {
      //--- проверим, нужно ли выделять кейворды тэгами
      ++$keywordsSelected;
      //--- проверяем, чтобы хватало предложений
      if($currentSentence >= $sentenceCount) break;
      //--- правильная разбивка на слова, т.к. могут встречаться ссылки
      $sentenceWords = $this->GetSentenceWords($textArray[$currentSentence]);
      $sz            = sizeof($sentenceWords) - 1;
      //--- правильное получение номера предложения, чтобы не было ссылок
      $wordPos = $this->GetRandNumberWord($sentenceWords, $sz);
      //---
      if($wordPos == 0 && $wordsCount > 1) $wordPos = 1;
      //---
      $anchor = ($noanchor > 0 ? (rand(0, 100) < $noanchor ? $urls[$i]['url'] : $urls[$i]['key']) : $urls[$i]['key']);
      $anchor = str_replace(' ', self::SPACE_REPLACE, $anchor);
      //---
      $sentenceWords[$wordPos] = (!empty($sentenceWords[$wordPos]) ? $sentenceWords[$wordPos] . ' ' : '') . '<a href="' . $urls[$i]['url'] . '">' . $anchor . '</a>';
      //---
      $textArray[$currentSentence] = implode(' ', $sentenceWords);
      //--- получим номер следующего предложение для вставки кейвордов
      $currentSentence += $step;
      }
    //---
    unset($sentenceWords);
    //---
    return $textArray;
    }

  /**
   * Добавляем ключевые слова в текст
   * @param array $textArray Массив предложений
   *
   * @return array
   */
  private function AddLinksToText($textArray)
    {
    $links = array();
    //--- ссылки на текущий дорвей
    if(isset($this->m_params['linksDor']) && $this->m_params['linksDor'] == 'on')
      {
      $keywordsCount = rand((int)$this->m_params['linksDorFrom'], (int)$this->m_params['linksDorTo']);
      $links         = array_merge($links, $this->m_model_keywords->GetRandKeywordsLinks($keywordsCount));
      }
    //--- ссылки на старые доры
    if(isset($this->m_params['linksOldDor']) && $this->m_params['linksOldDor'] == 'on')
      {
      $keywordsCount = rand((int)$this->m_params['linksOldDorFrom'], (int)$this->m_params['linksOldDorTo']);
      //---
      $old_links = $this->m_model_links->GetRandOldLinks($this->m_params['saveLinkPath'], $keywordsCount, $this->m_files_links, $this->m_loc_links_fhandle, $this->m_loc_links_file_size);
      if(!empty($old_links)) $links = array_merge($links, $old_links);
      }
    //--- ссылки на левые сайты из файла
    if(isset($this->m_params['linksFromFile']) && $this->m_params['linksFromFile'] == 'on')
      {
      $linksCount = rand((int)$this->m_params['linksFromFileFrom'], (int)$this->m_params['linksFromFileTo']);
      //---
      $old_links = $this->m_model_links->GetRandLinksFromFile("", $this->m_params['linksFromFileName'], $linksCount);
      if(!empty($old_links)) $links = array_merge($links, $old_links);
      }
    //--- вставляем ссылки
    $textArray = $this->AddLinks($textArray, $links);
    //---
    return $textArray;
    }

  /**
   * Добавляем ключевые слова в текст
   *
   * @param array $textArray Массив предложений
   * @param bool $is_add_parag
   *
   * @return array
   */
  private function AddKeywordsToText($textArray, $is_add_parag = true)
    {
    $sentenceWords = array();
    $sentenceCount = sizeof($textArray);
    //--- получаем кол-во слов в тексте поделил по полам, т.к. половину это лишние пробелы. Более точно искать количество слов затратно
    $wordsCount = (int)(mb_substr_count(implode(' ', $textArray), ' ') / 1.6);
    /*
    //--- сколько процентов ключевых слов надо добавить
    $keywordsPercent=rand((int)$this->m_params['keysDensityMin'],(int)$this->m_params['keysDensityMax']);
    //--- сколько в итоге нужно поставить ключевых слов
    $keywordsCount=round($wordsCount*$keywordsPercent/100);
    */
    //--- сколько нужно поставить ключевых слов
    $keywordsCount = rand((int)$this->m_params['keysDensityMin'], (int)$this->m_params['keysDensityMax']);
    //--- нужно ли выделять кейворды тэгами
    $isNeedKeySelect = sizeof($this->m_tags_keywords_selection) > 0 && $this->m_density_keywords_selection[1] > 0;
    //--- сколько процентов ключевых слов надо выделять
    $keywordsSelectionPercent = rand($this->m_density_keywords_selection[0], $this->m_density_keywords_selection[1]);
    //--- сколько в итоге нужно выделить ключевых слов
    $keywordsSelectionCount = round($keywordsCount * $keywordsSelectionPercent / 100);
//--- сколько ключевых слов надо выделять
    if($this->m_keys_color_type == 'percent')
      {
//--- выделение ключевиков в процентах
      $keyColorPercent          = rand($this->m_keys_min_color_percent, $this->m_keys_max_color_percent);
      $keywordsNeedColoredCount = round($keywordsCount * $keyColorPercent / 100);
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
      $keywordsNeedFontsCount = round($keywordsCount * $keyColorPercent / 100);
      }
    else
      {
      $keywordsNeedFontsCount = rand($this->m_keys_min_font, $this->m_keys_max_font);
      }
    //--- сколько нужно выделить предложений
    $sentenceNeedColoredCount = rand($this->m_sentence_min_color, $this->m_sentence_max_color);
    //--- распределение вероятности выделения ключевых слов
    //$keywordsSelectionMax = $keywordsSelectionCount > 0 ? round($keywordsCount / ($keywordsSelectionCount * 1.4)) : 0;
    //--- счетчик выделенных ключевых слов
    $keywordsSelected = 0;
    //--- сколько слов уже разукрашено
    $countKeywordsColored = 0;
    //--- у скольки слов уже разукрашены фоны
    $countKeywordsBackColored = 0;
    //--- у скольки слов уже поменяли шрифт
    $countKeywordsFonts = 0;
    //--- сколько предложений уже разукрашено
    $countSentencesColored = 0;
    //---
    if($keywordsCount == 0) return $textArray;
    //--- получим шаг для равномерного распределения кейвордов по предложениям
    $step = $sentenceCount / $keywordsCount;
    if($step < 1) $step = 1;
    //--- итератор для выбора предложения для вставки кейвордов
    $currentSentence = 0;
    //---
    mt_srand((int)microtime(true));
    $count_tags = count($this->m_tags_keywords_selection);
    $last_rand  = rand(0, 1);
    //---
    for($i = 0; $i < $keywordsCount; $i++)
      {
      //--- проверим, нужно ли выделять кейворды тэгами
      if($isNeedKeySelect && $last_rand && $keywordsSelected < $keywordsSelectionCount)
        {
        $last_rand = rand(0, 1);
        //--- получим вариант тэга
        if($is_add_parag)
          {
          $tag = $this->m_tags_keywords_selection[rand(0, $count_tags - 1)];
          //---
          $openedTag = "<{$tag}>";
          $closedTag = "</{$tag}>";
          }
        else
          {
          $openedTag = '';
          $closedTag = '';
          }
        //---
        ++$keywordsSelected;
        }
      else
        {
        $openedTag = '';
        $closedTag = '';
        $last_rand = !$last_rand;
        }
      //--- проверяем, чтобы хватало предложений
      if($currentSentence >= $sentenceCount) break;
      //--- правильная разбивка на слова, т.к. могут встречаться ссылки
      $sentenceWords = $this->GetSentenceWords($textArray[$currentSentence]);
      $sz            = sizeof($sentenceWords) - 1;
      //--- правильное получение номера предложения, чтобы не было ссылок
      $wordPos = $this->GetRandNumberWord($sentenceWords, $sz);
      //---
      if($wordPos < 0 || empty($sentenceWords[$wordPos])) continue;
      //---
      if($wordPos == 0 && $wordsCount > 1) $wordPos = 1;
      //--- нужно ли выделять цветом текущий ключ
      $isColorKey = $is_add_parag && $this->IsColorKeyword($countKeywordsColored, $i, $keywordsCount, $keywordsNeedColoredCount);
      //--- нужно ли выделять фон у ключевика
      $isColorBackground = $is_add_parag && $this->IsColorBackgroundKeyword($countKeywordsBackColored, $i, $keywordsCount, $keywordsNeedColoredCount);
      //--- нужно ли менять шрифты
      $isChangeFont = $is_add_parag && $this->IsChangeFontsKeyword($countKeywordsFonts, $i, $keywordsCount, $keywordsNeedFontsCount);
      //--- вставка ключевика
      //var_dump($sentenceWords);
      $sentenceWords[$wordPos] = $this->SelectedWordsInSentence($wordPos, $sentenceWords, $openedTag, $closedTag, $isColorKey, $isColorBackground, $isChangeFont);
      //---
      $isSentenceSelect            = $this->IsColorSentence($countSentencesColored, $i, $keywordsCount, $sentenceNeedColoredCount);
      $textArray[$currentSentence] = implode(' ', $sentenceWords);
      if($isSentenceSelect)
        {
        $style = $this->GetSentenceStyleSelect();
        $tag   = 'span';
        if($this->m_sentence_strong) $tag = 'strong';
        //--- выделяем стилями предложение, в которое вставили ключевое слово
        $textArray[$currentSentence] = '<' . $tag . ' style="' . $style . '">' . trim($this->mb_ucfirst($textArray[$currentSentence])) . '.</' . $tag . '>';
        }
      //--- получим номер следующего предложение для вставки кейвордов
      $currentSentence += $step;
      }
    //---
    unset($sentenceWords);
    //---
    return $textArray;
    }

  private function GetRandNumberWord(&$sentenceWords, $sz)
    {
    $wordPos = rand(0, $sz);
    $i       = 0;
    while($i < $sz)
      {
      $word = $sentenceWords[$wordPos];
      if(!isset($word[1])) return $wordPos;
      if($word[0] == '<' && $word[1] == 'a')
        {
        $wordPos++;
        if($wordPos > $sz) $wordPos = 0;
        }
      else return $wordPos;
      $i++;
      }
    return -1;
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
   * Так как в предложениях уже могут встречаться теги <a, нужно правильно разбитьс на слова
   * @param $currentSentence
   */
  private function GetSentenceWords(&$currentSentence)
    {
    $a = explode(' ', $currentSentence);
    if(strpos($currentSentence, '</a>') === false) return $a;
    //---
    $j   = $i = 0;
    $len = count($a);
    while($j < $len)
      {
      if($a[$j] == '<a')
        {
        if(($j + 1) < $len)
          {
          $a[$i] = $a[$j];
          while(strpos($a[$j], '</a>') === false && ($j + 1) < $len)
            {
            $a[$i] .= ' ' . $a[$j + 1];
            $j++;
            };
          }
        else $a[$i] = $a[$j];
        }
      else $a[$i] = $a[$j];
      $i++;
      $j++;
      }
    //--- остатки не забываем зачищать
    for($k = $i; $k < $len; $k++) unset($a[$k]);
    return $a;
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
    $is_color = rand(0, 1);
    if($is_color)
      {
      $countKeywordsColored++;
      return true;
      }
    return false;
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
    if(!$this->m_sentence_color_rand && empty($this->m_sentence_colors) && !$this->m_sentence_color_background_rand && empty($this->m_sentence_background_colors) && !$this->m_sentence_strong) return false;
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
    $is_color = rand(0, 1);
    if($is_color)
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
    $is_color = rand(0, 1);
    if($is_color)
      {
      $countBackgroundColored++;
      return true;
      }
    return false;
    }

  /**
   * Начинаем синонимизацуию
   * @param array $textArray
   */
  private function Synonimaze(&$textArray)
    {
    if($this->m_synonimazer == null) $this->m_synonimazer = new CModel_synonimazer($this->m_synonimazer_langs);
    foreach($textArray as &$text)
      {
      $text = $this->m_synonimazer->Sync($text, $this->m_params['synonimizerMin'], $this->m_params['synonimizerMax']);
      }
    }

  /**
   * Запуск рерайтера
   * @param string $textArray
   */
  private function Rewrite(&$textArray)
    {
    if($this->m_rewrite == null) $this->m_rewrite = new CModel_Rewriter(CModel_Rewriter::REWRITE_RU, $this->m_params['shakeFrom'], $this->m_params['shakeTo'], $this->m_params['changestructurFrom'], $this->m_params['changestructurTo'], $this->m_params['adjFrom'], $this->m_params['adjTo']);
    $this->m_rewrite->Rewrite($textArray);
    }

  /**
   * Первую букву делаем заглавной
   * @param string $string
   */
  private function mb_ucfirst($string)
    {
    return mb_strtoupper(mb_substr($string, 0, 1, 'UTF-8')) . mb_substr($string, 1);
    }

  /**
   * Получаем форматированный текст из заранее
   * подготовленного массива предложений.
   *
   * Разделяем на параграфы, добавляем ключевые слова и т.п.
   * @param array $textArray
   *
   * @return string
   */
  private function GetFormattedText($textArray, $is_add_key = true, $is_add_parag = true)
    {
    //--- если включен синонимайзер, то нужно все предложения поменять
    if($this->m_synonimazer_langs > 0) $this->Synonimaze($textArray);
    if($this->m_params['rewriteText']) $this->Rewrite($textArray);
    //--- добавляем ссылки
    if($this->m_is_add_links && $is_add_parag) $textArray = $this->AddLinksToText($textArray);
    //--- добавляем ключевые слова
    if($is_add_key) $textArray = $this->AddKeywordsToText($textArray, $is_add_parag);
//--- разделяем текст на параграфы
    if($is_add_parag) $text = '<p>';
    else               $text = '';
    //---
    if(!empty($this->m_model_plugins)) $this->m_model_plugins->OnBeforTextAddTags($textArray);
    $isClosed = false;
    //---
    $images_added    = 0;
    $randlines_added = 0;
    for($i = 0, $sz = count($textArray); $i < $sz; $i++)
      {
      if($isClosed)
        {
        if($is_add_parag) $text .= "\r\n<p>";
        else $text .= ' ';
        $isClosed = false;
        }
      //---
      $sentence = trim($this->mb_ucfirst($textArray[$i]));
      $text .= $sentence;
      //--- нужна ли точка в конце
      if(strpos($sentence, ".</span>") === false && strpos($sentence, ".</strong>") === false) $text .= '.';
      //---
      if((($i + 1) % $this->m_count_sentenses) == 0)
        {
        if($is_add_parag) $text = rtrim($text) . '</p>';
        //--- если нужно добавляем картинку в текст
        if($this->m_need_images) $this->AddImagesText($text, $i, $sz, $images_added);
        //--- если нужно добавляем случайную строку в текст
        if($this->m_need_randlines) $this->AddRandLinesText($text, $i, $sz, $randlines_added);
        $isClosed = true;
        }
      else  $text .= ' ';
      }
    //---
    if(!$isClosed)
      {
      if($is_add_parag)
        {
        $text = rtrim($text) . '</p>';
        //--- если нужно добавляем картинку в текст
        if($this->m_need_images) $this->AddImagesText($text, $i, $sz, $images_added);
        //--- если нужно добавляем случайную строку в текст
        if($this->m_need_randlines) $this->AddRandLinesText($text, $i, $sz, $randlines_added);
        }
      }
    //---
    if($this->m_is_add_links) $text = str_replace(self::SPACE_REPLACE, ' ', $text);
    //---
    if(!empty($this->m_model_plugins)) $this->m_model_plugins->OnEndTextFormated($textArray);
    CLogger::write(CLoggerType::DEBUG, 'texts ' . $sz . ' sentences, count paragraphs ' . $this->m_count_sentenses);
    return $text;
    }

  /**
   * В зависимости от установок выделим либо ключевое слово, либо фразу
   * @param int $wordPos
   * @param array $sentenceWords
   * @param string $openedTag
   * @param string $closedTag
   * @param bool $isColorKey
   * @param bool $isColorBackground
   * @param bool $isChangeFont
   * @return string
   */
  private function SelectedWordsInSentence(&$wordPos, &$sentenceWords, &$openedTag, &$closedTag, $isColorKey = false, $isColorBackground = false, $isChangeFont = false)
    {
    //--- нужно ли разукрашивать фон или ключ
    if($isColorKey || $isColorBackground || $isChangeFont)
      {
      $this->GetStyleColorsKeyword($openedTag, $closedTag, $isColorKey, $isColorBackground, $isChangeFont);
      }
    //--- получени ключа для вставки
    $keyword = $this->m_current_keyword->getKeywordRand();
    //--- если нужно то перемешиваем
    if($this->m_mix_keyword) $keyword = CModel_keywords::MixWordsInKey($keyword);
    //--- что нужно выделять
    switch($this->m_params['densitySelectionFor'])
    {
      //--- все выделять
      case 'all':
        if(rand(0, 1) == 1)
          {
          return (!empty($sentenceWords[$wordPos]) ? $sentenceWords[$wordPos] . ' ' : '') . $openedTag . $keyword . $closedTag;
          }
      //--- выделяем фразу
      case 'phrases':
        $next = rand(0, 1) == 1 ? 1 : -1;
        //--- проверим существует следующий?
        $next_i    = $wordPos + $next;
        $next_word = $this->GetNextWord($sentenceWords, $next, $next_i);
        //--- если слово пусто, не повезло
        if(empty($next_word))
          {
          return (!empty($sentenceWords[$wordPos]) ? $sentenceWords[$wordPos] . ' ' : '') . $openedTag . $keyword . $closedTag;
          }
        //--- уберем лишний элемент из массива
        unset($sentenceWords[$next_i]);
        return $openedTag . ($next < 0 ? $next_word . ' ' : '') . (!empty($sentenceWords[$wordPos]) ? $sentenceWords[$wordPos] . ' ' : '') . $keyword . ($next < 0 ? '' : ' ' . $next_word) . $closedTag;
      //--- выделяем просто ключевое слово
      default:
        return (!empty($sentenceWords[$wordPos]) ? $sentenceWords[$wordPos] . ' ' : '') . $openedTag . $keyword . $closedTag;
    }
    }

  /**
   * вставка картинок в тексты
   * @param $text
   * @param $i
   * @param $sz
   * @param $randlines_added
   */
  private function AddRandLinesText(&$text, $i, $sz, &$randlines_added)
    {
    //--- сколько строк уже вставлено
    if($randlines_added >= $this->m_randlines_need_add) return;
    //--- случай
    if(rand(0, (int)$sz / $this->m_randlines_need_add + 1) == 1) return;
    $randline = $this->GetRandLine(null);
    //---
    $this->ImageAddIntoText($text, $randline);
    $randlines_added++;
    }

  /**
   * вставка картинок в тексты
   * @param $text
   * @param $i
   * @param $sz
   * @param $images_added
   */
  private function AddImagesText(&$text, $i, $sz, &$images_added)
    {
    //--- сколько картинок уже вставлено
    if($images_added >= $this->m_images_need_add) return;
    //--- сколько еще осталось вставить картинок
    $images_a = $this->m_images_need_add - $images_added;
    //--- определяем позицию для вставки картинки
    if(($this->m_params['imagesWhere'] & CModel_imageslinks::RAND) == CModel_imageslinks::RAND)
      {
      //--- если впереди еще есть предложения, то будем выбирать случайным образом
      if(($sz - $i) > $images_a)
        {
        //--- нужно или нет
        if(rand(0, $images_a)) return;
        }
      }
    else
      {
      //--- картинка нужна в самом начале
      if(($this->m_params['imagesWhere'] & CModel_imageslinks::TEXT_BEFORE) == CModel_imageslinks::TEXT_BEFORE)
        {
        }
      elseif(($this->m_params['imagesWhere'] & CModel_imageslinks::TEXT_CENTER) == CModel_imageslinks::TEXT_CENTER)
        {
        $c = $sz / 2;
        if((($c - $this->m_images_need_add) < $i) && ($i < ($c + $this->m_images_need_add)))
          {
          if(($i + $images_a) == ($c + $this->m_images_need_add))
            {
            //--- точно вставляем
            }
          else
            {
            //--- нужно или нет
            if(rand(0, 1)) return;
            }
          }
        }
      elseif(($this->m_params['imagesWhere'] & CModel_imageslinks::TEXT_AFTER) == CModel_imageslinks::TEXT_AFTER)
        {
        $c = $sz / 2;
        if(($sz - $this->m_images_need_add) >= $i || $i == $sz)
          {
          if(($i + $images_a) == ($c + $this->m_images_need_add))
            {
            //--- точно вставляем
            }
          else
            {
            //--- нужно или нет
            if(rand(0, 1)) return;
            }
          }
        }
      }
    //--- получаем саму картинку
    $img = $this->m_model_images->GetRandImageFromFile($this->m_params['imagesFromFileName'], $this->m_params['imagesAlign']);
    if($this->m_need_urls_image && !empty($img))
      {
      $img = $this->m_model_images->GetRandUrlFromFiles($this->m_params['imagesUrlsFromFile'], $img);
      }
    if(empty($img)) return '';
    //--- вставляем картинку
    if(!empty($img))
      {
      if($i < 3 && ($this->m_params['imagesWhere'] & CModel_imageslinks::TEXT_BEFORE) == CModel_imageslinks::TEXT_BEFORE)
        {
        //--- вставляем перед текстом
        //--- нужно в тег p, или отдельной <p>
        if(isset($this->m_params['imagesInText']) && $this->m_params['imagesInText'] == 'on')
          {
          $this->ImageAddIntoText($text, $img);
          }
        else
          {
          $tag  = rand(0, 1) ? 'div' : 'p';
          $text = '<' . $tag . '>' . $img . '</' . $tag . '>' . (rand(0, 1) ? "\r\n" : '') . $text;
          }
        //---
        $images_added++;
        }
      //--- картинку после текста
      else
        {
        if($i == $sz && ($this->m_params['imagesWhere'] & CModel_imageslinks::TEXT_AFTER) == CModel_imageslinks::TEXT_AFTER)
          {
          $tag = rand(0, 1) ? 'div' : 'p';
          $text .= (rand(0, 1) ? "\r\n" : '') . '<' . $tag . '>' . $img . '</' . $tag . '>';
          }
        //--- вставляем перед текстом
        //--- нужно в тег p, или отдельной <p>
        if(isset($this->m_params['imagesInText']) && $this->m_params['imagesInText'] == 'on')
          {
          $this->ImageAddIntoText($text, $img);
          }
        else
          {
          $tag = rand(0, 1) ? 'div' : 'p';
          $text .= '<' . $tag . '>' . $img . '</' . $tag . '>' . (rand(0, 1) ? "\r\n" : '');
          }
        //---
        $images_added++;
        }
      }
    }

  /**
   * вставка картинки в текст
   * @param $text
   * @param $img
   */
  private function ImageAddIntoText(&$text, $img)
    {
    //$count = 0;
    $poses = array();
    $len   = strlen($text);
    $i     = 0;
    while($i < $len)
      {
      if($text[$i] == '<')
        {
        $i++;
        while($text[$i] != '>' && $i < $len)
          {
          $i++;
          }
        /*$tag = strtollower($tag);
        //--- если такой тег, то нужно найти закрывающийся
        if($tag=='div' || $tag=='span' || $tag=='strong')
         {
         while($text[$i]!='<' && $text[$i+1]!='/' && $i<$len)
         {
          //--- пропускаем данные в теге
          $i++;
          }

         }
        */
        }
      if($text[$i] == ' ') $poses[] = $i;
      $i++;
      }
    //---
    $c   = rand(0, count($poses) - 1);
    $pos = $poses[$c];
    //--- встка картинки
    if($pos > 0)
      {
      $text = substr($text, 0, $pos) . ' ' . $img . ' ' . substr($text, $pos + 1);
      }
    else $text = $img . $text;
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
   * Получаем рандомную строку
   */
  public function GetRandLine($matches)
    {
    //--- NOTE: поменять бы способ указыват СЕКЦИЮ (да, так они называются) в файлах со строчками.
    //--- NOTE: Вместо [DOGS:] писать [DOGS] (заранее знать список всех макросов)
    //--- NOTE: Но лучше наверно просто макросы писать как {MACROS}, а не [MACROS]
    if(!isset($matches[1]))
      {
      $sz = isset($this->randLines[0]) ? (count($this->randLines[0]) - 1) : -1;
      return $sz >= 0 ? $this->randLines[0][rand(0, $sz)] : null;
      }
    //--- ключ для выбора строки
    $key = $matches[1];
    //---
    if(!isset($this->randLinesIndex[$key])) return null;
    //---
    $num = $this->randLinesIndex[$key];
    $sz  = sizeof($this->randLines[$num]) - 1;
    //---
    return $sz >= 0 ? $this->randLines[$num][rand(0, $sz)] : null;
    }

  /**
   * Получаем рандомную строку из указанного файла
   * @param $matches
   * @return string
   */
  public function GetRandLineFile($matches)
    {
    $filename = $matches[1];
    $filename = trim($filename, '/');
    if(empty($filename))
      {
      CLogger::write(CLoggerType::ERROR, 'texts randline file not exists: ' . $filename);
      return '';
      }
    $filename = self::PATH_RANDLINES . '/' . $filename;
    if(!file_exists($filename))
      {
      CLogger::write(CLoggerType::ERROR, 'texts randline file not exists: ' . $filename);
      return '';
      }
//--- файла нет, то загрузим данные
    if(!isset($this->m_files_index[$filename]))
      {
      $content                        = file_get_contents($filename);
      $this->m_files_index[$filename] = array();
      $list                           = explode("\n", $content);
      $j                              = 0;
      for($i = 0; $i < count($list); $i++)
        {
        $l = trim($list[$i]);
        if(empty($l)) continue;
        //---
        $this->m_files_index[$filename][$j] = $l;
        $j++;
        }
      //---
      CLogger::write(CLoggerType::ERROR, 'texts randline loaded  lines: ' . count($this->m_files_index[$filename]) . ' from file ' . $filename);
      }
    $r = rand(0, count($this->m_files_index[$filename]) - 1);
    if($r < 0) return '';;
    return $this->m_files_index[$filename][$r];
    }

  /**
   * Получаем строку для каждого ключевика из указанного файла
   * @param $matches
   */
  public function GetNextLineFile($matches)
    {
    $filename = $matches[1];
    $filename = trim($filename, '/');
    if(empty($filename))
      {
      CLogger::write(CLoggerType::ERROR, 'texts randline file not exists: ' . $filename);
      return '';
      }
    $filename = self::PATH_RANDLINES . '/' . $filename;
    if(!file_exists($filename))
      {
      CLogger::write(CLoggerType::ERROR, 'texts randline file not exists: ' . $filename);
      return '';
      }
//--- файла нет, то загрузим данные
    if(!isset($this->m_nextfiles_index[$filename]))
      {
      $content                            = file_get_contents($filename);
      $this->m_nextfiles_index[$filename] = array();
      $list                               = explode("\n", $content);
      $j                                  = 0;
      for($i = 0; $i < count($list); $i++)
        {
        $l = trim($list[$i]);
        if(empty($l)) continue;
        //---
        $this->m_nextfiles_index[$filename][$j] = $l;
        $j++;
        }
      //---
      CLogger::write(CLoggerType::ERROR, 'texts nextlines loaded  lines: ' . count($this->m_nextfiles_index[$filename]) . ' from file ' . $filename);
      }
    //---
    if(empty($this->m_nextfiles_index[$filename])) return '';
    //---
    $num = $this->m_current_keyword->getId() % (count($this->m_nextfiles_index[$filename]));
    return $this->m_nextfiles_index[$filename][$num];
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

  /**
   * Случайные строки для рандомного выбора
   */
  private function LoadRandLines()
    {
    //--- получим имя файла
    $fileName = $this->m_params['randLinesFrom'] == 'list' ? self::PATH_RANDLINES . '/' . (empty($this->m_params['randLinesFromList']) ? 'no-files' : $this->m_params['randLinesFromList']) : (isset($_FILES['randLinesFromFile']['tmp_name']) ? $_FILES['randLinesFromFile']['tmp_name'] : '');
    //--- проверим наличие файла
    if(!file_exists($fileName)) return false;
    //--- проверяем файл на UTF-8
    $fileContent = file_get_contents($fileName);
    if(!CModel_tools::IsUTF8($fileContent))
      {
      //--- если надо - перекодируем файл в UTF-8 и сохраняем его
      $fileContent = mb_convert_encoding($fileContent, 'UTF-8', 'WINDOWS-1251');
      }
    //--- получим массив из строк файла
    $lines = explode("\n", $fileContent);
    //--- проверим наличие BOM в первой строке
    if(isset($lines[0]))
      {
      //--- удаляем BOM из первого предложения
      $lines[0] = CModel_tools::RemoveBom($lines[0]);
      }
    $indexNum                   = 0;
    $key                        = '';
    $this->randLinesIndex[$key] = 0;
    //--- пройдемся по всем строчкам
    for($i = 0, $sz = sizeof($lines); $i < $sz; $i++)
      {
      $line = trim($lines[$i]);
      //--- пропускаем пустые строки и комментарии
      if(empty($line) || $line[0] == ';') continue;
      //---
      if(strpos($line, ':]') !== FALSE && preg_match("/^\[[a-z0-9\-]{1,64}\:\]$/i", $line) > 0)
        {
        $key                        = substr($line, 1, strlen($line) - 3);
        $this->randLinesIndex[$key] = ++$indexNum;
        continue;
        }
      //---
      $this->randLines[$indexNum][] = $line;
      }
    //---
    unset($lines, $fileContent, $line);
    return true;
    }

  /**
   * Загрузка текста
   */
  private function LoadText($is_need_print = true)
    {
    //--- получим имя файла
    $fileName = $this->m_params['textFrom'] == 'list' ? self::PATH_TEXTS . '/' . $this->m_params['textFromList'] : $_FILES['textFromFile']['tmp_name'];
    //--- проверим наличие файла
    if($this->m_params['algorithm'] == 'markov')
      {
      //--- подгружаем алгоритм маркова
      global $DATA_MARKOV;
      if(empty($DATA_MARKOV) || !isset($DATA_MARKOV[$fileName]))
        {
        $this->m_text_generator_markov = new CModel_TextMarkov($fileName, $is_need_print, $this->m_params);
        $DATA_MARKOV[$fileName]        = & $this->m_text_generator_markov;
        }
      else
      $this->m_text_generator_markov = $DATA_MARKOV[$fileName];
      //---
      return;
      }
    //---
    if(!file_exists($fileName))
      {
      CLogger::write(CLoggerType::ERROR, 'text: load texts failed, file not exists ' . $fileName);
      return;
      }
    //---
    CLogger::write(CLoggerType::DEBUG, 'text: load texts from ' . $fileName);
    //--- проверим есть ли кеш, и есть ли в кеше данные
    if(extension_loaded('apc'))
      {
      $data = apc_fetch($fileName);
      CLogger::write(CLoggerType::DEBUG, 'text: use apc_fetch: ' . $fileName);
      if(!empty($data))
        {
        $this->texts      = $data;
        $this->textsCount = count($this->texts);
        return true;
        }
      }
    //--- проверяем файл на UTF-8 (TODO: переделать, т.к. будут проблемы с большими файлами)
    $fileContent = file_get_contents($fileName);
    if(!CModel_tools::IsUTF8($fileContent))
      {
      //--- если надо - перекодируем файл в UTF-8 и сохраняем его
      file_put_contents($fileName, mb_convert_encoding($fileContent, 'UTF-8', 'WINDOWS-1251'));
      }
    unset($fileContent);
    //---
    if($this->m_params['algorithm'] == 'markov')
      {
      //--- объект создали нужно просто выйти
      return;
      }
    // TODO: тут надо подумать, выгружать весь файл сразу в память?
    $time_start = time();
    //--- загрузим все предложения из текста в массив
    $text = str_replace(array("\r\n",
                              "\r",
                              "\n"), " ", file_get_contents($fileName));
    $text = str_replace("!", "!. ", $text);
    $text = str_replace("?", "?. ", $text);
    $text = str_replace(": ", ". ", $text);
    $text = str_replace("; ", ". ", $text);
    //---
    $this->texts = explode('. ', $text);
    //---
    $this->textsCount = count($this->texts);
    //--- если есть хотя-бы одно предложение
    if(isset($this->texts[0]))
      {
      //--- удаляем BOM из первого предложения
      $this->texts[0] = CModel_tools::RemoveBom($this->texts[0]);
      }
    if(isset($this->m_params['is_cache']) && $this->m_params['is_cache'] == 1 && extension_loaded('apc'))
      {
      apc_store($fileName, $this->texts, 1200);
      CLogger::write(CLoggerType::DEBUG, 'text: use apc_store: ' . $fileName);
      }
    CLogger::write(CLoggerType::DEBUG, 'text: load texts ' . time() - $time_start . ' seconds, ' . $this->textsCount . ' sentences');
    }

  /**
   * Получить следующее слово
   */
  private function GetNextWord(&$sentenceWords, $next, &$next_i)
    {
    return !empty($sentenceWords[$next_i]) ? $sentenceWords[$next_i] : '';
    }

  /**
   * Настройки для рерайтера
   */
  private function SettingsRewrite()
    {
    $this->m_params['rewriteText'] = (isset($this->m_params['rewriteShake']) && $this->m_params['rewriteShake']) || (isset($this->m_params['rewriteChangeStruct']) && $this->m_params['rewriteChangeStruct']) || (isset($this->m_params['rewriteAdj']) && $this->m_params['rewriteAdj']);
    //--- шей не установлен
    if(!(isset($this->m_params['rewriteShake']) && $this->m_params['rewriteShake']))
      {
      $this->m_params['shakeFrom'] = 0;
      $this->m_params['shakeTo']   = 0;
      }
    //--- изменение структуры
    if(!(isset($this->m_params['rewriteChangeStruct']) && $this->m_params['rewriteChangeStruct']))
      {
      $this->m_params['changestructurFrom'] = 0;
      $this->m_params['changestructurTo']   = 0;
      }
    //--- прилагательные
    if(!(isset($this->m_params['rewriteAdj']) && $this->m_params['rewriteAdj']))
      {
      $this->m_params['adjFrom'] = 0;
      $this->m_params['adjTo']   = 0;
      }
    }

  /**
   * Закрыть файлы
   */
  public function Close()
    {
    //--- если запускали синонимайзер, нужно все закрыть
    if($this->m_synonimazer != null) $this->m_synonimazer->CloseAll();
    //--- если запускали рерайтор, нужно все закрыть
    if($this->m_rewrite != null) $this->m_rewrite->CloseAll();
    }

  public function GetParagraphText($text, $minSentenseParagraph, $maxSentenseParagraph)
    {
    //--- загрузим все предложения из текста в массив
    $text = str_replace(array("\r\n",
                              "\r",
                              "\n"), " ", $text);
    $text = str_replace(". ", ".. ", $text);
    $text = str_replace("!", "!. ", $text);
    $text = str_replace("?", "?. ", $text);
    $text = str_replace(": ", ". ", $text);
    $text = str_replace("; ", ". ", $text);
    //---
    $texts  = explode('. ', $text);
    $i      = 0;
    $count  = rand($minSentenseParagraph, $maxSentenseParagraph);
    $result = '<p>';
    foreach($texts as $t)
      {
      $result .= ' ' . $t;
      $i++;
      if($i == $count)
        {
        $result .= "</p><p>";
        $count = rand($minSentenseParagraph, $maxSentenseParagraph);
        $i     = 0;
        }
      }
    $result .= '</p>';
    return $result;
    }
  }