<?php
/**
 * App api
 *
 * @since      1.0.0
 * @author     Beeketing
 *
 */

namespace Beeketing\MagentoCommon\Api;


use Beeketing\MagentoCommon\Data\Constant;
use Beeketing\MagentoCommon\Data\Setting;
use Beeketing\MagentoCommon\Data\Webhook;
use Beeketing\MagentoCommon\Libraries\Helper;
use Beeketing\MagentoCommon\Libraries\SettingHelper;
use Buzz\Browser;
use Buzz\Exception\ClientException;
use Buzz\Client\Curl;
use Buzz\Message\RequestInterface;

class CommonApi
{
    const METHOD_GET = 'GET';
    const METHOD_POST = 'POST';
    const METHOD_PUT = 'PUT';
    const METHOD_DELETE = 'DELETE';
    const REQUEST_TIMEOUT = 20;
    const MAXIMUM_RETRY_TIME = 2;

    private $beeketingPath;
    private $beeketingApi;
    private $apiKey;
    private $appCode;

    /**
     * @var SettingHelper
     */
    private $settingHelper;

    /**
     * @var Helper
     */
    private $helper;

    /**
     * @var \Magento\Framework\App\ObjectManager
     */
    private $objectManager;

    /**
     * @var Browser
     */
    private $browser;

    /**
     * App constructor.
     *
     * @param $beeketingPath
     * @param $beeketingApi
     * @param $appCode
     * @throws \Exception
     */
    public function __construct($beeketingPath, $beeketingApi, $appCode)
    {
        if (
            !$beeketingPath ||
            !$beeketingApi ||
            !$appCode
        ) {
            throw new \Exception('Failed to config api');
        }

        $this->beeketingPath = $beeketingPath;
        $this->beeketingApi = $beeketingApi;
        $this->appCode = $appCode;

        // Object manager
        $this->objectManager = \Magento\Framework\App\ObjectManager::getInstance();

        // Helper
        $this->helper = new Helper();

        // Set http client
        $client = null;
        if (function_exists('curl_version')) {
            $client = new Curl();
        }
        $this->browser = new Browser($client);
        $this->browser->getClient()->setTimeout(self::REQUEST_TIMEOUT);
    }

    /**
     * Set api key
     * @param $apiKey
     */
    public function setApiKey($apiKey)
    {
        $this->apiKey = $apiKey;
    }

    /**
     * Get api key
     * @return mixed
     */
    public function getApiKey()
    {
        return $this->apiKey;
    }

    /**
     * Set setting helper
     *
     * @param SettingHelper $settingHelper
     */
    public function setSettingHelper(SettingHelper $settingHelper)
    {
        $this->settingHelper = $settingHelper;
        SettingHelper::setInstance($settingHelper);
    }

    /**
     * Get setting helper
     *
     * @return SettingHelper
     */
    public function getSettingHelper()
    {
        return $this->settingHelper;
    }

    /**
     * Install app
     *
     * @return bool
     */
    public function installApp()
    {
        // If not api key
        if (!$this->apiKey) {
            return false;
        }

        // Generate access token
        $token = $this->settingHelper->getSettings(Setting::SETTING_ACCESS_TOKEN);
        if (!$token) {
            $token = Helper::generateAccessToken();
            // Update setting access token
            $this->settingHelper->updateSettings(Setting::SETTING_ACCESS_TOKEN, $token);
        }

        // Update api key
        $this->settingHelper->updateSettings(Setting::SETTING_API_KEY, $this->apiKey);
        $this->settingHelper->updateSettings(Setting::SETTING_SITE_URL, $this->helper->getShopAbsolutePath());

        // Update setting store id
        /** @var \Magento\Store\Model\StoreManagerInterface $storeManager */
        $storeManager = $this->objectManager->get('\Magento\Store\Model\StoreManagerInterface');
        $this->settingHelper->updateSettings(Setting::SETTING_STORE_ID, $storeManager->getStore()->getId());

        $params = array(
            'app' => $this->appCode,
            'access_token' => $token,
        );

        $url = $this->getUrl('magento/install_app');
        $result = $this->post($url, $params);
        if (isset($result['hit']) && $result['hit']) { // Install successfully
            return true;
        } else { // Install fail
            $this->settingHelper->deleteSettings();
        }

        return false;
    }

    /**
     * Update shop absolute path
     * @return bool
     */
    public function updateShopAbsolutePath()
    {
        $url = $this->getUrl('shops');
        $result = $this->put($url, array(
            'absolute_path' => $this->helper->getShopAbsolutePath(),
        ));

        if (!isset($result['errors'])) {
            return true;
        }

        return false;
    }

    /**
     * Get login shop url
     *
     * @return string
     */
    public function getLoginShopUrl()
    {
        $token = base64_encode(json_encode(array(
            'api_key' => $this->apiKey,
            'app' => $this->appCode,
        )));

        return $this->getUrl('magento/login_shop', array(
            'token' => $token
        ), $this->beeketingPath);
    }

