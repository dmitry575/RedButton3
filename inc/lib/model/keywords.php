<?
/**
 * Класс для управлением категорий
 * @author User
 */
class Category_keywords
  {
  private $m_keyword_begin;
  private $m_keyword_end;
  private $m_name;
  private $m_url;

  /**
   * Конструктор
   * @param string $name
   * @param string $url
   * @param int $keyword_begin
   * @param int $keyword_end
   */
  public function __construct($name, $url, $keyword_begin, $keyword_end)
    {
    $this->m_name          = $name;
    $this->m_url           = $url;
    $this->m_keyword_begin = $keyword_begin;
    $this->m_keyword_end   = $keyword_end;
    }

  /**
   * установка последнего индекса на кейводрд для данной категории
   * @param int $num
   */
  public function setKeywordEnd($num)
    {
    $this->m_keyword_end = $num;
    }

  /**
   * установка первого индекса
   * @param int $num
   */
  public function setKeywordBegin($num)
    {
    $this->m_keyword_begin = $num;
    }

  /**
   * имя категории
   */
  public function getName()
    {
    return $this->m_name;
    }

  /**
   * получение урла
   */
  public function getUrl()
    {
    return $this->m_url;
    }

  /**
   * индекс первого кейворда
   */
  public function getKeywordBegin()
    {
    return $this->m_keyword_begin;
    }

  /**
   * индекс последнего кейворда
   */
  public function getKeywordEnd()
    {
    return $this->m_keyword_end;
    }

  /**
   * количесво кейвордов в категории
   */
  public function getKeywordsCount()
    {
    return $this->m_keyword_end - $this->m_keyword_begin + 1;
    }
  }
/**
 * Данные о конкретном ключевике
 * Class CKeywordInfo
 */
class CKeywordInfo
  {
  /**
   * id ключевика
   * @var int
   */
  private $m_id;
  /**
   * урл
   * @var string
   */
  public $m_url;
  /**
   * списко ключевиков
   * @var array
   */
  public $m_list_key;
  /**
   * Исходный ключевик
   * @var string
   */
  public $m_key;
//--- название файла для ключевика
  private $m_filename;

  /**
   * Задаем первоначальные данные
   * @param        $id
   * @param        $key
   * @param        $keys
   * @param string $url
   */
  public function __construct($id, $key, $keys, $url = '')
    {
    $this->m_id       = $id;
    $this->m_key      = $key;
    $this->m_count    = 0;
    $this->m_list_key = array();
    if(is_array($keys))
      {
      $cur_key = '';
      foreach($keys as $k)
        {
        //--- если последний символ экранирования, то нужно объединить ключи
        if($k[strlen($k) - 1] == '\\') $cur_key .= (!empty($cur_key) ? ' ' : '') . rtrim($k, '\\');
        else
          {
          $cur_key .= (!empty($cur_key) ? '|' : '') . $k;
          $this->m_list_key[] = $cur_key;
          $this->m_count++;
          $cur_key = '';
          }
        }
      }
    //---
    $this->m_url = $url;
    }

  /**
   * id ключевика
   * @return int
   */
  public function getId()
    {
    return $this->m_id;
    }

  public function setId($id)
    {
    $this->m_id = $id;
    }

  /**
   * Получить урл
   * @return string
   */
  public function getUrl()
    {
    return $this->m_url;
    }

  /**
   * Установить урл
   * @param $url
   */
  public function setUrl($url)
    {
    $this->m_url = $url;
    }

  /**
   * Получить ключвек в первоначальном значении
   * @return string
   */
  public function getKeyword()
    {
    return $this->m_key;
    }

  /**
   * получение по индексу и получение следующего индекса, может быть следующий это 0
   * @param int $num
   * @param int $next
   *
   * @return string
   */
  public function getKeywordIndexNext($num, &$next)
    {
    if(isset($this->m_list_key[$num]))
      {
      if(!empty($this->m_list_key[$num + 1])) $next = $num + 1;
      else $next = 0;
      return $this->m_list_key[$num];
      }
    return '';
    }

  /**
   * Получени ключевика по индексу
   * @param $num
   *
   * @return string
   */
  public function getKeywordIndex($num)
    {
    return isset($this->m_list_key[$num]) ? $this->m_list_key[$num] : '';
    }

  /**
   * Получить случайны ключевик
   * @param int $is_rand_part
   * @return string
   */
  public function getKeywordRand($is_rand_part = 1)
    {
    if($is_rand_part == 1)
      {
      return $this->m_count > 1 ? $this->m_list_key[0] : $this->m_key;
      }
    //---
    if($this->m_count > 1) return $this->m_list_key[rand(0, $this->m_count - 1)];
    return $this->m_key;
    }

  /**
   * преобразованное имя кейворда в файл
   */
  public function getFilename()
    {
    return $this->m_filename;
    }

  public function setFilename($filename)
    {
    $this->m_filename = $filename;
    }

  /**
   * Количество ключевико
   * @return int
   */
  public function getCount()
    {
    return $this->m_count;
    }
  }
/**
 * Данные о категории
 * Class CategoryInfo
 */
class CategoryInfo
  {
  public $name;
  public $url;
  public $keyword_count;
  }
/**
 * Основной класс для управлением ключевыми словами
 * @author Dmitry
 */
