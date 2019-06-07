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
 class Topefekt_Magesms_Model_Mysql4_Smsuser_Collection extends Mage_Core_Model_Mysql4_Collection_Abstract { public function _construct() { parent::_construct(); $this->_init('magesms/smsuser'); } protected function _afterLoad() { foreach ($this->getItems() as $i705fa7c9639d497e1179d7d5691c212668a8c9c8) $i705fa7c9639d497e1179d7d5691c212668a8c9c8->setData('deliveryReportsErrorOnly', Mage::getStoreConfig('magesms/user/deliveryReportsErrorOnly')); return parent::_afterLoad(); } }