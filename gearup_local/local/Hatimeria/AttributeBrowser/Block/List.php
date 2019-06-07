<?php

/**
 * List of browserable attributes
 */
class Hatimeria_AttributeBrowser_Block_List extends Mage_Core_Block_Template
{
    protected function _construct()
    {
        parent::_construct();
        $this->addData(array(
            'cache_lifetime' => 12*3600,
            'cache_tags' => array(Mage_Core_Block_Template::CACHE_GROUP)
        ));
    }
    
    /**
     * Items
     * 
     * @return array 
     */
    public function getItems()
    {
        $model = Mage::getModel('attributebrowser/config'); /* @var $model Hatimeria_AttributeBrowser_Model_Config */
        
        return $model->getConfig();
    }
}