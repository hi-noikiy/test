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
 * @package     Magestore_Affiliateplusprogram
 * @copyright   Copyright (c) 2012 Magestore (http://www.magestore.com/)
 * @license     http://www.magestore.com/license-agreement.html
 */

namespace Magestore\Affiliateplusprogram\Controller\Program;
class Detail extends \Magestore\Affiliateplusprogram\Controller\AbstractAction
{
    /**
     * Display detail information of a program
     * @return \Magento\Framework\App\ResponseInterface|\Magento\Framework\View\Result\Page
     */
    public function execute(){
        if (!$this->_helper->isPluginEnabled()) {
            return $this->_redirect('affiliateplus/index/index');
        }
        if ($this->_accountHelper->accountNotLogin()){
            return $this->_redirect('affiliateplus/account/login');
        }
        if ($this->_accountHelper->isNotAvailableAccount()){
            return $this->_redirect('affiliateplus/index/index');
        }
        $program = $this->getModel('Magestore\Affiliateplusprogram\Model\Program')
            ->setStoreId($this->_storeManager->getStore()->getId())
            ->load($this->getRequest()->getParam('id'));
        if ($program && $program->getId() && $program->getStatus()) {
            $resultPage = $this->_pageFactory->create();
            $resultPage->getConfig()->getTitle()->set(__('Program "%1"', $program->getName()));
            return $resultPage;
        } else {
            $this->messageManager->addError('Program not found!');
            return $this->_redirect('*/*/index');
        }
    }
}