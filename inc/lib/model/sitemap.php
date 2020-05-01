<?php
/**
 *
 * класс для генерация файла sitemap.xml
 * @author User
 *
 */
class CModel_sitemap
  {
  //--- данные о sitemap
  private $m_sitemap_txt;
  private $m_localPath;
  private $m_params;
  /**
   *
   * @var CModel_keywords
   */
  private $m_model_keywords;
  /**
   *
   * @var CModel_macros
   */
  private $m_model_macros;
  /**
   * настройки
   * @var CModel_settings
   */
  private $m_model_settings;

  /**
   *
   * конструктор
   * @param string $localPath
   * @param $params
   * @param $model_keywords
   * @param $model_macros
   */
  public function __construct(&$localPath, &$params, &$model_keywords, &$model_macros, &$model_settings)
    {
    $this->m_localPath      = $localPath;
    $this->m_sitemap_txt    = '';
    $this->m_params         = $params;
    $this->m_model_keywords = $model_keywords;
    $this->m_model_macros   = $model_macros;
    $this->m_model_settings = $model_settings;
    }

  /**
   * Первоначальная запись в xml
   *
   */
  public function Init()
    {
    $this->m_sitemap_txt = '<?xml version="1.0" encoding="UTF-8"' . '?' . '>' . '
<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9" xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xsi:schemaLocation="http://www.sitemaps.org/schemas/sitemap/0.9 http://www.sitemaps.org/schemas/sitemap/0.9/sitemap.xsd">
   <url>
      <loc>' . htmlspecialchars($this->m_params['nextUrl']) . '</loc>
      <changefreq>daily</changefreq>
      <priority>0.9</priority>
   </url>
';
    }

  /**
   * Окончание сайтмапа
   *
   */
  public function Finals()
    {
    $this->m_sitemap_txt .= '</urlset>';
    }

  /**
   * Добавление
   *
   * @param string $url
   */
  public function AddUrl($url)
    {
    $this->m_sitemap_txt .= '   <url>
      <loc>' . htmlspecialchars($url) . '</loc>
      <changefreq>weekly</changefreq>
      <priority>0.5</priority>
   </url>
';
    }

  /**
   * Физическое создание файла sitemap.xml
   *
   */
  public function CreateFile()
    {

    if(isset($this->m_params['is_commpress']) && $this->m_params['is_commpress'] == 'on')
      {
      //--- если режим экономии места, то не sitemap.xml, а sitemap.zip
      //$zipfile = new CTools_zip();
      //$zipfile->create_file($this->m_sitemap_txt, 'sitemap.zip');
      //file_put_contents($this->m_localPath . '/sitemap.zip', $zipfile->zipped_file());
      file_put_contents($this->m_localPath . '/sitemap.xml', $this->m_sitemap_txt);
      return;
      }
    file_put_contents($this->m_localPath . '/sitemap.xml', $this->m_sitemap_txt);
    }

  /**
   * СОздание html версии ссылок на все внутреннии страницы
   * @param $page
   * @param $filename
   */
  public function CreateHtml($page, $filename)
    {
    //--- если найден [SITEMAP-CONTENT], то заменяем его иначе весь контент
    if(strpos($page, '[SITEMAP-CONTENT]') !== false)
      {
      $page = str_replace('[SITEMAP-CONTENT]', $this->GetSitemapHtml(), $page);
      $page = str_replace('[RB:CONTENT]', '', $page);
      }
    else
      {
      $page = str_replace('[RB:CONTENT]', $this->GetSitemapHtml(), $page);
      }
    //---
    CLogger::write(CLoggerType::DEBUG, 'replace many macros');
    $page = $this->m_model_macros->ReplaceManyMacros($page, 0);
    //---
    CLogger::write(CLoggerType::DEBUG, 'replace many macros end');
    $fname = $this->m_localPath . '/' . $filename;
    file_put_contents($fname, $page);
    CLogger::write(CLoggerType::DEBUG, 'sitemap: file create ' . $fname . ', length data: ' . strlen($page));
    //---
    chmod($fname, 0777);
    unset($page);
    }

  /**
   * СОздание html версии сайта
   * @return string
   */
  private function GetSitemapHtml()
    {
    $format = trim($this->m_model_settings->GetGlobal('sitemap', '<li><a href="{url}">{title}</a></li>'));
    //---
    $is_need_ul = stristr($format, '<li') !== false;
    if($is_need_ul) $text = '<ul class="sitemap">';
//---
    for($i = 0; $i < $this->m_model_keywords->GetCountKeywords(); $i++)
      {
      $key = $this->m_model_keywords->GetKeywordByNum($i);
      //$url = $this->m_model_keywords->GetPageNameKey($key, $i);
      $text .= str_replace(array('{url}',
                                 '{title}'), array($key->GetUrl(),
                                                   $key->GetKeywordIndex(0)), $format);
      }
    if($is_need_ul) $text .= "</ul>";
    return $text;
    }
  }

?>