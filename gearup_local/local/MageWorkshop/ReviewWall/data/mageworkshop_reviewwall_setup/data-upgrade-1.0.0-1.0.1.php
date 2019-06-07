<?php
/**
 * MageWorkshop
 * Copyright (C) 2016 MageWorkshop <info@mage-review.com>
 *
 * @category   MageWorkshop
 * @package    MageWorkshop_DetailedReview
 * @copyright  Copyright (c) 2016 MageWorkshop Co. (https://mage-review.com)
 * @license    http://opensource.org/licenses/gpl-3.0.html GNU General Public License, version 3 (GPL-3.0)
 * @author     MageWorkshop <info@mage-review.com>
 */

/* @var $installer Mage_Core_Model_Resource_Setup */
$installer = $this;

$installer->startSetup();

$connection = $installer->getConnection();

$coreConfigTable = $installer->getTable('core/config_data');

$connection->beginTransaction();

try {
	/** @var Mage_Core_Model_Resource_Config_Data_Collection $coreConfigDataCollection */
	$coreConfigDataCollection = Mage::getModel('core/config_data')
		->getCollection()
		->addFieldToFilter('path', 'reviewwall/reviewwall_settings/reviewwall_image_lazy_loading');
	
	if ($coreConfigDataCollection->getSize()) {
		$connection->delete($coreConfigTable, new Zend_Db_Expr("path = 'reviewwall/reviewwall_settings/reviewwall_image_lazy_loading'"));
	}
	
	/** @var Mage_Core_Model_Resource_Config_Data_Collection $coreConfigDataCollection */
	$coreConfigDataCollection = Mage::getModel('core/config_data')
		->getCollection()
		->addFieldToFilter('path', 'reviewwall/javascript_libraries/lazyloading_enable');
	
	if ($coreConfigDataCollection->getSize()) {
		$connection->delete($coreConfigTable, new Zend_Db_Expr("path = 'reviewwall/javascript_libraries/lazyloading_enable'"));
	}
	
	$connection->commit();
	
} catch (Exception $e) {
	$connection->rollBack();
	Mage::logException($e);
}

$installer->endSetup();