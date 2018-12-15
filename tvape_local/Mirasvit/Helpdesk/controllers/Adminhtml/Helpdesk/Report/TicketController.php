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
 * @package   mirasvit/extension_helpdesk
 * @version   1.5.4
 * @copyright Copyright (C) 2017 Mirasvit (https://mirasvit.com/)
 */



class Mirasvit_Helpdesk_Adminhtml_Helpdesk_Report_TicketController extends Mage_Adminhtml_Controller_Action
{
    public function _initAction()
    {
        $this->loadLayout()->_setActiveMenu('helpdesk');

        return $this;
    }

    public function indexAction()
    {
        $this->_title($this->__('Tickets Report'));

        $refreshRecentPermission = Mage::getSingleton('admin/session')->isAllowed('helpdesk/report/recent');
        $refreshLifetimePermission = Mage::getSingleton('admin/session')->isAllowed('helpdesk/report/lifetime');

        if($refreshRecentPermission || $refreshLifetimePermission) {
            $this->_showLastExecutionTime();
        }

        $this->_initAction();

        $this->renderLayout();

        if ($this->getRequest()->getParam('export')) {
            $grid = $this->getLayout()->getBlock('container')->getGrid();

            if ($this->getRequest()->getParam('export') == 'csv') {
                $fileName = 'report.csv';
                $this->_prepareDownloadResponse($fileName, $grid->getCsvFile());
            } elseif ($this->getRequest()->getParam('export') == 'xml') {
                $fileName = 'report.xml';
                $this->_prepareDownloadResponse($fileName, $grid->getExcelFile());
            }
        }
    }

    protected function _showLastExecutionTime()
    {
        $flag = Mage::getModel('reports/flag')
            ->setReportFlagCode(Mirasvit_Helpdesk_Model_Resource_Report_Ticket::FLAG_CODE)->loadSelf();

        $updatedAt = $flag->hasData()
            ?  Mage::getSingleton('core/date')->date('M, d Y h:i A', strtotime($flag->getLastUpdate()))
            : 'undefined';

        $refreshLifetimeLink = $this->getUrl('*/*/refreshLifetime');
        $refreshRecentLink = $this->getUrl('*/*/refreshRecent');

        $noticeText = Mage::helper('adminhtml')->__('Last updated: %s. ', $updatedAt);
        if(Mage::getSingleton('admin/session')->isAllowed('helpdesk/report/recent')) {
            $noticeText = $noticeText . Mage::helper('adminhtml')->__('To refresh recent statistics, click <a href="%s">here</a>', $refreshRecentLink);
            if(Mage::getSingleton('admin/session')->isAllowed('helpdesk/report/lifetime')) {
                $noticeText = $noticeText . Mage::helper('adminhtml')->__(' (refresh lifetime statistic <a href="%s">here</a>)', $refreshLifetimeLink);
            }
        } elseif (Mage::getSingleton('admin/session')->isAllowed('helpdesk/report/lifetime')) {
            $noticeText = $noticeText . Mage::helper('adminhtml')->__('To refresh lifetime statistic, click <a href="%s">here</a>', $refreshLifetimeLink);
        }

        Mage::getSingleton('adminhtml/session')->addNotice($noticeText);

        return $this;
    }

    public function refreshRecentAction()
    {
        $flag = Mage::getModel('reports/flag')
            ->setReportFlagCode(Mirasvit_Helpdesk_Model_Resource_Report_Ticket::FLAG_CODE)->loadSelf();

        $from = $flag->hasData()
            ? Mage::app()->getLocale()->storeDate(
                0, new Zend_Date($flag->getLastUpdate(), Varien_Date::DATETIME_INTERNAL_FORMAT), true
            )
            : null;

        try {
            Mage::getResourceModel('helpdesk/report_ticket')->aggregate($from);

            Mage::getSingleton('adminhtml/session')->addSuccess(
                Mage::helper('adminhtml')->__('Recent statistics was successfully updated'));
        } catch (Mage_Core_Exception $e) {
            Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
        }

        $this->_redirectReferer('*/*/*');

        return $this;
    }

    public function refreshLifetimeAction()
    {
        try {
            Mage::getResourceModel('helpdesk/report_ticket')->aggregate();

            Mage::getSingleton('adminhtml/session')->addSuccess(
                Mage::helper('adminhtml')->__('Lifetime statistics was successfully updated'));
        } catch (Mage_Core_Exception $e) {
            Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
        }

        $this->_redirectReferer('*/*/*');

        return $this;
    }

    protected function _isAllowed()
    {
        return Mage::getSingleton('admin/session')->isAllowed('helpdesk/report');
    }
}
