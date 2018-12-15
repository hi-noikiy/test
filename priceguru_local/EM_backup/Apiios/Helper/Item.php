<?php
class EM_Apiios_Helper_Item extends Mage_Tax_Helper_Data
{
    protected $_store = null;
    protected $_source = null;
    protected $_purchasedLinks = null;

    public function setStore($store){
        $this->_store = $store;
        return $this;
    }

    public function getStore(){
        return $this->_store;
    }

    /**
     * Show price for item(in order and checkout review)
     *
     * @param Mage_Sales_Model_Order_Item | Mage_Sales_Model_Quote_Item $_item
     * @param string $type (price or subtotal)
     */
    public function getPriceItem($_item,$key = 'price'){
        $result = array();
        $helperSale = Mage::helper('sales');
        $flat = 0;
        $value = 0;
        /* Price */
        if ($this->displayCartPriceExclTax() || $this->displayCartBothPrices()){
            if($key == 'price'){
                if (Mage::helper('weee')->typeOfDisplay($_item, array(0, 1, 4), 'sales') && $_item->getWeeeTaxAppliedAmount()){
                    $value = $this->getStore()->formatPrice($_item->getCalculationPrice()+$_item->getWeeeTaxAppliedAmount()+$_item->getWeeeTaxDisposition(),false);
                } else {
                    $value = $this->getStore()->formatPrice($_item->getCalculationPrice(),false);
                }
            }
            else{
                if (Mage::helper('weee')->typeOfDisplay($_item, array(0, 1, 4), 'sales') && $_item->getWeeeTaxAppliedAmount()){
                    $value = $this->getStore()->formatPrice($_item->getRowTotal()+$_item->getWeeeTaxAppliedRowAmount()+$_item->getWeeeTaxRowDisposition(),false);
                } else {
                    $value = $this->getStore()->formatPrice($_item->getRowTotal(),false);
                }

            }
            $result['exc'] = array(
                'label' =>  $helperSale->__('Excl. Tax'),
                'value' =>  $value
            );
            $flat++;
        }

        if($this->displayCartPriceInclTax() || $this->displayCartBothPrices()){
            if($key == 'price'){
                $_incl = Mage::helper('checkout')->getPriceInclTax($_item);
                if (Mage::helper('weee')->typeOfDisplay($_item, array(0, 1, 4), 'sales') && $_item->getWeeeTaxAppliedAmount()){
                    $value = $this->getStore()->formatPrice($_incl+$_item->getWeeeTaxAppliedAmount(),false);
                } else {
                    $value = $this->getStore()->formatPrice($_incl-$_item->getWeeeTaxDisposition(),false);
                }
            }
            else {
                $_incl = Mage::helper('checkout')->getSubtotalInclTax($_item);
                if (Mage::helper('weee')->typeOfDisplay($_item, array(0, 1, 4), 'sales') && $_item->getWeeeTaxAppliedAmount()){
                    $value =  $this->getStore()->formatPrice($_incl+$_item->getWeeeTaxAppliedRowAmount(),false);
                } else {
                    $value =  $this->getStore()->formatPrice($_incl-$_item->getWeeeTaxRowDisposition(),false);
                }
            }
            $result['inc'] = array(
                'label' =>  $helperSale->__('Incl. Tax'),
                'value' =>  $value
            );
            $flat++;
        }

        if($flat < 2){
            $result = array('regular_price' => $value);
        }
        return $result;

    }

    /**
     * Get product customize options (checkout review)
     *
     * @param $item
     * @return array || false
     */
    public function getProductOptions($item)
    {
        /* @var $helper Mage_Catalog_Helper_Product_Configuration */
        $helper = Mage::helper('catalog/product_configuration');
        return $helper->getCustomOptions($item);
    }

    /**
     * Get list of all otions for product (checkout review)
     *
     * @param $item
     * @return array
     */
    public function getOptionList($item)
    {
        return $this->getProductOptions($item);
    }

    /**
     * Get list of all otions for product (recent order in customer account)
     *
     * @param $item
     * @return array
     */
    public function getItemOptions($item)
    {
        $result = array();
        if ($options = $item->getProductOptions()) {
            //print_r($item->getProductOptions());
            if (isset($options['options'])) {
                $result = array_merge($result, $options['options']);
            }
            if (isset($options['additional_options'])) {
                $result = array_merge($result, $options['additional_options']);
            }
            if (isset($options['attributes_info'])) {
                $result = array_merge($result, $options['attributes_info']);
            }
        }

        if($item->getProductType() == 'downloadable'){
            if($links = $this->getLinks($item)){
                $optionData = array();
                $optionData['label']    =   $this->getLinksTitle();
				$value = array();
                foreach($links->getPurchasedItems() as $link){
                    $value[] = Mage::helper('core')->escapeHtml($link->getLinkTitle());
                }
				$optionData['value'] = $value;
				//print_r($optionData);
                $result[] = $optionData;
            }//exit;
			//print_r($result);exit;
        }
		
        return $result;
    }

