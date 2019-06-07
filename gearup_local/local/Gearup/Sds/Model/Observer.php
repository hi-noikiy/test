<?php

class Gearup_Sds_Model_Observer
{
    public function addProductSds(Varien_Event_Observer $observer) {
        $product = $observer->getEvent()->getProduct();
        $cate = Mage::getSingleton('catalog/category_api');
        $cates = Mage::getModel('catalog/category')->getCollection();
        $cates->addAttributeToFilter('category_deal', 1);
        if ($product->getSameDayShipping()) {
            foreach ($cates as $catChild) {
                $cate->assignProduct($catChild->getId(), $product->getId());
            }
        } else {
            foreach ($cates as $catChild) {
                $cate->assignProduct($catChild->getId(), $product->getId());
                $cate->removeProduct($catChild->getId(), $product->getId());
            }
        }

        $this->checkQtychange($product);
    }

    public function addCategorySds(Varien_Event_Observer $observer) {
        $category = $observer->getEvent()->getCategory();
        $cate = Mage::getSingleton('catalog/category_api');
        $products = Mage::getModel('catalog/product')->getCollection();
        $products->addAttributeToFilter('same_day_shipping', 1);
        if ($category->getCategoryDeal()) {
            foreach ($products as $product) {
                if (!in_array($category->getId(), $product->getCategoryIds())){
                    $cate->assignProduct($category->getId(), $product->getId());
                }
            }
        }
    }

    public function checkQtychange($product, $prevoiusQty=NULL) {
//        $product = $observer->getEvent()->getProduct();
        if (!$product->getDxbs()) {
            return;
        }
        if (!$prevoiusQty) {
            if ($product->getStockData('original_inventory_qty') != $product->getStockData('qty')) {
                $track = Mage::getModel('gearup_sds/tracking');
                $hastrack = $track->load($product->getId(), 'product_id');
                if ($hastrack->getData('sds_tracking_id')) {
                    $hastrack->setUpdateLastAt(Mage::getModel('core/date')->date('Y-m-d H:i:s'));
                    $hastrack->save();
                } else {
                    $track->setProductId($product->getId());
                    $track->setUpdateLastAt(Mage::getModel('core/date')->date('Y-m-d H:i:s'));
                    $track->save();
                }
            }
        } else {
            $stockItem = Mage::getModel('cataloginventory/stock_item')->loadByProduct($product->getId());
            if ($stockItem->getData('qty') != $prevoiusQty) {
                $track = Mage::getModel('gearup_sds/tracking');
                $hastrack = $track->load($product->getId(), 'product_id');
                if ($hastrack->getData('sds_tracking_id')) {
                    $hastrack->setUpdateLastAt(Mage::getModel('core/date')->date('Y-m-d H:i:s'));
                    $hastrack->save();
                } else {
                    $track->setProductId($product->getId());
                    $track->setUpdateLastAt(Mage::getModel('core/date')->date('Y-m-d H:i:s'));
                    $track->save();
                }
            }
        }

    }

    public function updateSds(Varien_Event_Observer $observer) {
        $product = $observer->getEvent()->getProduct();
        if (!$product->getDxbs()) {
            return;
        }
        $model = Mage::getModel('catalog/product')->load($product->getEntityId());
        $previousSds = Mage::helper('gearup_sds')->sdsStatus($model->getSameDayShipping());
        if ($product->getStockData('qty') == 1) {
            $model->setData('same_day_shipping', 1);
            //$action = 'SDS set to YES : Previous SDS is '.$previousSds;
        } else {
            $model->setData('same_day_shipping', 0);
            //$action = 'SDS set to No : Previous SDS is '.$previousSds;
        }
        $model->save();
        //Mage::helper('gearup_sds')->recordHistory($model->getId(), $action);
    }

