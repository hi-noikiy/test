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



/**
 * @method Mirasvit_Rma_Model_Offline_Order getFirstItem()
 * @method Mirasvit_Rma_Model_Offline_Order getLastItem()
 * @method Mirasvit_Rma_Model_Resource_Offline_Order_Collection|Mirasvit_Rma_Model_Offline_Order[] addFieldToFilter
 * @method Mirasvit_Rma_Model_Resource_Offline_Order_Collection|Mirasvit_Rma_Model_Offline_Order[] setOrder
 */
class Mirasvit_Rma_Model_Resource_Offline_Order_Collection extends Mage_Core_Model_Mysql4_Collection_Abstract
{
    /**
     * @return void
     */
    protected function _construct()
    {
        $this->_init('rma/offline_order');
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
        /** @var Mirasvit_Rma_Model_Offline_Order $item */
        foreach ($this as $item) {
            $arr[] = array('value' => $item->getId(), 'label' => $item->getName());
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
        /** @var Mirasvit_Rma_Model_Offline_Order $item */
        foreach ($this as $item) {
            $arr[$item->getId()] = $item->getName();
        }

        return $arr;
    }
}
