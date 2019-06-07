<?php

class Gearup_Review_IndexController extends Mage_Core_Controller_Front_Action {
    
    public function reviewAction() {
        $this->getResponse()->setBody($this->getLayout()
                ->createBlock('detailreview/Productreview')
                ->toHtml());
        
    }

}