<?php
/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magento.com so we can send you a copy immediately.
 *
 *
 * @category    Gearup
 * @package     Gearup_Printinvoice
 * @copyright  Copyright (c) 2006-2015 X.commerce, Inc. (http://www.magento.com)
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */


/**
 * Rewrite sales orders invoice controller
 *
 * @author      Magento Core Team <core@magentocommerce.com>
 */
require_once Mage::getModuleDir('controllers', 'Mage_Adminhtml').DS.'Sales'.DS.'Order'.DS.'InvoiceController.php';
class Gearup_Printinvoice_Adminhtml_Sales_Order_InvoiceController extends Mage_Adminhtml_Sales_Order_InvoiceController
{
    /**
     * Create pdf for current invoice
     */
    public function printAction()
    {
        $this->_initInvoice();
        if ($invoiceId = $this->getRequest()->getParam('invoice_id')) {
            if ($invoice = Mage::getModel('sales/order_invoice')->load($invoiceId)) {
                $pdf = Mage::getModel('sales/order_pdf_invoice')->getPdf(array($invoice));
                $order = $invoice->getOrder();
                $this->_prepareDownloadResponse('invoice'.$order->getIncrementId().
                    '.pdf', $pdf->render(), 'application/pdf');
            }
        }
        else {
            $this->_forward('noRoute');
        }
    }
}