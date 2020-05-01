<?
//+------------------------------------------------------------------+
//|                  Copyright c 2012, RedButton Web Sites Generator |
//|                                      http://www.getredbutton.com |
//+------------------------------------------------------------------+
/**
 * Обработка макросов для вставки изображения из файла
 * Class CModel_imagesurls
 */
class CModel_ImagesUrls
  {
  const MAX_URLS = 500000;
  /**
   * имя файла куда будет перенаправлять запрос
   */
  const PATH_IMG_PHP = './inc/public/img.php';
  /**
   * список урлов из файла
   * @var array
   */
  private $m_list_urls;
  /**
   * список папок для картинок
   * @var array
   */
  private $m_directoies = array('c',
                                'i',
                                'imgs',
                                'upl',
                                'upload',
                                'uploads',
                                'contents',
                                'img',
                                'wp-content');
  /**
   * текущая папка
   * @var string
   */
  private $m_current_dir;
  /**
   * список название файлов и урлов
   * @var array
   */
  private $m_list_names;

  /**
   *
   * @param string $filename
   */
  public function __construct()
    {
    //--- получим папку для текущей сессии
    $this->m_current_dir = $this->m_directoies[array_rand($this->m_directoies)];
    //--- зачистим название
    $this->m_list_names = array();
    }

  /**
   * Инцилизация данных
   * @return bool
   */
  public function Init($filename)
    {
    //--- проверка файла со ссылками
    if(!file_exists($filename))
      {
      CLogger::write(CLoggerType::ERROR, 'file with images not found: ' . $filename);
      return false;
      }
    //---
    $hf = fopen($filename, "r");
    if(!$hf)
      {
      CLogger::write(CLoggerType::ERROR, 'file can not open: ' . $filename);
      return false;
      }
    if(isset($this->m_list_urls[$filename])) unset($this->m_list_urls[$filename]);
    //---
    $this->m_list_urls[$filename] = array();
    $i                            = 0;
    //---
    while(($url = fgets($hf, 4096)) !== false)
      {
      $u = trim($url);
      if(empty($u)) continue;
      $this->m_list_urls[$filename][] = $u;
      $i++;
      if($i > self::MAX_URLS) break;
      }
    fclose($hf);
    //---
    return true;
    }

  /**
   * Получение строки для .htaccess
   * @return string
   */
  public function GetDataHtaccess()
    {
    return 'RewriteRule ' . $this->m_current_dir . '/(.*)\.(jpg|png|jpeg|gif)$ img.php?image=' . $this->m_current_dir . '/$1.$2 [NC,L]' . "\r\n";
    }

  /**
   * получаем случайный урл на картинку
   * @param string $filename
   * @param string $newname
   */
  public function GetRandUrlName($filename, &$new_basename)
    {
    if(!isset($this->m_list_urls[$filename])) $this->Init($filename);
    //--- получаем случайный урл
    if(isset($this->m_list_urls[$filename]) && is_array($this->m_list_urls[$filename]))
      {
      $temp_name = $this->m_list_urls[$filename][array_rand($this->m_list_urls[$filename])];
      }
    else return '';
    //--- информацию
    $path_info = pathinfo($temp_name);
    //---
    if(empty($path_info)) return '';
    //---
    if(empty($path_info['extension'])) $ext = 'jpg';
    else                               $ext = strtolower($path_info['extension']);
    //---
    $full_name = $this->m_current_dir . '/' . $new_basename . '.' . $ext;
    //---
    $i = 0;
    while(isset($this->m_list_names[$full_name]))
      {
      $full_name = $this->m_current_dir . '/' . $new_basename . '_' . (++$i) . '.' . $ext;
      }
    //---
    $this->m_list_names[$full_name] = $temp_name;
    //---
    return $full_name;
    }

  /**
   * получаем случайный урл на картинку, без изменений
   * @param string $filename
   * @param string $newname
   */
  public function GetRandUrl($filename)
    {
    if(!isset($this->m_list_urls[$filename])) $this->Init($filename);
    //--- получаем случайный урл
    if(isset($this->m_list_urls[$filename]) && is_array($this->m_list_urls[$filename]))
      {
      $temp_name = $this->m_list_urls[$filename][array_rand($this->m_list_urls[$filename])];
      }
    else return '';
    //---
    return $temp_name;
    }

  /**
   * Проверка наличие данных о картинках
   * @return bool
   */
  public function HaveImagesUrl()
    {
    return !empty($this->m_list_names);
    }

  /**
   * Возвращаем пхп массив для записи в файл
   */
  public function GetPHParray()
    {
    if(empty($this->m_list_names)) return '';
    //---
    $ar = '$images=array(';
    foreach($this->m_list_names as $name => $url)
      {
      $ar .= "'" . $name . "'=>'" . $url . "',";
      $ar .= "\r\n";
      }
    //---
    $ar .= ');' . "\r\n";
    return $ar;
    }

  /**
   * Создание файл img.php
   * @param string $path
   */
  public function CreateImgPhpFile($path)
    {
    if(!file_exists(self::PATH_IMG_PHP))
      {
      CLogger::write(CLoggerType::ERROR, 'img.php not found: ' . self::PATH_IMG_PHP);
      return;
      }
    //---
    $content = file_get_contents(self::PATH_IMG_PHP);
    $php     = $this->GetPHParray();
    $content = str_replace('[IMAGES]', $php, $content);
    //---
    $path     = rtrim($path, '/\\');
    $filename = $path . '/img.php';
    //---
    file_put_contents($filename, $content);
    //---
    CLogger::write(CLoggerType::ERROR, 'img.php created: ' . $filename);
    }
  }

?>