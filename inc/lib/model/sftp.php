<?php
include_once 'inc/lib/osrc/Net/SFTP.php';
include_once 'inc/lib/osrc/Net/SSH2.php';
/**
 *
 * Класс для работы по sftp
 * @author User
 *
 */
class CModel_sftp extends CModel_baseftp
{
    protected $port = 22;
    /**
     * @var Net_SFTP
     */
    private $m_sftp;

    /**
     * Установка соединения с фтп сервером
     *
     */
    protected function connect()
    {
        /*      $conn_id = null;
              $methods = array(
                   'kex' => 'diffie-hellman-group1-sha1',
                   'client_to_server' => array(
                     'crypt' => '3des-cbc',
                     'comp' => 'none'),
                   'server_to_client' => array(
                     'crypt' => 'aes256-cbc,aes192-cbc,aes128-cbc',
                     'comp' => 'none')
              );
              //---
              CModel_helper::PrintInfo('SFTP: соединение с сервером '.$this->ftpServer, true);
              if(!($conn_id = ssh2_connect($this->ftpServer,$this->port,$methods)))
              {
                 CLogger::write(CLoggerType::ERROR, "error ftp connect ".$this->ftpServer.' login: '.$this->login.', pass: ******, path: '.$this->descPath);
                 //--- если установлена флаг, то пытаемся несколько раз
                 $count_connect = 1;
                 if($this->isAutoResume)
                 {
                    sleep($this->attemptsTimeout);
                    while($count_connect<$this->attemptsToResume)
                    {
                       if(($conn_id= ssh2_connect($this->ftpServer,$this->port,$methods)))
                       {
                          //--- наконец-то соединение успешно установлено
                          break;
                       }
                       $count_connect++;
                       CLogger::write(CLoggerType::ERROR, "error sftp connect ".$this->ftpServer.", try ".$count_connect);
                       sleep($this->attemptsTimeout*$count_connect);
                    }
                 }
              }
              return $conn_id;
        */
    }

    /**
     * Установка соединения с фтп сервером
     *
     */
    protected function login()
    {
        $res_login = $this->m_sftp->login($this->login, $this->password);
        if (!$res_login) {
            CLogger::write(CLoggerType::ERROR, "error sftp auth " . $this->ftpServer . ":" . $this->login);
            //--- если установлена флаг, то пытаемся несколько раз
            $count_connect = 1;
            if ($this->isAutoResume) {
                CLogger::write(CLoggerType::ERROR, "error sftp auth " . $this->ftpServer . ":" . $this->login);
                sleep($this->attemptsTimeout);
                while ($count_connect < $this->attemptsToResume) {
                    if ($this->m_sftp->login($this->login, $this->password)) {
                        //--- наконец-то соединение успешно установлено
                        return true;
                    }
                    $count_connect++;
                    CLogger::write(CLoggerType::ERROR, "error sftp connect " . $this->ftpServer . ":" . $this->login . ", try " . $count_connect);
                    sleep($this->attemptsTimeout * $count_connect);
                }
            }
        }
        return true;
    }

    /**
     * Удаление списка файлов
     * @param array $list_files
     * @return void
     */
    public function DeleteListFile($list_files)
    {
        $this->m_sftp = new Net_SFTP($this->ftpServer, $this->port);
        //--- установка соединения по фтп
        //if(!($conn_id = $this->connect()))
        //return;
        //---
        CLogger::write(CLoggerType::DEBUG, "ftp connect " . $this->ftpServer);
        //--- логинимся на фтп сервере
        if (!($login_result = $this->login())) return;
        //---
        CLogger::write(CLoggerType::DEBUG, "sftp login " . $this->ftpServer . ":" . $this->login);
        //--- переходим на текущую папку
        if (!$this->ChangeCreateDirectory('')) {
            CLogger::write(CLoggerType::ERROR, "error ftp change or create directory " . $this->descPath);
            return;
        }
        foreach ($list_files as $filename) {
            $dst_name = $this->descPath . $filename;
            if ($this->m_sftp->delete($dst_name)) {
                CLogger::write(CLoggerType::DEBUG, "sftp: file deleted on server: " . $dst_name);
            } else {
                CLogger::write(CLoggerType::DEBUG, "sftp: error file delete on server: " . $dst_name);
            }
            //---
            $this->m_sftp->_disconnect(1);
            //---
            CModel_helper::PrintInfo('SFTP: чистка на sftp завершена', true);
            CLogger::write(CLoggerType::DEBUG, "sftp: connection closed");
        }
    }

