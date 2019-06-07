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
 * @copyright   Copyright (c) 2017 Plumrocket Inc. (http://www.plumrocket.com)
 * @license     http://wiki.plumrocket.net/wiki/EULA  End-user License Agreement
 */


class Plumrocket_Checkoutspage_Model_System_Config_Nextordercoupon extends Mage_Eav_Model_Entity_Attribute_Source_Abstract
{
    public function getAllOptions()
    {

        if (is_null($this->_options)) {
            $coupons = Mage::getModel('salesrule/rule')->getCollection()
                    ->addFieldToFilter('coupon_type', array('in' => array(Mage_SalesRule_Model_Rule::COUPON_TYPE_SPECIFIC, Mage_SalesRule_Model_Rule::COUPON_TYPE_AUTO)))
                    ->addFieldToFilter('is_active', true);

            $this->_options[] = array(
                'label' => Mage::helper('eav')->__('Disabled'),
                'value' => 0,
            );

            foreach($coupons as $coupon) {
                $this->_options[] = array(
                    'label' => Mage::helper('adminhtml')->__($coupon['name']),
                    'value' => $coupon['rule_id'],
                );
            }
        }

        return $this->_options;
    }

}