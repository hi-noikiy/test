<?php

class MindMagnet_Catalog_Block_Product_View_Type_Configurable extends Amasty_Conf_Block_Catalog_Product_View_Type_Configurable
{


    public function getJsonConfig()
    {
        $attributeIdsWithImages = array();
        $jsonConfig = parent::getJsonConfig();
        $config = Zend_Json::decode($jsonConfig);
        $productImagesAttributes = $this->getImagesFromProductsAttributes();

        foreach ($config['attributes'] as $attributeId => $attribute)
        {
            $this->_currentAttributes[] = $attribute['code'];

            $attr = Mage::getModel('amconf/attribute')->load($attributeId, 'attribute_id');
            if ($attr->getUseImage())
            {
                $attributeIdsWithImages[] = $attributeId;
                $config['attributes'][$attributeId]['use_image']        = 1;
                $config['attributes'][$attributeId]['enable_carousel']  =  Mage::getStoreConfig('amconf/general/swatch_carou');
                $config['attributes'][$attributeId]['config']           = $attr->getData();

                $smWidth     = $attr->getSmallWidth() != "0"? $attr->getSmallWidth() : self::SMALL_SIZE;
                $smHeight    = $attr->getSmallHeight()!= "0"? $attr->getSmallHeight(): self::SMALL_SIZE;
                $bigWidth    = $attr->getBigWidth()!= "0"?    $attr->getBigWidth()   : self::BIG_SIZE;
                $bigHeight   = $attr->getBigHeight()!= "0"?   $attr->getBigHeight()  : self::BIG_SIZE;

                foreach ($attribute['options'] as $i => $option)
                {
                    if (in_array($attributeId, $productImagesAttributes)){

                        foreach($option['products'] as $product_id){

                            $product = Mage::getModel('catalog/product')->load($product_id);
                            $config['attributes'][$attributeId]['options'][$i]['image'] =
                                (string)Mage::helper('catalog/image')->init($product, 'image')->resize($smWidth, $smHeight);
                            $config['attributes'][$attributeId]['options'][$i]['bigimage'] =
                                (string)Mage::helper('catalog/image')->init($product, 'image')->resize($bigWidth, $bigHeight);
                            $config['attributes'][$attributeId]['options'][$i]['stock'] = ($product->getStockItem()->getIsInStock() == 1) ? 'in_stock' : 'out_of_stock';
                        }
                    }
                    else {
                        $imgUrl     = Mage::helper('amconf')->getImageUrl($option['id'], $smWidth, $smHeight);
                        $tooltipUrl = Mage::helper('amconf')->getImageUrl($option['id'], $bigWidth, $bigHeight);
                        if($imgUrl == ""){
                            $imgUrl     = Mage::helper('amconf')->getPlaceholderUrl($attributeId, $smWidth, $smHeight);
                            $tooltipUrl = Mage::helper('amconf')->getPlaceholderUrl($attributeId, $bigWidth, $bigHeight);
                        }
                        $config['attributes'][$attributeId]['options'][$i]['image'] = $imgUrl;
                        $config['attributes'][$attributeId]['options'][$i]['bigimage'] = $tooltipUrl;

                        $swatchModel = Mage::getModel('amconf/swatch')->load($option['id']);
                        $config['attributes'][$attributeId]['options'][$i]['color'] = $swatchModel->getColor();

                        $config['attributes'][$attributeId]['options'][$i]['stock'] = 'out_of_stock';

                        foreach($option['products'] as $product_id){
                            $product = Mage::getModel('catalog/product')->load($product_id);
                            $config['attributes'][$attributeId]['options'][$i]['stock'] = ($product->getStockItem()->getIsInStock() == 1) ? 'in_stock' : 'out_of_stock';
                        }
                    }
                }
            }
        }
        Mage::unregister('amconf_images_attrids');
        Mage::register('amconf_images_attrids', $attributeIdsWithImages, true);

        return Zend_Json::encode($config);
    }

