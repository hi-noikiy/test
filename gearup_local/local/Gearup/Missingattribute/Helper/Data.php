<?php
class Gearup_Missingattribute_Helper_Data extends Mage_Core_Helper_Abstract
{
    public function removefile($path) {
        $files = array();
        if ($handle = opendir($path)) {
            while (false !== ($file = readdir($handle))) {
                if ($file != "." && $file != "..") {
                   $files[filemtime($path.$file)] = $file;
                }
            }
            closedir($handle);
            krsort($files);
            $i = 0;
            foreach($files as $file) {
                if(++$i <= 30){ continue;}
                unlink($path.$file);
            }
        }
    }
}
	 