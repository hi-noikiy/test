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

class Welcome extends \Magestore\Affiliateplus\Block\AbstractTemplate
{
    /**
     * @return mixed
     */

    public function getPageIdentifier(){
        return $this->_configHelper->getGeneralConfig('welcome_page');
    }
    /**
     * @return mixed
     */
    public function getPageId(){
        $pageId = $this->_pageModel->checkIdentifier($this->getPageIdentifier(), $this->_storeManager->getStore()->getId());
        return $pageId;
    }

    /**
     * @return \Magento\Cms\Model\Page
     */
    public function getPage(){
        return $this->_objectManager->create('Magento\Cms\Model\Page')->load($this->getPageId());
    }

    /**
     * Contruct
     */
    protected function _construct(){
        parent::_construct();

    }

    /**
     * @return string
     */
    protected function _toHtml(){

        $html = $this->getLayout()->getMessagesBlock()->getGroupedHtml();
        $resultPage = $this->_resultPageFactory->create();
        $contentHeadingBlock = $resultPage->getLayout()->getBlock('page_content_heading');
        if ($contentHeadingBlock) {
            $contentHeading = $this->_escaper->escapeHtml($this->getPage()->getContentHeading());
            $contentHeadingBlock->setContentHeading($contentHeading);
            $html .= $contentHeadingBlock->toHtml();
        }else{
            $html .= '<div class="affiiate-page-title">
             <h1>'.$this->getPage()->getContentHeading().'</h1>
            </div>';
        }
        $html .= $this->_filterProvider->getPageFilter()->filter($this->getPage()->getContent());
        return $html;
    }
}