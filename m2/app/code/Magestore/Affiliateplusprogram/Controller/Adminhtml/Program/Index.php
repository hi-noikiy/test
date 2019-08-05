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
namespace Magestore\Affiliateplusprogram\Controller\Adminhtml\Program;

/**
 * Class Index
 * @package Magestore\Affiliateplusprogram\Controller\Adminhtml\Program
 */
class Index extends \Magestore\Affiliateplusprogram\Controller\Adminhtml\AbstractAction
{
    /**
     * Index action
     *
     * @return \Magento\Backend\Model\View\Result\Page
     */
    public function execute()
    {
        /** @var \Magento\Backend\Model\View\Result\Page $resultPage */
        $resultPage = $this->_resultPageFactory->create();
        $resultPage->setActiveMenu('Magestore_Affiliateplusprogram::program');
        $resultPage->addBreadcrumb(__('Program'), __('Manage Program'));
        $resultPage->addBreadcrumb(__('Program'), __('Manage Program'));
        $resultPage->getConfig()->getTitle()->prepend(__('Manage Program'));

        return $resultPage;
    }

    /**
     * @return bool
     */
    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('Magestore_Affiliateplusprogram::program');
    }
}