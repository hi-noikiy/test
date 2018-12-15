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



class Mirasvit_Rma_Adminhtml_Rma_RmaController extends Mage_Adminhtml_Controller_Action
{
    /**
     * Initializes action.
     *
     * @return Mirasvit_Rma_Adminhtml_Rma_RmaController
     */
    protected function _initAction()
    {
        $this->loadLayout()->_setActiveMenu('rma');

        return $this;
    }

    /**
     * Redirects action to certain page.
     *
     * @param string $route
     * @param array  $params
     *
     * @return bool | Mage_Adminhtml_Controller_Action
     */
    public function _redirect($route, $params = array())
    {
        if (Mage::getBaseUrl() != Mage::getModel('core/url')->getBaseUrl(array('_store' => 0))) {
            $params['_store'] = 0;
            $this->_redirectUrl(Mage::helper('adminhtml')->getUrl($route, $params));

            return false;
        }
        parent::_redirect($route, $params);
    }

    /**
     * Default index page action.
     * @return void
     */
    public function indexAction()
    {
        Mage::register('is_archive', (bool) $this->getRequest()->getParam('is_archive'));
        $this->_title($this->__('RMA'));
        $this->_initAction();
        $this->_addContent($this->getLayout()->createBlock('rma/adminhtml_rma'));
        $this->renderLayout();
    }

    /**
     * Renders backend RMA adding dialog.
     * @return void
     */
    public function addAction()
    {
        $this->_title($this->__('New RMA'));

        /** @var Mirasvit_Rma_Model_Rma $rma */
        $rma = $this->_initRma();
        $ordersId = $this->getRequest()->getParam('orders_id');
        $customerId = $this->getRequest()->getParam('customer_id');
        if ($ordersId == -1 && !Mage::getSingleton('rma/config')->getGeneralIsOfflineOrdersAllowed()) {
            Mage::getSingleton('adminhtml/session')->addError(
                Mage::helper('adminhtml')->__('Offline RMA are not allowed.')
            );
            $this->_redirect('*/*/');

            return;
        }

        $data = Mage::getSingleton('adminhtml/session')->getFormData(true);
        if (!empty($data)) {
            $rma->setData($data);
        }

        if (($ordersId && $ordersId != -1) || ($ordersId == -1 && $customerId !== null)) {
            $ordersId = explode(',', $ordersId);
            foreach ($ordersId as $key => $orderId) {
                if ($orderId == -1) {
                    unset($ordersId[$key]);
                } elseif (!Mage::helper('rma')->isReturnAllowed($orderId)) {
                    $order = Mage::getModel('sales/order')->load($orderId);
                    Mage::getSingleton('adminhtml/session')->addError(
                        Mage::helper('adminhtml')->__('According to RMA rules, we should not allow RMA for order %s',
                            Mage::helper('rma')->getOrderLabel($order))
                    );
                }
            }
            if ($ordersId) {
                $rma->initFromOrder($ordersId);
            }
            $mode = Mirasvit_Rma_Model_Config::BACKEND_RMA_MODE_EDIT_RMA;
        } elseif ($customerId === null) {
            $mode = Mirasvit_Rma_Model_Config::BACKEND_RMA_MODE_SELECT_CUSTOMER;
        } else {
            $mode = Mirasvit_Rma_Model_Config::BACKEND_RMA_MODE_CREATE_RMA;
        }

        $this->_initAction();

        $this->_addBreadcrumb(Mage::helper('adminhtml')->__('RMA  Manager'),
            Mage::helper('adminhtml')->__('RMA Manager'), $this->getUrl('*/*/'));
        $this->_addBreadcrumb(Mage::helper('adminhtml')->__('Add RMA '), Mage::helper('adminhtml')->__('Add RMA'));

        $this->getLayout()
            ->getBlock('head')
            ->setCanLoadExtJs(true);

        if ($mode == Mirasvit_Rma_Model_Config::BACKEND_RMA_MODE_CREATE_RMA) {
            $this->_addContent($this->getLayout()->getBlock('rma_adminhtml_rma_create'));
        } elseif ($mode == Mirasvit_Rma_Model_Config::BACKEND_RMA_MODE_SELECT_CUSTOMER) {
            $this->_addContent($this->getLayout()->createBlock('rma/adminhtml_rma_selectCustomer'));
        } else {
            $this->_addContent($this->getLayout()->createBlock('rma/adminhtml_rma_edit'));
        }

        $this->renderLayout();
    }

