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

class MindMagnet_Sort_Model_Config extends Mage_Catalog_Model_Config
{
    public function getAttributeUsedForSortByArray()
    {
        $attributes = array();
        $attributes['popularity'] = Mage::helper('mindmagnetsort')->__('Popularity');
        $attributes['value'] = Mage::helper('mindmagnetsort')->__('Value');

        return array_merge(
            parent::getAttributeUsedForSortByArray(),
            $attributes
        );
    }
}
