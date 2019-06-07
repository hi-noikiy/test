<?php

class Gearup_Activity_Model_Attribute_Source_Manufacturer extends Mage_Eav_Model_Entity_Attribute_Source_Abstract
{

    public function getAllOptions()
    {
        if (is_null($this->_options)) {
            $attribute = Mage::getSingleton('eav/config')->getAttribute(Mage_Catalog_Model_Product::ENTITY, 'manufacturer');

            if ($attribute->usesSource()) {
                $options = $attribute->getSource()->getAllOptions(false);
            }
            foreach ($options as $option) {
                $this->_options[] = array( 'label' => $option['label'], 'value' =>  $option['value']);
            }
        }
        return $this->_options;
    }

    public function toOptionArray()
    {
        return $this->getAllOptions();
    }
}