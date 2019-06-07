<?php

class MageWorkshop_ImportExportReview_Model_Resource_Sync extends Mage_Core_Model_Resource_Db_Abstract
{
    protected function _construct()
    {
        $this->_init('mageworkshop_importexportreview/sync', 'id');
    }

    /**
     * @param Mage_Core_Model_Abstract $object
     * @param mixed $value
     * @param null $field
     * @return Mage_Core_Model_Resource_Db_Abstract
     */
    public function load(Mage_Core_Model_Abstract $object, $value, $field = null)
    {
        if (!is_numeric($value) && is_null($field)) {
            $field = 'store_identity';
        }
        return parent::load($object, $value, $field);
    }
}