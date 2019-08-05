<?php

namespace Cminds\Salesrep\Controller\Checkout;

use Magento\Framework\App\Action\Context;

class Selectsalesrepcheckout extends \Magento\Framework\App\Action\Action
{
    protected $checkoutSession;
    protected $resultJsonFactory;

    public function __construct(
        Context $context,
        \Magento\Checkout\Model\Session $checkoutSession,
        \Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory
    ) {
        parent::__construct($context);
        $this->checkoutSession = $checkoutSession;
        $this->resultJsonFactory = $resultJsonFactory;
    }

    public function execute()
    {
        $data = $this->getRequest()->getParams();

        if (isset($data['selectedSalesrep'])) {
            $this->checkoutSession->setSelectedSalesrepId($data['selectedSalesrep']);
        }

        $result = $this->resultJsonFactory->create();

        $result->setData(['success' => true]);

        return $result;
    }
}
