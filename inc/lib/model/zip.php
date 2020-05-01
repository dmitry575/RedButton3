<?php
class CModel_zip
  {
  /**
   *
   * СОздаем zip архив
   * @param string $dir папка для упаковки
   * @param string $zipname имя zip архива
   */
  public function CreateZipFile($dir, $zipname)
    {
    $zipfile = new CTools_zip();
    //---
    $list_files = array();
    CTools_files::GetAllFiles($dir, $list_files, array(), true);
    //---
    foreach($list_files as $filname)
      {
//--- может быть файла, а может быть пустая папка
      if(is_file($filname)) $zipfile->create_file(file_get_contents($filname), str_replace($dir . '/', '', $filname));
      else
      $zipfile->create_dir(str_replace($dir . '/', '', $filname));
      }
    //---
    file_put_contents($zipname, $zipfile->zipped_file());
    return true;
    }

  /**
   *
   * СОздаем zip архив
   * @param string $liast_file список файлов для упаковки
   * @param string $zipname имя zip архива
   * @param bool $only_name в архив только имена файлов
   */
  public function CreateZipOnlyFiles($list_files, $zipname, $only_name = true)
    {
    $zipfile = new CTools_zip();
    //---
    foreach($list_files as $filname)
      {
      $zipfile->create_file(file_get_contents($filname), $only_name ? pathinfo($filname, PATHINFO_BASENAME) : $filname);
      }
    //---
    file_put_contents($zipname, $zipfile->zipped_file());
    return true;
    }
  }

?>