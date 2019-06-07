<?php
/**
 * MageWorkshop
 * Copyright (C) 2016 MageWorkshop <info@mage-review.com>
 *
 * @category   MageWorkshop
 * @package    MageWorkshop_DRReminder
 * @copyright  Copyright (c) 2016 MageWorkshop Co. (https://mage-review.com/)
 * @license    http://opensource.org/licenses/gpl-3.0.html GNU General Public License, version 3 (GPL-3.0)
 * @author     MageWorkshop <info@mage-review.com>
 */

class MageWorkshop_DRReminder_Model_Uninstall extends Mage_Core_Model_Abstract
{
    public function clearDatabaseInformation()
    {
        $setup = Mage::getModel('eav/entity_setup', 'core_setup');
        $setup->startSetup();
        
        /** @var Mage_Core_Model_Resource $coreResource */
        $coreResource = Mage::getSingleton('core/resource');
        $coreResourceTable = $coreResource->getTableName('core/resource');
        $reviewRemindersTable = $coreResource->getTableName('drreminder/review_reminders');
        $salesFlatOrderItemTable = $coreResource->getTableName('sales/order_item');
        $sql = "DROP TABLE IF EXISTS `$reviewRemindersTable`;";
        $setup->run($sql);
        $coreResource->getConnection('core_write')->dropColumn($salesFlatOrderItemTable,'reminder');

        try {
            $setup->deleteTableRow($coreResourceTable,'code','drreminder_setup');
        } catch (Exception $e) {
            Mage::logException($e);
        }

        $setup->endSetup();
    }

}
