<?php

/**
 * Contus Support Interactive.
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the EULA
 * that is bundled with this package in the file GCLONE-LICENSE.txt.
 *
 * =================================================================
 *                 MAGENTO EDITION USAGE NOTICE
 * =================================================================
 * This package designed for Magento 1.4.1.1 COMMUNITY edition
 * Contus Support does not guarantee correct work of this package
 * on any other Magento edition except Magento 1.4.1.1 COMMUNITY edition.
 * =================================================================
 */
class Contus_Ordercustomer_Model_Ordercustomer extends Mage_Core_Model_Abstract {

    public function _construct() {
        parent::_construct();
        $this->_init('ordercustomer/ordercustomer');
    }

    public function getCouponData($ocCollection, $orderId, $i=NULL, $type) {

        foreach ($ocCollection as $oc) {
            $_orderId = $oc->getOrderId();
            $_i = $oc->getQuantitynumber();
            if (isset($i)) {
                if ($orderId == $_orderId && $i == $_i) {
                    $resultSet = $oc->getUniqueid();
                }
            } else {
                if ($orderId == $_orderId) {
                    if ($type == 'all') {
                        $resultSet[] = $oc;
                    } else {
                        $resultSet = $oc->getUniqueid();
                    }
                }
            }
        }
        return $resultSet;
    }
    
    public function getSelectCountSql()
    {
        if ($this->getIsCustomerMode()) {
            $this->_renderFilters();
     
            $unionSelect = clone $this->getSelect();
     
            $unionSelect->reset(Zend_Db_Select::ORDER);
            $unionSelect->reset(Zend_Db_Select::LIMIT_COUNT);
            $unionSelect->reset(Zend_Db_Select::LIMIT_OFFSET);
     
            $countSelect = clone $this->getSelect();
            $countSelect->reset();
            $countSelect->from(array('a' => $unionSelect), 'COUNT(*)');
        } else {
            $countSelect = parent::getSelectCountSql();
        }
     
        return $countSelect;
    }
}