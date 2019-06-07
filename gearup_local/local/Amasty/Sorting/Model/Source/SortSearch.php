<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2019 Amasty (https://www.amasty.com)
 * @package Amasty_Sorting
 */


class Amasty_Sorting_Model_Source_SortSearch extends Mage_Adminhtml_Model_System_Config_Source_Catalog_ListSort
{
    public function toOptionArray()
    {
        $options = array_filter(parent::toOptionArray(), function($val) {
            return (isset($val['value']) && $val['value'] != 'position');
        });
        array_unshift($options, array('value' => 'relevance', 'label' => 'Relevance'));
        return $options;
    }
}
