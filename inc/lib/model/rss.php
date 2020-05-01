<?php
class CModel_rss
  {
  /**
   * путь к папке
   * @var string
   */
  private $m_local_path;
  /**
   * Управление ключевиками
   * @var CModel_keywords
   */
  private $m_model_keywords;
  /**
   * Параметры
   * @var array
   */
  private $m_params;
  /**
   * Максимальное количество строк
   */
  const MAX_RSS_ROWS = 250;
  //---
  const MAX_REPEAT_GET_KEY = 50;

  /**
   *
   * Конструктор
   * @param string $localPath
   */
  public function __construct(&$localPath, &$params, &$model_keywords)
    {
    $this->m_local_path     = $localPath;
    $this->m_params         = $params;
    $this->m_model_keywords = $model_keywords;
    }

  /**
   *
   * Генерация файла rss
   */
  public function RSSGenerateFile()
    {
    $i          = 0;
    $pub_date   = time();
    $rss_result = '<?xml version="1.0" encoding="UTF-8"?>
<rss version="2.0">
<channel>
<title></title>
   <link>' . $this->m_params['nextUrl'] . '</link>
   <pubDate>' . gmdate("D, d M Y H:i:s") . ' GMT</pubDate>
   <ttl>3600</ttl>
   <copyright>Copyright (c) ' . rand(1999, date('Y')) . '-' . date('Y') . '</copyright>
';
    $used_keys  = array();
    $num        = 0;
    //---
    while($i < $this->m_model_keywords->GetCountKeywords() && $i < self::MAX_RSS_ROWS)
      {
      $key_info = $this->m_model_keywords->GetRandKeyword($num);
      $k        = $key_info->getKeywordIndex(0);
      //---
      if(isset($used_keys[$k]))
        {
        $find = false;
        for($j = 0; $j < self::MAX_REPEAT_GET_KEY; $j++)
          {
          $key_info = $this->m_model_keywords->GetRandKeyword($num);
          $k        = $key_info->getKeywordIndex(0);
          if(!isset($used_keys[$k]))
            {
            $find = true;
            break;
            }
          }
        //--- ничего не смогли найти выходим
        if(!$find) break;
        }
      //---
      $used_keys[$k] = 1;
      $pub_date -= rand(4 * 3600, 24 * 3600);
      $url        = $key_info->getUrl();
      $encodedKey = htmlspecialchars($k);
      $rss_result .= '<item>
      <title>' . $encodedKey . '</title>
      <link>' . $url . '</link>
      <guid>' . $url . '</guid>
      <description><![CDATA[' . $encodedKey . ']]></description>
      <pubDate>' . gmdate("D, d M Y H:i:s", $pub_date) . ' GMT</pubDate>
   </item>
';
      $i++;
      }
    $rss_result .= '</channel></rss>';
    //--- можеть быть нужно ужать?
    if(isset($this->m_params['is_commpress']) && $this->m_params['is_commpress'] == 'on')
      $rss_result = CModel_tools::HtmlCompress($rss_result);
    file_put_contents($this->m_local_path . '/rss.xml', $rss_result);
    CLogger::write(CLoggerType::DEBUG, 'generated ' . $this->m_local_path . '/rss.xml');
    //---
    }
  }