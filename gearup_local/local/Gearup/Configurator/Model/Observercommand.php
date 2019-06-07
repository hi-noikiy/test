<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

class Gearup_Configurator_Model_Observercommand {

    public function setConfiguratorTemplate($observer) {
        $action = $observer->getEvent()->getAction();
        $layout = $observer->getEvent()->getLayout();
        if ($action->getFullActionName() == 'catalog_product_view') {
            $productId = Mage::registry('current_product')->getId();
            $flag = false;

            $product = Mage::getModel('catalog/product')->load($productId);


            foreach ($product->getOptions() as $o) {
                $optionType = $o->getType();
                if ($optionType == 'configurator') {
                    $flag = true;
                    break;
                }
            }

            if ($flag) {
                $root = $layout->getBlock('root');
                if ($root) {
                    $root->setTemplate('page/1column.phtml');
                    $layout->getBlock('product.info')->unsetChild('media');
                    $layout->getBlock('product.info')->unsetChild('upsell_products');
                    $staticBlock = $layout->createBlock('cms/block', 'Promo')->setBlockId('configurator-hide-blocks');
                    $rightRenderer = $layout->createBlock('configurator/renderer/summary', 'summery')
                            ->setTemplate('configurator/renderer/summary.phtml');
                    $rightRenderer->append($layout->createBlock('catalog/product_view', 'prices')
                                    ->setTemplate('catalog/product/view/price_clone.phtml'));
                    $rightRenderer->append($layout->createBlock('catalog/product_view', 'summary-addtocart')
                                    ->setTemplate('catalog/product/view/addtocart.phtml'));
                    $configuratorForm = $layout->createBlock('core/template', 'configurator.contact_form')
                            ->setTemplate('configurator/contact_form.phtml');
                    $layout->getBlock('content')->append($staticBlock);
                    $layout->getBlock('content')->append($rightRenderer);
                    $layout->getBlock('content')->append($configuratorForm);
                }
            }
        }
    }

    public function updateConfiguratorProducts($orderId) {
        //$order = $observer->getEvent()->getOrder();
        $order = Mage::getModel('sales/order')->loadByIncrementId($orderId);
        //get order

        //$cart = Mage::getModel('checkout/cart')->getQuote();

        //if (!Mage::registry('gearup_configurator_customorder' . $order->getId())) {
            foreach ($order->getAllItems() as $item){
                $this->isConfigurator($item, $order);
            }
            //Mage::register('gearup_configurator_customorder' . $order->getId(), true);
        //}
            echo 'done';
    }

    protected function isConfigurator($product, $order) {
        $customOption = $product->getProduct()->getOptions();
        $cartHelper = Mage::helper('checkout/cart');
        //var_dump($customOption);
        foreach ($customOption as $o) {
            $optionType = $o->getType();
            if ($optionType == 'configurator') {
                $optionId = $o->getOptionId();
                $options = unserialize($product->getData('product_options'));
                echo'<pre>';
                foreach ($options['info_buyRequest']['options'] as $option) {
                    foreach ($option as $templates) {
                        foreach ($templates['template'] as $templete) {
                            $templateOptionModel = Mage::getModel('configurator/value')->load($templete);
                            $productId = $templateOptionModel->getProductId();
                            if ($productId) {
                                $subProduct = Mage::getModel('catalog/product')->load($productId);
                                $stock = Mage::getModel('cataloginventory/stock_item')->loadByProduct($subProduct);
                                $previousQty = $stock->getQty();

                                $this->_salesOrderProcessing($subProduct, $product->getQty(), $order, $previousQty);
                                // Save
                            }
                        }
                    }
                }
            }
        }
    }

    public function modifyPrice(Varien_Event_Observer $obs) {
        // Get the quote item

        $quote = $obs->getEvent()->getQuote();
        $item = $obs->getQuoteItem();
        $this->_calculateConfiguratorPricing($quote, $item);
    }

    public function hookToControllerActionPostDispatch($observer) {
        if ($observer->getEvent()->getControllerAction()->getFullActionName() == 'directory_currency_switch') {
            $quote = Mage::getSingleton('checkout/session')->getQuote();
            if ($quote && $quote->hasItems()) {
                foreach ($quote->getAllVisibleItems() as $item):
                    //add condition for target item
                    $this->_calculateConfiguratorPricing($quote, $item, true);

                endforeach;
            }
//             if($currencySwitch == true)
            $quote->collectTotals()->save();
        }
    }

