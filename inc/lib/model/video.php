<?
class CModel_video
  {
  /**
   * Основной путь к картинкам
   */
  const PATH_VIDEO = 'data/video';
  /**

  для вставки
   */
  const YOUTUBE_OBJECT = '<iframe width="{width}" height="{height}" src="{url}" frameborder="0" allowfullscreen></iframe>';
  /**
   * список название файлов и урлов
   * @var array
   */
  private $m_list_names;

  /**
   * Конструктор
   */
  public function __constructor()
    {
    //--- зачистим название
    $this->m_list_names = array();
    }

  /**
   * Берем случайное видое из указанного файла и подставляем размеры
   * @param $filename
   * @param $width
   * @param $height
   *
   * @internal param array $matches
   * @return string
   */
  public function GetRandVideoSize($filename, $width, $height)
    {
    if(empty($this->m_list_names[$filename])) $this->Load($filename);
    $url = $this->GetRandUrl($filename);
    if(!CModel_helper::IsExistHttp($url)) $url = 'http://' . $url;
      //---
    return str_replace(array('{width}', '{height}', '{url}'), array($width, $height, $url), self::YOUTUBE_OBJECT);
    }

  /**
   * Берем случайное видое из указанного файла и подставляем размеры
   * @param array $matches
   *
   * @return string
   */
  public function GetRandVideoUrl($matches)
    {
    $filename = $matches[1];
    //---
    $url = $this->GetRandUrl($filename);
    if(!CModel_helper::IsExistHttp($url)) $url = 'http://' . $url;
    return $url;
    }

  /**
   * Загрузка в кеш всех ссылки с видео
   * @param $filename
   *
   * @return mixed
   */
  private function Load($filename)
    {
    $fullname = self::PATH_VIDEO . '/' . $filename;
    if(!file_exists($fullname)) return;
//---
    $this->m_list_names[$filename] = file($fullname);
    array_walk($this->m_list_names[$filename], create_function('&$val', '$val = !is_array($val) ? trim($val):$val;'));
    }

  /**
   * Получение случайного урла
   * @param $filename
   *
   * @return mixed
   */
  public function GetRandUrl($filename)
    {
    if(empty($this->m_list_names[$filename])) return null;
    return $this->m_list_names[$filename][rand(0, count($this->m_list_names) - 1)];
    }
  }

?>