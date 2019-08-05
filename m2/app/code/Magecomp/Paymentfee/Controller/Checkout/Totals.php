<?php
namespace Magecomp\Paymentfee\Controller\Checkout;

use Magento\Framework\App\Action\Context;
use Magento\Checkout\Model\Session;
use Magento\Framework\Json\Helper\Data as Datahel;
use Magento\Framework\Controller\Result\JsonFactory;
use Magecomp\Paymentfee\Helper\Data;

class Totals extends \Magento\Framework\App\Action\Action
{
    /**
     * @var \Magento\Checkout\Model\Session
     */
    protected $_checkoutSession;

    /**
     * @var \Magento\Framework\Controller\Result\JsonFactory
     */
    protected $_resultJson;

    /**
     * @var \Magento\Framework\Json\Helper\Data
     */
    protected $_helper;
	protected $surhelper;

    public function __construct(
        Context $context,
        Session $checkoutSession,
        Datahel $helper,
        JsonFactory $resultJson,
		Data $surhelper
    )
    {
        parent::__construct($context);
        $this->_checkoutSession = $checkoutSession;
        $this->_helper = $helper;
        $this->_resultJson = $resultJson;
		$this->surhelper = $surhelper;
    }

    /**
     * Trigger to re-calculate the collect Totals
     *
     * @return bool
     */
    public function execute()
    {
        try 
		{
            //Trigger to re-calculate totals
            $payment = $this->_helper->jsonDecode($this->getRequest()->getContent());
            $this->_checkoutSession->getQuote()->getPayment()->setMethod($payment['payment']);
            $this->_checkoutSession->getQuote()->collectTotals()->save();
			
			$paytext = $this->_checkoutSession->getQuote()->getMcPaymentfeeDescription();
			if($paytext == '')
			{
				$paytext = $this->surhelper->getPaymentLabel();
			}
			
			$response = [
            	'errors' => false,
            	'message' => 'successful',
				'title' => $paytext,
        	];

        } catch (\Exception $e) {
            $response = [
                'errors' => true,
                'message' => $e->getMessage(),
				'title' => $this->_checkoutSession->getQuote()->getMcPaymentfeeDescription(),
            ];
        }

        /** @var \Magento\Framework\Controller\Result\Raw $resultRaw */
        $resultJson = $this->_resultJson->create();
        return $resultJson->setData($response);
    }
}