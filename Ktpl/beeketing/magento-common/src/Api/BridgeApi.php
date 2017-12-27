<?php
/**
 * Bridge api communicate with Beeketing
 *
 * @since      1.0.0
 * @author     Beeketing
 */

namespace Beeketing\MagentoCommon\Api;


use Beeketing\MagentoCommon\Data\Api;
use Beeketing\MagentoCommon\Data\Setting;
use Beeketing\MagentoCommon\Manager\CartManager;
use Beeketing\MagentoCommon\Manager\CollectionManager;
use Beeketing\MagentoCommon\Manager\CollectManager;
use Beeketing\MagentoCommon\Libraries\Helper;
use Beeketing\MagentoCommon\Libraries\SettingHelper;
use Beeketing\MagentoCommon\Manager\CustomerManager;
use Beeketing\MagentoCommon\Manager\OrderManager;
use Beeketing\MagentoCommon\Manager\ProductManager;
use Beeketing\MagentoCommon\Manager\VariantManager;
use Buzz\Browser;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Buzz\Client\Curl;

class BridgeApi
{
    const VALIDATE_HEADER_ACCESS_TOKEN = 'X-Beeketing-Access-Token';
    const VALIDATE_HEADER_API_KEY = 'X-Beeketing-Key';
    const VALIDATE_HEADER_CLIENT_KEY = 'X-Beeketing-Client-Key';
    const REQUEST_TIMEOUT = 20;

    /**
     * @var Browser
     */
    private $browser;

    /**
     * @var SettingHelper
     */
    private $settingHelper;

    /**
     * @var \Magento\Framework\App\ObjectManager
     */
    private $objectManager;

    /**
     * @var CollectManager
     */
    private $collectManager;

    /**
     * @var CollectionManager
     */
    private $collectionManager;

    /**
     * @var ProductManager
     */
    private $productManager;

    /**
     * @var CustomerManager
     */
    private $customerManager;

    /**
     * @var OrderManager
     */
    private $orderManager;

    /**
     * @var VariantManager
     */
    private $variantManager;

    /**
     * @var CartManager
     */
    private $cartManager;

    /**
     * BridgeApi constructor.
     */
    public function __construct()
    {
        // Set http client
        $client = null;
        if (function_exists('curl_version')) {
            $client = new Curl();
        }
        $this->browser = new Browser($client);
        $this->browser->getClient()->setTimeout(self::REQUEST_TIMEOUT);

        // Managers
        $this->objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $this->collectManager = new CollectManager();
        $this->collectionManager = new CollectionManager();
        $this->productManager = new ProductManager();
        $this->customerManager = new CustomerManager();
        $this->orderManager = new OrderManager();
        $this->variantManager = new VariantManager();
        $this->cartManager = new CartManager();
    }

    /**
     * Set setting helper
     * @param SettingHelper $settingHelper
     */
    public function setSettingHelper(SettingHelper $settingHelper)
    {
        $this->settingHelper = $settingHelper;
        SettingHelper::setInstance($settingHelper);
    }

    /**
     * Response error
     *
     * @param $message
     */
    private function responseError($message)
    {
        $this->response(array(
            'errors' => $message,
        ));
        exit;
    }

    /**
     * Api response
     *
     * @param array $result
     */
    private function response($result = array())
    {
        $response = new Response();
        $response->headers->set('Content-Type', 'application/json');
        $response->setContent(json_encode($result));

        $response->send();
        exit;
    }

    /**
     * Handle request
     */
    public function handleRequest()
    {
        $this->handleBeeketingRequest();
        $this->handleApiRequest();
    }

    /**
     * Process request
     *
     * @param Request $request
     * @param string $suffix
     */
    private function processRequest(Request $request, $suffix = null)
    {
        // Validate request
        if (!$request->get('resource')) {
            $this->responseError('Resource unrecognized');
        }

        // Get handle method by resource and request method
        $resource = $request->get('resource');
        $resourceList = explode('_', $resource);

        // Insert method suffix
        if ($suffix) {
            $resourceList[] = $suffix;
        }

        $resourceList = array_map('ucfirst', $resourceList);
        $method = strtolower($request->getMethod()) . implode('', $resourceList);

        if (method_exists($this, $method)) {
            $this->{$method}($request);
        } else {
            // Method not found
            $this->responseError('Method not allowed');
        }

        exit;
    }

