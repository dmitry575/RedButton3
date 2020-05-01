<?php
/**
 *
 * Основной интерфейс для страниц
 *
 */
class IPage
{
    private $m_template = 'index';
    private $m_title = '';

    /**
     *
     * Отображение старницы
     * @param unknown_type $path
     */
    public function Show($path = null)
    {
        if (is_null($path)) $path = array('index');
        //----
        $page = $this->GetPageObj($path);
        //----
        $page->Show($url);
    }

    /**
     *
     * Выполнение каких-либо запросов
     * @param unknown_type $url
     * @param unknown_type $action
     */

    public function Action($url, $action)
    {
        $page = $this->GetPageObj($url);
        //----
        $method_name = 'on' . $action;
        //----
        if (!empty($action) && method_exists($page, $method_name)) $page->$method_name($url);
    }

    /**
     *
     * Получение имя страницы
     * @param unknown_type $url
     */
    public function GetPageObj($url)
    {
        $path = reset($url);
        //----
        if ($path != '') $path = '_' . $path;
        //----
        $className = get_class($this) . $path;
        return (new $className());
    }

    public function GetTemplate()
    {
        return $this->m_template;
    }

    /**
     * Заголовок страницы
     *
     */
    public function GetTitle()
    {
        return $this->m_title;
    }

    /**
     *
     * Шаблон страницы
     * @param unknown_type $template
     */
    public function SetTemplate($template)
    {
        $this->m_template = $template;
    }

    /**
     * Тайтл страницы
     * @param string $title
     */
    public function SetTitle($title)
    {
        $this->m_title = $title;
    }

}

?>