<?php
/**
 * Created by PhpStorm.
 * User: tungquach
 * Date: 01/04/2017
 * Time: 00:11
 */

namespace Beeketing\MagentoCommon\Manager;


use Beeketing\MagentoCommon\Data\Api;
use Beeketing\MagentoCommon\Libraries\Helper;
use Beeketing\MagentoCommon\Libraries\SettingHelper;

class OrderManager
{
    /**
     * @var \Magento\Sales\Model\ResourceModel\Order\CollectionFactory
     */
    private $orderCollectionFactory;

    /**
     * @var \Magento\Directory\Model\CountryFactory
     */
    private $countryFactory;

    /**
     * @var \Magento\Catalog\Api\ProductRepositoryInterface
     */
    private $productRepository;

    /**
     * OrderManager constructor.
     */
    public function __construct()
    {
        $this->customerManager = new CustomerManager();
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $this->orderCollectionFactory = $objectManager->get('\Magento\Sales\Model\ResourceModel\Order\CollectionFactory');
        $this->countryFactory = $objectManager->get('\Magento\Directory\Model\CountryFactory');
        $this->productRepository = $objectManager->get('\Magento\Catalog\Api\ProductRepositoryInterface');
    }

    /**
     * Get orders count
     *
     * @return int
     */
    public function getOrdersCount()
    {
        $storeId = SettingHelper::getInstance()->getCurrentStoreId();
        $result = $this->orderCollectionFactory->create();
        $result->addFieldToFilter('store_id', $storeId);

        return $result->getSize();
    }

    /**
     * Get order by id
     *
     * @param $id
     * @return array
     */
    public function getOrderById($id)
    {
        $result = $this->orderCollectionFactory->create();
        $result->addAttributeToSelect('*');
        $result->addFieldToFilter('entity_id', $id);

        if ($result->getSize()) {
            return $this->formatOrder($result->getFirstItem());
        }

        return [];
    }

    /**
     * Get orders
     *
     * @param int $page
     * @param int $limit
     * @return array
     */
    public function getOrders($page = Api::PAGE, $limit = Api::ITEM_PER_PAGE)
    {
        $storeId = SettingHelper::getInstance()->getCurrentStoreId();
        $result = $this->orderCollectionFactory->create();
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
                $results[] = $this->formatOrder($item);
            }
        }

        return $results;
    }

    /**
     * Format order
     *
     * @param $order
     * @return array
     */
    public function formatOrder(\Magento\Sales\Model\Order $order)
    {
        $orderData = array(
            'id' => (int)$order->getId(),
            'email' => $order->getCustomerEmail(),
            'financial_status' => $order->getStatus(),
            'fulfillment_status' => '',
            'line_items' => array(),
            'cart_token' => $order->getQuoteId(),
            'currency' => $order->getOrderCurrencyCode(),
            'name' => $order->getRealOrderId(),
            'total_tax' => Helper::formatPrice($order->getTaxAmount()),
            'total_discounts' => Helper::formatPrice($order->getDiscountAmount()),
            'total_price' => Helper::formatPrice($order->getGrandTotal()),
            'subtotal_price' => Helper::formatPrice($order->getSubtotal()),
            'total_line_items_price' => '',
            'processed_at' => $order->getCreatedAt(),
            'updated_at' => $order->getUpdatedAt(),
            'cancelled_at' => '',
            'note_attributes' => array(),
            'source_name' => '',
        );

        // Add contact info
        if ($order->getCustomerId()) {
            $contact = $this->customerManager->getCustomerById($order->getCustomerId());

            if ($contact) {
                $orderData['customer'] = $contact;
            }
        } else {
            $address = $order->getBillingAddress();
            $orderData['customer']['email'] = $order->getCustomerEmail();
            $orderData['customer']['first_name'] = $order->getCustomerFirstname();
            $orderData['customer']['last_name'] = $order->getCustomerLastname();
            $orderData['customer']['address1'] = $address->getStreetLine(1);
            $orderData['customer']['address2'] = $address->getStreetLine(2);
            $orderData['customer']['city'] = $address->getCity();
            $orderData['customer']['company'] = $address->getCompany();
            $orderData['customer']['province'] = $address->getRegion();
            $orderData['customer']['zip'] = $address->getPostcode();
            $countryCode = $address->getCountryId();
            $country = $this->countryFactory->create()->loadByCode($countryCode);
            $orderData['customer']['country'] = $country->getName();
            $orderData['customer']['country_code'] = $countryCode;
            $orderData['customer']['signed_up_at'] = $order->getCreatedAt();
            $orderData['customer']['accepts_marketing'] = '';
            $orderData['customer']['verified_email'] = '';
            $orderData['customer']['orders_count'] = '';
            $orderData['customer']['total_spent'] = '';
        }

        // Add line items
        /** @var \Magento\Sales\Model\Order\Item $item */
        $variantItems = array();
        foreach ($order->getAllItems() as $item) {
            if ($item->getParentItem()) {
                $variantItems[$item->getParentItem()->getQuoteItemId()] = $item;
            }
        }

        /** @var \Magento\Sales\Model\Order\Item $item */
        foreach ($order->getAllVisibleItems() as $item) {
            // Fix visible items webhook
            if ($item->getParentItem() && !$item->getId()) {
                continue;
            }

            $variant = isset($variantItems[$item->getQuoteItemId()]) ? $variantItems[$item->getQuoteItemId()] : $item;
            $productId = $item->getProductId();
            // If beeketing variant
            if (strpos($item->getSku(), '_BEEKETING-') !== false) {
                preg_match('/(_BEEKETING-)(\d+)-(\d+)$/', $item->getSku(), $skuMatches);
                if (isset($skuMatches[3]) && is_numeric($skuMatches[3])) {
                    $product = $this->productRepository->getById($skuMatches[3]);
                    $productId = $product->getId();
                }
            }

            $orderData['line_items'][] = array(
                'id' => (int)$item->getQuoteItemId(),
                'title' => $item->getName(),
                'price' => Helper::formatPrice($item->getPrice()),
                'sku' => $item->getSku(),
                'requires_shipping' => '',
                'taxable' => (float)$item->getTaxAmount() ? true : false,
                'product_id' => (int)$productId,
                'variant_id' => (int)$variant->getProductId(),
                'vendor' => '',
                'name' => $variant->getName(),
                'fulfillable_quantity' => (int)$item->getQtyOrdered(),
                'fulfillment_service' => '',
                'fulfillment_status' => '',
            );
        }

        return $orderData;
    }
}