    /**
     * Handle beeketing request
     */
    private function handleBeeketingRequest()
    {
        $request = Request::createFromGlobals();
        $headerApiKey = $request->headers->get(BridgeApi::VALIDATE_HEADER_API_KEY);
        if (
            $headerApiKey &&
            $headerApiKey == $this->settingHelper->getSettings(Setting::SETTING_API_KEY)
        ) {
            $this->processRequest($request, 'beeketing');
        }
    }

    /**
     * Handle api request
     */
    private function handleApiRequest()
    {
        $request = Request::createFromGlobals();
        $headerAccessToken = $request->headers->get(BridgeApi::VALIDATE_HEADER_ACCESS_TOKEN);

        $resourceConnection = $this->objectManager->get('\Magento\Framework\App\ResourceConnection');
        $connection = $resourceConnection->getConnection();
        $select = $connection->select()
            ->from($resourceConnection->getTableName('core_config_data'), 'path')
            ->where('path like ?', 'beeketing/%/settings')
            ->where('value like ?', '%"' . $headerAccessToken . '"%');
        $appSettingKey = $connection->fetchOne($select);
        if ($appSettingKey) {
            $this->settingHelper->setAppSettingKey($appSettingKey);
        }

        if (
            $headerAccessToken &&
            $headerAccessToken == $this->settingHelper->getSettings(Setting::SETTING_ACCESS_TOKEN)
        ) {
            $this->processRequest($request);
        }
    }

    /**
     * Post install app
     *
     * @param Request $request
     * @resource install_app
     */
    protected function postInstallAppBeeketing(Request $request)
    {
        $apiKey = $request->headers->get(BridgeApi::VALIDATE_HEADER_API_KEY);
        $content = json_decode($request->getContent(), true);
        if (!isset($content['app'])) {
            $this->responseError('Data is not valid');
        }

        $appCode = $content['app'];
        $appKey = isset($content['app_key']) ? $content['app_key'] : null;
        $settingKeys = SettingHelper::settingKeys();
        if (isset($settingKeys[$appCode])) {
            $this->settingHelper->switchSettings($appCode);
        } elseif ($appKey) {
            $this->settingHelper->setAppSettingKey(Helper::generateAppSettingKey($appKey));
        } else {
            $this->responseError('App setting key is not valid');
        }

        $token = Helper::generateAccessToken();
        $this->settingHelper->updateSettings(Setting::SETTING_ACCESS_TOKEN, $token);
        $this->settingHelper->updateSettings(Setting::SETTING_API_KEY, $apiKey);

        $this->response(array(
            'setting' => $this->settingHelper->getSettings(),
        ));
    }

    /**
     * Get cart
     *
     * @param Request $request
     * @resource cart
     */
    protected function getCartBeeketing(Request $request)
    {
        $result = $this->cartManager->getCart();

        $this->response(array(
            'cart' => $result,
        ));
    }

    /**
     * Add cart
     *
     * @param Request $request
     * @resource cart
     */
    protected function postCartBeeketing(Request $request)
    {
        $type = $request->get('type');
        $productId = $request->get('product_id');
        $variantId = $request->get('variant_id');
        $quantity = $request->get('quantity');
        $attributes = $request->get('attributes');
        $options = $request->get('options');
        /** @var \Magento\Catalog\Api\ProductRepositoryInterface $productRepository */
        $productRepository = $this->objectManager->get('\Magento\Catalog\Api\ProductRepositoryInterface');

        if ($type == 'multi') { // Add multi
            foreach ($productId as $key => $id) {
                /** @var \Magento\Catalog\Model\Product $product */
                $product = $productRepository->getById($id);
                $productTypeInstance = $product->getTypeInstance();
                $childrenIds = $productTypeInstance->getChildrenIds($product->getId());
                $childrenIds = array_shift($childrenIds) ?: $childrenIds;

                // Get real product id
                if (isset($variantId[$key]) && !in_array($variantId[$key], $childrenIds)) {
                    $id = $variantId[$key];
                }

                $params = array(
                    'qty' => $quantity ? $quantity[$key] : 1,
                    'super_attribute' => $attributes && isset($attributes[$key]) ? $attributes[$key] : array(),
                );

                $this->cartManager->addCart($id, $params, false);
            }

            $this->cartManager->saveCart();
            $result = $this->cartManager->getCart();
            $key = 'cart';
        } else { // Add single
            /** @var \Magento\Catalog\Model\Product $product */
            $product = $productRepository->getById($productId);
            $productTypeInstance = $product->getTypeInstance();
            $childrenIds = $productTypeInstance->getChildrenIds($product->getId());
            $childrenIds = array_shift($childrenIds) ?: $childrenIds;

            // Get real product id
            if (isset($variantId) && !in_array($variantId, $childrenIds)) {
                $productId = $variantId;
            }

            $params = array(
                'qty' => $quantity,
                'super_attribute' => $attributes,
                'options' => $options,
            );

            $result = $this->cartManager->addCart($productId, $params);
            $key = 'item';
        }

        $this->response(array(
            $key => $result,
        ));
    }

