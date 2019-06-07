<?php

class MageWorkshop_ImportExportReview_Model_History extends Mage_Core_Model_Abstract
{
    CONST TYPE_IMPORT = 0;
    CONST TYPE_EXPORT = 1;

    protected function _construct()
    {
        $this->_init('mageworkshop_importexportreview/history');
    }

    /**
     * @return array
     */
    public function getTypesArray()
    {
        return array(
            self::TYPE_IMPORT => Mage::helper('mageworkshop_importexportreview')->__('Import'),
            self::TYPE_EXPORT => Mage::helper('mageworkshop_importexportreview')->__('Export'),
        );
    }
}