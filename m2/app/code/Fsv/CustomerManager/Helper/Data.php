<?php
/**
 * Fsv_CustomerManager
 *
 * @category    Fsv
 * @package     Fsv_CustomerManager
 * @author      Sergey Fedosimov <sfedosimov@gmail.com>
 */

namespace Fsv\CustomerManager\Helper;

use Magento\Customer\Api\Data\CustomerInterface;
use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\HTTP\PhpEnvironment\Request;
use Magento\Framework\App\Helper\Context;
use Magento\Framework\Registry;
use Magento\Customer\Model\Customer;

/**
 * Fsv\CustomerManager\Helper\Data
 *
 * @category    Fsv
 * @package     Fsv_CustomerManager
 */
class Data extends AbstractHelper
{
    /**
     * CHANGE_PASSWORD_FLAG
     *
     * @var string
     */
    const CHANGE_PASSWORD_FLAG = 'changePassword';

    /**
     * Request
     *
     * @var Request
     */
    protected $request;

    /**
     * Registry
     *
     * @var Registry
     */
    protected $registry;

    /**
     * Customer
     *
     * @var Customer
     */
    protected $customer;

    /**
     * Data constructor.
     *
     * @param Context $context
     * @param Request $request
     * @param Registry $registry
     * @param Customer $customer
     */
    public function __construct(Context $context, Request $request, Registry $registry, Customer $customer)
    {
        $this->request = $request;
        $this->registry = $registry;
        $this->customer = $customer;

        parent::__construct($context);
    }

    /**
     * Returns generated secret
     *
     * @param CustomerInterface $customer
     * @return string
     */
    public function generateSecret(CustomerInterface $customer)
    {
        $this->customer->load($customer->getId());

        return hash('sha256', $this->customer->getPasswordHash() . $customer->getEmail());
    }

    /**
     * Is is process of change password?
     *
     * @return bool
     */
    public function isChangePassword()
    {
        return $this->registry->registry(self::CHANGE_PASSWORD_FLAG);
    }
}