<?
/**
 * Class CModel_uploadtask
 * Класс для управлению очередями по загрузке по фтп
 */
class CModel_UploadTask
  {
  const PATH = 'data/ftp/';
  /**
   * Статус начала
   */
  const STATUS_BEGIN = 1;
  /**
   * Статус начала задачи
   */
  const STATUS_START = 2;
  /**
   * Cтатус завершения задачи
   */
  const STATUS_FINISH = 3;
  /**
   * Cтатус не успешного завершения задачи
   */
  const STATUS_FAILED_FINISH = 4;
  /**
   * Префикс для логов
   */
  const PREFIX_LOG = 'upload: ';
  /**
   * количество потоков будет 10
   */
  const THREADS_COUNT = 10;
  //--- имена файлов
  private $m_start_file = "start.php";
  private $m_stop_file = "stop.php";

  //---
  /**
   * Инициализация
   */
  public function __construct()
    {
    }

  /**
   *
   * Получить следующий номер задачи
   */
  private function GetNextTaskNumber(&$id_thread)
    {
    $max = 0;
    $this->CheckPathTasks();
    $pathname = self::PATH . $id_thread;
    $dir      = dir(self::PATH . $id_thread);
    //---
    while(false !== $fname = $dir->read())
      {
      if(!is_file($pathname . '/' . $fname)) continue;
      //--- Skip pointers
      if($fname == '.' || $fname == '..') continue;
      $pos = strpos($fname, '.');
      //---
      if($pos > 0)
        {
        $num = (int)substr($fname, 0, $pos);
        if($num > $max) $max = $num;
        }
      }
    //---
    return $max + 1;
    }

  /**
   * Подсчитаем сколько и в какой папке дорвеев на закачку
   */
  private function GetCountForAllThread()
    {
    $result = array();
    for($i = 0; $i < self::THREADS_COUNT; $i++)
      {
      $dirname    = self::PATH . $i;
      $result[$i] = CTools_files::GetCountSubDirs($dirname);
      }
    return $result;
    }

  /**
   * найдем номер папки куда нужно будет заливать очередную задачу
   * @return int
   */
  private function GetPathIdUpload()
    {
    $id  = 0;
    $min = -1;
    for($i = 0; $i < self::THREADS_COUNT; $i++)
      {
      $dirname = self::PATH . $i;
      $c       = CTools_files::GetCountSubDirs($dirname);
      if($c == 0) return $i;
      //---
      if($min == -1)
        {
        $min = $c;
        $id  = $i;
        }
      elseif($c < $min)
        {
        $min = $c;
        $id  = $i;
        }
      }
    return $id;
    }

  /**
   * сохранение данных при генерации
   * @param $path_from
   * @param $settings
   *
   * @return bool
   */
  public function SaveSiteToTask($path_from, &$settings)
    {
    $path_id = $this->GetPathIdUpload();
    //---
    $pathname = $this->GetPathName($path_id,$settings);
    $path_to  = self::PATH . $path_id . '/' . $pathname;
    //--- скопируем данные
    if(!CTools_files::CopyPath($path_from, $path_to)) return false;
    //--- сохраним данные
    return $this->SaveTask($settings, $path_id, $pathname);
    }

  /**
   * получим новую папку и создадим ее
   * @param $path_id
   * @return string
   */
  private function GetPathName($path_id,&$settings)
    {
    $path = uniqid("upl_".CModel_tools::generate_file_name($settings['nextUrl']));
    $d    = self::PATH . $path_id . '/' . $path;
    $i    = 1;
    while(file_exists($d))
      {
      $p = $path . '__' . ($i++);
      $d = self::PATH . $path_id . '/' . $p;
      }
    if(!empty($p)) $path = $p;
    if(mkdir($d, 0777, true))
      {
      CLogger::write(CLoggerType::DEBUG, self::PREFIX_LOG . "directory " . $d . ' created');
      }
    return $path;
    }

  /**
   * Сохранение задачи  и добавление из настроеек
   * @param array $task
   * @param int $type
   * @param string $filename название файла в котором хранятся настройки
   * @return bool
   */
  public function SaveTask($settings, $path_id, $pathname)
    {
    //--- сериализуем настройки
    $number = $this->GetNextTaskNumber($path_id);
    //--- сохраняем настройки
    $result = array('task'        => $settings,
                    'date_create' => time(),
                    'date_end'    => 0,
                    'status'      => self::STATUS_BEGIN,
                    'number'      => $number,
                    'pathname'    => $pathname);
    //--- получаем имя файла
    $fileName = self::PATH . $path_id . '/' . $number . ".data.php";
    //--- сохраняем сериализованные настройки
    file_put_contents($fileName, serialize($result));
    //--- выставляем права 777 на файл
    chmod($fileName, 0777);
    //---
    return true;
    }

  /**
   *
   * Проверка папки
   */
  private function CheckPathTasks()
    {
    if(!file_exists(self::PATH))
      {
      if(mkdir(self::PATH, 0777, true)) CLogger::write(CLoggerType::DEBUG, self::PREFIX_LOG . "directory " . self::PATH . ' created');
      else
      CLogger::write(CLoggerType::ERROR, self::PREFIX_LOG . "directory " . self::PATH . ' not create');
      }
    }

  /**
   * Получение списка задач
   */
  public function GetListTask($thread_id)
    {
    $path       = self::PATH . $thread_id;
    $list_files = CTools_files::GetAllOnlyFiles($path, array('php'));
    //---
    if(!empty($list_files)) $this->SortByNumber($list_files);
    return $list_files;
    }

  /**
   * Сортировка данных по номеру
   * @param array $list_files
   */
  private function SortByNumber(&$list_files)
    {
    usort($list_files, array($this,
                             'SortByFilename'));
    }

  /**
   *
   * Само упорядочивание
   * @param string $a
   * @param string $b
   * @return int
   */
  private function SortByFilename($a, $b)
    {
    if($a == $b)
      {
      return 0;
      }
    if(!preg_match_all("|([0-9]*)\.data\.php|U", $a, $out_a, PREG_PATTERN_ORDER)) return 1;
    if(!preg_match_all("|([0-9]*)\.data\.php|U", $b, $out_b, PREG_PATTERN_ORDER)) return -1;
    return ((int)$out_a[1][0] < (int)$out_b[1][0]) ? -1 : 1;
    }

  /**
   * Список стоп файлов
   */
  private function GetStopFilesname()
    {
    return array($this->m_start_file,
                 $this->m_stop_file);
    }

  /**
   *
   * Получение данных о задаче
   * @param string $filename
   * @return array
   */
  public function GetTask($filename)
    {
    if(!file_exists($filename)) return null;
    //---
    return unserialize(file_get_contents($filename));
    }

  /**
   * Получение имени файла
   * @param $id
   */
  public function GetFilename($thread_id, $id)
    {
    return self::PATH . $thread_id . '/' . $id . ".data.php";
    }

  /**
   *
   * Получение данных о задаче
   * @param string $filename
   * @return array
   */
  public function GetTaskById($thread_id, $id)
    {
    return $this->GetTask($this->GetFilename($thread_id, $id));
    }

  /**
   *
   * Получение имени статуса
   * @param int $status
   * @return string
   */
  public static function GetStatusName($status)
    {
    global $LNG, $TRANSLATE;
    switch($status)
    {
      case self::STATUS_BEGIN:
        return "<span>" . $TRANSLATE[$LNG]['wait_status'] . "</span>";
      case self::STATUS_START:
        return "<span>" .$TRANSLATE[$LNG]['starting_status']. "</span>";
      case self::STATUS_FAILED_FINISH:
        return "<span>" .$TRANSLATE[$LNG]['finish_failed_status']. "</span>";
      case self::STATUS_FINISH:
        return "<span>" . $TRANSLATE[$LNG]['finish_status'] . "</span>";
    }
    return "<span style='color: #AF2020'>" . $TRANSLATE[$LNG]['error_status'] . "</span>";
    }

  /**
   * Нужно ли останавливать выполнение задачи
   */
  public function IsStopTask()
    {
    return file_exists(self::PATH . 'stop.php');
    }

  /**
   * Запущены ли сейчас таски?
   */
  public function IsStartTask()
    {
    return file_exists(self::PATH . 'start.php');
    }

  /**
   * Закончили работу
   */
  public function FinishedTask()
    {
    //--- удаление start.php
    if(file_exists(self::PATH . 'stop.php')) unlink(self::PATH . 'stop.php');
    //--- удаление stop.php
    if(file_exists(self::PATH . 'start.php')) unlink(self::PATH . 'start.php');
    }

  /**
   * Начали работау
   */
  public function StartTask()
    {
    $this->CheckPathTasks();
    //---
    file_put_contents(self::PATH . 'start.php', '<??>');
    chmod(self::PATH . 'start.php', 0777);
    //---
    if(file_exists(self::PATH . 'stop.php')) unlink(self::PATH . 'stop.php');
    }

  /**
   * Останавливаем работу задач
   */
  public function StopTask()
    {
    $this->CheckPathTasks();
    //---
    if(file_exists(self::PATH . 'start.php')) unlink(self::PATH . 'start.php');
    //---
    $isCreate = file_put_contents(self::PATH . 'stop.php', '<??>');
    chmod(self::PATH . 'stop.php', 0777);
    //---
    return $isCreate;
    }

  /**
   * Очистка всех задач
   *
   */
  public function ClearTask()
    {
    for($i = 0; $i < self::THREADS_COUNT; $i++) CTools_files::DeleteAll(self::PATH . $i, false);
    }

  /**
   * Удаление задачи
   * @param int $id
   * @return bool
   */
  public function Delete($thread_id, $id)
    {
    $id    = (int)$id;
    $fname = self::PATH . $thread_id . '/' . $id . ".data.php";
    if(file_exists($fname))
      {
      unlink($fname);
      return true;
      }
    return false;
    }

  public function UpdateStatus($thread_id, $task, $status)
    {
    $task['status'] = $status;
    $this->UpdateTask($thread_id, $task);
    }

  /**
   *
   * Обновление статуса начать
   * @param array $task
   */
  public function UpdateStatusStarting($thread_id, $task)
    {
   $this->UpdateStatus($thread_id,$task,self::STATUS_START);
    }
  /**
   *
   * Обновим задачу
   * @param array $task
   * @return bool
   */
  public function UpdateTask($thread_id,$task)
    {
    //--- проверка папки
    $this->CheckPathTasks();
    //--- сохраняем сериализованные настройки
      $filename = self::PATH.$thread_id.'/' . $task['number'] . ".data.php";
    if(file_put_contents($filename, serialize($task)))
      {
      CLogger::write(CLoggerType::DEBUG, self::PREFIX_LOG . "task " . $filename . ' updated');
      }
    chmod($filename, 0777);
    //---
    return true;
    }
  /**
   * Обновление статуса финиш
   * @param array $task
   */
  public function UpdateStatusFinish($thread_id,$task)
    {
    $task['date_end'] = time();
    $this->UpdateStatus($thread_id,$task,self::STATUS_FINISH);
    }
  /**
   * Обновление статуса финиш
   * @param array $task
   */
  public function UpdateStatusFailedFinish($thread_id,$task)
    {
    $this->UpdateStatus($thread_id,$task,self::STATUS_FAILED_FINISH);
    }

  /**
   * Получение id из имени файла
   * @param $fname
   */
  public function GetId($fname)
    {
    $pos = strrpos($fname, '/');
    //--- нашли последний слеш
    if($pos !== FALSE)
      {
      $fname = substr($fname, $pos + 1);
      }
    //---
    $pos = strpos($fname, '.');
    if($pos !== FALSE) $fname = substr($fname, 0, $pos);
    //---
    return (int)$fname;
    }
  }

?>