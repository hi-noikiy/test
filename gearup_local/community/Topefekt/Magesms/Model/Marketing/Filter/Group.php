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
class Topefekt_Magesms_Model_Marketing_Filter_Group extends Varien_Object { public $filter; public function _construct() { $this->filter = array( 'title' => Mage::helper('magesms')->__('Groups'), 'firstItem' => Mage::helper('magesms')->__('All'), 'type' => 'select', 'name' => 'group', 'color' => '#d2b48c', ); } public function getValues() { return array_merge( array(array('value' => '', 'label' => $this->filter['firstItem'])), Mage::getModel('customer/group')->getCollection()->toOptionArray(false) ); } public function getFilter($iff7e46827cbb6547116c592bf800f4687428abf9, $i2d8fb6b6f17ec9aa17899ea311cc26bc493cd9a2) { $i3381b8e0f8a10cba70bd1ec41b87d5c91ed140dc = false; $i76200fed8240be52de0fc75ec3367898a197407f = Mage::getModel('eav/entity_attribute')->loadByCode('customer', 'group_id'); if ($i76200fed8240be52de0fc75ec3367898a197407f->getFrontendInput() == 'multiselect') $i3381b8e0f8a10cba70bd1ec41b87d5c91ed140dc = true; $i717aafa07eeca1a7c0f40cc18a0eb90e0984de3e = array(); foreach($i2d8fb6b6f17ec9aa17899ea311cc26bc493cd9a2 as $iba20acc78644ac0e9cd48ea35d8ad03b058f6b5a) { if ($iba20acc78644ac0e9cd48ea35d8ad03b058f6b5a instanceof $this) { if ($i3381b8e0f8a10cba70bd1ec41b87d5c91ed140dc) $i717aafa07eeca1a7c0f40cc18a0eb90e0984de3e[] = array('%'.$iba20acc78644ac0e9cd48ea35d8ad03b058f6b5a->getValue().'%'); else $i717aafa07eeca1a7c0f40cc18a0eb90e0984de3e[] = array((int)$iba20acc78644ac0e9cd48ea35d8ad03b058f6b5a->getValue()); } } if (count($i717aafa07eeca1a7c0f40cc18a0eb90e0984de3e)) if ($i3381b8e0f8a10cba70bd1ec41b87d5c91ed140dc) $iff7e46827cbb6547116c592bf800f4687428abf9->addFieldToFilter('group_id', array('like' => $i717aafa07eeca1a7c0f40cc18a0eb90e0984de3e)); else $iff7e46827cbb6547116c592bf800f4687428abf9->addFieldToFilter('group_id', $i717aafa07eeca1a7c0f40cc18a0eb90e0984de3e); return $iff7e46827cbb6547116c592bf800f4687428abf9; } } 