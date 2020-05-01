<?php
function isBot(&$botname='')
{
   global $BOTS_IP;
   $bots=array('rambler', 'google', 'aport', 'yahoo', 'msnbot', 'turtle', 'mail.ru', 'omsktele', 'yetibot', 'picsearch', 'sape.bot', 'sape_context', 'gigabot', 'snapbot', 'alexa.com', 'megadownload.net', 'askpeter.info', 'igde.ru', 'ask.com', 'qwartabot', 'yanga.co.uk', 'scoutjet', 'similarpages', 'oozbot', 'shrinktheweb.com', 'aboutusbot', 'followsite.com', 'dataparksearch', 'google-sitemaps', 'appEngine-google', 'feedfetcher-google', 'liveinternet.ru', 'xml-sitemaps.com', 'agama', 'metadatalabs.com', 'h1.hrn.ru', 'googlealert.com', 'seo-rus.com', 'yaDirectBot', 'yandeG', 'yandex', 'yandexSomething', 'Copyscape.com', 'AdsBot-Google', 'domaintools.com', 'Nigma.ru', 'bing.com', 'dotnetdotcom');
   foreach($bots as $bot)
      {
      if(stripos($_SERVER['HTTP_USER_AGENT'], $bot) !== false)
         {
         $botname=$bot;
         return true;
         }
      }
   if(!empty($BOTS_IP))
      {
      $ip=ip2long($_SERVER['REMOTE_ADDR']);
      foreach($BOTS_IP as $denied_ip)
         {
         if(($denied_ip[0]<=$ip) && ($ip<=$denied_ip[1])) return true;
         }
      }
   return false;
}

/**
 *
 * info from file
 * @param int $id
 */
function GetInfo($id)
{
   $id=(int)$id;
   $file_number=(int)($id / MAX_BLOCK_IN_FILE);
   //---
   $filename='./data/' . $file_number . '.dat.php';
   $numberInFile=(int)(fmod($id, MAX_BLOCK_IN_FILE));
   //---
   if(!file_exists($filename)) return array();
   //---
   $fhandle=fopen($filename, "r");
   if(!$fhandle) return array();
   //--- get info about file
   $file_info=fstat($fhandle);
   //--- get file size
   $filesize=$file_info['size'];
   //--- read header of file
   $header=DynamicReadHeaderFile($fhandle);
   if(empty($header))
      {
      fclose($fhandle);
      return array();
      }
   //---
   if(!isset($header[$numberInFile]))
      {
      fclose($fhandle);
      return array();
      }
   //---
   if($header[$numberInFile][0]>$filesize || ($header[$numberInFile][0] + $header[$numberInFile][1])>$filesize)
      {
      fclose($fhandle);
      return array();
      }
   //--- read data
   fseek($fhandle, $header[$numberInFile][0]);
   //--- check length data
   if($header[$numberInFile][1]<=0)
      {
      fclose($fhandle);
      return array();
      }
   $data=fread($fhandle, $header[$numberInFile][1]);
   //---
   fclose($fhandle);
   if(empty($data)) return array();
   //---
   return unserialize($data);
}

//---
function DynamicUnpackHeader($data)
{
   if(empty($data)) return array();
   //---
   $len=strlen($data);
   //---
   $result=array();
   //---
   for($i=0; $i<MAX_BLOCK_IN_FILE; $i++)
      {
      if($len>=(($i + 1) * 8))
         {
         $r=unpack("Inum/Lcount", substr($data, $i * 8, 8));
         if(empty($r)) $result[$i]=array(0, 0);
         else          $result[$i]=array($r['num'], $r['count']);
         }
      else
         {
         $result[$i]=array(0, 0);
         }
      }
   return $result;
}

//---
function DynamicReadHeaderFile($fhandle)
{
   fseek($fhandle, 0);
   $data=fread($fhandle, 8 * MAX_BLOCK_IN_FILE);
   return DynamicUnpackHeader($data);
}

?>