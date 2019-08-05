<?php

namespace Ktpl\General\Plugin;
use \Magento\Store\Model\StoreManagerInterface; 
class AbstractHelpPlugin
{

    public function beforeGetConvertedWeight(\CollinsHarper\CanadaPost\Helper\AbstractHelp $subject,$value, $from = false, $to = \Zend_Measure_Weight::KILOGRAM)
    {
        $value=(float)$value;
        return array($value, $from,$to);
    }    

}