    public function catalogInventorySave(Varien_Event_Observer $observer)
    {
        if (Mage::app()->getRequest()->getControllerName() == 'sales_order_creditmemo' && Mage::app()->getRequest()->getActionName() == 'save') {
            return;
        }
        if (Mage::app()->getRequest()->getControllerName() == 'sales_order' && Mage::app()->getRequest()->getActionName() == 'addComment') {
            return;
        }
        if (Mage::app()->getRequest()->getControllerName() == 'mageworx_ordersedit_history' && Mage::app()->getRequest()->getActionName() == 'addComment') {
            return;
        }
        if (Mage::app()->getRequest()->getControllerName() == 'mageworx_ordersedit_edit' && Mage::app()->getRequest()->getActionName() == 'saveOrder') {
            return;
        }
        if (Mage::app()->getRequest()->getControllerName() == 'sds_sds' && Mage::app()->getRequest()->getActionName() == 'updatespqty') {
            return;
        }
        if (Mage::app()->getRequest()->getControllerName() == 'sales_order' && Mage::app()->getRequest()->getActionName() == 'cancel') {
            return;
        }
        if (Mage::app()->getRequest()->getControllerName() == 'sales_order' && Mage::app()->getRequest()->getActionName() == 'massCancel') {
            return;
        }
        
        $event = $observer->getEvent();
        $_item = $event->getItem();

        $product = $this->getProduct($_item->getProductId());
        $previousSds = Mage::helper('gearup_sds')->sdsStatus($product->getSameDayShipping());
        if (!$product->getDxbs()) {
            return;
        }
        $stockItem = Mage::getModel('cataloginventory/stock_item')->loadByProduct($product->getId());
        if (Mage::app()->getRequest()->getControllerName() != 'catalog_product' && Mage::app()->getRequest()->getActionName() != 'save') {
            if ($stockItem->getData('is_in_stock')) {
                    $product->setSameDayShipping(1);
                    Mage::helper('gearup_sds')->assignSDS($product->getId());
                    //$action = 'SDS set to YES and assign SDS label : Previous SDS is '.$previousSds;
            } else {
                $product->setSameDayShipping(0);
                Mage::helper('gearup_sds')->unassignSDS($product->getId());
                //$action = 'SDS set to No and remove SDS label : Previous SDS is '.$previousSds;
            }
        } else {
            if (!$stockItem->getData('is_in_stock') || !$product->getSameDayShipping()) {
                $product->setSameDayShipping(0);
                Mage::helper('gearup_sds')->unassignSDS($product->getId());
            } else if ($product->getSameDayShipping()) {
                Mage::helper('gearup_sds')->assignSDS($product->getId());
            }
        }
        $product->save();
        //Mage::helper('gearup_sds')->recordHistory($product->getId(), $action);
        if ((int)$_item->getData('qty') != (int)$_item->getOrigData('qty')) {
            $track = Mage::getModel('gearup_sds/tracking');
            $hastrack = $track->load($_item->getProductId(), 'product_id');
            if ($hastrack->getData('sds_tracking_id')) {
                $hastrack->setUpdateLastAt(Mage::getModel('core/date')->date('Y-m-d H:i:s'));
                $hastrack->save();
            } else {
                $track->setProductId($_item->getProductId());
                $track->setUpdateLastAt(Mage::getModel('core/date')->date('Y-m-d H:i:s'));
                $track->save();
            }
        }
        //Mage::log('Product id = '.$product->getPartNr().' qty = '.$stockItem->getData('qty').' controller = '.Mage::app()->getRequest()->getControllerName().' action = '.Mage::app()->getRequest()->getActionName().' when = '.Mage::getModel('core/date')->date('Y-m-d H:i:s'), null, 'productupdatesourceback.log');
    }

