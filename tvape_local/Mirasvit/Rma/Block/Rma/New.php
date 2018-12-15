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



class Mirasvit_Rma_Block_Rma_New extends Mage_Core_Block_Template
{
    /**
     * @return Mirasvit_Rma_Helper_Rma_Create_AbstractNewControllerStrategy
     */
    protected function getStrategy() {
        return Mage::registry('newControllerStrategy');
    }

    /**
     */
    protected function _prepareLayout()
    {
        parent::_prepareLayout();
        if ($headBlock = $this->getLayout()->getBlock('head')) {
            $headBlock->setTitle(Mage::helper('rma')->__('Request new return'));
        }
    }

    /**
     * @return Mirasvit_Rma_Model_Config
     */
    protected function getConfig()
    {
        return Mage::getSingleton('rma/config');
    }

    /**
     * @return Mage_Customer_Model_Customer
     */
    protected function getCustomer()
    {
        return Mage::getSingleton('customer/session')->getCustomer();
    }

    /**
     * @return int
     */
    public function getStoreId()
    {
        return Mage::app()->getStore()->getId();
    }

    /**
     * @param int $orderItem
     *
     * @return Mirasvit_Rma_Model_Item[]|Mirasvit_Rma_Model_Resource_Item_Collection
     */
    public function getRmaItemsByOrderItem($orderItem)
    {
        $collection = Mage::getModel('rma/item')->getCollection();
        $collection->addFieldToFilter('order_item_id', $orderItem->getId());
        $collection->addFieldToFilter('qty_requested', array('gt' => 0));

        return $collection;
    }

    /**
     * @param Mirasvit_Rma_Model_Rma $rma
     *
     * @return string
     */
    public function getRMAUrl($rma)
    {
        return $rma->getUrl();
    }

    /**
     * @param int $orderItem
     *
     * @return string
     */
    public function getRmasByOrderItem($orderItem)
    {
        $result = array();
        foreach ($this->getRmaItemsByOrderItem($orderItem) as $item) {
            $rma = Mage::getModel('rma/rma')->load($item->getRmaId());
            $result[] = "<a href='{$this->getRMAUrl($rma)}' target='_blank'>#{$rma->getIncrementId()}</a>";
        }

        return implode(', ', $result);
    }
}
