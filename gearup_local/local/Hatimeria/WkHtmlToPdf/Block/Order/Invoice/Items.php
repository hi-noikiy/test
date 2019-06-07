<?php
/**
 * Class Hatimeria_WkHtmlToPdf_Block_Order_Invoice_Items
 */
class Hatimeria_WkHtmlToPdf_Block_Order_Invoice_Items extends Mage_Sales_Block_Order_Invoice_Items
{
    protected $order;
    protected $invoice;
    protected $store;
    protected $customer;

    /**
     * Return url path to printing the invoice.
     *
     * @param $invoice
     * @return string
     */
    public function getPrintInvoiceUrl($invoice)
    {
        parent::getPrintInvoicesUrl();

        return Mage::getUrl('hwkhtmltopdf/invoice/printInvoice', array('invoice_id' => $invoice->getId()));
    }


    /**
     * Return model of Customer by Id from Order
     *
     * @return Mage_Core_Model_Abstract
     */
    public function getCustomer()
    {
        if(is_null($this->customer)){
            $this->customer = Mage::getModel('customer/customer')->load($this->getOrder()->getCustomerId());
        }

        return $this->customer;
    }

    /**
     * Return Invoice model by id from request
     *
     * @return Mage_Core_Model_Abstract
     * @throws Exception
     */
    public function getInvoice()
    {
        $invoiceId = (int)$this->getRequest()->getParam('invoice_id');
        $invoice = Mage::getModel('sales/order_invoice')->load($invoiceId);
        $this->invoice = $invoice;

        return $invoice;
    }

    /**
     * Return Order model by id from request
     *
     * @return Mage_Core_Model_Abstract|Mage_Sales_Model_Order
     */
    public function getOrder()
    {
        $orderId = (int)$this->getRequest()->getParam('order_id');
        return Mage::getModel('sales/order')->load($orderId);
    }

    /**
     * Return Store Name
     *
     * @return mixed
     */
    public function getStoreNameFromConfig()
    {
        return Mage::getStoreConfig('general/store_information/name');
    }

    /**
     * Return Store Address
     *
     * @return mixed
     */
    public function getStoreAddressFromConfig()
    {
        return Mage::getStoreConfig('general/store_information/address');
    }

    /**
     * Return Store
     *
     * @return Mage_Core_Model_Store
     */
    public function getStore()
    {
        if (is_null($this->store)){
            $this->store = Mage::app()->getStore();
        }

        return $this->store;
    }

    /**
     * Return Customer address
     *
     * @return mixed
     */
    public function getCustomerAddress()
    {
        if(is_null($this->customer)){
            $this->customer = $this->getCustomer();
        }

        return $this->customer->getAddressess();
    }
} 