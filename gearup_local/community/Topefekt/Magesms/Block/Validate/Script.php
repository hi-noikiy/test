<?php
/**
 * Mage SMS - SMS notification & SMS marketing
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the BSD 3-Clause License
 * It is available through the world-wide-web at this URL:
 * http://opensource.org/licenses/BSD-3-Clause
 *
 * @category    TOPefekt
 * @package     TOPefekt_Magesms
 * @copyright   Copyright (c) 2012-2017 TOPefekt s.r.o. (http://www.mage-sms.com)
 * @license     http://opensource.org/licenses/BSD-3-Clause
 */
 class Topefekt_Magesms_Block_Validate_Script extends Mage_Core_Block_Template { protected function _prepareLayout() { parent::_prepareLayout(); if (Mage::helper('magesms')->isActive() && (Mage::helper('magesms')->getOtpOrderType() || Mage::helper('magesms')->getOtpCheckoutCustomerType())) $this->getLayout()->getBlock('head')->addJs('topefekt/smsvalid.js'); return $this; } public function _toHtml() { if (Mage::helper('magesms')->isActive() && Mage::helper('magesms')->getOtpOrderType()) { $i3358fd35282548f1f8ccafbf23d60a4ade466fd3 = 'var magesmsValidUrl = "' . Mage::getUrl('magesms/validate/order', array('_secure' => true)) . '";'; $i3358fd35282548f1f8ccafbf23d60a4ade466fd3 .= "Translator.add('OTP SMS','".Mage::helper('magesms')->__('OTP SMS')."');"; return Mage::helper('core/js')->getScript($i3358fd35282548f1f8ccafbf23d60a4ade466fd3); } return ''; } } 