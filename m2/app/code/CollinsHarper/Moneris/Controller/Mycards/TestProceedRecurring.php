<?php

namespace CollinsHarper\Moneris\Controller\Mycards;

use CollinsHarper\Moneris\Controller\AbstractMycards;
use CollinsHarper\Moneris\Cron\ProceedRecurringPayment;
use CollinsHarper\Moneris\Helper\Data as chHelper;
use CollinsHarper\Moneris\Helper\Data;
use CollinsHarper\Moneris\Model\VaultSaveService;
use Magento\Framework\App\Action\Context;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\Registry;
use Magento\Framework\Session\SessionManagerInterface;
use Magento\Framework\View\Result\PageFactory;
use Magento\Sales\Api\OrderRepositoryInterface;
use Magento\Sales\Model\Order;

class TestProceedRecurring extends \Magento\Framework\App\Action\Action
{
    /**
     * TestProceedRecurring constructor.
     * @param Context $context
     * @param ProceedRecurringPayment $cron
     */
    public function __construct(Context $context, ProceedRecurringPayment $cron)
    {
        parent::__construct($context);
        $this->cron = $cron;
    }

    public function execute()
    {
        /** @var Data $helper */
        $helper = ObjectManager::getInstance()->get(Data::class);

        if ($helper->isCCTestMode()) {
            $this->cron->execute();
        } else {
            return false;
        }
        return;
    }
}
