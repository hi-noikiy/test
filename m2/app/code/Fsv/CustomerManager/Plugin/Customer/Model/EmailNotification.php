<?php
/**
 * Fsv_CustomerManager
 *
 * @category    Fsv
 * @package     Fsv_CustomerManager
 * @author      Sergey Fedosimov <sfedosimov@gmail.com>
 */

namespace Fsv\CustomerManager\Plugin\Customer\Model;

use Closure;
use Magento\Customer\Api\Data\CustomerInterface;
use Magento\Customer\Model\EmailNotificationInterface;
use Fsv\CustomerManager\Helper\Data as CustomerManagerHelper;

/**
 * Fsv\CustomerManager\Plugin\Customer\Model\EmailNotification
 *
 * @category    Fsv
 * @package     Fsv_CustomerManager
 */
class EmailNotification
{
    /**
     * CustomerManagerHelper
     *
     * @var CustomerManagerHelper
     */
    protected $customerManagerHelper;

    /**
     * EmailNotification constructor.
     *
     * @param CustomerManagerHelper $customerManagerHelper
     */
    public function __construct(CustomerManagerHelper $customerManagerHelper)
    {
        $this->customerManagerHelper = $customerManagerHelper;
    }

    /**
     * Send notification to customer when email and/or password changed
     *
     * @param EmailNotificationInterface $emailNotification
     * @param Closure $proceed
     * @param CustomerInterface $savedCustomer
     * @param string $origCustomerEmail
     * @param bool $isPasswordChanged
     */
    public function aroundCredentialsChanged(
        EmailNotificationInterface $emailNotification,
        Closure $proceed,
        CustomerInterface $savedCustomer,
        $origCustomerEmail,
        $isPasswordChanged = false
    ) {
        if (!$this->customerManagerHelper->isChangePassword()) {
            $proceed($savedCustomer, $origCustomerEmail, $isPasswordChanged);
        }
    }

    /**
     * Send email with new account related information
     *
     * @param EmailNotificationInterface $emailNotification
     * @param Closure $proceed
     * @param CustomerInterface $customer
     * @param string $type
     * @param string $backUrl
     * @param int $storeId
     * @param mixed $sendemailStoreId
     */
    public function aroundNewAccount(
        EmailNotificationInterface $emailNotification,
        Closure $proceed,
        CustomerInterface $customer,
        $type = EmailNotificationInterface::NEW_ACCOUNT_EMAIL_REGISTERED,
        $backUrl = '',
        $storeId = 0,
        $sendemailStoreId = null
    ) {
        if (!$this->customerManagerHelper->isChangePassword()) {
            $proceed($customer, $type, $backUrl, $storeId, $sendemailStoreId);
        }
    }
}