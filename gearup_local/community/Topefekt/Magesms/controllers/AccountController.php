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
 require_once (Mage::getModuleDir('controllers','Mage_Customer').DS.'AccountController.php'); class Topefekt_Magesms_AccountController extends Mage_Customer_AccountController { public function createAction() { if (!Mage::helper('magesms')->isActive() || !Mage::helper('magesms')->getOtpCustomerType()) return parent::createAction(); if ($this->_getSession()->isLoggedIn()) { $this->_redirect('*/*'); return $this; } if (Mage::helper('magesms')->getOtpCustomerType() == 1) { $i0e3e80cee9c51f140b823db0b7df66493acca657 = $this->_getSession(); if ($i0e3e80cee9c51f140b823db0b7df66493acca657->getData('magesms_validate') && $i0e3e80cee9c51f140b823db0b7df66493acca657->getData('magesms_validate_time') >= time()-Mage::getStoreConfig('magesms/smsvalid/session_lifetime')) return parent::createAction(); $this->_redirect('magesms/validate/mobile'); return $this; } $i0e3e80cee9c51f140b823db0b7df66493acca657 = $this->_getSession(); if (!$i0e3e80cee9c51f140b823db0b7df66493acca657->getData('magesms_validate_code') || $i0e3e80cee9c51f140b823db0b7df66493acca657->getData('magesms_validate_time') <= time()-Mage::getStoreConfig('magesms/smsvalid/code_lifetime')) { $i0e3e80cee9c51f140b823db0b7df66493acca657->setData('magesms_validate_code', Mage::helper('magesms')->getOtpRandCode()); $i0e3e80cee9c51f140b823db0b7df66493acca657->setData('magesms_validate', false); $i0e3e80cee9c51f140b823db0b7df66493acca657->setData('magesms_validate_time', time()); } $this->loadLayout(); $this->getLayout()->getBlock('head')->addJs('topefekt/smsvalid.js'); $i3358fd35282548f1f8ccafbf23d60a4ade466fd3 = 'var magesmsValidUrl = "'.Mage::getUrl('magesms/validate/customer', array('_secure' => true)).'";'; $i3358fd35282548f1f8ccafbf23d60a4ade466fd3 .= "Translator.add('OTP SMS','".Mage::helper('magesms')->__('OTP SMS')."');"; $i8ee45e0018a32fb1a855b82624506e35789cc4d2 = $this->getLayout()->createBlock('core/text', 'magesmsvalid')->setText(Mage::helper('core/js')->getScript($i3358fd35282548f1f8ccafbf23d60a4ade466fd3)); $this->getLayout()->getBlock('content')->append($i8ee45e0018a32fb1a855b82624506e35789cc4d2); $this->_initLayoutMessages('customer/session'); $this->renderLayout(); } }