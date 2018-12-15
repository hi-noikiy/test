<?php

class Ktpl_Wholesaler_Block_Inquery extends Mage_Core_Block_Template {

    protected function _construct() {
        parent::_construct();
        $this->setTemplate('wholesaler/inqueryform.phtml');
    }
    
    public function getPostActionUrl(){
        $params = $this->getRequest()->getParams();
        return $this->getUrl('wholesaler/index/inqueryPost', $params);
    }
    
}
