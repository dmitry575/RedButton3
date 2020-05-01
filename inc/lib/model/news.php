<?php
//+------------------------------------------------------------------+
//|                  Copyright c 2012, RedButton Web Sites Generator |
//|                                      http://www.getredbutton.com |
//+------------------------------------------------------------------+
/**
 * Обработка новостей
 */
class CModel_news
  {
  //--- файл содержит когда последний раз обновлялись
  const FILE_LAST_UPDATE = './data/last_update.dat.php';
  //--- файл где хранятся последние новости
  const FILE_NEWS = './data/news.dat.php';
  //--- файл где хранbтся последний билд
  const FILE_LAST_BUILD = './data/last_build.dat.php';
  //--- как часто нужно ходить за последними новостями раз в сутки
  const TIME_UPDATE = 86400;
  /**
   * Сотрировка новостей
   * @param $a
   * @param $b
   * @return mixed
   */
  private function SortNews($a, $b)
    {
    return $b['unix_date_publish'] - $a['unix_date_publish'];
    }

  /**
   *
   * Обновление новостей
   * @param array $news
   */
  private function UpdateNews($news)
    {
    file_put_contents(self::FILE_NEWS, serialize($news));
    }

  /**
   *
   * Получение списка новостей
   */
  public function GetNews()
    {
    if(!file_exists(self::FILE_NEWS)) return array();
    //---
    return unserialize(file_get_contents(self::FILE_NEWS));
    }

  /**
   *
   * Нужно ли отправлять запрос на обновление данных
   */
  public static function IsNeedRequestToServer()
    {
    //---
    if(!file_exists(self::FILE_LAST_UPDATE)) return true;
    //---
    $time = (int)file_get_contents(self::FILE_LAST_UPDATE);
    //--- если прошло больше суток с последнего обновления
    return ($time + self::TIME_UPDATE) < time();
    }

  /**
   *
   * Получение последней версии билда
   */
  public static function GetLastBuild()
    {
    //--- Получение последней версии
    if(file_exists(self::FILE_LAST_BUILD))
      {
      return file_get_contents(self::FILE_LAST_BUILD);
      }
    }
  }