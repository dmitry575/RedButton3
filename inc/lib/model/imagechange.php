<?php
//+------------------------------------------------------------------+
//|                  Copyright c 2012, RedButton Web Sites Generator |
//|                                      http://www.getredbutton.com |
//+------------------------------------------------------------------+
/**
 * Класс для обработки картинок
 * Ресайз картинки: случайная ширина от [   100] px до [   200] px
Случайным образом немного обрезать по краям
Перевернуть картинку слева-направо
Накладывать текст/кейворд на картинку с прозрачностью [ 20] %, цвет текста в формате HEX: [ #FFFFFF]

 */
class CModel_ImageChange
  {
  /**
   * Изменять размеры картинки случайным образом
   */
  const RESIZE_RANDOM = 1;
  /**
   * Изменение картинки с соблюдением пропорции
   */
  const RESIZE_PROPORTION = 2;
  /**
   * Делать обрезания по краям
   */
  const CROP = 4;
  /**
   * Переворачивать картинку вокруг своей оси
   */
  const INVERT = 8;
  /**
   * Нужен ли водяной знак
   */
  const WATERMARK = 16;
  /**
   * зеркальное отражение
   */
  const MIRROR    = 32;
  /**
   * Негатив
   */
  const NEGATIF   = 64;
  /**
   * контуры
   */
  const EMBOSS    = 128;
  /**
   * в серый цвет
   */
  const GRAYSCALE = 256;
  /**
   * Массив с случайными элементами
   */
  private $m_random_width = array(100,
                                  200);
  private $m_random_crop = array(1,
                                 10);
  /**
   * водяной знак
   * @var string
   */
  private $m_watermark = '';
  /**
   * путь к шрифтам
   * @var string
   */
  private $m_path_to_fonts;

  /**
   *
   * Конструктор
   * @param array $random_width случайные размеры
   * @param array $random_crop насколько обрезать края
   * @param string $path_to_fonts путь к шрифтам
   */
  public function __construct($random_width = array(100,
                                                    200), $random_crop = array('1',
                                                                               '10'), $path_to_fonts = './data/fonts/')
    {
    $this->SetNewParams($random_width, $random_crop, $path_to_fonts);
    }

  /**
   *
   * Установка новых параметров
   * @param array $random_width
   * @param array $random_crop
   * @param string $path_to_fonts
   */
  public function SetNewParams($random_width = null, $random_crop = null, $path_to_fonts = null)
    {
    if(!empty($random_width)) $this->m_random_width = $random_width;
    if(!empty($random_crop)) $this->m_random_crop = $random_crop;
    if(!empty($path_to_fonts)) $this->m_path_to_fonts = $path_to_fonts;
    }

  /**
   *
   * Работаем с картинкой
   * @param string $src_file исходные файл
   * @param string $dst_file получаемый файл, куда скопировать
   * @param int $options опции, что делать с картинкой
   */
  public function ChangeImage($src_file, $dst_file, $options)
    {
    CLogger::write(CLoggerType::DEBUG, "image: " . $src_file);
    if($options == 0)
      {
      //--- копировать файл
      if(!copy($src_file, $dst_file))
        {
        CLogger::write(CLoggerType::DEBUG, "image: copy from " . $src_file . ' to ' . $dst_file);
        return true;
        }
      }
    //--- а существует ли картинка
    if(!file_exists($src_file))
      {
      CLogger::write(CLoggerType::DEBUG, "image: not found file " . $src_file);
      //--- картинки нет
      return true;
      }
    //--- объект картинки
    $src = imagecreatefromstring(file_get_contents($src_file));
    if(!$src)
      {
      CLogger::write(CLoggerType::DEBUG, "image: not open file " . $src_file);
      //--- картинки нет
      return true;
      }
    list($width, $height, $img_type, $attr) = getimagesize($src_file);
    CLogger::write(CLoggerType::DEBUG, "image: " . $src_file . ' width: ' . $width . ', height: ' . $height . ', options: ' . $options);
    //--- нужно ли изменять размер
    if($options & CModel_ImageChange::RESIZE_RANDOM || $options & CModel_ImageChange::RESIZE_PROPORTION)
      {
      $sizes = $this->GetNewSizeImage($src, $options);
      CLogger::write(CLoggerType::DEBUG, "image: " . $src_file . ' new width: ' . $sizes['width'] . ', new height: ' . $sizes['height']);
      //---
      $src = $this->ImageResize($src, $this->getWidth($src), $this->getHeight($src), $sizes['width'], $sizes['height']);
      CLogger::write(CLoggerType::DEBUG, "image: resize " . $src_file . ' to ' . $sizes['width'] . ' x ' . $sizes['height']);
      }
    //--- нужно ли обрезать края
    if($options & CModel_ImageChange::CROP)
      {
      $sizes = $this->GetCropSizeImage();
      $src   = $this->ImageCrop($src, $this->getWidth($src), $this->getHeight($src), $sizes['width'], $sizes['height']);
      CLogger::write(CLoggerType::DEBUG, "image: crop " . $src_file . ' to ' . $sizes['width'] . ' x ' . $sizes['height']);
      }
    //--- нужно переворачивать картинку
    if($options & CModel_ImageChange::INVERT)
      {
      $src = $this->ImageInvert($src);
      CLogger::write(CLoggerType::DEBUG, "image: invert " . $src_file);
      }
    //--- нужно зеркалирование
    if($options & CModel_ImageChange::MIRROR)
      {
      $src = $this->ImageMirror($src);
      CLogger::write(CLoggerType::DEBUG, "image: mirror " . $src_file);
      }
    //--- нужно негатиф
    if($options & CModel_ImageChange::NEGATIF)
      {
      $src = $this->ImageNegatif($src);
      CLogger::write(CLoggerType::DEBUG, "image: negatif " . $src_file);
      }
    //--- нужно рельеф
    if($options & CModel_ImageChange::EMBOSS)
      {
      $src = $this->ImageEmboss($src);
      CLogger::write(CLoggerType::DEBUG, "image: emboss " . $src_file);
      }
    //--- нужно серым
    if($options & CModel_ImageChange::GRAYSCALE)
      {
      $src = $this->ImageGray($src);
      CLogger::write(CLoggerType::DEBUG, "image: gray " . $src_file);
      }
    //--- нужно писать водный знак
    if($options & CModel_ImageChange::WATERMARK)
      {
      $src = $this->ImageWatermark($src, $this->m_watermark);
      CLogger::write(CLoggerType::DEBUG, "image: watermark " . $src_file . ' set: ' . $this->m_watermark);
      }
    $this->SaveToFile($src, $img_type, $dst_file);
    CLogger::write(CLoggerType::DEBUG, "image: " . $src_file . ' saved to ' . $dst_file);
    return true;
    }

  /**
   * В зависимости от параметро делаем размеры картинки
   *
   * @param obj $src
   * @param int $options
   */
  private function GetNewSizeImage($src, $options)
    {
    $width  = $this->getWidth($src);
    $height = $this->getHeight($src);
    //---
    $new_width  = $width;
    $new_height = $height;
    //--- если случайный размер, то нужно сделать ширину случайную
    if($options & CModel_ImageChange::RESIZE_RANDOM)
      {
      //--- получаем случайную ширину
      $new_width = rand($this->m_random_width[0] < $width ? $this->m_random_width[0] : $width, $this->m_random_width[1] < $width ? $this->m_random_width[1] : $width);
      }
    //--- если нужно соблюдать пропорции, то new_height уменьшим пропорционально
    if($options & CModel_ImageChange::RESIZE_PROPORTION)
      {
      //--- коффециенты
      $k_width = $width / $new_width;
      if($k_width != 0) $new_height = $height / $k_width;
      }
    else
      {
      //--- высоту тоже сделае случайной
      if($options & CModel_ImageChange::RESIZE_RANDOM)
        {
        $new_height = rand($this->m_random_width[0] < $height ? $this->m_random_width[0] : $height, $this->m_random_width[1] < $height ? $this->m_random_width[1] : $height);
        }
      }
    //--- возвращаем полученную ширину и высоту
    return array('width'  => $new_width,
                 'height' => $new_height);
    }

  /**
   * На сколько нужно обрезать картинку
   *
   * @param obj $src
   */
  private function GetCropSizeImage()
    {
    //--- возвращаем на сколько обрезать ширину и высоту
    return array('width'  => rand($this->m_random_crop[0], $this->m_random_crop[1]),
                 'height' => rand($this->m_random_crop[0], $this->m_random_crop[1]));
    }

  /**
   *
   * Получение ширины картинки
   * @param obj $src
   */
  private function getWidth($src)
    {
    return imagesx($src);
    }

  /**
   * Получение высоты картинки
   * @param obj $src
   */
  private function getHeight($src)
    {
    return imagesy($src);
    }

  /**
   * Установка водяного знака
   */
  public function setWatermark($watermark)
    {
    $this->m_watermark = $watermark;
    }

  /**
   *
   * Установка водянного знака на картинку
   * @param handel $src картинка
   * @param string $text текст, который будет нанесен на картинку
   * @param string $font название шрифта, этот шрифт должен быть загружен
   * @param int $r цвет водяного знака
   * @param int $g цвет водяного знака
   * @param int $b цвет водяного знака
   * @param int $alpha_level уровень прозрачности
   * @return handel
   */
  private function ImageWatermark($src, $text, $font = 'tahoma.ttf', $r = 128, $g = 128, $b = 128, $alpha_level = 70)
    {
    if(empty($text)) return $src;
    //---
    $font = $this->m_path_to_fonts . $font;
    if(!file_exists($font))
      {
      CLogger::write(CLoggerType::ERROR, 'font not found ' . $font);
      return $src;
      }
    $width  = $this->getWidth($src);
    $height = $this->getHeight($src);
    //--- если нужно перевернуть надпись
    //$angle =  -rad2deg(atan2((-$height),($width)));
    $angle = 0;
    //---
    $c         = imagecolorallocatealpha($src, $r, $g, $b, $alpha_level);
    $c_shadow1 = imagecolorallocatealpha($src, 0, 0, 0, 85);
    //--- с тенью
    $size = (($width + $height) / 2) * 2 / strlen($text) / 1.2;
    $box  = imagettfbbox($size, $angle, $font, $text);
    $x    = $width / 2 - abs($box[4] - $box[0]) / 2;
    $y    = $height / 2 + abs($box[5] - $box[1]) / 2;
    imagettftext($src, $size, $angle, $x + 1, $y + 1, $c_shadow1, $font, $text);
    imagettftext($src, $size, $angle, $x, $y, $c, $font, $text);
    //---
    return $src;
    }

  /**
   * Картинку переворачиваем относительно оси y
   * @param obj $src
   */
  private function ImageInvert($src)
    {
    $width  = $this->getWidth($src);
    $height = $this->getHeight($src);
    //---
    $new_image = imagecreatetruecolor($this->getWidth($src), $this->getHeight($src));
    //--- переворачиваем картинку
    for($y = 0; $y < $height; $y++) imagecopy($new_image, $src, 0, $y, 0, $height - $y - 1, $width, 1);
    //---
    return $new_image;
    }

  /**
   * Картинку зераклируем
   * @param obj $src
   */
  private function ImageMirror($src)
    {
    $width  = $this->getWidth($src);
    $height = $this->getHeight($src);
    //---
    $new_image = imagecreatetruecolor($this->getWidth($src), $this->getHeight($src));
    //--- переворачиваем картинку
    for($x = 0; $x < $width; $x++)
      {
      for($y = 0; $y < $height; $y++)
        {
        $color = imagecolorat($src, $x, $y);
        imagesetpixel($new_image, $width - 1 - $x, $y, $color);
        }
      }
    //---
    return $new_image;
    }

  /**
   * Картинку негатиф
   * @param obj $src
   * @return obj
   */
  private function ImageNegatif($src)
    {
    imagefilter($src, IMG_FILTER_NEGATE);
    //---
    return $src;
    }

  /**
   * Картинку негатиф
   * @param obj $src
   */
  private function ImageEmboss($src)
    {
    imagefilter($src, IMG_FILTER_EMBOSS);
    //---
    return $src;
    }

  /**
   * Картинку негатиф
   * @param obj $src
   */
  private function ImageGray($src)
    {
    imagefilter($src, IMG_FILTER_COLORIZE, 100, 70, 50);
    //---
    return $src;
    }

  /**
   * Сохранение картинки в файл
   * @param obj $src
   * @param $img_type
   * @param $filename
   */
  public static function SaveToFile($src, $img_type, $filename)
    {
    //---
    switch($img_type)
    {
      case 1:
      case 6:
        return imagegif($src, $filename);
        break;
      case 3:
        return imagepng($src, $filename);
        break;
      case 2:
      default:
      return imagejpeg($src, $filename, 90);
    }
    }

  /**
   * Фактическое уменьшение картинок и сохранение в новое место с новым именем
   * @param resource $src
   * @param int $width
   * @param int $height
   * @param int $new_width
   * @param int $new_height
   * @return resource
   */
  public static function ImageResize($src, $width, $height, $new_width, $new_height)
    {
    $new_image = imagecreatetruecolor($new_width, $new_height);
    imagecopyresampled($new_image, $src, 0, 0, 0, 0, $new_width, $new_height, $width, $height);
    return $new_image;
    }

  /**
   * Обрезание картинки
   * @param $src
   * @param $width
   * @param $height
   * @param $top
   * @param $left
   * @return resource
   */
  private function ImageCrop($src, $width, $height, $top, $left)
    {
    $new_image = imagecreatetruecolor($width > 2 * $left ? ($width - 2 * $left) : $width, $height > 2 * $top ? ($height - 2 * $top) : $height);
    imagecopyresampled($new_image, $src, 0, 0, $top, $left, $width, $height, $width - $left, $height - $top);
    return $new_image;
    }

  /**
   * Установка размеров для случайного уменьшения картинки
   * @param array $random_width
   */
  public function setRandowWidth($random_width)
    {
    if(is_array($random_width)) $this->m_random_width = $random_width;
    }
  }

?>
