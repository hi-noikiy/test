<?php
/**
 * MageWorkshop
 * Copyright (C) 2016 MageWorkshop <info@mage-review.com>
 *
 * @category   MageWorkshop
 * @package    MageWorkshop_DRReminder
 * @copyright  Copyright (c) 2016 MageWorkshop Co. (https://mage-review.com/)
 * @license    http://opensource.org/licenses/gpl-3.0.html GNU General Public License, version 3 (GPL-3.0)
 * @author     MageWorkshop <info@mage-review.com>
 */

class MageWorkshop_DRReminder_Adminhtml_Mageworkshop_Drreminder_IndexController extends Mage_Adminhtml_Controller_Action
{

    protected function _initAction()
    {
        return $this->loadLayout()->_setActiveMenu('reminder');
    }

    public function indexAction()
    {
        $this->_initAction();
        $this->renderLayout();
    }

    public function editAction()
    {
        $reminder = Mage::getModel('drreminder/reminder')->load($this->getRequest()->getParam('id'));
        if ($reminder->getId()) {
            Mage::register('drreminder_reminder', $reminder);
            $this->_initAction();
            $this->_addContent(
                $this->getLayout()
                    ->createBlock('drreminder/adminhtml_reminder_edit'))
                ->_addLeft(
                    $this->getLayout()->createBlock('drreminder/adminhtml_reminder_edit_tabs')
                );
            $this->renderLayout();
        } else {
            $this->_getSession()->addError($this->__('This reminder does not exist.'));
            return $this->_redirect('*/*/');
        }
    }

    public function saveAction()
    {
        if ($data = $this->getRequest()->getPost()) {
            $reminder = Mage::getModel('drreminder/reminder')->load($this->getRequest()->getParam('id'));
            if($reminder->getStatus() != MageWorkshop_DRReminder_Model_Source_Reminder_Status::REMINDER_STATUS_SENT) {
                $reminder
                    ->setCustomerName($data['customer_name'])
                    ->setEmail($data['email'])
                    ->setFormKey($data['form_key']);
                Mage::dispatchEvent('drreminder_adminhtml_reminder_before_save', array(
                    'reminder' => $reminder,
                    'request' => $data
                ));
                try {
                    $reminder->save();
                    $this->_getSession()->addSuccess($this->__('Item was successfully saved.'));
                } catch (Exception $e) {
                    Mage::logException($e);
                    $this->_getSession()->addError($e->getMessage());
                }
            } else {
                $this->_getSession()->addError($this->__('This reminder has already been sent.'));
            }
        } else {
            $this->_getSession()->addError($this->__('Cannot find data for save.'));
        }
        $this->_redirect('*/*/');

    }

    public function viewAction()
    {
        $reminder = Mage::getModel('drreminder/reminder')->load($this->getRequest()->getParam('id'));
        $this->_title($this->__('View Reminder '));

        if (!$reminder->getId()) {
            $this->_getSession()->addError($this->__('This reminder no longer exists.'));
            $this->_redirect('*/*/');
        } else {
            Mage::register('drreminder_reminder', $reminder);
            $this->_initAction();
            $this->_title(sprintf('#%s', $reminder->getId()));
            $this->renderLayout();
        }

    }
    public function holdAction()
    {
        $reminder = Mage::getModel('drreminder/reminder')->load($this->getRequest()->getParam('id'));

        if (!$reminder ->getId()) {
            $this->_getSession()->addError($this->__('This reminder no longer exists.'));
        } else {
            try {
                $status = MageWorkshop_DRReminder_Model_Source_Reminder_Status::REMINDER_STATUS_ON_HOLD;
                if ($reminder->getStatus() == MageWorkshop_DRReminder_Model_Source_Reminder_Status::REMINDER_STATUS_SENT) {
                    $this->_getSession()->addError($this->__('This reminder has already been sent.'));
                } else if ($reminder->getStatus() == $status){
                    $this->_getSession()->addError($this->__('This reminder is already on hold.'));
                } else {
                    $reminder
                        ->setStatus($status)
                        ->save();
                    $this->_getSession()->addSuccess(
                        $this->__('Item #%s status was changed to On Hold.', $reminder->getId())
                    );
                }
            } catch (Exception $e) {
                Mage::logException($e);
                $this->_getSession()->addError($e->getMessage());
            }
        }
        $this->_redirect('*/*/');

    }

    public function unholdAction()
    {
        $reminder = Mage::getModel('drreminder/reminder')->load($this->getRequest()->getParam('id'));
        if (!$reminder ->getId()) {
            $this->_getSession()->addError($this->__('This reminder no longer exists.'));
        } else {
            try {
                $status = MageWorkshop_DRReminder_Model_Source_Reminder_Status::REMINDER_STATUS_PENDING;
                if ($reminder->getStatus() == $status) {
                    $this->_getSession()->addError($this->__('This reminder is already pending.'));
                    $this->_redirect('*/*/');
                } else if ($reminder->getStatus() == MageWorkshop_DRReminder_Model_Source_Reminder_Status::REMINDER_STATUS_SENT) {
                    $this->_getSession()->addError($this->__('This reminder has already been sent.'));
                } else {
                    $reminder
                        ->setStatus($status)
                        ->save();
                    $this->_getSession()->addSuccess(
                        $this->__('Item #%s status was changed to Pending', $reminder->getId())
                    );
                }
            } catch (Exception $e) {
                Mage::logException($e);
                $this->_getSession()->addError($e->getMessage());
            }
        }
        $this->_redirect('*/*/');

    }