    public function catalogInventorySavefront(Varien_Event_Observer $observer)
    {
        $event = $observer->getEvent();
        $_item = $event->getItem();
        $product = $this->getProduct($_item->getProductId());
        $stockItem = Mage::getModel('cataloginventory/stock_item')->loadByProduct($product->getId());
        if (Mage::app()->getRequest()->getControllerName() == 'onepage' && Mage::app()->getRequest()->getActionName() == 'saveOrder' && Mage::app()->getRequest()->getRouteName() == 'checkout') {
            $previousSds = Mage::helper('gearup_sds')->sdsStatus($product->getSameDayShipping());
            if (!$product->getDxbs()) {
                return;
            }
                if ($stockItem->getData('is_in_stock')) {
                    //$product->setSameDayShipping(1);
                    Mage::helper('gearup_sds')->assignSDS($product->getId());
                    //$action = 'Assign SDS label : Previous SDS is '.$previousSds;
                } else {
                    //$product->setSameDayShipping(0);
                    Mage::helper('gearup_sds')->unassignSDS($product->getId());
                    //$action = 'Remove SDS label : Previous SDS is '.$previousSds;
                }
            //$product->save();
            //Mage::helper('gearup_sds')->recordHistory($product->getId(), $action);
            if ((int)$_item->getData('qty') != (int)$_item->getOrigData('qty')) {
                $track = Mage::getModel('gearup_sds/tracking');
                $hastrack = $track->load($_item->getProductId(), 'product_id');
                if ($hastrack->getData('sds_tracking_id')) {
                    $hastrack->setUpdateLastAt(Mage::getModel('core/date')->date('Y-m-d H:i:s'));
                    $hastrack->save();
                } else {
                    $track->setProductId($_item->getProductId());
                    $track->setUpdateLastAt(Mage::getModel('core/date')->date('Y-m-d H:i:s'));
                    $track->save();
                }
            }
        } else {
            if (!Mage::app()->getRequest()->getControllerName() && !Mage::app()->getRequest()->getActionName()) {
                if (!$product->getDxbs()) {
                    return;
                }
                $stockItem = Mage::getModel('cataloginventory/stock_item')->loadByProduct($product->getId());
                if ($stockItem->getData('is_in_stock')) {
                    $status = 'In stock';
                } else {
                    $status = 'Out of stock';
                }
                $statusSds = Mage::helper('gearup_sds')->sdsStatus($product->getSameDayShipping());
                $action = 'Product had changed from some programmatically script or cron updated not from magento, "'.round($stockItem->getData('qty')).' QTY '.$status.' SDS is '.$statusSds.'"';
                Mage::helper('gearup_sds')->recordHistory($product->getId(), $action, round($stockItem->getData('qty')), round($stockItem->getData('qty')));
            }
            //Mage::log('Product id = '.$product->getPartNr().' qty = '.$stockItem->getData('qty').' controller = '.Mage::app()->getRequest()->getControllerName().' action = '.Mage::app()->getRequest()->getActionName().' when = '.Mage::getModel('core/date')->date('Y-m-d H:i:s'), null, 'productupdatesource.log');
        }
    }

