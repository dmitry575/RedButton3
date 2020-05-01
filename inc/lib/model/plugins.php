<?php
class CModel_plugins
  {
  const PATH_PLUGINS        = "./inc/plugins/";
  const CONFIG_PLUGINS_NAME = './data/plugins_config.php';
  private $m_plugins_active = null;

  /**
   * Получение списка плагинов
   */
  public function GetList()
    {
    if(!file_exists(self::PATH_PLUGINS))
      {
      mkdir(self::PATH_PLUGINS, 0777, true);
      return null;
      }
    //---
    return CTools_dir::GetDirs(self::PATH_PLUGINS);
    }

  /**
   * Активировать плагин
   * @param string $name
   */
  public function Activate($name)
    {
    global $GLOBAL_ACTIVE_PLUGINS;
    //---
    $GLOBAL_ACTIVE_PLUGINS[$name] = 1;
    //---
    $php_string = '<?php $GLOBAL_ACTIVE_PLUGINS=array(' . $this->GetStringPlugins($GLOBAL_ACTIVE_PLUGINS) . '); ?>';
    //---
    $this->WriteToConfig($php_string);
    }

  /**
   * Записать в файл
   * @param string $str
   */
  private function WriteToConfig($str)
    {
    file_put_contents(self::CONFIG_PLUGINS_NAME, $str);
    }

  /**
   * Удалить плагин
   * @param string $name
   */
  public function DeActivate($name)
    {
    global $GLOBAL_ACTIVE_PLUGINS;
    //---
    if(isset($GLOBAL_ACTIVE_PLUGINS[$name])) unset($GLOBAL_ACTIVE_PLUGINS[$name]);
    //---
    $php_string = '<?php $GLOBAL_ACTIVE_PLUGINS=array(' . $this->GetStringPlugins($GLOBAL_ACTIVE_PLUGINS) . '); ?>';
    //---
    $this->WriteToConfig($php_string);
    }

  /**
   * Получение плагинов в строке
   * @param array $plugins
   */
  private function GetStringPlugins($plugins)
    {
    $str = '';
    foreach($plugins as $plugin => $id)
      {
      $str .= "'" . $plugin . "'=>1,";
      }
    //---
    return $str;
    }

  /**
   * Активируем все
   */
  public function ActivateAll()
    {
    global $GLOBAL_ACTIVE_PLUGINS;
    //---
    if(empty($GLOBAL_ACTIVE_PLUGINS)) return;
    //---
    $this->m_plugins_active = array();
    foreach($GLOBAL_ACTIVE_PLUGINS as $plugin_name => $num_temp)
      {
      $class_name = 'CPlugin_' . $plugin_name;
      if(!class_exists($class_name))
        {
        CLogger::write(CLoggerType::ERROR, 'plugin: try activte ' . $plugin_name . ', but class ' . $class_name . ' not exists');
        continue;
        }
      //---
      $this->m_plugins_active[$plugin_name] = new $class_name();
      }
    }

  /**
   * Вызовем OnBeginMacros событие у всех макросов
   * @param string $keyword
   * @param string $text
   * @param array $data
   */
  public function OnBeginMacros(&$keyword, &$text, &$data)
    {
    if(empty($this->m_plugins_active)) return;
    //--- вызываем для всех макросов
    foreach($this->m_plugins_active as $plugin_obj)
      {
      $plugin_obj->OnBeginMacros($keyword, $text, $data);
      }
    }

  /**
   * Вызовем OnEndMacros событие у всех макросов
   * @param string $keyword
   * @param string $text
   * @param array $data
   */
  public function OnEndMacros(&$keyword, &$text, &$data)
    {
    if(empty($this->m_plugins_active)) return;
    //--- вызываем для всех макросов
    foreach($this->m_plugins_active as $plugin_obj)
      {
      $plugin_obj->OnEndMacros($keyword, $text, $data);
      }
    }

  /**
   * Вызовем OnEndKeyword событие у всех макросов
   * @param string $keyword
   * @param string $text
   * @param array $data
   */
  public function OnEndKeyword(&$keyword, &$text, &$data)
    {
    if(empty($this->m_plugins_active)) return;
    //--- вызываем для всех макросов
    foreach($this->m_plugins_active as $plugin_obj)
      {
      $plugin_obj->OnEndKeyword($keyword, $text, $data);
      }
    }

  /**
   * Вызовем OnBeginKeyword событие у всех макросов
   * @param string $keyword
   * @param string $text
   * @param array $data
   */
  public function OnBeginKeyword(&$keyword, &$text, &$data)
    {
    if(empty($this->m_plugins_active)) return;
    //--- вызываем для всех макросов
    foreach($this->m_plugins_active as $plugin_obj)
      {
      $plugin_obj->OnBeginKeyword($keyword, $text, $data);
      }
    }

  /**
   * Вызовем OnGetPageName событие
   * @param string $keyword Кейворд
   * @param string $pageNum Номер страницы (кейворда)
   */
  public function OnGetPageName(&$keyword, &$pageName, $pageNum)
    {
    if(empty($this->m_plugins_active)) return;
    //--- вызываем для всех плагинов
    foreach($this->m_plugins_active as $plugin_obj)
      {
      $plugin_obj->OnGetPageName($keyword, $pageName, $pageNum);
      }
    }

  /**
   * Вызовем OnSavePage событие
   * @param string $keyword Кейворд
   * @param string $pageNum Номер страницы (кейворда)
   */
  public function OnSavePage(&$keyword, &$pageName)
    {
    if(empty($this->m_plugins_active)) return;
    //--- вызываем для всех плагинов
    foreach($this->m_plugins_active as $plugin_obj)
      {
      $plugin_obj->OnSavePage($keyword, $pageName);
      }
    }

  /**
   * Дорвей сгенерировали
   * @param $localPath
   * @param $params
   */
  public function OnEndGenerate($localPath,$params)
    {
    if(empty($this->m_plugins_active)) return;
    //--- вызываем для всех плагинов
    foreach($this->m_plugins_active as $plugin_obj)
      {
      $plugin_obj->OnEndGenerate($localPath,$params);
      }
    }

  /**
   * Начали генерировать
   * @param $localPath
   * @param $params
   */
  public function OnStartGenerate($localPath,$params)
    {
    if(empty($this->m_plugins_active)) return;
    //--- вызываем для всех плагинов
    foreach($this->m_plugins_active as $plugin_obj)
      {
      $plugin_obj->OnBeginGenerate($localPath,$params);
      }
    }
  /**
   * Обработка текста перед вставкой тегов
   * @param $textArray
   */
  public function OnBeforTextAddTags(&$textArray)
    {
    if(empty($this->m_plugins_active)) return;
    //--- вызываем для всех плагинов
    foreach($this->m_plugins_active as $plugin_obj)
      {
      $plugin_obj->OnBeforTextAddTags($textArray);
      }
    }
  /**
   * Обработка текста после всех обработок
   * @param $textArray
   */
  public function OnEndTextFormated(&$text)
    {
    if(empty($this->m_plugins_active)) return;
    //--- вызываем для всех плагинов
    foreach($this->m_plugins_active as $plugin_obj)
      {
      $plugin_obj->OnEndTextFormated($text);
      }
    }
/**
   * Обработка ключа и получение урла
   * @param $textArray
   */
  public function OnTranslitUrl(&$keyword,&$url)
    {
    if(empty($this->m_plugins_active)) return;
    //--- вызываем для всех плагинов
    foreach($this->m_plugins_active as $plugin_obj)
      {
      $plugin_obj->OnTranslitUrl($keyword,$url);
      }
    }



  }

?>