    private function _calculateConfiguratorPricing($quote, $item, $currencySwitch = false) {
        $product_id = $item->getProductId();
        $_product = Mage::getModel('catalog/product')->load($product_id);
//        $newprice = $_product->getPrice();

        $connection = Mage::getSingleton('core/resource')->getConnection('core_read');
        $optionValueTable = Mage::getSingleton("core/resource")->getTableName('configurator/option_value');
        $requestData = $item->getBuyRequest()->getData();


        $qty = $requestData['qty'];
        if ($qty == 0) {
            $qty = 1;
        }
        $options = $_product->getOptions();
        $helper = Mage::helper('configurator');
        $price = 0;
        foreach ($options as $option) {
            if ($option->getType() == 'configurator') {
                $optionId = $option->getOptionId();

                $code = Mage::app()->getStore()->getCurrentCurrencyCode();
                if (isset($requestData['options'][$optionId])) {
                    foreach ($requestData['options'][$optionId] as $templateJsId => $template) {
                        $templateOptionValues = $template['template'];
                        foreach ($templateOptionValues as $templateOptionId => $templateOptionValue) {
                            try {
                                $optionValuesSelect = $connection->select()
                                        ->from(array('cov' => $optionValueTable), array('product_id'));
                                $optionValuesSelect->where('cov.id = ?', $templateOptionValue);
                                $productId = $connection->fetchOne($optionValuesSelect);
                                //echo $optionValuesSelect->getSelect();exit;
                            } catch (Exception $e) {
                                //echo $e->getMessage();exit;
                            }

                            if ($productId !== null) {
                                $alt_product = Mage::getModel('catalog/product')->load($productId);
                                $price += $helper->priceConvert($alt_product->getFinalPrice());
                            }
                        }
                        $price += $helper->priceConvert($_product->getFinalPrice());
                        $item->setCustomPrice($price);
                        $item->setOriginalCustomPrice($price);
//                        // Enable super mode on the product.
                        $item->getProduct()->setIsSuperMode(true);
                    }
                }
            }
        }
    }

    public function addCanceledProductsBackInStock(Varien_Event_Observer $observer) {
        $order = $observer->getEvent()->getOrder();
        if ($order->getStatus() == Mage_Sales_Model_Order::STATE_CANCELED) {
            foreach ($order->getAllItems() as $item) {
                if ($result = $this->_checkIfConfigurator($item)) {
                    $requestData = $result['requestData'];
                    $optionId = $result['optionId'];
                    if (isset($requestData['options'][$optionId])) {
                        foreach ($requestData['options'][$optionId] as $templateJsId => $template) {
                            $templateOptionValues = $template['template'];
                            foreach ($templateOptionValues as $templateOptionId => $templateOptionValue) {
                                $templateOptionModel = Mage::getModel('configurator/value')->load($templateOptionValue);
                                $productId = $templateOptionModel->getProductId();
                                if ($productId) {
                                    $subProduct = Mage::getModel('catalog/product')->load($productId);
                                    $this->_SDSProcessing($subProduct, $order, $item->getQtyOrdered());
                                }
                            }
                        }
                    }
                }
            }
        }
    }

//    public function addCreditmemoProductsBackInStock() {
//
//        $creditmemo = $observer->getEvent()->getCreditmemo();
//        foreach ($creditmemo->getAllItems() as $item) {
//            if ($result = $this->_checkIfConfigurator($item)) {
//
//                $requestData = $result['requestData'];
//                $optionId = $result['optionId'];
//                if (isset($requestData['options'][$optionId])) {
//                    foreach ($requestData['options'][$optionId] as $templateJsId => $template) {
//                        $templateOptionValues = $template['template'];
//                        foreach ($templateOptionValues as $templateOptionId => $templateOptionValue) {
//                            $templateOptionModel = Mage::getModel('configurator/value')->load($templateOptionValue);
//                            $productId = $templateOptionModel->getProductId();
//                            if ($productId) {
//                                $subProduct = Mage::getModel('catalog/product')->load($productId);
//                                $this->_SDSProcessing($subProduct, $creditmemo->getOrder(), $item->getQtyOrdered(), $item->getBackToStock());
//                            }
//                        }
//                    }
//                }
//            }
//        }
//    }

