<?
class C404 extends IPage
{
   public $template='404';
   public $title='404';
   //---
   public function __construct()
   {
      header("HTTP/1.1 404 Not Found");
   }
   public function Show($path=null)
   {
return;
   }
   /**
    * Покажем 404 ошибку
    */
   static public function Show404($path=null)
   {
      header("HTTP/1.1 404 Not Found");
      //---
      include("./inc/views/404.phtml");
      //---
      exit;
   }
}

?>