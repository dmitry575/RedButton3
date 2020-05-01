<?php
//+------------------------------------------------------------------+
//|                  Copyright c 2012, RedButton Web Sites Generator |
//|                                      http://www.getredbutton.com |
//+------------------------------------------------------------------+
/**
Работа с сокетами
 */
class SocketResult
  {
  /**
   * Сокет
   * @var obj
   */
  private $m_socket;
  /**
   * статус сокета 0 = не готов, 1 = готов
   * @var int
   */
  private $m_status;

  /**
   * @param handel $socket
   * @param int $status
   */
  public function __construct($socket, $status)
    {
    $this->m_socket = $socket;
    $this->m_status = $status;
    }

  /**
   * данные о сокете
   * @return handel|obj
   */
  public function getSocket()
    {
    return $this->m_socket;
    }

  /**
   * данные о статусе
   * @return int
   */
  public function getStatus()
    {
    return $this->m_status;
    }

  /**
   * выставление статуса сокета
   * @param $status
   */
  public function setStatus($status)
    {
    //--- если статус выставили окончание, и еще не закрыт сокет, принудительно закроем
    if($status == 1 && $this->m_socket) fclose($this->m_socket);
    $this->m_status = $status;
    }

  /**
   * окончание
   * @return bool
   */
  public function feof()
    {
    return feof($this->m_socket);
    }

  /**
   * Данные из сокета
   * @return string
   */
  public function fread()
    {
    return fread($this->m_socket, 65536);
    }
  }
/**
 * Многопоточный HTTP клиент
 */
