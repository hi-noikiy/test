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

class Belvg_Countdown_Model_Observer_Product
{
    /**
     * Enter the Tab for a product
     */
    public function injectTabs(Varien_Event_Observer $observer)
    {
        if (Mage::helper('countdown')->isEnabled()) {
            $block  = $observer->getEvent()->getBlock();
            if ($block instanceof Mage_Adminhtml_Block_Catalog_Product_Edit_Tabs) {
                if ($this->_getRequest()->getActionName() == 'edit' || $this->_getRequest()->getParam('type')) {
                    $block->addTab('custom-countdown-tab', array(
                        'label'   => 'Count Down',
                        'content' => $block->getLayout()->createBlock('countdown/adminhtml_countdown', 'custom-countdown-tab', array('template' => 'belvg/countdown/product_tab_form.phtml'))->toHtml(),
                    ));
                }
            }
        }
    }

    /**
     * Save a Tab for the product
     *
     * @param Mage_Catalog_Model_Product
     * @param boolean Recursive saving = false
     * @return boolean
     */
    public function saveData($product, $additionally)
    {
        if ($post = $this->_getRequest()->getPost()) {
            $info                  = array();
            $info['countdown_off'] = (int) $this->_getRequest()->getParam('offitem', 0);
            $info['entity_type']   = 'product';
            $info['entity_id']     = $product->getId();
            $info['offer_price']   = $this->_getRequest()->getParam('offer_price') ? $this->_getRequest()->getParam('offer_price') : null ;
            $info['offer_qty']   = $this->_getRequest()->getParam('offer_qty') ? $this->_getRequest()->getParam('offer_qty') : null;

            try {
                $info['expire_datetime_on']  = Mage::helper('countdown')->getPostDatetime('on');
                $info['expire_datetime_off'] = Mage::helper('countdown')->getPostDatetime('off');

                /* enabling or disabling the object according to countdown starting and ending time */
                /*$info['entity_enabled']      = Mage::helper('countdown')->getEntityEnabled($info['expire_datetime_on'], $info['expire_datetime_off']);*/
                
                if($info['expire_datetime_on'] != '0000-00-00 00:00:00' || $info['expire_datetime_off'] != '0000-00-00 00:00:00'){
                    $id = (int)$this->_getRequest()->getParam('countdown_id', 0);
                    /* If applied recursively – define what to edit */
                    if (!$additionally) {
                        $countdown = Mage::getModel('countdown/countdown')->getCountdown('product', $product->getId());
                        $id        = $countdown->getId();
                    }else{

                        /* INSERT / UPDATE */ 
                        if($id){
                            $countdown = Mage::getModel('countdown/countdown')->getCountdown('product', $product->getId());
                            $id        = $countdown->getId();
                        }else{
                            $countdown = Mage::getModel('countdown/countdown');
                        }
                    }
                    
                    $countdown->setData($info);

                    if ($id) {
                        $countdown->setId($id);
                        $info['id'] = $id;
                    }

                    $countdown->save();
                }

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
     * 3) Enables or disables the product after tab saving
     */
    public function saveTabData(Varien_Event_Observer $observer)
    {
        if (Mage::helper('countdown')->isEnabled()) {
            $product = $observer->getEvent()->getProduct();
            if ($product->getId()) {
                $this->saveData($product, TRUE);
            }

            //Mage::getModel('countdown/countdown')->clearBase();
            //Mage::getModel('countdown/observer_changestatus')->changeStatus();
        }
    }

    protected function _getRequest()
    {
        return Mage::app()->getRequest();
    }
    
    protected function _getColSku() {
        return Mage_ImportExport_Model_Import_Entity_Product::COL_SKU;
    }
    
    public function importFinishBefore(Varien_Event_Observer $observer)
    {
        $adapter = $observer->getEvent()->getAdapter();
        $affectedEntityIds = $adapter->getAffectedEntityIds();

        if (empty($affectedEntityIds)) {
            return;
        }

        while ($bunch = $adapter->getNextBunch()) {
            foreach ($bunch as $rowNum => $rowData) {
                if (!$adapter->isRowAllowedToImport($rowData, $rowNum)) {
                    continue;
                }
                
                $newSku = $adapter->getNewSku();

                if (!isset($newSku[$rowData[$this->_getColSku()]]['entity_id'])) {
                    continue;
                }

                $productId = $newSku[$rowData[$this->_getColSku()]]['entity_id'];
                //$rowData['countdown_on']
                $this->_importCountdown($productId, $rowData);
            }
        }

        Mage::getModel('countdown/countdown')->clearBase();
        Mage::getModel('countdown/observer_changestatus')->changeStatus();
    }

    protected function _importCountdown($productId, $rowData)
    {
        if ($productId) {
            $info                  = array();
            $info['countdown_off'] = (int) $rowData['countdown_offitem'];
            $info['entity_type']   = 'product';
            $info['entity_id']     = $productId;

            try {
                $info['expire_datetime_on']  = $rowData['countdown_on'];
                $info['expire_datetime_off'] = $rowData['countdown_off'];
                /* enabling or disabling the object according to countdown starting and ending time */
                $info['entity_enabled']      = Mage::helper('countdown')->getEntityEnabled($info['expire_datetime_on'], $info['expire_datetime_off']);

                $countdown = Mage::getModel('countdown/countdown')->getCountdown('product', $productId);
                $id        = $countdown->getId();

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

}
