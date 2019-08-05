<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2019 Amasty (https://www.amasty.com)
 * @package Amasty_Storelocator
 */


namespace Amasty\Storelocator\Controller\Adminhtml\Schedule;

class Save extends \Amasty\Storelocator\Controller\Adminhtml\Schedule
{
    public function execute()
    {
        if ($this->getRequest()->getPostValue()) {
            try {
                $data = $this->getRequest()->getPostValue();
                $scheduleId = (int)$this->getRequest()->getParam('id');
                if ($scheduleId) {
                    $model = $this->scheduleModel->load($scheduleId);
                    if ($scheduleId != $model->getId()) {
                        throw new \Magento\Framework\Exception\LocalizedException(__('The wrong item is specified.'));
                    }
                }
                if (is_array($data['schedule'])) {
                    $this->scheduleModel->setSchedule($this->serializer->serialize($data['schedule']));
                }
                $this->scheduleModel->setName($this->getRequest()->getParam('name'));
                $this->scheduleModel->save();
                $this->messageManager->addSuccessMessage(__('You saved the schedule.'));
                if ($this->getRequest()->getParam('back')) {
                    $this->_redirect('*/*/edit', ['id' => $this->scheduleModel->getId()]);
                    return;
                }
                $this->_redirect('*/*/');
                return;
            } catch (\Magento\Framework\Exception\LocalizedException $e) {
                $this->messageManager->addErrorMessage($e->getMessage());
                $id = (int)$this->getRequest()->getParam('id');
                if (!empty($id)) {
                    $this->_redirect('*/*/edit', ['id' => $id]);
                } else {
                    $this->_redirect('*/*/new');
                }
                return;
            } catch (\Exception $e) {
                $errorMessage = $e->getMessage();
                $this->messageManager->addErrorMessage(
                    __($errorMessage)
                );
                $this->logger->critical($e);
                $this->sessionModel->setPageData($data);
                return;
            }
        }
        $this->_redirect('*/*/');
    }
}
