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

class Belvg_Countdown_Block_Product_List extends Belvg_Countdown_Block_Countdown
{
    protected $_ids        = FALSE;
    protected $_collection = FALSE;
    protected $_prefix     = FALSE;
    protected $_scriptId   = FALSE;

    protected function _getLoadedProductCollection()
    {
        if ($this->_collection !== FALSE) {
            return $this->_collection;
        }
        
        if (Mage::app()->getFrontController()->getRequest()->getModuleName() == 'countdown') {
            $layer = Mage::getSingleton('countdown/layer');
        } else {
            $layer = Mage::getSingleton('catalog/layer');
        }

        return $layer->getProductCollection();
    }

    public function setCollection($collection) 
    {
        $this->_collection = $collection;

        return $this;
    }

    public function setIds($ids) 
    {
        $this->_ids = $ids;

        return $this;
    }

    public function setContainer($prefix, $scriptId)
    {
        $this->_prefix   = $prefix;
        $this->_scriptId = $scriptId;

        return $this;
    }

    public function getScriptId()
    {
        if (!$this->_scriptId) {
            $this->_scriptId = rand();
        }

        return $this->_scriptId;
    }

    public function getPrefix()
    {
        if (!$this->_prefix) {
            $this->_prefix = Belvg_Countdown_Helper_Data::PRODUCT_LIST_CONTAINER_PREFIX;
        }

        return $this->_prefix;
    }

    public function getContainerClass()
    {
        return $this->_prefix . $this->_scriptId;
    }

    /**
     * Getting current product ids list
     * @return array
     */
    public function getProductIds() 
    {
        if ($this->_ids === FALSE) {
            $collection = $this->_getLoadedProductCollection();
            $this->_ids = $collection->getColumnValues('entity_id');
        }

        return $this->_ids;
    }

    public function getCountdowns() 
    {
        $countdows  = array();
        $collection = $this->_getLoadedProductCollection();

        foreach ($collection as $_product) {
            if ($_product->getCountdownData()) {
                $countdows[$_product->getId()] = $_product->getCountdownData();
            }
        }

        return $countdows;
    }
}