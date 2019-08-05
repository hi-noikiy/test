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
namespace Magestore\Affiliateplus\Model;
use Magento\Framework\App\Area;
/**
 * Model Account
 */
class Account extends AbtractModel
{

    const ACCOUNT_ENABLED = '1';
    const ACCOUNT_DISABLED = '2';
    const ACCOUNT_APPROVED_YES = '1';
    const ACCOUNT_APPROVED_NO = '2';
    /**
     * Email path
     */
    const XML_PATH_ADMIN_EMAIL_IDENTITY = 'trans_email/ident_general';
    const XML_PATH_ADMIN_SALES_EMAIL_IDENTITY = 'trans_email/ident_sales';
    const XML_PATH_NEW_ACCOUNT_EMAIL = 'affiliateplus/email/new_account_email_template';
    const XML_PATH_APPROVED_ACCOUNT_EMAIL = 'affiliateplus/email/approved_account_email_template';
    const XML_PATH_SENT_EMAIL_TO_SALES_NEW_ACCOUNT = 'affiliateplus/email/is_sent_to_sales_new_account';
    const XML_PATH_SALES_ACCOUNT_TEMPLATE = 'affiliateplus/email/new_account_sales_email_template';
    /**
     * @var null
     */
    protected $_storeViewId = null;
    /**
     * @var bool
     */
    protected $_balance_is_global = false;
    /**
     * @var string
     */
    protected $_eventPrefix = 'affiliateplus_account';
    /**
     * @var string
     */
    protected $_eventObject = 'affiliateplus_account';

    /**
     * Model construct that should be used for object initialization
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('Magestore\Affiliateplus\Model\ResourceModel\Account');
    }

    /**
     * @return int
     */
    public function getStoreViewId()
    {
        return $this->_storeViewId;
    }

    /**
     * @param $storeViewId
     * @return $this
     */
    public function setStoreViewId($storeViewId)
    {
        $this->_storeViewId = $storeViewId;
        return $this;
    }

    /**
     * Generate Indentify Code
     *
     * @return string
     */
    public function generateIdentifyCode() {
        $i = 0;
        do {
            $code = md5($this->getCustomerEmail() . $i);
            $collection = $this->getCollection()
                ->addFieldToFilter('identify_code', $code);
            $i++;
        } while (count($collection));

        return $code;
    }

    /**
     * @param $customerId
     * @return $this
     */
    public function loadByCustomerId($customerId) {
        return $this->load($customerId, 'customer_id');
    }

    /**
     * @return bool
     */
    public function isEnabled() {
        return ($this->getStatus() == 1) ? true : false;
    }
    /**
     * @return mixed
     */
    public function getStoreAttributes() {

        $storeAttribute = new \Magento\Framework\DataObject(
            [
                'store_attribute' => [
                    'status',
                    'approved',
                ]
            ]
        );

        $this->_eventManager->dispatch($this->_eventPrefix . '_get_store_attributes',[$this->_eventObject => $this, 'attributes' => $storeAttribute]);

        return $storeAttribute->getStoreAttribute();
    }

    /**
     * @return mixed
     */
    public function getBalanceAttributes() {
        $balanceAttribute = new \Magento\Framework\DataObject(
            [
                'balance_attribute' => [
                    'balance',
                    'total_commission_received',
                    'total_paid',
                ]
            ]
        );

        $this->_eventManager->dispatch($this->_eventPrefix . '_get_balance_attributes', [
            $this->_eventObject => $this,
            'attributes' => $balanceAttribute,
            ]
        );

        return $balanceAttribute->getBalanceAttribute();
    }


