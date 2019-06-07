<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

class Gearup_Configurator_Model_Observer {

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

    public function updateConfiguratorProducts($observer) {
        $order = $observer->getEvent()->getOrder();
        //get order

        $cart = Mage::getModel('checkout/cart')->getQuote();
        $sds = 0;

        if (!$this->checkIfEventExist('gearup_configurator_customorder' . $order->getId())) {
            foreach ($order->getAllItems() as $item) {
                if ($result = $this->isConfigurator($item, $order)) {
                    if ($result[0] && $result[1]) {
                        $sds ++;
                    } else {
                        $product = Mage::getModel('catalog/product')->load($item->getProductId());
                        if (!$result[0] && ($product->getSameDayShipping() && $product->getDxbs()))
                            $sds ++;
                    }
                }
            }

            if (count($order->getAllItems()) == $sds)
                Mage::helper('gearup_sds')->flagSdsAll($order->getId(), 1);
            else
                Mage::helper('gearup_sds')->flagSdsAll($order->getId(), 0);
        }
    }

    protected function isConfigurator($product, $order) {
        $resource = Mage::getSingleton('core/resource');
        $write = $resource->getConnection('core_write');
        $table = $resource->getTableName('configurator_order_item');

        $customOption = $product->getProduct()->getOptions();
        $cartHelper = Mage::helper('checkout/cart');
        foreach ($customOption as $o) {
            $optionType = $o->getType();
            if ($optionType == 'configurator') {
                $optionId = $o->getOptionId();
                $sds = 0;
                $requestData = $product->getBuyRequest()->getData(); //$cartHelper->getQuote()->getItemById($product->getId())->getBuyRequest()->getData();
                
                 $helper = Mage::helper('hordermanager');        
                    $sdsHOrder = Mage::getModel('hordermanager/observer');
                    $sdsHOrder->setCreatedAtTimeData($order->getCreatedAtStoreDate());
                    $sdsHOrder->orderDay = $helper->getCurrentDate($sdsHOrder->dateCreatedAt);
                    $orderTime = $helper->getCurrentTime($sdsHOrder->hourCreatedAt);
                    $sdsHOrder->setOrderDayNumber($orderTime);
                    $sdsHOrder->edgeDayOfPeriodNumber = $helper->getConfig('day');        
                    $newPeriod = $sdsHOrder->checkExistedPeriods();
                    if (!$newPeriod) {
                        $newPeriod = $sdsHOrder->checkPeriod();
                        $currentPeriod = $sdsHOrder->initPeriod($newPeriod);
                        //Mage::log('newPeriod : No', null, 'periods_configurator.log');
                    } else {
                        $currentPeriod = $newPeriod;
                        //Mage::log('newPeriod : Yes', null, 'periods_configurator.log');
                    }
                
                
                if (isset($requestData['options'][$optionId])) {
                    foreach ($requestData['options'][$optionId] as $templateJsId => $template) {
                        $templateOptionValues = $template['template'];
                        foreach ($templateOptionValues as $templateOptionId => $templateOptionValue) {
                            $templateOptionModel = Mage::getModel('configurator/value')->load($templateOptionValue);
                            $productId = $templateOptionModel->getProductId();
                            if ($productId) {
                                $subProduct = Mage::getModel('catalog/product')->load($productId);
                                try{                                
                                 $write->insert($table, array(
                                    'order_id'=>$order->getId(),
                                    'parent_item_id'=> $product->getItemId(),
                                    'product_id'=> $subProduct->getId(),
                                    'sku'=>  $subProduct->getSku(), 
                                    'title'=> $subProduct->getName(),
                                    'price'=>  $subProduct->getFinalPrice(),
                                    'sds' =>  ($subProduct->getSame_day_shipping())?1:0,
                                    'dxbs' => ($subProduct->getDxbs())?1:0,
                                    'part_no'=>$subProduct->getPartNr() ));
                                 }catch(Exception $e){
                                    Mage::log($e->getMessage().$productId,true,'gearup_confgirator_error.log');
                                 }
                                if ($subProduct->getSame_day_shipping() && $subProduct->getDxbs()) {
                                    $sds++;
                                }
                                $stock = Mage::getModel('cataloginventory/stock_item')->loadByProduct($subProduct);
                                $previousQty = $stock->getQty();
                                $stock->setQty($stock->getQty() - $product->getQtyOrdered()); // Set to new Qty
                                $stock->save();
                                $this->_salesOrderProcessing($subProduct, $product->getQtyOrdered(), $order, $previousQty,$currentPeriod);
                                // Save
                            }
                        }
                        return [true, (count($templateOptionValues) == $sds) ? true : false];
                    }
                }
            }
        }
        return [false, false];
    }

