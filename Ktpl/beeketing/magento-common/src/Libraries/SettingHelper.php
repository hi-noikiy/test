<?php
/**
 * Created by PhpStorm.
 * User: tungquach
 * Date: 30/03/2017
 * Time: 11:35
 */

namespace Beeketing\MagentoCommon\Libraries;


use Beeketing\MagentoCommon\Data\AppCodes;
use Beeketing\MagentoCommon\Data\AppSettingKeys;
use Beeketing\MagentoCommon\Data\Setting;
use Magento\Store\Model\ScopeInterface;

class SettingHelper
{
    /**
     * @var \Magento\Config\Model\ResourceModel\Config
     */
    private $resourceConfig;

    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    private $scopeConfig;

    /**
     * @var \Magento\Framework\App\CacheInterface
     */
    private $cache;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    private $storeManager;

    /**
     * @var string
     */
    private $appSettingKey;

    /**
     * @var string
     */
    private $storeId = null;

    /**
     * @var array
     */
    private static $settings = array();

    /**
     * @var SettingHelper
     */
    private static $instance = null;

    /**
     * SettingHelper constructor.
     */
    public function __construct()
    {
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $this->resourceConfig = $objectManager->get('\Magento\Config\Model\ResourceModel\Config');
        $this->scopeConfig = $objectManager->get('\Magento\Framework\App\Config\ScopeConfigInterface');
        $this->cache = $objectManager->get('\Magento\Framework\App\CacheInterface');
        $this->storeManager = $objectManager->get('\Magento\Store\Model\StoreManagerInterface');
    }

    /**
     * Set singleton instance
     *
     * @param $instance
     * @return SettingHelper
     */
    public static function setInstance($instance) {
        self::$instance = $instance;
        // Return instance of class
        return self::$instance;
    }

    /**
     * Singleton instance
     *
     * @return SettingHelper
     */
    public static function getInstance() {
        // Check to see if an instance has already
        // been created
        if (is_null(self::$instance)) {
            // If not, return a new instance
            self::$instance = new self();
            return self::$instance;
        } else {
            // If so, return the previously created
            // instance
            return self::$instance;
        }
    }

    /**
     * Set app setting key
     *
     * @return string
     */
    public function getAppSettingKey()
    {
        return $this->appSettingKey;
    }

    /**
     * Set app setting key
     *
     * @param $appSettingKey
     */
    public function setAppSettingKey($appSettingKey)
    {
        $this->appSettingKey = $appSettingKey;
    }

    /**
     * Get store id
     *
     * @return string
     */
    public function getStoreId()
    {
        return $this->storeId;
    }

    /**
     * Set store id
     *
     * @param $storeId
     */
    public function setStoreId($storeId)
    {
        $this->storeId = $storeId;
    }

    /**
     * App setting keys
     *
     * @return array
     */
    public static function settingKeys()
    {
        return array(
            AppCodes::BETTERCOUPONBOX => AppSettingKeys::BETTERCOUPONBOX_KEY,
            AppCodes::BOOSTSALES => AppSettingKeys::BOOSTSALES_KEY,
            AppCodes::CHECKOUTBOOST => AppSettingKeys::CHECKOUTBOOST_KEY,
            AppCodes::HAPPYEMAIL => AppSettingKeys::HAPPYEMAIL_KEY,
            AppCodes::MAILBOT => AppSettingKeys::MAILBOT_KEY,
            AppCodes::PERSONALIZEDRECOMMENDATION => AppSettingKeys::PERSONALIZEDRECOMMENDATION_KEY,
            AppCodes::QUICKFACEBOOKCHAT => AppSettingKeys::QUICKFACEBOOKCHAT_KEY,
            AppCodes::SALESPOP => AppSettingKeys::SALESPOP_KEY,
        );
    }

    /**
     * Switch settings
     *
     * @param $appCode
     */
    public function switchSettings($appCode)
    {
        $settingKeys = self::settingKeys();
        if (isset($settingKeys[$appCode])) {
            $settingKey = $settingKeys[$appCode];
            $this->appSettingKey = $settingKey;
        }
    }

    /**
     * Get settings
     *
     * @param null $key
     * @param null $default
     * @return mixed
     */
    public function getSettings($key = null, $default = null)
    {
        $settings = isset(self::$settings[$this->appSettingKey]) ? self::$settings[$this->appSettingKey] : array();
        if (!$settings) {
            $storeId = $this->getStoreId();
            $settings = $this->scopeConfig->getValue($this->appSettingKey, ScopeInterface::SCOPE_STORE, $storeId);
            $settings = $settings ? unserialize($settings) : array();
        }

        // Get setting by key
        if ($key) {
            if (isset($settings[$key])) {
                return $settings[$key];
            }

            return $default;
        }

        return $settings;
    }

    /**
     * Update settings
     *
     * @param $key
     * @param $value
     * @return array|mixed
     */
    public function updateSettings($key, $value)
    {
        $settings = isset(self::$settings[$this->appSettingKey]) ? self::$settings[$this->appSettingKey] : array();
        if (!$settings) {
            $settings = $this->getSettings();
        }
        $storeId = $this->getCurrentStoreId();
        $settings[$key] = $value;
        self::$settings[$this->appSettingKey] = $settings;
        $this->resourceConfig->saveConfig($this->appSettingKey, serialize($settings), ScopeInterface::SCOPE_STORES, $storeId);

        // Clean cache
        $this->cache->clean([\Magento\Framework\App\Config::CACHE_TAG]);

        return $settings;
    }

    /**
     * Delete settings
     */
    public function deleteSettings()
    {
        $storeId = $this->getCurrentStoreId();
        $this->resourceConfig->deleteConfig($this->appSettingKey, ScopeInterface::SCOPE_STORES, $storeId);
    }

    /**
     * Get store id
     *
     * @return int
     */
    public function getCurrentStoreId()
    {
        $settingStoreId = $this->getSettings(Setting::SETTING_STORE_ID);
        $storeId = $settingStoreId ? $settingStoreId : $this->storeManager->getStore()->getId();
        return $storeId;
    }
}