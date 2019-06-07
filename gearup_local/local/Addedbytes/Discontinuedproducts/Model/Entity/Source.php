<?php

class Addedbytes_Discontinuedproducts_Model_Entity_Source extends Mage_Eav_Model_Entity_Attribute_Source_Abstract
{

    public function getAllOptions()
    {
        $storeId = $this->getAttribute()->getStoreId();
        if (!is_array($this->_options)) {
            $this->_options = array();
        }
        if (!isset($this->_options[$storeId])) {

            // Load categories, ordered by path, including category name
            $categories = Mage::getModel('catalog/category')
                ->getCollection()
                ->setStoreId($storeId)
                ->addAttributeToSelect('name')
                ->addFieldToFilter('is_active', array('eq' => '1'))
                ->addAttributeToSort('path', 'asc')
                ->load()
                ->toArray();

            // Loop through and build options array
            $res = array();
            foreach ($categories as $_category) {

                // Skip system root
                if ($_category['entity_id'] == 1) {
                    continue;
                }

                // Build array for options
                $data = array();
                $data['value'] = $_category['entity_id'];

                $label = '';
                for ($i = 1; $i < $_category['level']; $i++) {
                    $label .= '- ';
                }
                $label .= $_category['name'];
                $data['label'] = $label;

                // Add this category to options
                $res[] = $data;
            }

            $this->_options[$storeId] = $res;
        }

        $options = $this->_options[$storeId];

        return $options;
    }
}
