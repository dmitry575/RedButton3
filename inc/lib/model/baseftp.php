<?php
//+------------------------------------------------------------------+
//|                  Copyright c 2012, RedButton Web Sites Generator |
//|                                      http://www.getredbutton.com |
//+------------------------------------------------------------------+
/**
 *
 * Базовый класс для фтп
 * @author User
 *
 */
abstract class CModel_baseftp
  {
  /**
   * Уникальный ID закачки
   * Дорген его заранее генерирут через uniqid()
   * С этим ID можно будет приостанавливать и возобновлять закачку
   * Например: 4b3403665fea6
   */
  protected $uploadID;
  /**
   * Адрес FTP-сервера
   * Например: ftp.lezzvie.ru
   */
  protected $ftpServer;
  /**
   * Логин к FTP-серверу
   * Например: lezzvie@lezzvie.ru или lezzvie
   */
  protected $login;
  /**
   * Пароль к FTP-серверу
   * Например: sjs73olsiPs
   */
  protected $password;
  /**
   * Путь к папке, содержимое которой нужно закачать по FTP
   * Дорген сначала создает весь дорвей в эту папку, а потом уже начинается медленнный
   * процесс закачки файлов. После закачки - папку очищаем.
   * Например: ../data/temp/ftp-4b3403665fea6
   */
  protected $tempPath;
  /**
   * Использовать ли пассивный режим закачки файлов по FTP?
   * Например: true
   */
  protected $isPassiveMode;
  /**
   * Использовать ли автоматическую докачку файлов при разрыве соединения?
   * Будет использоваться преимущественно при пакетной генерации (через крон), когда человека
   * нет рядом и он не может следить за закачкой
   * Например: true
   */
  protected $isAutoResume = true;
  /**
   * Число попыток автоматической докачки при разрыве соединения
   * Работает, только если $isAutoResume=true
   * Например: 3
   */
  protected $attemptsToResume = 3;
  /**
   * Timeput между закачками файла
   * в секундах
   * @var int
   */
  protected $attemptsTimeout = 2;
  /**
   * Проверять наличие файла для остановки
   * в секундах
   * @var int
   */
  protected $checkStop = 10;
  /**
   * Папка которая находится на фтп
   * @var string
   */
  protected $descPath = './';
  /**
   * Количество потоков
   * @var int
   */
  protected $m_thread_count = 5;
  /**
   * После соединения с фтп сервером получаем текущую папку, от которой и начинаем работать
   * @var string
   */
  protected $m_ftp_base_path;

  /**
   *
   * Конструктор для загрузки по фтп
   * @param string $uploadID Уникальный ID закачки
   * @param string $ftpServer фтп сервер
   * @param int $port порт фтп
   * @param string $login логин на фтп
   * @param string $password пароль на фтп
   * @param string $descPath удаленная папка
   * @param string $tempPath темповая папка
   * @param bool $isPassiveMode пассивный режим
   */
  public function __construct($uploadID, $ftpServer, $port, $login, $password, $descPath = '.', $tempPath = './data/tmp/', $isPassiveMode = true)
    {
    $this->uploadID      = $uploadID;
    $this->ftpServer     = $ftpServer;
    $this->port          = $port;
    $this->login         = $login;
    $this->password      = $password;
    $this->tempPath      = $tempPath;
    $this->descPath      = $descPath;
    $this->isPassiveMode = $isPassiveMode;
    //--- первоначальные настройки
    if(empty($this->tempPath) || $this->tempPath[strlen($this->tempPath) - 1] != '/') $this->tempPath .= '/';
    if(empty($this->descPath) || $this->descPath[strlen($this->descPath) - 1] != '/') $this->descPath .= '/';
    //--- нужен только хост, иначе коннект не работает, если вначале указать ftp://
    $url_info = parse_url($this->ftpServer);
    if(!empty($url_info) && !empty($url_info['host']))
      {
      $this->ftpServer = $url_info['host'];
      if(!empty($url_info['port']) && $url_info['port'] > 0) $this->port = (int)$url_info['port'];
      }
    CLogger::write(CLoggerType::DEBUG, "FTP: upload id " . $uploadID);
    }

  /**
   * Текущая папка где файлы для фтп
   * @return string
   */
public function GetTempPath(){return $this->tempPath;}
  /**
   * Начать закачку файлов
   * Если указан $uploadID, то значит это ручное возобновление старой закачки и все настройки
   * берем из заранее созданного файла $tempPath/.settings
   */
  public function Start()
    {
    //--- при старте закачки - проверяем наличие файла .stop и удаляем его
    //--- в дальнейшем, в цикле нужно проверять наличие файла $tempPath/.stop, который создается при вызове метода Stop()
    $file_stop = $this->tempPath . '.stop_' . $this->uploadID;
    if(file_exists($file_stop)) unlink($file_stop);
    //--- нужно пробежаться по всем папкам и получить список файлов
    $list_files  = array();
    $count_files = $size_files = 0;
    $this->getListFilesForThreads($this->tempPath, '', $list_files, $count_files, $size_files);
    //---
    CLogger::write(CLoggerType::DEBUG, "files to load " . $count_files . ", size " . $size_files . " bytes");
    //--- запускаем закачку
    $this->SendList($list_files);
    //--- удаляем все за собой
    //$this->DeleteEmptyDirectory($this->tempPath);
    }

  /**
   * Получение списка файлов в массиве разбитом по количеству потоков.
   * В каждом массиве должно быть файлов на одинаковый размер
   */
  protected function getListFilesForThreads($fullpath, $basepath, &$paths, &$count_files, &$size_files)
    {
    if($handle = opendir($fullpath))
      {
      /* Именно этот способ чтения элементов каталога является правильным. */
      while(false !== ($fl = readdir($handle)))
        {
        if($fl == '.' || $fl == '..') continue;
        //---
        $new_f = $fullpath . $fl;
        if(is_dir($new_f))
          {
          $this->getListFilesForThreads($new_f . '/', $basepath . $fl . '/', $paths, $count_files, $size_files);
          }
        else
          {
          $size = filesize($new_f);
          $size_files += $size;
          $count_files++;
          $paths[] = new DirInfo($basepath . $fl, $size);
          }
        }
      closedir($handle);
      }
    }

  /**
   * Удаление списка файлов с фтп
   * @param array $list_files
   * @return bool
   */
  public abstract function DeleteListFile($list_files);

  /**
   * Отправка по фтп списка файлов
   * @param array $list_files
   */
  protected abstract function SendList($list_files);

  /**
   * Удаление всех пустых папок
   */
  public function DeleteEmptyDirectory($dir)
    {
    if(CTools_files::DeleteAll($dir))
      {
      CLogger::write(CLoggerType::DEBUG, "ftp: path deleted " . $dir);
      }
    }

  /**
   * Принудительная остановка закачки файлов
   * При остановке создается файл $tempPath/.stop, наличие которого потом
   * будет проверяться в цикле при закачке каждого файла
   */
  protected function Stop()
    {
    $file_stop = $this->tempPath . '.stop_' . $this->uploadID;
    file_put_contents($file_stop, "stop");
    }

  /**
   * Нужно ли прекращать закачку файлов
   */
  protected function IsStop()
    {
    $file_stop = $this->tempPath . '.stop_' . $this->uploadID;
    return file_exists($file_stop);
    }
  }
class DirInfo
  {
  public $Name;
  public $Size;

  public function __construct($name, $size)
    {
    $this->Name = $name;
    $this->Size = $size;
    }
  }
