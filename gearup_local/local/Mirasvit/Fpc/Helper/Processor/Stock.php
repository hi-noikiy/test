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
 * @package   mirasvit/extension_fpc
 * @version   1.0.87
 * @copyright Copyright (C) 2018 Mirasvit (https://mirasvit.com/)
 */



class Mirasvit_Fpc_Helper_Processor_Stock extends Mage_Core_Helper_Abstract
{
    /**
     * Frontend stock check for category
     *
     * @param array $productIds
     * @return string
     */
    public function getStock($productIds)
    {
        $catHash = '';
        $request = Mage::app()->getRequest();

        if ($request) {
            $catId =  $request->getParam('id');
        }

        if (isset($catId)) {
            $resource = Mage::getSingleton('core/resource');
            $readConnection = $resource->getConnection('core_read');
            $table = $resource->getTableName('cataloginventory_stock_item');

            foreach ($productIds as $productId) {
                $childrenIds = Mage::getModel('catalog/product_type_grouped')->getChildrenIds($productId);
                $childrenIds = $this->_prepareChildrenIds($childrenIds);
                if (!$childrenIds) {
                    $childrenIds = Mage::getModel('catalog/product_type_configurable')->getChildrenIds($productId);
                    $childrenIds = $this->_prepareChildrenIds($childrenIds);
                }

                if ($childrenIds) {
                    $productIds =array_merge($productIds, $childrenIds);
                }
            }

            $select = $readConnection->select()
                ->from($table, array(
                    'product_id',
                    'qty',
                    'is_in_stock',
                ))->where('product_id IN (?)', $productIds);

            $qtyStockRowSet = $readConnection->fetchAll($select);

            $categoryCollection = Mage::getResourceModel('catalog/product_collection')
                ->joinField('category_id','catalog/category_product','category_id','product_id=entity_id',null,'left')
                ->addAttributeToFilter('category_id', array('in' => $catId))
                ->joinField('is_in_stock','cataloginventory/stock_item','is_in_stock','product_id=entity_id',null,'left')
                ->addAttributeToSelect('*');

            $qtyStockRowSet[] = $categoryCollection->getSize();

            $catHash = md5(serialize($qtyStockRowSet)) . '_' .  md5(serialize($categoryCollection->getData()));
        }

        return $catHash;
    }


    /**
     * Frontend stock check for product
     *
     * @param array $productIds
     * @return string
     */
    public function getProductStock()
    {
        $prodHash = '';
        $fullActionCode = Mage::helper('fpc')->getFullActionCode();
        $request = Mage::app()->getRequest();

        if ($fullActionCode == 'catalog/product_view') {
            if ($request ) {
                $prodId =  $request->getParam('id');
            }
            if (isset($prodId)) {
                $resource = Mage::getSingleton('core/resource');
                $readConnection = $resource->getConnection('core_read');
                $table = $resource->getTableName('cataloginventory_stock_item');

                $childrenIds = Mage::getModel('catalog/product_type_grouped')->getChildrenIds($prodId);
                $childrenIds = $this->_prepareChildrenIds($childrenIds);
                if (!$childrenIds) {
                    $childrenIds = Mage::getModel('catalog/product_type_configurable')->getChildrenIds($prodId);
                    $childrenIds = $this->_prepareChildrenIds($childrenIds);
                }

                if (!$childrenIds
                    && Mage::helper('fpc/compatibility_action')->isMagentoBundleEnabled())
                {
                    $childrenIds = Mage::getResourceSingleton('bundle/selection')->getChildrenIds($prodId);
                    $childrenIds = $this->_prepareChildrenIds($childrenIds);
                }

                if ($childrenIds && is_array($childrenIds)) {
                    $prodIds = array_merge($childrenIds, array($prodId));
                } else {
                    $prodIds = array($prodId);
                }

                $select = $readConnection->select()
                    ->from($table, array(
                        'product_id',
                        'qty',
                        'is_in_stock',
                    ))->where('product_id IN (?)', $prodIds);

                $qtyRowSet = $readConnection->fetchAll($select);

                $prodHash = md5(serialize($qtyRowSet));
            }
        }

        return $prodHash;
    }

    /**
     * @param array $childrenIds
     * @return array
     */
    protected function _prepareChildrenIds($childrenIds)
    {
        $childrenIdsValues = array_values($childrenIds);
        $childrenIds = ($childrenIdsValues && isset($childrenIdsValues[0]) && $childrenIdsValues[0])
            ? array_shift($childrenIdsValues) : false;

        return $childrenIds;
    }
}