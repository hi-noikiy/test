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
namespace Magestore\Affiliateplus\Controller\Index;

/**
 * Action Index
 */
class Materials extends \Magestore\Affiliateplus\Controller\AbstractAction
{
    /**
     * Execute action
     */
    public function execute()
    {
        if (!$this->_dataHelper->isAffiliateModuleEnabled()) {
            return $this->_redirectUrl($this->getBaseUrl());
        }
        $storeId = $this->_storeManager->getStore()->getId();
        if (!$this->_dataHelper->getConfig('affiliateplus/general/material_page', $storeId)){
            $this->messageManager()->addError(__('The Materials function has been disabled.'));
            return $this->_redirect('*/*/');
        }
        if($this->_accountHelper->accountNotLogin()) {
            $this->messageManager()->addError(__('You need to login before access this page'));
            return $this->_redirect('*/*/');
        }
        if ($this->_accountHelper->isNotAvailableAccount()){
            return $this->_redirect('affiliateplus/index/index');
        }

        $resultPage = $this->resultFactory->create(\Magento\Framework\Controller\ResultFactory::TYPE_PAGE);

        $page = $this->_objectManager->create('Magento\Cms\Model\Page');

        if ($page->getId()) {
            $resultPage->getConfig()->getTitle()->set($page->getContentHeading());
        } else {

            $resultPage->getConfig()->getTitle()->set(__('Affiliate Material'));
        }
        return $resultPage;
    }
}