    /**
     * Accept option value and return its formatted view (recent orders in customer account)
     *
     * @param mixed $optionValue
     * Method works well with these $optionValue format:
     *      1. String
     *      2. Indexed array e.g. array(val1, val2, ...)
     *      3. Associative array, containing additional option info, including option value, e.g.
     *          array
     *          (
     *              [label] => ...,
     *              [value] => ...,
     *              [print_value] => ...,
     *              [option_id] => ...,
     *              [option_type] => ...,
     *              [custom_view] =>...,
     *          )
     *
     * @return array
     */
    public function getFormatedOptionValue($optionValue)
    {
        $optionInfo = array();

        // define input data format
        if (is_array($optionValue)) {
            if (isset($optionValue['option_id'])) {
                $optionInfo = $optionValue;
                if (isset($optionInfo['value'])) {
                    $optionValue = $optionInfo['value'];
                }
            } elseif (isset($optionValue['value'])) {
                $optionValue = $optionValue['value'];
            }
        }

        // render customized option view
        if (isset($optionInfo['custom_view']) && $optionInfo['custom_view']) {
            $_default = array('value' => $optionValue);
            if (isset($optionInfo['option_type'])) {
                try {
                    $group = Mage::getModel('catalog/product_option')->groupFactory($optionInfo['option_type']);
                    return array('value' => $group->getCustomizedView($optionInfo));
                } catch (Exception $e) {
                    return $_default;
                }
            }
            return $_default;
        }

        // truncate standard view
        $result = array();
        if (is_array($optionValue)) {
            $_truncatedValue = implode("(*_*!)", $optionValue);
            //$_truncatedValue = nl2br($_truncatedValue);
            return array('value' => $_truncatedValue);
        } else {
            $_truncatedValue = Mage::helper('core/string')->truncate($optionValue, 55, '');
            $_truncatedValue = nl2br($_truncatedValue);
        }

        $result = array('value' => $_truncatedValue);

        if (Mage::helper('core/string')->strlen($optionValue) > 55) {
            $result['value'] = $result['value'] . ' <a href="#" class="dots" onclick="return false">...</a>';
            $optionValue = nl2br($optionValue);
            $result = array_merge($result, array('full_view' => $optionValue));
        }

        return $result;
    }

    /**
     * Accept option value and return its formatted view (checkout review)
     *
     * @param mixed $optionValue
     * Method works well with these $optionValue format:
     *      1. String
     *      2. Indexed array e.g. array(val1, val2, ...)
     *      3. Associative array, containing additional option info, including option value, e.g.
     *          array
     *          (
     *              [label] => ...,
     *              [value] => ...,
     *              [print_value] => ...,
     *              [option_id] => ...,
     *              [option_type] => ...,
     *              [custom_view] =>...,
     *          )
     *
     * @return array
     */
    public function getFormatedOptionValueReview($optionValue)
    {
        /* @var $helper Mage_Catalog_Helper_Product_Configuration */
        $helper = Mage::helper('catalog/product_configuration');
        $params = array(
            'max_length' => 55,
            'cut_replacer' => ' <a href="#" class="dots" onclick="return false">...</a>'
        );
        return $helper->getFormattedOptionValue($optionValue, $params);
    }

    /**
     * Return option array for json
     *
     * @param Mage_Sales_Model_Order_Item | Mage_Sales_Model_Quote_Item $item
     * @param string $type
     * @return array with format
     *         array( array('label' => string, 'value' => string, 'full_view' => mixed),...)
     */
    public function getItemOptionsArray($item , $type = 'order'){
        $optionsArray = array();
        if($type == 'order')
            $options = $this->getItemOptions($item);
        else if($type == 'review')
            $options = $this->getOptionList($item);
        if(!empty($options)){
            $coreHelper = Mage::helper('core');
            foreach($options as $_option){
                $optionData = array();
                $optionData['label'] = $coreHelper->escapeHtml($_option['label']);
                if($type == 'order')
                    $_formatedOptionValue = $this->getFormatedOptionValue($_option);
                else if($type == 'review')
                    $_formatedOptionValue = $this->getFormatedOptionValueReview($_option);
                if(isset($_formatedOptionValue)){
                    $optionData['value'] = $_formatedOptionValue['value'];
                    if (isset($_formatedOptionValue['full_view'])){
                        $optionData['full_view'] = array(
                            'label' =>  $coreHelper->escapeHtml($_option['label']),
                            'value' =>  $_formatedOptionValue['full_view']
                        );
                    }
                    $optionsArray[] = $optionData;
                }

            }
        }

        return $optionsArray;
    }

    /**
     * Enter description here...
     *
     * @return unknown
     */
    public function getLinks($item)
    {
        $this->_purchasedLinks = Mage::getModel('downloadable/link_purchased')
            ->load($item->getOrder()->getId(), 'order_id');
        $purchasedItems = Mage::getModel('downloadable/link_purchased_item')->getCollection()
            ->addFieldToFilter('order_item_id', $item->getId());
        $this->_purchasedLinks->setPurchasedItems($purchasedItems);

        return $this->_purchasedLinks;
    }

    public function getLinksTitle()
    {
        if ($this->_purchasedLinks->getLinkSectionTitle()) {
            return $this->_purchasedLinks->getLinkSectionTitle();
        }
        return Mage::getStoreConfig(Mage_Downloadable_Model_Link::XML_PATH_LINKS_TITLE);
    }
}