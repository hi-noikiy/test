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


class Plumrocket_Checkoutspage_Model_System_Config_Coupon  extends Mage_Adminhtml_Block_System_Config_Form_Field 
  implements Varien_Data_Form_Element_Renderer_Interface
{
    public function toOptionArray()
    {

        $coupons = Mage::getModel('salesrule/rule')->getCollection()
                ->addFieldToFilter('coupon_type', array('in' => array(Mage_SalesRule_Model_Rule::COUPON_TYPE_SPECIFIC, Mage_SalesRule_Model_Rule::COUPON_TYPE_AUTO)))
                ->addFieldToFilter('is_active', true);

        $values['custom'] =  array('value' => 'custom', 'label'=>Mage::helper('adminhtml')->__('Custom Coupon Code'));
        foreach($coupons as $coupon) {
          $values[] = array('value' => $coupon['rule_id'], 'label'=>Mage::helper('adminhtml')->__($coupon['name']));
        }

        return $values;
    }


}