class CModel_keywords
  {
  //--- максимальное количество слов в облаке тегов
  const MAX_TAGS = 25;
  //--- путь к ключевым словам
  const PATH_KEYWORDS = 'data/keywords';
  /**
   * Стоп слова
   * @var array
   */
  private static $m_stop_words = array();
  /*
  расширение для файлов
*/
  private $m_extension = 'html';
  /**
   * массив ключевых слов
   * @var array
   */
  private $keywords = array();
  /**
   * размер массива с ключами
   * @var int
   */
  private $m_count_keywords = 0;
  /**
   * количество опубликованных ключевиков
   * @var int
   */
  private $m_count_keywords_published = 0;
  /**
   * массив категорий
   * @var array
   */
  private $m_categories = array();
  /**
   * вообще есть категории, или можно все делать быстро
   * @var bool
   */
  private $m_is_set_category = false;
  /**
   * массив для тегов
   * @var string
   */
  private $m_tags = null;
  /**
   * Для работы с плагинами
   * @var CModel_plugins
   */
  private $m_model_plugins;
  /**
   * @var array
   */
  private $m_params;
  /**
   * Не использовать транслит
   * @var bool
   */
  private $m_not_use_translit = false;
  /**
   * Не приводит все к нижнему регистру
   * @var bool
   */
  private $m_convert_lower = true;
  //--- массив с текущим классом для других файлов
  private $m_file_keywords_list = array();
  /**
   * Использование APC кеша
   * @var bool
   */
  private $m_has_apc = false;

  /**
   * Конструктор
   */
  public function __constructor()
    {
    }

  /**
   * иницилизация объекта
   * @param array $params
   * @param CModel_plugins $model_plugins
   */
  public function Init(&$params, &$model_plugins)
    {
    $this->m_params        = $params;
    $this->m_model_plugins = $model_plugins;
    //---
    $this->m_categories = array();
    $this->keywords     = array();
    //--- теги
    $this->m_tags        = array();
    $this->m_tags_result = '';
    //--- использовать транслит для урлов
    $this->m_not_use_translit = isset($this->m_params['urlsOnLanguage']) && $this->m_params['urlsOnLanguage'] == 'on';
    //--- имена файлов и папок в том же регистре, что и в файле
    $this->m_convert_lower = !(isset($this->m_params['urlNoLower']) && $this->m_params['urlNoLower'] == 'on');
    //--- максимальное количество ключевиков в категории
    $is_auto_categories = isset($this->m_params['autoCategories']) && $this->m_params['autoCategories'] == 'on';
    $min_categories     = (int)$this->m_params['minCountAutoCategories'];
    if($min_categories <= 0) $min_categories = 1;
    //---
    $max_categories = (int)$this->m_params['maxCountAutoCategories'];
    if($max_categories <= 0) $max_categories = 25;
    //--- получим имя файла
    $fileName = $this->m_params['keysFrom'] == 'list' ? self::PATH_KEYWORDS . '/' . trim($this->m_params['keysFromList']) : $_FILES['keysFromFile']['tmp_name'];
    //--- проверим наличие файла
    if(!file_exists($fileName))
      {
      //---
      CLogger::write(CLoggerType::ERROR, 'keyword file ' . $fileName . ' not found');
      return;
      }
    $this->m_has_apc = extension_loaded('apc');
    //--- попробуем кешировать список ключевиков
    $res = false;
    if($this->m_has_apc && $this->m_params['keysFrom'] == 'list')
      {
      $time = microtime(true);
      $data = apc_fetch($fileName . '_keywords');
      if(!empty($data))
        {
        $this->keywords     = $data;
        $this->m_categories = apc_fetch($fileName . '_categories');
        $res                = true;
        CLogger::write(CLoggerType::DEBUG, 'keywords load from apc: ' . $fileName . ', ' . count($data) . ' keywords, ' . (int)((microtime(true) - $time) * 1000) . ' ms');
        }
      }
    //---
    $cat_from_text = !empty($this->m_params['keywordsCategoies']);
    //--- если в кеше ключевиков нет, то будем по старинке
    if(!$res)
      {
      $content_keys = file_get_contents($fileName);
      //--- проверим кодировку
      $isUtf8 = CModel_tools::IsUTF8(substr($content_keys, 0, 10)) ? 1 : 0;
      if(!$isUtf8) $content_keys = mb_convert_encoding($content_keys, 'UTF-8', 'WINDOWS-1251');
      //--- загрузим список кейвордов в массив
      $keys = explode("\n", $content_keys);
      if(sizeof($keys) < 1)
        {
        //---
        CLogger::write(CLoggerType::ERROR, 'keyword file ' . $fileName . ' is empty');
        return;
        }
      //---
      $curr_category   = '';
      $number          = 0;
      $category_number = 0;
      //--- загрузим в зависимости от категории
      foreach($keys as $key)
        {
        //---
        $key = trim($key);
        //--- не берем пустые ключи
        if(empty($key)) continue;
        //--- если это не кейворд, а название категории и название категории добавлнем в кейвод
        if($key[0] == '[' && $key[strlen($key) - 1] == ']')
          {
          //--- если категории установлены в текстовом поле, то категории из файла просто игнорируем
          if($cat_from_text) continue;
          //---
          $curr_category = $this->GetCategoryFromKey($key);
          //---
          if($category_number > 0) $this->m_categories[$category_number - 1]->setKeywordEnd($number - 1);
          if($this->m_not_use_translit)
            {
            $name_category = CModel_tools::ClearUrlSymbols($curr_category);
            }
          else
            {
            //--- вызов транслита из плагина
            $name_category = '';
            $this->m_model_plugins->OnTranslitUrl($curr_category, $name_category);
            if(empty($name_category)) $name_category = CModel_tools::Translit($curr_category, $this->m_convert_lower);
            }
          //--- сохраним данные о категории и индекс на ключ
          $this->m_categories[$category_number] = new Category_keywords($curr_category, $name_category, $number, -1);
          if(strpos($curr_category, '|') !== FALSE) $this->keywords[$number] = new CKeywordInfo($number, $curr_category, explode('|', $curr_category));
          else        $this->keywords[$number] = new CKeywordInfo($number, $curr_category, explode('|', $curr_category));
          //---
          $number++;
          $category_number++;
          continue;
          }
        //--- список слов
        if(strpos($key, '|') !== FALSE) $this->keywords[$number] = new CKeywordInfo($number, $key, explode('|', $key));
        else                            $this->keywords[$number] = new CKeywordInfo($number, $key, array($key));
        $number++;
        }
      //---
      if(!empty($this->m_categories))
        {
        $this->m_categories[$category_number - 1]->setKeywordEnd($number - 1);
        }
      //--- если есть хотя-бы один кейворд
      if(isset($this->keywords[0]))
        {
        //--- триммим каждый кейворд
        //array_walk($this->keywords, create_function('&$val', '$val = !is_array($val) ? trim($val):$val;'));
        //--- удаляем BOM из первого кейворда
        //$this->keywords[0] = CModel_tools::RemoveBom($this->keywords[0]);
        }
      if($this->m_has_apc)
        {
        if(apc_store($fileName . '_keywords', $this->keywords, 600))
          {
          CLogger::write(CLoggerType::DEBUG, 'keywords save to apc: ' . $fileName . ', ' . count($this->keywords) . ' keywords');
          if(apc_store($fileName . '_categories', $this->m_categories, 600))
            {
            CLogger::write(CLoggerType::DEBUG, 'keywords categories save to apc: ' . $fileName);
            }
          }
        }
      }
    //---
    $this->m_count_keywords  = count($this->keywords);
    $this->m_is_set_category = !empty($this->m_categories) && count($this->m_categories) > 0;
    //--- если нужно перемешивать кейворды
    if(isset($this->m_params['keysRandom']) && $this->m_params['keysRandom'] == 'on')
      {
      $keysRandomMin = (int)$this->m_params['keysRandomMin'];
      $keysRandomMax = (int)$this->m_params['keysRandomMax'];
      //--- проверим мин. и макс. значения
      if($keysRandomMin < 0) $keysRandomMin = 0;
      if($keysRandomMax < 0) $keysRandomMax = 0;
      if($keysRandomMax < $keysRandomMin) $keysRandomMax = $keysRandomMin;
      //--- если кейворды с категориями из файла, то сохраняем структуру категорий
      if($this->m_is_set_category)
        {
        $this->ShiffleKeywordsCategories(mt_rand($keysRandomMin, $keysRandomMax));
        } //--- если указаны категории в файле, то нужно будет добавить эти категории в список категорий и распределить кейворды
      else
        {
        //--- перемешиваем кейводы (так быстрее, чем получить случайные кейворды в цикле)
        shuffle($this->keywords);
        //--- выбираем из них указанную часть
        $r              = mt_rand($keysRandomMin, $keysRandomMax);
        $this->keywords = array_slice($this->keywords, 0, $r);
        }
      //--- обновим кол-во кейвордов
      $this->m_count_keywords = sizeof($this->keywords);
      }
    //--- если нужно то добавляем все в категории
    if($cat_from_text)
      {
      //--- обрабатываем введенные категории и добавляем туда ключевики
      $this->SetRandomCategories();
      }
    //--- может быть включена автоматическая растановка категорий
    elseif($is_auto_categories)
      {
      //--- обрабатываем введенные категории и добавляем туда ключевики
      $this->SetAutoCategories($min_categories, $max_categories);
      }
    //--- расширение для файлов
    if(!empty($this->m_params['staticPageNameCustom']) && $this->m_params['staticPageNamesFrom'] != 'list')
      {
      //--- значит задали файлы с раширением
      $path_info = pathinfo($this->m_params['staticPageNameCustom']);
      if(!empty($path_info['extension'])) $this->m_extension = $path_info['extension'];
      }
    //--- если флага нет, то нужно проставлять урлы
    if(!isset($this->m_params['no_urls_keyword']))
      {
      //--- после того, как списко кейвордов устаканился, нужно прописать для всех урлы и потримить ключевики
      $this->SetUrlsKeywords();
      }
    //--- отложенная публикация
    if(isset($this->m_params['delayedPublication']) && $this->m_params['delayedPublication'] == 'on')
      {
      $this->m_count_keywords_published = (int)$this->m_count_keywords * .1;
      }
    else
      {
      //--- генерация облака тегов, только если нет отложенной генерации
      $this->GetTags();
      }
    }

  /**
   * Загрузка ключевых слов из другого файла
   * @param $filename
   * @return bool
   */
  private function LoadListFileKeywords($filename)
    {
    //---
    $fname = self::PATH_KEYWORDS . '/' . trim($filename);
    if(!file_exists($fname))
      {
      return false;
      CLogger::write(CLoggerType::ERROR, 'loading: keyword file ' . $filename . ' not found');
      }
    //---
    $params                 = $this->m_params;
    $params['keysFrom']     = 'list';
    $params['keysFromList'] = $filename;
    $model_keyword          = new CModel_keywords();
    $model_keyword->Init($params, $this->m_model_plugins);
    $this->m_file_keywords_list[$filename] = $model_keyword;
    return true;
    }

  /**
   * иницилизация объекта
   * @param array $params
   * @param CModel_plugins $model_plugins
   */
  public function InitAnchorUrls(&$params, &$model_plugins, $files_urls, $files_anchors)
    {
    $this->m_params        = $params;
    $this->m_model_plugins = $model_plugins;
    //---
    $this->m_categories = array();
    $this->keywords     = array();
    //--- теги
    $this->m_tags        = array();
    $this->m_tags_result = '';
    //--- использовать транслит для урлов
    $this->m_not_use_translit = isset($this->m_params['urlsOnLanguage']) && $this->m_params['urlsOnLanguage'] == 'on';
    //--- имена файлов и папок в том же регистре, что и в файле
    $this->m_convert_lower = !(isset($this->m_params['urlNoLower']) && $this->m_params['urlNoLower'] == 'on');
    //--- проверим наличие файла
    $urls = CModel_links::LoadUrls($files_urls);
    shuffle($urls);
    $anchors   = CModel_links::LoadAnchors($files_anchors);
    $number    = 0;
    $url_i     = 0;
    $url_count = count($urls);
    //--- загрузим в зависимости от категории
    foreach($anchors as $key)
      {
      //$key = trim($key);
      //--- не берем пустые ключи
      if(empty($key)) continue;
      //--- список слов
      if(strpos($key, '|') !== FALSE) $this->keywords[$number] = new CKeywordInfo($number, $key, explode('|', $key));
      else                            $this->keywords[$number] = new CKeywordInfo($number, $key, explode('|', $key));
      if(!empty($urls)) $this->keywords[$number]->SetUrl($urls[$url_i++]);
      if($url_count >= $url_i) $url_i = 0;
      $number++;
      }
    //---
    $this->m_count_keywords  = count($this->keywords);
    $this->m_is_set_category = false;
    //--- если нужно перемешивать кейворды
    if(isset($this->m_params['keysRandom']) && $this->m_params['keysRandom'] == 'on')
      {
      $keysRandomMin = (int)$this->m_params['keysRandomMin'];
      $keysRandomMax = (int)$this->m_params['keysRandomMax'];
      //--- проверим мин. и макс. значения
      if($keysRandomMin < 0) $keysRandomMin = 0;
      if($keysRandomMax < 0) $keysRandomMax = 0;
      if($keysRandomMax < $keysRandomMin) $keysRandomMax = $keysRandomMin;
      //--- если кейворды с категориями из файла, то сохраняем структуру категорий
      //--- перемешиваем кейводы (так быстрее, чем получить случайные кейворды в цикле)
      shuffle($this->keywords);
      //--- выбираем из них указанную часть
      $r              = mt_rand($keysRandomMin, $keysRandomMax);
      $this->keywords = array_slice($this->keywords, 0, $r);
      //--- обновим кол-во кейвордов
      $this->m_count_keywords = count($this->keywords);
      }
    }

  /**
   * Установка урлов
   */
  private function SetUrlsKeywords()
    {
    foreach($this->keywords as $num => &$key_info)
      {
      $key = $key_info->getKeywordIndex(0);
      $key_info->setUrl($this->GetUrlForKey($key, $num));
      $key_info->setFilename($this->GetPageName($num));
      $key_info->setId($num);
      }
    }

  /**
   * Получение расширения для текущих настроек
   * @return string
   */
  public function GetExtension()
    {
    return $this->m_extension;
    }

  /**
   * установка слов по категориям
   * @param int $min_categories
   * @param int $max_categories
   */
  private function SetAutoCategories($min_categories, $max_categories)
    {
    $template = $this->m_params['templateAutoCategories'];
    if(empty($template)) $template = 'path-[N]';
    //--- 0 индекс занят всегда по определению
    $index_keyword = 1;
    //--- индекс категории
    $n = 1;
    //--- пока не обработали все слова
    while($index_keyword < $this->m_count_keywords)
      {
      //--- количество слов в категории
      $count = rand($min_categories, $max_categories);
      //--- имя
      $keyword_info = $this->GetKeywordByNum($index_keyword);
      $name         = str_replace('[N]', $n, $template);
      $name         = str_replace('[KEYWORD]', $keyword_info->getKeyword(0), $name);
      //---
      if($this->m_not_use_translit)
        {
        $url = CModel_tools::ClearUrlSymbols($name);
        }
      else
        {
        //--- вызов транслита из плагина
        $url = '';
        $this->m_model_plugins->OnTranslitUrl($name, $url);
        if(empty($url)) $url = CModel_tools::Translit($name, $this->m_convert_lower);
        }
      //--- сохраним в массив
      $this->m_categories[] = new Category_keywords($name, $url, $index_keyword, $index_keyword + $count - 1);
      //--- переходим к следующему
      $index_keyword += $count;
      $n++;
      }
    }

  /**
   * распарсим данные из админке
   * @param string $data
   *
   * @return array|null
   */
  private function ParsingInfoCategories($data)
    {
    if(empty($data)) return null;
    $ar     = explode("\n", $data);
    $result = array();
    //--- бежим по всем строчкам
    foreach($ar as $info)
      {
      $info = trim($info);
      if(empty($info)) continue;
      //---
      $cat_info = new CategoryInfo();
      //---
      $line = explode('|', $info);
      //--- первый столбец название категории
      $cat_info->name = $line[0];
      //--- второй стоблец урл категории
      if(!(empty($line[1]))) $cat_info->url = $line[1];
      else
        {
        if($this->m_not_use_translit)
          {
          $cat_info->url = CModel_tools::ClearUrlSymbols($cat_info->name);
          }
        else
          {
          //--- вызов транслита из плагина
          $url_cat = '';
          if($this->m_model_plugins != null) $this->m_model_plugins->OnTranslitUrl($cat_info->name, $url_cat);
          if(empty($url_cat)) $cat_info->url = CModel_tools::Translit($cat_info->name, $this->m_convert_lower);
          else $cat_info->url = $url_cat;
          }
        }
      //--- третий столбец количество слов в категории
      if(!(empty($line[2]))) $cat_info->keyword_count = (int)$line[2];
      else $cat_info->keyword_count = 0;
      //---
      $result[] = $cat_info;
      }
    //---
    return $result;
    }

  /**
   * Установка случайных категорий

   */
  private function SetRandomCategories()
    {
    $categories = $this->ParsingInfoCategories($this->m_params['keywordsCategoies']);
    //--- рассчитаем примерное кол-во ключей для каждой категории
    $keysInCategory = (int)(sizeof($this->keywords) / sizeof($categories));
    if($keysInCategory < 1) $keysInCategory = 1;
    //--- рассчитаем добавочное кол-во ключей для каждой категории
    //--- (чтобы не было одинаковое кол-во ключей в каждой категории)
    $sliceVariable = (int)($keysInCategory / 15);
    if($sliceVariable < 1) $sliceVariable = 1;
    //--- 0 индекс занят всегда по определению
    $index_keyword = 1;
    foreach($categories as $category)
      {
      //--- $count - количество слов в категории
      if($category->keyword_count > 0) $count = $category->keyword_count;
      else $count = $keysInCategory + rand($sliceVariable * -1, $sliceVariable);
      //---
      if($count <= 0) $count = 1;
      //--- нужно вставить слово
      array_splice($this->keywords, $index_keyword, 0, $category->name);
      $this->keywords[$index_keyword] = new CKeywordInfo($index_keyword, $category->name, array($category->name), '');
      //--- сохраним в массив
      $this->m_categories[] = new Category_keywords($category->name, $category->url, $index_keyword, $index_keyword + $count - 1);
      $index_keyword += $count;
      }
    //--- у последней категории до конца
    if(!empty($this->m_categories)) $this->m_categories[count($this->m_categories) - 1]->setKeywordEnd(count($this->keywords));
    }

  /**
   * Получение облака тега
   */
  private function GetTags()
    {
    if(isset($this->m_params['fromTags']) && isset($this->m_params['toTags'])) $count_tags = rand($this->m_params['fromTags'], $this->m_params['toTags']);
    else       $count_tags = self::MAX_TAGS;
    //--- создадим облако тегов
    $keys = $this->keywords;
    //--- перемешаем ключи
    shuffle($keys);
    //--- максимальный размер шрифта 24pt
    $i = 0;
    foreach($keys as $key)
      {
      //---
      $this->m_tags[] = array('size' => rand(11, 24),
                              'num'  => $key->GetId()
        /*$this->GetIdKeyword($k)*/,
                              'key'  => $key);
      $i++;
      //--- проверяем, может уже хватит?
      if($i >= $count_tags) break;
      }
    }

  /**
   * Нацдем id кейворда по его значению
   *
   * @param $key
   *
   * @return int|string
   */
  /*private function GetIdKeyword($key)
    {
    foreach($this->keywords as $id => $keyword)
      {
      if(is_array($keyword))
        {
        if(array_search($key, $keyword)) return $id;
        }
      else
        {
        if($key == $keyword) return $id;
        }
      }
    }
*/
  /**
   * Перемешиваем кейворды в категориях. Эти категории установлены в самом файле с ключами
   */
  private function ShiffleKeywordsCategories($max_keyword = 0)
    {
    //--- рассчитаем примерное кол-во ключей для каждой категории
    if($max_keyword <= 0) $keysInCategory = (int)(sizeof($this->keywords) / sizeof($this->m_categories));
    else
    $keysInCategory = (int)($max_keyword / sizeof($this->m_categories));
    if($keysInCategory < 1) $keysInCategory = 1;
    //--- рассчитаем добавочное кол-во ключей для каждой категории
    //--- (чтобы не было одинаковое кол-во ключей в каждой категории)
    $sliceVariable = (int)($keysInCategory / 10);
    if($sliceVariable < 1) $sliceVariable = 1;
    $new_keywords = array();
    //--- перемешиваем первые кейводры
    if($this->m_categories[0]->getKeywordBegin() > 0)
      {
      $keys = array_slice($this->keywords, 0, $this->m_categories[0]->getKeywordBegin());
      shuffle($keys);
      $new_keywords = $keys;
      }
    else
      {
      $new_keywords = array_slice($this->keywords, 0, 1);
      }
    $i = count($new_keywords);
    //--- пройдемся по списку категорий, и в этом же массиве будем менять индексы на категории
    foreach($this->m_categories as $num => $category_info)
      {
      if($max_keyword > 0 && $i > $max_keyword) break;
      //---
      if($category_info->getKeywordsCount() <= 0) continue;
      //--- определим, сколько случайных кейвордов нужно взять для каждой категории
      $slice = $keysInCategory + rand($sliceVariable * -1, $sliceVariable);
      if($slice < 1) $slice = 1;
      if($slice > $category_info->getKeywordsCount()) $slice = $category_info->getKeywordsCount();
      //--- получим кейворды которые принадлежат данной категории
      $keys = array_slice($this->keywords, $category_info->getKeywordBegin() + 1, $category_info->getKeywordsCount() - 1);
      //--- получаем выбранную часть кейвордов и удаляем их из общего массива
      if(empty($keys)) continue;
      //--- перемешиваем
      shuffle($keys);
      //---
      $sliceKeywords = array_splice($keys, 0, $slice);
      //--- сохраним сам кейворд с категорией
      $new_keywords[$i] = $this->keywords[$category_info->getKeywordBegin()];
      $i++;
      //--- устанавливаем индекс первого кейводра для категории
      $this->m_categories[$num]->setKeywordBegin($i - 1);
      //--- если кейвордов больше не осталось
      if(sizeof($sliceKeywords) == 0)
        {
        $this->m_categories[$num]->setKeywordEnd($i - 1);
        break;
        }
      //--- добавляем выбранную часть кейвордов
      foreach($sliceKeywords as $sliceItemKey => $sliceItemValue)
        {
        //--- добавляем кейворд в список
        $new_keywords[$i] = $sliceItemValue;
        //--- индекс кейвородв
        $i++;
        }
      //--- установка конечного индекса
      $this->m_categories[$num]->setKeywordEnd($i - 1);
      }
    //---
    $this->keywords = $new_keywords;
    }

  /**
   * Получение общее количество ключевиков
   */
  public function GetCountKeywords($to = -1)
    {
    return $to == -1 || $this->m_count_keywords_published <= 0 ? $this->m_count_keywords : max($this->m_count_keywords_published, $to);
    }

  /**
   * Получение общее количество ключевиков
   */
  public function GetCountPublished()
    {
    return $this->m_count_keywords_published;
    }

  /**
   * Получение ключивика по номеру
   * @param int $i
   *
   * @return CKeywordInfo
   */
  public function GetKeywordByNum($i)
    {
    if(isset($this->keywords[$i]))
      {
      return $this->keywords[$i];
      }
    return null;
    }

  /**
   * Получение названия категории для
   * @param int $num
   *
   * @return Category_keywords
   */
  public function GetCategoryByNumKey($num)
    {
    if(empty($this->m_categories)) return null;
    foreach($this->m_categories as $cat) if(($cat->getKeywordBegin() <= $num) && ($num <= $cat->getKeywordEnd())) return $cat;
    return null;
    }

  /**
   * Получение названия категории из ключа
   * @param string $key
   *
   * @return string
   */
  private function GetCategoryFromKey($key)
    {
    return substr($key, 1, strlen($key) - 2);
    }

  /**
   * Данные по облаку тегов
   */
  public function GetTagsData()
    {
    return $this->m_tags;
    }

  /**
   * Формирование урлов для одностраничников
   * @param $key
   * @param $n
   *
   * @return string
   */
  private function GetPageNameKeyForOnePage($key, $n)
    {
    if($n == 0) return $this->m_params['nextUrl'];
    if($this->m_model_plugins != null) $this->m_model_plugins->OnTranslitUrl($key, $url);
    if(empty($url)) $key = CModel_tools::Translit($key);
    else $key = $url;
    if(isset($this->m_params['onepage_oneword']) && $this->m_params['onepage_oneword'] == 'on') $key = str_replace('-', '', $key);
    return 'http://' . $key . '.' . $this->m_params['clearnextUrl'];
    }

  /**
   * Получаем название страницы (файла), если есть категории то и с категорией
   * по кейворду
   * @param string $key
   * @param int $n
   *
   * @return null|string
   */
  /*  public function GetPageNameKey($key, $n)
      {
      if(isset($this->m_params['onepage_create']) && $this->m_params['onepage_create'] == 'on') return $this->GetPageNameKeyForOnePage($key, $n);
      //--- получаем тип дорвея
      $isStatic = $this->m_params['type'] == 'static';
      //--- если делаем статический дорвей
      if($isStatic)
        {
        //--- получаем шаблон названия файлов страниц
        $pageName = $this->m_params['staticPageNamesFrom'] == 'list' ? $this->m_params['staticPageName'] : $this->m_params['staticPageNameCustom'];
        } //--- если делаем динамический дорвей
      else
        {
        //--- получаем шаблон названия файлов страниц
        $pageName = $this->m_params['dynamicPageNamesFrom'] == 'list' ? $this->m_params['dynamicPageName'] : $this->m_params['dynamicPageNameCustom'];
        }
      //--- проверяем этот ключ находится в какой-нибудь категории
      $category = $this->GetCategoryByNumKey($n);
      //--- если это не первая страница
      if($n > 0)
        {
        $pageName = str_replace('[N]', $n, $pageName);
        //---
        //$key = $this->m_not_use_translit ? CModel_tools::ClearUrlSymbols($key) : CModel_tools::Translit($key);
        if($this->m_not_use_translit) $key = CModel_tools::ClearUrlSymbols($key);
        else
          {
          $temp_url = '';
          $this->m_model_plugins->OnTranslitUrl($key, $temp_url);
          if(empty($temp_url)) $key = CModel_tools::Translit($key);
          else $key = $temp_url;
          }
        if(isset($this->m_params['onepage_oneword']) && $this->m_params['onepage_oneword'] == 'on') $key = str_replace('-', '', $key);
        //---
        $pageName = str_replace('[KEYWORD]', $key, $pageName);
        if(!empty($category) && ($category->getKeywordBegin() != $n))
          {
          $pageName = str_replace('[TOPIC]', $category->getUrl(), $pageName);
          }
        else
          {
          $pageName = str_replace('[TOPIC]', '', $pageName);
          }
        //--- зачистим, т.к. ТОПИК может быть пустым, а урл к дорвею всегда заканчивается /
        $pageName = ltrim($pageName, '/');
        }
      //--- если это первая (индексная) страница
      else
      $pageName = $isStatic ? ('index.' . $this->m_extension) : '';
      //---
      if($isStatic && ($category != null))
        {
        if($category->getKeywordBegin() == $n) $pageName = 'index.' . $this->m_extension;
        $pageName = $category->getUrl() . '/' . ($pageName != ('index.' . $this->m_extension) ? ($pageName) : '');
        }
      //--- нужно добавить http
      if(!CModel_helper::IsExistHttp($pageName)) $pageName = $this->m_params['nextUrl'] . $pageName;
      //---
      $this->m_model_plugins->OnGetPageName($key, $pageName, $n);
      //---
      return $pageName;
      }
  */
  /**
   * Получаем название страницы (файла), если есть категории то и с категорией
   * по кейворду
   * @param string $key
   * @param int $n
   *
   * @return null|string
   */
  private function GetUrlForKey($key, $n)
    {
    if(isset($this->m_params['onepage_create']) && $this->m_params['onepage_create'] == 'on') return $this->GetPageNameKeyForOnePage($key, $n);
    //--- получаем тип дорвея
    $isStatic = $this->m_params['type'] == 'static';
    //--- если делаем статический дорвей
    if($isStatic)
      {
      //--- получаем шаблон названия файлов страниц
      $pageName = $this->m_params['staticPageNamesFrom'] == 'list' ? $this->m_params['staticPageName'] : $this->m_params['staticPageNameCustom'];
      } //--- если делаем динамический дорвей
    else
      {
      //--- получаем шаблон названия файлов страниц
      $pageName = $this->m_params['dynamicPageNamesFrom'] == 'list' ? $this->m_params['dynamicPageName'] : $this->m_params['dynamicPageNameCustom'];
      }
    //--- проверяем этот ключ находится в какой-нибудь категории
    $category = $this->GetCategoryByNumKey($n);
    //--- если это не первая страница
    if($n > 0)
      {
      $pageName = str_replace('[N]', $n, $pageName);
      //---
      //$key = $this->m_not_use_translit ? CModel_tools::ClearUrlSymbols($key) : CModel_tools::Translit($key);
      if($this->m_not_use_translit) $key = CModel_tools::ClearUrlSymbols($key);
      else
        {
        $temp_url = '';
        $this->m_model_plugins->OnTranslitUrl($key, $temp_url);
        if(empty($temp_url)) $key = CModel_tools::Translit($key, $this->m_convert_lower);
        else $key = $temp_url;
        }
      if(isset($this->m_params['onepage_oneword']) && $this->m_params['onepage_oneword'] == 'on') $key = str_replace('-', '', $key);
      //---
      $pageName = str_replace('[KEYWORD]', $key, $pageName);
      if(!empty($category) && ($category->getKeywordBegin() != $n))
        {
        $pageName = str_replace('[TOPIC]', $category->getUrl(), $pageName);
        }
      else
        {
        $pageName = str_replace('[TOPIC]', '', $pageName);
        }
      //--- зачистим, т.к. ТОПИК может быть пустым, а урл к дорвею всегда заканчивается /
      $pageName = ltrim($pageName, '/');
      }
    //--- если это первая (индексная) страница
    else
    $pageName = $isStatic ? ('index.' . $this->m_extension) : '';
    //---
    if($isStatic && ($category != null))
      {
      if($category->getKeywordBegin() == $n) $pageName = 'index.' . $this->m_extension;
      $pageName = $category->getUrl() . '/' . ($pageName != ('index.' . $this->m_extension) ? ($pageName) : '');
      }
    //--- нужно добавить http
    if(!CModel_helper::IsExistHttp($pageName)) $pageName = $this->m_params['nextUrl'] . $pageName;
    //---
    if($this->m_model_plugins != null) $this->m_model_plugins->OnGetPageName($key, $pageName, $n);
    //---
    return $pageName;
    }

  /**
   * получение части урла на страницу category/namepage.html
   *
   * @param $n
   *
   * @return null|string
   */
  public function GetPageNameNumber($n)
    {
    $key = $this->GetKeywordByNum($n);
    return $key != null ? $key->GetUrl() : ''; //$this->GetPageNameKey($key, $n);
    }

  /**
   * Получаем название страницы (файла) без категории
   * по порядковому номеру кейворда
   * @param int $n
   *
   * @return string
   */
  public function GetPageName($n)
    {
    //$key = $this->GetKeywordByNum($n);
    //return $this->GetPageNameKey($key,$n);
    //--- получаем тип дорвея
    $isStatic = $this->m_params['type'] == 'static';
    //--- если делаем статический дорвей
    if($isStatic)
      {
      //--- получаем шаблон названия файлов страниц
      $pageName = $this->m_params['staticPageNamesFrom'] == 'list' ? $this->m_params['staticPageName'] : $this->m_params['staticPageNameCustom'];
      } //--- если делаем динамический дорвей
    else
      {
      //--- получаем шаблон названия файлов страниц
      $pageName = $this->m_params['dynamicPageNamesFrom'] == 'list' ? $this->m_params['dynamicPageName'] : $this->m_params['dynamicPageNameCustom'];
      }
    //--- если это не первая страница
    if($n > 0)
      {
      $pageName = str_replace('[N]', $n, $pageName);
      $k        = '';
//--- проверим нужно ли использовать транслит
      $key_info = $this->GetKeywordByNum($n);
//var_dump($key_info); exit;
      if($this->m_not_use_translit)
        {
        $key = CModel_tools::ClearUrlSymbols($key_info->GetKeywordIndex(0));
        $k   = $key;
        //--- из-за особенности виндоус
        if($isStatic && isset($this->m_params['urlsOnLanguage']) && $this->m_params['urlsOnLanguage'] == 'on' && substr(PHP_OS, 0, 3) == "WIN")
          {
          $k = iconv('UTF-8', 'windows-1251//TRANSLIT', $k);
          }
        }
      else
        {
        $k        = $key_info->GetKeywordIndex(0);
        $temp_url = '';
        $this->m_model_plugins->OnTranslitUrl($k, $temp_url);
        if(empty($temp_url)) $k = CModel_tools::Translit($k, $this->m_convert_lower);
        else $k = $temp_url;
        }
      $pageName = str_replace('[KEYWORD]', $k, $pageName);
      $pageName = str_replace('[TOPIC]', '', $pageName);
      //--- зачистим, т.к. ТОПИК может быть пустым, а урл к дорвею всегда заканчивается /
      $pageName = ltrim($pageName, '/');
      if(isset($this->m_params['onepage_oneword']) && $this->m_params['onepage_oneword'] == 'on') $pageName = str_replace('-', '', $pageName);
      } //--- если это первая (индексная) страница
    else
    $pageName = $isStatic ? ('index.' . $this->m_extension) : '';
    //--- NOTE: закомментировал, т.к. нельзя передавать результат функции "GetKeywordByNum($n)" как параметр переменной
    //$this->m_model_plugins->OnGetPageName($this->GetKeywordByNum($n), $pageName, $n);
    $keywordByNum = $this->GetKeywordByNum($n);
    if($this->m_model_plugins != null) $this->m_model_plugins->OnGetPageName($keywordByNum, $pageName, $n);
    //---
    return $pageName;
    }

  /**
   * Получаем полный урл на категорию
   * @param Category_keywords $category
   *
   * @return string
   */
  public function GetCategoryUrl($category)
    {
    //--- получаем тип дорвея
    $isStatic = $this->m_params['type'] == 'static';
    //--- если делаем статический дорвей
    if($isStatic)
      {
      //--- получаем шаблон названия файлов страниц
      $pageName = $category->getUrl();
      }
    //--- если делаем динамический дорвей
    else
      {
      //--- получаем шаблон названия файлов страниц
      $pageName = $this->m_params['dynamicPageNamesFrom'] == 'list' ? $this->m_params['dynamicPageName'] : $this->m_params['dynamicPageNameCustom'];
      //---
      $p = stripos($pageName, '[TOPIC]');
      if($p !== false && ($p == 0 || $p == 1))
        {
        $pageName = str_replace('[KEYWORD]', '', $pageName);
        $pageName = rtrim($pageName, '/');
        }
      else
        {
        $kname    = $this->m_not_use_translit ? CModel_tools::ClearUrlSymbols($category->getName()) : $category->getUrl();
        $pageName = str_replace('[KEYWORD]', $kname, $pageName);
        if(empty($kname)) $pageName = rtrim($pageName, '/');
        }
      $pageName = str_replace('[TOPIC]', $category->getUrl(), $pageName);
      $pageName = str_replace('[N]', $category->getKeywordBegin(), $pageName);
      }
    //--- нужно добавить http
    if(!CModel_helper::IsExistHttp($pageName)) $pageName = $this->m_params['nextUrl'] . $pageName;
    //---
    return $pageName;
    }

  /**
   * Получаем рандомный кейворд
   */
  public function GetRandKeyword(&$num, $to = -1)
    {
    //--- проверим можно использовать все ключевики или только меньше
    $n = rand(0, $this->GetCountKeywords($to) - 1);
    if($n < 0) $n = 0;
    //---
    $key = $this->GetKeywordByNum($n);
    $num = $n;
    return $key;
    }

  /**
   * получение модуля для кейворда и если такого нет, то инцилизируем
   */
  public function GetLoadedModules($filename)
    {
    if(!isset($this->m_file_keywords_list[$filename]))
      {
      if(!$this->LoadListFileKeywords($filename)) return null;
      }
    return $this->m_file_keywords_list[$filename];
    }

  /**
   * Получаем рандомный кейворд из указанного файла
   * @param $filename
   * @return CKeywordInfo
   */
  public function GetRandKeywordFromFile($filename)
    {

    if(!isset($this->m_file_keywords_list[$filename]))
      {
      if(!$this->LoadListFileKeywords($filename)) return null;
      }
    //--- проверим можно использовать все ключевики или только меньше
    $n = rand(0, $this->m_file_keywords_list[$filename]->GetCountKeywords() - 1);
    //---
    return $this->m_file_keywords_list[$filename]->GetKeywordByNum($n);
    }

  /**
   * Получаем рандомные кейворды из указанного файла, в виде массива
   * @param $filename
   * @return CKeywordInfo
   */
  public function GetRandKeywordsListFromFile($count, $filename)
    {
    for($i = 0; $i < $count; $i++)
      {
      $key_info = $this->GetRandKeywordFromFile($filename);
      if(empty($key_info)) continue;
      $res[] = $key_info->getKeywordRand();
      }
    return $res;
    }

  /**
   * Получаем рандомный кейворд из указанного файла
   */
  public function GetRandKeywordsFromFiles($count, $files)
    {
    //--- получение ключевиков из файла 
    $j           = 0;
    $count_files = count($files);
    $result      = '';
    for($i = 0; $i < $count; $i++)
      {
      $key = $this->GetRandKeywordFromFile($files[$j]);
      if($key != null) $result .= (!empty($result) ? ', ' : '') . $key->getKeywordRand();
      $j++;
      if($j >= $count_files) $j = 0;
      }
    return $result;
    }

  /**
   * Получение списка ключевиков
   */
  public function GetRandKeywords($id, $to = -1)
    {
    return $this->GetKeywordByNum($id)->getKeywordIndex(0) . ', ' . $this->GetRandKeyword($id, $to)->getKeywordIndex(0) . ', ' . $this->GetRandKeyword($id, $to)->getKeywordIndex(0);
    }

  /**
   * Все категории
   */
  public function GetCategoies()
    {
    return $this->m_categories;
    }

  /**
   * Получение списка ключевиков: array(key,url)
   *
   * @param int $count
   *
   * @return array
   */
  public function GetRandKeywordsLinks($count = 5)
    {
    $ret = array();
    for($i = 0; $i < $count; $i++)
      {
      $n   = 0;
      $key = $this->GetRandKeyword($n);
      if($key != null)
        {
        $url   = $key->GetUrl();
        $ret[] = array('key' => $key->GetKeywordIndex(0),
                       'url' => $url);
        }
      }
    return $ret;
    }

  /**
   * Загрузка стоп слов
   * @return mixed
   */
  private static function LoadStopWords()
    {
    $filename = CModel_Rewriter::DICTIONARY_PATH . 'stop.words.dat';
    if(!file_exists($filename)) return;
    $words = file($filename);
    if(count($words) <= 0) return;
    //---
    $isUtf8 = CModel_tools::IsUTF8($words[0]) ? 1 : 0;
    //--- проверим кодировку
    foreach($words as $word)
      {
      //--- пытаемся сконвертировать из windows-1251
      if($isUtf8 < 1) $word = mb_convert_encoding($word, 'UTF-8', 'WINDOWS-1251');
      //---
      $word = trim($word);
      if(!isset(self::$m_stop_words[$word])) self::$m_stop_words[$word] = true;
      }
    }

  /**
   * Перемешиваем ключевики
   */
  public static function MixWordsInKey($key)
    {
    $ar = explode(' ', $key);
    if(count($ar) <= 1) return $key;
    //---
    $count  = count($ar);
    $result = '';
    $i      = 0;
    while($i < $count)
      {
      $word = trim($ar[$i]);
      if(empty($word))
        {
        $i++;
        continue;
        }
      //--- если это стоп слово, то его перемешиваем только со следующим
      if(isset(self::$m_stop_words[$word]))
        {
        $j    = $i;
        $r    = '';
        $temp = $word;
        //---
        while($j < $count && isset(self::$m_stop_words[$temp]))
          {
          $temp = trim($ar[$j]);
          if(empty($temp)) continue;
          $r = (!empty($r) ? ' ' : '') . $temp;
          $j++;
          }
        //---
        $word = $r;
        $i    = $j - 1;
        }
      //--- вставим либо в начало, либов конец строки
      if(rand(0, 1)) $result .= (!empty($result) ? ' ' : ' ') . $word;
      else  $result = $word . (!empty($result) ? ' ' : ' ') . $result;
      $i++;
      }
    //---
    return $result;
    }
  }

?>