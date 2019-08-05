<?php

namespace Ktpl\AddonPopup\Controller\Cart;

use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Checkout\Model\Cart as CustomerCart;
use Magento\Framework\Exception\NoSuchEntityException;

/** 
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class Addtocartcustom extends \Magento\Checkout\Controller\Cart
{
    const KEY_QTY_ITEM_PREFIX = 'ampromo_qty_select_';
    /**
     * @var ProductRepositoryInterface
     */
    protected $productRepository;

    /**
     * @var \Amasty\Promo\Model\Registry
     */
    protected $promoRegistry;

    /**
     * @var \Amasty\Promo\Helper\Cart
     */
    protected $promoCartHelper;
    
    /**
     * @param \Magento\Framework\App\Action\Context $context
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     * @param \Magento\Checkout\Model\Session $checkoutSession
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Magento\Framework\Data\Form\FormKey\Validator $formKeyValidator
     * @param CustomerCart $cart
     * @param ProductRepositoryInterface $productRepository
     * @codeCoverageIgnore
     */
    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Checkout\Model\Session $checkoutSession,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Framework\Data\Form\FormKey\Validator $formKeyValidator,
        CustomerCart $cart,
        \Amasty\Promo\Model\Registry $promoRegistry,
        \Amasty\Promo\Helper\Cart $promoCartHelper,
        ProductRepositoryInterface $productRepository
    ) {
        parent::__construct(
            $context,
            $scopeConfig,
            $checkoutSession,
            $storeManager,
            $formKeyValidator,
            $cart
        );
        $this->promoRegistry = $promoRegistry;
        $this->promoCartHelper = $promoCartHelper;
        $this->productRepository = $productRepository;
    }

    /**
     * Initialize product instance from request data
     *
     * @return \Magento\Catalog\Model\Product|false
     */
    protected function _initProduct()
    {
        $productId = (int)$this->getRequest()->getParam('product');
        if ($productId) {
            $storeId = $this->_objectManager->get(
                \Magento\Store\Model\StoreManagerInterface::class
            )->getStore()->getId();
            try {
                return $this->productRepository->getById($productId, false, $storeId);
            } catch (NoSuchEntityException $e) {
                return false;
            }
        }
        return false;
    }

    /**
     * Add product to shopping cart action
     *
     * @return \Magento\Framework\Controller\Result\Redirect
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     */
    public function execute()
    {
        if (!$this->_formKeyValidator->validate($this->getRequest())) {
            return $this->resultRedirectFactory->create()->setPath('*/*/');
        }

        $params = $this->getRequest()->getParams();

        try {
            if (isset($params['qty'])) {
                $filter = new \Zend_Filter_LocalizedToNormalized(
                    ['locale' => $this->_objectManager->get(
                        \Magento\Framework\Locale\ResolverInterface::class
                    )->getLocale()]
                );
                $params['qty'] = $filter->filter($params['qty']);
            }

            $product = $this->_initProduct();
            $related = $this->getRequest()->getParam('related_product');

            /**
             * Check product availability
             */
            if (!$product) {
                return $this->goBack();
            }

            $this->cart->addProduct($product, $params);
            if (!empty($related)) {
                $this->cart->addProductsByIds(explode(',', $related));
            }

            $this->cart->save();

            //--------------added for gift product ---------------------
            /*if (isset($params['radio-gift-update']) && $params['radio-gift-update']) {
                $productId = (int)$params['radio-gift-update'];
                $item = [];
                $item['product_id'] = $params['radio-gift-update'];
                $productgift = $this->productRepository->getById($productId);
                $qty = $this->getQtyByProductId($productId, $item);
                $params = $item;
                    $requestOptions = array_intersect_key($params, array_flip([
                        'super_attribute', 'options', 'super_attribute', 'links'
                    ]));
                $this->promoCartHelper->addProduct(
                        $productgift,
                        $qty,false,$requestOptions
                       
                    );
                $this->promoCartHelper->updateQuoteTotalQty(true); 
            }
            else if (isset($params['radio-group-gift'])) {
                $item = [];
                $productId = (int)$params['radio-group-gift'];
                $item['product_id'] = $params['radio-group-gift'];
                //=====================================================

               /** @var \Magento\Catalog\Model\Product $product *
            $productgift = $this->productRepository->getById($productId);

            if ($productgift->getId()) {
                $addAllRule = false;
                $addOneRule = false;
                $discount = null;
                $minimalPrice = null;
                $allowQty = null;

                $sku = $productgift->getSku();
                $limits = $this->promoRegistry->getLimits();
                $allRules = $limits;
                unset($allRules['_groups']);

                if ($allRules) {
                    foreach ($allRules as $ruleId => $rule) {
                        if (isset($rule['sku'][$sku])) {
                            $addAllRule =  $rule['sku'][$sku] > 0;
                            $addOneRule = $ruleId;
                            $allowQty = $rule['sku'][$sku]['qty'];
                            $discount = $rule['sku'][$sku]['discount'];

                            $minimalPrice = $rule['sku'][$sku]['discount']['minimal_price'];
                            break;
                        }
                    }
                }

                if (!$addAllRule && is_array($limits['_groups'])) {
                    foreach ($limits['_groups'] as $ruleId => $rule) {
                        if (in_array($sku, $rule['sku'])) {
                            $addOneRule = $ruleId;
                        }
                        $discount = $rule['discount'];
                        $minimalPrice = $rule['discount']['minimal_price'];
                    }
                }

                if ($addAllRule || $addOneRule) {
                    $qty = $this->getQtyByProductId($productId, $item);
                    if ($allowQty && $qty > $allowQty) {
                        $qty = $allowQty;
                    }

                    $params = $item;
                    $requestOptions = array_intersect_key($params, array_flip([
                        'super_attribute', 'options', 'super_attribute', 'links'
                    ]));
                    if (isset($params['radio-gift-update']) && $params['radio-gift-update']) {
                        $productgift = $this->productRepository->getById($params['radio-gift-update']);
                    }
                    $this->promoCartHelper->addProduct(
                        $productgift,
                        $qty,
                        $addOneRule,
                        $requestOptions,
                        $discount,
                        $minimalPrice
                    );
                }
            }

                //================================

                $this->promoCartHelper->updateQuoteTotalQty(true);
            }

            //--------------added for gift product end ---------------------

            /**
             * @todo remove wishlist observer \Magento\Wishlist\Observer\AddToCart
             */
            $this->_eventManager->dispatch(
                'checkout_cart_add_product_complete',
                ['product' => $product, 'request' => $this->getRequest(), 'response' => $this->getResponse()]
            );

            if (!$this->_checkoutSession->getNoCartRedirect(true)) {
                if (!$this->cart->getQuote()->getHasError()) {
                    $message = __(
                        'You added %1 to your cart.',
                        $product->getName()
                    );
                    $this->messageManager->addSuccessMessage($message);
                }
                return $this->goBack(null, $product);
            }
        } catch (\Magento\Framework\Exception\LocalizedException $e) {
            if ($this->_checkoutSession->getUseNotice(true)) {
                $this->messageManager->addNotice(
                    $this->_objectManager->get(\Magento\Framework\Escaper::class)->escapeHtml($e->getMessage())
                );
            } else {
                $messages = array_unique(explode("\n", $e->getMessage()));
                foreach ($messages as $message) {
                    $this->messageManager->addError(
                        $this->_objectManager->get(\Magento\Framework\Escaper::class)->escapeHtml($message)
                    );
                }
            }

            $url = $this->_checkoutSession->getRedirectUrl(true);

            if (!$url) {
                $cartUrl = $this->_objectManager->get(\Magento\Checkout\Helper\Cart::class)->getCartUrl();
                $url = $this->_redirect->getRedirectUrl($cartUrl);
            }

            return $this->goBack($url);
        } catch (\Exception $e) {
            $this->messageManager->addException($e, __($e->getMessage().'We can\'t add this item to your shopping cart right now.'));
            $this->_objectManager->get(\Psr\Log\LoggerInterface::class)->critical($e);
            return $this->goBack();
        }
    }

    /**
     * Resolve response
     *
     * @param string $backUrl
     * @param \Magento\Catalog\Model\Product $product
     * @return $this|\Magento\Framework\Controller\Result\Redirect
     */
    protected function goBack($backUrl = null, $product = null)
    {
        if (!$this->getRequest()->isAjax()) {
            return parent::_goBack($backUrl);
        }

        $result = [];

        if ($backUrl || $backUrl = $this->getBackUrl()) {
            $result['backUrl'] = $backUrl;
        } else {
            if ($product && !$product->getIsSalable()) {
                $result['product'] = [
                    'statusText' => __('Out of stock')
                ];
            }
        }

        $this->getResponse()->representJson(
            $this->_objectManager->get(\Magento\Framework\Json\Helper\Data::class)->jsonEncode($result)
        );
    }

    protected function getQtyByProductId($productId, $data)
    {
        return isset($data[self::KEY_QTY_ITEM_PREFIX . $productId])
            ? $data[self::KEY_QTY_ITEM_PREFIX . $productId] : 1;
    }
}
