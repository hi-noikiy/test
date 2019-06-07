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
 $iddb18dc4afa6663cf07a52c741943ff87cbe3896 = $this; $iddb18dc4afa6663cf07a52c741943ff87cbe3896->startSetup(); $iddb18dc4afa6663cf07a52c741943ff87cbe3896->run("
	UPDATE `{$this->getTable('magesms_hooks')}` SET `group` = 5 WHERE `name` IN ('customerRegisterOTP', 'newOrderOTP');
"); $ie54fcd5470bd7f31f709089290e33bb03e655c25 = Topefekt_Magesms_Model_System_Config_Lang::toOptionArray(); foreach ($ie54fcd5470bd7f31f709089290e33bb03e655c25 as $i593f9fb6306ab4cdb862f1ef6769504d63647c90) { if ($i593f9fb6306ab4cdb862f1ef6769504d63647c90['value'] == 'cz') { $i092fed12249a415fe47769fa9b0bb17968e798c0 = "(NULL, 'checkoutCustomerOTP', 'Ověřený zákazník', 3, 5, '', '', '', 'Vazeny zakazniku, toto je Vas jednorazovy kod pro overeni: {code} . {shop_name}', '{code}<br /><br />{shop_domain}, {shop_name}, {shop_email}, {shop_phone}', 'cz', 0)"; } elseif ($i593f9fb6306ab4cdb862f1ef6769504d63647c90['value'] == 'sk') { $i092fed12249a415fe47769fa9b0bb17968e798c0 = "(NULL, 'checkoutCustomerOTP', 'Overený zákazník', 3, 5, '', '', '', 'Vazeny zakaznik, toto je Vas jednorazovy kod pre overenie: {code} . {shop_name}', '{code}<br /><br />{shop_domain}, {shop_name}, {shop_email}, {shop_phone}', 'sk', 0)"; } else { $i092fed12249a415fe47769fa9b0bb17968e798c0 = "(NULL, 'checkoutCustomerOTP', 'Verified cutomer', 3, 5, '', '', '', 'Dear customer, this is your OTP code for verification: {code} . {shop_name}', '{code}<br /><br />{shop_domain}, {shop_name}, {shop_email}, {shop_phone}', '{$i593f9fb6306ab4cdb862f1ef6769504d63647c90['value']}', 0);"; } if (!Mage::getModel('magesms/hooks')->getCollection()->addFieldToFilter('name', 'checkoutCustomerOTP')->addFieldToFilter('lang', $i593f9fb6306ab4cdb862f1ef6769504d63647c90['value'])->count()) $iddb18dc4afa6663cf07a52c741943ff87cbe3896->run("
			INSERT INTO `{$this->getTable('magesms_hooks')}` VALUES $i092fed12249a415fe47769fa9b0bb17968e798c0;
		"); } $i0933475b5bd80561a9f50282fd9eb0b8345cec4b = Mage::getModel('magesms/variables')->getCollection()->addFieldToFilter('name', 'admin_name'); if (!$i0933475b5bd80561a9f50282fd9eb0b8345cec4b->count()) { Mage::getModel('magesms/variables')->setName('admin_name')->setTemplate('Admin Doe')->setTranslate(0)->save(); } $i0933475b5bd80561a9f50282fd9eb0b8345cec4b = Mage::getModel('magesms/variables')->getCollection()->addFieldToFilter('name', 'admin_phone'); if (!$i0933475b5bd80561a9f50282fd9eb0b8345cec4b->count()) { Mage::getModel('magesms/variables')->setName('admin_phone')->setTemplate('800654321')->setTranslate(0)->save(); } $iddb18dc4afa6663cf07a52c741943ff87cbe3896->endSetup(); 