<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2019 Amasty (https://www.amasty.com)
 * @package Amasty_Sorting
 */


class Amasty_Sorting_Model_Source_SortCategoryAfter extends Mage_Adminhtml_Model_System_Config_Source_Catalog_ListSort
{
    public function toOptionArray()
    {
        $options = parent::toOptionArray();
        array_unshift(
            $options,
            array('value'=>'', 'label'=> Mage::helper('adminhtml')->__('--Please Select--'))
        );
        return $options;
    }
}
