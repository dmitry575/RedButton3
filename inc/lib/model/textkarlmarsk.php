<?php
/*
 * Класс генерации текста
 * Пример запуска: $CText=new CTextGenerator('text.txt');
 */
class CModel_TextKarlMarsk
{
    /**
     * Общая суть алгоритма "КарлаМаркса" из РедБаттона:
     * 1. Выбираем случайное предложение
     * 2. Ищем предложения, похожие на выбранное по кол-ву слов (по-сути бред, но эффективно)
     * 3. Перемешиваем выбранное предложение с одним из найденных
     * 4. Повторяем процедуру нужное кол-вол раз
     *    ...
     * 5. Добавляем кейворды
     * 6. Разбиваем на параграфы
     */
    public function GetText($textArray = '', $keyword = '', $settings)
    {
        //--- проверка данных
        if ( /*empty($text)*/
            empty($textArray) || !is_array($settings)
        )
            return '';
        //--- инициализация
        //$textArray=array();
        $indexArray = array();
        $sentencesArray = array();
        $numSentencesArray = array();
        $wordsArray = array();
        $sentencesList = array();
        $totalWordsCount = 0;
        $numSentences = 0;
        //--- уберем лишние пробелы
        /*$text=str_replace('  ', ' ',
         //--- заменим переводы строк на пробелы
         str_replace("\n", ' ',
         str_replace("\r\n", ' ',
         //--- заменим точки в сокращенных словах - на макрос {dot}
         preg_replace('/( [\w]{1,3})\. /', "$1{dot}",
         //--- уберем слова, которые в скобках
         preg_replace('/\([^\(\)]*\)|\"/', '', $text)))));
         //--- разобьем текст на предложения
         $textArray=explode('. ', $text);
         */
        //--- составляем индекс предложений по кол-ву слов
        for ($i = 0, $sz = count($textArray); $i < $sz; $i++) {
            if (empty($textArray[$i]) || strlen($textArray[$i]) < 10)
                continue;
            $textArray[$i] = str_replace("\n", ' ',
                str_replace("\r\n", ' ',
                    //--- заменим точки в сокращенных словах - на макрос {dot}
                    preg_replace('/( [\w]{1,3})\. /', "$1{dot}",
                        //--- уберем слова, которые в скобках
                        preg_replace('/\([^\(\)]*\)|\"/', '', $textArray[$i]))));
            //---
            $numWords = substr_count($textArray[$i], ' ') + 1;
            if (isset($indexArray[$numWords]))
                $indexArray[$numWords][] = $i;
            else
                $indexArray[$numWords] = array($i);
        }
        //--- решаем, сколько нам взять предложений для каждого параграфа
        for ($i = 0; $i < $settings['numpar']; $i++) {
            //--- рандомное число предложений
            $randNum = rand(2, 4);
            //--- будем знать число предложений для каждого параграфа
            $numSentencesArray[] = $randNum;
            //--- суммируем общее кол-во предложений
            $numSentences += $randNum;
        }
        //--- начинаем выбирать предложения
        for ($i = 0; $i < $numSentences; $i++) {
            if (sizeof($indexArray) < 1) break;
            //--- выберем случайный массив с предложениями одной длины
            $randIndexPos = array_rand($indexArray);
            //--- выберем случайное предложение из выбранного массива
            $firstSentencePos = array_rand($indexArray[$randIndexPos]);
            $firstSentence = $textArray[$indexArray[$randIndexPos][$firstSentencePos]];
            //--- удалим из индекса номер выбранного предложения
            unset($indexArray[$randIndexPos][$firstSentencePos]);
            //--- если в массиве осталось хотя бы одно предложение
            if (sizeof($indexArray[$randIndexPos]) > 0) {
                //--- возьмем второе случайное предложение с таким-же кол-вом слов
                $secondSentencePos = array_rand($indexArray[$randIndexPos]);
                $secondSentence = $textArray[$indexArray[$randIndexPos][$secondSentencePos]];
                //--- удалим из индекса номер выбранного предложения
                unset($indexArray[$randIndexPos][$secondSentencePos]);
                //--- если массив стал пустым - удалим и сам массив
                if (sizeof($indexArray[$randIndexPos]) == 0)
                    unset($indexArray[$randIndexPos]);
            } //--- если в массиве не осталось предложений - возьмем из другого массива
            else {
                //--- удалим этот массив
                unset($indexArray[$randIndexPos]);
                //--- возьмем случайным массив с предложениями
                $randOtherIndexPos = array_rand($indexArray);
                //--- если предложений нет - пропускаем
                if ($randOtherIndexPos == null)
                    continue;
                //--- возьмем второе случайное предложение с таким-же кол-вом слов
                $secondSentencePos = array_rand($indexArray[$randOtherIndexPos]);
                $secondSentence = $textArray[$indexArray[$randOtherIndexPos][$secondSentencePos]];
                //--- удалим из индекса номер выбранного предложения
                unset($indexArray[$randOtherIndexPos][$secondSentencePos]);
                //--- если массив стал пустым - удалим и сам массив
                if (sizeof($indexArray[$randOtherIndexPos]) == 0)
                    unset($indexArray[$randOtherIndexPos]);
            }
            //--- обработка предложений
            $firstSentence = $this->mb_lcfirst(trim($firstSentence, ' .,!?:;-/,'));
            $secondSentence = $this->mb_lcfirst(trim($secondSentence, ' .,!?:;-/,'));
            //--- получим массив слов из обоих предложений
            $wordsArray = explode(' ', $firstSentence . ' ' . $secondSentence);
            //--- перемешаем массив слов
            shuffle($wordsArray);
            //--- получим кол-во слов для нового предложения
            $wordsCount = substr_count($firstSentence, ' ') + 1;
            //--- проверим, чтобы кол-во слов не превышело допустимое значение, указанное в настройках
            $maxRandWordsCount = rand($settings['numwords'][0], $settings['numwords'][1]);
            if ($wordsCount > $maxRandWordsCount)
                $wordsCount = $maxRandWordsCount;
            //--- составим новое предложение
            $newSentence = array();
            for ($s = 0; $s < $wordsCount; $s++) {
                if (empty($wordsArray[$s]))
                    continue;
                //--- не берем последнее слово в предложении, если оно меньше 4-х символов
                if (($s + 2 == $wordsCount) && (strlen($wordsArray[$s]) < 4 || (strlen($wordsArray[$s]) < 4 && strlen($wordsArray[$s + 1]) < 4)))
                    break;
                //---
                $newSentence[] = trim($wordsArray[$s]);
                ++$totalWordsCount;
            }
            //--- добавим новое предложение в массив всех предложений
            //$sentencesArray[] =  ucfirst(trim(implode(' ', $newSentence), ' .,!?:;-/')).'. '; //trim($newSentence);
            $sentencesArray[]=$newSentence;
            //--- очистим массив
            unset($newSentence);
        }
        //--- узнаем, сколько надо добавить ключевых слов в текст
        //--- (убрал, т.к. требуется просто четко указать кол-во кеев, без процентного соотношения)
        //$maxKeysCount = ($totalWordsCount>0 && $settings['numkeys']>0) ? round(100*$settings['numkeys']/$totalWordsCount) : 0;

        //--- возьмем из настроек данные о том, сколько ключевых слов надо добавить в текст
        $maxKeysCount = $settings['numkeys'];
        //--- узнаем кол-во предложений
        $sentencesMaxKey = sizeof($sentencesArray) - 1;
        if (!empty($keyword)) {
            //--- будем рандомно выбирать слова и менять их на кейворды
            for ($k = 0; $k < $maxKeysCount; $k++) {
                $randPar = rand(0, $sentencesMaxKey);
                $sentencesArray[$randPar][array_rand($sentencesArray[$randPar])] = $keyword;
            }
        }
        $this->recordCnt = array();
        //--- склеим каждое предложение и постим его в отдельный массив
        for ($s = 0, $ssz = count($sentencesArray); $s < $ssz; $s++)
            $sentencesList[] = $this->mb_ucfirst(trim(implode(' ', $sentencesArray[$s]), ' .,!?:;-/')) . '. ';
        //---
        unset($textArray, $indexArray, $sentencesArray, $numSentencesArray, $wordsArray, $newSentence, $firstSentence, $secondSentence, $text);

        return $sentencesList;
        /*
         //--- разложим все предложения по параграфам
         $content='';
         $shift=0;
         for($p=0, $psz=$settings['numpar']; $p<$psz; $p++)
         {
         $content.='<p>'.implode('',
         array_merge(array_slice($sentencesList, $shift, $numSentencesArray[$p]))
         )."</p>\r\n";
         //--- подсчитаем, с какой позиции брать предложения для след. параграфа
         $shift+=$numSentencesArray[$p];
         }
         //--- принудительно очистим память
         unset($textArray, $indexArray, $sentencesArray, $sentencesList, $numSentencesArray, $wordsArray, $newSentence, $firstSentence, $secondSentence, $text);
         //--- возвращаем результат
         return str_replace('{dot}', '. ', $content);
         */
    }

    /**
     *
     * Первую букву делаем заглавной
     * @param string $string
     */
    private function mb_ucfirst($string)
    {
        return mb_strtoupper(mb_substr($string, 0, 1)) . mb_substr($string, 1);
    }

    /**
     *
     * Первую букву делаем маленькой
     * @param string $string
     */
    private function mb_lcfirst($string)
    {
        return mb_strtolower(mb_substr($string, 0, 1)) . mb_substr($string, 1);
    }
}