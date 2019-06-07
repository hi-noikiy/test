<?php

/**
 * Report
 *
 * @package GearUp.me
 */
class Mish_Import_Block_Adminhtml_Update extends Mage_Adminhtml_Block_Template
{
    protected function _construct() 
    {
        parent::_construct();
        $this->setTemplate('import/update.phtml');
    }
    
    public function getSummary()
    {
        $now = new DateTime();
        $filepath = sprintf("%s/import/logs/%s.txt", Mage::getBaseDir(), $now->format('Y-m-d'));
        
        return file($filepath);
    }
    
    public function getOutput()
    {
        $filepath = sprintf("%s/var/import/log/last.log", Mage::getBaseDir());
        
        return file($filepath);
    }
}