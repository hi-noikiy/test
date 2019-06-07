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

class MageWorkshop_DetailedReview_Adminhtml_Mageworkshop_Detailedreview_ComplaintController extends Mage_Adminhtml_Controller_Action
{

    const TABLE_ID = 'entity_id';

    public function indexAction()
    {
        $this->_title($this->__('Catalog'))
            ->_title($this->__('Reviews and Ratings'))
            ->_title($this->__('Customer Comments'));

        $this->_title($this->__('List of Complaint'));

        if ($this->getRequest()->getParam('ajax')) {
            $this->_forward('complaintGrid');
            return;
        }

        $this->loadLayout();
        $this->_setActiveMenu('catalog/review');

        $this->_addContent($this->getLayout()->createBlock('detailedreview/adminhtml_complaint_main'));

        $this->renderLayout();
    }

    public function editAction()
    {
        $this->_title($this->__('Catalog'))
            ->_title($this->__('Reviews and Ratings'))
            ->_title($this->__('Customer Comment'));

        $this->_title($this->__('Edit Complaint'));

        $this->loadLayout();
        $this->_setActiveMenu('catalog/review');

        $this->_addContent($this->getLayout()->createBlock('detailedreview/adminhtml_complaint_edit'));

        $this->renderLayout();
    }

    public function newAction()
    {
        $this->_forward('edit');
    }

    public function saveAction()
    {
        // check if data sent
        if ($data = $this->getRequest()->getPost()) {

            $helper = Mage::helper('detailedreview');

            //init model and set data
            $model = Mage::getModel('detailedreview/complaintType');

            if ($id = $this->getRequest()->getParam('entity_id')) {
                $model->load($id, 'entity_id');
            }

            $model->addData($data);

            // try to save it
            try {
                // save the data
                $model->save();

                $session = Mage::getSingleton('adminhtml/session');
                // display success message

                $session->addSuccess($helper->__('The complaint has been saved.'));
                // clear previously saved data from session
                $session->setFormData(false);

                // check if 'Save and Continue'
                if ($this->getRequest()->getParam('back')) {
                    return $this->_redirect('*/*/edit', array('page_id' => $model->getEntityId(), '_current'=>true));
                }
                // go to grid
                return $this->_redirect('*/*/');

            } catch (Mage_Core_Exception $e) {
                $this->_getSession()->addError($e->getMessage());
            }
            catch (Exception $e) {
                $this->_getSession()->addException($e,
                    $helper->__('An error occurred while saving this complaint.'));
            }

            $this->_getSession()->setFormData($data);
            return $this->getResponse()->setRedirect($this->getUrl($this->getRequest()->getParam('ret') == 'pending' ? '*/*/pending' : '*/*/'));
        }
        $this->_redirect('*/*/');
    }

    public function deleteAction()
    {
        if ($entityId = (int) $this->getRequest()->getParam('entity_id')) {
            try {
                Mage::getModel('detailedreview/complaintType')->load($entityId)->delete();
                Mage::getSingleton('adminhtml/session')->addSuccess(
                    Mage::helper('detailedreview')->__('The %s has been deleted.', $this->_entityName)
                );
            } catch (Exception $e) {
                Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
            }
        }

        $this->_redirect('*/*/' . $this->getRequest()->getParam('ret', 'index'));
    }

    public function massDeleteAction()
    {
        $entityIds = $this->getRequest()->getParam('complaint_data');
        if (!is_array($entityIds)) {
            Mage::getSingleton('adminhtml/session')->addError(Mage::helper('adminhtml')->__('Please select %s(s).', $this->_className));
        } else {
            try {
                foreach ($entityIds as $entityId) {
                    $model = Mage::getModel("detailedreview/complaintType")->load($entityId);
                    $model->delete();
                }
                Mage::getSingleton('adminhtml/session')->addSuccess(
                    Mage::helper('adminhtml')->__('Total of %d record(s) have been deleted.', count($entityIds))
                );
            } catch (Exception $e) {
                Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
            }
        }

        $this->_redirect('*/*/' . $this->getRequest()->getParam('ret', 'index'));
    }

    public function massUpdateStatusAction()
    {
        $entityIds = $this->getRequest()->getParam('complaint_data');
        if (!is_array($entityIds)) {
            Mage::getSingleton('adminhtml/session')->addError(Mage::helper('adminhtml')->__('Please select %s(s).', $this->_className));
        } else {
            $session = Mage::getSingleton('adminhtml/session');
            /* @var Mage_Adminhtml_Model_Session $session */
            try {
                $status = (int) $this->getRequest()->getParam('update_status');
                foreach ($entityIds as $entityId) {
                    $model = Mage::getModel('detailedreview/complaintType')->load($entityId);
                    $model->setStatusId($status)
                        ->save();
                }
                $session->addSuccess(
                    Mage::helper('adminhtml')->__('Total of %d record(s) have been updated.', count($entityIds))
                );
            }
            catch (Mage_Core_Exception $e) {
                $session->addException($e, 'An error has been occurred');
            }
            catch (Exception $e) {
                $session->addError(Mage::helper('adminhtml')->__('An error occurred while updating the selected %s(s).', $this->_className));
            }
        }

        $this->_redirect('*/*/' . $this->getRequest()->getParam('ret', 'index'));
    }

    protected function _isAllowed()
    {
        return Mage::getSingleton('admin/session')->isAllowed('catalog/reviews_ratings/reviews/complaint');
    }
}