    public function getJsonConfigCore()
    {
        $attributes = array();
        $options    = array();
        $store      = $this->getCurrentStore();
        $taxHelper  = Mage::helper('tax');
        $currentProduct = $this->getProduct();

        $preconfiguredFlag = $currentProduct->hasPreconfiguredValues();
        if ($preconfiguredFlag) {
            $preconfiguredValues = $currentProduct->getPreconfiguredValues();
            $defaultValues       = array();
        }

        foreach ($this->getAllowProducts() as $product) {
            $productId  = $product->getId();

            foreach ($this->getAllowAttributes() as $attribute) {
                $productAttribute   = $attribute->getProductAttribute();
                $productAttributeId = $productAttribute->getId();
                $attributeValue     = $product->getData($productAttribute->getAttributeCode());
                if (!isset($options[$productAttributeId])) {
                    $options[$productAttributeId] = array();
                }

                if (!isset($options[$productAttributeId][$attributeValue])) {
                    $options[$productAttributeId][$attributeValue] = array();
                }
                $options[$productAttributeId][$attributeValue][] = $productId;
            }
        }

        $this->_resPrices = array(
            $this->_preparePrice($currentProduct->getFinalPrice())
        );

        foreach ($this->getAllowAttributes() as $attribute) {
            $productAttribute = $attribute->getProductAttribute();
            $attributeId = $productAttribute->getId();
            $info = array(
                'id'        => $productAttribute->getId(),
                'code'      => $productAttribute->getAttributeCode(),
                'label'     => $attribute->getLabel(),
                'options'   => array()
            );

            $optionPrices = array();
            $prices = $attribute->getPrices();
            if (is_array($prices)) {
                foreach ($prices as $value) {
                    if(!$this->_validateAttributeValue($attributeId, $value, $options)) {
                        continue;
                    }
                    $currentProduct->setConfigurablePrice(
                        $this->_preparePrice($value['pricing_value'], $value['is_percent'])
                    );
                    $currentProduct->setParentId(true);
                    Mage::dispatchEvent(
                        'catalog_product_type_configurable_price',
                        array('product' => $currentProduct)
                    );
                    $configurablePrice = $currentProduct->getConfigurablePrice();

                    if (isset($options[$attributeId][$value['value_index']])) {
                        $productsIndex = $options[$attributeId][$value['value_index']];

                    } else {
                        $productsIndex = array();
                    }
                    if ($productsIndex[0] && isset($productsIndex[0])) {
                        $loadedProduct = Mage::getModel('catalog/product')->load($productsIndex[0]);
                    }

                    $info['options'][] = array(
                        'id'        => $value['value_index'],
                        'label'     => $value['label'],
                        'price'     => $configurablePrice,
                        'oldPrice'  => $this->_prepareOldPrice($value['pricing_value'], $value['is_percent']),
                        'products'  => $productsIndex,
                        'stock'     => (($loadedProduct->getId()) && ($loadedProduct->getStockItem()->getIsInStock() == 1)) ? 'in_stock' : 'out_of_stock',
                        'not_is_in_stock' => 'not_is_in_stock',

                    );
                    $optionPrices[] = $configurablePrice;
                }
            }
            /**
             * Prepare formated values for options choose
             */
            foreach ($optionPrices as $optionPrice) {
                foreach ($optionPrices as $additional) {
                    $this->_preparePrice(abs($additional-$optionPrice));
                }
            }
            if($this->_validateAttributeInfo($info)) {
                $attributes[$attributeId] = $info;
            }

            // Add attribute default value (if set)
            if ($preconfiguredFlag) {
                $configValue = $preconfiguredValues->getData('super_attribute/' . $attributeId);
                if ($configValue) {
                    $defaultValues[$attributeId] = $configValue;
                }
            }
        }

        $taxCalculation = Mage::getSingleton('tax/calculation');
        if (!$taxCalculation->getCustomer() && Mage::registry('current_customer')) {
            $taxCalculation->setCustomer(Mage::registry('current_customer'));
        }

        $_request = $taxCalculation->getDefaultRateRequest();
        $_request->setProductClassId($currentProduct->getTaxClassId());
        $defaultTax = $taxCalculation->getRate($_request);

        $_request = $taxCalculation->getRateRequest();
        $_request->setProductClassId($currentProduct->getTaxClassId());
        $currentTax = $taxCalculation->getRate($_request);

        $taxConfig = array(
            'includeTax'        => $taxHelper->priceIncludesTax(),
            'showIncludeTax'    => $taxHelper->displayPriceIncludingTax(),
            'showBothPrices'    => $taxHelper->displayBothPrices(),
            'defaultTax'        => $defaultTax,
            'currentTax'        => $currentTax,
            'inclTaxTitle'      => Mage::helper('catalog')->__('Incl. Tax')
        );

        $config = array(
            'attributes'        => $attributes,
            'template'          => str_replace('%s', '#{price}', $store->getCurrentCurrency()->getOutputFormat()),
            'basePrice'         => $this->_registerJsPrice($this->_convertPrice($currentProduct->getFinalPrice())),
            'oldPrice'          => $this->_registerJsPrice($this->_convertPrice($currentProduct->getPrice())),
            'productId'         => $currentProduct->getId(),
            'chooseText'        => Mage::helper('catalog')->__('Choose an Option...'),
            'taxConfig'         => $taxConfig
        );

        if ($preconfiguredFlag && !empty($defaultValues)) {
            $config['defaultValues'] = $defaultValues;
        }

        $config = array_merge($config, $this->_getAdditionalConfig());

        return Mage::helper('core')->jsonEncode($config);
    }


    /**
     * @return object
     */
    public function getAllowProducts()
    {
        if (!$this->hasAllowProducts()) {
            $products = array();
            $skipSaleableCheck = Mage::helper('catalog/product')->getSkipSaleableCheck();
            $allProducts = $this->getProduct()->getTypeInstance(true)
                ->getUsedProducts(null, $this->getProduct());
            foreach ($allProducts as $product) {
                    $products[] = $product;
            }
            $this->setAllowProducts($products);
        }
        return $this->getData('allow_products');
    }
}