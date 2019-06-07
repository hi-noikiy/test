<?php

class TM_ProLabels_Model_Mysql4_System extends Mage_Core_Model_Mysql4_Abstract
{
    protected function _construct()
    {
        $this->_init('prolabels/system', 'system_id');
    }

    public function getUploader()
    {
        return Mage::getSingleton('prolabels/image_uploader');
    }

    public function load(Mage_Core_Model_Abstract $object, $value, $field=null)
    {

        if (!intval($value) && is_string($value)) {
            $field = 'identifier'; // You probably don't have an identifier...
        }
        return parent::load($object, $value, $field);
    }

    protected function _afterLoad(Mage_Core_Model_Abstract $object)
    {
        $select = $this->_getReadAdapter()->select()
        ->from($this->getTable('prolabels/sysstore'))
        ->where('system_id = ?', $object->getId());
        if ($data = $this->_getReadAdapter()->fetchAll($select)) {
            $storesArray = array();
            foreach ($data as $row) {
                $storesArray[] = $row['store_id'];
            }
            $object->setData('store_id', $storesArray);
        }

        return parent::_afterLoad($object);
    }

    protected function _getLoadSelect($field, $value, $object)
    {

        $select = parent::_getLoadSelect($field, $value, $object);

        if ($object->getStoreId()) {
            $select->join(array('cbs' => $this->getTable('prolabels/sysstore')), $this->getMainTable().'.system_id = cbs.system_id')
            ->where('cbs.store_id in (0, ?) ', $object->getStoreId())
            ->order('store_id DESC')
            ->limit(1);
        }
        return $select;
    }

   public function loadLabelProductImage($object)
    {
        $this->getUploader()->upload($object, 'product_image');
        return $this;
    }

    public function loadLabelCategoryImage($object)
    {
        $this->getUploader()->upload($object, 'category_image');
        return $this;
    }

    public function loadLabelCategoryOutImage($object) {
        $this->getUploader()->upload($object, 'category_out_stock_image');
        return $this;
    }

    public function loadLabelProductOutImage($object) {
        $this->getUploader()->upload($object, 'product_out_stock_image');
        return $this;
    }

    public function lookupStoreIds($id)
    {
        return $this->_getReadAdapter()->fetchCol($this->_getReadAdapter()->select()
                        ->from($this->getTable('prolabels/sysstore'), 'store_id')
                        ->where("{$this->getIdFieldName()} = ?", $id)
                        );
    }

    public function getStoreIds($id)
    {
        return $this->_getReadAdapter()->fetchCol($this->_getReadAdapter()->select()
                        ->from($this->getTable('prolabels/sysstore'), 'store_id')
                        ->where("system_id = ?", $id)
                        );
    }

    /* public function getStoreSystemLabelId($rulesId) {
        $storeId = Mage::app()->getStore()->getId();

        $result = $this -> _getReadAdapter() -> fetchAll(
                        $this -> _getReadAdapter() -> select()
                        -> from($this->getTable('sysstore'))
                        ->where('rules_id = ?', $rulesId)
        );
        return $result;
    } */

    public function getSystemLabelsData($rulesId) {
        $result = $this -> _getReadAdapter() -> fetchAll(
            $this -> _getReadAdapter() -> select()
                ->from($this->getTable('system'))
                ->where('rules_id = ?', $rulesId)
                ->where('l_status = 1')
        );
        return $result;
    }

    public function getSystemContentLabels()
    {
        $result = $this->_getReadAdapter()->fetchAll(
            $this->_getReadAdapter()->select()
                ->from($this->getTable('system'))
                ->where('product_position=?','content')
                ->where('l_status = 1')
        );
        return $result;
    }
}