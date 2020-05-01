<?php
/**
 *
 * Работа с файлами
 * @author User
 *
 */
class CTools_files
  {
  /**
   *
   * Рекурсивное копирование папок
   * @param string $source
   * @param string $dest
   */
  public static function CopyPath($source, $dest, $stop_files = array())
    {
    // Simple copy for a file
    if(is_file($source))
      {
      return copy($source, $dest);
      }
    // Make destination directory
    if(!is_dir($dest))
      {
      mkdir($dest, 0777, true);
      }
    // If the source is a symlink
    if(is_link($source))
      {
      $link_dest = readlink($source);
      return symlink($link_dest, $dest);
      }
    //--- Loop through the folder
    $dir = dir($source);
    //---
    if(!$dir) return false;
    while(false !== $entry = $dir->read())
      {
      // Skip pointers
      if($entry == '.' || $entry == '..' || $entry == '.svn' || in_array($entry, $stop_files))
        {
        continue;
        }
      // Deep copy directories
      $src_path = $source . '/' . $entry;
      if($dest !== $src_path)
        {
        self::CopyPath($src_path, $dest . '/' . $entry);
        }
      }
    // Clean up
    $dir->close();
    return true;
    }

  /**
   * Удаление пути
   * @param string $path
   */
  public static function DeleteAll($path, $is_delete_main = true, $not_files_delete = array())
    {
    // Simple delete for a file
    if(!is_dir($path))
      {
      if(!in_array($path, $not_files_delete)) unlink($path);
      return true;
      }
    //--- Loop through the folder
    $dir = dir($path);
    if(!$dir)
      {
      if(file_exists($path) && !in_array($path, $not_files_delete)) unlink($path);
      return true;
      }
    //---
    while(false !== $entry = $dir->read())
      {
      // Skip pointers
      if($entry == '.' || $entry == '..')
        {
        continue;
        }
      self::DeleteAll($path . '/' . $entry, true, $not_files_delete);
      }
    // Clean up
    $dir->close();
    //--- удаление основной папки
    if($is_delete_main) rmdir($path);
    //---
    return true;
    }

  public static function GetCountFiles($path, $exts = array())
    {
    $count = 0;
    if(!is_dir($path)) return null;
    $list_files = array();
    //--- Loop through the folder
    $dir = dir($path);
    //---
    while(false !== $entry = $dir->read())
      {
      // Skip pointers
      if($entry == '.' || $entry == '..')
        {
        continue;
        }
      //--- имя файла
      $fname = pathinfo($entry, PATHINFO_BASENAME);
      //--- если нужно найти только с определенным расширением
      if(empty($exts)) $count++;
      //--- проверим тот ли файл
      elseif(in_array(strtolower(pathinfo($fname, PATHINFO_EXTENSION)), $exts))
        {
        $count++;
        }
      }
    // Clean up
    $dir->close();
    //---
    return $count;
    }

  /**
   * Получение всех файлов
   * @param string $path
   */
  public static function GetAllFiles($path, &$list_files, $stop_files = array(), $empty_dir = false)
    {
    // Simple copy for a file
    if(!is_dir($path))
      {
      $list_files[] = $path;
      return true;
      }
    //--- Loop through the folder
    $dir = dir($path);
    //---
    $add = false;
    while(false !== $entry = $dir->read())
      {
      // Skip pointers
      if($entry == '.' || $entry == '..' || in_array($entry, $stop_files))
        {
        continue;
        }
      $add = true;
      self::GetAllFiles($path . '/' . $entry, $list_files, $stop_files, $empty_dir);
      }
//--- пустую папку добавим
    if(!$add && $empty_dir) $list_files[] = $path;
    // Clean up
    $dir->close();
    //---
    return true;
    }

  /**
   * Получение всех файлов без каталога и без рекурсии
   * @param string $path
   */
  public static function GetAllOnlyFiles($path, $exts = array())
    {
    if(!is_dir($path)) return null;
    $list_files = array();
    //--- Loop through the folder
    $dir = dir($path);
    //---
    while(false !== $entry = $dir->read())
      {
      // Skip pointers
      if($entry == '.' || $entry == '..')
        {
        continue;
        }
      //--- имя файла
      $fname = pathinfo($entry, PATHINFO_BASENAME);
      //--- если нужно найти только с определенным расширением
      if(empty($exts)) $list_files[] = $fname;
      //--- проверим тот ли файл
      elseif(in_array(strtolower(pathinfo($fname, PATHINFO_EXTENSION)), $exts))
        {
        $list_files[] = $fname;
        }
      }
    // Clean up
    $dir->close();
    //---
    return $list_files;
    }

  /**
   *
   * Копирование без замены с новым именем, в ответ получаем полное название файла
   * @param string $src_file полный путь откуда копировать файл
   * @param string $dst_path папка куда копировать
   * @param string $dst_filename имя файла без расширения, расширение берется из исходного файла
   */
  public static function CopyFileNotReplace($src_file, $dst_path, $dst_filename)
    {
    if(!file_exists($src_file))
      {
      CLogger::write(CLoggerType::ERROR, 'file not found ' . $src_file);
      return false;
      }
    //--- новое имя и полный путь
    $full_file = self::GetNewFilenameNotReplace($src_file, $dst_path, $dst_filename);
    //---
    if(copy($src_file, $full_file))
      {
      CLogger::write(CLoggerType::DEBUG, 'file copy ' . $src_file . ' to ' . $full_file);
      return $full_file;
      }
    CLogger::write(CLoggerType::ERROR, 'error copy file ' . $src_file . ' to ' . $full_file);
    return false;
    }

  /**
   *
   * Копирование c заменой если файл с таким именем существует, в ответ получаем полное название файла
   * @param string $src_file полный путь откуда копировать файл
   * @param string $dst_path папка куда копировать
   * @param string $dst_filename имя файла без расширения, расширение берется из исходного файла
   */
  public static function CopyFileReplace($src_file, $dst_path, $dst_filename)
    {
    if(!file_exists($src_file))
      {
      CLogger::write(CLoggerType::ERROR, 'file not found ' . $src_file);
      return false;
      }
    //--- может папку нужно создать
    if(!file_exists($dst_path))
      {
      //--- создаем рекурсивно
      if(!mkdir($dst_path, 0777, true))
        {
        CLogger::write(CLoggerType::ERROR, 'can not create path ' . $dst_path);
        return false;
        }
      }
    $last_s = substr($dst_path, -1, 1);
    if($last_s != '/' || $last_s != '\\') $dst_path .= '/';
    //--- расширение файла
    $ext = strtolower(pathinfo($src_file, PATHINFO_EXTENSION));
    //--- полное название нового файла
    $full_file = $dst_path . $dst_filename . '.' . $ext;
    //---
    if(copy($src_file, $full_file))
      {
      CLogger::write(CLoggerType::DEBUG, 'file copy ' . $src_file . ' to ' . $full_file);
      return $full_file;
      }
    CLogger::write(CLoggerType::ERROR, 'error copy file ' . $src_file . ' to ' . $full_file);
    return false;
    }

  /**
   *
   * Получение полного имени файла, которое будет
   * @param string $src_file исходный файл
   * @param string $dst_path будущая папка
   * @param string $dst_filename базовое имя файла, без расширения
   */
  public static function GetNewFilenameNotReplace($src_file, $dst_path, $dst_filename)
    {
    //--- может папку нужно создать
    if(!file_exists($dst_path))
      {
      //--- создаем рекурсивно
      if(!mkdir($dst_path, 0777, true))
        {
        CLogger::write(CLoggerType::ERROR, 'can not create path ' . $dst_path);
        return false;
        }
      }
    $dst_filename = strtolower($dst_filename);
    //---
    $last_s = substr($dst_path, -1, 1);
    if($last_s != '/' || $last_s != '\\') $dst_path .= '/';
    //--- расширение файла
    $ext = strtolower(pathinfo($src_file, PATHINFO_EXTENSION));
    //--- полное название нового файла
    $full_file = $dst_path . $dst_filename . '.' . $ext;
    $i         = 0;
    while(file_exists($full_file))
      {
      $full_file = $dst_path . $dst_filename . '_' . (++$i) . '.' . $ext;
      }
    //--- полное имя файла, с путем
    return $full_file;
    }

  /**
   * количество подпапок
   * @param $path
   * @return int
   */
  public static function GetCountSubDirs($path)
    {
    $path = trim($path,'/').'/';
    if(!file_exists($path)) return 0;
    $dir = dir($path);
    //---
    $count = 0;
    while(false !== $entry = $dir->read())
      {
      // Skip pointers
      if($entry == '.' || $entry == '..')  continue;
      if(is_dir($path.$entry)) $count++;
      }
    // Clean up
    $dir->close();
    return $count;
    }
  }