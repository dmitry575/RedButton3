<?php
class CModel_task
{
   //--- массив с настройками
   private $m_task_path='data/tasks/';
   //--- статусы
   const STATUS_BEGIN=1;
   const STATUS_START=2;
   const STATUS_FINISH=3;
   //--- типы
   const TYPE_GENERATE=1;
   const TYPE_TRANSLATE=2;
   //--- имена файлов
   private $m_start_file="start.php";
   private $m_stop_file="stop.php";
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
   private function GetNextTaskNumber()
   {
      $max=0;
      $this->CheckPathTasks();
      $dir=dir($this->m_task_path);
      //---
      while(false !== $fname=$dir->read())
         {
         //--- Skip pointers
         if($fname == '.' || $fname == '..') continue;
         $pos=strpos($fname, '.');
         //---
         if($pos>0)
            {
            $num=(int)substr($fname, 0, $pos);
            if($num>$max) $max=$num;
            }
         }
      return $max + 1;
   }
   /**
    *
    * Проверка папки
    */
   private function CheckPathTasks()
   {
      if(!file_exists($this->m_task_path))
         {
         if(mkdir($this->m_task_path, 0777, true)) CLogger::write(CLoggerType::DEBUG, "directory " . $this->m_task_path . ' created');
         else
         CLogger::write(CLoggerType::ERROR, "directory " . $this->m_task_path . ' not create');
         }
   }
   /**
    * Сохранение задачи из POST-запроса, и добавление из настроеек
    * @param array $task
    * @param int $type
    * @param string $filename название файла в котором хранятся настройки
    * @return bool
    */
   public function SaveTask($task, $type,$filename)
   {
      //--- сериализуем настройки
      $number=$this->GetNextTaskNumber();
      //---
      $result=array('task'=>$task, 'date_create'=>time(),'date_end'=>0, 'status'=>self::STATUS_BEGIN, 'number'=>$number, 'type'=>$type,'settings'=>$filename);
      //--- проверка папки
      $this->CheckPathTasks();
      //--- получаем имя файла
      $fileName=$this->m_task_path . $number . ".data.php";
      //--- сохраняем сериализованные настройки
      file_put_contents($fileName, serialize($result));
      //--- выставляем права 777 на файл
      chmod($fileName, 0777);
      //---
      return true;
   }
   /**
    *
    * Обновим задачу
    * @param array $task
    * @return bool
    */
   public function UpdateTask($task)
   {
      //--- проверка папки
      $this->CheckPathTasks();
      //--- сохраняем сериализованные настройки
      file_put_contents($this->m_task_path . $task['number'] . ".data.php", serialize($task));
      chmod($this->m_task_path . $task['number'] . ".data.php", 0777);
      //---
      return true;
   }
   /**
    *
    * Обновление статуса начать
    * @param array $task
    */
   public function UpdateStatusStarting($task)
   {
      $task['status']=self::STATUS_START;
      $this->UpdateTask($task);
   }
   /**
    * Обновление статуса финиш
    * @param array $task
    */
   public function UpdateStatusFinish($task)
   {
      $task['status']=self::STATUS_FINISH;
      $task['date_end'] = time();
     //---
      $this->UpdateTask($task);
   }
   /**
    * Получение списка задач
    */
   public function GetListTask()
   {
      $list_files=array();
      CTools_files::GetAllFiles(rtrim($this->m_task_path, '/'), $list_files, $this->GetStopFilesname());
      $this->SortByNumber($list_files);
      return $list_files;
   }
   /**
    * Сортировка данных по номеру
    * @param array $list_files
    */
   private function SortByNumber(&$list_files)
   {
      usort($list_files, array($this, 'SortByFilename'));
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
      return ((int)$out_a[1][0]<(int)$out_b[1][0]) ? -1 : 1;
   }
   /**
    * Список стоп файлов
    */
   private function GetStopFilesname()
   {
      return array($this->m_start_file, $this->m_stop_file);
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
   public function GetFilename($id)
   {
      return $this->m_task_path . $id . ".data.php";
   }
   /**
    *
    * Получение данных о задаче
    * @param string $filename
    * @return array
    */
   public function GetTaskById($id)
   {
      return $this->GetTask($this->GetFilename($id));
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
      return file_exists($this->m_task_path . 'stop.php');
   }
   /**
    * Запущены ли сейчас таски?
    */
   public function IsStartTask()
   {
      return file_exists($this->m_task_path . 'start.php');
   }
   /**
    * Закончили работу
    */
   public function FinishedTask()
   {
      //--- удаление start.php
      if(file_exists($this->m_task_path . 'stop.php')) unlink($this->m_task_path . 'stop.php');
      //--- удаление stop.php
      if(file_exists($this->m_task_path . 'start.php')) unlink($this->m_task_path . 'start.php');
   }
   /**
    * Начали работау
    */
   public function StartTask()
   {
      $this->CheckPathTasks();
      //---
      file_put_contents($this->m_task_path . 'start.php', '<??>');
      chmod($this->m_task_path . 'start.php', 0777);
      //---
      if(file_exists($this->m_task_path . 'stop.php')) unlink($this->m_task_path . 'stop.php');
   }
   /**
    * Останавливаем работу задач
    */
   public function StopTask()
   {
      $this->CheckPathTasks();
      //---
      if(file_exists($this->m_task_path . 'start.php')) unlink($this->m_task_path . 'start.php');
      //---
      $isCreate=file_put_contents($this->m_task_path . 'stop.php', '<??>');
      chmod($this->m_task_path . 'stop.php', 0777);
      //---
      return $isCreate;
   }
   /**
    * Очистка всех задач
    *
    */
   public function ClearTask()
   {
      CTools_files::DeleteAll($this->m_task_path, false);
   }
   /**
    * Удаление задачи
    * @param int $id
    * @return bool
    */
   public function Delete($id)
   {
      $id=(int)$id;
      $fname=$this->m_task_path . $id . ".data.php";
      if(file_exists($fname))
         {
         unlink($fname);
         return true;
         }
      return false;
   }
   /**
    *
    * Получение данных из строки
    * @param string $line
    */
   public function GetDataFromString($line)
   {
      $d=explode('|', $line);
      $sz=sizeof($d);
      //---
      if($sz<3) return null;
      //---
      switch($sz)
      {
         //--- локально
         case 5:
            $info['nextUrl']=str_replace('http://', '', trim($d[0]));
            $info['path']=trim($d[1]);
            $info['keywords']=trim($d[2]);
            $info['text']=trim($d[3]);
            $info['settings']=trim($d[4]);
            //---
            $info['uploadTo']='loc';
            break;
         //--- на ФТП
         case 6:
            $info['nextUrl']=str_replace('http://', '', trim($d[0]));
            $info['ftpPath']=trim($d[2]);
            $info['keywords']=trim($d[3]);
            $info['text']=trim($d[4]);
            $info['settings']=trim($d[5]);
            //---
            $info['uploadTo']='ftp';
            //---
            $url_info=parse_url(trim($d[1]));
            if(!empty($url_info['scheme']))
               {
               $info['uploadTo']='ftp';
               //--- данные для фтп
               $info['ftpServer']=$url_info['host'] . (empty($url_info['port']) ? '' : ':' . $url_info['port']);
               $info['ftpLogin']=$url_info['user'];
               $info['ftpPassword']=$url_info['pass'];
               if(isset($url_info['port'])) $info['ftpPort']=$url_info['port'];
               }
            //---
            break;
      }
      //---
      return $info;
   }
   /**
    * Получение id из имени файла
    * @param $fname
    */
   public function GetId($fname)
   {
      $pos=strrpos($fname, '/');
      //--- нашли последний слеш
      if($pos !== FALSE)
         {
         $fname=substr($fname, $pos + 1);
         }
      //---
      $pos=strpos($fname, '.');
      if($pos !== FALSE) $fname=substr($fname, 0, $pos);
      //---
      return (int)$fname;
   }
}