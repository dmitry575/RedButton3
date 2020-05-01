<?php
//+------------------------------------------------------------------+
//|                  Copyright c 2012, RedButton Web Sites Generator |
//|                                      http://www.getredbutton.com |
//+------------------------------------------------------------------+

class CModel_clib
{
    /**
     *
     * Удаление файлов из папки
     * @param string $path папка на удаление
     */
    public function rfr($path)
    {
        $images = glob($path . "/images/*");

        // delete images
        if (sizeof($images == 0)) {
            foreach ($images as $imgfile) {
                if (is_file($imgfile))
                    unlink($imgfile);
            }
        }
        // delete dir
        @rmdir($path . '/images');
        // delete base.txt
        @unlink($path . '/base.txt');
        // delete project dir
        @rmdir($path);
        return true;
    }

    // zip directory with all contains
    public function ZipMe($dir, $zipname)
    {
        require "zip.class.php";
        $zipfile = new zipfile;
        //---
        $dir = trim($dir, '/ ');
        $maindir = glob($dir . '/*', GLOB_ONLYDIR);
        foreach ($maindir as $fdir) {
            $fdir = str_replace($dir . '/', '', $fdir);
            $zipfile->create_dir($fdir);
            // look for files
            $fullpath = $dir . '/' . $fdir . '/';
            $files = glob($fullpath . '*');
            if ($files) {
                foreach ($files as $ffile) {
                    if (is_file($ffile) && strpos($ffile, '.zip') === FALSE)
                        $zipfile->create_file(file_get_contents($ffile), $fdir . '/' . str_replace($fullpath, '', $ffile));
                }
            }
        }
        // look for files
        $files = glob($dir . '/*');
        foreach ($files as $ffile) {
            if (is_file($ffile) && strpos($ffile, '.zip') === FALSE)
                $zipfile->create_file(file_get_contents($ffile), str_replace($dir . '/', '', $ffile));
        }
        file_put_contents($zipname, $zipfile->zipped_file());
        return true;
    }


    //---
    public function GetName()
    {
        $names = Array('Саша', 'Маша', 'Мария', 'Петр', 'Иван', 'Олег', 'Женя', 'Сергей', 'Сережа', 'Вася', 'Марат', 'Коля', 'Николай', 'Света', 'Лена', 'Елена', 'Владимир', 'Мария', 'Оля', 'Ольга', 'Татьяна');
        $sz = sizeof($names) - 1;
        return $names[rand(0, $sz)];
    }

    //---


    //---


    //--- Отразить картинку по горизонтали
    public function reflectImage($filename, $newfilename = NULL)
    {
        if (empty($filename) || !file_exists($filename) || pathinfo($filename, PATHINFO_EXTENSION) != 'jpg')
            return false;
        //---
        if (empty($newfilename))
            $newfilename = $filename;
        //---
        $source = imagecreatefromjpeg($filename);
        $wid = imagesx($source);
        $hei = imagesy($source);
        $newimg = imagecreatetruecolor($wid, $hei);
        //---
        for ($i = 0; $i < $wid; $i++) {
            for ($j = 0; $j < $hei; $j++) {
                $ref = imagecolorat($source, $i, $j);
                imagesetpixel($newimg, $wid - $i, $j, $ref);
            }
        }
        //---
        imagejpeg($newimg, $newfilename, 80);
        imagedestroy($source);
        imagedestroy($newimg);
        unset($source);
        unset($newimg);
    }

}

?>