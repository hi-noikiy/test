<?php

class Gearup_Review_Block_Productreview  extends Mage_Core_Block_Template {

    protected function _construct() {
        parent::_construct();
        $this->setTemplate('detailedreview/review.phtml');
    }
    
    public function getPostActionUrl(){
        $params = $this->getRequest()->getParams();
        return $this->getUrl('review/product/post', $params);
    }
}    