    public function cancelOrderItem(Varien_Event_Observer $observer)
    {
        $item = $observer->getEvent()->getItem();
        //$qty = $item->getQtyOrdered() - max($item->getQtyShipped(), $item->getQtyInvoiced()) - $item->getQtyCanceled();
        $product = $this->getProduct($item->getProductId());
        $previousSds = Mage::helper('gearup_sds')->sdsStatus($product->getSameDayShipping());
        $order = Mage::getModel('sales/order')->load($item->getOrderId());
        $incrementId = $order->getIncrementId();
        if (!$product->getDxbs()) {
            return;
        }
        if ($order->getStatus() == Mage_Sales_Model_Order::STATE_CANCELED) {
            return;
        }

        $stockItem = Mage::getModel('cataloginventory/stock_item')->loadByProduct($product->getId());     
        $horderSDS = Mage::helper('gearup_sds')->getHorder($product, $order->getId());

        // check back to stock
        if (Mage::getStoreConfigFlag(Mage_CatalogInventory_Model_Stock_Item::XML_PATH_CAN_BACK_IN_STOCK)) {
           
            if ($horderSDS[0]['sds']) {
                //Below condition is used as magento add qty to stock on order cancellation. Where as we have modified that logix for sds products. An without
                //this condition was resulting in adding order qty twice in stock. //
                 $stockItem->setQty($stockItem->getQty() - $item->getQtyOrdered());      
                //end//
                if (!$product->getSameDayShipping()) {
                    $stockItem->setQty($item->getQtyOrdered());
                }else{
                    $stockItem->setQty($stockItem->getQty() + $item->getQtyOrdered());                          
                }
                
                $stockItem->setIsInStock(1);
                $product->setSameDayShipping(1);
                Mage::helper('gearup_sds')->assignSDS($product->getId());
            } else if (!$horderSDS[0]['sds']) {
                if (!$product->getSameDayShipping()) {
                    $stockItem->setQty($stockItem->getQty());
                    $stockItem->setIsInStock(1);
                    Mage::helper('gearup_sds')->unassignSDS($product->getId());
                }
            }
        }

        $stockItem->save();
        $product->save();
        $statusSds = Mage::helper('gearup_sds')->sdsStatus($product->getSameDayShipping());
        $prevoiusQty = $stockItem->getData('qty') - $item->getQtyOrdered();
        $action = '<strong>Cancel Order : '.$incrementId.'</strong>, Update QTY to ' . round($stockItem->getData('qty')) . ' In Stock; SDS is '.$statusSds;
        Mage::helper('gearup_sds')->recordHistory($product->getId(), $action, $prevoiusQty, round($stockItem->getData('qty')), $incrementId);
        $track = Mage::getModel('gearup_sds/tracking');
        $hastrack = $track->load($product->getId(), 'product_id');
        if ($hastrack->getData('sds_tracking_id')) {
            $hastrack->setUpdateLastAt(Mage::getModel('core/date')->date('Y-m-d H:i:s'));
            $hastrack->save();
        } else {
            $track->setProductId($product->getId());
            $track->setUpdateLastAt(Mage::getModel('core/date')->date('Y-m-d H:i:s'));
            $track->save();
        }
    }

