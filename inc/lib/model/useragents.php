<?php
//+------------------------------------------------------------------+
//|                  Copyright c 2012, RedButton Web Sites Generator |
//|                                      http://www.getredbutton.com |
//+------------------------------------------------------------------+
/**
 * Created by JetBrains PhpStorm.
 * User: Dmitry
 * Date: 11.06.13
 * Time: 22:42
 * To change this template use File | Settings | File Templates.
 */
class CModel_UserAgents
  {
  /**
   * список всех юзер агентов
   * @var array
   */
  private static $m_useragents = null;

  /**
   * получение случайного
   * @return string
   */
  public static function GetRandom()
    {
    if(self::$m_useragents == null) self::load();
    if(!empty(self::$m_useragents)) return array_rand(self::$m_useragents);
    //---
    return CModel_helper::GetUserAgent();
    }

  /**
   * загрузка юзер агентов
   */
  private static function load()
    {
    if(!file_exists(CModel_settings::USER_AGENT_FILE)) return;
    $filename = CModel_settings::USER_AGENT_FILE;
    //---
    $fp = fopen($filename, "r");
    if(!$fp)
      {
      CLogger::write(CLoggerType::DEBUG, 'useragent: file not open ' . $filename);
      return;
      }
    //---
    $data = fread($fp, filesize($filename));
    fclose($fp);
    if(empty($data)) return;
    //--- разобьем
    $array = explode("\n", $data);
    if(is_array($array) && count($array) > 0)
      {
      self::$m_useragents = array();
      //--- делаем зачистку
      foreach($array as $v)
        {
        if(strlen(trim($v)) > 0)
          {
          self::$m_useragents[] = trim($v);
          }
        }
      }
      //---
    CLogger::write(CLoggerType::DEBUG, 'useragent: loaded ' . count(self::$m_useragents));

    }
  }