<?php
class CModel_robots
  {
  /**
   *
   * СОздание файла robots.txt
   */
  public function CreateRobotsTxt($url, $localPath, $pathTemplate, $isCreateSiteMap)
    {
    $pathTemplate = rtrim($pathTemplate, '\\/');
    $file_robots  = $pathTemplate . '/robots.txt';
    $text_robots  = '';
    if(file_exists($file_robots))
      {
      $text_robots = file_get_contents($file_robots);
      }
    $needSlesh = empty($url) || $url[strlen($url) - 1] != '/';
    $txt       = 'User-agent: *
Allow: /
Host: ' . $url;
    if($isCreateSiteMap)
      {
      $txt .= "\r\n" . 'Sitemap: ' . $url . ($needSlesh ? '/' : '') . 'sitemap.xml';
      }
    $txt .= "\r\n";
    $txt .= $text_robots;
    //---
    file_put_contents($localPath . '/robots.txt', $txt);
    }
  }

?>