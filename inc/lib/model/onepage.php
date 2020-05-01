<?
class CModel_onepage
  {
  /**
   * Парамтеры
   * @var array
   */
  private $m_params;
  /**
   * Парамтеры
   * @var CModel_keywords
   */
  private $m_model_keywords;
  /**
   * папка куда сохранятеся дорвей
   * @var string
   */
  private $localPath;

  /**
   * @param array $params
   * @param CModel_keywords $model_keywords
   * @param string $localPath
   */
  public function __construct(&$params, &$model_keywords, &$localPath)
    {
    $this->m_params         = $params;
    $this->m_model_keywords = $model_keywords;
    $this->localPath        = $localPath;
    }

  /**
   * Получение папки
   * @param $i
   * @return string
   */
  public function GetPath($i)
    {
    $cur_path = $this->localPath . '/' . ltrim($this->m_params['pathOnePage'], './\\');
    //---
    $key_info = $this->m_model_keywords->GetKeywordByNum($i);
    $key = CModel_tools::Translit($key_info->getKeywordIndex(0));
    if(isset($this->m_params['onepage_oneword']) && $this->m_params['onepage_oneword'] == 'on') $key = str_replace('-', '', $key);
    //--- если папка больше
    if(strlen($key) > $this->m_params['onepage_dir_len']) return null;
    //---
    $cur_path = str_replace('[KEYWORD]', $key, $cur_path);
    $cur_path = ltrim($cur_path, './\\');

    //---
    return $cur_path;
    }
  }

?>