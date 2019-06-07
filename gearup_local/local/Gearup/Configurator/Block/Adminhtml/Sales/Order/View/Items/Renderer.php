<?php

/**
 * Adminhtml sales order item renderer
 *
 * @category    Mage
 * @package     Mage_Bundle
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Gearup_Configurator_Block_Adminhtml_Sales_Order_View_Items_Renderer extends Mage_Adminhtml_Block_Sales_Items_Renderer_Default {

    /**
     * Getting all available childs for Invoice, Shipmen or Creditmemo item
     *
     * @param Varien_Object $item
     * @return array
     */
    public function isConfigurator($item) {
        try {
            $options = $item->getProduct()->getOptions();
        } catch (Exception $e) {
            $options = Mage::getModel('catalog/product')->load($item->getProductId())->getOptions();
        }
        foreach ($options as $option)
            if ($option->getType() == 'configurator')
                return true;

        return false;
    }

    public function getChilds($item) {

        $_itemsArray = array();
        try {
            $options = $item->getProduct()->getOptions();
        } catch (Exception $e) {
            $options = Mage::getModel('catalog/product')->load($item->getProductId())->getOptions();
        }

        $resource = Mage::getSingleton('core/resource');
        $read = $resource->getConnection('core_read');
        //echo $item->getOrder_id().'===>'.$item->getId(); exit;
        $tableName = $resource->getTableName('configurator_order_item');
        $sql = $read->select()
                  ->from($tableName) 
                  ->where('order_id = ?', $item->getOrder_id())
                  ->where('parent_item_id = ?', $item->getId()); 
        /**
         * Execute the query and store the results in $results
         */
        $getStore = Mage::app()->getStore($item->getStoreId());
        $baseCurrencyCode = $getStore->getBaseCurrencyCode();
        $currentCurrencyCode = $getStore->getCurrentCurrencyCode();
        $results = $read->fetchAll($sql);
        foreach($results as $result){
            $product = new Varien_Object();
            
             $product->setParentItemId($item->getId());
                            $product->setQtyOrdered($item->getQtyOrdered());
                            $product->setQty($item->getQty());
                            $product->setStatus($item->getStatus());
                            $product->setFinalPrice($result['price']);
                            $product->setSku($result['sku']);                           
                            $product->setPartNr($result['part_no']);                                                        
                            $product->setName($result['title']);       
                            $product->setSDS($result['sds']);
                            $product->setDxbs($result['dxbs']);
                            $amt = $result['price'];
// Allowed currencies
                            $allowedCurrencies = Mage::getModel('directory/currency')->getConfigAllowCurrencies();
                            $rates = Mage::getModel('directory/currency')->getCurrencyRates($baseCurrencyCode, array_values($allowedCurrencies));
// the price converted      
                         
                            if( $item->getOrder()->getOrderCurrencyCode() != $baseCurrencyCode ):    
                                $amt = $amt * $rates[$currentCurrencyCode];
                                $PriceTwo = Mage::helper('rounding')->process(Mage::getModel('directory/currency')->load($currentCurrencyCode), $amt); 
                            else:
                                $PriceTwo = $amt;
                            endif;
//                  
                         
                            $product->setBaseOriginalPrice($product->getFinalPrice());
                            $product->setOriginalPrice($PriceTwo);
                            $product->setProductId($result['product_id']);
                            $product->setBasePrice($product->getFinalPrice() * $item->getQtyOrdered());
                            $product->setBaseRowTotal($product->getFinalPrice() * $item->getQtyOrdered());
                            $product->setRowTotal($PriceTwo * $item->getQtyOrdered());
                            $product->setPrice($PriceTwo * $item->getQtyOrdered());
//                            $product->setBasePrice($product->getPrice());
//                            $product->setPrice($PriceTwo);                            
                            if ($result['sku'])
                                $_itemsArray[] = $product;
            

        }

//        $baseCurrencyCode = Mage::app()->getStore($item->getStoreId())->getBaseCurrencyCode();
//        $currentCurrencyCode = Mage::app()->getStore($item->getStoreId())->getCurrentCurrencyCode();
//        foreach ($options as $option) {
//            if ($option->getType() == 'configurator') {
//                $conf_product = true;
//                $optionId = $option->getOptionId();
//                $requestData = $item->getBuyRequest()->getData();
//
//                if (isset($requestData['options'][$optionId])) {
//                    foreach ($requestData['options'][$optionId] as $templateJsId => $template) {
//                        $templateOptionValues = $template['template'];
//                        foreach ($templateOptionValues as $templateOptionId => $templateOptionValue) {
//
//                            $templateOptionModel = Mage::getModel('configurator/value')->load($templateOptionValue);
//                            $productId = $templateOptionModel->getProductId();
//                            $product = Mage::getModel('catalog/product')->load($productId);
//                            $product->setParentItemId($item->getId());
//                            $product->setQtyOrdered($item->getQtyOrdered());
//                            $product->setQty($item->getQty());
//                            $product->setStatus($item->getStatus());
//                            $amt = $product->getFinalPrice();
//// Allowed currencies
//                            $allowedCurrencies = Mage::getModel('directory/currency')->getConfigAllowCurrencies();
//                            $rates = Mage::getModel('directory/currency')->getCurrencyRates($baseCurrencyCode, array_values($allowedCurrencies));
//// the price converted
//                            $amt = $amt * $rates[$currentCurrencyCode];
//                            $PriceTwo = Mage::helper('rounding')->process(Mage::getModel('directory/currency')->load($currentCurrencyCode), $amt); //Mage::app()->getLocale()->currency($currentCurrencyCode)->toCurrency($amt, array('precision' => $precision));
////
//                            $product->setBaseOriginalPrice($product->getFinalPrice());
//                            $product->setOriginalPrice($PriceTwo);
//
//                            $product->setBasePrice($product->getFinalPrice() * $item->getQtyOrdered());
//                            $product->setBaseRowTotal($product->getFinalPrice() * $item->getQtyOrdered());
//                            $product->setRowTotal($PriceTwo * $item->getQtyOrdered());
//                            $product->setPrice($PriceTwo * $item->getQtyOrdered());
////                            $product->setBasePrice($product->getPrice());
////                            $product->setPrice($PriceTwo);                            
//                            if ($productId)
//                                $_itemsArray[$productId] = $product;
//                        }
//                    }
//                }
//            }
//        }

        return $_itemsArray;
    }

    public function isShipmentSeparately($item = null) {
        if ($item) {
            if ($item->getOrderItem()) {
                $item = $item->getOrderItem();
            }
            if ($parentItem = $item->getParentItem()) {
                if ($options = $parentItem->getProductOptions()) {
                    if (isset($options['shipment_type']) && $options['shipment_type'] == Mage_Catalog_Model_Product_Type_Abstract::SHIPMENT_SEPARATELY
                    ) {
                        return true;
                    } else {
                        return false;
                    }
                }
            } else {
                if ($options = $item->getProductOptions()) {
                    if (isset($options['shipment_type']) && $options['shipment_type'] == Mage_Catalog_Model_Product_Type_Abstract::SHIPMENT_SEPARATELY
                    ) {
                        return false;
                    } else {
                        return true;
                    }
                }
            }
        }

        if ($options = $this->getOrderItem()->getProductOptions()) {
            if (isset($options['shipment_type']) && $options['shipment_type'] == Mage_Catalog_Model_Product_Type_Abstract::SHIPMENT_SEPARATELY
            ) {
                return true;
            }
        }
        return false;
    }

    public function resetMainItemPrice($item) {
        $baseCurrencyCode = Mage::app()->getStore($item->getStoreId())->getBaseCurrencyCode();
        $currentCurrencyCode =  $item->getOrder()->getOrderCurrencyCode();
        $price = $item->getProduct()->getFinalPrice();
        $amt = $price;
// Allowed currencies
        $allowedCurrencies = Mage::getModel('directory/currency')->getConfigAllowCurrencies();
        $rates = Mage::getModel('directory/currency')->getCurrencyRates($baseCurrencyCode, array_values($allowedCurrencies));
// the price converted
         if( $currentCurrencyCode != $baseCurrencyCode ):    
            $amt = $amt * $rates[$currentCurrencyCode];
        return [$price, Mage::helper('rounding')->process(Mage::getModel('directory/currency')->load($currentCurrencyCode), $amt)];         
         endif;
        return [$price, $amt];
    }

    public function isChildCalculated($item = null) {
        if ($item) {
            if ($item->getOrderItem()) {
                $item = $item->getOrderItem();
            }
            if ($parentItem = $item->getParentItem()) {
                if ($options = $parentItem->getProductOptions()) {
                    if (isset($options['product_calculations']) && $options['product_calculations'] == Mage_Catalog_Model_Product_Type_Abstract::CALCULATE_CHILD
                    ) {
                        return true;
                    } else {
                        return false;
                    }
                }
            } else {
                if ($options = $item->getProductOptions()) {
                    if (isset($options['product_calculations']) && $options['product_calculations'] == Mage_Catalog_Model_Product_Type_Abstract::CALCULATE_CHILD
                    ) {
                        return false;
                    } else {
                        return true;
                    }
                }
            }
        }

        if ($options = $this->getOrderItem()->getProductOptions()) {
            if (isset($options['product_calculations']) && $options['product_calculations'] == Mage_Catalog_Model_Product_Type_Abstract::CALCULATE_CHILD
            ) {
                return true;
            }
        }
        return false;
    }

    public function getSelectionAttributes($item) {
//        if ($item instanceof Mage_Sales_Model_Order_Item) {
//            return $options = $item->getProductOptions();
//        } else {
//            return $options = $item->getOrderItem()->getProductOptions();
//        }
        return $item->getProduct()->getTypeInstance(true)->getOrderOptions($item->getProduct());
    }

    public function getOrderOptions($item = null) {
        $result = array();

        if ($options = $this->getOrderItem()->getProductOptions()) {
            if (isset($options['options'])) {
                $result = array_merge($result, $options['options']);
            }
            if (isset($options['additional_options'])) {
                $result = array_merge($result, $options['additional_options']);
            }
            if (!empty($options['attributes_info'])) {
                $result = array_merge($options['attributes_info'], $result);
            }
        }
        return $result;
    }

    public function getOrderItem() {
        if ($this->getItem() instanceof Mage_Sales_Order_Item) {
            return $this->getItem();
        } else {
            return $this->getItem()->getOrderItem();
        }
    }

    public function getValueHtml($item) {
        $result = $this->escapeHtml($item->getName());
        if (!$this->isShipmentSeparately($item)) {

            $result = sprintf('%d', $attributes['qty']) . ' x ' . $result;
        }
        if (!$this->isChildCalculated($item)) {

            $result .= " " . $this->getOrderItem()->getOrder()->formatPrice($item->getPrice());
        }
        return $result;
    }

    public function canShowPriceInfo($item) {

        return true;
    }

}
