<?php
class Ktpl_Productimport_Model_Convert_Adapter_Productimport extends Mage_Catalog_Model_Convert_Adapter_Product
{
    /**
     * Save product (import)
     *
     * @param array $importData
     * @throws Mage_Core_Exception
     * @return bool
     */
    public function saveRow(array $importData)
    {
    	if (empty($importData['store'])) 
        {
            if (!is_null($this->getBatchParams('store')))
            {
                $store = $this->getStoreById($this->getBatchParams('store'));
            } 
            else
            {
                $message = Mage::helper('catalog')->__('Skip import row, required field "%s" not defined', 'store');
                Mage::throwException($message);
                Mage::log(sprintf('Skip import row, required field "store" not defined', $message), null, 'ktpl_product_import_errors.log');
            }
        }
        else
        {
            $store = $this->getStoreByCode($importData['store']);
        }

        if ($store === false) {
            $message = Mage::helper('catalog')->__('Skip import row, store "%s" field not exists', $importData['store']);
            Mage::throwException($message);
            Mage::log(sprintf('Skip import row, store "' . $importData['store'] . '" field not exists', $message), null, 'ktpl_product_import_errors.log');
        }
        $product = $this->getProductModel()->reset();
        $productId = $product->getIdBySku($importData['sku']);
        $product->load($productId);
        
        if(!$product)
        {
            $message = Mage::helper('catalog')->__('Skip import row, sku "%s" product not exists', $importData['sku']);
            Mage::throwException($message);
            Mage::log(sprintf('Skip import row, sku "' . $importData['sku'] . '" product not exists', $message), null, 'ktpl_product_import_errors.log');
        }
        
        $product->setStoreId($store->getId());
        $this->setProductTypeInstance($product);
        
        foreach ($this->_ignoreFields as $field)
        {
            if (isset($importData[$field]))
            {
                unset($importData[$field]);
            }
        }
        
        if ($store->getId() != 0) 
        {
            $websiteIds = $product->getWebsiteIds();
            if (!is_array($websiteIds)) 
            {
                $websiteIds = array();
            }
            if (!in_array($store->getWebsiteId(), $websiteIds)) 
            {
                $websiteIds[] = $store->getWebsiteId();
            }
            $product->setWebsiteIds($websiteIds);
        }
        
        foreach ($importData as $field => $value) 
        {
            if (in_array($field, $this->_imageFields)) 
            {
                continue;
            }
            if (in_array($field, $this->_inventoryFields)) {
                continue;
            }
            $attribute = $this->getAttribute($field);
            if (!$attribute) {
                continue;
            }
            
            $isArray = false;
            $setValue = $value;
            
            if ($attribute->getFrontendInput() == 'multiselect') {
                $value = split(self::MULTI_DELIMITER, $value);
                $isArray = true;
                $setValue = array();
            }

            if ($value && $attribute->getBackendType() == 'decimal') {
                $setValue = $this->getNumber($value);
            }
            
            if ($attribute->usesSource()) 
            {
                $options = $attribute->getSource()->getAllOptions(false);
                if ($isArray) {
                    foreach ($options as $item) 
                    {
                        if (in_array($item['label'], $value)) 
                        {
                            $setValue[] = $item['value'];
                        }
                    }
                }
                else
                {
                    $setValue = null;
                    foreach ($options as $item) 
                    {
                        if ($item['label'] == $value)
                        {
                            $setValue = $item['value'];
                        }
                    }
                }
            }
            $product->setData($field, $setValue);
        }
        
        if (!$product->getVisibility()) {
            $product->setVisibility(Mage_Catalog_Model_Product_Visibility::VISIBILITY_BOTH);
        }
        if(isset($importData['status']))
        {
            $product->setStatus($importData['status']);
        }
        
        // stock 
        $stockData = array();
        $inventoryFields = isset($this->_inventoryFieldsProductTypes[$product->getTypeId()]) ? $this->_inventoryFieldsProductTypes[$product->getTypeId()] : array();
        foreach ($inventoryFields as $field) {
            if (isset($importData[$field])) {
                if (in_array($field, $this->_toNumber)) {
                    $stockData[$field] = $this->getNumber($importData[$field]);
                } else {
                    $stockData[$field] = $importData[$field];
                }
            }
        }
        $product->setStockData($stockData);
        // stock 
        
        $product->setIsMassupdate(true);
        $product->setExcludeUrlRewrite(true);
        
        try
        {
            $product->save();
        }
        catch (Exception $e)
        {

            Mage::log(sprintf('failed to import sku: ' . $importData["sku"] . ' %s', $e->getMessage()), null, 'ktpl_product_import_errors.log');
        }
        return true;
    }
}