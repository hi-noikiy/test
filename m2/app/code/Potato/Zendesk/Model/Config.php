<?php
namespace Potato\Zendesk\Model;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Store\Model\ScopeInterface;
use Magento\Store\Model\Website;
use Magento\Store\Model\Store;

/**
 * Class Config
 */
class Config
{
    const SUPPORT_ORDER_SECTION_PATH = 'potato_zendesk/features/order_section';
    const SUPPORT_CUSTOMER_SECTION_PATH = 'potato_zendesk/features/customer_section';

    const ZENDESK_CONFIG_API_AGENT_TOKEN_PATH = 'potato_zendesk/account/zendesk_token';
    const ZENDESK_CONFIG_API_AGENT_EMAIL_PATH = 'potato_zendesk/account/agent_email';
    const ZENDESK_CONFIG_API_AGENT_DOMAIN_PATH = 'potato_zendesk/account/domain';

    const ZENDESK_CONFIG_API_TOKEN_PATH = 'potato_zendesk/general/token';

    const ZENDESK_CONFIG_IS_SEPARATE_WEBSITE_PATH = 'potato_zendesk/general/separate_website';
    const ZENDESK_CONFIG_IS_SEPARATE_STORE_VIEW_PATH = 'potato_zendesk/general/separate_store';
    
    /** @var ScopeConfigInterface  */
    protected $scopeConfig;

    /**
     * Config constructor.
     * @param ScopeConfigInterface $scopeConfig
     */
    public function __construct(
        ScopeConfigInterface $scopeConfig
    ) {
        $this->scopeConfig = $scopeConfig;
    }

    /**
     * @return bool
     */
    public function isSupportOrderSection()
    {
        return (bool)$this->scopeConfig->getValue(
            self::SUPPORT_ORDER_SECTION_PATH
        );
    }

    /**
     * @return bool
     */
    public function isSupportCustomerSection()
    {
        return (bool)$this->scopeConfig->getValue(
            self::SUPPORT_CUSTOMER_SECTION_PATH
        );
    }

    /**
     * @param null|integer|Store $store
     * @return string
     */
    public function getSubdomain($store = null)
    {
        return $this->scopeConfig->getValue(
            self::ZENDESK_CONFIG_API_AGENT_DOMAIN_PATH,
            ScopeInterface::SCOPE_STORE,
            $store
        );
    }

    /**
     * @param null|integer|Store $store
     * @return string
     */
    public function getAgentEmail($store = null)
    {
        return $this->scopeConfig->getValue(
            self::ZENDESK_CONFIG_API_AGENT_EMAIL_PATH,
            ScopeInterface::SCOPE_STORE,
            $store
        );
    }

    /**
     * @param null|integer|Store $store
     * @return string
     */
    public function getAgentToken($store = null)
    {
        return $this->scopeConfig->getValue(
            self::ZENDESK_CONFIG_API_AGENT_TOKEN_PATH,
            ScopeInterface::SCOPE_STORE,
            $store
        );
    }

    /**
     * @param null|integer|Store $store
     * @return string
     */
    public function getApiTokenForStore($store = null)
    {
        return $this->scopeConfig->getValue(
            self::ZENDESK_CONFIG_API_TOKEN_PATH,
            ScopeInterface::SCOPE_STORE,
            $store
        );
    }

    /**
     * @param null|integer|Website $website
     * @return string
     */
    public function getApiTokenForWebsite($website = null)
    {
        return $this->scopeConfig->getValue(
            self::ZENDESK_CONFIG_API_TOKEN_PATH,
            ScopeInterface::SCOPE_WEBSITE,
            $website
        );
    }

    /**
     * @return string
     */
    public function getApiTokenForDefault()
    {
        return $this->scopeConfig->getValue(
            self::ZENDESK_CONFIG_API_TOKEN_PATH
        );
    }

    /**
     * @param null|integer|Store $store
     * @return bool
     */
    public function isSeparateInfoForWebsite($store = null)
    {
        return (bool)$this->scopeConfig->getValue(
            self::ZENDESK_CONFIG_IS_SEPARATE_WEBSITE_PATH,
            ScopeInterface::SCOPE_STORE,
            $store
        );
    }

    /**
     * @param null|integer|Store $store
     * @return bool
     */
    public function isSeparateInfoForStoreView($store = null)
    {
        $isSeparateForWebsite = $this->isSeparateInfoForWebsite($store);
        $isSeparateForStoreView = (bool)$this->scopeConfig->getValue(
            self::ZENDESK_CONFIG_IS_SEPARATE_STORE_VIEW_PATH,
            ScopeInterface::SCOPE_STORE,
            $store
        );
        return $isSeparateForWebsite && $isSeparateForStoreView;
    }
}
