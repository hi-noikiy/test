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
class Delete extends \Magestore\Affiliateplus\Controller\Adminhtml\Affiliateplus
{
    /**
     * Execute action
     */
    public function execute()
    {
        $bannerId = $this->getRequest()->getParam('banner_id');
            try {
                /** @var \{{model_name}} $model */
                $model = $this->_objectManager->create('Magestore\Affiliateplus\Model\Banner')->setId($bannerId);
                $title = $model->getTitle();
                $model->delete();
                $this->messageManager->addSuccess(
                    __('The banner %1 has been deleted!', $title)
                );
            } catch (\Exception $e) {
                $this->messageManager->addError($e->getMessage());
            }

        $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);

        return $resultRedirect->setPath('*/*/');
    }
}
