<?php
/**
 * MindMagnet Products Sort
 * Model Class
 *
 * Copyright (C) 2015-2016 MindMagnet <contact@blugento.com>
 * LICENSE: GNU General Public License for more details <http://opensource.org/licenses/gpl-license.php>
 *
 * @package MindMagnet_Sort
 * @author Simona Trifan <simona.plesuvu@mindmagnetsoftware.com>
 * @link http://www.blugento.com
 */

class MindMagnet_Sort_Model_Sortby extends Mage_Catalog_Model_Category_Attribute_Source_Sortby
{
    /**
     * Retrieve All options
     *
     * @return array
     */
    public function getAllOptions()
    {
        $attributes = array();
        $attributes[] = array(
            'label' => Mage::helper('mindmagnetsort')->__('Popularity'),
            'value' => 'popularity'
        );
        $attributes[] = array(
            'label' => Mage::helper('mindmagnetsort')->__('Value'),
            'value' => 'value'
        );

        $this->_options = array_merge(
            parent::getAllOptions(),
            $attributes
        );

        return $this->_options;
    }
}
