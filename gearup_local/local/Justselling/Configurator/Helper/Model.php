<?php

/**
 * justselling Germany Ltd. EULA
 * http://www.justselling.de/
 * Read the license at http://www.justselling.de/lizenz
 *
 * Do not edit or add to this file, please refer to http://www.justselling.de for more information.
 *
 * @category    justselling
 * @package     justselling_configurator
 * @copyright   Copyright (C) 2012 justselling Germany Ltd. (http://www.justselling.de)
 * @license     http://www.justselling.de/lizenz
 **/
class Justselling_Configurator_Helper_Model extends Mage_Core_Helper_Abstract
{
    /** @var array local cache for template options. Key templateId => array(Justselling_Configurator_Model_Mysql4_Option_Collection) */
    protected static $_cachedOptions = array();

    /**
     * Returns the option values of the given (order/quote-) item.<br/>
     * The returned array contains array('option'=>array(), 'values'=>array()).
     * @param $item Mage_Sales_Model_Quote_Item|Mage_Sales_Model_Order_Item
     * @return bool|array may be empty!
     */
    public function getOptionsAndOptionsValuesFromItem($item) {
        $optionValue = $this->getFirstFoundItemOptionValue($item);
        if (!$optionValue) return false;
        $configuratorOptionCustom = Mage::getModel('configurator/product_option_type_custom');
        $options = $configuratorOptionCustom->getTemplateOption($optionValue);
        return $options; // may be false
    }

    /**
     * Returns the value of the given option from the (order/quote-) item.<br/>
     * @param $item Mage_Sales_Model_Quote_Item|Mage_Sales_Model_Order_Item
     * @param $altTitele String
     * @return bool|array may be empty!
     */
    public function getOptionFromItemByAltTitle($item, $altTitle) {
        $options = $this->getOptionsAndOptionsValuesFromItem($item);
        foreach ($options as $option) {
            if (isset($option['option']['alt_title']) && $option['option']['alt_title'] == $altTitle) {
                return $option['value'];
            }
        }
        return false;
    }

    /**
     * Retrieve a single configurator option out of the given Quote or Sales Item, identified by the option-key (alt-title)
     * @param $item Mage_Sales_Model_Quote_Item|Mage_Sales_Model_Order_Item
     * @param $optionKey String
     * @return array|bool array containing 'price', 'title' (title contains the value! :o)
     */
    public function getOptionValueFromItem($item, $optionKey) {
        $options = $this->getOptionsAndOptionsValuesFromItem($item);
        if ($options) {
            foreach($options as $item) {
                $optionData = $item['option'];
                $optionValueData = $item['value'];
                if (isset($optionData['alt_title']) && $optionData['alt_title'] == $optionKey) {
                    return $optionValueData;
                }
            }
        }
        return false;
    }

    /**
     * Backward-compatible method.
     * @see Justselling_Configurator_Helper_Model::getOptionsAndOptionsValuesFromItem()
     */
    public function getOptionValuesFromItem($item) {
        return $this->getOptionsAndOptionsValuesFromItem($item);
    }

    /**
     * Returns the (locally cached) option identified by given id.
     * @param $templateId
     * @param $optionId
     * @return Justselling_Configurator_Model_Option
     */
    private function getCachedOptionById($templateId, $optionId) {
        if (!isset(self::$_cachedOptions[$templateId])) {
            /** @var $options Justselling_Configurator_Model_Mysql4_Option_Collection */
            $options = Mage::getModel("configurator/option")->getCollection();
            $options->addFieldToFilter('template_id', array('eq' => $templateId));
            $options->load();
            self::$_cachedOptions[$templateId] = $options;
        }
        /** @var $optionCollection Justselling_Configurator_Model_Mysql4_Option_Collection */
        $optionCollection = self::$_cachedOptions[$templateId];
        $option = $optionCollection->getItemById($optionId);
        return $option;
    }

    /**
     * @param $item Mage_Sales_Model_Quote_Item|Mage_Sales_Model_Order_Item
     * @return bool
     * @deprecated
     */
    public function istConfiguratorItem($item) {
        $codeForConfiguratorOptions = $this->getItemOptionCodeContainingConfiguratorOptions($item);
        return $codeForConfiguratorOptions ? true : false;
    }

    /**
     * Returns true if given item is a configurator item => configurator related product
     * @param $item Mage_Sales_Model_Quote_Item|Mage_Sales_Model_Order_Item
     * @return bool
     */
    public function isConfiguratorItem($item) {
        $codeForConfiguratorOptions = $this->getItemOptionCodeContainingConfiguratorOptions($item);
        return $codeForConfiguratorOptions ? true : false;
    }

    /**
     * Checks whether given product is a configurator-product.
     * @param Mage_Catalog_Model_Product $product
     * @return bool
     */
    public function isConfiguratorProduct($product) {
        $productOptions = $product->getOptions();
        /* @var $productOption Mage_Catalog_Model_Product_Option */
        foreach ($productOptions as $productOption) {
            if ($productOption->getType() == 'configurator') { return true; }
        }
        return false;
    }

