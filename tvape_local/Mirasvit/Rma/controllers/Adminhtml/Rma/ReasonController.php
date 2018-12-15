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
 * @package   mirasvit/extension_rma
 * @version   2.4.7
 * @copyright Copyright (C) 2017 Mirasvit (https://mirasvit.com/)
 */



class Mirasvit_Rma_Adminhtml_Rma_ReasonController extends Mage_Adminhtml_Controller_Action
{
    /**
     * @return Mirasvit_Rma_Adminhtml_Rma_ReasonController
     */
    protected function _initAction()
    {
        $this->loadLayout()->_setActiveMenu('rma');

        return $this;
    }

    /**
     * @return void
     */
    public function indexAction()
    {
        $this->_title($this->__('Reason'));
        $this->_initAction();
        $this->_addContent($this->getLayout()
            ->createBlock('rma/adminhtml_reason'));
        $this->renderLayout();
    }

    /**
     * @return void
     */
    public function addAction()
    {
        $this->_title($this->__('New Reason'));

        $this->_initReason();

        $this->_initAction();
        $this->_addBreadcrumb(Mage::helper('adminhtml')->__('Reason  Manager'),
                Mage::helper('adminhtml')->__('Reason Manager'), $this->getUrl('*/*/'));
        $this->_addBreadcrumb(Mage::helper('adminhtml')->__('Add Reason '),
            Mage::helper('adminhtml')->__('Add Reason'));

        $this->getLayout()
            ->getBlock('head')
            ->setCanLoadExtJs(true);
        $this->_addContent($this->getLayout()->createBlock('rma/adminhtml_reason_edit'));
        $this->renderLayout();
    }

    /**
     * @return void
     */
    public function editAction()
    {
        $reason = $this->_initReason();

        if ($reason->getId()) {
            $this->_title($this->__("Edit Reason '%s'", $reason->getName()));
            $this->_initAction();
            $this->_addBreadcrumb(Mage::helper('adminhtml')->__('Reason'),
                    Mage::helper('adminhtml')->__('Reason'), $this->getUrl('*/*/'));
            $this->_addBreadcrumb(Mage::helper('adminhtml')->__('Edit Reason '),
                    Mage::helper('adminhtml')->__('Edit Reason '));

            $this->getLayout()
                ->getBlock('head')
                ->setCanLoadExtJs(true);
            $this->_addContent($this->getLayout()->createBlock('rma/adminhtml_reason_edit'));

            $this->renderLayout();
        } else {
            Mage::getSingleton('adminhtml/session')->addError(
                Mage::helper('adminhtml')->__('The Reason does not exist.'));
            $this->_redirect('*/*/');
        }
    }

    /**
     * @return void
     */
    public function saveAction()
    {
        if ($data = $this->getRequest()->getPost()) {
            $reason = $this->_initReason();
            $reason->addData($data);
            //format date to standart
            // $format = Mage::app()->getLocale()->getDateFormat(Mage_Core_Model_Locale::FORMAT_TYPE_SHORT);
            // Mage::helper('mstcore/date')->formatDateForSave($reason, 'active_from', $format);
            // Mage::helper('mstcore/date')->formatDateForSave($reason, 'active_to', $format);

            try {
                $reason->save();

                Mage::getSingleton('adminhtml/session')->addSuccess(
                    Mage::helper('adminhtml')->__('Reason was successfully saved'));
                Mage::getSingleton('adminhtml/session')->setFormData(false);

                if ($this->getRequest()->getParam('back')) {
                    $this->_redirect('*/*/edit', array('id' => $reason->getId(), 'store' => $reason->getStoreId()));

                    return;
                }
                $this->_redirect('*/*/');

                return;
            } catch (Mage_Core_Exception $e) {
                Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
                Mage::getSingleton('adminhtml/session')->setFormData($data);
                if ($id = $this->getRequest()->getParam('id')) {
                    $this->_redirect('*/*/edit', array('id' => $id));
                } else {
                    $this->_redirect('*/*/add');
                }

                return;
            }
        }
        Mage::getSingleton('adminhtml/session')->addError(
            Mage::helper('adminhtml')->__('Unable to find Reason to save'));
        $this->_redirect('*/*/');
    }

    /**
     * @return void
     */
    public function deleteAction()
    {
        if ($this->getRequest()->getParam('id') > 0) {
            try {
                $reason = Mage::getModel('rma/reason');

                $reason->setId($this->getRequest()
                    ->getParam('id'))
                    ->delete();

                Mage::getSingleton('adminhtml/session')->addSuccess(
                        Mage::helper('adminhtml')->__('Reason was successfully deleted'));
                $this->_redirect('*/*/');
            } catch (Mage_Core_Exception $e) {
                Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
                $this->_redirect('*/*/edit', array('id' => $this->getRequest()
                    ->getParam('id'), ));
            }
        }
        $this->_redirect('*/*/');
    }

    /**
     * @return void
     */
    public function massChangeAction()
    {
        $ids = $this->getRequest()->getParam('reason_id');
        if (!is_array($ids)) {
            Mage::getSingleton('adminhtml/session')->addError(
                Mage::helper('adminhtml')->__('Please select Reason(s)'));
        } else {
            try {
                $isActive = $this->getRequest()->getParam('is_active');
                foreach ($ids as $id) {
                    /** @var Mirasvit_Rma_Model_Reason $reason */
                    $reason = Mage::getModel('rma/reason')->load($id);
                    $reason->setIsActive($isActive);
                    $reason->save();
                }
                Mage::getSingleton('adminhtml/session')->addSuccess(
                    Mage::helper('adminhtml')->__(
                        'Total of %d record(s) were successfully updated', count($ids)
                    )
                );
            } catch (Exception $e) {
                Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
            }
        }
        $this->_redirect('*/*/index');
    }

    /**
     * @return void
     */
    public function massDeleteAction()
    {
        $ids = $this->getRequest()->getParam('reason_id');
        if (!is_array($ids)) {
            Mage::getSingleton('adminhtml/session')->addError(
                Mage::helper('adminhtml')->__('Please select Reason(s)'));
        } else {
            try {
                foreach ($ids as $id) {
                    /** @var Mirasvit_Rma_Model_Reason $reason */
                    $reason = Mage::getModel('rma/reason')
                        ->setIsMassDelete(true)
                        ->load($id);
                    $reason->delete();
                }
                Mage::getSingleton('adminhtml/session')->addSuccess(
                    Mage::helper('adminhtml')->__(
                        'Total of %d record(s) were successfully deleted', count($ids)
                    )
                );
            } catch (Mage_Core_Exception $e) {
                Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
            }
        }
        $this->_redirect('*/*/index');
    }

    /**
     * @return Mirasvit_Rma_Model_Reason
     */
    public function _initReason()
    {
        $reason = Mage::getModel('rma/reason');
        if ($this->getRequest()->getParam('id')) {
            $reason->load($this->getRequest()->getParam('id'));
            if ($storeId = (int) $this->getRequest()->getParam('store')) {
                $reason->setStoreId($storeId);
            }
        }

        Mage::register('current_reason', $reason);

        return $reason;
    }

    /**
     * @return bool
     */
    protected function _isAllowed()
    {
        return Mage::getSingleton('admin/session')->isAllowed('rma/dictionary/reason');
    }

    /************************/
}
