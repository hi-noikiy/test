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
namespace Magestore\Affiliateplus\Block\Payment\Offline;


/**
 * Class Request
 * @package Magestore\Affiliateplus\Block\Payment
 */
class Form extends \Magestore\Affiliateplus\Block\Payment\Form
{
    /**
     * @var
     */
    protected $_countryCollection;
    /**
     * @return $this
     */
    public function _prepareLayout(){
        parent::_prepareLayout();
        $this->setTemplate('Magestore_Affiliateplus::payment/offline/form.phtml');
        return $this;
    }

    /**
     * @return \Magento\Framework\App\RequestInterface
     */
    public function getPostData(){
        $data = $this->_objectManager->get('Magento\Framework\App\RequestInterface')->getParams();
        return $data;
    }

    /**
     * @return mixed
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
     * @return mixed
     */
    public function isLoggedIn(){
        return $this->_getSession()->isLoggedIn();
    }

    /**
     * @return mixed
     */
    public function getCustomer(){
        return $this->_sessionCustomer->getCustomer();
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
    public function getAddress() {
        $address = $this->_objectManager->create('Magento\Customer\Model\Address');
        $data = $this->getDataPost();
        if($this->isShowForm() && isset($data['account'])){
            $address->setData($data['account']);
        }else{
            if($this->isLoggedIn()){
                $address->load($this->getAccount()->getAddressId());
            } elseif($this->customerLoggedIn()){
                if(!$address->getFirstname())
                    $address->setFirstname($this->getCustomer()->getFirstname());
                if(!$address->getLastname())
                    $address->setLastname($this->getCustomer()->getLastname());
            }
        }
        return $address;
    }

    /**
     * @return mixed
     */
    public function customerHasAddresses(){
        return $this->getCustomer()->getAddressesCollection()->getSize();
    }

    /**
     * @return mixed
     */
    public function getDataPost(){
        $data = $this->getPostData();
        return $data;
    }

    /**
     * @return bool
     */
    public function isShowForm(){
        $data = $this->getDataPost();
        if(isset($data['account_address_id']))
            if(!$data['account_address_id'])
                return true;
        return false;
    }

    /**
     * @param $type
     * @return string
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getAddressesHtmlSelect($type){
        $data = $this->getDataPost();

        if ($this->customerLoggedIn()){
            $options = array();
            foreach ($this->getCustomer()->getAddresses() as $address) {
                $data['account']['address_id'] = $options[] = [
                    'value'=>$address->getId(),
                    'label'=>$address->format('oneline')
                ];
            }
            if(isset($data['account_address_id'])){
                $addressId = $data['account_address_id'];
            }else{
                $addressId = $this->getAddress()->getId();
                if (empty($addressId)) {
                    $address = $this->getCustomer()->getPrimaryBillingAddress();
                }
            }
            if($addressId)
                $this->setAddressId($addressId);

            $select = $this->_blockFactory->createBlock('Magento\Framework\View\Element\Html\Select')
                ->setName($type.'_address_id')
                ->setId($type.'-address-select')
                ->setClass('address-select')
                ->setExtraParams('onchange=lsRequestTrialNewAddress(this.value);')
                ->setValue($addressId)
                ->setOptions($options);
            $select->addOption('', __('New Address'));
            return $select->getHtml();
        }
        return '';
    }

    /**
     * @return bool
     */
    public function addressIsVerified(){
        $addressId = $this->getAddressId();
        $account = $this->_sessionModel->getAccount();
        $verifyCollection = $this->_objectManager->create('Magestore\Affiliateplus\Model\Payment\Verify')
            ->getCollection()
            ->addFieldToFilter('account_id',$account->getId())
            ->addFieldToFilter('payment_method','offline')
            ->addFieldToFilter('field',$addressId)
            ->addFieldToFilter('verified','1')
        ;
        if($verifyCollection->getSize())
            return true;
        return false;
    }

    /**
     * @param $type
     * @return mixed
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getCountryHtmlSelect($type){
        $countryId = $this->getAddress()->getCountryId();
        if (is_null($countryId)) {
            $countryId = $this->_dataHelper->getConfig('general/country/default');
        }
        $select = $this->_blockFactory->createBlock('Magento\Framework\View\Element\Html\Select')
            ->setName($type.'[country_id]')
            ->setId($type.':country_id')
            ->setTitle(__('Country'))
            ->setClass('validate-select')
            ->setValue($countryId)
            ->setOptions($this->getCountryOptions());

        return $select->getHtml();
    }

    /**
     * @return mixed
     */
    public function getRegionCollection(){
        if (!$this->_regionCollection){
            $this->_regionCollection = $this->_objectManager->create('Magento\Directory\Model\Region')->getResourceCollection()
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
            $this->_countryCollection = $this->_objectManager->create('Magento\Directory\Model\Country')->getResourceCollection()
                ->loadByStore();
        }
        return $this->_countryCollection;
    }

    /**
     * @return bool|mixed
     */
    public function getCountryOptions(){
        $options    = false;
            $cacheId    = 'DIRECTORY_COUNTRY_SELECT_STORE_' . $this->getStoreManager()->getStore()->getCode();
            $cacheTags  = ['config'];
            if ($optionsCache = $this->_configCacheType->load($cacheId)) {
                $options = unserialize($optionsCache);
            }

        if ($options == false) {
            $options = $this->getCountryCollection()->toOptionArray();
            $this->_configCacheType->save(serialize($options), $cacheId, $cacheTags);
        }
        return $options;
    }

    /**
     * @return \Magestore\Affiliateplus\Helper\Payment
     */
    public function getPaymentHelper(){
        return $this->_objectManager->get('Magestore\Affiliateplus\Helper\Payment');
    }

    /**
     * @return \Magento\Customer\Helper\Address
     */
    public function getHelperCustomerAddress()
    {
        return $this->_objectManager->get('Magento\Customer\Helper\Address');
    }

    /**
     * @return \Magento\Directory\Helper\Data
     */
    public function getHelperDirectory()
    {
        return $this->_objectManager->get('Magento\Directory\Helper\Data');
    }

}
