<?php

namespace Ktpl\Guestabandoned\Controller\Adminhtml\Guestabandoned;

class MassStatus extends \Magento\Backend\App\Action {

    protected function _isAllowed() {
        return true; //$this->_authorization->isAllowed('Ktpl_Guestabandoned::Guestabandoned');
    }

    public function execute() {
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $quoteIds = $this->getRequest()->getParam('entity_id');
        if (!is_array($quoteIds)) {
            $this->messageManager->addError(__('Please select quote(s)'));
        } else {
            try {
                $resource = $objectManager->get('Magento\Framework\App\ResourceConnection');
                $connection = $resource->getConnection();
                $tableName = $resource->getTableName('quote');

                foreach ($quoteIds as $quoteId) {
                    $connection->update($tableName, ['status' => $this->getRequest()->getParam('status')], ['entity_id = ?' => $quoteId]);
                }

                $this->messageManager->addSuccess(
                        __('Total of %1 record(s) were successfully updated', count($quoteIds))
                );
            } catch (\Exception $e) {
                $this->messageManager->addError($e->getMessage());
            }
        }
        $this->_redirect('*/*/index');
    }

}
