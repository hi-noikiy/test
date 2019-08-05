<?php
/**
 * Copyright © 2015 Collins Harper. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace CollinsHarper\CanadaPost\Controller\Adminhtml\Manifest;



class Edit extends \CollinsHarper\CanadaPost\Controller\Adminhtml\AbstractController
{

    /**
     * @return bool
     */
    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('Magento_Sales::manifests');
    }

    /**
     * manifet grid
     *
     * @return \Magento\Backend\Model\View\Result\Page
     */
    public function execute()
    {
        $manifestLoaded = $this->loadManifest();
        if($manifestLoaded !== true) {
            return $manifestLoaded;
        }

        $this->chLogger->info(__METHOD__ . __LINE__);
        /** @var \Magento\Backend\Model\View\Result\Page $resultPage */
        $resultPage = $this->resultPageFactory->create();
        $resultPage->setActiveMenu('CollinsHarper_CanadaPost::cpcanadapost_manifest')
            ->addBreadcrumb(__('Sales'), __('Sales'))
            ->addBreadcrumb(__('Manifests'), __('Manage Canada Post Manifest Shipments'));
        $resultPage->getConfig()->getTitle()->prepend(__('Manage Canada Post Manifest Shipments'));

        return $resultPage;
    }


}