    public function refundOrderInventory(Varien_Event_Observer $observer)
    {
        $creditmemo = $observer->getEvent()->getCreditmemo();
        foreach ($creditmemo->getAllItems() as $item) {
            $product = $this->getProduct($item->getProductId());
            $order = Mage::getModel('sales/order')->load(Mage::app()->getRequest()->getParam('order_id'));
            $previousSds = Mage::helper('gearup_sds')->sdsStatus($product->getSameDayShipping());
            if (!$product->getDxbs()) {
                continue;
            }

            $horderSDS = Mage::helper('gearup_sds')->getHorder($product, $order->getId());
            $stockItem = Mage::getModel('cataloginventory/stock_item')->loadByProduct($product->getId());
            
            /*if ($stockItem->getData('is_in_stock') && $horderSDS[0]['sds']) {
                $previousQty = $stockItem->getData('qty') - $item->getQty();
            }else{
                if (!$horderSDS[0]['sds'] && !$product->getSameDayShipping()) {
                    $stockItem->setData('qty', $item->getQty());
                    $stockItem->save();
                }
            }*/

            //$horderSDS = Mage::helper('gearup_sds')->getHorder($product, $order->getId());
            if ($horderSDS[0]['sds']) {
                if (!$product->getSameDayShipping()) {
                    $stockItem->setQty($item->getQty());
                }else
                    $stockItem->setQty($stockItem->getQty() + $item->getQty());                            
                
                $stockItem->setIsInStock(1);
                $product->setSameDayShipping(1);
                Mage::helper('gearup_sds')->assignSDS($product->getId());
            } else if (!$horderSDS[0]['sds']) {
                if (!$product->getSameDayShipping()) {
                    $stockItem->setQty($stockItem->getQty() + $item->getQtyOrdered());
                    $stockItem->setIsInStock(1);
                    Mage::helper('gearup_sds')->unassignSDS($product->getId());
                }
            }
            
            /*$horderSDS = Mage::helper('gearup_sds')->getHorder($product, $order->getId());
            if ($stockItem->getData('qty') > 0 && $horderSDS[0]['sds']) {
                if ($item->getBackToStock()) {
                    $stockItem->setIsInStock(1);
                }
                $product->setSameDayShipping(1);
                Mage::helper('gearup_sds')->assignSDS($product->getId());
            } else if ($stockItem->getData('qty') > 0 && !$horderSDS[0]['sds']) {
                if ($item->getBackToStock()) {
                    $stockItem->setIsInStock(1);
                }
                Mage::helper('gearup_sds')->unassignSDS($product->getId());
            } else {
                $product->setSameDayShipping(0);
                Mage::helper('gearup_sds')->unassignSDS($product->getId());
            }*/

            $stockItem->save();
            $product->save();

            $qty = $stockItem->getQty();
            $prevQty = $stockItem->getQty() - $item->getQty();
            $statusSds = Mage::helper('gearup_sds')->sdsStatus($product->getSameDayShipping());
            if ($order->getPayment()->getMethodInstance()->getCode() != 'cashondelivery') {
                $action = '<strong>Cancel Order : '.$order->getIncrementId().'</strong>, Update QTY to ' . round($stockItem->getData('qty')) . ' In Stock; SDS is '.$statusSds;
                Mage::helper('gearup_sds')->recordHistory($product->getId(), $action, $prevQty, $qty, $order->getIncrementId());
            }
            $track = Mage::getModel('gearup_sds/tracking');
            $hastrack = $track->load($product->getId(), 'product_id');
            if ($hastrack->getData('sds_tracking_id')) {
                $hastrack->setUpdateLastAt(Mage::getModel('core/date')->date('Y-m-d H:i:s'));
                $hastrack->save();
            } else {
                $track->setProductId($product->getId());
                $track->setUpdateLastAt(Mage::getModel('core/date')->date('Y-m-d H:i:s'));
                $track->save();
            }
       }
    }

    public function orderSubmit(Varien_Event_Observer $observer)
    {
        $orderIncrementId = $observer->getEvent()->getOrder()->getIncrementId();
        $order = Mage::getModel('sales/order')->loadByIncrementId($orderIncrementId);
        $items = $order->getAllVisibleItems();
        foreach ($items as $item) {
            $product = $this->getProduct($item->getProductId());
            //Mage::getSingleton('core/session')->setQuoteSds($product->getSameDayShipping());
            if (!$product->getDxbs()) {
                continue;
            }
            //------------ check stock level and set SDS ------------//
            $stockItem = Mage::getModel('cataloginventory/stock_item')->loadByProduct($product->getId());
            /*if ($stockItem->getData('is_in_stock')) {
                $product->setSameDayShipping(1);
                Mage::helper('gearup_sds')->assignSDS($product->getId());
            } else {
                $product->setSameDayShipping(0);
                Mage::helper('gearup_sds')->unassignSDS($product->getId());
            }
            $product->save();*/
            $track = Mage::getModel('gearup_sds/tracking');
            $hastrack = $track->load($product->getId(), 'product_id');
            if ($hastrack->getData('sds_tracking_id')) {
                $hastrack->setUpdateLastAt(Mage::getModel('core/date')->date('Y-m-d H:i:s'));
                $hastrack->setOrderId($order->getEntityId());
                $hastrack->save();
            } else {
                $track->setProductId($product->getId());
                $track->setUpdateLastAt(Mage::getModel('core/date')->date('Y-m-d H:i:s'));
                $track->setOrderId($order->getEntityId());
                $track->save();
            }
            //Mage::log('Product id = '.$product->getId().' submit updated when = '.Mage::getModel('core/date')->date('Y-m-d H:i:s'), null, 'dxbsupdate.log');
       }
    }

