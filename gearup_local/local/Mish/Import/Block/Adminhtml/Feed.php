<?php

/**
 * Report
 *
 * @package GearUp.me
 */
class Mish_Import_Block_Adminhtml_Feed extends Mage_Adminhtml_Block_Template
{
    protected function _construct() 
    {
        parent::_construct();
        $this->setTemplate('import/feed.phtml');
    }

    public function getFeed()
    {
        $now = new DateTime();
        $filepath = sprintf("%s/var/import/log/feed.log", Mage::getBaseDir());

        return file($filepath);
    }
}