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
namespace Magestore\Affiliateplus\Controller\Adminhtml\Banner;

use Magento\Framework\Controller\ResultFactory;

/**
 * Action MassStatus
 */
class MassDelete extends \Magestore\Affiliateplus\Controller\Adminhtml\Affiliateplus
{
    /**
     * Execute action
     */
    public function execute()
    {
        $bannerIds = $this->getRequest()->getParam('banner');
        $storeId = $this->getRequest()->getParam('store');

        if (!is_array($bannerIds) || empty($bannerIds)) {
            $this->messageManager->addError(__('Please select record(s).'));
        } else {
            /** @var \Magestore\Affiliateplus\Model\ResourceModel\Banner\Collection $collection */
            $collection = $this->_objectManager->create('Magestore\Affiliateplus\Model\ResourceModel\Banner\Collection');
            $collection->addFieldToFilter('banner_id', ['in' => $bannerIds]);
            try {
                foreach ($collection as $item) {
                    $item->setStoreViewId($storeId)
                        ->delete();
                }
                $this->messageManager->addSuccess(
                    __('A total of %1 record(s) have been deleted.', count($bannerIds))
                );
            } catch (\Exception $e) {
                $this->messageManager->addError($e->getMessage());
            }
        }
        $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);

        return $resultRedirect->setPath('*/*/');
    }
}
