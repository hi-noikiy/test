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
class Topefekt_Magesms_Model_System_Config_Connector { public function toOptionArray() { return array( array( 'value' => '', 'label' => Mage::helper('magesms')->__('Auto (SSL priority)'), ), array( 'value' => 'ssl', 'label' => Mage::helper('magesms')->__('fsockopen (SSL)'), ), array( 'value' => 'curl-ssl', 'label' => Mage::helper('magesms')->__('CURL (SSL)'), ), array( 'value' => 'no-ssl', 'label' => Mage::helper('magesms')->__('fsockopen (no-SSL)'), ), array( 'value' => 'curl', 'label' => Mage::helper('magesms')->__('CURL (no-SSL)'), ), ); } } 