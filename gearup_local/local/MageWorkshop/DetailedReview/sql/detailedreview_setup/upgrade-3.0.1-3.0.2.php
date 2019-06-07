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

$setup = new Mage_Eav_Model_Entity_Setup('core_setup');

$catalogProductEntity = $installer->getEntityTypeId('catalog_product');

$idAttributes[] = $setup->getAttribute($catalogProductEntity, 'popularity_by_sells', 'attribute_id');
$idAttributes[] = $setup->getAttribute($catalogProductEntity, 'popularity_by_reviews', 'attribute_id');
$idAttributes[] = $setup->getAttribute($catalogProductEntity, 'popularity_by_rating', 'attribute_id');

$connection->beginTransaction();

try {
	/** @var Mage_Core_Model_Resource_Config_Data_Collection $coreConfigDataCollection */
	$coreConfigDataCollection = Mage::getModel('core/config_data')
		->getCollection()
		->addFieldToFilter('path', 'detailedreview/settings/enable_jquery');

	/** @var Mage_Core_Model_Config_Data $detailedreviewQueryConfig */
	foreach ($coreConfigDataCollection as $detailedreviewQueryConfig) {
		/** @var Mage_Core_Model_Resource_Config $configResource */
		$configResource = Mage::getResourceModel('core/config');
		$configResource->saveConfig(
			MageWorkshop_DetailedReview_Helper_Config::DETAILEDREVIEW_XML_PATH_JS_LIB_JQUERY,
			$detailedreviewQueryConfig->getValue(),
			$detailedreviewQueryConfig->getScope(),
			$detailedreviewQueryConfig->getScopeId()
		);
	}

    foreach ($idAttributes as $idAttribute) {
        $installer->updateAttribute($catalogProductEntity, $idAttribute, array(
            'is_visible' => false
        ));
    }

    $connection->delete($coreConfigTable, new Zend_Db_Expr("path = 'detailedreview/settings/enable_jquery'"));
    $connection->commit();

} catch (Exception $e) {
    $connection->rollBack();
    Mage::logException($e);
}

$installer->endSetup();