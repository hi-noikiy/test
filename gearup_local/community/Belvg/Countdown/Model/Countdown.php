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
 * @copyright  Copyright (c) 2010 - 2014 BelVG LLC. (http://www.belvg.com)
 * @license    http://store.belvg.com/BelVG-LICENSE-COMMUNITY.txt
 */

class Belvg_Countdown_Model_Countdown extends Mage_Core_Model_Abstract
{
    public function _construct()
    {
        parent::_construct();
        $this->_init('countdown/countdown');
    }

    /**
     * Get 'Countdown' object, filtered by type and entity id
     *
     * @param string
     * @param int Entity Id (example: if $type is 'product', $entity_id is Product id)
     * @return Belvg_Countdown_Model_Countdown
     */
    public function getCountdown($type, $id)
    {
        $countdown = Mage::getModel('countdown/countdown')->getCollection()
                        ->addFieldToFilter('entity_type', $type)
                        ->addFieldToFilter('entity_id', $id);
        $countdown = $countdown->getFirstItem();

        return $countdown;
    }

    public function getCountdownCollection($type, $ids)
    {
        if (!is_array($ids)) {
            $ids = explode(',', $ids);
        }

        $countdown = Mage::getModel('countdown/countdown')->getCollection()
                        ->addFieldToFilter('entity_type', $type)
                        ->addFieldToFilter('entity_id', array('in' => $ids));

        return $countdown;
    }

    /**
     * Enable/disable categories
     *
     * @param mixed Id categories (array or string separated by commas)
     * @param boolean Enable = true
     */
    public function categoryEnabled($ids, $enabled)
    {
        if (!is_array($ids) AND $ids!='') {
            $ids = explode(',',$ids);
        }

        //print_r($ids); die;
        if (count($ids)) {
            foreach ($ids AS $categoryId) {
                $category = Mage::getModel('catalog/category')->load($categoryId);
                if ($category->getId()) {
                    if ($enabled) {
                        if ($category->getIsActive()==0) {
                            $category->setIsActive(1)->save();
                        }
                    } else {
                        if ($category->getIsActive()==1) {
                            $category->setIsActive(0)->save();
                        }
                    }
                }
            }
        }
    }

    /**
     * Enable/disable Products
     *
     * @param mixed Id products (array or string separated by commas)
     * @param boolean Enable = true
     */
    public function productEnabled($ids, $enabled)
    {
        
        if (!is_array($ids) AND $ids!='') {
            $ids = explode(',',$ids);
        }

        if (count($ids)) {
            if ($enabled) {
                $statusFrom = Mage_Catalog_Model_Product_Status::STATUS_DISABLED;
                $statusTo   = Mage_Catalog_Model_Product_Status::STATUS_ENABLED;
            } else {
                $statusFrom = Mage_Catalog_Model_Product_Status::STATUS_ENABLED;
                $statusTo   = Mage_Catalog_Model_Product_Status::STATUS_DISABLED;
            }

            Mage::getSingleton('catalog/product_action')
                ->updateAttributes($ids, array('status' => $statusTo), 0);
            Mage::getSingleton('catalog/product_action')
                ->updateAttributes($ids, array('status' => $statusTo), Mage::app()->getStore()->getId());
        }
    }

    /**
     * Receive all subcategories for a category
     * Not paying attention to the enabled/disabled category
     *
     * @param Mage_Catalog_Model_Category
     * @return Mage_Catalog_Model_Resource_Category_Collection
     */
    public function getAllChildrenCategories($category)
    {
        $collection = Mage::getModel('catalog/category')->getCollection();
        $collection
            ->addFieldToFilter('parent_id', $category->getId())
            ->setOrder('position', Varien_Db_Select::SQL_ASC)
            ->load();

        return $collection;
    }

    /**
     * Receive �status� attribute id for the product
     *
     * @return int
     */
    public function getProductStatusAttributId()
    {
        $attribute   = Mage::getModel('eav/entity_attribute')->loadByCode(Mage::getResourceModel('catalog/product')->getTypeId(), 'status');
        $attributeId = $attribute->getAttributeId();
        return $attributeId;
    }

    /**
     * Collect the �countdown� objects to enable, filtered by type
     *
     * @param string (example: 'product' or 'category')
     * @return array
     */
    public function loadChangeOn($type)
    {
        $current    = Mage::helper('countdown')->getCurrentTime('sql');
        $collection = Mage::getModel('countdown/countdown')->getCollection();
        $collection->addChangeOnFilter($type, $current);
        $collection->load();

        return $collection->getColumnValues('entity_id');
    }

    /**
     * Collect the �countdown� objects to disable, filtered by type
     *
     * @param string (example: 'product' or 'category')
     * @return array
     */
    public function loadChangeOff($type)
    {
        $current    = Mage::helper('countdown')->getCurrentTime('sql');
        $collection = Mage::getModel('countdown/countdown')->getCollection();
        $collection->addChangeOffFilter($type, $current);
        $collection->load();

        return $collection->getColumnValues('entity_id');
    }

    /**
     * Change enabled/disabled option for a particular type of �countdown� objects
     *
     * @param mixed Id products (array or string separated by commas)
     * @param boolean Enable = true
     * @param string (example: 'product' or 'category')
     */
    public function changeEntityEnabled($ids, $enabled, $type)
    {
        if (!is_array($ids) AND $ids!='') {
            $ids = explode(',',$ids);
        }

        if (count($ids)) {
            $collection = Mage::getModel('countdown/countdown')->getCollection()
                ->addFieldToFilter('entity_type', $type)
                ->addFieldToFilter('entity_id', array("in" => $ids));
            if ($collection->count()) {
                $collection->setDataToAll('entity_enabled', $enabled)->save();
            }
        }
    }

    /**
     * Remove all empty �countdown� object
     */
    public function clearBase()
    {
        $collection = Mage::getModel('countdown/countdown')->getCollection();
        $collection
            ->addFieldToFilter('expire_datetime_off', '0000-00-00 00:00:00')
            ->addFieldToFilter('expire_datetime_on', '0000-00-00 00:00:00');
        $collection->delete();
    }

    /**
     * Collection of shortly disabled products
     *
     * @return Mage_Catalog_Model_Resource_Product_Collection
     */
    public function getProductCollection()
    {
        $current     = Mage::helper('countdown')->getCurrentTime('sql');
        $tablePrefix = Mage::getConfig()->getTablePrefix();
        $count       = Mage::helper('countdown')->getProductsCount();

        if ($count) {

            $collection = Mage::getModel('catalog/product')->getCollection();
            /*$collection->addAttributeToSelect('*')->getSelect()
                ->joinLeft(array('cd' => $tablePrefix . 'belvg_countdown'), "cd.entity_id = e.entity_id AND cd.entity_type='product'")
                ->where('cd.expire_datetime_off>"' . $current . '" AND cd.entity_enabled=1 AND cd.countdown_off=1')
                ->order('cd.expire_datetime_off asc')
                ->group('cd.entity_id')
                ->limit($count); */

            $collection->addAttributeToSelect('*')->getSelect()
                ->joinLeft(array('cd' => $tablePrefix . 'belvg_countdown'), "cd.entity_id = e.entity_id AND cd.entity_type='product'")
                ->where('cd.expire_datetime_off>"' . $current . '" && cd.expire_datetime_on <="' . $current .'"')
                ->order('cd.expire_datetime_off asc')
                ->group('cd.entity_id')
                ->limit($count);

            Mage::getSingleton('catalog/product_status')->addVisibleFilterToCollection($collection);
            Mage::getSingleton('catalog/product_visibility')->addVisibleInCatalogFilterToCollection($collection);
        } else {
            $collection = FALSE;
        }

        return $collection;
    }
    
    public function getProductEndedCollection()
    {
        $current     = Mage::helper('countdown')->getCurrentTime('sql');
        $tablePrefix = Mage::getConfig()->getTablePrefix();
        $count       = Mage::helper('countdown')->getProductsCount();

        if ($count) {
            $collection = Mage::getModel('catalog/product')->getCollection();
            $collection->addAttributeToSelect('*')->getSelect()
                ->joinLeft(array('cd' => $tablePrefix . 'belvg_countdown'), "cd.entity_id = e.entity_id AND cd.entity_type='product'")
                ->where('cd.expire_datetime_off<"' . $current . '"')
                ->order('cd.expire_datetime_off desc')
                ->group('cd.entity_id')
                ->limit($count);

            Mage::getSingleton('catalog/product_status')->addVisibleFilterToCollection($collection);
            Mage::getSingleton('catalog/product_visibility')->addVisibleInCatalogFilterToCollection($collection);
        } else {
            $collection = FALSE;
        }

        return $collection;
    }

    /**
     * Collection of shortly disabled categories
     *
     * @return Mage_Catalog_Model_Resource_Category_Collection
     */
    public function getCategoryCollection()
    {
        $tablePrefix = Mage::getConfig()->getTablePrefix();
        $current     = Mage::helper('countdown')->getCurrentTime('sql');
        $count       = Mage::helper('countdown')->getCategoriesCount();

        if ($count) {
            $collection = Mage::getModel('catalog/category')->getCollection();
            $collection->addAttributeToSelect('*')->getSelect()
                ->joinLeft(array('cd' => $tablePrefix . 'belvg_countdown'), "cd.entity_id = e.entity_id AND cd.entity_type='category'")
                ->where('cd.expire_datetime_off>"' . $current . '" AND cd.entity_enabled=1 AND cd.countdown_off=1')
                ->order('cd.expire_datetime_off asc')
                ->group('cd.entity_id')
                ->limit($count);
        } else {
            $collection = FALSE;
        }

        return $collection;
    }
}
