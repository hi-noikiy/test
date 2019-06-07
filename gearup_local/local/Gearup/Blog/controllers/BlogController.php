<?php

class Gearup_Blog_BlogController extends Mage_Core_Controller_Front_Action {

    public function listAction() {
        $this->loadLayout();
        $this->getLayout()->getBlock('root')->setTemplate('page/empty.phtml');
        $this->renderLayout();
    }

}
