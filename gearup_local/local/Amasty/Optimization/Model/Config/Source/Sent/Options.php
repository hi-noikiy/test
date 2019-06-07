<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2019 Amasty (https://www.amasty.com)
 * @package Amasty_Optimization
 */


class Amasty_Optimization_Model_Config_Source_Sent_Options extends Varien_Object
{
    const SENT_URL = 0;
    const SENT_CODE = 1;

    public function toOptionArray()
    {
        $helper = Mage::helper('amoptimization');

        return array(
            array('value' => self::SENT_URL, 'label' => $helper->__('File Url')),
            array('value' => self::SENT_CODE, 'label' => $helper->__('Js Code')),
        );
    }
}
