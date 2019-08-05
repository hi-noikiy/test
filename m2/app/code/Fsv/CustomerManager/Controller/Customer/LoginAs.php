<?php
/**
 * Fsv_CustomerManager
 *
 * @category    Fsv
 * @package     Fsv_CustomerManager
 * @author      Sergey Fedosimov <sfedosimov@gmail.com>
 */

namespace Fsv\CustomerManager\Controller\Customer;

use Exception;
use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Customer\Model\Session as CustomerSession;
use Magento\Customer\Model\ResourceModel\CustomerRepository;
use Magento\Framework\Controller\Result\Redirect;
use Magento\Framework\Exception\EmailNotConfirmedException;
use Magento\Framework\Exception\State\UserLockedException;
use Magento\Framework\Controller\ResultFactory;
use Fsv\CustomerManager\Helper\Data as CustomerManagerHelper;

/**
 * Fsv\CustomerManager\Controller\Customer\LoginAs
 *
 * @category    Fsv
 * @package     Fsv_CustomerManager
 */
class LoginAs extends Action
{
    /**
     * CustomerRepository
     *
     * @var CustomerRepository
     */
    protected $customerRepository;

    /**
     * CustomerSession
     *
     * @var CustomerSession
     */
    protected $customerSession;

    /**
     * CustomerManagerHelper
     *
     * @var CustomerManagerHelper
     */
    protected $customerManagerHelper;

    /**
     * LoginAs constructor.
     *
     * @param Context $context
     * @param CustomerRepository $customerRepository
     * @param CustomerSession $customerSession
     * @param CustomerManagerHelper $customerManagerHelper
     */
    public function __construct(
        Context $context,
        CustomerRepository $customerRepository,
        CustomerSession $customerSession,
        CustomerManagerHelper $customerManagerHelper

    ) {
        $this->customerRepository = $customerRepository;
        $this->customerSession = $customerSession;
        $this->customerManagerHelper = $customerManagerHelper;

        parent::__construct($context);
    }

    /**
     * Login customer as
     */
    public function execute()
    {
        /**
         * @var $result Redirect
         */
        $result = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);

        if (($customerId = $this->getRequest()->getParam('customer_id'))
            && ($secret = $this->getRequest()->getParam('secret'))
        ) {
            $customer = $this->customerRepository->getById($customerId);
            $trueSecret = $this->customerManagerHelper->generateSecret($customer);

            if ($secret !== $trueSecret) {
                $result->setPath('customer/account/logout');

                return $result;
            }

            try {
                $this->customerSession->logout();
                $this->customerSession->setCustomerDataAsLoggedIn($customer);
                $this->customerSession->regenerateId();

                $result->setPath('customer/account');

                return $result;
            } catch (EmailNotConfirmedException $e) {
                $this->messageManager->addErrorMessage(__('This account is not confirmed.'));
            } catch (UserLockedException $e) {
                $this->messageManager->addErrorMessage(__('The account is locked.'));
            } catch (Exception $e) {
                $this->messageManager->addErrorMessage(__('An unspecified error occurred.'));
            }
        }

        $result->setPath('/');

        return $result;
    }
}