    /**
     * Get beeketing user email
     *
     * @return string|bool
     */
    public function getBeeketingUserEmail()
    {
        $this->apiKey = md5(uniqid()); // Fake api key
        $domain = $this->helper->getShopDomain();
        $url = $this->getUrl('magento/get_user_email', array(
            'domain' => $domain,
        ));

        $result = $this->get($url);

        if (isset($result['email'])) {
            return $result['email'];
        }

        return false;
    }

    /**
     * Get snippet
     *
     * @return string
     */
    public function getSnippet()
    {
        $snippet = $this->settingHelper->getSettings(Setting::SETTING_SNIPPET);

        // Get shop snippet from api
        if (!$snippet) {
            $snippet = $this->getShopSnippet();
            if ($snippet) {
                $this->settingHelper->updateSettings(Setting::SETTING_SNIPPET, $snippet);
            }
        }

        return html_entity_decode($snippet) . $this->getPageSnippet();
    }

    private function getPageSnippet()
    {
        /** @var \Magento\Framework\App\Request\Http $request */
        $request = $this->objectManager->get('\Magento\Framework\App\Request\Http');
        /** @var \Magento\Customer\Model\Session $customerSession */
        $customerSession = $this->objectManager->get('\Magento\Customer\Model\Session');
        /** @var \Magento\Framework\UrlInterface $url */
        $url = $this->objectManager->get('\Magento\Framework\UrlInterface');

        $data = array();

        // Page url
        $data['page_url'] = array(
            'home' => $url->getBaseUrl(),
            'cart' => $url->getUrl('checkout/cart'),
            'checkout' => $url->getUrl('checkout'),
        );

        // Customer
        if ($customerSession->isLoggedIn()) {
            $data['customer'] = array(
                'id' => $customerSession->getId(),
            );
        }

        // Page
        $data['page'] = array();
        switch ($request->getFullActionName()) {
            case 'cms_index_index':
                $data['page']['type'] = 'home';
                break;
            case 'checkout_cart_index':
                $data['page']['type'] = 'cart';
                break;
            case 'checkout_index_index':
                $data['page']['type'] = 'checkout';
                break;
            case 'catalog_product_view':
                $registry = $this->objectManager->get('\Magento\Framework\Registry');
                $currentProduct = $registry->registry('current_product');
                $data['page']['type'] = 'product';
                $data['page']['id'] = (int)$currentProduct->getId();
                break;
            case 'catalog_category_view':
                $registry = $this->objectManager->get('\Magento\Framework\Registry');
                $currentCategory = $registry->registry('current_category');
                $data['page']['type'] = 'collection';
                $data['page']['id'] = (int)$currentCategory->getId();
                break;
            case 'checkout_onepage_success':
            case 'multishipping_checkout_success':
                $data['page']['type'] = 'post_checkout';
                break;
        }

        // Convert to js snippet
        $data = json_encode($data);
        $snippet = '<script>var _beeketing = JSON.parse(\'' . $data . '\');</script>';

        return $snippet;
    }

    /**
     * Get shop snippet
     *
     * @return string|bool
     */
    public function getShopSnippet()
    {
        $url = $this->getUrl('magento/shop_snippet');
        $result = $this->get($url);

        if (isset($result['snippet'])) {
            return $result['snippet'];
        }

        return false;
    }

    /**
     * Get sign up url
     * @return string
     */
    public function getSignUpUrl()
    {
        /** @var \Magento\Backend\Model\Auth $auth */
        $auth = $this->objectManager->get('\Magento\Backend\Model\Auth');
        $email = $auth->getUser()->getEmail();
        $domain = $this->helper->getShopDomain();

        return $this->getPlatformUrl('registration/account', array(
            'display' => 'popup',
            'domain' => $domain,
            'platform' => Constant::PLATFORM,
            'email' => $email,
        ));
    }

    /**
     * Get sign in url
     * @return string
     */
    public function getSignInUrl()
    {
        $domain = $this->helper->getShopDomain();

        return $this->getPlatformUrl('sign-in', array(
            'display' => 'popup',
            'platform' => Constant::PLATFORM,
            'domain' => $domain,
        ));
    }

    /**
     * Get oauth sign in url
     * @return string
     */
    public function getOAuthSignInUrl()
    {
        return $this->getPlatformUrl('oauth/magento/' . $this->appCode, array(
            'domain' => $this->helper->getShopDomain(),
            'absolute_path' => $this->helper->getShopAbsolutePath(),
            'access_token' => $this->settingHelper->getSettings(Setting::SETTING_ACCESS_TOKEN),
            'signature' => $this->settingHelper->getSettings(Setting::SETTING_API_KEY),
            'timestamp' => time(),
            'shop_id' => $this->settingHelper->getSettings(Setting::SETTING_SHOP_ID),
        ));
    }

    /**
     * Get platform url
     *
     * @param $path
     * @param array $params
     * @return string
     */
    private function getPlatformUrl($path, $params = array())
    {
        $url = $this->beeketingPath . '/' . $path;

        if ($params) {
            $url .= '?' . http_build_query($params, '', '&');
        }

        return $url;
    }