    public function orderSuccess(Varien_Event_Observer $observer)
    {
        $orderIds = $observer->getEvent()->getOrderIds();
        $order = Mage::getModel('sales/order')->load($orderIds[0]);
        $items = $order->getAllVisibleItems();
        foreach ($items as $item) {
            $product = $this->getProduct($item->getProductId());
            $previousSds = Mage::helper('gearup_sds')->sdsStatus($product->getSameDayShipping());
            if (!$product->getDxbs()) {
                continue;
            }

            //------------ check stock level and set SDS ------------//
            /*$stockItem = Mage::getModel('cataloginventory/stock_item')->loadByProduct($product->getId());
            if ($stockItem->getData('is_in_stock')) {
                $product->setSameDayShipping(1);
                Mage::helper('gearup_sds')->assignSDS($product->getId());
                //$action = 'SDS set to YES and assign SDS label : Previous SDS is '.$previousSds;
            } else {
                $product->setSameDayShipping(0);
                Mage::helper('gearup_sds')->unassignSDS($product->getId());
                //$action = 'SDS set to No and remove SDS label : Previous SDS is '.$previousSds;
            }
            $product->save();*/
            //Mage::helper('gearup_sds')->recordHistory($product->getId(), $action);
            $track = Mage::getModel('gearup_sds/tracking');
            $hastrack = $track->load($product->getId(), 'product_id');
            if ($hastrack->getData('sds_tracking_id')) {
                $hastrack->setUpdateLastAt(Mage::getModel('core/date')->date('Y-m-d H:i:s'));
                $hastrack->setOrderId($order->getEntityId());
                $hastrack->save();
            } else {
                $track->setProductId($product->getId());
                $track->setUpdateLastAt(Mage::getModel('core/date')->date('Y-m-d H:i:s'));
                $track->setOrderId($order->getEntityId());
                $track->save();
            }
            //Mage::log('Product id = '.$product->getId().' success updated when = '.Mage::getModel('core/date')->date('Y-m-d H:i:s'), null, 'dxbsupdate.log');
       }
    }

    public function getProduct($id) {
        return Mage::getModel('catalog/product')->load($id);
    }

