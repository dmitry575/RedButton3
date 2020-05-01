<?php
class CTemplates extends IPage
  {
  //--- название шаблона
  private $m_template;
  /**
   * текущая страница из шаблона
   * @var string
   */
  private $m_page;
  //--- переводы
  protected static $m_translate = array('ru' => array('main_title'              => 'Редактор шаблонов',
                                                      'error_action'            => 'Ошибка обработки команды.',
                                                      'error_empty_tmpl'        => 'Название шаблона пустое',
                                                      'b_keywords'              => 'Ключевые слова',
                                                      'b_menu'                  => 'Меню',
                                                      'b_links'                 => 'Ссылки и гиперссылки',
                                                      'b_urls'                  => 'Urls',
                                                      'b_text'                  => 'Текст',
                                                      'b_images'                => 'Изображения',
                                                      'b_other'                 => 'Разное',
                                                      'b_urls_spam'             => 'Для спама',
                                                      'b_save'                  => 'Сохранить',
                                                      'b_template_save_success' => 'Шаблон сохранен успешно',
                                                      'b_template_save_error'   => 'Шаблон не сохранен',
                                                      'b_view_template'         => 'Просмотр шаблона',),
    //---
                                        'en' => array('main_title'              => 'Templates Editor',
                                                      'error_action'            => 'Unknown this command',
                                                      'error_empty_tmpl'        => 'Name template is empty',
                                                      'b_keywords'              => 'Keywords',
                                                      'b_menu'                  => 'Menu',
                                                      'b_links'                 => 'Links',
                                                      'b_urls'                  => 'Urls',
                                                      'b_text'                  => 'Text',
                                                      'b_images'                => 'Images',
                                                      'b_other'                 => 'Other',
                                                      'b_save'                  => 'Save',
                                                      'b_urls_spam'             => 'Urls for spam',
                                                      'b_template_save_success' => 'Template saved',
                                                      'b_template_save_error'   => 'Template not save',
                                                      'b_delete_tasks'          => 'delete',
                                                      'b_repeate_tasks'         => 'repeate',
                                                      'b_view_template'         => 'View template',));
  //---
  private $m_model;

  /**
   * Конструктор
   */
  public function __construct()
    {
    $this->SetTitle(CTemplates::GetTranslate('main_title'));
    $this->m_model = new CModel_template('','');
    //---
    $this->m_template = $this->GetTemplateName();
    $this->m_page     = $this->GetPageName();
    }

  /**
   *
   * Получение шаблона либо из формы, либо по умолчанию
   */
  private function GetTemplateName()
    {
    if(!empty($_REQUEST['template'])) return $_REQUEST['template'];
    if(!empty($_SESSION['last_template_edit'])) return $_SESSION['last_template_edit'];
    //---
    return CModel_helper::FirstFromDirs(CModel_template::TEMPLATE_PATH);
    }

  /**
   *
   * Получение шаблона либо из формы, либо по умолчанию
   */
  private function GetPageName()
    {
    if(!empty($_REQUEST['page'])) return $this->GetPageRequest();
    if(!empty($_SESSION['last_page_edit'])) return $_SESSION['last_page_edit'];
    //---
    return 'page.html';
    }

  /**
   * Отображение доверя
   * @see IPage::Show()
   */
  public function Show($path = null)
    {
    //---
    $text = $this->m_model->GetTextTemplate($this->m_template, $this->m_page);
    if($text==null) $text = '';
    include("./inc/pages/templates/index.phtml");
    //--- зачистка сессий
    if(isset($_SESSION['templates_save'])) unset($_SESSION['templates_save']);
    if(isset($_SESSION['last_page_edit'])) unset($_SESSION['last_page_edit']);
    if(isset($_SESSION['last_template_edit'])) unset($_SESSION['last_template_edit']);
    }

  /**
   *
   * Текущая модель
   */
  public function GetModel()
    {
    return $this->m_model;
    }

  /**
   * Обработка запросов
   * @see IPage::Action()
   */
  public function Action($url, $action)
    {
    $method_name = 'on' . $action;
    //---
    if(method_exists($this, $method_name)) $this->$method_name($url);
    else
      {
      echo CTemplates::GetTranslate('error_action');
      C404::Show404();
      }
    }

  /**
   * Отображение текста шаблона
   * @param array $url
   */
  private function OnViewTemplate($url)
    {
    if(empty($_REQUEST['tmpl']))
      {
      echo CTemplates::GetTranslate('error_empty_tmpl');
      C404::Show404();
      }
    $page = $this->GetPageRequest();
    //---
    echo $this->m_model->GetTextTemplate(trim($_REQUEST['tmpl'], '/\\'), $page);
    exit;
    }

  /**
   * Получим название страницы
   * @return string
   */
  private function GetPageRequest()
    {
    if(empty($_REQUEST['page'])) $page = 'page.html';
    else
      {
      $page = $_REQUEST['page'];
      if(in_array($page,$this->m_model->getStopFiles())){}
      else
        {
        $page = 'page.html';
        }
      }
    return $page;
    }

  /**
   *
   * Сохранение задачи
   * @param array $url
   */
  private function OnSaveTemplate($url)
    {
    $page = $this->GetPageRequest();
    //---
    CLogger::write(CLoggerType::DEBUG, "template: saved '" . $_REQUEST['template'] . "', page: " . $page);
    //---
    if($this->m_model->SaveTextTemplate(trim($_REQUEST['template'], '/\\'), $page, stripslashes($_REQUEST['text']))
    ) $_SESSION['templates_save'] = 1;
    else
    $_SESSION['templates_save'] = 0;
    //---
    $_SESSION['last_template_edit'] = $_REQUEST['template'];
    $_SESSION['last_page_edit']     = $_REQUEST['page'];
    header("location: ./?module=templates");
    exit;
    }

  /**
   * Получение перевода
   *
   * @param string $name
   * @return string
   */
  public static function GetTranslate($name)
    {
    global $LNG;
    if(isset(self::$m_translate[$LNG]) && isset(self::$m_translate[$LNG][$name]))
      {
      return self::$m_translate[$LNG][$name];
      }
    //--- если языка нет, то может английский подойдет?
    if($LNG != 'en' && isset(self::$m_translate['en']) && isset(self::$m_translate[$LNG][$name]))
      {
      return self::$m_translate[$LNG][$name];
      }
    //----
    return '';
    }

  /**
   * Получим список для шаблона
   */
  private function ListTemplates()
    {
    $s = '';
    $s .= $this->getOption('page.html');
    $s .= $this->getOption('index.html');
    $s .= $this->getOption('category.html');
    $s .= $this->getOption('sitemap.html');
    return $s;
    }

  /**
   * для каждой странице свой option
   * @param $page
   * @return string
   */
  private function getOption($page)
    {
    return '<option value="' . $page . '"' . ($page == $this->m_page ? ' selected="selected"' : '') . '>' . $page . '</opntion>';
    }

  /**
   * Предварительный просмотр шаблона
   * @param $url
   */
  public function OnShowTemplate($url)
    {
    //--- нужно все href удалить макросы и подставить ?module=template&a[getfile]&template=&file=
    $text = $this->m_model->GetTextTemplate(trim($this->m_template, '/\\'), $this->m_page);
    $this->m_model->ReplaceTemplate($this->m_template, $text);
    //---
      $this->m_model->ReplaceMacrocesViewTemplate();
    echo $text;
    }

  /**
   * Получить файл
   * @param array $url
   */
  public function OnGetFile($url)
    {
    if(empty($_REQUEST['file'])) return;
    $filename = $_REQUEST['file'];
    $s        = $this->m_model->GetTextFileTemplate(trim($this->m_template, '/\\'), $filename, $size);
    //---
    $path = pathinfo($filename);
    //---
    $ext  = $path['extension'];
    $mime = CModel_tools::GetMime($ext);
    //---
    header('Content-Type: ' . $mime);
    header('Content-Disposition: attachment; filename="' . $path['basename'] . '"');
    header('Content-Size: ' . (int)$size);
    header('Cache-Control: public');
    //---
    echo $s;
    //---
    exit;
    }
  }

?>
