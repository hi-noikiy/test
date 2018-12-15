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



class Mirasvit_Rma_Block_Adminhtml_Rma_Edit_Form_OrderList extends Mirasvit_Rma_Block_Adminhtml_Rma_Edit_Form
{
    /**
     * @return void
     */
    protected function _construct()
    {
        parent::_construct();
        $this->setTemplate('mst_rma/rma/edit/form/order_list.phtml');
    }

    /**
     * @return Mage_Sales_Model_Order[]
     */
    public function getOrderCollection()
    {
        $collection = Mage::getModel('sales/order')->getCollection();
        if ($this->getRma()->getId()) {
            if ($this->getRma()->getCustomerId()) {
                $collection->addFieldToFilter(array('customer_id', 'customer_email'),
                    array($this->getRma()->getCustomerId(), $this->getRma()->getEmail()));
            } else {
                $collection->addFieldToFilter('customer_email', $this->getRma()->getEmail());
            }
        } else {
            if ($this->getRma()->getOrdersId()) {
                $collection->addFieldToFilter('entity_id', $this->getRma()->getOrdersId());
            } else {
                $collection->addFieldToFilter('entity_id', -1);
            }
        }
        return $collection;
    }

    /**
     * @param Mage_Sales_Model_Order $order
     * @return Mirasvit_Rma_Model_Resource_Item_Collection|Mirasvit_Rma_Model_Item[]
     */
    public function getItems($order) {
        return Mage::helper('rma')->getRmaItems($order, $this->getRma());
//        if ($this->getRma()->getId()) {
//            return Mage::helper('rma')->getRmaItems($order, $this->Rma)
//            $collection = Mage::getModel('rma/item')->getCollection()
//                ->addFieldToFilter('rma_id', $this->getRma()->getRmaId())
//                ->addFieldToFilter('order_id', $order->getId());
//            return $collection;
//        } else {
//            $items = array();
//            foreach($order->getItemsCollection() as $orderItem) {
//                $item = Mage::getModel('rma/item')->initFromOrderItem($orderItem);
//                $items[] = $item;
//            }
//            return $items;
//        }
    }


    /**
     * @param Mage_Sales_Model_Order $order
     * @return string
     */
    public function getOrderUrl($order) {
        return $this->getUrl('adminhtml/sales_order/view', array('order_id' => $order->getId()));
    }

    /**
     * @param Mage_Sales_Model_Order $order
     * @return Mage_Sales_Model_Order_Creditmemo[]
     */
    public function getCreditmemos($order) {
        $rma = $this->getRma();
        $creditmemos = array();
        if ($rma->getCreditMemoIds()) {
            foreach ($rma->getCreditMemoIds() as $id) {
                $creditmemo = Mage::getModel('sales/order_creditmemo')->load($id);
                if ($creditmemo->getOrderId() == $order->getId()) {
                    $creditmemos[] = $creditmemo;
                }
            }
        }
        return $creditmemos;
    }

    /**
     * @param Mage_Sales_Model_Order_Creditmemo $creditmemo
     * @return string
     */
    public function getCreditmemoViewUrl($creditmemo)
    {
        return $this->getUrl(
            'adminhtml/sales_creditmemo/view',
            array('creditmemo_id' => $creditmemo->getId())
        );
    }
}
