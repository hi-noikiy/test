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



class Mirasvit_Rma_Block_Adminhtml_Rma_Edit_Form_Offline_OrderList extends Mirasvit_Rma_Block_Adminhtml_Rma_Edit_Form
{
    /**
     * @return void
     */
    protected function _construct()
    {
        parent::_construct();
        $this->setTemplate('mst_rma/rma/edit/form/offline_order_list.phtml');
    }

    /**
     * @return Mirasvit_Rma_Model_Resource_Offline_Order_Collection|Mirasvit_Rma_Model_Offline_Order[]
     */
    public function getOrderCollection()
    {
        $orderIds = array(0);
        $collection = Mage::getModel('rma/offline_item')->getCollection()
                        ->addFieldToFilter('rma_id', $this->getRma()->getId());
        foreach($collection as $item) {
            $orderIds[] = $item->getOfflineOrderId();
        }

        $collection = Mage::getModel('rma/offline_order')->getCollection()
            ->addFieldToFilter('offline_order_id', $orderIds);
        return $collection;
    }


    /**
     * @param Mirasvit_Rma_Model_Offline_Order $order
     * @return Mirasvit_Rma_Model_Offline_Item[]|Mirasvit_Rma_Model_Resource_Offline_Item_Collection
     */
    public function getItemCollection(Mirasvit_Rma_Model_Offline_Order $order) {
        $collection = $order->getItemCollection()
            ->addFieldToFilter('rma_id', $this->getRma()->getId());
        return $collection;
    }


    /**
     * @return Mirasvit_Rma_Model_Config
     */
    public function getConfig() {
        return Mage::getSingleton('rma/config');
    }

    /**
     * @return boolean|int
     */
    public function getIsOfflineOrdersAllowed()
    {
        return $this->getConfig()->getGeneralIsOfflineOrdersAllowed();
    }


}