    /**
     * Modifies the given Item for the given key.
     * Returns array with modified options, null on failue, false in case no configurator product.<br/>
     * Important: this only works for options values for type string|int|float!
     * @param $item Mage_Sales_Model_Quote_Item|Mage_Sales_Model_Order_Item
     * @param $key
     * @param $value
     * @return bool|array false if no configurator product, array on success with modified options
     */
    public function setConfiguratorOptionValueOnItem($item, $key, $value) {
        $itemOptions = $item->getOptions();
        $codeForConfiguratorOptions = $this->getItemOptionCodeContainingConfiguratorOptions($item);
        if (!$codeForConfiguratorOptions) return false;
        /** @var $quoteItemOption Mage_Sales_Model_Quote_Item_Option */
        foreach ($itemOptions as $quoteItemOption) {
            if ($quoteItemOption->getCode() == $codeForConfiguratorOptions) {
                $itemOptionValueData = unserialize($quoteItemOption->getValue());
                $optionValueKey = reset(array_keys($itemOptionValueData));
                if (is_array($itemOptionValueData)) {
                    foreach ($itemOptionValueData as $jsTemplateOption){ // now we have justSelling TemplateOptions
                        if (isset($jsTemplateOption['template'])) {
                            $configuratorOptions = $jsTemplateOption['template'];
                            if (is_array($configuratorOptions)) {
                                $anyOptionId = reset(array_keys($configuratorOptions));
                                $templateId = Mage::getModel('configurator/option')->load($anyOptionId)->getTemplateId();
                                $hit = false;
                                foreach ($configuratorOptions as $configuratorOptionId => $configuratorOptionValue) {
                                    /** @var $optionObj Justselling_Configurator_Model_Option */
                                    if ($optionObj = $this->getCachedOptionById($templateId, $configuratorOptionId)) {
                                        if ($optionObj->getAltTitle() == $key) {
                                            $itemOptionValueData[$optionValueKey]['template'][$optionObj->getId()] = $value;
                                            $hit = true;
                                            break;
                                        }
                                    }
                                }
                                if ($hit) {
                                    $quoteItemOption->setValue(serialize($itemOptionValueData));
                                    $item->setOptions($itemOptions);
                                    return $itemOptions;
                                }
                            }
                        }
                    }
                }
            }
        }
        return false;
    }

    /**
     * Returns the quote item option containing the configurators options. Example:
     * array('option_2' => 'a:1:{s:17:"js53ede7db2c9dejs";a:2:{s:9:"postprice";s:1:"0";s:8:"template";a:1...')
     *
     * @param $quoteItem Mage_Sales_Model_Quote_Item
     * @return bool|array
     */
    public function getQuoteItemOption($quoteItem) {
        if (!($quoteItem instanceof Mage_Sales_Model_Quote_Item)) return false;
        $codeForConfiguratorOptions = $this->getItemOptionCodeContainingConfiguratorOptions($quoteItem);
        if (!$codeForConfiguratorOptions) return false;
        $options = $this->getItemOptions($quoteItem);
        foreach ($options as $option){
            if ($option['code'] == $codeForConfiguratorOptions) {
                $result = array();
                $result[$codeForConfiguratorOptions] = $option['value'];
                return $result;
            }
        }
        return false;
    }

    /**
     * Returns the first found (!) configurator options as serialized array.
     * @param $item Mage_Sales_Model_Quote_Item|Mage_Sales_Model_Order_Item
     * @param $valueKey string default='value', the column key
     * @return string|bool serialized value
     */
    private function getFirstFoundItemOptionValue($item, $valueKey='value') {
        $codeForConfiguratorOptions = $this->getItemOptionCodeContainingConfiguratorOptions($item);
        if (!$codeForConfiguratorOptions) return false;
        $options = $this->getItemOptions($item);
        foreach ($options as $option){
            if ($option['code'] == $codeForConfiguratorOptions) {
                return $option[$valueKey]; // serialized array
            }
        }
        return false;
    }

    /**
     * Returns the quote item option code (i.e. 'option_2') containing configurator options.
     * @param $item Mage_Sales_Model_Quote_Item|Mage_Sales_Model_Order_Item
     * @return string|bool false in case no configurator option could be found.
     */
    private function getItemOptionCodeContainingConfiguratorOptions($item) {
        if (!($item instanceof Mage_Sales_Model_Quote_Item) &&
            !($item instanceof Mage_Sales_Model_Order_Item)) return false;
        $optionCodes = array();
        $options = $this->getItemOptions($item);
        if (!$options) return false;
        foreach ($options as $option){
            if (isset($option['code'])) {
                $code = $option['code'];
                if ($code == 'option_ids') {
                    $optionIds = explode(",", $option['value']);
                    if (!empty($optionIds)) {
                        foreach ($optionIds as $optionId) { $optionCodes[] = "option_" . $optionId; }
                    }
                }
                if (in_array($code, $optionCodes) && isset($option['value'])) {
                    $optionsData = unserialize($option['value']);
                    if (is_array($optionsData) && array_key_exists('template', $optionsData) ) {
                        return $code; // i.e. 'option_2' containing configurator options
                    } elseif (is_array($optionsData) && array_key_exists('template', reset($optionsData))) {
                        return $code;
                    }
                }
            }
        }
        return false;
    }

    /**
     * @param $item Mage_Sales_Model_Quote_Item|Mage_Sales_Model_Order_Item
     * @return array|bool
     */
    private function getItemOptions($item) {
        if ($item instanceof Mage_Sales_Model_Quote_Item) {
            return $item->getOptions();
        } elseif ($item instanceof Mage_Sales_Model_Order_Item) {
            return $item->getProductOptions();
        }
        return false;
    }
}