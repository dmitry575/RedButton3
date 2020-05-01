<?php
//+------------------------------------------------------------------+
//|                  Copyright c 2012, RedButton Web Sites Generator |
//|                                      http://www.getredbutton.com |
//+------------------------------------------------------------------+
/**
 * Класс для работы с HTTP протоколом
 */
class CModel_http
  {
  const SERVER = 'http://support.getredbutton.com';

  //const SERVER = 'http://support.lezzvie.dev';
  /**
   * HTTP запрос к серверу
   * @param $url
   * @param $method
   * @param null $cookies
   * @param null $referer
   * @param null $user_agent
   * @return array|bool|string
   */
  public static function openHttp($url, $method, $cookies = null, $referer = null, $user_agent = null)
    {
    $addpath = '';
    //--- Бывает, что в запросе (например на market.yandex.ru) вставляют всякую хрень, и плюсы меняют на %2B, но parse_url меняет их опять на плюсы.
    //--- Так что пока используем вот такое кривое решение.
    if(strpos($url, '=') !== FALSE) $url = preg_replace_callback("/([\?|&][^&]{1,}=[^&]{1,}=[^&]{1,})/", create_function('$matches', 'return str_replace("+","%2B",$matches[0]);'), $url);
    //--- парсим урл
    $parse_url = parse_url($url);
    if(empty($parse_url['host'])) return '';
    //---
    if($parse_url['path'] == '') $parse_url['path'] = '/';
    //---
    $query = '';
    if(isset($parse_url['query']))
      {
      (strpos($parse_url['query'], "?") !== FALSE) ? list($addpath, $query) = explode("?", $parse_url['query']) : $query = $parse_url['query'];
      }
    $host = $parse_url['host'];
    $addpath == "" ? $path = $parse_url['path'] : $path = $parse_url['path'] . "?" . $addpath;
    //---
    $method = strtoupper($method);
    if($method == 'GET') $path .= '?' . $query;
    //---
    $filePointer = @fsockopen($host, substr($url, 0, 6) == 'https:' ? 443 : 80, $errorNumber, $errorString, 5);
    if(!$filePointer)
      {
      return false;
      }
    //---
    $requestHeader = $method . ' ' . $path . " HTTP/1.1\r\n";
    $requestHeader .= "Host: " . $host . "\r\n";
    //---
    if(!empty($user_agent)) $requestHeader .= "User-Agent: " . $user_agent . "\r\n";
    else
    $requestHeader .= "User-Agent: Mozilla/5.0 (Windows NT 5.1; rv:12.0) Gecko/20100101 Firefox/12.0\r\n";
    //---
    $requestHeader .= "Accept: text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8\r\n";
    $requestHeader .= "Accept-Language: ru-ru,ru;q=0.8,en-us;q=0.5,en;q=0.3\r\n";
    //$requestHeader.= "Accept-Encoding: gzip,deflate\r\n";
    $requestHeader .= "Accept-Charset: utf-8,windows-1251;q=0.7,*;q=0.7\r\n";
    if(!empty($cookies)) $requestHeader .= "Cookie: " . $cookies . "\r\n";
    if(!empty($referer)) $requestHeader .= "Referer: " . $referer . "\r\n";
    //
    //---
    if($method == "POST")
      {
      $requestHeader .= "Content-Length: " . strlen($query) . "\r\n";
      $requestHeader .= "Content-Type: application/x-www-form-urlencoded\r\n";
      }
    //---
    //requestHeader.= "Cache-Control: max-age=0\r\n";
    $requestHeader .= "Connection: close\r\n\r\n";
    if($method == "POST") $requestHeader .= $query;
    //---
    fwrite($filePointer, $requestHeader);
    //---
    $responseHeader = '';
    do
      {
      $responseHeader .= fread($filePointer, 1);
      } while(!preg_match('/\\r\\n\\r\\n$/', $responseHeader));
    //---
    $headers = self::http_parse_headers2($responseHeader);
    //$headers = self::http_parse_message($responseHeader);
    // кукисы
    $arr_cookies = array();
    $cookies     = "";
    preg_match_all("/Set-Cookie: (.*)/", $responseHeader, $mas);
    for($m = 0, $sz = sizeof($mas[1]); $m < $sz; $m++) $arr_cookies = array_merge($arr_cookies, explode(";", trim($mas[1][$m])));
    //---
    $cookies = trim(implode("; ", array_unique($arr_cookies)), "; ");
    $cookies = str_replace(";  ", "; ", $cookies);
    //---
    $responseContent = '';
    if(!strstr($responseHeader, "Transfer-Encoding: chunked"))
      {
      while(!feof($filePointer))
        {
        $ggg = fgets($filePointer, 128);
        $responseContent .= $ggg;
        }
      }
    else
      {
      while($chunk_length = hexdec(fgets($filePointer)))
        {
        $responseContentChunk = '';
        $read_length          = 0;
        //---
        while($read_length < $chunk_length)
          {
          $ggg = fread($filePointer, $chunk_length - $read_length);
          $responseContentChunk .= $ggg;
          $read_length = strlen($responseContentChunk);
          }
        //---
        $responseContent .= $responseContentChunk;
        fgets($filePointer);
        }
      }
    fclose($filePointer);
    //---
    return array(trim($responseContent),
                 $cookies,
                 $headers);
    }

  /**
   *
   * парсинг headers
   * @param string $header
   * @return array
   */
  static public function http_parse_headers2($header)
    {
    $retVal = array();
    $fields = explode("\r\n", preg_replace('/\x0D\x0A[\x09\x20]+/', ' ', $header));
    foreach($fields as $field)
      {
      if(preg_match('/([^:]+):(.+)/m', $field, $match))
        {
        $match[1] = @preg_replace('/(?<=^|[\x09\x20\x2D])./', 'strtoupper("\0")', strtolower(trim($match[1])));
        $match[2] = trim($match[2]);
        //---
        if(isset($retVal[$match[1]]))
          {
          if(is_array($retVal[$match[1]]))
            {
            $retVal[$match[1]][] = $match[2];
            }
          else
            {
            $retVal[$match[1]] = array($retVal[$match[1]],
                                       $match[2]);
            }
          }
        else
          {
          $retVal[$match[1]] = $match[2];
          }
        }
      else if(preg_match('/([A-Za-z]+) (.*) HTTP\/([\d.]+)/', $field, $match))
        {
        $retVal["Request-Line"] = array("Method"       => $match[1],
                                        "Request-URI"  => $match[2],
                                        "HTTP-Version" => $match[3]);
        }
      else if(preg_match('/HTTP\/([\d.]+) (\d+) (.*)/', $field, $match))
        {
        $retVal["Status-Line"] = array("HTTP-Version"  => $match[1],
                                       "Status-Code"   => $match[2],
                                       "Reason-Phrase" => $match[3]);
        }
      }
    return $retVal;
    }

  /**
   * Отправка данных на сервер
   *
   */
  public static function SentToServerData($l)
    {
    $result = self::openHttp(self::SERVER . '/update.php?l=' . urlencode($l) . "&" . rand(0, 100000000), "post", "", "");
    if($result[0] == "OK") return true;
    //---
    return false;
    }

  /**
   * Отправка данных на сервер
   *
   */
  public static function SentToActivate($lic, $data, &$headers)
    {
    //echo self::SERVER.'/public/activate?lic='.urlencode($lic)."&data=".$data.'&'.rand(0, 100000000);
    $result  = self::openHttp(self::SERVER . '/public/activate?v=2&lic=' . urlencode(base64_encode($lic)) . "&data=" . urlencode(base64_encode($data)) . '&t=' . rand(0, 100000000), "post", "", "");
    $headers = $result[2];
    //---
    return $result[0];
    }

  /**
   *
   * Отправка данных на сервер и получение новостей
   * @param string $lic
   * @param string $lng
   * @param string $data
   * @param string $headers
   */
  public static function SentToCheckNews($lic, $data, $lng, &$headers)
    {
    $result  = self::openHttp(self::SERVER . '/public/getlastnews?lng=' . urlencode(base64_encode($lng)) . '&lic=' . urlencode(base64_encode($lic)) . "&data=" . urlencode(base64_encode($data)) . '&t=' . rand(0, 100000000), "post", "", "");
    $headers = $result[2];
    return $result[0];
    }
  }

?>