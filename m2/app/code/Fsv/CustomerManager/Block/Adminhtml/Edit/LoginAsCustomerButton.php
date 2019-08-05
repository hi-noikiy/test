<?php
/**
 * Fsv_CustomerManager
 *
 * @category    Fsv
 * @package     Fsv_CustomerManager
 * @author      Sergey Fedosimov <sfedosimov@gmail.com>
 */

namespace Fsv\CustomerManager\Block\Adminhtml\Edit;

use Magento\Backend\Block\Widget\Context;
use Magento\Customer\Model\ResourceModel\CustomerRepository;
use Magento\Framework\Registry;
use Magento\Framework\Url;
use Magento\Framework\View\Element\UiComponent\Control\ButtonProviderInterface;
use Magento\Customer\Block\Adminhtml\Edit\GenericButton;
use Fsv\CustomerManager\Helper\Data as CustomerManagerHelper;
use Magento\Store\Model\StoreManagerInterface;

/**
 * Fsv\CustomerManager\Block\Adminhtml\Edit\LoginAsCustomerButton
 *
 * @category    Fsv
 * @package     Fsv_CustomerManager
 */
class LoginAsCustomerButton extends GenericButton implements ButtonProviderInterface
{
    /**
     * Url
     *
     * @var Url
     */
    protected $url;

    /**
     * CustomerRepository
     *
     * @var CustomerRepository
     */
    protected $customerRepository;

    /**
     * CustomerManagerHelper
     *
     * @var CustomerManagerHelper
     */
    protected $customerManagerHelper;

    /**
     * StoreManagerInterface
     *
     * @var StoreManagerInterface
     */
    protected $storeManager;

    /**
     * LoginAsCustomerButton constructor.
     *
     * @param Context $context
     * @param Registry $registry
     * @param Url $url
     * @param CustomerRepository $customerRepository
     * @param CustomerManagerHelper $customerManagerHelper
     */
    public function __construct(
        Context $context,
        Registry $registry,
        Url $url,
        CustomerRepository $customerRepository,
        CustomerManagerHelper $customerManagerHelper
    ) {
        $this->url = $url;
        $this->customerRepository = $customerRepository;
        $this->customerManagerHelper = $customerManagerHelper;
        $this->storeManager = $context->getStoreManager();

        parent::__construct($context, $registry);
    }

    /**
     * Returns button data
     *
     * @return array
     */
    public function getButtonData()
    {
        $customerId = $this->getCustomerId();
        $data = [];
        if ($customerId) {
            $data = [
                'label'      => __('Login as customer'),
                'class'      => 'login-as-customer',
                'on_click'   => sprintf("window.open('%s', '_blank');", $this->getUrlAuthAsCustomer()),
                'sort_order' => 70,
            ];
        }
        return $data;
    }

    /**
     * Returns url for login as customer
     *
     * @return string
     */
    public function getUrlAuthAsCustomer()
    {
        $customer = $this->customerRepository->getById($this->getCustomerId());
        $secret = $this->customerManagerHelper->generateSecret($customer);

        $this->url->setScope($this->storeManager->getWebsite($customer->getWebsiteId())->getDefaultStore());

        $url = $this->url->getUrl('customer_manager/customer/loginAs',
            [
                '_nosid'      => true,
                'customer_id' => $customer->getId(),
                'secret'      => $secret,
            ]
        );

        return $url;
    }
}
