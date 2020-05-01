<?php
/**
 *
 * Класс для рерайта
 * @author User
 *
 */
class CModel_Rewriter
{
    //--- путь к словарям
    const DICTIONARY_PATH = 'data/dictionaries/';
    //---
    //--- русский словарь
    const REWRITE_RU = 1;
    //--- английский словарь
    const REWRITE_EN = 2;
    //--- список ключей
    private $m_keys;
    //--- список языков
    private $m_languages;
    //--- открытые файлы для вычитки прилагательных
    private $m_file_handel_words;
    private $m_settings_nums = true;
    //----
    private $m_settings_shake;
    private $m_settings_changestruct;
    private $m_settings_adj;
    //---
    private $m_settings_shake_from = 0;
    private $m_settings_shake_to = 0;
    //---
    private $m_settings_adj_from = 0;
    private $m_settings_adj_to = 0;
    //---
    private $line_exc = array("'", '"', "&", "<", ">", "-", "#", "(", ")", "+", "{", "}", "/", "\\", "*", "!", "?", ":", ";", "[", "]");
    private $adverbs_kol = array("очень", "мало", "много", "сильно", "почти", "чуть-чуть", "временно", "более", "менее");
    private $stopwords = array("также", "было", "будет", "даже", "очень", "уже"); // был
    //---
    public function __construct($lang, $shake_from, $shake_to, $changestruct_from, $changestruct_to, $adj_from, $adj_to)
    {
        //--- перемешка
        $this->m_settings_shake_from = $shake_from;
        $this->m_settings_shake_to = $shake_to;
        //--- изменение структуры
        $this->m_settings_changestruct_from = $changestruct_from;
        $this->m_settings_changestruct_to = $changestruct_to;
        //--- добавление прилагательных
        $this->m_settings_adj_from = $adj_from;
        $this->m_settings_adj_to = $adj_to;
        //---
        $this->m_language = $lang;
        if (($this->m_language & self::REWRITE_RU) > 0) {
            $this->LoadKeys('ru');
            $this->m_languages[] = 'ru';
        }
        if (($this->m_language & self::REWRITE_EN) > 0) {
            $this->LoadKeys('en');
            $this->m_languages[] = 'en';
        }
    }

    /**
     *
     * Путь к файлу с ключами
     * @param string $lang
     */
    private function GetKeysFilename($lang)
    {
        return self::DICTIONARY_PATH . $lang . '_adjs_keys.bin';
    }

    /**
     *
     * Путь к файлу с ключами
     * @param string $lang
     */
    private function GetWordsFilename($lang)
    {
        return self::DICTIONARY_PATH . $lang . '_adjs.bin';
    }

    /**
     *
     * Загружаем индексы нужных языков
     * @param string $lang
     */
    private function LoadKeys($lang)
    {
        $file_keys = $this->GetKeysFilename($lang);
        $file_words = $this->GetWordsFilename($lang);
        //---
        if (!file_exists($file_keys) || !file_exists($file_words)) {
            //--- пишем в лог и выходим
            CLogger::write(CLoggerType::DEBUG, 'rewriter: files not found: ' . $file_keys . ', ' . $file_words);
            return;
        }
        //---
        CLogger::write(CLoggerType::DEBUG, 'rewriter: file find: ' . $file_keys . ', ' . $file_words);
        CLogger::write(CLoggerType::DEBUG, 'rewriter: befor load keys memory: ' . memory_get_usage(true));
        CLogger::write(CLoggerType::DEBUG, 'rewriter: keys content length: ' . $file_keys . ' ' . filesize($file_keys));
        //--- ключи загрузим из кеша, если это возможно
    //--- проверим есть ли кеш, и есть ли в кеше данные
    if(extension_loaded('apc'))
      {
      $data = apc_fetch($file_keys);
      if(!empty($data))
        {
        CLogger::write(CLoggerType::DEBUG, 'rewrite: use apc_fetch: ' . $file_keys.' load keys ' . $lang );
        $this->m_keys[$lang] = $data;
        return true;
        }
      }

        //---
        $fkey = fopen($file_keys, "r");
        if (!$fkey) {
            CLogger::write(CLoggerType::DEBUG, 'rewriter: open file failed: ' . $file_keys);
            return;
        }
        //---
        $time_start = time();
        //---
        $i = 0;
        while (!feof($fkey)) {
            $k = fread($fkey, 104);
            if (strlen($k) < 104) continue;
            //---
            $r = unpack("a100key/Llen", $k);
            $m[trim($r['key'])] = $r['len'];
            $i++;
        }
        //---
        fclose($fkey);
        CLogger::write(CLoggerType::DEBUG, 'rewriter: load keys ' . $lang . ': ' . (time() - $time_start) . ' seconds, ' . $i . ' words');
        $this->m_keys[$lang] = $m;
        if(extension_loaded('apc'))
         {apc_store($file_keys, $m, 1200);}
        //--- очистка памяти
        unset($m);
    }

