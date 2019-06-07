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



class Mirasvit_Fpc_Adminhtml_Fpc_FlushcacheController extends Mage_Adminhtml_Controller_Action
{
    /**
     * @var Mirasvit_Fpc_Model_Cache
     */
    protected $_cache;

    /**
     * @var Mirasvit_Fpc_Helper_Action
     */
    protected $_action;

    public function _construct()
    {
        $this->_cache = Mage::getSingleton('fpc/cache');
        $this->_action = Mage::helper('fpc/action');
    }

    protected function _isAllowed()
    {
        return Mage::getSingleton('admin/session')->isAllowed('system/fpc');
    }

    /**    
     */
    public function flushAction()
    {
        $data = $this->getRequest()->getPost();
        $storeId = false;
        $action = false;

        if ($data && isset($data['fpc_flushcache_store']) && $data['fpc_flushcache_store']) {
            $storeId = $data['fpc_flushcache_store'];
        }
        if ($data && isset($data['fpc_flushcache_actions']) && $data['fpc_flushcache_actions']) {
            $action = $data['fpc_flushcache_actions'];
        }

        if ($storeId && !$action) {
            $storeTag = array(Mirasvit_Fpc_Model_Config::STORE_TAG.$storeId);
            $this->_cache->clearCacheByTags($storeTag);
            $storeInfo = $this->getStoreInfo($storeId);
            Mage::getSingleton('adminhtml/session')->addNotice(Mage::helper('adminhtml')
                    ->__('Full Page Cache cache storage has been flushed for store: '.$storeInfo));
        } elseif (!$storeId && $action) {
            $actionTag = $this->_action->getActionTag($action);
            $this->_cache->clearCacheByTags($actionTag);
            Mage::getSingleton('adminhtml/session')->addNotice(Mage::helper('adminhtml')
                ->__('Full Page Cache cache has been flushed for action "'.$action.'".'));
        } elseif ($storeId && $action) {
            $actionTag = $this->_action->getActionTag($action, $storeId);
            $this->_cache->clearCacheByTags($actionTag);
            $storeInfo = $this->getStoreInfo($storeId);
            Mage::getSingleton('adminhtml/session')->addNotice(Mage::helper('adminhtml')
                ->__('Full Page Cache cache has been flushed for action "'.$action.'". Only for store: '.$storeInfo));
        } elseif (!$storeId && !$action) {
            Mage::getSingleton('adminhtml/session')->addWarning(Mage::helper('adminhtml')
                ->__('Store and action are not selected. Cache storage wasn\'t flushed.'));
        }

        $this->_redirectReferer();
    }

    /**
     * @return string
     */
    protected function getStoreInfo($storeId)
    {
        $store = Mage::getModel('core/store')->load($storeId);

        return Mage::helper('adminhtml')->escapeHtml($store->getName()).' — '.$store->getBaseUrl().'&nbsp;&nbsp;&nbsp;( ID: '.$store->getId().')';
    }
}
