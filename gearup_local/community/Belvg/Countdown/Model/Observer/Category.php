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

class Belvg_Countdown_Model_Observer_Category
{
    /**
     * Enter the Tab for a category
     */
    public function injectTabs(Varien_Event_Observer $observer)
    {
        if (Mage::helper('countdown')->isEnabled()) {
            $block = $observer->getEvent()->getBlock();
            if ($block instanceof Mage_Adminhtml_Block_Catalog_Category_Tabs) {
                if ($this->_getRequest()->getActionName() == 'edit' || $this->_getRequest()->getParam('type')) {
                    $block->addTab('custom-countdown-tab', array(
                        'label'   => 'Count Down',
                        'content' => $block->getLayout()->createBlock('countdown/adminhtml_countdown', 'custom-countdown-tab', array('template' => 'belvg/countdown/category_tab_form.phtml'))->toHtml(),
                    ));
                }
            }
        }
    }

    /**
     * Save a Tab for the category
     *
     * @param Mage_Catalog_Model_Category
     * @param boolean Recursive saving = false
     * @return boolean
     */
    public function saveData($category, $additionally)
    {
        if ($this->_getRequest()->getPost()) {
            $info                  = array();
            $info['countdown_off'] = (int) $this->_getRequest()->getParam('offitem', 0);
            $info['countdown_sub'] = (int) $this->_getRequest()->getParam('subtimer', 0);
            $info['entity_type']   = 'category';
            $info['entity_id']     = $category->getId();

            /* Applying to all subcategories and products */
            if ($info['countdown_sub']) {
                $_products = $category->getProductCollection();
                if ($_products->count()) {
                    foreach($_products AS $_product)
                        Mage::getModel('countdown/observer_product')->saveData($_product, FALSE);
                }

                $_cats = Mage::getModel('countdown/countdown')->getAllChildrenCategories($category);
                if ($_cats->count()) {
                    foreach ($_cats AS $_cat) {
                        $this->saveData($_cat, FALSE);
                    }
                }
            }

            try {
                $info['expire_datetime_on']  = Mage::helper('countdown')->getPostDatetime('on');
                $info['expire_datetime_off'] = Mage::helper('countdown')->getPostDatetime('off');;
                /* enabling or disabling the object according to countdown starting and ending time */
                $info['entity_enabled']      = Mage::helper('countdown')->getEntityEnabled($info['expire_datetime_on'], $info['expire_datetime_off']);
                
                $id = (int) $this->_getRequest()->getParam('countdown_id', 0);
                /* If applied recursively – define what to edit */
                if (!$additionally) {
                    $countdown = Mage::getModel('countdown/countdown')->getCountdown('category', $category->getId());
                    $id        = $countdown->getId();
                }

                /* INSERT / UPDATE */
                $countdown = Mage::getModel('countdown/countdown');
                $countdown->setData($info);

                if ($id) {
                    $countdown->setId($id);
                    $info['id'] = $id;
                }

                $countdown->save();

                return TRUE;
            } catch (Exception $e) {
                Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
                return FALSE;
            }
        }
    }

    /**
     * If the module is enabled:
     * 1) Saves Tab data
     * 2) Clears the empty entries in db
     * 3) Enables or disables the category or product after tab saving
     */
    public function saveTabData(Varien_Event_Observer $observer)
    {
        if (Mage::helper('countdown')->isEnabled()) {
            //$category   = Mage::registry('category');
            $category = $observer->getEvent()->getCategory();
            if ($category->getId()) {
                $this->saveData($category, true);
            }

            Mage::getModel('countdown/countdown')->clearBase();
            Mage::getModel('countdown/observer_changestatus')->changeStatus();
        }
    }

    protected function _getRequest()
    {
        return Mage::app()->getRequest();
    }

    public function toHtmlAfter(Varien_Event_Observer $observer)
    {
        $helper = Mage::helper('countdown');
        if (!$helper->isEnabled()) {
            return;
        }

        $transport = $observer->getEvent()->getTransport();
        $block     = $observer->getEvent()->getBlock();

        if ($block instanceof Mage_Catalog_Block_Product_List || $block instanceof Mage_Catalog_Block_Product_New || $block instanceof Belvg_Productslider_Block_Product) {
            $collection = $block->getProductCollection();
            if (!($collection instanceof Mage_Eav_Model_Entity_Collection_Abstract)) {
                $collection = $block->getLoadedProductCollection();
            }

            if ($collection instanceof Mage_Eav_Model_Entity_Collection_Abstract && $collection->count()) {
                $listBlock = Mage::app()->getLayout()->createBlock('countdown/product_list');

                //$ids     = $collection->getColumnValues('entity_id');
                //$listBlock->setIds($ids);
                $scriptId  = rand();
                $prefix    = Belvg_Countdown_Helper_Data::PRODUCT_LIST_CONTAINER_PREFIX;
                $listBlock
                    ->setCollection($collection)
                    ->setContainer($prefix, $scriptId)
                    ->setTemplate('belvg/countdown/product_list_script.phtml');

                $html = '<div class="' . $prefix . ' ' . $listBlock->getContainerClass() . '">' . $transport->getHtml() . '</div>' . $listBlock->toHtml();

                $transport->setHtml($html);
            }
        }
    }

    public function loadCountdownToProductCollection(Varien_Event_Observer $observer)
    {
        $helper = Mage::helper('countdown');
        if ($helper->isEnabled()) {
            $array = array();

            $collection = $observer->getEvent()->getCollection();
            $filters    = $collection->getLimitationFilters();

            if (isset($filters['category_id']) && $filters['category_id']) {
                $setting    = $helper->getListSettings();
                $productIds = $collection->getColumnValues('entity_id');
                $countdowns = Mage::getModel('countdown/countdown')->getCountdownCollection('product', $productIds);
                foreach ($countdowns AS $countdown) {
                    $array[$countdown->getEntityId()] = array(
                        'expireDatetimeOff' => $countdown->getExpireDatetimeOff(),
                        'expireDatetimeOn' => $countdown->getExpireDatetimeOn(),
                    );
                }

                foreach ($collection AS &$item) {
                    if (isset($array[$item->getId()])) {
                        $item->setData('countdown_data', $array[$item->getId()]);
                    }
                }
            }
        }

        return $this;
    }
}
 
 