    /**
     *
     * выполнение рерайта
     * @param array $textarray
     */
    public function Rewrite(&$textarray)
    {
        $temp = "";
        $newTextArray = array();
        //---
        $this->m_settings_changestruct = rand($this->m_settings_changestruct_from, $this->m_settings_changestruct_to);
        $this->m_settings_shake = rand($this->m_settings_shake_from, $this->m_settings_shake_to);
        $this->m_settings_adj = rand($this->m_settings_adj_from, $this->m_settings_adj_to);
        // Перемешиваем предложения до цикла
        if ($this->m_settings_shake > 0) $textarray = $this->Shake($textarray);
        //--- колдуем над каждым предложеним по отдельности
        for ($i = 0, $sz = count($textarray); $i < $sz; $i++) {
            $temp = $textarray[$i];
            //--- добавим прилагательное
            if ($this->m_settings_adj > 0) {
                //--- если нужно открываем файлы со словарями
                $this->OpenFilesWords();
                //---
                $words = explode(' ', $temp);
                $k = 0;
                $new_text = '';
                //---
                while ($k < count($words)) {
                    $w = trim(trim(str_replace("\r\n", ' ', $words[$k]), " \t.!?,$:\"-\r\n"));
                    if ($w == '') {
                        $new_text .= $words[$k] . ' ';
                        $k++;
                        continue;
                    }
                    //---
                    $rand_percent = rand(0, 100);
                    if ($rand_percent > $this->m_settings_adj) {
                        //---
                        //CLogger::write(CLoggerType::DEBUG, 'rewriter: no adj, rand '.$rand_percent.', must be '.$this->m_settings_adj);
                        $new_text .= $words[$k] . ' ';
                        $k++;
                        continue;
                    }
                    $w = mb_strtolower($w, 'UTF-8');
                    //---
                    $adj = $this->GetAdj($w, 1);
                    //---
                    if (!empty($adj) && $adj != $w) {
                        $new_text .= $adj . ' ' . $words[$k] . ' ';
                    } else $new_text .= $words[$k] . ' ';
                    //---
                    $k++;
                }
                $temp = $new_text;
            }
            //--- изменение структуры
            if ($this->m_settings_changestruct > 0) {
                // Колдуем над числами
                if ($this->m_settings_nums) $temp = preg_replace_callback("/([0-9]{1,})/i", array($this, 'MyRand'), $temp);
                // Меняем структуру каждого предложения
                if (rand(0, 100) < $this->m_settings_changestruct) {
                    // делаем действия, которые элементарны и нужны сразу
                    if (substr_count($temp, " ") < 3) continue; // убираем короткие предложения
                    // Операции по упрощению предложения
                    $temp = $this->GetSimpleSentence($temp);
                    // Стандартные операции для предложения
                    $temp = $this->GetTypicalSentence($temp);
                }
                //---
                //$new = trim($temp, ". ").". ";
                //---
                /*$new=str_replace("!.", "!", $new);
                 $new=str_replace("?.", "?", $new);
                 $new=str_replace(",.", ".", $new);
                 //---
                 echo "<br>";
                 */
            }
            $newTextArray[] = $temp;
        }
        $textarray = $newTextArray;
        return true;
    }

    /**
     *
     * Находим очищенное слово в нашей базе
     * @param string $w
     * @param int $c
     */
    private function GetAdj($w, $c)
    {
        //--- языки
        foreach ($this->m_languages as $lang) {
            if (isset($this->m_keys[$lang][$w])) {
                $pos = $this->m_keys[$lang][$w];
                fseek($this->m_file_handel_words[$lang], $pos);
                $data = fread($this->m_file_handel_words[$lang], 1100);
                if (empty($data)) {
                    //--- запись в лог
                    CLogger::write(CLoggerType::DEBUG, 'rewriter: not open file: ' . $lang);

                    return;
                }
                $r = unpack("a100key/a1000words", $data);
                $wor = explode(',', $r['words']);
                $sz = sizeof($wor) - 1;
                $word = trim($wor[rand(0, $sz)]);
                //---
                //CLogger::write(CLoggerType::DEBUG, 'rewriter: '.$w."=>".$word);
                //---
                return $word;
            }
        }
        return $w;
    }

