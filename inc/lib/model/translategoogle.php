<?php
/*
 * Класс для использования API переводчика от Googles
 * Идеален для славянских языков, в частности русский <-> украинский
 */
class Cmodel_TranslateGoogle
  {
  protected $rootURL = 'http://translate.google.com/translate_a/t?client=x&text={TEXT}&sl={LANG_FROM}&tl={LANG_TO}&ie=UTF-8&otf=1&pc=0';
  /**
   * @var string - символ или тег конца абзаца
   * Варианты: вывод в браузер - <br />, в файл - \n, может зависеть от ОС
   */
  public $eolSymbol = '<br />';
  /**
   * @var string - разделитель языков в запросе. Пока однозначно так определено Яндексом
   */
  public $langDelimiter = '-';
  protected $cURLHeaders = array('User-Agent'      => "Mozilla/5.0 (Windows NT 10.0; WOW64; rv:49.0) Gecko/20100101 Firefox/49.0",
                                 'Accept'          => "text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8",
                                 'Accept-Language' => "ru,en-us;q=0.7,en;q=0.3",
                                 'Accept-Encoding' => "gzip,deflate",
                                 'Accept-Charset'  => "utf-8;q=0.7,*;q=0.7",
                                 'Keep-Alive'      => '300',
                                 'Connection'      => 'keep-alive',);
  protected function Connect($url)
    {
    $ch      = curl_init();
    $Headers = array("Accept: text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8",
                     "Mozilla/5.0 (Windows NT 10.0; WOW64; rv:49.0) Gecko/20100101 Firefox/49.0",
                     "Accept-Language: en-us;q=0.7,en;q=0.3",
                     "Accept-Charset: utf-8;q=0.7,*;q=0.7");
    curl_setopt($ch, CURLOPT_HTTPHEADER, $Headers);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
    curl_setopt($ch, CURLOPT_MAXREDIRS, 3);
    curl_setopt($ch, CURLOPT_REFERER, "http://translate.google.com");


    curl_setopt($ch, CURLOPT_URL, $url);
    $response = curl_exec($ch);
    curl_close($ch);
    return $response;
    }

  /**
   * Собственно перевод
   * @param  $fromLang - с какого, код языка, 'ru' напр.
   * @param  $toLang - на какой, код языка. Следите: не все языки FROM доступны в TO
   * @param  $text - переводимый текст
   * @return mixed - перевод. Следите за разделителями eolSymbol
   */
  public function Translate($lang_from, $lang_to, $text)
    {
    //--- отправляем запрос к гуглу
    $url = str_replace(array('{TEXT}',
                             '{LANG_FROM}',
                             '{LANG_TO}'), array(urlencode($text),
                                                 $lang_from,
                                                 $lang_to), $this->rootURL);
    //--- получаем текст
    $rawTranslate = $this->Connect($url);
    /*if($lang_to == 'ru') $rawTranslate = mb_convert_encoding($rawTranslate, 'UTF-8', "KOI8-R");
    else
    */
    $rawTranslate = mb_convert_encoding($rawTranslate, 'UTF-8');
    //---

    $translate_json = json_decode($rawTranslate, true);
    if(empty($translate_json) || !is_array($translate_json)) return '';
    //---
    $text = '';
    foreach($translate_json['sentences'] as $arr)
      {
      $text .= $arr['trans'] . ' ';
      }
    //---
    return $text;
    }
  }
