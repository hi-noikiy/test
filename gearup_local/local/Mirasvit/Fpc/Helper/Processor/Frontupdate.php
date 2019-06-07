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



/**
 * Pimgento_Product compatibility
 */
class Mirasvit_Fpc_Helper_Processor_Frontupdate extends Mage_Core_Helper_Abstract
{
    /**
     * Check if use frontend update method
     *
     * @return bool
     */
    public function isFrontUpdateEnabled()
    {
        if (Mage::helper('core')->isModuleEnabled('Pimgento_Product')) {
            return true;
        }

        return false;
    }

    /**
     * Frontend check data for category
     *
     * @param array $productIds
     * @return string
     */
    public function getCategoryListingHash($productIds)
    {
        $catHash = '';
        $request = Mage::app()->getRequest();

        if ($request) {
            $catId =  $request->getParam('id');
        }

        if (isset($catId)) {
            $categoryCollection = Mage::getResourceModel('catalog/product_collection')
                ->addMinimalPrice()
                ->addFinalPrice()
                ->addTaxPercents()
                ->joinField('category_id','catalog/category_product','category_id','product_id=entity_id',null,'left')
                ->addAttributeToFilter('category_id', array('in' => $catId))
                ->joinField('is_in_stock','cataloginventory/stock_item','is_in_stock','product_id=entity_id',null,'left')
                ->addAttributeToSelect('*')
                ->addFieldToFilter('visibility', array(
                    Mage_Catalog_Model_Product_Visibility::VISIBILITY_BOTH,
                    Mage_Catalog_Model_Product_Visibility::VISIBILITY_IN_CATALOG
                ));

            $categoryCollectionSize = $categoryCollection->getSize();

            $categoryCollection->addFieldToFilter('entity_id', array('in' => $productIds));

            $catHash = md5(serialize($categoryCollectionSize))
                . '_' .  md5(serialize($categoryCollection->getData()));
        }

        return $catHash;
    }


    /**
     * Frontend check data for product
     *
     * @return string
     */
    public function getProductListingHash()
    {
        $prodHash = '';
        $fullActionCode = Mage::helper('fpc')->getFullActionCode();
        $request = Mage::app()->getRequest();

        if ($fullActionCode == 'catalog/product_view') {
            if ($request ) {
                $prodId =  $request->getParam('id');
            }
            if (isset($prodId)) {
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

                if (is_array($childrenIds)) {
                    $prodIds = array_merge($childrenIds, array($prodId));
                } else {
                    $prodIds = array($prodId);
                }

                foreach ($prodIds as $key => $prodId) {
                    $productData = Mage::getModel('catalog/product')->load($prodId)->getData();
                    $prodHash .= md5(json_encode($productData));
                }
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