class CModel_multiHttp
  {
  const PREFIX_LOG = "multihttp:";
  /**
   * массив сокетов
   * @var array
   */
  private $m_sockets = array();
  /**
   * массив результатов
   * @var array
   */
  private $m_results = array();
  /**
   * текущий id
   * @var int
   */
  private $m_current_id = 0;
  private $m_is_stop = false;

  /**
   * Добавление запроса в очередь, создание сокета
   * @param  string $url
   */
  public function AddRequestGet($url)
    {
    $url_info          = parse_url($url);
    $url_info['port']  = !empty($url_info['port']) ? $url_info['port'] : 80;
    $url_info['path']  = !empty($url_info['path']) ? $url_info['path'] : '/';
    $url_info['query'] = !empty($url_info['query']) ? "?" . $url_info['query'] : "";
    //--- создение
    $socket = fsockopen($url_info['host'], $url_info['port'], $errno, $error, 15);
    if(!$socket)
      {
      CLogger::write(CLoggerType::DEBUG, self::PREFIX_LOG . " create request failed " . $error . " [" . $errno . "] " . $url_info['host'] . ":" . $url_info['port']);
      return -1;
      }
    stream_set_blocking($socket, 0);
    stream_set_timeout($socket, 60);
    //---
    $requestHeader = 'GET ' . $url_info['path'] . " HTTP/1.1\r\n";
    $requestHeader .= "Host: " . $url_info['host'] . "\r\n";
    $requestHeader .= "Accept: */*,application/xhtml+xml,application/xml;q=0.9\r\n";
    $requestHeader .= "Accept-Language: ru-ru,ru;q=0.8,en-us;q=0.5,en;q=0.3\r\n";
    $requestHeader .= "Accept-Charset: utf-8,windows-1251;q=0.7,*;q=0.7\r\n";
    $requestHeader .= "User-Agent: " . CModel_helper::GetUserAgent() . "\r\n\r\n";
    $requestHeader .= "Connection: close\r\n\r\n";
    //---
    $write = fputs($socket, $requestHeader);
    //---
    $id = $this->m_current_id;
    //---
    $this->m_sockets[$id] = new SocketResult($socket, 0);
    $this->m_results[$id] = '';
    //---
    CLogger::write(CLoggerType::DEBUG, self::PREFIX_LOG . " create request " . $id . " to " . $url);
    //---
    $this->m_current_id++;
    return $id;
    }

  /**
   * Получение количества потоков
   */
  public function GetCountThreads()
    {
    return count($this->m_sockets);
    }

  /**
   * Зачистка результата
   *
   * @param int $id
   */
  public function ClearResult($id)
    {
    CLogger::write(CLoggerType::DEBUG, self::PREFIX_LOG . " delete thread " . $id);
    //--- удаляем из памяти
    if(isset($this->m_results[$id])) unset($this->m_results[$id]);
    if(isset($this->m_sockets[$id])) unset($this->m_sockets[$id]);
    }

  /**
   * Получение готовых результатов
   * @return array
   */
  public function GetReady()
    {
    $result = array();
    //---
    foreach($this->m_sockets as $id => $socket)
      {
      if($socket->getStatus()) $result[$id] = $this->m_results[$id];
      }
    //---
    return $result;
    }

  /**
   * вычитываем данные из сокетов и ждем, чтобы хотя бы один вычитался до конца
   */
  public function WaitEndsSomeone()
    {
    while(true)
      {
      if($this->m_is_stop) return false;
      $need_exit = true;
      foreach($this->m_sockets as $id => $socket)
        {
        if($socket == null || $socket->getSocket() == null)
          {
          $this->m_sockets[$id]->setStatus(1);
          CLogger::write(CLoggerType::DEBUG, self::PREFIX_LOG . " thread " . $id . " is null");
          continue;
          }
        //---
        if($socket->getStatus() == 1)
          {
          CLogger::write(CLoggerType::DEBUG, self::PREFIX_LOG . " thread " . $id . " status FINISHED");
          return true;
          }
        $need_exit = false;
        //---
        if($socket->feof())
          {
          // убиваем сокет, который отработал
          $this->m_sockets[$id]->setStatus(1);
          CLogger::write(CLoggerType::DEBUG, self::PREFIX_LOG . " " . $id . ' finished OK');
          return true;
          }
        else
          {
          // читаем данные из сокета
          $temp = $socket->fread();
          //---
          if(isset($this->m_results[$id])) $this->m_results[$id] .= $temp;
          else $this->m_results[$id] = $temp;
          }
        }
      //--- если нужно вхыодить выходим
      if($need_exit) return true;
      //--- пауза
      usleep(100);
      }
    }

  /**
   * подождем завершение всех потоков
   */
  public function WaitAll()
    {
    $now = time();
    while(true)
      {
      $need_exit = true;
      foreach($this->m_sockets as $id => $socket)
        {
        //--- проверка статуса
        if($socket->getStatus() == 1)
          {
          //CLogger::write(CLoggerType::DEBUG, self::PREFIX_LOG . " thread " . $id . " status FINISHED");
          continue;
          }
        //CLogger::write(CLoggerType::DEBUG, self::PREFIX_LOG . " thread " . $id . " status not finished");
        $need_exit = false;
        //---
        if($socket->feof() || (time() - $now) > 20)
          {
          // убиваем сокет, который отработал
          $this->m_sockets[$id]->setStatus(1);
          CLogger::write(CLoggerType::DEBUG, self::PREFIX_LOG . " thread " . $id . " set status FINISHED");
          }
        else
          {
          // читаем данные из сокета
          $temp = $socket->fread();
          //---
          if(isset($this->m_results[$id])) $this->m_results[$id] .= $temp;
          else $this->m_results[$id] = $temp;
          }
        }
      //--- если нужно вхыодить выходим
      if($need_exit) return true;
      //--- пауза
      usleep(100);
      if($this->m_is_stop) return false;
      }
    }

  /**
   * подождем завершение всех потоков
   */
  public function CloseAll()
    {
    foreach($this->m_sockets as $id => $socket)
      {
      // убиваем сокет, который отработал
      $this->m_sockets[$id]->setStatus(1);
      CLogger::write(CLoggerType::DEBUG, self::PREFIX_LOG . " thread " . $id . " set status FINISHED");
      }
    }

  /**
   * Установка флага выхода
   */
  public function setStop()
    {
    $this->m_is_stop = true;
    }
  }
