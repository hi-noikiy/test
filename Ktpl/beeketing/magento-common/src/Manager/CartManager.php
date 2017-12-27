<?php
/**
 * Created by PhpStorm.
 * User: tungquach
 * Date: 12/07/2017
 * Time: 00:11
 */

namespace Beeketing\MagentoCommon\Manager;


use Beeketing\MagentoCommon\Libraries\Helper;

class CartManager
{
    /**
     * @var \Magento\Checkout\Model\Cart
     */
    private $cart;

    /**
     * @var \Magento\Framework\App\ObjectManager
     */
    private $objectManager;

    /**
     * @var \Magento\CatalogUrlRewrite\Model\ProductUrlPathGenerator
     */
    private $productUrlPathGenerator;

    /**
     * @var \Magento\Quote\Model\ResourceModel\Quote\Item
     */
    private $quoteItem;

    /**
     * OrderManager constructor.
     */
    public function __construct()
    {
        $this->objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $this->cart = $this->objectManager->get('\Magento\Checkout\Model\Cart');
        $this->productUrlPathGenerator = $this->objectManager->get('\Magento\CatalogUrlRewrite\Model\ProductUrlPathGenerator');
        $this->quoteItem = $this->objectManager->get('\Magento\Quote\Model\ResourceModel\Quote\Item');
    }

    /**
     * Get cart
     *
     * @return array
     */
    public function getCart()
    {
        $cart = $this->cart->getQuote();
        $result = [
            'token' => $cart->getId(),
            'item_count' => (int)$cart->getItemsCount(),
            'total_price' => (float)$cart->getSubtotal(),
            'items' => array(),
        ];

        // Traverse cart items
        $variantItems = array();
        foreach ($cart->getAllItems() as $item) {
            if ($item->getParentItemId()) {
                $variantItems[$item->getParentItemId()] = $item;
            }
        }

        foreach ($cart->getAllVisibleItems() as $item) {
            $variant = isset($variantItems[$item->getId()]) ? $variantItems[$item->getId()] : $item;

            $result['items'][] = $this->formatItem($variant, $item);
        }

        return $result;
    }

    /**
     * Save cart
     */
    public function saveCart()
    {
        $this->cart->save();
    }

    /**
     * Add cart
     *
     * @param $productId
     * @param $params
     * @param bool $save
     * @return array
     */
    public function addCart($productId, $params, $save = true)
    {
        // Add product to cart
        $this->cart->addProduct($productId, $params);

        if ($save) {
            $this->cart->save();

            // Get added item
            $cart = $this->cart->getQuote();

            // Traverse cart items
            $variantItems = array();
            foreach ($cart->getAllItems() as $item) {
                if ($item->getParentItemId()) {
                    $variantItems[$item->getParentItemId()] = $item;
                }
            }

            // Traverse cart visible items
            foreach ($cart->getAllVisibleItems() as $item) {
                if ($item->getProductId() != $productId) {
                    continue;
                }

                $variant = isset($variantItems[$item->getId()]) ? $variantItems[$item->getId()] : $item;

                // Check attributes
                $vResult = null;
                $product = $item->getProduct();
                $productTypeInstance = $product->getTypeInstance();
                if (
                    isset($params['super_attribute']) &&
                    $params['super_attribute'] &&
                    $productTypeInstance->getChildrenIds($product->getId())
                ) {
                    $options = $productTypeInstance->getOrderOptions($product);
                    $infoBuyRequestSuperAttribute = isset($options['info_buyRequest']['super_attribute']) ?
                        $options['info_buyRequest']['super_attribute'] : array();
                    $count = 0;
                    if ($infoBuyRequestSuperAttribute) {
                        foreach ($params['super_attribute'] as $supperAttributeId => $supperAttributeValue) {
                            foreach ($infoBuyRequestSuperAttribute as $k => $v) {
                                if ($k == $supperAttributeId && $v == $supperAttributeValue) {
                                    $count++;
                                }
                            }
                        }
                    }

                    // Validate
                    if ($count != count($infoBuyRequestSuperAttribute)) {
                        continue;
                    }
                }

                // Check options
                if (
                    isset($params['options']) &&
                    $params['options']
                ) {
                    $options = $productTypeInstance->getOrderOptions($product);
                    $infoBuyRequestOptions = isset($options['info_buyRequest']['options']) ?
                        $options['info_buyRequest']['options'] : array();
                    $count = 0;
                    if ($infoBuyRequestOptions) {
                        foreach ($params['options'] as $optionId => $optionValue) {
                            foreach ($infoBuyRequestOptions as $k => $v) {
                                if ($k == $optionId && $v == $optionValue) {
                                    $count++;
                                }
                            }
                        }
                    }

                    // Validate
                    if ($count != count($infoBuyRequestOptions)) {
                        continue;
                    }
                }

                return $this->formatItem($variant, $item);
            }
        }

        return array();
    }

    /**
     * Update cart
     *
     * @param $itemId
     * @param $quantity
     */
    public function updateCart($itemId, $quantity)
    {
        if ($quantity) { // Update cart item
            $cartData[$itemId]['qty'] = $quantity;
            if (is_array($cartData)) {
                $filter = new \Zend_Filter_LocalizedToNormalized(
                    ['locale' => $this->objectManager->get('Magento\Framework\Locale\ResolverInterface')->getLocale()]
                );
                foreach ($cartData as $index => $data) {
                    if (isset($data['qty'])) {
                        $cartData[$index]['qty'] = $filter->filter(trim($data['qty']));
                    }
                }
                if (!$this->cart->getCustomerSession()->getCustomerId() && $this->cart->getQuote()->getCustomerId()) {
                    $this->cart->getQuote()->setCustomerId(null);
                }
                $cartData = $this->cart->suggestItemsQty($cartData);
                $this->cart->updateItems($cartData);
            }

        } else { // Remove cart item
            $this->cart->removeItem($itemId);
        }
    }

    /**
     * Format item
     *
     * @param $variant
     * @param $item
     * @return array
     */
    private function formatItem($variant, $item)
    {
        $product = $variant->getProduct() ?: $item->getProduct();
        return array(
            'id' => (int)$item->getId(),
            'variant_id' => (int)$variant->getProductId(),
            'variant_title' => $variant->getName(),
            'product_id' => (int)$item->getProductId(),
            'title' => $item->getName(),
            'product_title' => $item->getName(),
            'price' => (float)$item->getPrice(),
            'line_price' => (float)$item->getPrice() * (int)$item->getQty(),
            'quantity' => (int)$item->getQty(),
            'sku' => $variant->getSku(),
            'handle' => $this->productUrlPathGenerator->getUrlPathWithSuffix($item->getProduct(), null),
            'image' => Helper::getProductImageUrl($product->getData('thumbnail')),
            'url' => $item->getProduct()->getProductUrl(),
        );
    }
}