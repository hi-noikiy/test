<?php

/**
 * Magestore.
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Magestore.com license that is
 * available through the world-wide-web at this URL:
 * http://www.magestore.com/license-agreement.html
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade this extension to newer
 * version in the future.
 *
 * @category    Magestore
 * @package     Magestore_Affiliateplus
 * @copyright   Copyright (c) 2012 Magestore (http://www.magestore.com/)
 * @license     http://www.magestore.com/license-agreement.html
 */
namespace Magestore\Affiliateplus\Block\Account;

/**
 * Class Edit
 * @package Magestore\Affiliateplus\Block\Account
 */
/**
 * Class Materials
 * @package Magestore\Affiliateplus\Block\Account
 */
class Materials extends \Magestore\Affiliateplus\Block\AbstractTemplate
{
    /**
     * @return mixed
     */
    public function getPageIdentifier(){
        return $this->_configHelper->getMaterialConfig('page');
    }

    /**
     * @return int
     */
    public function getPageId(){
        $page = $this->_pageModel;
        $pageId = $page->checkIdentifier($this->getPageIdentifier(), $this->_storeManager->getStore()->getId());
        return $pageId;
    }

    /**
     * @return \Magento\Cms\Model\Page
     */
    public function getPage(){
        return $this->_pageModel;
    }

    /**
     * Construct function
     */
    protected function _construct(){
        parent::_construct();
        $page = $this->_pageModel;
        if ($pageId = $this->getPageId())
            $page->setStoreId($this->_storeManager->getStore()->getId())->load($pageId);
    }

    /**
     * @return string
     */
    protected function _toHtml(){
        $page = $this->_pageModel;

        if ($pageId = $this->getPageId())
            $page->setStoreId($this->_storeManager->getStore()->getId())->load($pageId);

        $html = $page->getContentHeading();

        $html .= $page->getContent();
        return $html;
    }
}