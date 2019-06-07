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

$connection->beginTransaction();

try {
    $connection->insertArray(
        $installer->getTable('detailedreview/complaint_type'),
        array('title', 'status_id'),
        array(
            array('Unwanted advertising content or spam', MageWorkshop_DetailedReview_Model_Source_Common_Status::STATUS_ENABLED),
            array('Pornography or sexually explicit material', MageWorkshop_DetailedReview_Model_Source_Common_Status::STATUS_ENABLED),
            array('Hate speech or graphic violence', MageWorkshop_DetailedReview_Model_Source_Common_Status::STATUS_ENABLED),
            array('Harassment or bullying', MageWorkshop_DetailedReview_Model_Source_Common_Status::STATUS_ENABLED),
            array('Copyrighted material', MageWorkshop_DetailedReview_Model_Source_Common_Status::STATUS_ENABLED),
        )
    );

    $connection->commit();

} catch (Exception $e) {
    Mage::logException($e);
    $connection->rollBack();
}

$installer->endSetup();