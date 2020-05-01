<?php
//+------------------------------------------------------------------+
//|                  Copyright c 2012, RedButton Web Sites Generator |
//|                                      http://www.getredbutton.com |
//+------------------------------------------------------------------+
/**
 * ССЫЛКИ ИЛИ ПРОСТО УРЛЫ ДЛЯ КАРТИНОК
 *
 */
/**
 *
 * Класс для управления ссылками на картинки
 * @author User
 *
 */
class CModel_imageslinks
  {
  const RAND         = 1;
  const TEXT_BEFORE  = 2;
  const TEXT_CENTER  = 4;
  const TEXT_AFTER   = 8;
  const ALIGN_LEFT   = 2;
  const ALIGN_CENTER = 4;
  const ALIGN_RIGHT  = 8;
  /**
   *
   * путь к папке с урлами
   * @var string
   */
  const PATH = "data/links";
//--- список загруженных урлов на картинки
  private $m_images = array();
//--- список ссылок на картинки
  private $m_images_links = array();

  /**
   * Пустой конструктор
   */
  public function __construct()
    {
    }

  /**
   * Конструктор
   *
   * @param array $params
   * @param $model_keywords
   * @param string $temp_dir
   */
  public function Init(&$params)
    {
    $this->m_params = $params;
    //--- заполним внутренние параметры
    if(!empty($this->m_params['images_where'])) $this->m_where = $this->GetWhere($this->m_params['images_where']);
    else
    $this->m_where = self::RAND;
    //---
    if(!empty($this->m_params['images_pos'])) $this->m_possition = $this->GetPossition($this->m_params['images_pos']);
    else
    $this->m_possition = self::RAND;
    }

  /**
   * загрузка урлов из файлов
   * @param $files_urls
   * @return array
   */
  public static function LoadUrls($filename, $is_cache = 1)
    {
    if(empty($filename)) return array();
//---
    $is_cache = $is_cache && extension_loaded('apc');
//---
    $result = array();
    $fname  = $filename;
    if(file_exists($fname))
      {
      if($is_cache)
        {
        $data = apc_fetch($fname);
        //--- наличие кеша
        if(empty($data))
          {
          $data = file($fname);
          array_walk($data, create_function('&$val', '$val = trim($val);'));
          apc_store($fname, $data, 600);
          }
        else
          {
          CLogger::write(CLoggerType::DEBUG, 'image links: loading urls from apc_store: ' . $fname);
          }
        return $data;
        }
      else
        {
        $data = file($fname);
        array_walk($data, create_function('&$val', '$val = trim($val);'));
        CLogger::write(CLoggerType::DEBUG, 'image links: loading urls from file: ' . $fname);
        return $data;
        }
      }
    CLogger::write(CLoggerType::DEBUG, 'image links: loaded urls ' . count($result) . ' from ' . $fname);
    return $result;
    }

  /**
   * Папка с ссылками
   */
  function GetPath()
    {
    return self::PATH;
    }

  /**
   * Получение случайной картинки
   * @param $files
   * @return null|string
   */
  public function GetRandImageFromFile($files, $align = 0, $keyword = '')
    {
    if(is_array($files))
      {
      $filename = $files[rand(0, count($files) - 1)];
      }
    else
    $filename = $files;
    //---
    $fullname = self::PATH . '/' . (!empty($path) ? (trim($path, '/') . '/') : "") . $filename;
    $ar_links = $this->LoadUrls($fullname);
    if(empty($ar_links)) return null;
    //---
    $link = $ar_links[rand(0, count($ar_links) - 1)];
    //---
    $align_str = $this->GetAlign($align);
    //--- запишим атрибуты в разном порядке
    $attr = array('src'    => $link,
                  'border' => 0,
                  'alt'    => str_replace('"', '', $keyword),
                  'title'  => str_replace('"', '', $keyword),);
    if(!empty($align_str)) $attr['align'] = $align_str;
    //---

    $attr = $this->shuffle_assoc($attr);
    $result = '';
    foreach($attr as $name => $val) $result .= (!empty($result) ? ' ' : '') . $name . '="' . $val . '"';
    //---
    return '<img ' . $result . '>';
    }

  /**
   * Получение значение align для картинки
   * @param $align
   * @return string
   */
  private function GetAlign($align)
    {
    $align = (int)$align;
    if($align == 0) return '';
    //--- случайный
    if($align & self::RAND)
      {
      $c = rand(0, 2);
      if($c == 0) return 'center';
      if($c == 1) return 'right';
      if($c == 2) return 'left';
      }
    elseif(($align & self::ALIGN_RIGHT) == self::ALIGN_RIGHT) return 'right';
    elseif(($align & self::ALIGN_LEFT) == self::ALIGN_LEFT) return 'left';
    elseif(($align & self::ALIGN_CENTER) == self::ALIGN_CENTER) return 'center';
    //---
    return '';
    }

  /**
   * Получение урла из файла
   * @param $files
   * @return null
   */
  public function GetRandUrlFromFiles($files, $anchor)
    {
    if(is_array($files))
      {
      $filename = $files[rand(0, count($files) - 1)];
      }
    else
    $filename = $files;
    //---
    $fullname = self::PATH . '/' . (!empty($path) ? (trim($path, '/') . '/') : "") . $filename;
    $ar_links = $this->LoadUrls($fullname);
    if(empty($ar_links)) return null;
    //---
    $link = $ar_links[rand(0, count($ar_links) - 1)];
    return '<a href="' . $link . '">' . $anchor . '</a>';
    }
private function shuffle_assoc( $array ) 
{ 
   $keys = array_keys( $array ); 
   shuffle( $keys ); 
   return array_merge( array_flip( $keys ) , $array ); 
}

  }