    public function deleteAction()
    {
        if($reminderId = (int) $this->getRequest()->getParam('id')) {
            try {
                Mage::getModel('drreminder/reminder')->setId($reminderId)->delete();
                $this->_getSession()->addSuccess($this->__('Item #%s was deleted.', $reminderId));
            } catch (Exception $e) {
                Mage::logException($e);
                $this->_getSession()->addError($e->getMessage());
            }
        }
        $this->_redirect('*/*/');
    }

    public function sendAction()
    {
        $reminder = Mage::getModel('drreminder/reminder')->load($this->getRequest()->getParam('id'));
        if (!$reminder->getId()) {
            $this->_getSession()->addError($this->__('This reminder no longer exists.'));
        } else {
            if (Mage::getStoreConfig('drreminder/settings/remind_enable')) {
                $productCollection = Mage::helper('drreminder')->getProductsByOrders($reminder->getOrderId(), false);
                if ($productCollection->getSize()) {
                    $reminder->sendReminderEmail();
                    $this->_getSession()->addSuccess(
                        $this->__('Reminder #%s was successfully sent.', $reminder ->getId())
                    );
                } else {
                    $this->_getSession()->addError($this->__('Cannot send reminder. Products from related order were deleted.'));
                }
            } else {
                $this->_getSession()->addError($this->__('Reminder sending is not allowed. Please, change Detailed Review Reminder extension configurations.'));
            }
        }
        $this->_redirect('*/*/');
    }

    public function massDeleteAction()
    {
        $reminderIds = $this->getRequest()->getParam('reminder_ids');
        if(!is_array($reminderIds)) {
            $this->_getSession()->addError($this->__('Please select item(s).'));
        } else {
            if (!empty($reminderIds)){
                try {
                    foreach ($reminderIds as $reminderId) {
                        Mage::getModel('drreminder/reminder')
                            ->setId($reminderId)
                            ->delete();
                    }
                    $this->_getSession()->addSuccess(
                        $this->__('Total of %d record(s) were successfully deleted.', count($reminderIds))
                    );
                } catch (Exception $e) {
                    Mage::logException($e);
                    $this->_getSession()->addError($e->getMessage());
                }
            }
        }
        $this->_redirect('*/*/');
    }

    public function massStatusAction()
    {
        $reminderIds = (array) $this->getRequest()->getParam('reminder_ids');
        $status = (int) $this->getRequest()->getParam('status');
        try {
            if (
                !Mage::getStoreConfig('drreminder/settings/remind_enable')
            ) {
                throw new MageWorkshop_DRReminder_Model_ReminderException('Reminder sending is not allowed. Please, change Detailed Review Reminder extension configurations.');
            }

            /** @var MageWorkshop_DRReminder_Model_Mysql4_Reminder_collection $reminderCollection */
            $reminderCollection = Mage::getModel('drreminder/reminder')->getCollection();

            $reminderCollection->addFieldToFilter('id', array('in' => $reminderIds));

            $counter = 0;

            foreach ($reminderCollection as $reminder) {
                $reminder->setStatus($status);
                $counter++;
            }
            $reminderCollection->save();

            if ($counter) {
                $this->_getSession()->addSuccess($this->__('Total of %d record(s) were successfully updated.', $counter));
            }
        } catch (MageWorkshop_DRReminder_Model_ReminderException $e) {
            $this->_getSession()->addError($e->getMessage());
        } catch (Exception $e) {
            Mage::logException($e);
            $this->_getSession()->addError($e->getMessage());
        }
        $this->_redirect('*/*/');
    }

    public function massCreateAction()
    {
        if (Mage::getStoreConfig('drreminder/settings/remind_enable')) {
            $reminders = Mage::getModel('drreminder/reminder')->getCollection();
            $existingOrders = array();
            $allowedStatuses = explode(',', Mage::getStoreConfig('drreminder/settings/remind_choice_status'));
            foreach ($reminders as $reminder) {
                $existingOrders[] = $reminder->getOrderId();
            }
            $orders = Mage::getModel('sales/order')->getCollection();
            if (count($existingOrders)) {
                $orders->addFieldToFilter('entity_id', array('nin' => $existingOrders));
            }
            $orders->addFieldToFilter('status', array('in' => $allowedStatuses));
            $resource = Mage::getSingleton('core/resource');
            $orders->getSelect()->join(
                array('c' => $resource->getTableName('customer_entity')),
                'main_table.customer_email = c.email',
                array('c.email')
            );
            if ($orders->getSize()) {
                Mage::getSingleton('core/resource_iterator')->walk($orders->getSelect(), array(array($this, 'ordersCallback')));
                $response['message'] = $this->__('Reminders were successfully created.');
                $response['success'] = true;
            } else {
                $response['message'] = $this->__('There are no orders without reminder.');
            }
        } else {
            $response['message'] = $this->__('Reminder sending is not allowed. Please, change Detailed Review Reminder extension configurations.');
        }
        $this->getResponse()->setBody(Mage::helper('core')->jsonEncode($response));
    }

    // callback method
    public function ordersCallback($args)
    {
        $order = Mage::getModel('sales/order');
        $order->setData($args['row']);
        Mage::helper('drreminder')->createReviewReminder($order);
    }

    public function gridAction()
    {
        $this->loadLayout();
        $this->getResponse()->setBody(
            $this->getLayout()->createBlock('drreminder/adminhtml_reminder_grid')->toHtml()
        );
    }

    protected function _isAllowed()
    {
        return Mage::getSingleton('admin/session')->isAllowed('catalog/reviews_ratings/reviews/reminder');
    }
}
