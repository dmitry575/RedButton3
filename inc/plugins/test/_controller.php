<?php
//+------------------------------------------------------------------+
//|                  Copyright c 2012, RedButton Web Sites Generator |
//|                                      http://www.getredbutton.com |
//+------------------------------------------------------------------+
class CPlugin_test extends IPlugin
  {
  /**
   *
   * Автор
   * @var string
   */
  protected $m_author = "Dmitry";
  /**
   *
   * Описание
   * @var string
   */
  protected $m_description = "Test plugin";
  //--- переводы
  protected static $m_translate = array('ru' => array('main_title' => 'Задачи для пакетной генерации',),
    //---
                                        'en' => array('main_title' => 'Tasks for packge generation',));

  //---
  /**
   *
   * Выполение функции до начала работы с ключевиками
   * @param string $keyword
   * @param string $text
   * @param array $data
   */
  public function OnBeginKeyword(&$keyword, &$text, &$data)
    {
    //file_put_contents("1.txt", "OnBeginKeyword");
    }

  /**
   *
   * Выполение функции до начала обработки макросов
   * @param string $keyword
   * @param string $text
   * @param array $data
   */
  public function OnBeginMacros(&$keyword, &$text, &$data)
    {
    $text = str_replace('[TEST-MACROS]', 'Testing macros - ' . $keyword . ' [NEW-TEST-MACROS]', $text);
    }

  /**
   *
   * Выполение функции после обработки всех макросов
   * @param string $keyword
   * @param string $text
   * @param array $data
   */
  public function OnEndMacros(&$keyword, &$text, &$data)
    {
    $text = str_replace('[NEW-TEST-MACROS]', 'test finished', $text);
    }

  /**
   *
   * Выполение функции после обработки ключа
   * @param string $keyword
   * @param string $text
   * @param array $data
   */
  public function OnEndKeyword(&$keyword, &$text, &$data)
    {
    //file_put_contents("2.txt", "OnEndKeywords");
    }
  /**
   *
   * Выполение функции вначале генерации
   * @param string $localPath
   * @param array $params
   */
  public function OnBeginGenerate($localPath,$params)
    {
    //file_put_contents("OnBeginGenerate.txt", var_export($localPath,true)."\r\n".var_export($params,true));
    }
  /**
   *
   * Выполение функции вконце генерации
   * @param string $localPath
   * @param array $params
   */
  public function OnEndGenerate($localPath,$params)
    {
    //file_put_contents("OnEndGenerate.txt",  var_export($localPath,true)."\r\n".var_export($params,true));
    }
  }

?>
