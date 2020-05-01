<?php
/**
 * Зачистка фтп и тмп
 */
class CModel_cleaner
  {
  /**
   * Максимальное время жизни темповых файлов
   */
  const DAYS = 7;
  /**
   * Тепмовая папка
   */
  const TEMP_PATH = './data/tmp';
  /**
   * Префик для логов
   */
  const PREFIX_LOG = 'cleaner: ';
  /**
   * название стоп папок
   * @var array
   */
  private $m_stop_path = array('logs');

  /**
   * Зачистка  файлов
   */
  public function Clean()
    {
    CLogger::write(CLoggerType::DEBUG, self::PREFIX_LOG . "cleaner directories starting...");
    //--- путь фтп для зачистки
    $this->CleanPathFtp(CModel_UploadTask::PATH);
    //--- путь тмп папки
    $this->CleanPathTemp(self::TEMP_PATH);
    //---
    CLogger::write(CLoggerType::DEBUG, self::PREFIX_LOG . "cleaner directories stopped");
    }

  /**
   * Зачистка фтп папок
   * @param $path
   */
  private function CleanPathFtp($path)
    {
    if(!file_exists($path))
      {
      return;
      }
//---
    $dir       = dir($path);
    $now       = time();
    $last_time = self::DAYS * 24 * 3600;
    //--- зачистим папки
    while(false !== ($entry = $dir->read()))
      {
      if($entry == '.' || $entry == '..') continue;
      $p = $path . '/' . $entry;
      if(!is_dir($p)) continue;
      //---
      $sub_d = dir($p);
      while(false !== ($sub_name = $dir->read()))
        {
        if($sub_name == '.' || $sub_name == '..') continue;
        $full_sub = $p . '/' . $sub_name;
        if(!is_dir($full_sub)) continue;
        //---
        $time = filemtime($full_sub);
        if(($now - $time) > $last_time)
          {
          CTools_files::DeleteAll($full_sub);
          //---
          CLogger::write(CLoggerType::DEBUG, self::PREFIX_LOG . "directory " . $full_sub . ' deleted, created: ' . date("Y.m.d H:i"));
          }
        }
      $sub_d->close();
      }
    $dir->close();
    }

  /**
   * Зачистка темповой папки
   * @param $path
   */
  private function CleanPathTemp($path)
    {
    if(!file_exists($path))
      {
      return;
      }
//---
    $dir       = dir($path);
    $now       = time();
    $last_time = self::DAYS * 24 * 3600;
    //--- зачистим папки
    while(false !== ($entry = $dir->read()))
      {
      if($entry == '.' || $entry == '..') continue;
      $p = $path . '/' . $entry;
      //---
      if(!is_dir($p) || in_array($entry, $this->m_stop_path)) continue;
      //---
      $time = filemtime($p);
      if(($now - $time) > $last_time)
        {
        CTools_files::DeleteAll($p);
        //---
        CLogger::write(CLoggerType::DEBUG, self::PREFIX_LOG . "directory " . $p . ' deleted, created: ' . date("Y.m.d H:i"));
        }
      }
    $dir->close();
    }
  }