<?php
/**
 * Class Manufacturer
 */
class Hatimeria_AttributeBrowser_Model_Attribute_Manufacturer extends Mage_Core_Model_Abstract
{
    public function toOptionArray()
    {
        $items = Mage::getSingleton('attributebrowser/list')->getAttributeItems('manufacturer');
        $options = array();

        foreach ($items as $key => $item) {
            $options[] = array('value' => $item['id'], 'label' => $item['name']);
        }

        return $options;
    }
}