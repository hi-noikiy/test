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
 * @package   mirasvit/extension_rma
 * @version   2.4.7
 * @copyright Copyright (C) 2017 Mirasvit (https://mirasvit.com/)
 */



class Mirasvit_Rma_Model_Resource_Return_Address_Collection extends Mage_Core_Model_Mysql4_Collection_Abstract
{
    /**
     * Constructor
     * @return void
     */
    protected function _construct()
    {
        $this->_init('rma/return_address');
    }

    /**
     * @param bool $emptyOption
     * @return array
     */
    public function toOptionArray($emptyOption = false)
    {
        $arr = array();
        if ($emptyOption) {
            $arr[0] = array('value' => 0, 'label' => Mage::helper('rma')->__('-- Please Select --'));
        }
        /** @var Mirasvit_Rma_Model_Return_Address $item */
        foreach ($this as $item) {
            if ($item->getIsActive()) {
                $arr[] = array('value' => $item->getId(), 'label' => $item->getTitle());
            }
        }

        return $arr;
    }

    /**
     * @param bool $emptyOption
     * @return array
     */
    public function getOptionArray($emptyOption = false)
    {
        $arr = array();
        if ($emptyOption) {
            $arr[0] = Mage::helper('rma')->__('-- Please Select --');
        }
        /** @var Mirasvit_Rma_Model_Return_Address $item */
        foreach ($this as $item) {
            $arr[$item->getId()] = $item->getTitle();
        }

        return $arr;
    }

}