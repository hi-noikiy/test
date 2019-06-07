<?php
/**
 * Mirasvit
 *
 * This source file is subject to the Mirasvit Software License, which is available at https://mirasvit.com/license/.
 * Do not edit or add to this file if you wish to upgrade the to newer versions in the future.
 * If you wish to customize this module for your needs.
 * Please refer to http://www.magentocommerce.com for more information.
 *
 * @category  Mirasvit
 * @package   mirasvit/extension_advr
 * @version   1.2.11
 * @copyright Copyright (C) 2019 Mirasvit (https://mirasvit.com/)
 */



class Mirasvit_Advr_Block_Adminhtml_Block_Chart_Map extends Mirasvit_Advr_Block_Adminhtml_Block_Chart_Abstract
{
    public function _prepareLayout()
    {
        $this->setTemplate('mst_advr/block/chart/map.phtml');

        return parent::_prepareLayout();
    }

    public function getSeries()
    {
        $series = array();

        foreach ($this->getCollection() as $itm) {
            $row = array();

            $hasAll = true;
            $label = '';
            foreach ($this->columns as $column) {
                $value = $itm->getData($column->getField());

                if ($value == '') {
                    $hasAll = false;
                }

                $value = $this->_castValue($column, $value);


                if ($column->getType() == 'label') {
                    $label .= $column->getLabel() . ': ' . $value . '<br>';
                    $row['label'] = $label;
                } else {
                    $row[] = $value;
                }
            }

            if ($hasAll) {
                $series[] = $row;
            }
        }

        return $series;
    }
}
