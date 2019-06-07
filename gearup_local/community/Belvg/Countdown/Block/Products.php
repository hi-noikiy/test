<?php
/**
 * BelVG LLC.
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the EULA
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://store.belvg.com/BelVG-LICENSE-COMMUNITY.txt
 *
/**********************************************
 *        MAGENTO EDITION USAGE NOTICE        *
 **********************************************/
/* This package designed for Magento COMMUNITY edition
 * BelVG does not guarantee correct work of this extension
 * on any other Magento edition except Magento COMMUNITY edition.
 * BelVG does not provide extension support in case of
 * incorrect edition usage.
/**********************************************
 *        DISCLAIMER                          *
 **********************************************/
/* Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future.
 **********************************************
 * @category   Belvg
 * @package    Belvg_Countdown
 * @copyright  Copyright (c) 2010 - 2012 BelVG LLC. (http://www.belvg.com)
 * @license    http://store.belvg.com/BelVG-LICENSE-COMMUNITY.txt
 */

class Belvg_Countdown_Block_Products extends Belvg_Countdown_Block_Countdown
{
    /**
     * Block for simplifying the entry: {{block type='countdown/products' name="your_categories_block_name"}}
     */
    protected function _construct()
    {
        parent::_construct();
        $this->setTemplate('belvg/countdown/blocks/products.phtml');
    }

    /**
     * Collection of shortly disabled products
     *
     * @return Mage_Catalog_Model_Resource_Product_Collection
     */
    public function getProductCollection()
    {
        return Mage::getModel('countdown/countdown')->getProductCollection();
    }
    
    public function getProductEndedCollection()
    {
        $collection = Mage::getModel('countdown/countdown')->getProductEndedCollection()->setPageSize(1);
        foreach ($collection as $product) {
            if ($product->getStockItem()->getIsInStock()) {
                $stockItem = Mage::getModel('cataloginventory/stock_item')->loadByProduct($product->getId());
                $stockItem->setQty(0);
                $stockItem->setIsInStock(0);
                $stockItem->save();
            }
        }

        return $collection;
    }
}