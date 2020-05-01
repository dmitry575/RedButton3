<?php
class CTools_dir
  {
  /**
   *
   * Получение папок в указанной папке
   * @param string $dir
   * @return array|null
   */
  public static function GetDirs($dir)
    {
    if(empty($dir)) return null;
    if(!file_exists($dir)) return null;
    if(!is_dir($dir)) return null;
    //---
    if($dir[strlen($dir) - 1] != '/') $dir .= '/';
    $ret = array();
    //--- Loop through the folder
    $cdir = dir($dir);
    //---
    while(false !== $entry = $cdir->read())
      {
      // Skip pointers
      if($entry == '.' || $entry == '..') continue;
      //--- не папка не нужна
      if(!is_dir($dir . $entry)) continue;
      //---
      $ret[] = $entry;
      }
    // Clean up
    $cdir->close();
    return $ret;
    }
  }

?>