<?php

/**
 * Magestore.
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Magestore.com license that is
 * available through the world-wide-web at this URL:
 * http://www.magestore.com/license-agreement.html
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade this extension to newer
 * version in the future.
 *
 * @category    Magestore
 * @package     Magestore_Affiliateplus
 * @copyright   Copyright (c) 2012 Magestore (http://www.magestore.com/)
 * @license     http://www.magestore.com/license-agreement.html
 */
namespace Magestore\Affiliateplus\Block\Account;

/**
 * Class Edit
 * @package Magestore\Affiliateplus\Block\Account
 */
class Edit extends \Magestore\Affiliateplus\Block\AbstractTemplate
{
    protected $_countryCollection;
    /**
     * @return \Magestore\Affiliateplus\Model\Session
     */
    protected function _getSession(){
        return $this->_sessionModel;
    }

    /**
     * @return mixed
     */
    public function customerLoggedIn(){
        return $this->_accountHelper->customerLoggedIn();
    }

    /**
     * @return bool
     */
    public function isLoggedIn(){
        return $this->_getSession()->isLoggedIn();
    }

    /**
     * @return \Magento\Customer\Model\Customer
     */
    public function getCustomer(){
        return $this->_sessionCustomer->getCustomer();
    }

    /**
     * @param null $field
     * @return null
     */
    public function getFormData($field=null){
        $formData = $this->_getSession()->getAffiliateFormData();
        if($field){
            return isset($formData[$field]) ? $formData[$field] : null;
        }
        return $formData;
    }

    /**
     * @return $this
     */
    public function unsetFormData(){
        $this->_getSession()->unsetData('affiliate_form_data');
        return $this;
    }

    /**
     * @return mixed
     */
    public function getAccount(){
        return $this->_getSession()->getAccount();
    }

    /**
     * @return mixed
     */
    public function requiredAddress(){
        return $this->_configHelper->getSharingConfig('required_address');
    }

    /**
     * @return mixed
     */
    public function requiredPaypal(){
        return $this->_configHelper->getSharingConfig('required_paypal');
    }

    /**
     * @return mixed
     */
    public function getFormattedAddress(){
        $account = $this->getAccount();
        return $this->_addressFactory->create()->load($account->getAddressId())->format('html');
    }

    /**
     * @return mixed
     */
    public function getAddress() {
        $address = $this->_addressFactory->create();
        $formData = $this->getFormData();
        if(isset($formData['account'])){
            $address->setData($formData['account']);
        } elseif($this->isLoggedIn()){
            $address->load($this->getAccount()->getAddressId());
        } elseif($this->customerLoggedIn()){
            if(!$address->getFirstname())
                $address->setFirstname($this->getCustomer()->getFirstname());
            if(!$address->getLastname())
                $address->setLastname($this->getCustomer()->getLastname());
        }
        return $address;
    }

    /**
     * @return int
     */
    public function customerHasAddresses(){
        return $this->getCustomer()->getAddressesCollection()->getSize();
    }

    /**
     * @param $type
     * @return string
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getAddressesHtmlSelect($type){
        if ($this->customerLoggedIn()){
            $options = [];
            foreach ($this->getCustomer()->getAddresses() as $address) {
                $options[] = [
                    'value'=>$address->getId(),
                    'label'=>$address->format('oneline')
                ];
            }

            $addressId = $this->getAddress()->getId();
            if (empty($addressId)) {
                $address = $this->getCustomer()->getPrimaryBillingAddress();
            }

            $select = $this->_blockFactory->createBlock('Magento\Framework\View\Element\Html\Select')
                ->setName($type.'_address_id')
                ->setId($type.'-address-select')
                ->setClass('address-select')
                ->setExtraParams('onchange=lsRequestTrialNewAddress(this.value);')
                ->setValue($addressId)
                ->setOptions($options);

            $select->addOption('',__('New Address'));

            return $select->getHtml();
        }
        return '';
    }

    /**
     * @param $type
     * @return mixed
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getCountryHtmlSelect($type){
        $countryId = $this->getAddress()->getCountryId();
        if (is_null($countryId)) {
            $countryId = $this->getCountryDefault();
        }
        $select = $this->_blockFactory->createBlock('Magento\Framework\View\Element\Html\Select')
            ->setName($type.'[country_id]')
            ->setId($type.':country_id')
            ->setTitle(__('Country'))
            ->setClass('validate-select')
            ->setValue($countryId)
            ->setOptions($this->getCountryOptions())
            ->setExtraParams(
                'data-validate="{\'validate-select\':true}"')
        ;

        return $select->getHtml();
    }

    protected function getCountryDefault(){
        $countryId = $this->getData('country_id');
        if ($countryId === null) {
            $directoryHelper = $this->_objectManager->create('Magento\Directory\Helper\Data');
            $countryId = $directoryHelper->getDefaultCountry();
        }
        return $countryId;
    }

    /**
     * @return mixed
     */
    public function getRegionCollection(){
        if (!$this->_regionCollection){
            $this->_regionCollection = $this->_regionCollectionFactory->create()
                ->addCountryFilter($this->getAddress()->getCountryId())
                ->load();
        }
        return $this->_regionCollection;
    }

