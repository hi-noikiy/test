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



class Mirasvit_Rma_Block_Rma_New_Step2_OrderList extends Mirasvit_Rma_Block_Rma_New_Step2
{
    /**
     * Returns array of selected items.
     *
     * @return array
     */
    public function getItems()
    {
        return Mage::helper('rma/rma_create_customer_step2PostDataProcessor')->getItems();
    }

    /**
     * @param int $id
     *
     * @return Mage_Sales_Model_Order
     */
    public function getOrderById($id)
    {
        $order = Mage::getModel('sales/order')->load($id);
        return $order;
    }


    /**
     * @param int $id
     *
     * @return Mirasvit_Rma_Model_Item
     */
    public function getOrderItemById($id)
    {
        $item = Mage::getModel('sales/order_item')->load($id);
        $item = Mage::getModel('rma/item')->initFromOrderItem($item);

        return $item;
    }

    /**
     * @param Mirasvit_Rma_Model_Item $item
     * @return bool|int
     */
    public function getIsAllowedToShow($item)
    {
        if (($item->getProductType() == 'bundle' && $this->getConfig()->getPolicyBundleOneByOne())
//            || $item->getQty() < 1 @todo check this
        ) {
            return false;
        }
        return true;
    }
}