    /**
     * Get api urls
     *
     * @return array
     */
    public function getApiUrls()
    {
        return array(
            'login_shop' => $this->getLoginShopUrl(),
            'sign_up' => $this->getSignUpUrl(),
            'sign_in' => $this->getSignInUrl(),
        );
    }

    /**
     * Uninstall app
     *
     * @return bool|mixed
     */
    public function uninstallApp()
    {
        $result = $this->sendRequestWebhook(Webhook::UNINSTALL);
        $this->settingHelper->deleteSettings();

        return $result;
    }

    /**
     * Get endpoint
     *
     * @param null $url
     * @return string
     */
    private function getEndpoint($url = null)
    {
        if (!$url) {
            $url = $this->beeketingApi;
        }

        return $url . '/rest-api/v1/';
    }

    /**
     * Send api request
     *
     * @param $type
     * @param $url
     * @param $content
     * @param array $headers
     * @return array|mixed
     */
    private function sendRequest($type, $url, $content, $headers = array())
    {
        if (!$this->apiKey) {
            return array();
        }

        $headers = array_merge(array(
            'Content-Type' => 'application/json',
            'X-Beeketing-Key' => $this->apiKey,
        ), $headers);

        // Resubmit request when timeout
        $result = null;
        $continue = true;
        $triedTime = 0;

        while ($continue) {
            try {
                $triedTime++;

                if ($type == RequestInterface::METHOD_PUT) {
                    $result = $this->browser->put($url, $headers, json_encode($content));
                } elseif ($type == RequestInterface::METHOD_GET) {
                    $result = $this->browser->get($url, $headers);
                } elseif ($type == RequestInterface::METHOD_DELETE) {
                    $result = $this->browser->delete($url);
                } else {
                    $result = $this->browser->post($url, $headers, json_encode($content));
                }

                $status = $result->getStatusCode();
                $isSuccess = preg_match('/2[0-9]{2}/', $status);

                // Stop query at here
                if ($isSuccess) {
                    $continue = false;
                }
            } catch (ClientException $e) {

            }

            if ($triedTime > self::MAXIMUM_RETRY_TIME) {
                // Reaching allowed maximum retried time
                return $this->responseError('Failed to send request to: ' . $url . ' after tried with ' . $triedTime . ' times');
            }
        }

        $arrayContent = json_decode($result->getContent(), true);
        return $arrayContent;
    }

    /**
     * Response error
     *
     * @param $message
     * @return array
     */
    private function responseError($message)
    {
        return array(
            'errors' => $message,
        );
    }

    /**
     * Send request webhook
     *
     * @param $topic
     * @param $content
     * @param array $headers
     * @return array|mixed
     */
    public function sendRequestWebhook($topic, $content = array(), $headers = array())
    {
        $shopId = $this->settingHelper->getSettings(Setting::SETTING_SHOP_ID);
        if (!$shopId) {
            return false;
        }

        $headers = array_merge(array(
            'X-Beeketing-Topic' => $topic,
        ), $headers);

        $url = $this->beeketingApi . '/webhook/callback/' . Constant::PLATFORM . '/' .
            $this->appCode . '/' . $shopId;

        return $this->post($url, $content, $headers);
    }

    /**
     * Send get request
     *
     * @param $url
     * @param array $params
     * @return array|bool
     */
    protected function get($url, $params = array())
    {
        return $this->sendRequest(self::METHOD_GET, $url, $params);
    }

    /**
     * Send post request
     *
     * @param $url
     * @param array $params
     * @param array $headers
     * @return array|bool
     */
    protected function post($url, $params = array(), $headers = array())
    {
        return $this->sendRequest(self::METHOD_POST, $url, $params, $headers);
    }

    /**
     * Send put request
     *
     * @param $url
     * @param array $params
     * @param array $headers
     * @return array|bool
     */
    protected function put($url, $params = array(), $headers = array())
    {
        return $this->sendRequest(self::METHOD_PUT, $url, $params, $headers);
    }

    /**
     * Send delete request
     *
     * @param $url
     * @param array $param
     * @param array $headers
     * @return array|bool
     */
    protected function delete($url, $param = array(), $headers = array())
    {
        return $this->sendRequest(self::METHOD_DELETE, $url, $param, $headers);
    }

    /**
     * Get request url
     *
     * @param $path
     * @param array $params
     * @param null $endpoint
     * @param string $ext
     * @return string
     */
    protected function getUrl($path, $params = array(), $endpoint = null, $ext = '.json')
    {
        $url = $this->getEndpoint($endpoint) . $path . $ext;

        if ($params) {
            $url .= '?' . http_build_query($params, '', '&');
        }

        return $url;
    }

    /**
     * Detect domain change
     *
     * @return bool
     */
    public function detectDomainChange()
    {
        $settingSiteUrl = $this->settingHelper->getSettings(Setting::SETTING_SITE_URL);
        $siteUrl = $this->helper->getShopAbsolutePath();
        if ($settingSiteUrl != $siteUrl) {
            $result = $this->updateShopAbsolutePath();

            if (!isset($result['errors'])) {
                $this->settingHelper->updateSettings(Setting::SETTING_SITE_URL, $siteUrl);
                return true;
            }

            return false;
        }
    }
}