    /**
     * @param $type
     * @return mixed
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getRegionHtmlSelect($type){
        $select = $this->_blockFactory->createBlock('Magento\Framework\View\Element\Html\Select')
            ->setName($type.'[region]')
            ->setId($type.':region')
            ->setTitle(__('State/Province'))
            ->setClass('required-entry validate-state')
            ->setValue($this->getAddress()->getRegionId())
            ->setOptions($this->getRegionCollection()->toOptionArray());

        return $select->getHtml();
    }

    /**
     * @return mixed
     */
    public function getCountryCollection(){
        if (!$this->_countryCollection) {
            $this->_countryCollection = $this->_countryCollectionFactory->create()
                ->loadByStore();
        }
        return $this->_countryCollection;
    }

    /**
     * @return bool|mixed
     */
    public function getCountryOptions(){
        $options    = false;
        $cacheId    = 'DIRECTORY_COUNTRY_SELECT_STORE_' . $this->_storeManager->getStore()->getCode();
        $cache = $this->_configCacheType->load($cacheId);
        if ($cache) {
            $options = unserialize($cache);
        }
        else  {
            $options = $this->getCountryCollection()->toOptionArray();
            $this->_configCacheType->save(serialize($options), $cacheId);
        }
        return $options;
    }

    /**
     * @param string $html
     * @return string
     */
    protected function _afterToHtml($html){
        $this->unsetFormData();
        return parent::_afterToHtml($html);
    }

    /**
     * @return string
     */
    public function getCheckCustomerEmailUrl(){
        return $this->getUrl('affiliateplus/account/checkemailregister');
    }

    /**
     * @return string
     */
    public function getCheckReferredEmailUrl(){
        return $this->getUrl('affiliateplus/account/checkreferredemail');
    }
    /**
     * @return int
     */
    public function getStoreId()
    {
        return $this->_storeManager->getStore()->getId();
    }

    /**
     * @return \Magento\Framework\View\Element\BlockFactory
     */
    public function getBlockFactory()
    {
        return $this->_blockFactory;
    }

    /**
     * @return mixed
     */
    public function getConditionConfig()
    {
        return $this->_accountHelper->getConfig('affiliateplus/account/terms_and_conditions');
    }

    /**
     * @return \Magento\Directory\Helper\Data
     */
    public function getHelperDirectory()
    {
        return $this->_helperDirectory;
    }

    /**
     * @param $moduleName
     * @return bool
     */
    public function isModuleOutputEnabled($moduleName){
        return $this->_dataHelper->isModuleOutputEnabled($moduleName);
    }

    /**
     * Get config value.
     *
     * @param string $path
     * @return string|null
     */
    public function getConfig($path)
    {
        return $this->_scopeConfig->getValue($path, \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
    }
    /**
     * @return array
     */
    public function getMethodArr() {
        $methodpayment = [];
        if ($this->paypalActive())
            $methodpayment['paypal'] = 'PayPal';
        if ($this->moneybookerActive())
            $methodpayment['moneybooker'] = 'MoneyBookers';
        return $methodpayment;
    }

    /**
     * @return mixed
     */
    public function getRecurringPayment() {
        return $this->getAccount()->getRecurringPayment();
    }

    /**
     * @return mixed
     */
    public function getRecurringMethod() {
        return $this->getAccount()->getRecurringMethod();
    }

    /**
     * @return mixed
     */
    public function getMoneybookerEmail() {
        return $this->getAccount()->getMoneybookerEmail();
    }

    /**
     * @return mixed
     */
    public function moneybookerActive() {
        return $this->getConfig('affiliateplus_payment/moneybooker/active');
    }

    /**
     * @return mixed
     */
    public function paypalActive() {
        return $this->getConfig('affiliateplus_payment/paypal/active');
    }

    /**
     * @return bool
     */
    public function moneybookerDisplay() {
        if (!$this->paypalActive()){
            return true;
        }
        if (($this->moneybookerActive() && ($this->getRecurringMethod() == 'moneybooker'))){
            return true;
        }
    }

    /**
     * @return \Magento\Store\Model\StoreManagerInterface
     */
    public function getStoreManager()
    {
        return $this->_storeManager;
    }
    /**
     * @return \Magento\Customer\Helper\Address
     */
    public function getHelperCustomerAddress()
    {
        return $this->_objectManager->get('Magento\Customer\Helper\Address');
    }

}