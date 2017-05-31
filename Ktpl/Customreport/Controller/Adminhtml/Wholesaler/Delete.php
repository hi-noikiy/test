<?php
/**
 *
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Ktpl\Customreport\Controller\Adminhtml\Wholesaler;

class Delete extends \Magento\Backend\App\Action
{
    
    public function execute()
    {
        // check if we know what should be deleted
        $id = $this->getRequest()->getParam('id');
        /** @var \Magento\Backend\Model\View\Result\Redirect $resultRedirect */
        $resultRedirect = $this->resultRedirectFactory->create();
        if ($id) {
            $title = "";
            try {
                // init model and delete
                $model = $this->_objectManager->create('Ktpl\Customreport\Model\Wholesaler');
                $model->load($id);
                $title = $model->getName();
                $model->delete();
                // display success message
                $this->messageManager->addSuccess(__('The Wholesaler has been deleted.'));
                // go to grid
               
                return $resultRedirect->setPath('*/*/');
            } catch (\Exception $e) {
                
                // display error message
                $this->messageManager->addError($e->getMessage());
                // go back to edit form
                return $resultRedirect->setPath('*/*/edit', ['id' => $id]);
            }
        }
        // display error message
        $this->messageManager->addError(__('We can\'t find a Wholesaler to delete.'));
        // go to grid
        return $resultRedirect->setPath('*/*/');
    }
}