    /**
     * Update cart
     *
     * @param Request $request
     * @resource cart
     */
    protected function putCartBeeketing(Request $request)
    {
        $id = $request->get('id');

        foreach ($id as $itemId => $quantity) {
            $this->cartManager->updateCart($itemId, $quantity);
        }

        $this->cartManager->saveCart();
        $result = $this->cartManager->getCart();

        $this->response(array(
            'cart' => $result,
        ));
    }

    /**
     * Get shop info
     *
     * @param Request $request
     * @resource shop
     */
    protected function getShop(Request $request)
    {
        $storeManager = $this->objectManager->get('\Magento\Store\Model\StoreManagerInterface');
        $priceCurrency = $this->objectManager->get('\Magento\Framework\Pricing\PriceCurrencyInterface');
        $url = $this->objectManager->get('\Magento\Framework\UrlInterface');
        $helper = new Helper();

        $formatted_price = null;
        $currency = null;
        $store = $storeManager->getStore();

        $formatted_price = $priceCurrency->format('11.11', true);
        $formatted_price = preg_replace('/[1]+[.,]{0,1}[1]+/', '{{amount}}', $formatted_price, 1);
        $currency = $store->getCurrentCurrencyCode();

        $result = array();
        // Set shop domain from response data
        $result['domain'] = $helper->getShopDomain();
        $result['absolute_path'] = $url->getBaseUrl();
        $result['currency'] = $currency;
        $result['currency_format'] = $formatted_price;
        $result['id'] = (int)$store->getId();

        $this->response(array(
            'shop' => $result,
        ));
    }

    /**
     * Get setting
     *
     * @param Request $request
     * @resource setting
     */
    protected function getSetting(Request $request)
    {
        $this->response(array(
            'shop' => $this->settingHelper->getSettings(),
        ));
    }

    /**
     * Put setting
     *
     * @param Request $request
     * @resource setting
     */
    protected function putSetting(Request $request)
    {
        // Validate
        if (!$request->getContent()) {
            $this->responseError('Setting data is not valid');
        }

        $content = json_decode($request->getContent(), true);
        if (!isset($content['setting'])) {
            $this->responseError('Setting data is not valid');
        }

        // Get store id
        $headerAccessToken = $request->headers->get(BridgeApi::VALIDATE_HEADER_ACCESS_TOKEN);
        $resourceConnection = $this->objectManager->get('\Magento\Framework\App\ResourceConnection');
        $connection = $resourceConnection->getConnection();
        $path = $this->settingHelper->getAppSettingKey();

        $select = $connection->select()
            ->from($resourceConnection->getTableName('core_config_data'), 'scope_id')
            ->where('path=?', $path)
            ->where('value like ?', '%"' . $headerAccessToken . '"%');
        $storeId = $connection->fetchOne($select);
        $this->settingHelper->setStoreId($storeId);

        // Update setting
        foreach ($content['setting'] as $setting => $value) {
            $this->settingHelper->updateSettings($setting, $value);
        }

        $this->response(array(
            'setting' => $this->settingHelper->getSettings(),
        ));
    }

    /**
     * Get collections count
     *
     * @param Request $request
     * @resource collections_count
     */
    protected function getCollectionsCount(Request $request)
    {
        $result = $this->collectionManager->getCollectionsCount();

        $this->response(array(
            'count' => $result,
        ));
    }