    /**
     *
     * @param type $product
     * @param type $order
     * @param type $qtyOrdered
     * @param type $backInStock
     * @return _SDSProcessing
     */
    public function _SDSProcessing($product, $order, $qtyOrdered = false, $backInStock = true) {

        $stockItem = Mage::getModel('cataloginventory/stock_item')->loadByProduct($product->getId());
        $prevoiusQty = '';
        if ($backInStock)
                $prevoiusQty = $stockItem->getData('qty') + $qtyOrdered;

        if (!$product->getDxbs()) {
            $stockItem->setQty($prevoiusQty);
            $stockItem->setIsInStock(1);
            $stockItem->save();
            return;
        }
        $stockItem->setQty($prevoiusQty);

        $horderSDS = Mage::helper('gearup_sds')->getHorder($product, $order->getId());

        if ($stockItem->getData('qty') > 0 && $horderSDS[0]['sds'] && $backInStock) {
            $stockItem->setIsInStock(1);
            $product->setSameDayShipping(1);
            Mage::helper('gearup_sds')->assignSDS($product->getId());
        } else if ($stockItem->getData('qty') > 0 && !$horderSDS[0]['sds'] && $backInStock) {
            $stockItem->setIsInStock(1);
            Mage::helper('gearup_sds')->unassignSDS($product->getId());
        } else {
            $product->setSameDayShipping(0);
            Mage::helper('gearup_sds')->unassignSDS($product->getId());
        }

        $stockItem->save();
        $product->save();
        if ($order->getPayment()->getMethodInstance()->getCode() != 'cashondelivery' && $backInStock) {
            if ($backInStock){
                $prevQty = $stockItem->getData('qty') - $qtyOrdered;
            }else{
                $prevQty = $stockItem->getData('qty');
            }
                        
            $action = '<strong>Cancel Order : ' . $order->getIncrementId() . '</strong>, Update QTY to ' . round($stockItem->getData('qty')) . ' In Stock; SDS is '.($product->getSameDayShipping()?"Yes":'No');
            Mage::helper('gearup_sds')->recordHistory($product->getId(), $action, $prevQty, round($stockItem->getData('qty')), $order->getIncrementId());
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

    /**
     *
     * @param type $product
     * @param type $qtyOrdered
     * @param type $order
     * @param type $previousQty
     */
    private function _salesOrderProcessing($product, $qtyOrdered, $order, $previousQty) {

          Mage::getModel('hordermanager/item')
            ->setItemId($product->getId())
            ->setOrderId($order->getId())
            ->setIsConfigurator(true)
            ->save();

        /*$sdsHOrder = Mage::getModel('hordermanager/observer');
        $sdsHOrder->setCreatedAtTimeData($order->getCreatedAtStoreDate());
        $newPeriod =$sdsHOrder->checkExistedPeriods();
        if (!$newPeriod) {
            $newPeriod = $sdsHOrder->checkPeriod();
            $currentPeriod = $sdsHOrder->initPeriod($newPeriod);
        } else {
            $currentPeriod = $newPeriod;
        }
        $sdsHelper = Mage::helper('gearup_sds');
        $sdsHelper->saveSdshorder($product,$currentPeriod->getId(),$order->getId());

        if ($product->getDxbs()) {
            if ((Mage::app()->getRequest()->getControllerName() == 'onepage' && Mage::app()->getRequest()->getActionName() == 'saveOrder')) {
                Mage::app()->setCurrentStore(Mage_Core_Model_App::ADMIN_STORE_ID);
            }
            $stockItem = Mage::getModel('cataloginventory/stock_item')->loadByProduct($product->getId());
            $previousSds = $product->getSameDayShipping();
            if ($stockItem->getData('is_in_stock') && ($previousQty - $qtyOrdered) > 0) {
                //$product->setSameDayShipping(1);
            } else {
                $product->setSameDayShipping(0);
            }
            $product->save();

            $sdsStatus = $sdsHelper ->sdsStatus($product->getSameDayShipping());
            $stockStatus = $sdsHelper ->stockLabel($stockItem->getData('is_in_stock'));
            if ($previousSds) {
                $action = '"' . round($previousQty) . ' In Stock; SDS is Yes", ' . round($qtyOrdered) . ' pcs sold and order number is ' . $order->getData('increment_id') . ' and marked green on this item, "' . round($stockItem->getData('qty')) . ' ' . $stockStatus . '; SDS is ' . $sdsStatus . '" ';
            } else {
                $action = '"' . round($previousQty) . ' In Stock; SDS is No", ' . round($qtyOrdered) . ' pcs sold and order number is ' . $order->getData('increment_id') . ', "' . round($stockItem->getData('qty')) . ' ' . $stockStatus . '; SDS is ' . $sdsStatus . '" ';
            }
            Mage::helper('gearup_sds')->recordHistory($product->getId(), $action, $order->getData('increment_id'));
        }*/
    }

    public function _checkIfConfigurator($product) {
        $customOption = $product->getProduct()->getOptions();
        foreach ($customOption as $o) {
            $optionType = $o->getType();
            if ($optionType == 'configurator') {
                return array('requestData' => $product->getBuyRequest()->getData(),
                    'optionId' => $o->getOptionId());
            }
        }
        return false;
    }

}
