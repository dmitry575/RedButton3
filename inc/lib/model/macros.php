<?php
//+------------------------------------------------------------------+
//|                  Copyright c 2012, RedButton Web Sites Generator |
//|                                      http://www.getredbutton.com |
//+------------------------------------------------------------------+
/**
 * Обработка различных макросов

 */
class CModel_macros
  {
  /**
   * основной путь до картинок
   */
  const PATH_IMAGES = 'data/images';
  /**
   * разрешенные расширения для картинок
   * @var array
   */
  private $m_exts_images = array('jpg',
                                 'jpeg',
                                 'gif',
                                 'png');
  /**
   * параметры
   * @var array
   */
  private $m_params;
  /**
   * модель для работы с настройками
   * @var CModel_settings
   */
  private $m_model_settings;
  /**
   * модель для работы с кейвордами
   * @var CModel_keywords
   */
  private $m_model_keywords;
  /**
   * модель для работы с текстами
   * @var CModel_text
   */
  private $m_model_text;
  /**
   * @var CModel_links
   */
  private $m_model_links;
  /**
   * @var CModel_template
   */
  private $m_template_module;
  /**
   * текущий кейворд
   * @var string
   */
  private $m_current_keyword;
  /**
   * текущий кейворд
   * @var CKeywordInfo
   */
  private $m_current_keyword_info;
  /**
   * Транслит текущего кейворда
   * @var string
   */
  private $m_keyword_translit;
  /**
   * текущий кейворд
   * @var string
   */
  private $m_keyword_id;
  /**
   * Настройки для картинок
   * @var int
   */
  private $m_options_images;
  /**
   * @var string
   */
  private $localPath;
  /**
   * модель для управления картинками
   * @var CModel_ImageChange
   */
  private $m_model_image;
  /**
   * модель для управления картинками
   * @var CModel_Video
   */
  private $m_model_video;
  /**
   * список путей для картинки
   * @var array
   */
  private $m_list_images;
  /**
   * список соотношения файла и ключевика
   * @var array
   */
  private $m_images_keywords;
  /**
   * Текущая папка откуда брать картинки для тегов RAND-IMG, GEN-IMG
   * @var string
   */
  private $m_current_image_path;
  /**
   * облаго тегов
   * @var
   */
  private $m_tags_result;
  /**
   * нужно если из файла подсунут хрень с макросами
   * @var bool
   */
  private $m_exists_macroces;
  /**
   * Модуль для картинок
   * @var CModel_ImagesUrls
   */
  private $m_model_imagesurls;

  /**
   * конструктор
   * @param array $params
   * @param CModel_keywords $model_keywords
   * @param CModel_text $model_text
   * @param CModel_links $model_links
   * @param                             $model_settings
   * @param CModel_Video $model_video
   * @param CModel_template $template_module
   * @param string $image_temp_path
   * @param string $localPath
   */
  public function __construct(&$params, &$model_keywords, &$model_text, &$model_links, &$model_settings, &$model_video, &$template_module, &$image_temp_path, &$localPath)
    {
    $this->m_params = $params;
    //--- ключевики
    $this->m_model_keywords  = $model_keywords;
    $this->m_model_text      = $model_text;
    $this->m_model_links     = $model_links;
    $this->m_model_settings  = $model_settings;
    $this->m_template_module = $template_module;
    $this->m_model_video     = $model_video;
    //---
    $this->m_model_imagesurls = new CModel_ImagesUrls();
    //--- текущая папка для картинок уже в самом дорвее
    $this->m_image_cur_path = $image_temp_path;
    //--- получение настроек
    $this->m_options_images = $this->GetOptionsImages();
    //--- путь
    $this->localPath = $localPath;
    //--- копируем в новое место картинку
    $this->m_model_image = new CModel_ImageChange();
    //---
    if(empty($this->m_params['loadImagePath'])) $this->m_current_image_path = '/';
    else  $this->m_current_image_path = '/' . trim($this->m_params['loadImagePath'], '/') . '/';
    }

  /**
   * Обработка текстового макроса вида [утро|вечер|день]
   * @param array $matches
   *
   * @return string
   */
  public function GetTextMacros($matches)
    {
    if(empty($matches[1])) return '';
    //--- разделяем
    $ar = explode('|', $matches[1]);
    //--- количество элементов в массиве
    $max = count($ar);
    if($max <= 1) return $ar[0];
    //--- берем случайный из списка
    return $ar[rand(0, $max - 1)];
    }

  /**
   * Замена макросов в самом контенте
   * @param string $page
   * @param int $keyword_id
   *
   * @return string
   */
  public function ReplaceManyMacros($page, $keyword_id)
    {
    $this->m_keyword_id           = $keyword_id;
    $keyword_info                 = $this->m_model_keywords->GetKeywordByNum($keyword_id);
    $this->m_current_keyword_info = $keyword_info;
    $this->m_current_keyword      = $keyword_info->getKeywordIndex(0);
    $this->m_keyword_translit     = CModel_tools::Translit($this->m_current_keyword);
    $this->m_model_text->setCurrentKeyword($keyword_info);
    $this->ReplaceAllMacros($page);
//---
    if(strpos($page, '[DOITSREL') !== FALSE)
      {
      $this->ReplacesDoitsBlock($page, $keyword_info);
      }
    //---
    return $page;
    }

  /**
   * Замена DOITSREL- блоков
   *
   * @param $page
   */
  private function ReplacesDoitsBlock(&$page, $keyword_info)
    {
//--- находим DOITS-REL и получаем его индекс
    while(($pos = strpos($page, '[DOITSREL-')) !== FALSE)
      {
      $end_pos = strpos($page, ']', $pos + 10); // +10 длина [DOITSREL-
      //---
      if($end_pos === FALSE) continue;
      //--- получим индекс для получения данных
      $dotrel = substr($page, $pos, $end_pos - $pos + 1);
      $this->GetDoitRelIndex($dotrel, $index, $rel_index);
      if(empty($index))
        {
        $page = substr($page, 0, $pos) . '' . substr($page, $end_pos + 1);
        continue;
        }
      //--- получем информацию о блок
      $block_info = $this->m_template_module->GetDoitsRelBlock($index, $rel_index);
      if(empty($block_info))
        {
        $page = substr($page, 0, $pos) . '' . substr($page, $end_pos + 1);
        continue;
        }
      //--- найдем сколько нужно повторять данный блок
      $new_text = $this->ReplaceRelDoitBlock($block_info, $keyword_info);
      $page     = substr($page, 0, $pos) . $new_text . substr($page, $end_pos + 1);
      }
    }

  /**
   * Получаем для данного блока, замененные макросы
   * @param $block_info
   */
  private function ReplaceRelDoitBlock($block_info, $keyword_info)
    {
    //--- сколько нужно будет блоков
    //--- выставляем текущий ключевик и данные
    $count = rand($block_info['min'], $block_info['max']);
    $count = min($count, $keyword_info->GetCount());
    $id    = $keyword_info->GetCount() > 1 ? 1 : 0;
    //---
    $text = '';
    for($i = 0; $i < $count; $i++)
      {
      //--- выставляем текущий ключевик и данные
      $this->m_current_keyword  = $keyword_info->getKeywordIndexNext($id, $id);
      $this->m_keyword_translit = CModel_tools::Translit($this->m_current_keyword);
      $this->m_model_text->setCurrentKeyword(new CKeywordInfo($keyword_info->getId(), $this->m_current_keyword, $keyword_info->getUrl()));
//---
      $new_text = $block_info['content'];
      $this->ReplaceAllMacros($new_text);
      $text .= $new_text;
      }
    return $text;
    }

  /**
   * Получаем данные по индексу
   * @param $text
   * @param $len
   * @param $index
   * @param $id
   *
   * @return int
   */
  private function GetDoitRelIndex($text, &$index, &$id)
    {
    if(preg_match('/^\[DOITSREL\-([0-9a-zA-Z]+)\-([0-9a-zA-Z]+)\]$/', $text, $matches))
      {
      $index = $matches[1];
      $id    = $matches[2];
      return true;
      }
    return false;
    }

  /**
   * Замена всех макросов в тексте
   * @param $page
   */
  private function ReplaceAllMacros(&$page, $level = 0)
    {
//---
    $page = str_replace('[TITLE]', $this->m_params['pageTitle'], $page);
    $page = str_replace('[KEYWORDS]', $this->m_model_keywords->GetRandKeywords($this->m_keyword_id), $page);
    $page = str_replace('[DESCRIPTION]', $this->m_params['metaDescription'], $page);
    //--- нужно если из файла подсунут хрень с макросами
    $this->m_exists_macroces = false;
    //--- приоритетный макрос случайных строчек
    $page = preg_replace_callback('/\[RANDLINE\]/i', array($this,
                                                           'GetRandLine'), $page);
    //--- еще один приоритетный макрос случайных строчек
    $page = preg_replace_callback('/\[RANDLINE\-([a-z0-9\-]{1,64})\]/i', array($this,
                                                                               'GetRandLine'), $page);
    //--- еще макрос случайных строчек, берем из файла
    $page = preg_replace_callback('/\[RANDLINE\-([a-z0-9\-\/]{1,64}\.[a-z0-9]{1,64})\]/i', array($this,
                                                                                                 'GetRandLineFile'), $page);
    //--- еще макрос случайных строчек, берем из файла
    $page = preg_replace_callback('/\[NEXTLINE\-([a-z0-9\-\/]{1,64}\.[a-z0-9]{1,64})\]/i', array($this,
                                                                                                 'GetNextLineFile'), $page);
    //--- если в предыдущих макросах получили еще, то нужно еще раз все вызвать. Но только еще один раз
    //--- иначе скатимся в рекурсию
    if($this->m_exists_macroces && $level <= 0) $this->ReplaceAllMacros($page, $level + 1);
    $page = preg_replace_callback('/\[KEYWORDS\-([0-9]{1,})\-([0-9]{1,})\]/', array($this,
                                                                                    'GetRandKeywordsCallback'), $page);
    $page = preg_replace_callback('/\[KEYWORDS\-([0-9]{1,})\-([0-9]{1,})([a-z0-9\;\.\-_\|\_=]*)\]/i', array($this,
                                                                                                            'GetRandKeywordsFromFilesCallback'), $page);
    //--- заменяем остальные макросы
    //--- ссылка на внешний ресурс
    $page = str_replace('[GO-LINK-HTML]', $this->m_params['goLinkHtml'], $page);
    $page = str_replace('[GO-URL]', $this->m_params['linksGo'], $page);
    $page = str_replace('[KEYWORD]', $this->m_current_keyword, $page);
    $page = str_replace('[KEYWORD-FOR-URL]', urlencode($this->m_current_keyword), $page);
    $page = str_replace('[UC-KEYWORD]', CModel_helper::GetUcFirst($this->m_current_keyword), $page);
    $page = str_replace('[UCW-KEYWORD]', CModel_helper::GetUcWords($this->m_current_keyword), $page);
    $page = str_replace('[HOME-URL]', $this->m_params['nextUrl'], $page);
    $page = str_replace('[PAGE-URL]', $this->m_current_keyword_info->getUrl(), $page);
    //---
    $page = str_replace('[SITE-URL]', $this->m_params['clearnextUrl'], $page);
    //--- sitemap
    $page = str_replace('[SITEMAP-CONTENT]', '', $page);
    //---
    CLogger::write(CLoggerType::DEBUG, 'base replaced');
    //---
    $page = preg_replace_callback('/\[TEXT\-([0-9]{1,})\-([0-9]{1,})([0-9a-zA-Z\_\-\.\=\;]{1,})\]/', array($this->m_model_text,
                                                                                                           'GetTextParams'), $page);
    //---
    $page = preg_replace_callback('/\[TEXT\-([0-9]{1,})\-([0-9]{1,})\]/', array($this->m_model_text,
                                                                                'GetText'), $page);
    //---
    $page = preg_replace_callback('/\[TEXT\-NOKEY\-([0-9]{1,})\-([0-9]{1,})\]/', array($this->m_model_text,
                                                                                       'GetTextNoKey'), $page);
    //---
    $page = preg_replace_callback('/\[TEXT\-CLEAR\-([0-9]{1,})\-([0-9]{1,})\]/', array($this->m_model_text,
                                                                                       'GetTextClear'), $page);
    //---
    $page = preg_replace_callback('/\[TEXT\-CLEAR\-NOKEY\-([0-9]{1,})\-([0-9]{1,})\]/', array($this->m_model_text,
                                                                                              'GetTextClearNoKey'), $page);
    //--- текст из указанного файла
    $page = preg_replace_callback('/\[TEXT\-([0-9]{1,})\-([0-9]{1,})([0-9a-zA-Z\_\-\.\=\;]{1,})\]/', array($this->m_model_text,
                                                                                                           'GetTextFromFile'), $page);
    //---
    $page = preg_replace_callback('/\[TEXT\-NOKEY\-([0-9]{1,})\-([0-9]{1,})([0-9a-zA-Z\_\-\.\=\;]{1,})\]/', array($this->m_model_text,
                                                                                                                  'GetTextNoKeyFromFile'), $page);
    //---
    $page = preg_replace_callback('/\[TEXT\-CLEAR\-([0-9]{1,})\-([0-9]{1,})([0-9a-zA-Z\_\-\.\=\;]{1,})\]/', array($this->m_model_text,
                                                                                                                  'GetTextClearFromFile'), $page);
    //---
    $page = preg_replace_callback('/\[TEXT\-CLEAR\-NOKEY\-([0-9]{1,})\-([0-9]{1,})([0-9a-zA-Z\-\.\=\;]{1,})\]/', array($this->m_model_text,
                                                                                                                       'GetTextClearNoKeyFromFile'), $page);
    //---
    $page = preg_replace_callback('/\[RAND\-KEYWORD\]/', array($this,
                                                               'GetRandKeywordCallback'), $page);
    //---
    $page = preg_replace_callback('/\[RAND\-UC\-KEYWORD\]/', array($this,
                                                                   'GetRandUcKeyword'), $page);
    //--- обработка мультикеев
    $page = preg_replace_callback('/\[MULTIKEYWORD\-([0-9]{1,})\]/', array($this,
                                                                           'GetMultiKeyword'), $page);
    //---
    $page = preg_replace_callback('/\[MULTIKEYWORD\-N\-([0-9]{1,})([0-9a-zA-Z\-\.\;\=]{0,})\]/', array($this,
                                                                                                       'GetMultiKeywordRand'), $page);
    $page = preg_replace_callback('/\[MULTIKEYWORD\-([0-9]{1,})\-([0-9]{1,})([0-9a-zA-Z\-\.\;\=]{0,})\]/', array($this,
                                                                                                                 'GetMultiKeywordNumber'), $page);
    //--- ключевики из других файлов
    $page = preg_replace_callback('/\[RAND\-KEYWORD([0-9a-zA-Z\-\.\;\=]{1,})\]/', array($this,
                                                                                        'GetRandKeywordFromFileCallback'), $page);
    //---
    $page = preg_replace_callback('/\[RAND\-UC\-KEYWORD([0-9a-zA-Z\-\.\;\=]{1,})\]/', array($this,
                                                                                            'GetRandUcKeywordFromFile'), $page);
    //---
    $page = preg_replace_callback('/\[RAND\-URL\]/', array($this,
                                                           'GetRandUrl'), $page);
//--- из файла
    $page = preg_replace_callback('/\[RAND\-URL([0-9a-zA-Z\-\.\=\;]{1,})\]/', array($this,
                                                                                    'GetRandUrlFromFile'), $page);
    //---
    $page = preg_replace_callback('/\[RAND\-LINK\]/', array($this,
                                                            'GetRandLink'), $page);
    //---
    $page = preg_replace_callback('/\[RAND\-LINK([0-9a-zA-Z\-\.\=\;]{1,})\]/', array($this,
                                                                                     'GetRandLinkFromFile'), $page);
    //---
    $page = preg_replace_callback('/\[RAND\-UC\-LINK\]/', array($this,
                                                                'GetRandUcLink'), $page);
    //---
    $page = preg_replace_callback('/\[RAND\-UC\-LINK\-([0-9]{1,})\-([0-9]{1,})\]/', array($this,
                                                                                          'GetRandUcLinks'), $page);
    //--- NOTE: эта проверка всегда должна быть последней в серии RAND-UC-LINK !!
    $page = preg_replace_callback('/\[RAND\-UC\-LINK([0-9a-zA-Z\-\.\=\;]{1,})\]/', array($this,
                                                                                         'GetRandUcLinkFromFile'), $page);
    //---  гиперссылка с кейвордом на след. страницу
    $page = preg_replace_callback('/\[NEXT\-LINK\]/', array($this,
                                                            'GetNextLink'), $page);
    //--- гиперссылка с кейвордом на пред. страницу
    $page = preg_replace_callback('/\[PREV\-LINK\]/', array($this,
                                                            'GetPrevLink'), $page);
    //--- гиперссылка с кейвордом с большой буквы на след. страницу
    $page = preg_replace_callback('/\[NEXT\-UC\-LINK\]/', array($this,
                                                                'GetNextUcLink'), $page);
    //---гиперссылка с кейвордом с большой буквы на пред. страницу
    $page = preg_replace_callback('/\[PREV\-UC\-LINK\]/', array($this,
                                                                'GetPrevUcLink'), $page);
    //--- гиперссылка с кейвордом, все слова которого  начинаются с большой буквы, на след.  страницу
    $page = preg_replace_callback('/\[NEXT\-UCW\-LINK\]/', array($this,
                                                                 'GetNextUcwLink'), $page);
    //--- гиперссылка с кейвордом, все слова которого начинаются с большой буквы, на пред. страницу
    $page = preg_replace_callback('/\[PREV\-UCW\-LINK\]/', array($this,
                                                                 'GetPrevUcwLink'), $page);
    //--- гиперссылка с кейвордом с большой буквы на след. страницу
    $page = preg_replace_callback('/\[NEXT\-UC\-LINK\-([0-9]{1,})]/', array($this,
                                                                            'GetNextUcLinksNumbers'), $page);
    //---гиперссылка с кейвордом с большой буквы на пред. страницу
    $page = preg_replace_callback('/\[PREV\-UC\-LINK\-([0-9]{1,})]/', array($this,
                                                                            'GetPrevUcLinksNumbers'), $page);
    //--- гиперссылка с кейвордом, все слова которого  начинаются с большой буквы, на след.  страницу
    $page = preg_replace_callback('/\[NEXT\-UCW\-LINK\-([0-9]{1,})]/', array($this,
                                                                             'GetNextUcwLinksNumbers'), $page);
    //--- гиперссылка с кейвордом, все слова которого начинаются с большой буквы, на пред. страницу
    $page = preg_replace_callback('/\[PREV\-UCW\-LINK\-([0-9]{1,})]/', array($this,
                                                                             'GetPrevUcwLinksNumbers'), $page);
    //--- просто ссылка на след. страницу
    $page = preg_replace_callback('/\[NEXT\-URL\]/', array($this,
                                                           'GetNextUrl'), $page);
    //--- просто ссылка на пред. страницу
    $page = preg_replace_callback('/\[PREV\-URL\]/', array($this,
                                                           'GetPrevUrl'), $page);
    //---
    $page = preg_replace_callback('/\[UC\-LINK\-([0-9]{1,})\]/', array($this,
                                                                       'GetUcLink'), $page);
    //--- картинки
    $page = preg_replace_callback('/\[RAND\-IMG\]/', array($this,
                                                           'GetRandImg'), $page);
    //---
    $page = preg_replace_callback('/\[RAND\-IMG\-([0-9]{1,})\-([0-9]{1,})\]/', array($this,
                                                                                     'GetRandImgResize'), $page);
    //---
    $page = preg_replace_callback('/\[RAND\-IMG\-([0-9a-zA-Z]{1,})\]/', array($this,
                                                                              'GetRandImgFrom'), $page);
    //---
    $page = preg_replace_callback('/\[RAND\-IMG\-([0-9a-zA-Z]{1,})\-([0-9]{1,})\-([0-9]{1,})\]/', array($this,
                                                                                                        'GetRandImgResizeFrom'), $page);
    //---
    $page = preg_replace_callback('/\[RAND\-IMG\-URLONLY\-([0-9a-zA-Z\-\.]{1,})\]/', array($this,
                                                                                           'GetRandImgUrlOnlyFromFileurl'), $page);
    //---
    $page = preg_replace_callback('/\[RAND\-IMG\-URL\-([0-9a-zA-Z\-\.]{1,})\]/', array($this,
                                                                                       'GetRandImgUrlFromFileurl'), $page);
    //---
    $page = preg_replace_callback('/\[GEN\-IMG\]/', array($this,
                                                          'GetGenerateImg'), $page);
    //---
    $page = preg_replace_callback('/\[GEN\-IMG\-([0-9]{1,})\-([0-9]{1,})\]/', array($this,
                                                                                    'GetGenerateImgResize'), $page);
    //---
    $page = preg_replace_callback('/\[GEN\-IMG\-([0-9a-zA-Z]{1,})\]/', array($this,
                                                                             'GetGenerateImgFrom'), $page);
    //---
    $page = preg_replace_callback('/\[GEN\-IMG\-([0-9a-zA-Z]{1,})\-([0-9]{1,})\-([0-9]{1,})\]/', array($this,
                                                                                                       'GetGenerateImgResizeFrom'), $page);
    //--- видо
    $page = preg_replace_callback('/\[RAND\-VIDEO\-([0-9a-zA-Z\.]{1,})\-([0-9]{1,})\-([0-9]{1,})\]/', array($this,
                                                                                                            'GetRandVideoSize'), $page);
    $page = preg_replace_callback('/\[RAND\-VIDEO\-([0-9a-zA-Z\.]{1,})\]/', array($this,
                                                                                  'GetRandVideo'), $page);
    //--- ссылка на видео
    $page = preg_replace_callback('/\[RAND\-VIDEO\-URL\-([0-9a-zA-Z\.]{1,})\]/', array($this,
                                                                                       'GetRandVideoUrl'), $page);
    //---
    $page = preg_replace_callback('/\[NICK\]/', array($this,
                                                      'GetNick'), $page);
    //---
    $page = preg_replace_callback('/\[MENU\-CATEGORY\]/', array($this,
                                                                'GetMenuCategory'), $page);
    //---
    $page = preg_replace_callback('/\[CATEGORY\-LINKS\]/', array($this,
                                                                 'GetSubCategoryLinks'), $page);
    //---
    $page = preg_replace_callback('/\[CATEGORY\-RAND\-LINKS\-([0-9]{1,})\-([0-9]{1,})\]/', array($this,
                                                                                                 'GetSubCategoryRandLinks'), $page);
    $page = preg_replace_callback('/\[CATEGORY\-RAND\-LINK]/', array($this,
                                                                 'GetCategoryRandLink'), $page);
    $page = preg_replace_callback('/\[CATEGORY\-RAND\-NAME]/', array($this,
                                                                 'GetCategoryRandName'), $page);
    //---
    $page = preg_replace_callback('/\[TAGS\]/', array($this,
                                                      'GetTags'), $page);
    //---
    $page = preg_replace('/\[N\]/', $this->m_keyword_id, $page);
    //---
    $page = preg_replace_callback('/\[RAND\-([0-9]{1,})\-([0-9]{1,})\]/', array($this,
                                                                                'GetRandInt'), $page);
    //---
    $page = preg_replace_callback('/\[ENCODE\-RAND\-KEY\]/', array($this,
                                                                   'GetEcodeRandKey'), $page);
    //---
    $page = preg_replace_callback('/\[ENCODE\-CURRENT\-KEY\]/', array($this,
                                                                      'GetEcodeCurrentKey'), $page);
    //--- случайная ссылка (просто ссылка) для спама на сгенеренную страницу дорвея текущей тематики
    $page = preg_replace_callback('/\[RAND\-SPAM\-URL\]/', array($this->m_model_links,
                                                                 'MacrosSpamUrl'), $page);
    //--- случайная гиперссылка для спама
    $page = preg_replace_callback('/\[RAND\-SPAM\-LINK\]/', array($this->m_model_links,
                                                                  'MacrosSpamLink'), $page);
    //--- случайная гиперссылка для спама, где кейворд с большой буквы
    $page = preg_replace_callback('/\[RAND\-SPAM\-UC\-LINK\]/', array($this->m_model_links,
                                                                      'MacrosSpamUCLink'), $page);
    //--- список (<ul>) случайных гиперссылок для спама, где кейворд с большой буквы
    $page = preg_replace_callback('/\[RAND\-SPAM\-UC\-LINK\-([0-9]{1,})\-([0-9]{1,})\]/', array($this->m_model_links,
                                                                                                'MacrosSpamUCManyLinks'), $page);
    //--- Обработка текстового макроса вида [утро|вечер|день]
    $page = preg_replace_callback('/\[([^\[\]]{1,}\|[^\[\]]{1,})\]/', array($this,
                                                                            'GetTextMacros'), $page);
    }

  //--- приоритетный макрос случайных строчек
  private function GetRandLine($matches)
    {
    $s = $this->m_model_text->GetRandLine($matches);
    if(!$this->m_exists_macroces) if(strpos($s, '[') !== FALSE && strpos($s, ']') !== FALSE) $this->m_exists_macroces = true;
    return $s;
    }

//--- еще макрос случайных строчек, берем из файла
  private function GetRandLineFile($matches)
    {
    $s = $this->m_model_text->GetRandLineFile($matches);
    if(!$this->m_exists_macroces) if(strpos($s, '[') !== FALSE && strpos($s, ']') !== FALSE) $this->m_exists_macroces = true;
    return $s;
    }

//--- еще макрос случайных строчек, берем из файла
  private function GetNextLineFile($matches)
    {
    $s = $this->m_model_text->GetNextLineFile($matches);
    if(!$this->m_exists_macroces) if(strpos($s, '[') !== FALSE && strpos($s, ']') !== FALSE) $this->m_exists_macroces = true;
    return $s;
    }

  /**
   * Просто берем из папки случайную картинку
   */
  public function GetRandImg()
    {
    $image_file = $this->GetImageFilenameByKeyword($this->m_current_image_path);
    if(empty($image_file)) return '';
    //--- новое имя файла
    $new_image_file = $this->GetRandomImageFilename($image_file);
    //--- текущее имя картинки
    $cur_image_name = self::PATH_IMAGES . $this->m_current_image_path . $image_file;
    //--- копируем в новое место картинку
    $new_image_file = CTools_files::CopyFileReplace($cur_image_name, $this->localPath . '/' . $this->m_image_cur_path, $new_image_file);
    //--- сохраняем или просто возвращаем данные, зависит это динамика или статика
    return $this->GetImageSrcForPage('rand-img', pathinfo($new_image_file, PATHINFO_BASENAME), getimagesize($cur_image_name));
    }

  /**
   * Берем случайную картинку и изменяем ей размер указанный в $matches
   * @param array $matches
   *
   * @return string
   */
  public function GetRandImgResize($matches)
    {
    $image_file = $this->GetImageFilenameByKeyword($this->m_current_image_path);
    if(empty($image_file)) return '';
    //--- новое имя файла
    $new_image_file = $this->GetRandomImageFilename($image_file);
    //--- текущее имя картинки
    $cur_image_name = self::PATH_IMAGES . $this->m_current_image_path . $image_file;
    //--- имя и папку куда копировать
    $new_image_fullname = CTools_files::GetNewFilenameNotReplace($cur_image_name, $this->localPath . '/' . $this->m_image_cur_path, $new_image_file);
    //--- нужно ресайзить и соблюдать пропорцию
    $options = CModel_ImageChange::RESIZE_RANDOM | CModel_ImageChange::RESIZE_PROPORTION;
    //--- копируем в новое место картинку
    $this->m_model_image->SetNewParams(array((int)$matches[1],
                                             (int)$matches[2]));
    //--- обработаем картинку и сохраним в нужном месте
    if($this->m_model_image->ChangeImage($cur_image_name, $new_image_fullname, $options))
      {
      //--- сохраняем или просто возвращаем данные, зависит это динамика или статика
      return $this->GetImageSrcForPage('rand-img-number', pathinfo($new_image_fullname, PATHINFO_BASENAME), getimagesize($new_image_fullname));
      }
    return '';
    }

  /**
   * Берем картинку из указанного каталога в $matches[1]
   * @param array $matches
   */
  public function GetRandImgFrom($matches)
    {
    //--- подпапка откуда брать картинки
    $path       = $matches[1];
    $image_file = $this->GetImageFilenameByKeyword($path);
    if(empty($image_file)) return '';
    //--- новое имя файла
    $new_image_file = $this->GetRandomImageFilename($image_file);
    //--- текущее имя картинки
    $cur_image_name = self::PATH_IMAGES . '/' . $path . '/' . $image_file;
    //--- копируем в новое место картинку
    $new_image_file = CTools_files::CopyFileReplace($cur_image_name, $this->localPath . '/' . $this->m_image_cur_path, $new_image_file);
    //--- сохраняем или просто возвращаем данные, зависит это динамика или статика
    return $this->GetImageSrcForPage('rand-img-' . $path, pathinfo($new_image_file, PATHINFO_BASENAME), getimagesize($cur_image_name));
    }

  /**
   * Обработка макроса IMG-RAND-URL-имя
   *
   * @param array $matches
   */
  public function GetRandImgUrlFromFileurl($matches)
    {
    //--- имя файла для где берем урлы
    $fname = CModel_ParserImage::IMAGES_FILE_PATH . $matches[1];
    //--- название файла для картинки
    $img_name = $this->m_keyword_translit;
    //--- получение урла картинки
    return $this->m_params['nextUrl'] . $this->m_model_imagesurls->GetRandUrlName($fname, $img_name);
    }

  /**
   * Обработка макроса IMG-RAND-URLONLY-имя
   * @param $matches
   * @return string
   */
  public function GetRandImgUrlOnlyFromFileurl($matches)
    {
    //--- имя файла для где берем урлы
    $fname = CModel_ParserImage::IMAGES_FILE_PATH . $matches[1];
    //--- получение урла картинки
    return $this->m_model_imagesurls->GetRandUrl($fname);
    }

  /**
   * Берем картинку из указанного каталога в $matches[1] и изменяем на указанные размеры
   * @param array $matches
   */
  public function GetRandImgResizeFrom($matches)
    {
    $path = $matches[1];
    //---
    $image_file = $this->GetImageFilenameByKeyword($path);
    if(empty($image_file)) return '';
    //--- новое имя файла
    $new_image_file = $this->GetRandomImageFilename($image_file);
    //--- текущее имя картинки
    $cur_image_name = self::PATH_IMAGES . '/' . $path . '/' . $image_file;
    //--- имя и папку куда копировать
    $new_image_fullname = CTools_files::GetNewFilenameNotReplace($cur_image_name, $this->localPath . '/' . $this->m_image_cur_path, $new_image_file);
    //--- нужно ресайзить и соблюдать пропорцию
    $options = CModel_ImageChange::RESIZE_RANDOM | CModel_ImageChange::RESIZE_PROPORTION;
    //--- копируем в новое место картинку
    $this->m_model_image->setRandowWidth(array((int)$matches[2],
                                               (int)$matches[3]));
    //--- обработаем картинку и сохраним в нужном месте
    if($this->m_model_image->ChangeImage($cur_image_name, $new_image_fullname, $options))
      {
      //--- сохраняем или просто возвращаем данные, зависит это динамика или статика
      return $this->GetImageSrcForPage('rand-img-number-' . $path, pathinfo($new_image_fullname, PATHINFO_BASENAME), getimagesize($new_image_fullname));
      }
    return '';
    }

  /**
   * Получение имени файл по ключу из списка возможных
   * @param string $path путь откуда файлы с картинками
   *
   * @return string
   */
  private function GetImageFilenameByKeyword($path)
    {
    //---
    if(!$this->CheckLoadImagesFiles($path)) return '';
    //--- получили имя файла
    $list_images =& $this->m_list_images[$path];
    //--- пробежимся и найдем похожие на ключевые слова картинки и их вернем
    $ar = array();
    foreach($list_images as $img_name)
      {
      if(strpos($img_name, $this->m_keyword_translit) !== FALSE) $ar[] = $img_name;
      }
    //--- проверим в новом массеве с картинками, что-нибудь есть
    $listSize = sizeof($ar) - 1;
    if($listSize > -1)
      {
      $rand_key = rand(0, $listSize);
      $name     = $ar[$rand_key];
      //---
      CLogger::write(CLoggerType::DEBUG, 'img: file: ' . $name . ' keyword translit: ' . $this->m_keyword_translit);
      return $name;
      }
    //--- ничего не нашли подходящего для данного ключевого слова, ищем случайно
    $listSize = sizeof($list_images) - 1;
    if($listSize < 0) return '';
    //---
    $rand_key = rand(0, $listSize);
    return $list_images[$rand_key];
    }

  /**
   * Берем случайную картинку из каталога и обрабатываем ее в зависимости от настроек
   * @param array $matches
   */
  public function GetGenerateImg()
    {
    $image_file = $this->GetImageFilenameByKeyword($this->m_current_image_path);
    if(empty($image_file)) return '';
    //---
    CLogger::write(CLoggerType::DEBUG, 'gen-img: file: ' . $image_file);
    //--- новое имя файла
    $new_image_file = $this->GetRandomImageFilename($image_file);
    //--- текущее имя картинки
    $cur_image_name = self::PATH_IMAGES . $this->m_current_image_path . $image_file;
    //--- имя и папку куда копировать
    $new_image_fullname = CTools_files::GetNewFilenameNotReplace($cur_image_name, $this->localPath . '/' . $this->m_image_cur_path, $new_image_file);
    $this->m_model_image->setRandowWidth(array(100,
                                               200));
    //--- установка водяного знака
    if(($this->m_options_images & CModel_ImageChange::WATERMARK) > 0) $this->m_model_image->setWatermark($this->m_params['imageWatermarkStr'] == '[KEYWORD]' ? $this->m_current_keyword : $this->m_params['imageWatermarkStr']);
    //--- обработаем картинку и сохраним в нужном месте
    if($this->m_model_image->ChangeImage($cur_image_name, $new_image_fullname, $this->m_options_images))
      {
      //--- сохраняем или просто возвращаем данные, зависит это динамика или статика
      return $this->GetImageSrcForPage('gen-img', pathinfo($new_image_fullname, PATHINFO_BASENAME), getimagesize($new_image_fullname));
      }
    return '';
    }

  /**
   * Получение урла для картинки, которая будет сгенерирована
   * @param $matches
   *
   * @return string
   */
  public function GetGenerateImgResize($matches)
    {
    $image_file = $this->GetImageFilenameByKeyword($this->m_current_image_path);
    if(empty($image_file)) return '';
    //---
    CLogger::write(CLoggerType::DEBUG, 'gen-img-resize: file: ' . $image_file);
    //--- новое имя файла
    $new_image_file = $this->GetRandomImageFilename($image_file);
    //--- текущее имя картинки
    $cur_image_name = self::PATH_IMAGES . $this->m_current_image_path . $image_file;
    //--- имя и папку куда копировать
    $new_image_fullname = CTools_files::GetNewFilenameNotReplace($cur_image_name, $this->localPath . '/' . $this->m_image_cur_path, $new_image_file);
    //--- копируем в новое место картинку
    $this->m_model_image->setRandowWidth(array((int)$matches[1],
                                               (int)$matches[2]));
    //--- установка водяного знака
    if(($this->m_options_images & CModel_ImageChange::WATERMARK) > 0) $this->m_model_image->setWatermark($this->m_params['imageWatermarkStr'] == '[KEYWORD]' ? $this->m_current_keyword : $this->m_params['imageWatermarkStr']);
    //--- обработаем картинку и сохраним в нужном месте
    //--- нужно ресайзить и соблюдать пропорцию
    if($this->m_model_image->ChangeImage($cur_image_name, $new_image_fullname, $this->m_options_images | CModel_ImageChange::RESIZE_RANDOM | CModel_ImageChange::RESIZE_PROPORTION))
      {
      //--- сохраняем или просто возвращаем данные, зависит это динамика или статика
      return $this->GetImageSrcForPage('gen-img-number', pathinfo($new_image_fullname, PATHINFO_BASENAME), getimagesize($new_image_fullname));
      }
    return '';
    }

  /**
   * Берем случайную картинку из указанного подкаталога и обрабатываем ее в зависимости от настроек
   * @param array $matches
   *
   * @return string
   */
  public function GetGenerateImgFrom($matches)
    {
    $path = $matches[1];
    //---
    $image_file = $this->GetImageFilenameByKeyword($path);
    if(empty($image_file)) return '';
    //---
    CLogger::write(CLoggerType::DEBUG, 'gen-img-from: file: ' . $image_file . ' ' . $path);
    //--- новое имя файла
    $new_image_file = $this->GetRandomImageFilename($image_file);
    //--- текущее имя картинки
    $cur_image_name = self::PATH_IMAGES . '/' . $path . '/' . $image_file;
    //--- имя и папку куда копировать
    $new_image_fullname = CTools_files::GetNewFilenameNotReplace($cur_image_name, $this->localPath . '/' . $this->m_image_cur_path, $new_image_file);
    //--- копируем в новое место картинку
    $this->m_model_image->setRandowWidth(array(100,
                                               200));
    //--- установка водяного знака
    if(($this->m_options_images & CModel_ImageChange::WATERMARK) > 0) $this->m_model_image->setWatermark($this->m_params['imageWatermarkStr'] == '[KEYWORD]' ? $this->m_current_keyword : $this->m_params['imageWatermarkStr']);
    //--- обработаем картинку и сохраним в нужном месте
    if($this->m_model_image->ChangeImage($cur_image_name, $new_image_fullname, $this->m_options_images))
      {
      //--- сохраняем или просто возвращаем данные, зависит это динамика или статика
      return $this->GetImageSrcForPage('gen-img-' . $path, pathinfo($new_image_fullname, PATHINFO_BASENAME), getimagesize($new_image_fullname));
      }
    return '';
    }

  /**
   * Проверяем список загруженных файлов и если нужно загружаем
   * @param string $path
   *
   * @return bool
   */
  private function CheckLoadImagesFiles($path)
    {
    if(empty($this->m_list_images[$path])) $this->LoadImagesFile($path);
    //---
    if(empty($this->m_list_images[$path]))
      {
      CLogger::write(CLoggerType::ERROR, 'image files not found in path ' . self::PATH_IMAGES . ($path[0] != '/' ? '/' : '') . $path);
      return false;
      }
    return true;
    }

  /**
   * Загружаем список файлов с диска
   * @param string $path
   *
   * @return bool
   */
  private function LoadImagesFile($path)
    {
    if(empty($path)) $path = '/';
    //---
    if(empty($this->m_list_images[$path]))
      {
      //--- проверим нужный путь
      if(!file_exists(self::PATH_IMAGES))
        {
        CLogger::write(CLoggerType::ERROR, 'images path not found: ' . self::PATH_IMAGES);
        return false;
        }
      //--- получим все файлы
      $curr_path                  = self::PATH_IMAGES . ($path[0] != '/' ? '/' : '') . $path;
      $this->m_list_images[$path] = CTools_files::GetAllOnlyFiles($curr_path, $this->m_exts_images);
      //--- если есть загрузим ключевики и файлы
      $this->LoadImagesKeywords($path, $curr_path);
      }
    //---
    CLogger::write(CLoggerType::DEBUG, 'img: load files catalog "' . $path . '", ' . count($this->m_list_images[$path]) . ': ' . var_export($this->m_list_images[$path], true));
    }

  /**
   * Загрузка данные по ключевикам и файлам
   * @param string $path
   */
  private function LoadImagesKeywords($path_name, $path)
    {
    $filename = $path . '/' . CModel_ParserImage::KEYWORD_FILENAME;
    if(file_exists($filename))
      {
      $this->m_images_keywords[$path_name] = unserialize(file_get_contents($filename));
      if(!empty($this->m_images_keywords)) CLogger::write(CLoggerType::DEBUG, 'img: load files keywords: ' . $filename . ', count: ' . count($this->m_images_keywords[$path_name]));
      }
    }

  /**
   * Получаем для статики или сохраняем данные для динамики
   */
  public function GetImageSrcForPage($name_dynamic, $new_image_file, $size)
    {
    //--- для статитики другое
    return 'src="' . $this->m_params['nextUrl'] . $this->m_image_cur_path . '/' . $new_image_file . '" width="' . $size[0] . '" height="' . $size[1] . '"';
    }

  /**
   * Берем случайную картинку из указанного подкаталога и обрабатываем ее в зависимости от настроек и уменьшаем размеры в зависимости от указанных размерах
   * @param array $matches
   *
   * @return string
   */
  public function GetGenerateImgResizeFrom($matches)
    {
    $path = $matches[1];
    //---
    $image_file = $this->GetImageFilenameByKeyword($path);
    if(empty($image_file)) return '';
    //--- новое имя файла
    $new_image_file = $this->GetRandomImageFilename($image_file);
    //--- текущее имя картинки
    $cur_image_name = self::PATH_IMAGES . '/' . $path . '/' . $image_file;
    //--- имя и папку куда копировать
    $new_image_fullname = CTools_files::GetNewFilenameNotReplace($cur_image_name, $this->localPath . '/' . $this->m_image_cur_path, $new_image_file);
    //--- копируем в новое место картинку
    $this->m_model_image->setRandowWidth(array((int)$matches[2],
                                               (int)$matches[3]));
    //--- установка водяного знака
    if(($this->m_options_images & CModel_ImageChange::WATERMARK) > 0) $this->m_model_image->setWatermark($this->m_params['imageWatermarkStr'] == '[KEYWORD]' ? $this->m_current_keyword : $this->m_params['imageWatermarkStr']);
    //--- обработаем картинку и сохраним в нужном месте
    if($this->m_model_image->ChangeImage($cur_image_name, $new_image_fullname, $this->m_options_images | CModel_ImageChange::RESIZE_RANDOM | CModel_ImageChange::RESIZE_PROPORTION))
      {
      //--- сохраняем или просто возвращаем данные, зависит это динамика или статика
      return $this->GetImageSrcForPage('gen-img-number-' . $path, pathinfo($new_image_fullname, PATHINFO_BASENAME), getimagesize($new_image_fullname));
      }
    return '';
    }

  /**
   * Берем случайное видое из указанного файла и подставляем размеры
   * @param array $matches
   *
   * @return string
   */
  public function GetRandVideoSize($matches)
    {
    $filename = $matches[1];
    $width    = $matches[2];
    $height   = $matches[3];
    //---
    return $this->m_model_video->GetRandVideoSize($filename, $width, $height);
    }

  /**
   * Берем случайное видое из указанного файла и подставляем размеры
   * @param array $matches
   *
   * @return string
   */
  public function GetRandVideo($matches)
    {
    $widths   = array(320,
                      420,
                      480,
                      520,
                      600,
                      640,
                      720,
                      800,
                      900,
                      1000,
                      1024);
    $heights  = array(320,
                      420,
                      480,
                      520,
                      600);
    $filename = $matches[1];
    $width    = $widths[rand(0, 10)];
    $h        = rand(0, 5);
    $height   = isset($heights[$h]) ? $heights[$h] : $heights[0];
    //---
    return $this->m_model_video->GetRandVideoSize($filename, $width, $height);
    }

  /**
   * Берем случайное видое из указанного файла и подставляем размеры
   * @param array $matches
   *
   * @return string
   */
  public function GetRandVideoUrl($matches)
    {
    return $this->m_model_video->GetRandVideoUrl($matches);
    }

  /**
   * Получаем рандомный кейворд, который
   * начинается с заглавной буквы
   */
  public function GetRandUcKeyword($i = -1)
    {
    $n        = 0;
    $key_info = $this->m_model_keywords->GetRandKeyword($n, $i);
    if(empty($key_info)) return '';
    return CModel_helper::GetUcFirst($key_info->getKeywordRand());
    }

  /**
   * Получаем рандомный кейворд, который
   * начинается с заглавной буквы
   */
  public function GetRandUcKeywordFromFile($matches)
    {
    $num   = 0;
    $ar    = explode(';', $matches[1]);
    $files = array();
    foreach($ar as $name)
      {
      $value = explode('=', $name);
      if(trim($value[0]) == 'files')
        {
        $files = explode('|', $value[1]);
        }
      }
    //---
    if(empty($files)) return '';
    if(count($files) > 1) $filename = $files[rand(0, count($files) - 1)];
    else                $filename = $files[0];
    //---
    $key_info = $this->m_model_keywords->GetRandKeywordFromFile($filename);
    //---
    if(empty($key_info)) return '';
    return CModel_helper::GetUcFirst($key_info->getKeywordRand());
    }

  /**
   * Получени случайного кейворда в urlencode
   */
  public function GetEcodeRandKey($i = -1)
    {
    $num = 0;
    return urlencode($this->m_model_keywords->GetRandKeyword($num, $i));
    }

  /**
   * Получени текущего кейворда в urlencode
   */
  private function GetEcodeCurrentKey()
    {
    return urlencode($this->m_current_keyword);
    }

  /**
   * Получаем рандомную гиперссылку с кейвордом
   */
  public function GetRandLink($i = -1)
    {
    $n   = 0;
    $key = $this->m_model_keywords->GetRandKeyword($n, $i);
    if(empty($key)) return '';
    //---
    //return "<a href='" . $this->m_model_keywords->GetPageNameKey($key, $n) . "'>" . $key . '</a>';
    return "<a href='" . $key->GetUrl() . "'>" . $key->getKeywordRand() . '</a>';
    }

  /**
   * Получаем рандомную гиперссылку с кейвордом
   */
  public function GetNick()
    {
    return CModel_tools::GetNick();
    }

  /**
   * Получение случайного урла
   */
  public function GetRandUrl($i = -1)
    {
    $n   = 0;
    $key = $this->m_model_keywords->GetRandKeyword($n, $i);
    if(empty($key)) return '';
    //---
    return $key->GetUrl(); //$this->m_model_keywords->GetPageNameKey($key, $n);
    }

  /**
   * получаем ссылку из файла
   * @param $matches
   * @return null|string
   */
  public function GetRandUrlFromFile($matches)
    {
    $ar    = explode(';', $matches[1]);
    $files = array();
    foreach($ar as $name)
      {
      $value = explode('=', $name);
      if(trim($value[0]) == 'files')
        {
        $files = explode('|', $value[1]);
        }
      }
    //---
    $result = '';
    if(!empty($files)) $result = $this->m_model_links->GetRandUrlFromFiles($files);
    return $result;
    }

  /**
   * Получение ссылки из случайного файла
   * @param $matches
   * @return null|string
   */
  public function GetRandUcLinkFromFile($matches)
    {
    $ar    = explode(';', $matches[1]);
    $files = array();
    foreach($ar as $name)
      {
      $value = explode('=', $name);
      if(trim($value[0]) == 'files')
        {
        $files = explode('|', $value[1]);
        }
      }
    //---
    $result = '';
    if(!empty($files)) $result = $this->m_model_links->GetRandUcLinkFromFile($files);
    return $result;
    }

  /**
   * Получение случайной ссылки из файла
   * @param $matches
   * @return string|void
   */
  public function GetRandLinkFromFile($matches)
    {
    $ar    = explode(';', $matches[1]);
    $files = array();
    foreach($ar as $name)
      {
      $value = explode('=', $name);
      if(trim($value[0]) == 'files')
        {
        $files = explode('|', $value[1]);
        }
      }
    //---
    $result = '';
    if(!empty($files)) $result = $this->m_model_links->GetRandLinkFromFile($files);
    return $result;
    }

  /**
   * Получаем рандомную гиперссылку с кейвордом,
   * которая начинается с заглавной буквы
   */
  public function GetRandUcLink($i = -1)
    {
    $n   = 0;
    $key = $this->m_model_keywords->GetRandKeyword($n, $i);
    //return "<a href='" . $this->m_model_keywords->GetPageNameKey($key, $n) . "'>" . CModel_helper::GetUcFirst($key) . '</a>';
    return "<a href='" . $key->getUrl() . "'>" . CModel_helper::GetUcFirst($key->getKeywordRand()) . '</a>';
    }

  /**
   * Получаем список случайный гиперссылок,
   * заключенных в тэги <li></li>
   * @param array $matches
   *
   * @return string
   */
  public function GetRandUcLinks($matches, $i = -1)
    {
    $text       = '';
    $linksCount = rand((int)$matches[1], (int)$matches[2]);
    //---
    $format = $this->m_model_settings->GetGlobal('rand_links', '<li><a href="{url}">{title}</a></li>');
    for($j = 0; $j < $linksCount; $j++)
      {
      $n   = 0;
      $key = $this->m_model_keywords->GetRandKeyword($n, $i);
      $url = $key->getUrl(); //$this->m_model_keywords->GetPageNameKey($key, $n);
      //---
      $text .= str_replace(array('{url}',
                                 '{title}'), array($url,
                                                   CModel_helper::GetUcFirst($key->getKeywordRand())), $format);
      }
    //---
    return $text;
    }

  /**
   * Получаем гиперссылку с кейвордом
   * по указанному порядковому номеру кейворда
   * или случайным образом
   * @param array $matches
   *
   * @return string
   */
  public function GetUcLink($matches, $i = -1)
    {
    $n = isset($matches[1]) ? $matches[1] : rand(0, $this->m_model_keywords->GetCountKeywords($i) - 1);
    //---
    $key = $this->m_model_keywords->GetKeywordByNum($n);
    if(!empty($this->m_current_category)) return "<a href='" . $key->getUrl() . "'>" . CModel_helper::GetUcFirst($key->getKeywordRand()) . "</a>";
    //---
    $num = 0;
    $key = $this->GetNextPrevKeyword($n, $num, $i);
    return "<a href='" . $key->GetUrl() . "'>" . CModel_helper::GetUcFirst($key->getKeywordRand()) . "</a>";
    }

  /**
   * гиперссылка с кейвордом на след. страницу
   */
  public function GetNextLink()
    {
    $count = $this->m_model_keywords->GetCountKeywords();
    if($count <= ($this->m_keyword_id + 1)) return '';
    //---
    $id  = $this->m_keyword_id + 1;
    $key = $this->m_model_keywords->GetKeywordByNum($id);
    //---
    return "<a href='" . $key->GetUrl() . "'>" . $key->getKeywordRand() . "</a>";
    }

  /**
   * гиперссылка с кейвордом на след. страницу
   */
  public function GetPrevLink()
    {
    if(($this->m_keyword_id - 1) < 0) return '';
    //---
    $id  = $this->m_keyword_id - 1;
    $key = $this->m_model_keywords->GetKeywordByNum($id);
    //return "<a href='" . $this->m_model_keywords->GetPageNameKey($key, $id) . "'>" . $key . "</a>";
    return "<a href='" . $key->getUrl() . "'>" . $key->getKeywordRand() . "</a>";
    }

  /**
   *  гиперссылка с кейвордом с большой буквы на след. страницу
   */
  public function GetNextUcLink()
    {
    $count = $this->m_model_keywords->GetCountKeywords();
    if($count <= ($this->m_keyword_id + 1)) return '';
    //---
    $id  = $this->m_keyword_id + 1;
    $key = $this->m_model_keywords->GetKeywordByNum($id);
//---
    //return "<a href='" . $this->m_model_keywords->GetPageNameKey($key, $id) . "'>" . CModel_helper::GetUcFirst($key) . "</a>";
    return "<a href='" . $key->getUrl() . "'>" . CModel_helper::GetUcFirst($key->getKeywordRand()) . "</a>";
    }

  /**
   * гиперссылка с кейвордом с большой буквы на пред. страницу
   */
  public function GetPrevUcLink()
    {
    if(($this->m_keyword_id - 1) < 0) return '';
    //---
    $id  = $this->m_keyword_id - 1;
    $key = $this->m_model_keywords->GetKeywordByNum($id);
    //---
    //return "<a href='" . $this->m_model_keywords->GetPageNameKey($key, $id) . "'>" . CModel_helper::GetUcFirst($key) . "</a>";
    return "<a href='" . $key->getUrl() . "'>" . CModel_helper::GetUcFirst($key->getKeywordRand()) . "</a>";
    }

  /**
   *   гиперссылка с кейвордом, все слова которого  начинаются с большой буквы, на след.  страницу
   */
  public function GetNextUcwLink()
    {
    $count = $this->m_model_keywords->GetCountKeywords();
    if($count <= ($this->m_keyword_id + 1)) return '';
    //---
    $id  = $this->m_keyword_id + 1;
    $key = $this->m_model_keywords->GetKeywordByNum($id);
//---
    //return "<a href='" . $this->m_model_keywords->GetPageNameKey($key, $id) . "'>" . CModel_helper::GetUcWords($key) . "</a>";
    return "<a href='" . $key->getUrl() . "'>" . CModel_helper::GetUcWords($key->getKeywordRand()) . "</a>";
    }

  /**
   * гиперссылка с кейвордом с большой буквы на пред. страницу
   */
  public function GetPrevUcwLink()
    {
    if(($this->m_keyword_id - 1) < 0) return '';
    //---
    $id  = $this->m_keyword_id - 1;
    $key = $this->m_model_keywords->GetKeywordByNum($id);
    //---
    //return "<a href='" . $this->m_model_keywords->GetPageNameKey($key, $id) . "'>" . CModel_helper::GetUcWords($key) . "</a>";
    return "<a href='" . $key->getUrl() . "'>" . CModel_helper::GetUcWords($key->getKeywordRand()) . "</a>";
    }

  /**
   *  гиперссылка с кейвордом с большой буквы на след. страницы
   */
  public function GetNextUcLinksNumbers($matches)
    {
    $n     = $matches[1];
    $count = $this->m_model_keywords->GetCountKeywords();
    if($count <= ($this->m_keyword_id + 1)) return '';
//---
    $result = '';
    $format = $this->m_model_settings->GetGlobal('links_numbers', '<li><a href="{url}">{title}</a></li>');
    //---
    for($i = 0; $i < $n && $this->m_keyword_id + 1 + $i < $count; $i++)
      {
      $id  = $this->m_keyword_id + 1 + $i;
      $key = $this->m_model_keywords->GetKeywordByNum($id);
      $result .= str_replace(array('{url}',
                                   '{title}'), array($key->GetUrl(),
                                                     CModel_helper::GetUcFirst($key->getKeywordRand())), $format);
      }
    return $result;
    }

  /**
   * гиперссылка с кейвордом с большой буквы на пред. страницу
   */
  public function GetPrevUcLinksNumbers($matches)
    {
    if(($this->m_keyword_id - 1) < 0) return '';
    //---
    $n      = $matches[1];
    $result = '';
    $format = $this->m_model_settings->GetGlobal('links_numbers', '<li><a href="{url}">{title}</a></li>');
    //---
    $j = 0;
    for($i = $this->m_keyword_id - 1; ($j < $n) && ($i > 0); $i--)
      {
      $id  = $this->m_keyword_id - 1 - $i;
      $key = $this->m_model_keywords->GetKeywordByNum($id);
      $result .= str_replace(array('{url}',
                                   '{title}'), array($key->GetUrl(),
                                                     CModel_helper::GetUcFirst($key->getKeywordRand())), $format);
      $j++;
      }
    return $result;
    }

  /**
   *   гиперссылка с кейвордом, все слова которого  начинаются с большой буквы, на след.  страницу
   */
  public function GetNextUcwLinksNumbers($matches)
    {
    $count = $this->m_model_keywords->GetCountKeywords();
    if($count <= ($this->m_keyword_id + 1)) return '';
    //---
    $n = $matches[1];
//---
    $result = '';
    $format = $this->m_model_settings->GetGlobal('links_numbers', '<li><a href="{url}">{title}</a></li>');
    //---
    for($i = 0; $i < $n && $this->m_keyword_id + 1 + $i < $count; $i++)
      {
      $id  = $this->m_keyword_id + 1 + $i;
      $key = $this->m_model_keywords->GetKeywordByNum($id);
      $result .= str_replace(array('{url}',
                                   '{title}'), array($key->GetUrl(),
                                                     CModel_helper::GetUcWords($key->getKeywordRand())), $format);
      }
    return $result;
    }

  /**
   * гиперссылка с кейвордом с большой буквы на пред. страницу
   */
  public function GetPrevUcwLinksNumbers($matches)
    {
    if(($this->m_keyword_id - 1) < 0) return '';
    //---
    $n      = $matches[1];
    $result = '';
    $format = $this->m_model_settings->GetGlobal('links_numbers', '<li><a href="{url}">{title}</a></li>');
    //---
    $j = 0;
    for($i = $this->m_keyword_id - 1; ($j < $n) && ($i > 0); $i--)
      {
      $id  = $this->m_keyword_id - 1 - $i;
      $key = $this->m_model_keywords->GetKeywordByNum($id);
      //---
      $result .= str_replace(array('{url}',
                                   '{title}'), array($key->GetUrl(),
                                                     CModel_helper::GetUcWords($key->getKeywordRand())), $format);
      $j++;
      }
    return $result;
    }

  /**
   * просто ссылка на след. страницу
   */
  public function GetNextUrl()
    {
    $count = $this->m_model_keywords->GetCountKeywords();
    if($count <= ($this->m_keyword_id + 1)) return '';
//---
    return $this->m_model_keywords->GetPageNameNumber($this->m_keyword_id + 1);
    }

  /**
   * гиперссылка с кейвордом на след. страницу
   */
  public function GetPrevUrl()
    {
    if(($this->m_keyword_id - 1) < 0) return '';
    //---
    return $this->m_model_keywords->GetPageNameNumber($this->m_keyword_id - 1);
    }

  /**
   * Кейворды из файла
   * @param $matches
   * @return string
   */
  private function GetRandKeywordsFromFilesCallback($matches)
    {
    $count = rand($matches[1], $matches[2]);
    //--- тут должна быть строчка вида files=f1.txt|f2.txt
    $ar    = explode(';', $matches[3]);
    $files = array();
    foreach($ar as $name)
      {
      $value = explode('=', $name);
      if(trim($value[0]) == 'files')
        {
        $files = explode('|', $value[1]);
        }
      }
    //---
    $result = '';
    if(!empty($files)) $result = $this->m_model_keywords->GetRandKeywordsFromFiles($count, $files);
    return $result;
    }

  /**
   * Кейворды через запятую
   * @param $matches
   */
  private function GetRandKeywordsCallback($matches)
    {
    $count  = rand($matches[1], $matches[2]);
    $result = '';
    $num    = 0;
    for($i = 0; $i < $count; $i++)
      {
      $key = $this->m_model_keywords->GetRandKeyword($num, -1);
      if($key != null) $result .= (!empty($result) ? ', ' : '') . $key->getKeywordRand();
      }
    //---
    return $result;
    }

  /**
   * Каллбек функция
   */
  private function GetRandKeywordCallback($i = -1)
    {
    $num = 0;
    $key = $this->m_model_keywords->GetRandKeyword($num, $i);
    return $key == null ? '' : $key->getKeywordRand();
    }

  /**
   * Получение случайного ключевика из другого файла
   */
  private function GetRandKeywordFromFileCallback($matches)
    {
    $num   = 0;
    $ar    = explode(';', $matches[1]);
    $files = array();
    foreach($ar as $name)
      {
      $value = explode('=', $name);
      if(trim($value[0]) == 'files')
        {
        $files = explode('|', $value[1]);
        }
      }
    //---
    if(empty($files)) return '';
    if(count($files) > 1) $filename = $files[rand(0, count($files) - 1)];
    else                $filename = $files[0];
    //---
    $key = $this->m_model_keywords->GetRandKeywordFromFile($filename);
    //---
    return $key == null ? '' : $key->getKeywordRand();
    }

  /**
   * Получение списка файлов из строки
   * @param $str
   */
  private function GetParamsFromStr($str)
    {
    $result = array();
    $ar     = explode(';', $str);
    foreach($ar as $name)
      {
      $value = explode('=', $name);
      if(trim($value[0]) == 'files')
        {
        $result['files'] = explode('|', $value[1]);
        }
      }
    return $result;
    }

  /**
   * У текущего ключа нужно получить, номер
   */
  private function GetMultiKeyword($matches)
    {
    return $this->m_current_keyword_info->getKeywordIndex($matches[1]);
    }

  /**
   * У текущего ключа нужно получить, номер
   */
  private function GetMultiKeywordRand($matches)
    {
    $num = 0;
    //--- Если нужно брать данные из файла
    if(!empty($matches[2]))
      {
      $params = $this->GetParamsFromStr($matches[2]);
      if(isset($params['files']) && count($params['files']) > 0)
        {
        $filename = $params['files'][rand(0, count($params['files']) - 1)];
        //---
        $keyword = $this->m_model_keywords->GetRandKeywordFromFile($filename);
        }
      }
    else
      {
      $keyword = $this->m_model_keywords->GetRandKeyword($num);
      }
    //---
    if(empty($keyword)) return '';
    //---
    return $keyword->getKeywordIndex($matches[1]);
    }

  /**
   * ПОлучение нужного ключевика и нужного поля у него
   * @param $matches
   * @return string
   */
  private function GetMultiKeywordNumber($matches)
    {
    $num = $matches[1];
    //--- Если нужно брать данные из файла
    if(!empty($matches[3]))
      {
      $params = $this->GetParamsFromStr($matches[3]);
      if(isset($params['files']) && count($params['files']) > 0)
        {
        $filename = $params['files'][rand(0, count($params['files']) - 1)];
        //---
        $keyword = $this->m_model_keywords->GetRandKeywordFromFile($filename);
        }
      }
    else
      {
      $keyword = $this->m_model_keywords->GetRandKeyword($num);
      }
    //---
    if(empty($keyword)) return '';
    //---
    return $keyword->getKeywordIndex($matches[2]);
    }

  /**
   * Получение следующего или если не существует следующего предедущего кейворда
   * @param int $n
   * @param int $num
   *
   * @return string
   */
  private function GetNextPrevKeyword($n, &$num, $i = -1)
    {
    //--- доходим до конца ключей
    for($j = $n; $j < $this->m_model_keywords->GetCountKeywords($i); $j++)
      {
      $key = $this->m_model_keywords->GetKeywordByNum($j);
      if(!empty($key))
        {
        $num = $j;
        return $key;
        }
      }
    //--- теперь пойдем в начало
    for($j = $n - 1; $j > -1; $j--)
      {
      $key = $this->m_model_keywords->GetKeywordByNum($j);
      if(!empty($key))
        {
        $num = $i;
        return $key;
        }
      }
    //--- что уж есть
    $num = 0;
    return $this->m_model_keywords->GetKeywordByNum(0);
    }

  /**
   * Возвращаем случайное число из диапозона
   * @param array $matches
   *
   * @return int
   */
  public function GetRandInt($matches)
    {
    $min = (int)$matches[1];
    $max = (int)$matches[2];
    //---
    if($min > $max) $max = $min;
    //---
    return rand($min, $max);
    }

  /**
   * Облако тегов
   */
  public function GetTags()
    {
    if(!empty($this->m_tags_result)) return $this->m_tags_result;
    CLogger::write(CLoggerType::DEBUG, 'generate tags');
    //--- по всем категориям
    $this->m_model_settings->GetGlobal('tags', '<a href="{url}" style="font-size: {size}pt;">{title}</a>');
    $str = '';
    foreach($this->m_model_keywords->GetTagsData() as $num => $tag)
      {
      $key = $tag['key'];
      //$str .= ' ' . '<a href="' . $this->m_model_keywords->GetPageNameKey($name, $tag['num']) . '" style="font-size: ' . $tag['size'] . 'pt;">' . CModel_helper::GetUcFirst($name) . '</a>';
      $str .= ' ' . '<a href="' . $key->GetUrl() . '" style="font-size: ' . $tag['size'] . 'pt;">' . CModel_helper::GetUcFirst($key->GetKeywordIndex(0)) . '</a>';
      }
    //---
    $this->m_tags_result = $str;
    //---
    return $this->m_tags_result;
    }

  /**
   * Меню из категорий
   */
  public function GetMenuCategory()
    {
    //--- по всем категориям
    $str           = '';
    $item_template = $this->m_model_settings->GetGlobal('menu', '<li><a href="{url}">{title}</a></li>');
    foreach($this->m_model_keywords->GetCategoies() as $category)
      {
      $url   = $this->m_model_keywords->GetCategoryUrl($category);
      $title = CModel_helper::GetUcFirst($category->getName());
      $str .= str_replace(array('{url}',
                                '{title}'), array($url,
                                                  $title), $item_template);
      }
    //---
    return $str;
    }

  /**
   * Замена макроса [CATEGORY-LINKS]
   * @return string
   */
  private function GetSubCategoryLinks()
    {
    $category_info = $this->m_model_keywords->GetCategoryByNumKey($this->m_keyword_id);
    if(empty($category_info)) return '';
//---
    $format = $this->m_model_settings->GetGlobal('subcategory_links', '<li><a href="{url}">{title}</a></li>');
    $text   = '';
    for($i = $category_info->getKeywordBegin() + 1; $i < $category_info->getKeywordEnd(); $i++)
      {
      $key = $this->m_model_keywords->GetKeywordByNum($i);
      if(empty($key)) continue;
      //$url = $this->m_model_keywords->GetPageNameKey($key, $i);
      $text .= str_replace(array('{url}',
                                 '{title}'), array($key->GetUrl(),
                                                   $key->GetKeywordIndex(0)), $format);
      }
    return $text;
    }
  /**
   * Замена макроса [CATEGORY-RAND-LINK]
   * @return string
   */
  private function GetCategoryRandLink()
    {
    $categories = $this->m_model_keywords->GetCategoies();
    $k = array_rand($categories);
    if($k===false)
      return '';
    $category_info = $categories[$k];
    if(empty($category_info)) return '';
    return '<a href="'.$this->m_model_keywords->GetCategoryUrl($category_info).'">'.$category_info->getName().'</a>';
    }

  /**
   * Замена макроса [CATEGORY-RAND-NAME]
   * @return string
   */
  private function GetCategoryRandName()
    {
    $categories = $this->m_model_keywords->GetCategoies();
    $k = array_rand($categories);
    if($k===false)
      return '';
    $category_info = $categories[$k];
    if(empty($category_info)) return '';
    return $category_info->GetName();
    }

  /**
   * Замена макроса [CATEGORY-RAND-LINKS-0-1]
   *
   * @param $matches
   *
   * @return string
   */
  private function GetSubCategoryRandLinks($matches)
    {
    $category_info = $this->m_model_keywords->GetCategoryByNumKey($this->m_keyword_id);
    if(empty($category_info)) return '';
    $text       = '';
    $linksCount = rand((int)$matches[1], (int)$matches[2]);
    //---
    $format = $this->m_model_settings->GetGlobal('subcategory_links', '<li><a href="{url}">{title}</a></li>');
    for($j = 0; $j < $linksCount; $j++)
      {
      $n = rand($category_info->getKeywordBegin() + 1, $category_info->getKeywordEnd());
      if($n < 0) $n = 0;
      //---
      $key = $this->m_model_keywords->GetKeywordByNum($n);
      //$url = $this->m_model_keywords->GetPageNameKey($key, $n);
      //---
if($key!=null)
      $text .= str_replace(array('{url}',
                                 '{title}'), array($key->GetUrl(),
                                                   $key->GetKeywordIndex(0)), $format);
      }
    //---
    return $text;
    }

  /**
   * получаем настройки для [GEN-IMG]
   * @return int
   */
  private function GetOptionsImages()
    {
    $options = 0;
    //--- обрезание картинок по краям
    if(isset($this->m_params['imageCrop']) && $this->m_params['imageCrop']) $options |= CModel_ImageChange::CROP;
    //--- инверсия
    if(isset($this->m_params['imageInvert']) && $this->m_params['imageInvert']) $options |= CModel_ImageChange::INVERT;
    //--- водяной знак
    if(isset($this->m_params['imageWatermark']) && $this->m_params['imageWatermark']) $options |= CModel_ImageChange::WATERMARK;
    //--- зеракльное отражение
    if(isset($this->m_params['imageMirror']) && $this->m_params['imageMirror']) $options |= CModel_ImageChange::MIRROR;
    //--- негатив
    if(isset($this->m_params['imageNegatif']) && $this->m_params['imageNegatif']) $options |= CModel_ImageChange::NEGATIF;
    //---
    if(isset($this->m_params['imageEmboss']) && $this->m_params['imageEmboss']) $options |= CModel_ImageChange::EMBOSS;
    //--- черное белое
    if(isset($this->m_params['imageGray']) && $this->m_params['imageGray']) $options |= CModel_ImageChange::GRAYSCALE;
    //---
    return $options;
    }

  /**
   * На вход image.jpg на выходт только тело или просто ключевик
   *
   * @param string $image_file
   *
   * @return string
   */
  private function GetRandomImageFilename($image_file)
    {
    if(isset($this->m_params['imageFilenameKeyword']) && $this->m_params['imageFilenameKeyword'])
      {
      return CModel_tools::Translit($this->m_current_keyword);
      }
    return CModel_tools::Translit(pathinfo($image_file, PATHINFO_FILENAME));
    }

  /**
   * создание файла .htaccess
   */
  public function CreateStaticHtaccess($path)
    {
    //--- php код
    if($this->m_model_imagesurls->HaveImagesUrl())
      {
      //--- создаем файл в корне img.php
      $this->m_model_imagesurls->CreateImgPhpFile($path);
      //--- вернем строчку
      return $this->m_model_imagesurls->GetDataHtaccess();
      }
    return '';
    }
  }

?>