    public function modifyPrice(Varien_Event_Observer $obs) {
        // Get the quote item

        $quote = $obs->getEvent()->getQuote();
        if (!$quote) {
            $quote = Mage::getSingleton('checkout/cart')->getQuote();
        }

        if (!$quote->getCustomer()) {
            $countryCode = Mage::app()->getStore()->getCurrentCurrencyCode();
            $shippingAddress = $quote->getShippingAddress()->setCountryId(substr($countryCode, 0, 2))
                ->setCity('')
                ->setPostcode(1)
                ->setRegionId('')
                ->setRegion('')
                ->setCollectShippingRates(true);
            $shippingAddress->save();

            //$quote;
            Mage::getSingleton('checkout/cart')->getQuote()->save();
            Mage::getSingleton('checkout/session')->setEstimatedShippingAddressData(array(
                'country_id' => substr($countryCode, 0, 2),
                'postcode'   => 1,
                'city'       => '',
                'region_id'  => '',
                'region'     => ''
            ));
        }


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

        if (isset($_POST) && isset($_POST['history']['status']))
            $orderStatus = $_POST['history']['status'];
        else
            $orderStatus = $order->getStatus();

        if ($orderStatus == Mage_Sales_Model_Order::STATE_CANCELED) {
            if (!$this->checkIfEventExist('gearup_configurator_cancelorder' . $order->getId())) {
                foreach ($order->getAllItems() as $item) {
                    if ($result = $this->_checkIfConfigurator($item)) {
                        if (isset($_POST) && isset($_POST['history']['status'])) {
                            $subProduct = Mage::getModel('catalog/product')->load($item->getProduct()->getId());
                            $this->_SDSProcessing($subProduct, $order, $item->getQtyOrdered());
                        }
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
    }

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
            if (!$product->getSameDayShipping()) {
               $stockItem->setQty($qtyOrdered);
            }
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
            if ($backInStock) {
                $prevQty = $stockItem->getData('qty') - $qtyOrdered;
            } else {
                $prevQty = $stockItem->getData('qty');
            }

            $action = '<strong>Cancel Order : ' . $order->getIncrementId() . '</strong>, Update QTY to ' . round($stockItem->getData('qty')) . ' In Stock; SDS is ' . ($product->getSameDayShipping() ? "Yes" : 'No');
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
    private function _salesOrderProcessing($product, $qtyOrdered, $order, $previousQty,$currentPeriod) {

        Mage::getModel('hordermanager/item')
                ->setItemId($product->getId())
                ->setOrderId($order->getId())
                ->setIsConfigurator(true)
                ->save();
       
        $sdsHelper = Mage::helper('gearup_sds');
        $sdsHelper->saveSdshorder($product, $currentPeriod->getId(), $order->getId());

        //Mage::log('Period name : ' . $currentPeriod->getData('custom_period_id') . ' Date : ' . date('Y-m-d H:i:s'), null, 'periods_configurator.log');

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

            $sdsStatus = $sdsHelper->sdsStatus($product->getSameDayShipping());
            $stockStatus = $sdsHelper->stockLabel($stockItem->getData('is_in_stock'));
            if ($previousSds) {
                $action = '"' . round($previousQty) . ' In Stock; SDS is Yes", ' . round($qtyOrdered) . ' pcs sold and order number is ' . $order->getData('increment_id') . ' and marked green on this item, "' . round($stockItem->getData('qty')) . ' ' . $stockStatus . '; SDS is ' . $sdsStatus . '" ';
            } else {
                $action = '"' . round($previousQty) . ' In Stock; SDS is No", ' . round($qtyOrdered) . ' pcs sold and order number is ' . $order->getData('increment_id') . ', "' . round($stockItem->getData('qty')) . ' ' . $stockStatus . '; SDS is ' . $sdsStatus . '" ';
            }

            Mage::helper('gearup_sds')->recordHistory($product->getId(), $action, $previousQty, round($stockItem->getData('qty')), $order->getData('increment_id'), $previousSds, $previousQty);
        }
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

    private function checkIfEventExist($event) {
        try {
            $resource = Mage::getSingleton('core/resource');
            $writeAdapter = $resource->getConnection('core_write');
            $table = $resource->getTableName('gearup_configurator_events');
            $query = "INSERT INTO {$table} (`event_name`) VALUES (?);";
            $writeAdapter->query($query, $event);
            return false;
        } catch (Exception $e) {
            return true;
        }
    }

    public function disableAddtoCart(){
         $product = Mage::registry('current_product');
         Mage::helper('configurator')->disableAddtoCart($product);       
    }
    
    public function checkConfiguratorProductInStock($observer) {      
            $cart = Mage::getModel('checkout/cart')->getQuote();
            $errorOptions = [];
            foreach ($cart->getAllItems() as $item) {
                if ($result = $this->_checkIfConfigurator($item)) {
                    $templateId = Mage::getModel("configurator/template")->getLinkedTemplateId($result['optionId']);
                    $requirdOptionIds = Mage::getModel("configurator/option")->getCollection()
                            ->addFieldToFilter('template_id', $templateId)
                            ->addFieldToFilter('is_require', 1);

                   // print_r($requirdOptionIds->getData());exit;        
                    $requestData = $result['requestData'];
                    
                    $optionId = $result['optionId'];
                    if (isset($requestData['options'][$optionId])) {
                        foreach ($requestData['options'][$optionId] as $templateJsId => $template) {
                            $templateOptionValues = $template['template'];
                            $itemOptionIds = array_keys($templateOptionValues);
                            foreach ($requirdOptionIds as $index):
                                if (!in_array($index->getId(), $itemOptionIds)) {
                                    $errorOptions[] = $index->getTitle();
                                }
                            endforeach;
                            if (count($errorOptions)) {
                                Mage::getSingleton('core/session')->setDisallowCart(1);
                                Mage::getSingleton("checkout/session")->addError(Mage::helper('checkout')->__("Apologies, the required component <font color=\"black\">'%s'</font> is currently out of stock from '%s', Please contact our technical team at <a href='mailto:support@gear-up.me'>support@gear-up.me</a>",implode(',',$errorOptions),$item->getProduct()->getName()));
                            }

                            foreach ($templateOptionValues as $templateOptionId => $templateOptionValue) {
                                $templateOptionModel = Mage::getModel('configurator/value')->load($templateOptionValue);
                                try {
                                    $productId = $templateOptionModel->getProductId();
                                    $product = Mage::getModel('catalog/product')->load($productId);
                                    $inStock = Mage::getModel('cataloginventory/stock_item')->loadByProduct($product)->getIsInStock();

                                    if (!$inStock || $product->getStatus() == 2) {                                        
                                       Mage::getSingleton('core/session')->setDisallowCart(1);
                                       $session2 = Mage::getSingleton("checkout/session");
                                       $session2->addError(Mage::helper('checkout')->__("'%s' is currently out of stock. Please go back to '%s' and choose different item to continue with your order.", $product->getName(),$item->getProduct()->getName() ));                                        
                                       return Mage::register('checkConfiguratorProductInStock', "called");
                                    }
                                } catch (Exception $e) {
                                    return $e->getMessage();
                                }
                            }
                        }
                    }
                }
            }
            if (!count($errorOptions)) 
            Mage::getSingleton('core/session')->setDisallowCart(0);
        
        //}
    }

}
