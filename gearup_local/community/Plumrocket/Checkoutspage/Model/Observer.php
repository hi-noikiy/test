<?php
/**
 * Plumrocket Inc.
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the End-user License Agreement
 * that is available through the world-wide-web at this URL:
 * http://wiki.plumrocket.net/wiki/EULA
 * If you are unable to obtain it through the world-wide-web, please
 * send an email to support@plumrocket.com so we can send you a copy immediately.
 *
 * @package     Plumrocket_Checkoutspage
 * @copyright   Copyright (c) 2015 Plumrocket Inc. (http://www.plumrocket.com)
 * @license     http://wiki.plumrocket.net/wiki/EULA  End-user License Agreement
 */


class Plumrocket_Checkoutspage_Model_Observer extends Mage_Core_Model_Abstract
{

    //fix only for plumrocket reward points
    public function setTemplate($observer)
    {

        if (!Mage::getConfig()->getModuleConfig('Plumrocket_Rewards') || !Mage::helper('checkoutspage')->moduleEnabled()) {
            return;
        }

        $controller = $observer->getAction();

        if ($controller->getFullActionName() == 'checkout_onepage_success') {

            $layout = $controller->getLayout();
            $block = $layout->getBlock('checkout.success');

            if ($block) {
                $block->setTemplate('checkoutspage/page.phtml');
            }
        }
    }

    public function setNextOrderCoupon($observer)
    {
        $nextOrderCouponModel = Mage::getModel('eav/entity_attribute')->loadByCode(Mage_Catalog_Model_Product::ENTITY, 'pr_next_order_coupon');
        $isAppliedToPr = false !== strpos($nextOrderCouponModel->getApplyTo(), 'prproducts');
        $checkoutspageGroupsConfig = Mage::app()->getRequest()->getParam('groups');
        $enableExtension = (bool)$checkoutspageGroupsConfig['general']['fields']['enabled']['value'];


        if ($enableExtension) {
            if ($isAppliedToPr)
                $nextOrderCouponModel->setApplyTo(null)->save();
        } else {
            if (! $isAppliedToPr)
                $nextOrderCouponModel->setApplyTo('prproducts')->save();
        }
    }
}
