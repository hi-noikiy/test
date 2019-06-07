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


class Plumrocket_Checkoutspage_Block_System_Config_Preview  extends Mage_Adminhtml_Block_Template
{
    public function getStoreId()
    {
        if (!$this->hasData('store_id')) {
            $request = Mage::app()->getRequest();
            if ($storeName = $request->getParam('store')) {
                if($store = Mage::app()->getStore($storeName)) {
                    $scope = array('stores', $store->getId());
                }
            } elseif ($websiteName = $request->getParam('website')) {
                if($website = Mage::app()->getWebsite($websiteName)) {
                    $scope = array('websites', $website->getId());
                }
            }

            if (empty($scope)) {
                $scope = array('default', 0);
            }

            list($scope, $scopeId) = $scope;

            if ($scope == 'stores') {
                if ($store = Mage::app()->getStore($scopeId)) {
                    $storeId = $store->getId();
                }
            } elseif ($scope == 'websites') {
                if ($website = Mage::app()->getWebsite($scopeId)) {
                    $storeId = $website->getDefaultGroup()->getDefaultStoreId();
                }
            }

            if (empty($storeId)) {
                $storeId = Mage::app()
                    ->getWebsite(true)
                    ->getDefaultGroup()
                    ->getDefaultStoreId();
            }

            $this->setData('store_id', $storeId);
        }
        return $this->getData('store_id');
    }


    public function getDefaultOrder()
    {
        if (!$this->hasData('default_order')) {
            $defaultOrder = Mage::getSingleton('sales/order')->getCollection()
                ->addFieldToFilter('store_id', $this->getStoreId())
                ->setOrder('created_at', 'DESC')
                ->setPageSize(1)
                ->getFirstItem();

            $this->setData('default_order', $defaultOrder);
        }
        return $this->getData('default_order');
    }

}
