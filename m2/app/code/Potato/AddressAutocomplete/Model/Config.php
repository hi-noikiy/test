<?php
namespace Potato\AddressAutocomplete\Model;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Store\Model\ScopeInterface;
use Magento\Framework\Module\Manager;
use Magento\Framework\App\RequestInterface;
use Magento\Customer\Model\Session as CustomerSession;
use Potato\AddressAutocomplete\Model\Source\Name\Type as NameType;

/**
 * Class Config
 */
class Config
{
    const GENERAL_IS_ENABLED                    = 'po_addressautocomplete/general/is_enabled';

    const GOOGLE_PLACES_API_KEY                 = 'po_addressautocomplete/google_places/api_key';
    const GOOGLE_PLACES_HIDE_LOGO               = 'po_addressautocomplete/google_places/hide_logo';
    const GOOGLE_PLACES_USE_BROWSER_GEOLOCATION = 'po_addressautocomplete/google_places/use_browser_geolocation';
    const GOOGLE_PLACES_USE_COUNTRY_RESTRICTION = 'po_addressautocomplete/google_places/use_country_restriction';

    const ADDRESS_COMPONENT_IS_STREET_COMBINED  = 'po_addressautocomplete/address_component/is_street_combined';
    const ADDRESS_COMPONENT_STREET1             = 'po_addressautocomplete/address_component/street1';
    const ADDRESS_COMPONENT_STREET2             = 'po_addressautocomplete/address_component/street2';
    const ADDRESS_COMPONENT_REGION              = 'po_addressautocomplete/address_component/region';
    const ADDRESS_COMPONENT_POSTCODE            = 'po_addressautocomplete/address_component/postcode';
    const ADDRESS_COMPONENT_CITY                = 'po_addressautocomplete/address_component/city';


    /** @var ScopeConfigInterface  */
    protected $scopeConfig;

    /** @var StoreManagerInterface  */
    protected $storeManager;

    /** @var Manager  */
    protected $moduleManager;

    /** @var RequestInterface  */
    protected $request;

    /** @var CustomerSession  */
    protected $customerSession;

    /**
     * Config constructor.
     * @param ScopeConfigInterface $scopeConfig
     * @param StoreManagerInterface $storeManager
     * @param Manager $moduleManager
     * @param RequestInterface $request
     * @param CustomerSession $customerSession
     */
    public function __construct(
        ScopeConfigInterface $scopeConfig,
        StoreManagerInterface $storeManager,
        Manager $moduleManager,
        RequestInterface $request,
        CustomerSession $customerSession
    ) {
        $this->scopeConfig = $scopeConfig;
        $this->storeManager = $storeManager;
        $this->moduleManager = $moduleManager;
        $this->request = $request;
        $this->customerSession = $customerSession;
    }

    /**
     * @param int|null $storeId
     * @return bool
     */
    public function isEnabled($storeId = null)
    {
        if (null === $storeId) {
            $storeId = $this->storeManager->getStore()->getId();
        }
        $isEnabled =  !!$this->scopeConfig->getValue(
            self::GENERAL_IS_ENABLED,
            ScopeInterface::SCOPE_STORE,
            $storeId
        );
        $isModuleEnabled = $this->moduleManager->isEnabled('Potato_AddressAutocomplete');
        $isModuleOutputEnabled = $this->moduleManager->isOutputEnabled('Potato_AddressAutocomplete');
        return $isEnabled && $isModuleEnabled && $isModuleOutputEnabled;
    }

    /**
     * @param null|int $storeId
     * @return string
     */
    public function getGooglePlacesApiKey($storeId = null)
    {
        if (null === $storeId) {
            $storeId = $this->storeManager->getStore()->getId();
        }
        return $this->scopeConfig->getValue(
            self::GOOGLE_PLACES_API_KEY,
            ScopeInterface::SCOPE_STORE,
            $storeId
        );
    }

    /**
     * @param null|int $storeId
     * @return bool
     */
    public function isGoogleHideLogo($storeId = null)
    {
        if (null === $storeId) {
            $storeId = $this->storeManager->getStore()->getId();
        }
        return !!$this->scopeConfig->getValue(
            self::GOOGLE_PLACES_HIDE_LOGO,
            ScopeInterface::SCOPE_STORE,
            $storeId
        );
    }