    /**
     * Get collections
     *
     * @param Request $request
     * @resource collections
     */
    protected function getCollections(Request $request)
    {
        $resourceId = $request->get('resource_id');
        $key = $resourceId ? 'collection' : 'collections';
        $title = $request->get('title');
        $limit = $request->get('limit', Api::ITEM_PER_PAGE);
        $page = $request->get('page', Api::PAGE);

        $result = array();
        try {
            if ($resourceId) { // Collection
                $result = $this->collectionManager->getCollectionById($resourceId);
            } else { // All collections
                $result = $this->collectionManager->getCollections($title, $page, $limit);
            }
        } catch (\Exception $e) {
            $this->responseError($e->getMessage());
        }

        // Result
        $this->response(array(
            $key => $result,
        ));
    }

    /**
     * Get collects count
     *
     * @param Request $request
     * @resource collects_count
     */
    protected function getCollectsCount(Request $request)
    {
        $collectionId = $request->get('collection_id');
        $productId = $request->get('product_id');

        $count = 0;
        try {
            if ($collectionId) { // Count by collection id
                $count = $this->collectManager->getCollectsCountByCollectionId($collectionId);
            } elseif ($productId) { // Count by product id
                $count = $this->collectManager->getCollectsCountByProductId($productId);
            } else { // Count all
                $count = $this->collectManager->getCollectsCount();
            }
        } catch (\Exception $e) {
            $this->responseError($e->getMessage());
        }

        $this->response(array(
            'count' => $count,
        ));
    }

    /**
     * Get collects
     *
     * @param Request $request
     * @resource collects
     */
    protected function getCollects(Request $request)
    {
        $limit = $request->get('limit', Api::ITEM_PER_PAGE);
        $page = $request->get('page', Api::PAGE);
        $collectionId = $request->get('collection_id');
        $productId = $request->get('product_id');

        $collects = array();
        try {
            if ($collectionId) {
                $collects = $this->collectManager->getCollectsByCollectionId($collectionId, $page, $limit);
            } elseif ($productId) {
                $collects = $this->collectManager->getCollectsByProductId($productId, $page, $limit);
            } else {
                $collects = $this->collectManager->getCollects($page, $limit);
            }
        } catch (\Exception $e) {
            $this->responseError($e->getMessage());
        }

        $this->response(array(
            'collects' => $collects,
        ));
    }

    /**
     * Get products count
     *
     * @param Request $request
     * @resource products_count
     */
    protected function getProductsCount(Request $request)
    {
        $result = 0;
        try {
            $result = $this->productManager->getProductsCount();
        } catch (\Exception $e) {
            $this->responseError($e->getMessage());
        }

        $this->response(array(
            'count' => $result,
        ));
    }

    /**
     * Get products
     *
     * @param Request $request
     * @resource products
     */
    protected function getProducts(Request $request)
    {
        $resourceId = $request->get('resource_id');
        $key = $resourceId ? 'product' : 'products';

        $limit = $request->get('limit', Api::ITEM_PER_PAGE);
        $page = $request->get('page', Api::PAGE);
        $title = $request->get('title');

        $result = array();
        try {
            if ($resourceId) { // Product
                $result = $this->productManager->getProductById($resourceId);
            } else { // All products
                $result = $this->productManager->getProducts($title, $page, $limit);
            }
        } catch (\Exception $e) {
            $this->responseError($e->getMessage());
        }

        // Result
        $this->response(array(
            $key => $result,
        ));
    }

    /**
     * Update products
     *
     * @param Request $request
     * @resource products
     */
    protected function putProducts(Request $request)
    {
        $resourceId = $request->get('resource_id');

        $content = json_decode($request->getContent(), true);
        if (!$resourceId || !isset($content['product'])) {
            $this->responseError('Data is not valid');
        }

        // Update
        $result = array();
        try {
            $result = $this->productManager->updateProduct($resourceId, $content['product']);
        } catch (\Exception $e) {
            $this->responseError($e->getMessage());
        }

        // Result
        $this->response(array(
            'product' => $result,
        ));
    }

    /**
     * Get customers count
     *
     * @param Request $request
     * @resource customers_count
     */
    protected function getCustomersCount(Request $request)
    {
        $result = 0;
        try {
            $result = $this->customerManager->getCustomersCount();
        } catch (\Exception $e) {
            $this->responseError($e->getMessage());
        }

        $this->response(array(
            'count' => $result,
        ));
    }

