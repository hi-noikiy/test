<?php
/**
 * Created by PhpStorm.
 * User: tungquach
 * Date: 31/03/2017
 * Time: 19:40
 */

namespace Beeketing\MagentoCommon\Manager;


use Beeketing\MagentoCommon\Data\Api;
use Beeketing\MagentoCommon\Libraries\SettingHelper;

class CustomerManager
{
    /**
     * @var \Magento\Customer\Model\ResourceModel\Customer\CollectionFactory
     */
    private $customerFactory;

    /**
     * @var \Magento\Newsletter\Model\Subscriber
     */
    private $subscriber;

    /**
     * @var \Magento\Directory\Model\CountryFactory
     */
    private $countryFactory;

    /**
     * @var \Magento\Sales\Model\ResourceModel\Order\CollectionFactory
     */
    private $orderCollectionFactory;

    public function __construct() {
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $this->customerFactory = $objectManager->get('\Magento\Customer\Model\ResourceModel\Customer\CollectionFactory');
        $this->subscriber = $objectManager->get('\Magento\Newsletter\Model\Subscriber');
        $this->countryFactory = $objectManager->get('\Magento\Directory\Model\CountryFactory');
        $this->orderCollectionFactory = $objectManager->get('\Magento\Sales\Model\ResourceModel\Order\CollectionFactory');
    }

    /**
     * Get customers count
     *
     * @return int
     */
    public function getCustomersCount()
    {
        $storeId = SettingHelper::getInstance()->getCurrentStoreId();
        $result = $this->customerFactory->create();
        $result->addFieldToFilter('store_id', $storeId);

        return $result->getSize();
    }

    /**
     * Get customer by id
     *
     * @param $id
     * @return array
     */
    public function getCustomerById($id)
    {
        $result = $this->customerFactory->create();
        $result->addAttributeToSelect('*');
        $result->addFieldToFilter('entity_id', $id);

        if ($result->getSize()) {
            return $this->formatCustomer($result->getFirstItem());
        }

        return [];
    }

    /**
     * Get customers
     *
     * @param int $page
     * @param int $limit
     * @return array
     */
    public function getCustomers($page = Api::PAGE, $limit = Api::ITEM_PER_PAGE)
    {
        $storeId = SettingHelper::getInstance()->getCurrentStoreId();
        $result = $this->customerFactory->create();
        $result->addAttributeToSelect('*');
        $result->addFieldToFilter('store_id', $storeId);
        $result->addOrder('entity_id');

        // Page
        if ($page) {
            $result->setCurPage($page);
        }

        // Limit
        if ($limit) {
            $result->setPageSize($limit);
        }

        $results = array();
        if ($result->getSize()) {
            foreach ($result as $item) {
                $results[] = $this->formatCustomer($item);
            }
        }

        return $results;
    }

    /**
     * Format customer
     *
     * @param $customer
     * @return array
     */
    public function formatCustomer(\Magento\Customer\Model\Customer $customer)
    {
        // Get accept marketing
        $acceptMarketing = false;
        $subscriber = $this->subscriber->loadByCustomerId($customer->getId());
        if ($subscriber->isSubscribed()) {
            $acceptMarketing = true;
        }

        // Get customer address
        $address = $customer->getDefaultBillingAddress();
        if (!$address) {
            foreach ($customer->getAddresses() as $addr) {
                $address = $addr;
                break;
            }
        }
        $street1 = $street2 = $city = $company = $region = $postcode = $country = $countryCode = '';
        if ($address) {
            $street1 = $address->getStreetLine(1);
            $street2 = $address->getStreetLine(2);
            $city = $address->getCity();
            $company = $address->getCompany();
            $region = $address->getRegion();
            $postcode = $address->getPostcode();
            $countryCode = $address->getCountryId();
            $country = $this->countryFactory->create()->loadByCode($countryCode);
            $country = $country->getName();
        }

        // Get customer order
        $orders = $this->orderCollectionFactory->create();
        $orders->addFieldToSelect('subtotal');
        $orders->addFieldToFilter('customer_id', $customer->getId());

        // Get total spent
        $totalSpent = 0;
        foreach ($orders as $order) {
            $totalSpent += (float) $order->getSubtotal();
        }
        $totalSpent = number_format($totalSpent, 2);

        return array(
            'id'  => (int) $customer->getId(),
            'email' => $customer->getEmail(),
            'first_name' => $customer->getFirstname(),
            'last_name' => $customer->getLastname(),
            'accepts_marketing' => $acceptMarketing,
            'verified_email' => $customer->isConfirmationRequired() ? $customer->getConfirmation() : true,
            'signed_up_at' => $customer->getCreatedAt(),
            'address1' => $street1,
            'address2' => $street2,
            'city' => $city,
            'company' => $company,
            'province' => $region,
            'zip' => $postcode,
            'country' => $country,
            'country_code' => $countryCode,
            'orders_count' => $orders->getSize(),
            'total_spent' => $totalSpent,
        );
    }
}