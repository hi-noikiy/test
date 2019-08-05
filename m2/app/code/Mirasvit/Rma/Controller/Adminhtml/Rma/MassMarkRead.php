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
 * @package   mirasvit/module-rma
 * @version   2.0.18
 * @copyright Copyright (C) 2018 Mirasvit (https://mirasvit.com/)
 */



namespace Mirasvit\Rma\Controller\Adminhtml\Rma;

use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\Exception\LocalizedException;
use Mirasvit\Rma\Controller\Adminhtml\Rma;

class MassMarkRead extends Rma
{
    public function __construct(
        \Mirasvit\Rma\Model\RmaFactory $rmaFactory,
        \Magento\Backend\App\Action\Context $context
    ) {
        $this->rmaFactory = $rmaFactory;

        parent::__construct($context);
    }

    /**
     * {@inheritdoc}
     */
    public function execute()
    {
        /** @var \Magento\Framework\Controller\Result\Redirect $resultRedirect */
        $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);

        $ids = $this->getRequest()->getParam('selected');
        if (!is_array($ids)) {
            if ($this->getRequest()->getParam('excluded')) {
                $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
                $collection = $objectManager->create('Mirasvit\Rma\Model\ResourceModel\Rma\Collection')->load();
                $ids = $collection->getAllIds();
            } else {
                $this->messageManager->addError(__('Please select Rma(s)'));
                return $resultRedirect->setPath('*/*/index');
            }
        }
        try {
            $isRead = $this->getRequest()->getParam('is_active');
            foreach ($ids as $id) {
                $rma = $this->rmaFactory->create()->load($id);
                if (!$rma->getId()) {
                    return $resultRedirect->setPath('*/*/');
                }
                $rma->setIsAdminRead($isRead)->save();
            }
            $this->messageManager->addSuccess(
                sprintf(__('Total of %d record(s) were successfully updated'), count($ids))
            );

        } catch (LocalizedException $e) {
            $this->messageManager->addError($e->getMessage());
        }

        return $resultRedirect->setPath('*/*/index');
    }
}
