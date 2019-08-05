<?php


namespace Ktpl\Paymentcharge\Block\Sales;

use Magento\Framework\View\Element\Template;
use Magento\Framework\DataObject;
use Magento\Sales\Api\Data\InvoiceInterface;
use Magento\Sales\Api\Data\OrderInterface;
use Magento\Sales\Api\Data\CreditmemoInterface;

class Paymentcharge extends Template
{
    /**
     * @var \Ktpl\Wholesaler\Helper\Data
     */
    protected $_dataHelper;

    /**
     * @var Order
     */
    protected $_order;

    /**
     * @var \Magento\Framework\DataObject
     */
    protected $_source;

    /**
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
         \Ktpl\Paymentcharge\Helper\Data $dataHelper,
        array $data = []
    )
    {
        $this->_dataHelper = $dataHelper;
        parent::__construct($context, $data);
    }
    
    public function displayFullSummary()
    {
        return true;
    }

    public function initTotals()
    {
        $parent = $this->getParentBlock();
        $source = $parent->getSource();

        $payment = $this->getPayment($source);
        if ($payment && ($payment->getMethod() == 'classyllama_llamacoin')) {
            $fee = new DataObject(
                [
                    'code' => 'payment_charge',
                    'strong' => false,
                    'value' => $source->getPaymentCharge(),
                    'label' => $this->_dataHelper->getchargeLabel(),
                ]
            );

            $parent->addTotalBefore($fee, 'grand_total');
        }

        return $this;
    }

    protected function getPayment($source)
    {
        if ($source instanceof InvoiceInterface) {
            return $source->getOrder()->getPayment();
        }

        if ($source instanceof OrderInterface) {
            return $source->getPayment();
        }

        if ($source instanceof CreditMemoInterface) {
            return $source->getOrder()->getPayment();
        }

        return null;
    }
}
