<?php
//+------------------------------------------------------------------+
//|                  Copyright c 2012, RedButton Web Sites Generator |
//|                                      http://www.getredbutton.com |
//+------------------------------------------------------------------+
/**
 * КЛАСС ЗАКАЧКИ ФАЙЛОВ ПО FTP
 */
class CModel_ftp extends CModel_baseftp
  {
  /**
   * Установка соединения с фтп сервером
   *
   */
  private function connect()
    {
    $conn_id = null;
    CModel_helper::PrintInfo('FTP: соединение с сервером ' . $this->ftpServer . ':' . $this->port, true);
    if(!($conn_id = ftp_connect($this->ftpServer, $this->port)))
      {
      CLogger::write(CLoggerType::ERROR, "error ftp connect " . $this->ftpServer . ':' . $this->port . ' login: ' . $this->login . ', pass: ' . $this->password . ', path: ' . $this->descPath);
      //--- если установлена флаг, то пытаемся несколько раз
      $count_connect = 1;
      if($this->isAutoResume)
        {
        sleep($this->attemptsTimeout);
        while($count_connect < $this->attemptsToResume)
          {
          if(($conn_id = ftp_connect($this->ftpServer, $this->port)))
            {
            //--- наконец-то соединение успешно установлено
            break;
            }
          $count_connect++;
          CLogger::write(CLoggerType::ERROR, "error ftp connect " . $this->ftpServer . ":" . $this->port . ' login: ' . $this->login . ", try " . $count_connect);
          sleep($this->attemptsTimeout * $count_connect);
          }
        }
      }
    return $conn_id;
    }

  /**
   * Установка соединения с фтп сервером
   *
   */
  private function login($conn_id, $is_pass = false)
    {
    $login_result = null;
    CModel_helper::PrintInfo('FTP: вход на сервер под логином ' . $this->login, true);
    if(!($login_result = ftp_login($conn_id, $this->login, $this->password)))
      {
      CLogger::write(CLoggerType::ERROR, "error ftp connect " . $this->ftpServer . ":" . $this->port);
      //--- если установлена флаг, то пытаемся несколько раз
      $count_connect = 1;
      if($this->isAutoResume)
        {
        CLogger::write(CLoggerType::ERROR, "error ftp connect " . $this->ftpServer . ":" . $this->port);
        sleep($this->attemptsTimeout);
        while($count_connect < $this->attemptsToResume)
          {
          if(($login_result = ftp_login($conn_id, $this->login, $this->password)))
            {
            //--- наконец-то соединение успешно установлено
            break;
            }
          $count_connect++;
          CLogger::write(CLoggerType::ERROR, "error ftp connect " . $this->ftpServer . ":" . $this->port . ", try " . $count_connect);
          sleep($this->attemptsTimeout * $count_connect);
          }
        //--- последняя попытка в пассивном режиме
        if(!$login_result)
          {
          ftp_pasv($conn_id, true);
          CLogger::write(CLoggerType::DEBUG, "ftp set passive mode");
          if(($login_result = ftp_login($conn_id, $this->login, $this->password)))
            {
            CLogger::write(CLoggerType::ERROR, "error ftp connect " . $this->ftpServer . ":" . $this->port . ", in passive mode too");
            }
          }
        }
      }
    //---
    return $login_result;
    }

  /**
   *
   * Копирование файла на сервер
   * @param handel $conn_id соедениение к фтп
   * @param string $file_name имя закачеваемого файла
   */
  private function file_send(&$conn_id, $file_name, $dst_file_name)
    {
    //--- открываем файл для закачки
    $fp = fopen($file_name, 'r');
    if(!$fp)
      {
      CLogger::write(CLoggerType::ERROR, "cannot open file " . $file_name);
      return;
      }
    $count_connect = 0;
    //--- максимально количество попыток
    $max_count_connect = $this->isAutoResume ? $this->attemptsToResume : 1;
    while($count_connect < $max_count_connect)
      {
      if(ftp_fput($conn_id, $dst_file_name, $fp, FTP_BINARY))
        {
        CLogger::write(CLoggerType::DEBUG, 'file copy from ' . $file_name . ' to ftp: ' . $dst_file_name);
        //--- закрываем файл
        fclose($fp);
        //--- удаляем файл
        if(unlink($file_name))
          {
          CLogger::write(CLoggerType::DEBUG, 'file deleted ' . $file_name);
          }
        //--- все ок выходим
        return;
        }
      else
        {
        CLogger::write(CLoggerType::ERROR, "error file upload " . $file_name . " to " . $dst_file_name);
        }
      $count_connect++;
      //--- если следующий раз будет последним, проверим соединение и выставим пассивный режим
      if(($count_connect + 1) == $max_count_connect)
        {
        ftp_pasv($conn_id, true);
        CLogger::write(CLoggerType::DEBUG, "ftp set passive mode");
        }
      }
    fclose($fp);
    CLogger::write(CLoggerType::DEBUG, "file closed " . $file_name);
    //---
    }

  /**
   * Удаление списка файлов
   * @param array $list_files
   * @return void
   */
  public function DeleteListFile($list_files)
    {
    if(!($conn_id = $this->connect())) return;
    //---
    CLogger::write(CLoggerType::DEBUG, "ftp connect " . $this->ftpServer);
    //--- логинимся на фтп сервере
    if(!($login_result = $this->login($conn_id))) return;
    //---
    CLogger::write(CLoggerType::DEBUG, "ftp: login " . $this->ftpServer . ":" . $this->login);
    //--- если пассивный режим, устанавливаем
    if($this->isPassiveMode)
      {
      ftp_pasv($conn_id, true);
      CLogger::write(CLoggerType::DEBUG, "ftp: ftp set passive mode");
      }
    //--- переходим на текущую папку
    if(!$this->ChangeCreateDirectory($conn_id, ''))
      {
      CLogger::write(CLoggerType::ERROR, "ftp: error ftp change or create directory " . $this->descPath);
      return;
      }
    $old_dir_name = '.';
    //---
    foreach($list_files as $filename)
      {
      $finfo = pathinfo($filename);
      //--- проверка текущей папки
      if($finfo['dirname'] != $old_dir_name)
        {
        if(!$this->ChangeCreateDirectory($conn_id, $finfo['dirname']))
          {
          CLogger::write(CLoggerType::ERROR, "error ftp change or create directory " . $finfo['dirname']);
          return;
          }
        else
          {
          CLogger::write(CLoggerType::DEBUG, "ftp: change or create directory " . $finfo['dirname']);
          }
        $old_dir_name = $finfo['dirname'];
        }
      //---
      if(ftp_delete($conn_id, $filename))
        {
        CLogger::write(CLoggerType::DEBUG, "ftp: file deleted on server: " . $filename);
        }
      else
        {
        CLogger::write(CLoggerType::DEBUG, "ftp: error file delete on server: " . $filename);
        }
      }
    //--- закрытие фтп коннекта
    ftp_close($conn_id);
    //---
    CModel_helper::PrintInfo('FTP: чистка на ftp завершена', true);
    CLogger::write(CLoggerType::DEBUG, "ftp: connection closed");
    }

  /**
   * Отправка по фтп списка файлов
   * @param array $list_files
   */
  protected function SendList($list_files)
    {
    //--- установка соединения по фтп
    if(!($conn_id = $this->connect())) return;
    //---
    CLogger::write(CLoggerType::DEBUG, "ftp connect " . $this->ftpServer);
    //--- логинимся на фтп сервере
    if(!($login_result = $this->login($conn_id))) return;
    //---
    CLogger::write(CLoggerType::DEBUG, "ftp login " . $this->ftpServer . ":" . $this->login);
    //--- если пассивный режим, устанавливаем
    if($this->isPassiveMode)
      {
      ftp_pasv($conn_id, true);
      CLogger::write(CLoggerType::DEBUG, "ftp set passive mode");
      }
    //--- переходим на текущую папку
    if(!$this->ChangeCreateDirectory($conn_id, ''))
      {
      CLogger::write(CLoggerType::ERROR, "error ftp change or create directory " . $this->descPath);
      return;
      }
    //--- пытаем закачать файл
    $old_dir_name   = '.';
    $last_time_stop = time();
    //---
    CModel_helper::PrintInfo('FTP: загружаем файлы на сервер...', true);
    //---
    foreach($list_files as $file_info)
      {
      $finfo = pathinfo($file_info->Name);
      //--- проверка текущей папки
      if($finfo['dirname'] != $old_dir_name)
        {
        //$dst_dir = $this->descPath . $finfo['dirname'];
        if(!$this->ChangeCreateDirectory($conn_id, $finfo['dirname']))
          {
          CLogger::write(CLoggerType::ERROR, "error ftp change or create directory " . $finfo['dirname']);
          return;
          }
        else
          {
          CLogger::write(CLoggerType::DEBUG, "ftp: change or create directory " . $finfo['dirname']);
          }
        //---
        $old_dir_name = $finfo['dirname'];
        }
      //--- полное имя закачеваемого файла
      $file_name = $this->tempPath . $file_info->Name;
      //--- удаленное имя файла
      $dst_file_name = $this->descPath . $file_info->Name;
      //--- отправка файла на фтп сервере
      CLogger::write(CLoggerType::DEBUG, 'try file copy from ' . $file_name . ' to ' . $dst_file_name . ' on ftp (' . $file_info->Name . ')');
      //---
      $this->file_send($conn_id, $file_name, $file_info->Name);
      //--- нужно ли проверять наличие файла
      if((time() - $last_time_stop) > $this->checkStop)
        {
        if($this->IsStop())
          {
          CLogger::write(CLoggerType::DEBUG, "ftp connection closed, find stop file");
          //--- закрытие фтп коннекта
          ftp_close($conn_id);
          return;
          }
        $last_time_stop = time();
        }
      }
    //--- закрытие фтп коннекта
    ftp_close($conn_id);
    //---
    CModel_helper::PrintInfo('FTP: загрузка файлов завершена', true);
    CLogger::write(CLoggerType::DEBUG, "ftp connection closed");
    }

  /**
   * Изменение папки или создание новой
   *
   * @param handel $conn_id
   * @param string $dir
   * @return bool
   */
  private function ChangeCreateDirectory($conn_id, $dir)
    {
    //--- папку создаем
    $path  = '/' . trim($this->descPath, '/ ');
    $dir   = str_replace("\\", "/", $dir);
    $paths = explode("/", $dir);
    //---
    $ret = true;
    //---
    for($i = 0, $sz = sizeof($paths); $i < $sz; $i++)
      {
      $path .= "/" . $paths[$i];
      if(!@ftp_chdir($conn_id, $path))
        {
        ftp_chdir($conn_id, "/");
        if(!ftp_mkdir($conn_id, $path))
          {
          $ret = false;
          break;
          }
        CLogger::write(CLoggerType::DEBUG, "ftp create path " . $path);
        if(ftp_chmod($conn_id, 0777, $path))
          {
          CLogger::write(CLoggerType::DEBUG, "ftp chmode 0777 path " . $path);
          }
        else
          {
          CLogger::write(CLoggerType::ERROR, "ftp error chmode 0777 path " . $path);
          }
        }
      }
    //---
    ftp_chdir($conn_id, '/' . trim($this->descPath, '/ '));
    return $ret;
    }
  }

?>
