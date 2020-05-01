<?
class IController
{
    public function Show($path = null)
    {
        if (is_null($path)) $path = array('index');
        //----
        $page = $this->GetPageObj($path);
        //----
        $page->Show($url);
    }

    public function Action($url, $action)
    {
        $page = $this->GetPageObj($url);
        //----
        $method_name = 'on' . $action;
        //----
        if (!empty($action) && method_exists($page, $method_name)) $page->$method_name($url);
    }

    public function GetPageObj($url)
    {
        $path = reset($url);
        //----
        if ($path != '') $path = '_' . $path;
        //----
        $className = get_class($this) . $path;
        return (new $className());
    }
}

?>