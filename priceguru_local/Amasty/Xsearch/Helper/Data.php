<?php
/**
* @author Amasty Team
* @copyright Copyright (c) 2010-2013 Amasty (http://www.amasty.com)
* @package Amasty_Xsearch
*/
class Amasty_Xsearch_Helper_Data extends Mage_Core_Helper_Abstract
{
 
    function substr($val, $max = 100){
        $ret = $val;
        
        if (strlen($val) > $max){
            $ret = substr($val, 0, $max).' ...';
        }
        
        return $ret;
    }
}