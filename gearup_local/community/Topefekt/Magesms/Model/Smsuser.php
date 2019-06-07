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
 class Topefekt_Magesms_Model_Smsuser extends Mage_Core_Model_Abstract { protected function _construct() { $this->_init('magesms/smsuser'); } public function validate() { $ieeea3fa58a065e13acdb42aab551831a98e9444c = array(); $i0d09b2a4f282150bf47b02f9f3d82586fe313844 = Mage::helper('magesms'); if ($this->getData('deliveryemail') && !Zend_Validate::is($this->getData('deliveryemail'), 'EmailAddress')) { $ieeea3fa58a065e13acdb42aab551831a98e9444c[] = $i0d09b2a4f282150bf47b02f9f3d82586fe313844->__('Invalid e-mail'); } if (!Zend_Validate::is($this->getData('email'), 'EmailAddress')) { $ieeea3fa58a065e13acdb42aab551831a98e9444c[] = $i0d09b2a4f282150bf47b02f9f3d82586fe313844->__('Invalid e-mail'); } if (!$this->getId() && $this->getData('agree', 0) != 1) { $ieeea3fa58a065e13acdb42aab551831a98e9444c[] = $i0d09b2a4f282150bf47b02f9f3d82586fe313844->__('You have to agree with licence terms.'); } if (empty($ieeea3fa58a065e13acdb42aab551831a98e9444c)) { return true; } return $ieeea3fa58a065e13acdb42aab551831a98e9444c; } public function _afterSave() { if ($this->getOrigData('country0') != $this->getCountry0()) { Mage::getModel('magesms/routes')->updatepricelist(); } Mage::getModel('core/config')->saveConfig('magesms/user/deliveryReportsErrorOnly', $this->getData('deliveryReportsErrorOnly')); return parent::_afterSave(); } protected function _afterLoad() { $this->setData('deliveryReportsErrorOnly', Mage::getStoreConfig('magesms/user/deliveryReportsErrorOnly')); return parent::_afterLoad(); } } 