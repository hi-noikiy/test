<?php
/**
 * Fsv_CustomerManager
 *
 * @category    Fsv
 * @package     Fsv_CustomerManager
 * @author      Sergey Fedosimov <sfedosimov@gmail.com>
 */
namespace Fsv\CustomerManager\Observer;

use Exception;
use Magento\Customer\Model\CustomerFactory;
use Magento\Customer\Model\Customer;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Message\ManagerInterface;
use Psr\Log\LoggerInterface;
use Fsv\CustomerManager\Helper\Data as CustomerManagerHelper;

/**
 * Fsv\CustomerManager\Observer\ChangePasswordObserver
 *
 * @category    Fsv
 * @package     Fsv_CustomerManager
 */
class ChangePasswordObserver implements ObserverInterface
{

    /**
     * CustomerFactory
     *
     * @var CustomerFactory
     */
    protected $customerFactory;

    /**
     * ManagerInterface
     *
     * @var ManagerInterface
     */
    protected $messageManager;

    /**
     * LoggerInterface
     *
     * @var LoggerInterface
     */
    protected $logger;

    /**
     * CustomerManagerHelper
     *
     * @var CustomerManagerHelper
     */
    protected $customerManagerHelper;

    /**
     * ChangePasswordObserver constructor.
     *
     * @param CustomerFactory $customerFactory
     * @param ManagerInterface $messageManager
     * @param LoggerInterface $logger
     * @param CustomerManagerHelper $customerManagerHelper
     */
    public function __construct(
        CustomerFactory $customerFactory,
        ManagerInterface $messageManager,
        LoggerInterface $logger,
        CustomerManagerHelper $customerManagerHelper
    ) {
        $this->customerFactory = $customerFactory;
        $this->messageManager = $messageManager;
        $this->logger = $logger;
        $this->customerManagerHelper = $customerManagerHelper;
    }

    /**
     * Change password for customer
     *
     * @param Observer $observer
     * @return void
     */
    public function execute(Observer $observer)
    {
        if (!$this->customerManagerHelper->isChangePassword()) {
            return;
        }

        $postData = $observer->getRequest()->getPostValue('customer');
        $customerData = $observer->getCustomer();

        /**
         * @var $customer Customer
         */
        $customer = $this->customerFactory->create();

        $customer->load($customerData->getId());
        $customer->changePassword($postData['password']);

        try {
            $customer->save();
        } catch (Exception $e) {
            $this->logger->error('Change password error: ' . $e->getMessage());
            $this->messageManager->addErrorMessage(__('Password is not changed! See logs.'));
        }
    }

}