    /**
     * @param $value
     * @return $this
     */
    public function setBalanceIsGlobal($value) {
        $this->_balance_is_global = $value;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getBalanceIsGlobal() {
        return $this->_balance_is_global;
    }

    /**
     * @param int $id
     * @param null $field
     * @return $this
     */
    public function load($id, $field = null) {
        parent::load($id, $field);

        $this->_eventManager->dispatch($this->_eventPrefix . '_load_store_value_before',$this->_getEventData());

        if ($this->getStoreViewId())
            $this->loadStoreValue($this->getStoreViewId());

        $this->_eventManager->dispatch($this->_eventPrefix . '_load_store_value_after',$this->_getEventData());

        return $this;
    }
    /**
     * @param null $storeId
     * @return $this
     */
    public function loadStoreValue($storeId = null) {
        if (!$storeId)
            $storeId = $this->getStoreViewId();
        if (!$storeId)
            return $this;

        $storeValues = $this->_objectManager->create('Magestore\Affiliateplus\Model\ResourceModel\AccountValue\Collection')
            ->addFieldToFilter('account_id', $this->getId())
            ->addFieldToFilter('store_id', $storeId);

        if ($this->getBalanceIsGlobal())
            $storeValues->addFieldToFilter('attribute_code', ['in' => $this->getStoreAttributes()]);
        else
            $balanceAttributes = $this->getBalanceAttributes();

        $balanceAttributesHasData = array();
        foreach ($storeValues as $value) {
            $balanceAttributesHasData[] = $value->getAttributeCode();
            $this->setData($value->getAttributeCode() . '_in_store', true);
            $this->setData($value->getAttributeCode(), $value->getValue());
        }
        foreach ($this->getStoreAttributes() as $attribute)
            if (!$this->getData($attribute . '_in_store'))
                $this->setData($attribute . '_default', true);
        if (!$this->getBalanceIsGlobal()) {
            $zeroAttributes = array_diff($balanceAttributes, $balanceAttributesHasData);
            foreach ($zeroAttributes as $attributeCode)
                $this->setData($attributeCode . '_in_store', true)
                    ->setData($attributeCode, 0);
            $balanceAttributes = array('balance', 'total_commission_received', 'total_paid');
            foreach ($balanceAttributes as $attributeCode)
                if ($this->getData($attributeCode) == 0)
                    $this->setData($attributeCode, 0.000000000001);
        }
        return $this;
    }

    /**
     * @param null $website
     * @return int
     */
    public function getWebsiteBalance($website = null){
        $storeId = $this->getStoreViewId();
        $scope = $this->_helperConfig->getAccountConfig('balance', $storeId);
        if($scope != 'website')
            return $this->getBalance();
        if(is_null($website))
            $website = $this->_storeManager->getWebsite()->getId();
        $balance = 0;
        $storeIds = $this->_objectManager->create('Magestore\Affiliateplus\Helper\Account')->getStoreIdsByWebsite($website);
        $collection = $this->_objectManager->create('Magestore\Affiliateplus\Model\ResourceModel\AccountValue\Collection')
            ->addFieldToFilter('account_id', $this->getId())
            ->addFieldToFilter('attribute_code', 'balance')
            ->addFieldToFilter('store_id', ['in'=>$storeIds]);
        foreach($collection as $item){
            $balance += $item->getValue();
        }
        return $balance;
    }

    /**
     * @return mixed
     */
    public function beforeSave() {
        if ($this->getStatus() == 1)
            $this->setApproved(1);

        $defaultAccount = $this->_objectManager->create('Magestore\Affiliateplus\Model\Account')->load($this->getId());

        if ($storeId = $this->getStoreViewId()) {
            $storeAttributes = $this->getStoreAttributes();
            foreach ($storeAttributes as $attribute) {
                if ($this->getData($attribute . '_default')) {
                    $this->setData($attribute . '_in_store', false);
                } else {
                    $this->setData($attribute . '_in_store', true);
                    $this->setData($attribute . '_value', $this->getData($attribute));
                }
                if ($defaultAccount->getId())
                    $this->setData($attribute, $defaultAccount->getData($attribute));
            }

            if ($this->getId()) {
                $balanceAttributes = $this->getBalanceAttributes();
                foreach ($balanceAttributes as $attribute) {
                    $attributeValue = $this->_objectManager->create('Magestore\Affiliateplus\Model\AccountValue')
                        ->loadAttributeValue($this->getId(), $storeId, $attribute);
                    if ($delta = ($this->getData($attribute) - $attributeValue->getValue())) {
                        try {
                            $attributeValue->setValue($this->getData($attribute));
                            $attributeValue->save();
                        } catch (\Exception $e) {

                        }
                    }
                    $this->setData($attribute, $defaultAccount->getData($attribute) + $delta);
                }
            }
        } elseif ($this->getId()) {
            if ($delta = ($this->getData('balance') - $defaultAccount->getData('balance'))) {
                $attributeValues = $this->_objectManager->create('Magestore\Affiliateplus\Model\ResourceModel\AccountValue\Collection')
                    ->addFieldToFilter('account_id', $this->getId())
                    ->addFieldToFilter('attribute_code', 'balance');
                $paid = $this->getData('total_paid') - $defaultAccount->getData('total_paid');

                foreach ($attributeValues as $attributeValue) {
                    if (($delta + $attributeValue->getValue()) >= 0) {
                        $attributeValue->setValue($attributeValue->getValue() + $delta);
                        $receivedAtt = $this->_objectManager->create('Magestore\Affiliateplus\Model\AccountValue')
                            ->loadAttributeValue($this->getId(), $attributeValue->getStoreViewId(), 'total_commission_received');
                        $receivedAtt->setValue($receivedAtt->getValue() - $delta)->save();
                        try {
                            $attributeValue->save();
                            if ($paid > 0) {
                                $paidAttribute = $this->_objectManager->create('Magestore\Affiliateplus\Model\AccountValue')
                                    ->loadAttributeValue($this->getId(), $attributeValue->getStoreViewId(), 'total_paid');
                                $paidAttribute->setValue($paidAttribute->getValue() + $paid)->save();
                            }
                        } catch (\Exception $e) {

                        }
                        break;
                    } else {
                        $delta += $attributeValue->getValue();
                        try {
                            if ($paid > 0) {
                                $paidAttribute = $this->_objectManager->create('Magestore\Affiliateplus\Model\AccountValue')
                                    ->loadAttributeValue($this->getId(), $attributeValue->getStoreViewId(), 'total_paid');
                                if ($attributeValue->getValue() >= $paid) {
                                    $paidAttribute->setValue($paidAttribute->getValue() + $paid)->save();
                                    $paid = 0;
                                } else {
                                    $paidAttribute->setValue($paidAttribute->getValue() + $attributeValue->getValue())->save();
                                    $paid -= $attributeValue->getValue();
                                }
                            }
                            $receivedAtt = $this->_objectManager->create('Magestore\Affiliateplus\Model\AccountValue')
                                ->loadAttributeValue($this->getId(), $attributeValue->getStoreViewId(), 'total_commission_received');
                            $receivedAtt->setValue($receivedAtt->getValue() + $attributeValue->getValue())->save();
                            $attributeValue->setValue(0)->save();
                        } catch (\Exception $e) {

                        }
                    }
                }
            }
        }
        return parent::beforeSave();
    }

    /**
     * @return mixed
     */
    public function afterSave() {
        $storeId = $this->getStoreViewId();
        if ($storeId) {
            $storeAttributes = $this->getStoreAttributes();
            foreach ($storeAttributes as $attribute) {
                $attributeValue = $this->_objectManager->create('Magestore\Affiliateplus\Model\AccountValue')
                    ->loadAttributeValue($this->getId(), $storeId, $attribute);
                if ($this->getData($attribute . '_in_store')) {
                    try {
                        $attributeValue->setValue($this->getData($attribute . '_value'))->save();
                    } catch (\Exception $e) {

                    }
                } else if ($attributeValue && $attributeValue->getId()) {
                    try {
                        $attributeValue->delete();
                    } catch (\Exception $e) {

                    }
                }
            }
        }
        return parent::afterSave();
    }

    /**
     * Load Affiliate Account by Customer
     * @param $customer
     * @return $this|Account
     */
    public function loadByCustomer($customer) {
        if ($customer && $customer->getId()){
            return $this->loadByCustomerId($customer->getId());
        }
        return $this;
    }

    /**
     * Load Affiliate account from identify code
     * @param $code
     * @return Account
     */
    public function loadByIdentifyCode($code)
    {
        return $this->load($code, 'identify_code');
    }

    /**
     * get Affiliate link after creating affiliate account
     * @param $url
     * @param $identifyCode
     * @param null $store
     * @return string
     */
    public function getAffiliateLink($url, $identifyCode, $store = null) {
        if (is_null($store)){
            $store = $this->_storeManager->getStore();
        }
        $url = $this->_helperUrl->getUrlLink($url);

        $referParam = $this->_helperUrl->getPersonalUrlParameter();
        if ($this->_helperConfig->getGeneralConfig('url_param_value') == \Magestore\Affiliateplus\Helper\Config::URL_PARAM_VALUE_AFFILIATE_ID){
            $account = $this->loadByIdentifyCode($identifyCode);
            if ($account->getId()){
                $identifyCode = $account->getAccountId();
            }
        }

        if (strpos($url, '?')){
            $url .= '&' . $referParam . '=' . $identifyCode;
        }else{
            $url .= '?' . $referParam . '=' . $identifyCode;
        }

        if ($this->_storeManager->getDefaultStoreView() && $store->getId() != $this->_storeManager->getDefaultStoreView()->getId()){
            $url .= '&___store=' . $store->getCode();
        }

        $urlParams = new \Magento\Framework\DataObject(array(
            'helper' => $this,
            'params' => array(),
        ));
        $this->_eventManager->dispatch('affiliateplus_helper_add_acc_to_url', array(
            // 'banner'	=> $banner,
            'url_params' => $urlParams,
        ));
        $params = $urlParams->getParams();
        if (count($params)){
            $url .= '&' . http_build_query($urlParams->getParams(), '', '&');
        }
        return $url;
    }

    /**
     * @return bool
     */
    public function isApproved() {
        return ($this->getApproved() == 1) ? true : false;
    }

    /**
     * Send email to customer when he register a new affiliate account
     * @param $identifyCode
     * @return $this
     * @throws LocalizedException
     */
    public function sendMailToNewAccount($identifyCode){
        if(!$this->_helper->isAffiliateModuleEnabled()){
            return;
        }

        $affiliateLink = $this->getAffiliateLink($this->_urlBuilder->getBaseUrl(), $identifyCode);

        $senderEmailConfiguration = $this->_helper->getSenderContact();
        if (!$this->_helperConfig->getEmailConfig('is_sent_email_new_account')){
            return $this;
        }

        $storeId = $this->getStoreViewId();
        $store = $this->_storeManager->getStore($storeId);
        $template = $this->_helper->getConfig(self::XML_PATH_NEW_ACCOUNT_EMAIL, $storeId);

        try{
            $transport = $this->_transportBuilder
                ->setTemplateIdentifier($template)
                ->setTemplateOptions(['area' => Area::AREA_FRONTEND, 'store' => $storeId])
                ->setTemplateVars(
                    [
                        'sender_name' => $senderEmailConfiguration['name'],
                        'affiliate_link' => $affiliateLink,
                        'account' => $this->setPassword('******'),
                        'trans_email_ident_support' => $this->_helper->getConfig('trans_email/ident_support/email'),
                        'store'=>$store
                    ]
                )
                ->setFrom($senderEmailConfiguration)
                ->addTo($this->getEmail(), $this->getName())
                ->getTransport();

            $transport->sendMessage();
        } catch(\Exception $e){
        }
        return $this;
    }

    /**
     * Send notification email to administrator when a customer register affiliate account
     * @return $this|void
     */
    public function sendNewAccountEmailToAdmin() {
        if(!$this->_helper->isAffiliateModuleEnabled()){
            return;
        }

        $storeId = $this->getStoreViewId();
        $store = $this->_storeManager->getStore($storeId);
        $senderEmailConfiguration = $this->_helper->getSenderContact();

        if (!$this->_helper->getConfig(self::XML_PATH_SENT_EMAIL_TO_SALES_NEW_ACCOUNT, $storeId)) {
            return $this;
        }
        try{
            $template = $this->_helper->getConfig(self::XML_PATH_SALES_ACCOUNT_TEMPLATE, $storeId);
            $recipient = $this->_helper->getConfig(self::XML_PATH_ADMIN_SALES_EMAIL_IDENTITY, $storeId);
            $backendUrl = $this->_helper->getBackendUrl();

            $transport = $this->_transportBuilder
                ->setTemplateIdentifier($template)
                ->setTemplateOptions(['area' => Area::AREA_FRONTEND, 'store' => $storeId])
                ->setTemplateVars(
                    [
                        'sender_name' => $senderEmailConfiguration['name'],
                        'account' => $this,'store'=>$store,
                        'backend_url'=>$backendUrl
                    ]
                )
                ->setFrom($senderEmailConfiguration)
                ->addTo($recipient['email'], $recipient['name'])
                ->getTransport()
            ;

            $transport->sendMessage();
        }catch(\Exception $e){
        }
        return $this;
    }

    /**
     * Send email notification to customer when his account has been approved.
     * @return $this|void
     */
    public function sendMailToApprovedAccount() {
        if(!$this->_helper->isAffiliateModuleEnabled()){
            return;
        }

        $storeId = $this->getStoreViewId();

        if (!$storeId) {
            $customer = $this->_objectManager->create('Magento\Customer\Model\Customer')->load($this->getCustomerId());
            $storeId = $customer->getStoreId();
        }
        $senderEmailConfiguration = $this->_helper->getSenderContact();
        $template = $this->_helper->getConfig(self::XML_PATH_APPROVED_ACCOUNT_EMAIL, $storeId);
        $transport = $this->_transportBuilder
            ->setTemplateIdentifier($template)
            ->setTemplateOptions(['area' => Area::AREA_FRONTEND, 'store' => $storeId])
            ->setTemplateVars(
                [
                    'account' => $this->setPassword('******'),
                    'sender_name' => $senderEmailConfiguration['name'],
                    'trans_email_ident_support' => $this->_helper->getConfig('trans_email/ident_support/email'),
                ]
            )
            ->setFrom($senderEmailConfiguration)
            ->addTo($this->getEmail(), $this->getName())
            ->getTransport();

        $transport->sendMessage();

        return $this;
    }
}
