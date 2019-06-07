<?php

/**
 * Class Hatimeria_OrderManager_ShippingController
 */
class Hatimeria_OrderManager_ShippingController extends Mage_Core_Controller_Front_Action
{
    /**
     * action to show popup window in catalog-product-view
     */
    public function detailsAction()
    {
        $this->loadLayout();
        $this->renderLayout();
    }
} 