    /**
     * @param null|int $storeId
     * @return bool
     */
    public function isGoogleUseBrowserGeo($storeId = null)
    {
        if (null === $storeId) {
            $storeId = $this->storeManager->getStore()->getId();
        }
        return !!$this->scopeConfig->getValue(
            self::GOOGLE_PLACES_USE_BROWSER_GEOLOCATION,
            ScopeInterface::SCOPE_STORE,
            $storeId
        );
    }

    /**
     * @param null|int $storeId
     * @return bool
     */
    public function isGoogleUseCountryRestriction($storeId = null)
    {
        if (null === $storeId) {
            $storeId = $this->storeManager->getStore()->getId();
        }
        return !!$this->scopeConfig->getValue(
            self::GOOGLE_PLACES_USE_COUNTRY_RESTRICTION,
            ScopeInterface::SCOPE_STORE,
            $storeId
        );
    }

    /**
     * @param null|int $storeId
     * @return bool
     */
    public function isAddressComponentStreetCombined($storeId = null)
    {
        if (null === $storeId) {
            $storeId = $this->storeManager->getStore()->getId();
        }
        return !!$this->scopeConfig->getValue(
            self::ADDRESS_COMPONENT_IS_STREET_COMBINED,
            ScopeInterface::SCOPE_STORE,
            $storeId
        );
    }

    /**
     * @param null|int $storeId
     * @return string
     */
    public function getAddressComponentStreetOne($storeId = null)
    {
        if (null === $storeId) {
            $storeId = $this->storeManager->getStore()->getId();
        }
        $value = $this->scopeConfig->getValue(
            self::ADDRESS_COMPONENT_STREET1,
            ScopeInterface::SCOPE_STORE,
            $storeId
        );
        if ($value) {
            return $value;
        }
        return NameType::SHORT_NAME_CODE;
    }

    /**
     * @param null|int $storeId
     * @return string
     */
    public function getAddressComponentStreetTwo($storeId = null)
    {
        if (null === $storeId) {
            $storeId = $this->storeManager->getStore()->getId();
        }
        $value = $this->scopeConfig->getValue(
            self::ADDRESS_COMPONENT_STREET2,
            ScopeInterface::SCOPE_STORE,
            $storeId
        );
        if ($value) {
            return $value;
        }
        return NameType::SHORT_NAME_CODE;
    }

    /**
     * @param null|int $storeId
     * @return string
     */
    public function getAddressComponentRegion($storeId = null)
    {
        if (null === $storeId) {
            $storeId = $this->storeManager->getStore()->getId();
        }
        $value = $this->scopeConfig->getValue(
            self::ADDRESS_COMPONENT_REGION,
            ScopeInterface::SCOPE_STORE,
            $storeId
        );
        if ($value) {
            return $value;
        }
        return NameType::LONG_NAME_CODE;
    }

    /**
     * @param null|int $storeId
     * @return string
     */
    public function getAddressComponentPostcode($storeId = null)
    {
        if (null === $storeId) {
            $storeId = $this->storeManager->getStore()->getId();
        }
        $value = $this->scopeConfig->getValue(
            self::ADDRESS_COMPONENT_POSTCODE,
            ScopeInterface::SCOPE_STORE,
            $storeId
        );
        if ($value) {
            return $value;
        }
        return NameType::LONG_NAME_CODE;
    }

    /**
     * @param null|int $storeId
     * @return string
     */
    public function getAddressComponentCity($storeId = null)
    {
        if (null === $storeId) {
            $storeId = $this->storeManager->getStore()->getId();
        }
        $value = $this->scopeConfig->getValue(
            self::ADDRESS_COMPONENT_CITY,
            ScopeInterface::SCOPE_STORE,
            $storeId
        );
        if ($value) {
            return $value;
        }
        return NameType::LONG_NAME_CODE;
    }

    /**
     * @param null $storeId
     * @return string
     */
    public function getLocaleCode($storeId = null)
    {
        return $this->storeManager->getStore($storeId)->getLocaleCode();
    }

    /**
     * @return array
     */
    public function getAddressIdList()
    {
        $customer = $this->customerSession->getCustomer();
        if (!$customer || !$customer->getId()) {
            return [];
        }
        return $customer->getAddressesCollection()->getAllIds();
    }

}