    /**
     * Renders backend RMA editing dialog. Also shown as second stage of backend RMA creation.
     * @return void
     */
    public function editAction()
    {
        $rma = $this->_initRma();
        Mage::register('is_archive', (bool) $rma->getIsArchived());

        if (!Mage::helper('rma/fedex')->isEnabled() &&
            Mage::getSingleton('rma/config')->getFedexFedexEnable($rma->getStore())) {
            Mage::getSingleton('adminhtml/session')->addError(Mage::helper('adminhtml')->__(
                    'FedEx is incorrectly set up. Check your key/password and account/meter credentials at '.
                    'Configuration -> Sales -> Shipping Methods -> FedEx.'));
        }

        if ($rma->getId()) {

            // Clear session quote, where order creation variables are stored.
            // This prevents filling order with customer data from previously dropped Exchange Order
            Mage::getSingleton('adminhtml/session_quote')->clear();

            $this->_title($this->__('RMA #%s', $rma->getIncrementId()));
            $this->_initAction();
            $this->_addBreadcrumb(Mage::helper('adminhtml')->__('RMA'),
                    Mage::helper('adminhtml')->__('RMA'), $this->getUrl('*/*/'));
            $this->_addBreadcrumb(Mage::helper('adminhtml')->__('Edit RMA '),
                    Mage::helper('adminhtml')->__('Edit RMA '));

            $this->getLayout()
                ->getBlock('head')
                ->setCanLoadExtJs(true);
            $this->_addContent($this->getLayout()->createBlock('rma/adminhtml_rma_edit'));

            $this->renderLayout();
        } else {
            Mage::getSingleton('adminhtml/session')->addError(Mage::helper('adminhtml')->__('The rma does not exist.'));
            $this->_redirect('*/*/');
        }
    }

    /**
     * Saves RMA.
     * @return void
     */
    public function saveAction()
    {
        if ($data = $this->getRequest()->getPost()) {
            $dataProcessor = Mage::helper('rma/rma_save_postDataProcessor');
            $dataProcessor->setData($data);
            try {
                $dataProcessor->validate($data);
                $rma = Mage::helper('rma/rma_save_user')->createOrUpdateRmaUser(
                    $dataProcessor,
                    Mage::getSingleton('admin/session')->getUser()
                );

                Mage::getSingleton('adminhtml/session')->addSuccess(
                    Mage::helper('adminhtml')->__('RMA was successfully saved')
                );
                Mage::getSingleton('adminhtml/session')->setFormData(false);

                if ($this->getRequest()->getParam('back')) {
                    $this->_redirect('*/*/edit', array('id' => $rma->getId()));
                    return;
                }
                $this->_redirect('*/*/');

                return;
            } catch (Mage_Core_Exception $e) {
                Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
                Mage::getSingleton('adminhtml/session')->setFormData($data);
                if ($this->getRequest()->getParam('id')) {
                    $this->_redirect('*/*/edit', array('id' => $this->getRequest()->getParam('id')));
                } else {
                    $this->_redirect('*/*/add', array('orders_id' => implode(',', array_keys($data['items']))));
                }

                return;
            }
        }
        Mage::getSingleton('adminhtml/session')->addError(Mage::helper('adminhtml')->__('Unable to find rma to save'));
        $this->_redirect('*/*/');
    }

