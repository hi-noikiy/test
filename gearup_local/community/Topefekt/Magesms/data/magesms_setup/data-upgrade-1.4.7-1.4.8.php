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
 $iddb18dc4afa6663cf07a52c741943ff87cbe3896 = $this; $iddb18dc4afa6663cf07a52c741943ff87cbe3896->startSetup(); $ib678de385e13abb750f86db873c70fd899d5324c = array( array("Afghanistan",93,9), array("Andorra",376,6), array("Aruba",297,7), array("Benin",229,8), array("Brunei",673,7), array("Burkina Faso",226,8), array("Burundi",257,8), array("Cape Verde",238,7), array("CĂ´te d'Ivoire",225,8), array("Comoros",269,7), array("Congo Dem. R.",243,9), array("Cook Islands",682,5), array("Djibouti",253,6), array("Equatorial Guinea",240,6), array("Falkland Islands",500,5), array("Gabon",241,8), array("Gambia",220,7), array("Gibraltar",350,8), array("French Guiana",594,9), array("Guinea",224,8), array("Guinea-Bissau",245,7), array("Guyana",592,7), array("Haiti",509,8), array("Chad",235,7), array("Kiribati",686,5), array("Liberia",231,7), array("Liberia",231,8), array("Mali",223,8), array("Mauritania",222,7), array("Mauritius",230,7), array("Mauritius",230,8), array("Montenegro",382,7), array("Montenegro",382,8), array("Montenegro",382,9), array("Montenegro",382,10), array("Mozambique",258,9), array("Netherlands Ant.",299,6), array("New Caledonia",687,6), array("Palau",680,7), array("RĂ©union",262,9), array("Saint Pierre a. M.",508,6), array("SĂŁo Tome a. P.",239,6), array("San Marino",378,9), array("San Marino",378,10), array("San Marino",378,11), array("San Marino",378,12), array("San Marino",378,13), array("Seychelles",248,6), array("Seychelles",248,7), array("Sierra Leone",232,8), array("Solomon Islands",677,5), array("Solomon Islands",677,7), array("Sudan",249,9), array("Suriname",597,7), array("Togo",228,7), array("Tuvalu",688,5), array("UAE",971,9), array("Uganda",256,9), array("Zambia",260,8), ); foreach ($ib678de385e13abb750f86db873c70fd899d5324c as $i04a044a36bef0ddde6d5de08f57f074024136d74) { $i4f3b75abfeef0eea3f3858aa24b2cf7c9edfa6ce = Mage::getModel('magesms/maps')->getCollection()->addFieldToFilter('area', $i04a044a36bef0ddde6d5de08f57f074024136d74[1])->addFieldToFilter('number', $i04a044a36bef0ddde6d5de08f57f074024136d74[2]); if (!$i4f3b75abfeef0eea3f3858aa24b2cf7c9edfa6ce->count()) { $i4f3b75abfeef0eea3f3858aa24b2cf7c9edfa6ce = Mage::getModel('magesms/maps'); $i4f3b75abfeef0eea3f3858aa24b2cf7c9edfa6ce->setArea($i04a044a36bef0ddde6d5de08f57f074024136d74[1])->setNumber($i04a044a36bef0ddde6d5de08f57f074024136d74[2]); $i4f3b75abfeef0eea3f3858aa24b2cf7c9edfa6ce->save(); } } $i0933475b5bd80561a9f50282fd9eb0b8345cec4b = Mage::getModel('magesms/variables')->getCollection()->addFieldToFilter('name', 'customer_shipping_firstname'); if (!$i0933475b5bd80561a9f50282fd9eb0b8345cec4b->count()) { Mage::getModel('magesms/variables')->setName('customer_shipping_firstname')->setTemplate('John')->setTranslate(0)->save(); } $i0933475b5bd80561a9f50282fd9eb0b8345cec4b = Mage::getModel('magesms/variables')->getCollection()->addFieldToFilter('name', 'customer_shipping_lastname'); if (!$i0933475b5bd80561a9f50282fd9eb0b8345cec4b->count()) { Mage::getModel('magesms/variables')->setName('customer_shipping_lastname')->setTemplate('DOE')->setTranslate(0)->save(); } $i0933475b5bd80561a9f50282fd9eb0b8345cec4b = Mage::getModel('magesms/variables')->getCollection()->addFieldToFilter('name', 'order_subtotal'); if (!$i0933475b5bd80561a9f50282fd9eb0b8345cec4b->count()) { Mage::getModel('magesms/variables')->setName('order_subtotal')->setTemplate('450')->setTranslate(0)->save(); } $i0933475b5bd80561a9f50282fd9eb0b8345cec4b = Mage::getModel('magesms/variables')->getCollection()->addFieldToFilter('name', 'order_shipping_amount'); if (!$i0933475b5bd80561a9f50282fd9eb0b8345cec4b->count()) { Mage::getModel('magesms/variables')->setName('order_shipping_amount')->setTemplate('14')->setTranslate(0)->save(); } $iddb18dc4afa6663cf07a52c741943ff87cbe3896->run("
	UPDATE `{$this->getTable('magesms_hooks')}`
		SET `notice` = REPLACE(`notice`, '{customer_phone}', '{customer_phone}, {customer_shipping_lastname}, {customer_shipping_firstname}')
		WHERE `notice` LIKE '%{customer_phone}%' AND `notice` NOT LIKE '%{customer_shipping_firstname}%';
"); $iddb18dc4afa6663cf07a52c741943ff87cbe3896->run("
	UPDATE `{$this->getTable('magesms_hooks')}`
		SET `notice` = REPLACE(`notice`, '{order_total_paid}', '{order_total_paid}, {order_subtotal}, {order_shipping_amount}')
		WHERE `notice` LIKE '%{order_total_paid}%' AND `notice` NOT LIKE '%{order_subtotal}%';
"); $iddb18dc4afa6663cf07a52c741943ff87cbe3896->endSetup(); 