    public function saveComment(Varien_Event_Observer $observer)
    {
        if (Mage::app()->getRequest()->getControllerName() == 'onepage' && Mage::app()->getRequest()->getActionName() == 'saveOrder') {
            return;
        }

        $order = $observer->getEvent()->getOrder();
        $commentpost = Mage::app()->getRequest()->getParam('history');

        if ($commentpost['status'] == Mage_Sales_Model_Order::STATE_CANCELED) {
            //$items = $order->getAllVisibleItems();
            $items = $order->getAllItems();
            foreach ($items as $item) {
                $product = $this->getProduct($item->getProductId());
                if (!$product->getDxbs()) {
                    continue;
                }
                $stockItem = Mage::getModel('cataloginventory/stock_item')->loadByProduct($product->getId());
                $histories = Mage::getModel('gearup_sds/history')->getCollection();
                $histories->addFieldToFilter('product_id', array('eq'=>$product->getId()));
                $histories->addFieldToFilter('order_id', array('eq'=>$order->getIncrementId()));
                $histories->addFieldToFilter('actions', array('like'=>'%Cancel Order%'));
                if ($histories->getSize()) {
                    foreach ($histories as $history) {
                        $history->delete();
                    }
                    $statusSds = Mage::helper('gearup_sds')->sdsStatus($product->getSameDayShipping());
                    $prevQty = $stockItem->getData('qty') - $item->getQtyOrdered();
                    $action = '<strong>Cancel Order : '.$order->getIncrementId().'</strong>, Update QTY to ' . round($stockItem->getData('qty')) . ' In Stock; SDS is '.$statusSds;
                    Mage::helper('gearup_sds')->recordHistory($product->getId(), $action, $prevQty, round($stockItem->getData('qty')), $order->getIncrementId());
                    continue;
                }
                //------------ check stock level and set SDS ------------//
                $prevoiusQty = $stockItem->getData('qty') - $item->getQtyOrdered();
                $diff = ', '.Mage::helper('gearup_sds')->diffItem($prevoiusQty, $stockItem->getData('qty'));
                $horderSDS = Mage::helper('gearup_sds')->getHorder($product, $order->getId());
                if ($horderSDS[0]['sds']) {
                    if (!$product->getSameDayShipping()) {
                        $stockItem->setQty($item->getQtyOrdered());
                    }else{
                        $stockItem->setQty($stockItem->getQty() + $item->getQtyOrdered());                            
                    }
                    
                    $stockItem->setIsInStock(1);
                    $product->setSameDayShipping(1);
                    Mage::helper('gearup_sds')->assignSDS($product->getId());
                } else if (!$horderSDS[0]['sds']) {
                    if (!$product->getSameDayShipping()) {
                        $stockItem->setQty($stockItem->getQty() + $item->getQtyOrdered());
                        $stockItem->setIsInStock(1);
                        Mage::helper('gearup_sds')->unassignSDS($product->getId());
                    }
                }
                $stockItem->save();
                $product->save();
                $statusSds = Mage::helper('gearup_sds')->sdsStatus($product->getSameDayShipping());        

                $prevQty = $stockItem->getData('qty') - $item->getQtyOrdered();

                $action = '<strong>Cancel Order : '.$order->getIncrementId().'</strong>, Update QTY to ' . round($stockItem->getData('qty')) . ' In Stock; SDS is '.$statusSds;
                Mage::helper('gearup_sds')->recordHistory($product->getId(), $action, $prevQty, round($stockItem->getData('qty')), $order->getIncrementId());
                $track = Mage::getModel('gearup_sds/tracking');
                $hastrack = $track->load($product->getId(), 'product_id');
                if ($hastrack->getData('sds_tracking_id')) {
                    $hastrack->setUpdateLastAt(Mage::getModel('core/date')->date('Y-m-d H:i:s'));
                    $hastrack->setOrderId($order->getEntityId());
                    $hastrack->save();
                } else {
                    $track->setProductId($product->getId());
                    $track->setUpdateLastAt(Mage::getModel('core/date')->date('Y-m-d H:i:s'));
                    $track->setOrderId($order->getEntityId());
                    $track->save();
                }
            }
        }
    }

    public function afterComment(Varien_Event_Observer $observer)
    {
        if (Mage::app()->getRequest()->getControllerName() == 'onepage' && Mage::app()->getRequest()->getActionName() == 'saveOrder') {
            return;
        }

        $event = $observer->getEvent();
        $admin = Mage::getSingleton('admin/session')->getUser();

        $resource = Mage::getSingleton('core/resource');
        $readConnection = $resource->getConnection('core_read');
        $writeConnection = $resource->getConnection('core_write');
        $table = $resource->getTableName('sales_flat_order_status_history');
        $table2 = $resource->getTableName('gearup_sales_commentby');
        try {
            $searchquery = "SELECT * FROM `{$table}` WHERE `parent_id` = ".$event->getOrder()." AND `comment` LIKE '".$event->getHistory()."';";
            $searchs = $readConnection->fetchAll($searchquery);
            if ($searchs) {
                foreach ($searchs as $search) {
                    /*var_dump($search->getData());
                    die();*/
                    $query = "INSERT INTO `{$table2}` (`commentby_id`, `parent_id`, `user`, `status`) VALUES (NULL, '".$search->getEntityId()."', '".$admin->getFirstname()."', '1');";
                    $writeConnection->query($query);
                }
            }
        } catch (Exception $exc) {
            /*var_dump($exc);
            die();*/
        }



    }
}