    /**
     * Deletes RMA.
     * @return void
     */
    public function deleteAction()
    {
        if ($this->getRequest()->getParam('id') > 0) {
            try {
                $rma = Mage::getModel('rma/rma');

                $rma->setId($this->getRequest()
                    ->getParam('id'))
                    ->delete();

                Mage::getSingleton('adminhtml/session')->addSuccess(
                        Mage::helper('adminhtml')->__('RMA was successfully deleted'));
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
     * Governs mass selecting in RMA Grid.
     * @return void
     */
    public function massSelectOrdersAction()
    {
        $ids = $this->getRequest()->getParam('selected_orders');
        if (!is_array($ids)) {
            Mage::getSingleton('adminhtml/session')->addError(Mage::helper('adminhtml')->__('Please select order(s)'));
        } else {
            $this->_redirect('*/*/add', array('orders_id' => implode(',', $ids)));

            return;
        }
        // Proper redirect if mass action was conducted in Tab Mode -
        // see in Mirasvit_Rma_Block_Adminhtml_Rma_Grid::_prepareMassaction()
        if ($this->getRequest()->getParam('back_url')) {
            $backUrl = base64_decode(strtr($this->getRequest()->getParam('back_url'), '-_,', '+/='));
            $this->_redirectUrl($backUrl);

            return;
        }
        $this->_redirect('*/*/add');
    }

    /**
     * Governs mass delete in RMA Grid.
     * @return void
     */
    public function massDeleteAction()
    {
        $ids = $this->getRequest()->getParam('rma_id');
        if (!is_array($ids)) {
            Mage::getSingleton('adminhtml/session')->addError(Mage::helper('adminhtml')->__('Please select rma(s)'));
        } else {
            try {
                foreach ($ids as $id) {
                    /** @var Mirasvit_Rma_Model_Rma $rma */
                    $rma = Mage::getModel('rma/rma')
                        ->setIsMassDelete(true)
                        ->load($id);
                    $rma->delete();
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
        // Proper redirect if mass action was conducted in Tab Mode
        // see in Mirasvit_Rma_Block_Adminhtml_Rma_Grid::_prepareMassaction()
        if ($this->getRequest()->getParam('back_url')) {
            $backUrl = base64_decode(strtr($this->getRequest()->getParam('back_url'), '-_,', '+/='));
            $this->_redirectUrl($backUrl);

            return;
        }
        $this->_redirect('*/*/index');
    }

    /**
     * Governs mass archive in RMA Grid.
     * @return void
     */
    public function massArchiveAction()
    {
        $ids = $this->getRequest()->getParam('rma_id');
        if (!is_array($ids)) {
            Mage::getSingleton('adminhtml/session')->addError(Mage::helper('adminhtml')->__('Please select rma(s)'));
        } else {
            try {
                $saved = 0;
                foreach ($ids as $id) {
                    /* @var Mirasvit_Rma_Model_Rma $rma */
                    Mage::getModel('rma/rma')
                        ->load($id)
                        ->setIsArchived(1)
                        ->save()
                    ;
                    ++$saved;
                }
                Mage::getSingleton('adminhtml/session')->addSuccess(
                    Mage::helper('adminhtml')->__(
                        'Total of %d record(s) were successfully moved to archive', count($saved)
                    )
                );
            } catch (Mage_Core_Exception $e) {
                Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
            }
        }
        // Proper redirect if mass action was conducted in Tab Mode
        // see in Mirasvit_Rma_Block_Adminhtml_Rma_Grid::_prepareMassaction()
        if ($this->getRequest()->getParam('back_url')) {
            $backUrl = base64_decode(strtr($this->getRequest()->getParam('back_url'), '-_,', '+/='));
            $this->_redirectUrl($backUrl);

            return;
        }
        $this->_redirect('*/*/index');
    }

    /**
     * Governs mass restore from archive in RMA Grid.
     * @return void
     */
    public function massRestoreAction()
    {
        $ids = $this->getRequest()->getParam('rma_id');
        if (!is_array($ids)) {
            Mage::getSingleton('adminhtml/session')->addError(Mage::helper('adminhtml')->__('Please select rma(s)'));
        } else {
            try {
                /* @var Mirasvit_Rma_Model_Status $status */
                $statusId = Mage::getSingleton('rma/config')->getGeneralDefaultStatus();
                $saved = 0;
                foreach ($ids as $id) {
                    /* @var Mirasvit_Rma_Model_Rma $rma */
                    Mage::getModel('rma/rma')
                        ->load($id)
                        ->setStatusId($statusId)
                        ->setIsArchived(0)
                        ->save()
                    ;
                    ++$saved;
                }
                Mage::getSingleton('adminhtml/session')->addSuccess(
                    Mage::helper('adminhtml')->__(
                        'Total of %d record(s) were successfully moved to RMA list', count($saved)
                    )
                );
            } catch (Mage_Core_Exception $e) {
                Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
            }
        }
        // Proper redirect if mass action was conducted in Tab Mode
        // see in Mirasvit_Rma_Block_Adminhtml_Rma_Grid::_prepareMassaction()
        if ($this->getRequest()->getParam('back_url')) {
            $backUrl = base64_decode(strtr($this->getRequest()->getParam('back_url'), '-_,', '+/='));
            $this->_redirectUrl($backUrl);

            return;
        }
        $this->_redirect('*/*/index');
    }

    /**
     * Governs mass status change in RMA Grid.
     * @return void
     */
    public function massChangeAction()
    {
        $ids = $this->getRequest()->getParam('rma_id');
        if (!is_array($ids)) {
            Mage::getSingleton('adminhtml/session')->addError(Mage::helper('adminhtml')->__('Please select rma(s)'));
        } else {
            try {
                /* @var Mirasvit_Rma_Model_Status $status */
                $statusId = $this->getRequest()->getParam('status');
                $status = Mage::getModel('rma/status')->load($statusId);
                $saved = 0;
                foreach ($ids as $id) {
                    /* @var Mirasvit_Rma_Model_Rma $rma */
                    $rma = Mage::getModel('rma/rma')
                        ->load($id);

                    $rma->setStatusId($statusId)
                        ->setIsArchived(0)
                        ->save()
                    ;

                    // Fire change event to have rules properly work
                    Mage::dispatchEvent('mst_rma_changed', array('rma'=>$rma));
                    ++$saved;
                }

                Mage::getSingleton('adminhtml/session')->addSuccess(
                    Mage::helper('adminhtml')->__(
                        'Total of %d record(s) were successfully changed to ' . $status->getName(), $saved
                    )
                );
            } catch (Mage_Core_Exception $e) {
                Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
            }
        }
        // Proper redirect if mass action was conducted in Tab Mode
        // see in Mirasvit_Rma_Block_Adminhtml_Rma_Grid::_prepareMassaction()
        if ($this->getRequest()->getParam('back_url')) {
            $backUrl = base64_decode(strtr($this->getRequest()->getParam('back_url'), '-_,', '+/='));
            $this->_redirectUrl($backUrl);

            return;
        }
        $this->_redirect('*/*/index');
    }

    /**
     * Initializes RMA. If integration is turned on, binds RMA to a ticket.
     *
     * @return Mirasvit_Rma_Model_Rma
     */
    public function _initRma()
    {
        $rma = Mage::getModel('rma/rma');
        if ($this->getRequest()->getParam('id')) {
            $rma->load($this->getRequest()->getParam('id'));
        }
        if ($ticketId = (int) $this->getRequest()->getParam('ticket_id')) {
            $rma->setTicketId($ticketId);
        }

        Mage::register('current_rma', $rma);

        return $rma;
    }

    /**
     * Returns ACL permission for this controller's actions.
     *
     * @return bool
     */
    protected function _isAllowed()
    {
        return Mage::getSingleton('admin/session')->isAllowed('rma/rma');
    }

    /************************/

    /**
     * Converts ticket to the RMA (works only if RMA -> Settings -> Enable integration with Help Desk is Yes).
     * @return void
     */
    public function convertTicketAction()
    {
        $ticket = Mage::getModel('helpdesk/ticket')->load($this->getRequest()->getParam('id'));
        $this->_redirect('*/*/add', array('orders_id' => $ticket->getOrderId(), 'ticket_id' => $ticket->getId()));
    }

    /**
     * Export rma grid to CSV format.
     * @return void
     */
    public function exportCsvAction()
    {
        Mage::register('is_archive', (bool) $this->getRequest()->getParam('is_archive'));
        $fileName = 'rma.csv';
        $content = $this->getLayout()->createBlock('rma/adminhtml_rma_grid')
            ->getCsvFile();

        $this->_prepareDownloadResponse($fileName, $content);
    }

    /**
     * Export rma grid to XML format.
     * @return void
     */
    public function exportXmlAction()
    {
        Mage::register('is_archive', (bool) $this->getRequest()->getParam('is_archive'));
        $fileName = 'rma.xml';
        $content = $this->getLayout()->createBlock('rma/adminhtml_rma_grid')
            ->getExcelFile();

        $this->_prepareDownloadResponse($fileName, $content);
    }

    /**
     * Creates Replacement Order.
     * @return void
     */
    public function createReplacementAction()
    {
        $rma = Mage::getModel('rma/rma')->load((int) $this->getRequest()->getParam('rma_id'));
        try {
            Mage::helper('rma/order')->createReplacementOrder($rma);
            Mage::getSingleton('adminhtml/session')->addSuccess(
                Mage::helper('adminhtml')->__('Replacement Order is created')
            );
        } catch (Mage_Core_Exception $e) {
            Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
        }
        $this->_redirect('*/*/edit', array('id' => $rma->getId()));
    }

    //    public function creditmemoAction()
    //    {
    //        $rma = Mage::getModel('rma/rma')->load((int) $this->getRequest()->getParam('rma_id'));
    //        try {
    //            Mage::helper('rma/order')->createCreditMemo($rma);
    //            Mage::getSingleton('adminhtml/session')->addSuccess(
    //                Mage::helper('adminhtml')->__('Credit Memo is created')
    //            );
    //        } catch (Mage_Core_Exception $e) {
    //            Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
    //        }
    //        $this->_redirect('*/*/edit', array('id' => $rma->getId()));
    //    }

    /**
     * Places RMA to the Archive
     * @return void
     */
    public function archiveAction()
    {
        if ($this->getRequest()->getParam('id') > 0) {
            try {
                $rma = Mage::getModel('rma/rma')->load($this->getRequest()->getParam('id'));
                $rma
                    ->setIsArchived(1)
                    ->save();

                Mage::getSingleton('adminhtml/session')->addSuccess(
                    Mage::helper('adminhtml')->__('RMA was moved to archive')
                );
                $this->_redirect('*/*/');
            } catch (Mage_Core_Exception $e) {
                Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
                $this->_redirect('*/*/edit', array('id' => $this->getRequest()->getParam('id')));
            }
        }
        $this->_redirect('*/*/');
    }

    /**
     * Restores RMA from the Archive
     * @return void
     */
    public function restoreAction()
    {
        if ($this->getRequest()->getParam('id') > 0) {
            try {
                /* @var Mirasvit_Rma_Model_Status $status */
                $statusId = Mage::getSingleton('rma/config')->getGeneralDefaultStatus();
                $rma = Mage::getModel('rma/rma')->load($this->getRequest()->getParam('id'));
                $rma
                    ->setStatusId($statusId)
                    ->setIsArchived(0)
                    ->save();

                Mage::getSingleton('adminhtml/session')->addSuccess(
                    Mage::helper('adminhtml')->__('RMA was moved to the RMA List')
                );
                $this->_redirect('*/*/');
            } catch (Mage_Core_Exception $e) {
                Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
                $this->_redirect('*/*/edit', array('id' => $this->getRequest()->getParam('id')));
            }
        }
        $this->_redirect('*/*/');
    }

    /**
     * Marks RMA as Read (mass action).
     * @return void
     */
    public function markReadAction()
    {
        $rma = Mage::getModel('rma/rma')->load((int) $this->getRequest()->getParam('rma_id'));
        try {
            $isRead = (int) $this->getRequest()->getParam('is_read');
            $rma->setIsAdminRead($isRead)->save();
            if ($isRead) {
                $message = Mage::helper('adminhtml')->__('Marked as read');
            } else {
                $message = Mage::helper('adminhtml')->__('Marked as unread');
            }
            Mage::getSingleton('adminhtml/session')->addSuccess($message);
        } catch (Mage_Core_Exception $e) {
            Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
        }
        $this->_redirect('*/*/edit', array('id' => $rma->getId()));
    }

    /**
     * Controller function to allow users create FedEx Label from popup dialog.
     * @return void
     */
    public function createFedExLabelAction()
    {
        $rma = Mage::getModel('rma/rma')->load((int) $this->getRequest()->getParam('rma_id'));
        $data = json_decode($this->getRequest()->getParam('data'));
        $fedexParams = Mage::helper('rma/fedex')->jsonToArray($data);
        $result = Mage::helper('rma/fedex')->createFedexLabel($rma, $fedexParams);
        $this->getResponse()->setHeader('status', $result['status']);
        if ($result['status'] == 'success') {
            $this->_getSession()->addSuccess(Mage::helper('rma')->__('FedEx Label was successfully created!'));
            $this->getResponse()->setBody(Mage::helper('adminhtml')->getUrl('*/*/edit', array('id' => $rma->getId())));
        } else {
            $this->getResponse()->setBody(implode('<br>', $result['errata']));
        }
    }

    /**
     * Controller function to allow users to remove FedEx Label.
     * @return void
     */
    public function removeFedExLabelAction()
    {
        $rma = Mage::getModel('rma/rma')->load((int) $this->getRequest()->getParam('rma_id'));
        $collection = Mage::getModel('rma/fedex_label')->getCollection()
            ->addFieldToFilter('track_number', $this->getRequest()->getParam('track_number'));
        $fedexLabel = $collection->getLastItem();
        $status = 'success';
        if ($fedexLabel->getId()) {
            $fedexLabel->delete();
        } else {
            $status = 'fail';
        }

        if ($status == 'success') {
            $this->_getSession()->addSuccess(Mage::helper('rma')->__('FedEx Label was successfully removed!'));
            $this->getResponse()->setBody(Mage::helper('adminhtml')->getUrl('*/*/edit', array('id' => $rma->getId())));
        } else {
            $this->_getSession()->addError(Mage::helper('rma')->__('FedEx Label remove was failed!'));
            $this->getResponse()->setBody('failed');
        }
    }

    /**
     * Controller function to allow users direct download FedEx Label after generation.
     * @return void
     */
    public function downloadFedExLabelAction()
    {
        $label = Mage::getModel('rma/fedex_label')->load($this->getRequest()->getParam('label_id'));
        if ($label) {
            $this->getResponse()->clearHeaders();
            $this->getResponse()
                ->setHttpResponseCode(200)
                ->setHeader('Cache-Control', 'must-revalidate, post-check=0, pre-check=0', true)
                ->setHeader('Pragma', 'public', true)
                ->setHeader('Content-Disposition', 'attachment; filename=fedexlabel_'.$label->getTrackNumber().'.pdf')
                ->setHeader('Content-type', 'application/x-pdf');
            $this->getResponse()->sendHeaders();
            $this->getResponse()->clearBody();
            echo $label->getLabelBody();
        }
    }

    /**
     * Refunds item to Store Credit (SCR should be installed)
     * @return void
     * @throws Exception
     */
    public function refundToCreditAction()
    {
        if ($creditModuleInstalled = Mage::helper('mstcore')->isModuleInstalled('Mirasvit_Credit')) {
            $rma = Mage::getModel('rma/rma')->load((int) $this->getRequest()->getParam('rma_id'));
            $order = Mage::getModel('sales/order')->load((int) $this->getRequest()->getParam('order_id'));

            // Pick up proper customer
            $customer = Mage::getModel('customer/customer')->load($rma->getCustomerId());
            if (!$customer->getId()) {
                $customer = Mage::getModel("customer/customer");
                $customer->setWebsiteId($order->getStore()->getWebsiteId());
                $customer->loadByEmail($order->getCustomerEmail());
            }

            if ($customer->getId()) {
                $balance = Mage::getModel('credit/balance')->loadByCustomer($customer->getId());

                $total = 0;

                $transactions = Mage::getModel('credit/transaction')->getCollection()
                    ->addFieldToFilter('message', array('like' => '%#r|'.$rma->getIncrementId().'%'))
                    ->addFieldToFilter('action', Mirasvit_Credit_Model_Transaction::ACTION_REFUNDED);

                if (!count($transactions)) {
                    $creditResolution = Mage::helper('rma')->getResolutionByCode('credit');

                    // Pure store credit order, refund full amount
                    foreach (Mage::helper('rma')->getRmaItems($order, $rma) as $item) {
                        if ($item->getResolutionId() == $creditResolution->getId()) {
                            $itemRefund = $item->getOrderItemPrice() * $item->getQtyRequested();
                            $total += $itemRefund;

                            // Add proper refund information to order item
                            $orderItem = $item->getOrderItem();

                            $orderItem->setQtyRefunded($orderItem->getQtyRefunded() + $item->getQtyRequested())
                                ->setAmountRefunded($orderItem->getAmountRefunded() + $itemRefund)
                                ->setBaseAmountRefunded($orderItem->getBaseAmountRefunded() + $itemRefund)
                                ->save();

                            Mage::helper('rma/order')->updateStockQty($orderItem->getProduct(),
                                -$item->getQtyRequested(), $orderItem->getOrder()->getStore()->getId());
                        }
                    }

                    // When full refund of store credit order detected, return all paid amount
                    if ($order->getBaseGrandTotal() == 0 && $order->getCreditAmount() > 0
                        && $order->getBaseSubtotal() == $total) {
                        $total = $order->getCreditAmount();
                    }


                    $balance->addTransaction(
                        $total,
                        Mirasvit_Credit_Model_Transaction::ACTION_REFUNDED,
                        'Order #o|' . $order->getIncrementId() . ', RMA #r|' . $rma->getIncrementId()
                    );

                    // Add proper refund information to order
                    $order->setBaseCreditRefunded($order->getBaseCreditRefunded() + $total)
                        ->setCreditRefunded($order->getCreditRefunded() + $total)
                        ->setBaseCreditTotalRefunded($order->getBaseCreditTotalRefunded() + $total)
                        ->setCreditTotalRefunded($order->getCreditTotalRefunded() + $total)
                        ->save();

                    $this->_getSession()->addSuccess(Mage::helper('rma')->__(
                        'Item(s) were successfully refunded to Store Credit!'));
                } else {
                    $this->_getSession()->addError(Mage::helper('rma')->__(
                        'Store Credit Refund was already created for this RMA!'));
                }

            }
        }

        $this->_redirectUrl(Mage::helper('adminhtml')->getUrl('*/*/edit', array('id' => $rma->getId())));
    }
}
