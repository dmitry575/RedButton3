<?php
//+------------------------------------------------------------------+
//|                  Copyright c 2012, RedButton Web Sites Generator |
//|                                      http://www.getredbutton.com |
//+------------------------------------------------------------------+
/*
 * Класс генерации текста
 * Пример запуска: $CText=new CTextGenerator('text.txt');
 */
class CModel_TextMarkov
  {
//--- максимальный размер текста 8мб - UTF-8 текста, иначе все убивается нахрен
  const MAX_SIZE_TEXT = 8388608;
  private $trigrams = array();
  private $model = array();
  /**
   * Параметы
   * @var array
   */
  private $m_params = array();
  /**
   * Большой ли это файл
   * @var bool
   */
  private $m_is_big = false;
  /**
   * Номер для большого файла
   * @var int
   */
  private $m_big_number = 0;
  /**
   * Сколько всего кусков
   * @var int
   */
  private $m_count_numbers = 0;
  /**
   * Установлен ли кешер APC
   * @var bool
   */
  private $m_has_apc = false;

  /**
   * Инициализация
   * TODO: НЕОБХОДИМО В INDEX.PHP ИЛИ GENERATION.PHP ВЫЗЫВАТЬ МЕТОД, УКАЗЫВАЮЩИЙ КОДИРОВКУ UTF-8 (ПОСМОТРИ КАК СДЕЛАНО В СТАРОМ ДОРГЕНЕ)
   */
  function __construct($fileName, $is_need_print = true, &$params)
    {
    if(!file_exists($fileName))
      {
      //print 'Error: TextGenerator: file '.$fileName.' not found!<br>';
      CLogger::write(CLoggerType::ERROR, "markov: file " . $fileName . " not found");
      return false;
      }
    //---
    $this->m_params = $params;
    //---
    $this->m_has_apc = extension_loaded('apc');
    //--- получили большой файл
    $size = filesize($fileName);
    if($size > (self::MAX_SIZE_TEXT * 2))
      {
      //--- получим количество кусков текста
      $this->m_count_numbers = floor($size / (self::MAX_SIZE_TEXT * 2));
      $this->m_is_big        = true;
      //--- текущий кусок текста
      $this->m_big_number = -1;
      if($this->m_has_apc)
        {
        $data = apc_fetch('markov_big_number');
        if($data != null && $data > -1)
          {
          $this->m_big_number = $data;
          CLogger::write(CLoggerType::DEBUG, 'Markov: apc get number file: ' . $this->m_big_number);
          }
        }
      //---
      if($this->m_big_number < 0)
        {
        $this->m_big_number = rand(0, $this->m_count_numbers);
        //-- сохраним в кеш номер текушего номер, сохраним на 10 минут
        if(apc_store('markov_big_number', $this->m_big_number, 3600)) CLogger::write(CLoggerType::DEBUG, 'Markov: apc set current number file: ' . $this->m_big_number);
        }
      }
    //---
    $pathinfo = pathinfo($fileName);
    //--- для больших файлов кеши будут свои
    if($this->m_is_big) $modelFileName = $pathinfo['dirname'] . '/' . $pathinfo['filename'] . '_' . $this->m_big_number . '.model.php';
    else
    $modelFileName = $pathinfo['dirname'] . '/' . $pathinfo['filename'] . '.model.php';
    //--- если модель файла уже существует
    if(file_exists($modelFileName))
      {
      CLogger::write(CLoggerType::DEBUG, 'Markov: loading model...');
      if($is_need_print)
        {
        CModel_helper::PrintInfo('Алгоритм Маркова: загрузка модели...', true);
        //--- узнаем время начала загрузки
        $timeStart = microtime(true);
        }
      //--- загружаем текстовую модель триграмм
      $this->LoadModel($modelFileName);
      //--- пишем время загрузки
      if($is_need_print) CModel_helper::PrintInfo('Алгоритм Маркова: модель загружена за ' . (number_format(round(microtime(true) - $timeStart, 2), 2, '.', ' ')) . ' сек.', true);
      }
    //--- если модели файла нет - создадим ее
    else
      {
      //--- попробуем создать все файлы сразу
      $timeStart = microtime(true);
      if($this->m_is_big)
        {
        //--- загрузка всех модулей всех сразу
        for($i = 0; $i <= $this->m_count_numbers; $i++)
          {
          $this->m_big_number = $i;
          $modelFileName      = $pathinfo['dirname'] . '/' . $pathinfo['filename'] . '_' . $this->m_big_number . '.model.php';
          if(!file_exists($modelFileName))
            {
            unset($this->model);
            unset($this->trigrams);
            //---
            $this->LoadingToken($is_need_print, $fileName, $modelFileName);
            }
          }
        }
      else
        {
        $this->LoadingToken($is_need_print, $fileName, $modelFileName);
        }
      //--- пишем общее время генерации модели
      if($is_need_print) CModel_helper::PrintInfo('Алгоритм Маркова: модель была подготовлена за ' . round(microtime(true) - $timeStart, 2) . ' сек.', true);
      }
    CLogger::write(CLoggerType::DEBUG, 'Markov: model loaded');
    }

  /**
   * Загрузка данных
   * @param $is_need_print
   * @param $fileName
   * @param $modelFileName
   */
  private function LoadingToken($is_need_print, $fileName, $modelFileName)
    {
    //--- получаем ключи
    if($is_need_print) CModel_helper::PrintInfo('Алгоритм Маркова: получаем токены для новой модели...', true);
    $this->GetTokens($fileName);
    //--- получаем модель
    if($is_need_print) CModel_helper::PrintInfo('Алгоритм Маркова: создаем новую модель...', true);
    $this->GetModel();
    //--- сохраняем модель
    if($is_need_print) CModel_helper::PrintInfo('Алгоритм Маркова: сохраняем новую модель...', true);
    $this->SaveModel($modelFileName);
    }

  /**
   * Поиск слева направо
   * @param $text
   * @param $current
   */
  private function GetLastPos(&$text, $current)
    {
    for($i = $current; $i > -1; $i--)
      {
      $c = mb_substr($text, $i, 1, 'UTF-8'); //$text[$i];
      // охуенно долго работает: mb_substr($text,$i,1,'UTF-8');
      if($c == '.' || $c == '?' || $c == '!' || $c == "。") return $i;
      }
    return 0;
    }

  /*
   * Получаем токены
   */
  private function GetTokens($fileName)
    {
    $listWords     = array();
    $pair          = array();
    $isStart       = true;
    $sentenceCount = 0;
    $index         = array();
    //---
    CLogger::write(CLoggerType::DEBUG, 'Markov: befor reading file ' . $fileName);
    $text_file = file_get_contents($fileName);
    //--- если большой файл
    if($this->m_is_big)
      {
      //--- найдем начала откуда надо искать
      $begin_pos = 0;
      $size_text = mb_strlen($text_file, 'UTF-8');
      //---
      $this->m_big_number = 0;
      if($this->m_big_number > 0 && $this->m_big_number * self::MAX_SIZE_TEXT < $size_text)
        {
        $begin_pos = $this->GetLastPos($text_file, $this->m_big_number * self::MAX_SIZE_TEXT);
        }
      //--- до какого байта нужны данные
      $end_pos = $size_text;
      if($this->m_big_number != $this->m_count_numbers && (($this->m_big_number + 1) * self::MAX_SIZE_TEXT < $size_text))
        {
        $end_pos = $this->GetLastPos($text_file, ($this->m_big_number + 1) * self::MAX_SIZE_TEXT);
        }
      //---
      if($end_pos < $begin_pos)
        {
        $begin_pos = 0;
        $end_pos   = self::MAX_SIZE_TEXT;
        }
      //---
      $text = mb_substr($text_file, $begin_pos, $end_pos - $begin_pos, 'UTF-8');
      unset($text_file);
      $text_file = $text;
      }
    //---
    $listWords = explode(' ', str_replace("\n", ' ', str_replace("\r\n", ' ', $text_file)), 2000000);
    //---
    unset($text_file);
    CLogger::write(CLoggerType::DEBUG, 'Markov: after read file ' . $fileName);
    $sz = count($listWords);
    for($i = 0; $i < $sz && $i < 2000000; $i++)
      {
      //--- pause
      //if($i % 1000000 == 0) {var_dump("!!");exit;}
      //---
      $word = trim($listWords[$i]);
      if(empty($word)) continue;
      //---
      if($isStart)
        {
        $pair[]  = '|';
        $pair[]  = '|';
        $pair[]  = $word;
        $isStart = false;
        }
      else
        {
        $pair[] = $word;
        array_shift($pair);
        }
      //---
      $wordsPair = $pair[0] . ' ' . $pair[1] . ' ' . $pair[2];
      $key       = substr(md5($wordsPair), 0, 8);
      //---
      if(!isset($index[$key])) $this->trigrams[] = $wordsPair;
      else
      $index[$key] = null; // TODO: наверно есть другие варианты добавления ключа в массив?
      //---
      $lastChar = mb_substr($word, -1);
      if(($lastChar == '.' && mb_strlen($word) > 2) || $lastChar == '!' || $lastChar == '?' || $lastChar == '。')
        {
        //---
        unset($pair);
        $pair = array();
        //---
        $isStart = true;
        }
      }
    //--- очищаем память
    unset($listWords, $index, $pair, $wordsPair);
    }

  /*
   * Составление модели
   */
  private function GetModel()
    {
    $word0 = $word1 = $word2 = '';
    //---
    $sz = count($this->trigrams);
    for($i = 0; $i < $sz; $i++)
      {
      //--- pause
      //if($i % 10000 == 0) sleep(1);
      //---
      list($word0, $word1, $word2) = explode(' ', $this->trigrams[$i]);
      //---
      $this->model[$word0 . ' ' . $word1][] = $word2;
      }
    //---
    unset($this->trigrams);
    }

  /**
   * Сохранение модели в файл
   */
  private function SaveModel($fileName)
    {
    if(isset($this->m_params['is_cache']) && $this->m_params['is_cache'] == 1 && $this->m_has_apc)
      {
      //$res = apc_store($fileName, $this->model, 86400);
      //CLogger::write(CLoggerType::DEBUG, 'Markov: use apc_store: ' . $fileName . ' save to cache: ' . ($res ? "true" : "false"));
      }
    //--- сохраним в файл
    if(file_put_contents($fileName, json_encode($this->model)))
      {
      CLogger::write(CLoggerType::DEBUG, 'Markov: save to file: ' . $fileName . ', file size: ' . filesize($fileName) . ' bytes');
      }
//---
    return true;
    }

  /**
   * Загрузка ранее созданной модели из файла
   */
  private function LoadModel($fileName)
    {
    if($this->m_has_apc)
      {
      /*$data = apc_fetch($fileName);
      //---
      CLogger::write(CLoggerType::DEBUG, 'Markov: use apc_fetch: ' . $fileName . ', ' . (!empty($data) ? "true" : "false"));
      if(!empty($data))
        {
        $this->model = $data;
        CLogger::write(CLoggerType::DEBUG, 'Markov: loaded ' . $fileName . ' from cache');
        return true;
        }
      */
      }
    //---
    CLogger::write(CLoggerType::DEBUG, 'Markov: before include json file: ' . $fileName . ', file size: ' . filesize($fileName) . ' bytes');
    //---
    $this->model = (array)json_decode(file_get_contents($fileName));
    //include($fileName);
    //$this->model = unserialize(file_get_contents($fileName));
    //include_once($fileName);
    CLogger::write(CLoggerType::DEBUG, 'Markov: end include file: ' . $fileName);
//---
    if(isset($this->m_params['is_cache']) && $this->m_params['is_cache'] == 1 && $this->m_has_apc)
      {
      //$res = apc_store($fileName, $this->model, 86400);
      //CLogger::write(CLoggerType::DEBUG, 'Markov: use apc_store: ' . $fileName . ' saved to cache - ' . ($res ? "true" : "false"));
      }
    return true;
    }

  /**
   * Получаем обработанное случайное
   * предложение из текста
   */
  public function GetSentence()
    {
    $phrase   = '';
    $t0       = '|';
    $t1       = '|';
    $lastKeys = array();
//---
    for($i = 0; $i < 100; $i++)
      {
      $key = $t0 . ' ' . $t1;
//---
      if(empty($this->model[$key])) continue;
//---
      $old = $t1;
//--- TODO: без этой проверки на повторы вроде тоже работает, но пока оставляем
//--- ее надо переделать, сделать, чтобы сравнивало не только $key, но и полученный от него $t1,
//--- тогда можно будет 100% предотвратить зацикливание
      if(isset($lastKeys[$key])) break;
//---
      $lastKeys[$key] = 1;
//---
      $sz    = count($this->model[$key]) - 1;
      $index = rand(0, $sz);
      $t1    = $this->model[$key][$index];
      if($t1 == null) break;
//---
      $t0 = $old;
      if($t1 == '|') break;
//---
      $phrase .= ' ' . $t1;
      }
//---
    return $phrase;
    }
  }

?>