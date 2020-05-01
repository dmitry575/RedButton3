<?php
class CModel_synonimazer
  {
  //--- русский словарь
  const SYNC_RU = 1;
  //--- английский словарь
  const SYNC_EN = 2;
  //--- путь к словарям
  const DICTIONARY_PATH = 'data/dictionaries/';
  //--- список ключей
  private $m_keys;
  //--- список языков
  private $m_languages;
  //--- открытые файлы для вычитки синонимов
  private $m_file_handel_words;

  /**
   *
   * Подключаем словари для дальнейшей синонимизацией
   * @param int $lang
   */
  public function __construct($lang)
    {
    $this->m_language = $lang;
    if(($this->m_language & self::SYNC_RU) > 0)
      {
      $this->LoadKeys('ru');
      $this->m_languages[] = 'ru';
      }
    if(($this->m_language & self::SYNC_EN) > 0)
      {
      $this->LoadKeys('en');
      $this->m_languages[] = 'en';
      }
    }

  /**
   *
   * Путь к файлу с ключами
   * @param string $lang
   */
  private static function GetKeysFilename($lang)
    {
    return self::DICTIONARY_PATH . $lang . '_keys.bin';
    }

  /**
   *
   * Путь к файлу с ключами
   * @param string $lang
   */
  private static function GetWordsFilename($lang)
    {
    return self::DICTIONARY_PATH . $lang . '_words.bin';
    }

  /**
   * Проверка наличия русского словаря
   */
  public static function CheckDictionaty($lang)
    {
    return file_exists(self::GetWordsFilename($lang));
    }

  /**
   *
   * Загружаем индексы нужных языков
   * @param string $lang
   */
  private function LoadKeys($lang)
    {
    CLogger::write(CLoggerType::DEBUG, 'synonimazer:begin load keys ' . $lang );

    $file_keys  = $this->GetKeysFilename($lang);
    $file_words = $this->GetWordsFilename($lang);
    //---
    if(!file_exists($file_keys) || !file_exists($file_words))
      {
      //--- пишем в лог и выходим
      CLogger::write(CLoggerType::DEBUG, 'synonimazer: files not found: ' . $file_keys . ', ' . $file_words);
      return;
      }
    //--- попробуем загрузить в кеш
    //--- проверим есть ли кеш, и есть ли в кеше данные
    if(extension_loaded('apc'))
      {
      $data = apc_fetch($file_words);
//---
      if(!empty($data))
        {
        CLogger::write(CLoggerType::DEBUG, 'synonimazer: use apc_fetch: ' . $file_words.' load keys ' . $lang );
        $this->m_keys[$lang] = $data;
        return true;
        }
      }
    //---
    CLogger::write(CLoggerType::DEBUG, 'synonimazer: files find: ' . $file_keys . ', ' . $file_words);
    CLogger::write(CLoggerType::DEBUG, 'synonimazer: befor load keys memory: ' . memory_get_usage(true));
    CLogger::write(CLoggerType::DEBUG, 'synonimazer: keys content length: ' . $file_keys . ' ' . filesize($file_keys));
    //---
    $fkey = fopen($file_keys, "r");
    if(!$fkey)
      {
      CLogger::write(CLoggerType::DEBUG, 'synonimazer: open file failed: ' . $file_keys);
      return;
      }
    //---
    $time_start = time();
    //---
    $i = 0;
    while(!feof($fkey))
      {
      $k = fread($fkey, 104);
      if(strlen($k) < 104) continue;
      //---
      $r                  = unpack("a100key/Llen", $k);
      $m[trim($r['key'])] = $r['len'];
      $i++;
      }
    //---
    fclose($fkey);
    CLogger::write(CLoggerType::DEBUG, 'synonimazer: load keys ' . $lang . ': ' . (time() - $time_start) . ' seconds, ' . $i . ' words');
    $this->m_keys[$lang] = $m;
    //--- загрузим в кеш
    if(extension_loaded('apc'))
      {
      apc_store($file_words, $m,1200);
      CLogger::write(CLoggerType::DEBUG, 'synonimazer: use apc_store: ' . $file_words);
      }
    //--- очистка памяти
    unset($m);
    //---
    CLogger::write(CLoggerType::DEBUG, 'synonimazer: total memory used: ' . memory_get_usage(true));
    }

  /**
   * функция синонимайзинга
   *
   * @param string $text
   */
  public function Sync($text, $min_percent, $max_persent)
    {
    $this->OpenFilesWords();
    $words       = explode(' ', $text);
    $new_text    = '';
    $count_words = count($words);
    $i           = 0;
    //---
    $percent = rand($min_percent, $max_persent);
    //--- бежимся по всем словам
    while($i < $count_words)
      {
      $w = trim(trim(str_replace("\r\n", ' ', $words[$i]), " \t.!?,$:\"-\r\n"));
      if($w == '')
        {
        $i++;
        continue;
        }
      //--- берем случайное число и смотрим попало она в наш диапозон или нет
      $per = rand(0, 100);
      //--- больше нашего процента, это слово пропускаем мимо и идем дальше
      if($per > $percent)
        {
        //CLogger::write(CLoggerType::DEBUG, 'synonimazer: no sync, rand '.$per.'%, must be '.$percent);
        $new_text .= $words[$i] . ' ';
        $i++;
        continue;
        }
      $big_w    = array();
      $big_w [] = $w;
      //--- берем все 3 слов, и по этим словам ищем синонимы
      for($j = $i + 1; $j < ($i + 3) && $j < $count_words; $j++)
        {
        $w = trim(trim(str_replace("\r\n", ' ', $words[$j]), " \t.!?,$:\"-<>@\r\n"));
        if(empty($w)) continue;
        $big_w[] = $w;
        }
      //--- хоть что-нибудь нашли
      $is_sync = false;
      //--- начинаем с самого большого словосочетания и заканчиваем одним словом
      for($j = count($big_w); $j >= 0; $j--)
        {
        $word = trim(implode(' ', $big_w));
        $word = mb_strtolower($word, 'UTF-8');
        //---
        $sync_word = $this->GetSync($word);
        if($sync_word != $word)
          {
          $new_text .= $sync_word . ' ';
          $i += $j + 1;
          $is_sync = true;
          break;
          }
        unset($big_w[$j]);
        }
      if(!$is_sync)
        {
        $new_text .= $words[$i] . ' ';
        $i++;
        }
      unset($big_w);
      }
    /*
     foreach($words as $word)
     {
     //--- зачистка по краям
     $w = trim($word," \t.!?,$:\"");
     //--- получение нового слова
     $new_w = $this->GetSync($w);
     if($w!=$new_w) $new_text.=str_replace($w,$new_w,$word).' ';
     else           $new_text.=$word.' ';
     }
     */
    //---
    return $new_text;
    }

  /**
   *
   * Открытие файлов
   */
  private function OpenFilesWords()
    {
    foreach($this->m_languages as $lang)
      {
      if(empty($this->m_file_handel_words[$lang]) || !$this->m_file_handel_words[$lang])
        {
        $fname = $this->GetWordsFilename($lang);
        if(!file_exists($fname))
          {
          CLogger::write(CLoggerType::ERROR, 'file words not exists: ' . $fname);
          continue;
          }
        $this->m_file_handel_words[$lang] = fopen($fname, "r");
        CLogger::write(CLoggerType::ERROR, 'file opened: ' . $fname);
        }
      }
    }

  /**
   *
   * Закрытие дискрипшенов файлов со словами
   */
  private function CloseFilesWords()
    {
    foreach($this->m_languages as $lang)
      {
      if(!empty($this->m_file_handel_words[$lang]) && $this->m_file_handel_words[$lang])
        {
        fclose($this->m_file_handel_words[$lang]);
        }
      }
    }

  /**
   *
   * Закрытие всего
   */
  public function CloseAll()
    {
    $this->CloseFilesWords();
    }

  /**
   *
   * Находим очищенное слово в нашей базе
   * @param string $w
   */
  private function GetSync($w)
    {
    //--- языки
    foreach($this->m_languages as $lang)
      {
      if(isset($this->m_keys[$lang][$w]))
        {
        $pos = $this->m_keys[$lang][$w];
        fseek($this->m_file_handel_words[$lang], $pos);
        $data = fread($this->m_file_handel_words[$lang], 1100);
        if(empty($data))
          {
          //--- запись в лог
          CLogger::write(CLoggerType::ERROR, 'replace: read file failed, ' . $this->m_file_handel_words[$lang]);
          return;
          }
        $r    = unpack("a100key/a1000words", $data);
        $wor  = explode(',', $r['words']);
        $sz   = sizeof($wor) - 1;
        $word = trim($wor[rand(0, $sz)]);
        //---
        //CLogger::write(CLoggerType::DEBUG, 'replace: '.$w."=>".$word);
        //---
        return $word;
        }
      }
    return $w;
    }
  }

?>