    /**
     * Get customers
     *
     * @param Request $request
     * @resource customers
     */
    protected function getCustomers(Request $request)
    {
        $resourceId = $request->get('resource_id');
        $key = $resourceId ? 'customer' : 'customers';

        $limit = $request->get('limit', Api::ITEM_PER_PAGE);
        $page = $request->get('page', Api::PAGE);

        $result = array();
        try {
            if ($resourceId) { // Customer
                $result = $this->customerManager->getCustomerById($resourceId);
            } else { // All customers
                $result = $this->customerManager->getCustomers($page, $limit);
            }
        } catch (\Exception $e) {
            $this->responseError($e->getMessage());
        }

        // Result
        $this->response(array(
            $key => $result,
        ));
    }

    /**
     * Get orders count
     *
     * @param Request $request
     * @resource orders_count
     */
    protected function getOrdersCount(Request $request)
    {
        $result = 0;
        try {
            $result = $this->orderManager->getOrdersCount();
        } catch (\Exception $e) {
            $this->responseError($e->getMessage());
        }

        $this->response(array(
            'count' => $result,
        ));
    }

    /**
     * Get orders
     *
     * @param Request $request
     * @resource orders
     */
    protected function getOrders(Request $request)
    {
        $resourceId = $request->get('resource_id');
        $key = $resourceId ? 'order' : 'orders';

        $limit = $request->get('limit', Api::ITEM_PER_PAGE);
        $page = $request->get('page', Api::PAGE);

        $result = array();
        try {
            if ($resourceId) { // Order
                $result = $this->orderManager->getOrderById($resourceId);
            } else { // All orders
                $result = $this->orderManager->getOrders($page, $limit);
            }
        } catch (\Exception $e) {
            $this->responseError($e->getMessage());
        }

        // Result
        $this->response(array(
            $key => $result,
        ));
    }

    /**
     * Get variants
     *
     * @param Request $request
     * @resource variants
     */
    protected function getVariants(Request $request)
    {
        $productId = $request->get('product_id');
        $resourceId = $request->get('resource_id');
        $key = $resourceId ? 'variant' : 'variants';

        $result = array();
        if ($resourceId) { // Variant
            try {
                $result = $this->variantManager->getVariantById($resourceId, $productId);
            } catch (\Exception $e) {
                $this->responseError($e->getMessage());
            }
        }

        $this->response(array(
            $key => $result,
        ));
    }

    /**
     * Put variants
     *
     * @param Request $request
     * @resource variants
     */
    protected function putVariants(Request $request)
    {
        $productId = $request->get('product_id');
        $resourceId = $request->get('resource_id');

        $content = json_decode($request->getContent(), true);
        if (!$resourceId || !isset($content['variant'])) {
            $this->responseError('Data is not valid');
        }

        // Update
        $result = array();
        try {
            $result = $this->variantManager->updateVariant($resourceId, $productId, $content['variant']);
        } catch (\Exception $e) {
            $this->responseError($e->getMessage());
        }

        // Result
        $this->response(array(
            'variant' => $result,
        ));
    }

    /**
     * Post products variants
     *
     * @param Request $request
     * @resource products_variants
     */
    protected function postProductsVariants(Request $request)
    {
        $productId = $request->get('product_id');

        $content = json_decode($request->getContent(), true);
        if (!$productId || !isset($content['variant'], $content['variant']['origin_id'])) {
            $this->responseError('Data is not valid');
        }

        // Create
        $result = array();
        try {
            $result = $this->variantManager->createVariant($productId, $content['variant']);
        } catch (\Exception $e) {
            $this->responseError($e->getMessage());
        }

        // Result
        $this->response(array(
            'variant' => $result,
        ));
    }

    /**
     * Delete products variants
     *
     * @param Request $request
     * @resource products_variants
     */
    protected function deleteProductsVariants(Request $request)
    {
        $productId = $request->get('product_id');
        $variantId = $request->get('variant_id');

        // Validate data
        if (!$variantId) {
            $this->responseError('Data is not valid');
        }

        // Create
        try {
            $this->variantManager->deleteVariant($productId, $variantId);
        } catch (\Exception $e) {
            $this->responseError($e->getMessage());
        }

        // Result
        $this->response(array());
    }
}