    /**
     * Отправка по фтп списка файлов
     * @param array $list_files
     */
    protected function SendList($list_files)
    {
        $this->m_sftp = new Net_SFTP($this->ftpServer, $this->port);
        //--- установка соединения по фтп
        //if(!($conn_id = $this->connect()))
        //return;
        //---
        CLogger::write(CLoggerType::DEBUG, "ftp connect " . $this->ftpServer);
        //--- логинимся на фтп сервере
        if (!($login_result = $this->login())) return;
        //---
        CLogger::write(CLoggerType::DEBUG, "sftp login " . $this->ftpServer . ":" . $this->login);
        //--- переходим на текущую папку
        if (!$this->ChangeCreateDirectory('')) {
            CLogger::write(CLoggerType::ERROR, "error ftp change or create directory " . $this->descPath);
            return;
        }
        //--- пытаем закачать файл
        $old_dir_name = '.';
        $last_time_stop = time();
        //---
        CModel_helper::PrintInfo('SFTP: загружаем файлы на сервер...', true);
        //---
        foreach ($list_files as $file_info) {
            $finfo = pathinfo($file_info->Name);
            //--- проверка текущей папки
            if ($finfo['dirname'] != $old_dir_name) {
                $dst_dir = $this->descPath . $finfo['dirname'];
                if (!$this->ChangeCreateDirectory($finfo['dirname'])) {
                    CLogger::write(CLoggerType::ERROR, "error ftp change or create directory " . $finfo['dirname']);
                    return;
                } else {
                    CLogger::write(CLoggerType::DEBUG, "ftp: change or create directory " . $finfo['dirname']);
                }
                $old_dir_name = $finfo['dirname'];
            }
            //--- полное имя закачеваемого файла
            $file_name = $this->tempPath . $file_info->Name;
            //--- удаленное имя файла
            $dst_file_name = $this->descPath . $file_info->Name;
            //--- отправка файла на фтп сервере
            CLogger::write(CLoggerType::DEBUG, 'SFTP: try file copy from ' . $file_name . ' to ' . $dst_file_name . ' on sftp (' . $file_info->Name . ')');
            $this->file_send($file_name, $file_info->Name);
            //--- нужно ли проверять наличие файла
            if ((time() - $last_time_stop) > $this->checkStop) {
                if ($this->IsStop()) {
                    CLogger::write(CLoggerType::DEBUG, "SFTP: ftp connection closed, find stop file");
                    //--- закрытие фтп коннекта
                    $this->m_sftp->close();
                    return;
                }
                $last_time_stop = time();
            }
        }
        //--- закрытие фтп коннекта
        $this->m_sftp->close();
        //---
        CModel_helper::PrintInfo('SFTP: загрузка файлов завершена', true);
        CLogger::write(CLoggerType::DEBUG, "SFTP: connection closed");
    }

    /**
     * Изменение папки или создание новой
     * @param string $dir
     */
    private
    function ChangeCreateDirectory($dir)
    {
        //--- папку создаем
        $path = '/' . trim($this->descPath, '/ ');
        $dir = str_replace("\\", "/", $dir);
        $paths = explode("/", $dir);
        //---
        $ret = true;
        //---
        for ($i = 0, $sz = sizeof($paths); $i < $sz; $i++) {
            $path .= "/" . $paths[$i];
            if (!$this->m_sftp->chdir($path)) {
                $this->m_sftp->chdir("/");
                if (!$this->m_sftp->mkdir($path)) {
                    $ret = false;
                    break;
                }
                CLogger::write(CLoggerType::DEBUG, "sftp create path " . $path);
            }
        }
        $this->m_sftp->chdir('/' . trim($this->descPath, '/ '));
        return $ret;
    }

    /**
     *
     * Копирование файла на сервер
     * @param resourse $conn_id соедениение к фтп
     * @param string $file_name имя закачеваемого файла
     * @param string $dst_file_name имя файла на фтп
     */
    private
    function file_send($file_name, $dst_file_name)
    {
        $count_connect = 0;
        //--- максимально количество попыток
        $max_count_connect = $this->isAutoResume ? $this->attemptsToResume : 1;
        while ($count_connect < $max_count_connect) {
            if ($this->m_sftp->put($dst_file_name, $file_name, NET_SFTP_LOCAL_FILE)) {
                CLogger::write(CLoggerType::DEBUG, 'SFTP: file copy from ' . $file_name . ' to ftp: ' . $dst_file_name);
                //--- удаляем файл
                if (unlink($file_name)) {
                    CLogger::write(CLoggerType::DEBUG, 'SFTP: file deleted ' . $file_name);
                }
                //--- все ок выходим
                return;
            } else {
                CLogger::write(CLoggerType::ERROR, "SFTP: error file upload " . $file_name . " to " . $dst_file_name);
            }
            $count_connect++;
        }
    }
}

?>