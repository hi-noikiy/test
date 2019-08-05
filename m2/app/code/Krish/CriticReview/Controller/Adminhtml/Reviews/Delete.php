<?php

namespace Krish\CriticReview\Controller\Adminhtml\Reviews;

use Magento\Backend\App\Action;
use Magento\TestFramework\ErrorLog\Logger;

class Delete extends \Magento\Backend\App\Action
{

    protected $model;

    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Krish\CriticReview\Model\Review $model
    ){
        $this->model = $model;
        parent::__construct($context);
    }
    
    /**
     * {@inheritdoc}
     */
    /*protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('Krish_Review::atachment_delete');
    }*/

    /**
     * Delete action
     *
     * @return \Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {
        $id = $this->getRequest()->getParam('review_id');
        /** @var \Magento\Backend\Model\View\Result\Redirect $resultRedirect */
        $resultRedirect = $this->resultRedirectFactory->create();
        if ($id) {
            try {
                $model = $this->model;
                $model->load($id);
                $model->delete();
                $this->messageManager->addSuccess(__('The review has been deleted.'));
                return $resultRedirect->setPath('*/*/');
            } catch (\Exception $e) {
                $this->messageManager->addError($e->getMessage());
                return $resultRedirect->setPath('*/*/edit', ['review_id' => $id]);
            }
        }
        $this->messageManager->addError(__('We can\'t find a review to delete.'));
        return $resultRedirect->setPath('*/*/');
    }
}