    /**
     *
     * Открытие файлов
     */
    private function OpenFilesWords()
    {
        foreach ($this->m_languages as $lang) {
            if (empty($this->m_file_handel_words[$lang]) || !$this->m_file_handel_words[$lang]) {
                $fname = $this->GetWordsFilename($lang);
                if (!file_exists($fname)) {
                    CLogger::write(CLoggerType::ERROR, 'rewriter: file words not exists: ' . $fname);
                    continue;
                }
                $this->m_file_handel_words[$lang] = fopen($fname, "r");
                CLogger::write(CLoggerType::ERROR, 'rewriter: file opened: ' . $fname);
            }
        }
    }

    /**
     *
     * Закрытие дискрипшенов файлов со словами
     */
    private function CloseFilesWords()
    {
        foreach ($this->m_languages as $lang) {
            if (!empty($this->m_file_handel_words[$lang]) && $this->m_file_handel_words[$lang]) {
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

    //---Input/return array $text
    private function Shake(&$text)
    {
        $arr_size = sizeof($text);
        $sz = round($arr_size / 2);
        //---
        if ($arr_size < 3) return $text;

        if (rand(0, 100) < $this->m_settings_shake) {
            $r = rand(($arr_size - 2), ($arr_size - 1));
            $tt = $text[$r];
            $text[$r] = $text[1];
            $text[1] = $tt;
            $r = rand(($sz - 1), $sz);
            $tt = $text[$r];
            $text[$r] = $text[0];
            $text[0] = $tt;
        } else {
            $r = rand(($arr_size - 1), ($arr_size - $sz));
            $tt = $text[$r];
            $text[$r] = $text[0];
            $text[0] = $tt;
            $r = rand(1, 2);
            $tt = $text[$r];
            $text[$r] = $text[($arr_size - 1)];
            $text[($arr_size - 1)] = $tt;
        }
        //---
        $textSize = count($text) - 1;
        for ($i = 1; $i < $sz; $i++) {
            if (rand(0, 100) > $this->m_settings_shake) continue;
            $r = array_rand($text);
            $r2 = array_rand($text);
            $tt = $text[$r];
            $text[$r] = $text[$r2];
            $text[$r2] = $tt;
        }
        return array_slice($text, 0, $arr_size);
    }

    //---
    private function GetSimpleSentence($text)
    {
        $text = trim($text);
        $words = explode(" ", $text);
        $szwords = sizeof($words) - 1;
        //---
        for ($i = 1, $sz = sizeof($words) - 1; $i < $sz; $i++) {
            $word = $words[$i];
            $lword = mb_strtolower($word); // все строчные
            // Постараемся не коверкать слова, с которых начинается
            // предложение ($i=1, а не $i=0), или которые в скобках-кавычках и т.д.
            if (in_array(mb_substr($word, 0, 1), $this->line_exc)) continue;
            if (in_array(mb_substr($word, -1, 1), $this->line_exc)) continue;
            // Убираем стоп-слова
            if (in_array($lword, $this->stopwords) && rand(0, 1) == 1 && !in_array($words[($i - 1)], array("а", "я", "он", "оно", "Оно", "мы", "Он"))) {
                $words[$i] = "";
                continue;
            }
            // Убираем вводные слова
            if ($i > 0 && rand(0, 1) == 1) {
                if (($word == "в" || $word == "я") && mb_substr($words[($i - 1)], -1) == "," && ($sz - $i) > 4) {
                    $words = array_slice($words, $i);
                    $words[0] = mb_strtoupper($words[0]);
                    $sz = sizeof($words);
                    continue;
                }
            }
            // Убираем слова, идущие после "по", "на".
            if ($i > 2 && rand(0, 1) == 1 && $szwords > 6) {
                if (($word == "по" || $word == "на" || $word == "или") && !in_array($words[($i - 1)], array("несмотря", "был", "бывал", "будем", "будут")) && $i > 4 && mb_substr($words[($i - 1)], -1, 1) != ",") {
                    $words = array_slice($words, 0, $i);
                    $sz = sizeof($words);
                    //$words[($sz-1)].=$flag;
                    continue;
                }
            }
            //---
        }
        //---
        unset($text);
        return trim(implode(" ", $words));
    }

    //---
    private function GetTypicalSentence($text)
    {
        $text = trim($text);
        // Убираем слова, которые в скобках
        $text = preg_replace("/\([^\(\)]{1,}\)/", "", $text);
        // Меняем "part1, part2"  на "part2, part1 (exc: ", и",  )"
        if (mb_substr_count($text, ", ") == 1 && substr_count($text, ", и ") == 0 && substr_count($text, ", так ") == 0)
            return $this->mb_ucfirst(implode(", ", array_reverse(explode(", ", $this->mb_lcfirst($text)))));
        // Меняем "part1 - part2" на "part2, part1"
        if (mb_substr_count($text, " - ") == 1)
            return $this->mb_ucfirst(implode(", ", array_reverse(explode(" - ", $this->mb_lcfirst($text), 2))));
        // Меняем "part1 К part2" на "part2 к part1" (и подсчитывается кол-во слов после " к ")
        if (mb_substr_count($text, " к ") == 1 && mb_substr_count(mb_substr($text, mb_strpos($text, " к ")), " ") > 4)
            return $this->mb_ucfirst(implode(" к ", array_reverse(explode(" к ", $this->mb_lcfirst($text), 2)))); // не юзать, если к стоит почти в конце
        //---
        if (substr_count($text, " в ") > 0) {
            $words = explode(" ", $text);
            // Меняем "part1(1w) В part2..." на "В part2"
            if ($words[1] == "в") {
                $words[0] = "";
                $words[1] = "В";
                return implode(" ", $words);
            }
            // меняем "part1 (part2 В) part3(1,)" на "В part3 part1 part2"
            $words = explode(" в ", $text, 2);
            if (substr_count($words[0], " ") > 3 && substr_count($words[1], " ") > 2)
                return "В " . $words[1] . " " . $words[0];
        }
        //---
        return $this->mb_ucfirst($text);
    }

    //---
    private function MyRand($num)
    {
        //---
        $n = $num[0];
        $y = intval(date("Y"));
        if ($n < 1) return $n;
        if ($n <= $y && $n > ($y - 80)) return ($n + rand(-4, 1)); // если число в пределах наших лет, то с ним работает аккуратно
        if ($n >= 2 && $n <= 5) return ($n + rand(-1, 3));
        if ($n <= 23 && $n > 5) return ($n + rand(-4, 1)); // аккуратно работаем и с месяцем
        if ($n <= 30 && $n > 5) return ($n + rand(-4, 1)); // аккуратно работаем и с месяцем
        if ($n > 35 && $n < 100) return ($n + rand(-3, 0) * 10);
        if ($n >= 100) return ($n + rand(-1, 10) * 10);
        //---
        return $n;
    }


    //---
    private function mb_ucfirst($string)
    {
        return mb_strtoupper(mb_substr($string, 0, 1)) . mb_substr($string, 1);
    }

    //---
    private function mb_lcfirst($string)
    {
        return mb_strtolower(mb_substr($string, 0, 1)) . mb_substr($string, 1);
    }

    //---
    private function is_mb_ucfirst($string)
    {
        return mb_substr($string, 0, 1) == mb_strtoupper(mb_substr($string, 0, 1));
    }

    //---
    private function print_all_arr($TheArray)
    { // Note: the function is recursive
        echo "<table border=1>\n";
        $Keys = array_keys($TheArray);
        foreach ($Keys as $OneKey) {
            echo "<tr>\n";
            echo "<td bgcolor='#727450'>";
            echo "<B>" . $OneKey . "</B>";
            echo "</td>\n";
            echo "<td bgcolor='#C4C2A6'>";
            if (is_array($TheArray[$OneKey]))
                Rewriter::print_all_arr($TheArray[$OneKey]);
            else
                echo "<![CDATA[" . $TheArray[$OneKey] . "]]>"; // Используем<![CDATA[ ]]>, на тот случай, если в значении могут присутствовать теги.
            echo "</td>\n";
            echo "</tr>\n";
        }
        echo "</table>\n";
    }

    //--
    private function compare($a, $b)
    {
        return ($a["1"] > $b["1"]) ? +1 : -1;
    }

    //---
    private function rel($a, $b)
    {
        stristr($mainkey, $val);
    }

}

?>