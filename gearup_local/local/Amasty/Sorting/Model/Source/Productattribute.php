<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2019 Amasty (https://www.amasty.com)
 * @package Amasty_Sorting
 */


class Amasty_Sorting_Model_Source_Productattribute
{
    private $renamedAttributes = array(
        'Open Amount Min Value',
        'Open Amount Max Value'
    );

    public function toOptionArray()
    {
        $entityTypeId = Mage::getModel('eav/entity')->setType(Mage_Catalog_Model_Product::ENTITY)->getTypeId();
        $attributes = Mage::getModel('eav/entity_attribute')->getCollection();
        $attributes->addFieldToFilter('entity_type_id', $entityTypeId);
        $attributes->addFieldToFilter('backend_type', 'decimal');
        $attributes->addFieldToFilter('attribute_code', array('neq' => 'weight'));

        $options = array();
        foreach($attributes as $attribute) {
            $label = $attribute->getFrontendLabel();
            $label .= in_array($label, $this->renamedAttributes) ? ' (for Magento EE)' : '';
            $options[] = array(
                'value' => $attribute->getAttributeCode(),
                'label' => $label,
            );
        }

        return $options;
    }
}
    