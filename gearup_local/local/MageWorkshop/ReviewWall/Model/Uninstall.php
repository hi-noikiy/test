<?php
/**
 * MageWorkshop
 * Copyright (C) 2016 MageWorkshop <info@mage-review.com>
 *
 * @category   MageWorkshop
 * @package    MageWorkshop_ReviewWall
 * @copyright  Copyright (c) 2016 MageWorkshop Co. (https://mage-review.com/)
 * @license    http://opensource.org/licenses/gpl-3.0.html GNU General Public License, version 3 (GPL-3.0)
 * @author     MageWorkshop <info@mage-review.com>
 */

class MageWorkshop_ReviewWall_Model_Uninstall
{
    public function clearDatabaseInformation()
    {
        $setup = new Mage_Eav_Model_Entity_Setup('core_setup');

        $setup->startSetup();

        /** @var Mage_Core_Model_Resource $coreResource */
        $coreResource = Mage::getSingleton('core/resource');

        try {
            $coreResourceTable = $coreResource->getTableName('core/resource');
            $setup->deleteTableRow($coreResourceTable,'code','mageworkshop_reviewwall_setup');
            
            if ($coreResource->getConnection('core_write')->isTableExists('permission_block')) {
                $permissionBlockTable = $coreResource->getTableName('admin/permission_block');
                $setup->deleteTableRow($permissionBlockTable, 'block_name', 'reviewwall/widget_wall');
            }
        } catch (Exception $e) {
            Mage::logException($e);
        }

        $setup->endSetup();
    }
}