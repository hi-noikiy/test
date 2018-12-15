<?php

/**
 * @author Amasty Team
 * @copyright Copyright (c) 2018 Amasty (https://www.amasty.com)
 * @package Amasty_StoreCredit
 */
class Amasty_StoreCredit_Model_Resource_Attribute_Backend_StoreCredit_Amount extends Mage_Core_Model_Resource_Db_Abstract
{
    protected function _construct()
    {
        $this->_init('amstcred/amount', 'amount_id');
    }


    /**
     * @param array $listPrices
     *
     * @return $this
     */
    public function insertAmounts(array $listPrices)
    {
        $this->_getWriteAdapter()->insertMultiple($this->getMainTable(), $listPrices);
        return $this;
    }

    /**
     * @param Mage_Catalog_Model_Product $product
     * @param Mage_Eav_Model_Entity_Attribute $attribute
     *
     * @return $this
     */
    public function deleteAllAmounts($product, $attribute)
    {
        $condition = array(
            'product_id=?' => $product->getId(),
            'attribute_id=?' => $attribute->getId(),
        );

        if (!$attribute->isScopeGlobal()) {
            if ($storeId = $product->getStoreId()) {
                $condition['website_id IN (?)'] = array(0, Mage::app()->getStore($storeId)->getWebsiteId());
            }
        }

        $this->_getWriteAdapter()->delete($this->getMainTable(), $condition);
        return $this;
    }

    /**
     *
     * @param Mage_Catalog_Model_Product $product
     * @param Mage_Eav_Model_Entity_Attribute $attribute
     * @return array
     */
    public function loadAmounts($product, $attribute)
    {
        $readAdapter = $this->_getReadAdapter();
        $query = $readAdapter->select()
            ->from($this->getMainTable(), array(
                'website_id',
                'value'
            ))
            ->where('product_id=:product_id')
            ->where('attribute_id=:attribute_id');
        $bindParams = array(
            'product_id' => $product->getId(),
            'attribute_id' => $attribute->getId()
        );
        if ($attribute->isScopeGlobal()) {
            $query->where('website_id=0');
        } else {
            if ($storeId = $product->getStoreId()) {
                $query->where('website_id IN (0, :website_id)');
                $bindParams['website_id'] = Mage::app()->getStore($storeId)->getWebsiteId();
            }
        }
        return $readAdapter->fetchAll($query, $bindParams);
    }
}
