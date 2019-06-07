<?php

class Gearup_Sds_Model_Resource_History_Collection extends Mage_Core_Model_Resource_Db_Collection_Abstract {

    protected $_joinedFields = array();

    protected function _construct() {
        parent::_construct();
        $this->_init("gearup_sds/history");
    }

    public function addProductName()
    {
        $productName = Mage::getSingleton('eav/config')->getAttribute('catalog_product','name');

        $this->getSelect()
            -> join( array('product_attribute' => $productName->getBackendTable()),
                'main_table.product_id = product_attribute.entity_id',
                array())
            ->where("product_attribute.attribute_id = ?", $productName->getId());

        return $this;
    }

    public function addProductData()
    {
        /** add particular attribute code to this array */
        $productAttributes = array('status');
        foreach ($productAttributes as $attributeCode) {
            $alias     = $attributeCode . '_table';
            $attribute = Mage::getSingleton('eav/config')
                ->getAttribute(Mage_Catalog_Model_Product::ENTITY, $attributeCode);

            /** Adding eav attribute value */
            $this->getSelect()->join(
                array($alias => $attribute->getBackendTable()),
                "main_table.product_id = $alias.entity_id AND $alias.attribute_id={$attribute->getId()}",
                array($attributeCode => 'value')
            );
            $this->_map['fields'][$attributeCode] = 'value';
        }
        return $this;
    }

}
