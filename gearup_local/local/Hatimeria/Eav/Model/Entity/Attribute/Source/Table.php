<?php

class Hatimeria_Eav_Model_Entity_Attribute_Source_Table extends Mage_Eav_Model_Entity_Attribute_Source_Table
{
    public function getAllOptions($withEmpty = true, $defaultValues = false)
    {
        $storeId = $this->getAttribute()->getStoreId();
        if (!is_array($this->_options)) {
            $this->_options = array();
        }
        if (!is_array($this->_optionsDefault)) {
            $this->_optionsDefault = array();
        }
        if (!isset($this->_options[$storeId])) {
            $collection = Mage::getResourceModel('eav/entity_attribute_option_collection');
            if ('manufacturer' != $this->getAttribute()->getAttributeCode()) {
                $collection->setPositionOrder('asc');
            }
            $collection->setAttributeFilter($this->getAttribute()->getId());
            $collection->setStoreFilter($this->getAttribute()->getStoreId());
            $collection->load();
            $this->_options[$storeId]        = $collection->toOptionArray();
            $this->_optionsDefault[$storeId] = $collection->toOptionArray('default_value');
        }
        $options = ($defaultValues ? $this->_optionsDefault[$storeId] : $this->_options[$storeId]);
        if ($withEmpty) {
            array_unshift($options, array('label' => '', 'value' => ''));
        }

        return $options;
    }
}