<?php

class MageWorkshop_CommentOnReview_Model_Uninstall
{
    public function clearDatabaseInformation()
    {
        $setup = new Mage_Eav_Model_Entity_Setup('core_setup');

        $setup->startSetup();

        $coreResource = Mage::getSingleton('core/resource');

        $coreResourceTable = $coreResource->getTableName('core/resource');
        $reviewEntityTable = $coreResource->getTableName('review/review_entity');

        try {
            $setup->deleteTableRow($coreResourceTable,'code','mageworkshop_commentonreview_setup');
            $setup->deleteTableRow($reviewEntityTable,'entity_code','review');
        } catch (Exception $e) {
            Mage::logException($e);
        }

        $setup->endSetup();
    }
}