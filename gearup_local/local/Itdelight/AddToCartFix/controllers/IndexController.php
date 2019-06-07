<?php

require_once Mage::getModuleDir('controllers', 'EM_Ajaxcart') . DS . 'IndexController.php';

/**
 * Class Itdelight_AddToCartFix_IndexController
 */
class Itdelight_AddToCartFix_IndexController extends EM_Ajaxcart_IndexController
{
    /**
     *
     */
    public function add2Action()
    {
        $this->getRequest()->setParam('id', 0);
        return parent::add2Action();
    }
}