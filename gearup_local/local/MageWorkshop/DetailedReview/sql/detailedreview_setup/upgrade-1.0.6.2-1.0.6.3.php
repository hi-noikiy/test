<?php
/**
 * MageWorkshop
 * Copyright (C) 2016 MageWorkshop <info@mage-review.com>
 *
 * @category   MageWorkshop
 * @package    MageWorkshop_DetailedReview
 * @copyright  Copyright (c) 2016 MageWorkshop Co. (https://mage-review.com/)
 * @license    http://opensource.org/licenses/gpl-3.0.html GNU General Public License, version 3 (GPL-3.0)
 * @author     MageWorkshop <info@mage-review.com>
 */

/** @var MageWorkshop_DetailedReview_Model_Mysql4_Setup $installer */
$installer = $this;
$installer->startSetup();
$connection = $installer->getConnection();

Mage::app()->cleanCache();
Mage::getConfig()->reinit();
Mage::app()->reinitStores();

$connection->beginTransaction();
try {
    if (!$active = Mage::getStoreConfig('detailedreview/rating_image/active')) {
        Mage::getModel('core/config')->saveConfig('detailedreview/rating_image/active', 'default/active-star.png' );
    }

    if (!$inactive = Mage::getStoreConfig('detailedreview/rating_image/unactive')) {
        Mage::getModel('core/config')->saveConfig('detailedreview/rating_image/unactive', 'default/unactive-star.png' );
    }
    $connection->commit();
} catch (Exception $e) {
    $connection->rollback();
    throw $e;
}

Mage::getConfig()->reinit();
Mage::app()->reinitStores();
$installer->endSetup();
