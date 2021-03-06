<?php

class Magestore_Affiliateplus_Model_Account extends Mage_Core_Model_Abstract {

    const XML_PATH_ADMIN_EMAIL_IDENTITY = 'trans_email/ident_general';
    const XML_PATH_NEW_ACCOUNT_EMAIL = 'affiliateplus/email/new_account_email_template';
    const XML_PATH_APPROVED_ACCOUNT_EMAIL = 'affiliateplus/email/approved_account_email_template';

    protected $_store_id = null;
    protected $_balance_is_global = false;
    protected $_eventPrefix = 'affiliateplus_account';
    protected $_eventObject = 'affiliateplus_account';

    public function _construct() {
        parent::_construct();
        $this->_init('affiliateplus/account');
    }

    public function getStoreAttributes() {
        $storeAttribute = new Varien_Object(array(
            'store_attribute' => array(
                //'name',
                'status',
                'approved',
            //'unique_clicks',
            )
        ));

        Mage::dispatchEvent($this->_eventPrefix . '_get_store_attributes', array(
            $this->_eventObject => $this,
            'attributes' => $storeAttribute,
        ));

        return $storeAttribute->getStoreAttribute();
    }

    public function getBalanceAttributes() {
        $balanceAttribute = new Varien_Object(array(
            'balance_attribute' => array(
                'balance',
                'total_commission_received',
                'total_paid',
            //'total_clicks',
            //'unique_clicks',
            )
        ));

        Mage::dispatchEvent($this->_eventPrefix . '_get_balance_attributes', array(
            $this->_eventObject => $this,
            'attributes' => $balanceAttribute,
        ));

        return $balanceAttribute->getBalanceAttribute();
    }

    public function setStoreId($value) {
        $this->_store_id = $value;
        return $this;
    }

    public function getStoreId() {
        return $this->_store_id;
    }

    public function setBalanceIsGlobal($value) {
        $this->_balance_is_global = $value;
        return $this;
    }

    public function getBalanceIsGlobal() {
        return $this->_balance_is_global;
    }

    public function load($id, $field = null) {
        parent::load($id, $field);

        Mage::dispatchEvent($this->_eventPrefix . '_load_store_value_before', $this->_getEventData());

        if ($this->getStoreId())
            $this->loadStoreValue();

        Mage::dispatchEvent($this->_eventPrefix . '_load_store_value_after', $this->_getEventData());

        return $this;
    }

    /**
     * function loadStoreValue
     *
     * @param int $storeId
     * @return Magestore_Affiliateplus_Model_Account
     */
    public function loadStoreValue($storeId = null) {
        if (!$storeId)
            $storeId = $this->getStoreId();
        if (!$storeId)
            return $this;
        $storeValues = Mage::getModel('affiliateplus/account_value')->getCollection()
                ->addFieldToFilter('account_id', $this->getId())
                ->addFieldToFilter('store_id', $storeId);

        if ($this->getBalanceIsGlobal())
            $storeValues->addFieldToFilter('attribute_code', array('in' => $this->getStoreAttributes()));
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
     * get affiliate balance by website id
     * @param website id
     * @return affiliate balance
     */
    public function getWebsiteBalance($website = null){
        echo "website ballance";
        die;
        $storeId = $this->getStoreId();
        $scope = Mage::getStoreConfig('affiliateplus/account/balance', $storeId);
        if($scope != 'website')
            return $this->getBalance();
        if(is_null($website))
            $website = Mage::app()->getWebsite()->getId();
        $balance = 0;
        $storeIds = Mage::helper('affiliateplus/account')->getStoreIdsByWebsite($website);
        $collection = Mage::getModel('affiliateplus/account_value')
            ->getCollection()
            ->addFieldToFilter('account_id', $this->getId())
            ->addFieldToFilter('attribute_code', 'balance')
            ->addFieldToFilter('store_id', array('in'=>$storeIds));;
        foreach($collection as $item){
            $balance += $item->getValue();
        }
        return $balance;
    }

    protected function _beforeSave() {
        if ($this->getStatus() == 1)
            $this->setApproved(1);

        $defaultAccount = Mage::getModel('affiliateplus/account')->load($this->getId());

        if ($storeId = $this->getStoreId()) {
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
                    $attributeValue = Mage::getModel('affiliateplus/account_value')
                            ->loadAttributeValue($this->getId(), $storeId, $attribute);
                    if ($delta = ($this->getData($attribute) - $attributeValue->getValue())) {
                        try {
                            $attributeValue->setValue($this->getData($attribute));
                            $attributeValue->save();
                        } catch (Exception $e) {
                            
                        }
                    }
                    $this->setData($attribute, $defaultAccount->getData($attribute) + $delta);
                }
            }
        } elseif ($this->getId()) {
            if ($delta = ($this->getData('balance') - $defaultAccount->getData('balance'))) {
                $attributeValues = Mage::getModel('affiliateplus/account_value')->getCollection()
                        ->addFieldToFilter('account_id', $this->getId())
                        ->addFieldToFilter('attribute_code', 'balance');
                $paid = $this->getData('total_paid') - $defaultAccount->getData('total_paid');

                foreach ($attributeValues as $attributeValue) {
                    if (($delta + $attributeValue->getValue()) >= 0) {
                        $attributeValue->setValue($attributeValue->getValue() + $delta);
                        $receivedAtt = Mage::getModel('affiliateplus/account_value')
                                ->loadAttributeValue($this->getId(), $attributeValue->getStoreId(), 'total_commission_received');
                        $receivedAtt->setValue($receivedAtt->getValue() - $delta)->save();
                        try {
                            $attributeValue->save();
                            if ($paid > 0) {
                                $paidAttribute = Mage::getModel('affiliateplus/account_value')
                                        ->loadAttributeValue($this->getId(), $attributeValue->getStoreId(), 'total_paid');
                                $paidAttribute->setValue($paidAttribute->getValue() + $paid)->save();
                            }
                        } catch (Exception $e) {
                            
                        }
                        break;
                    } else {
                        $delta += $attributeValue->getValue();
                        try {
                            if ($paid > 0) {
                                $paidAttribute = Mage::getModel('affiliateplus/account_value')
                                        ->loadAttributeValue($this->getId(), $attributeValue->getStoreId(), 'total_paid');
                                if ($attributeValue->getValue() >= $paid) {
                                    $paidAttribute->setValue($paidAttribute->getValue() + $paid)->save();
                                    $paid = 0;
                                } else {
                                    $paidAttribute->setValue($paidAttribute->getValue() + $attributeValue->getValue())->save();
                                    $paid -= $attributeValue->getValue();
                                }
                            }
                            $receivedAtt = Mage::getModel('affiliateplus/account_value')
                                    ->loadAttributeValue($this->getId(), $attributeValue->getStoreId(), 'total_commission_received');
                            $receivedAtt->setValue($receivedAtt->getValue() + $attributeValue->getValue())->save();
                            $attributeValue->setValue(0)->save();
                        } catch (Exception $e) {
                            
                        }
                    }
                }
            }
        }
        return parent::_beforeSave();
    }

    protected function _afterSave() {
        if ($storeId = $this->getStoreId()) {
            $storeAttributes = $this->getStoreAttributes();
            foreach ($storeAttributes as $attribute) {
                $attributeValue = Mage::getModel('affiliateplus/account_value')
                        ->loadAttributeValue($this->getId(), $storeId, $attribute);
                if ($this->getData($attribute . '_in_store')) {
                    try {
                        $attributeValue->setValue($this->getData($attribute . '_value'))->save();
                    } catch (Exception $e) {
                        
                    }
                } elseif ($attributeValue && $attributeValue->getId()) {
                    try {
                        $attributeValue->delete();
                    } catch (Exception $e) {
                        
                    }
                }
            }
        }
        return parent::_afterSave();
    }

    public function loadByIdentifyCode($code) {
        return $this->load($code, 'identify_code');
        /*
          $instance = $this->getCollection()
          ->addFieldToFilter('identify_code',$code)
          ->getFirstItem();
          $this->setData($instance->getData())
          ->setId($instance->getId());
          return $this;
         */
    }

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

    public function loadByCustomer($customer) {
        if ($customer && $customer->getId())
            return $this->loadByCustomerId($customer->getId());
        return $this;
    }

    public function loadByCustomerId($customerId) {
        return $this->load($customerId, 'customer_id');
    }

    public function isEnabled() {
        return ($this->getStatus() == 1) ? true : false;
    }

    public function isApproved() {
        return ($this->getApproved() == 1) ? true : false;
    }

    //get Affiliate link after create affiliate account
    public function getAffiliateLink($url, $identifyCode, $store = null) {
        if (is_null($store))
            $store = Mage::app()->getStore();
        $url = Mage::helper('affiliateplus/url')->getUrlLink($url);
        //hainh 29-07-2014 

        $referParam = Mage::helper('affiliateplus/url')->getPersonalUrlParameter();
        if (Mage::getStoreConfig('affiliateplus/general/url_param_value') == 2) {
            $account = Mage::getModel('affiliateplus/account')->loadByIdentifyCode($identifyCode);
            if ($account->getId())
                $identifyCode = $account->getAccountId();
        }


        if (strpos($url, '?'))
            $url .= '&' . $referParam . '=' . $identifyCode;
        else
            $url .= '?' . $referParam . '=' . $identifyCode;

        //end editing
        // Changed By Adam: 10/11/2014: Fix loi khi chay multiple website nhung ko co default store view
        if (Mage::app()->getDefaultStoreView() && $store->getId() != Mage::app()->getDefaultStoreView()->getId())
            $url .= '&___store=' . $store->getCode();

        $urlParams = new Varien_Object(array(
            'helper' => $this,
            'params' => array(),
        ));
        Mage::dispatchEvent('affiliateplus_helper_add_acc_to_url', array(
            // 'banner'	=> $banner,
            'url_params' => $urlParams,
        ));
        $params = $urlParams->getParams();
        if (count($params))
            $url .= '&' . http_build_query($urlParams->getParams(), '', '&');

        return $url;
    }

    public function sendMailToNewAccount($identifyCode) {
        // Changed By Adam 28/07/2014
        if (!Mage::helper('affiliateplus')->isAffiliateModuleEnabled())
            return;
        //affilate link and sender information
        $affiliateLink = $this->getAffiliateLink(Mage::getBaseUrl(), $identifyCode);
        $affiliateLink = '<a id="affiliate-link" href="' . $affiliateLink . '" target="_blank">' . $affiliateLink . '</a>';
        $senderEmailConfiguration = Mage::helper('affiliateplus')->getSenderContact();
        //	
        if (!Mage::getStoreConfig('affiliateplus/email/is_sent_email_new_account'))
            return $this;

        $storeId = $this->getStoreId();
        $translate = Mage::getSingleton('core/translate');
        $translate->setTranslateInline(false);
        $template = Mage::getStoreConfig(self::XML_PATH_NEW_ACCOUNT_EMAIL, $storeId);

        $sendTo = array(
            array(
                'email' => $this->getEmail(),
                'name' => $this->getName(),
            )
        );

        $mailTemplate = Mage::getModel('core/email_template');

        foreach ($sendTo as $recipient) {
            $mailTemplate->setDesignConfig(array('area' => 'frontend', 'store' => $storeId))
                    ->sendTransactional(
                            $template, $senderEmailConfiguration, $recipient['email'], $recipient['name'], array(
                        'sender_name' => $senderEmailConfiguration['name'],
                        'affiliate_link' => $affiliateLink, //add variable for email template
                        'account' => $this->setPassword('******'),
                            )
            );
        }

        $translate->setTranslateInline(true);

        return $this;
    }

    public function sendNewAccountEmailToAdmin() {
        // Changed By Adam 28/07/2014
        if (!Mage::helper('affiliateplus')->isAffiliateModuleEnabled())
            return;
        $storeId = $this->getStoreId();
        $senderEmailConfiguration = Mage::helper('affiliateplus')->getSenderContact();
        if (!Mage::getStoreConfig('affiliateplus/email/is_sent_to_sales_new_account', $storeId)) {
            return $this;
        }
        $translate = Mage::getSingleton('core/translate');
        $translate->setTranslateInline(false);
        $template = Mage::getStoreConfig('affiliateplus/email/new_account_sales_email_template', $storeId);
        $sendTo = array(
            Mage::getStoreConfig('trans_email/ident_sales', $storeId)
        );

        $mailTemplate = Mage::getModel('core/email_template');
        foreach ($sendTo as $recipient) {
            $mailTemplate->setDesignConfig(array('area' => 'frontend', 'store' => $storeId))
                    ->sendTransactional(
                            $template, $senderEmailConfiguration, $recipient['email'], $recipient['name'], array(
                        'sender_name' => $senderEmailConfiguration['name'],
                        'account' => $this
                            )
            );
        }

        $translate->setTranslateInline(true);
        return $this;
    }

    public function sendMailToApprovedAccount() {
        // Changed By Adam 28/07/2014
        if (!Mage::helper('affiliateplus')->isAffiliateModuleEnabled())
            return;
        $storeId = $this->getStoreId();
        // Changed By Adam 21/05/2014: Fix issue send email in English when customer signup in French
        if (!$storeId) {
            $customer = Mage::getModel('customer/customer')->load($this->getCustomerId());
            $storeId = $customer->getStoreId();
        }
        $senderEmailConfiguration = Mage::helper('affiliateplus')->getSenderContact();
        $translate = Mage::getSingleton('core/translate');
        $translate->setTranslateInline(false);
        $template = Mage::getStoreConfig(self::XML_PATH_APPROVED_ACCOUNT_EMAIL, $storeId);

        $sendTo = array(
            array(
                'email' => $this->getEmail(),
                'name' => $this->getName(),
            )
        );

        $mailTemplate = Mage::getModel('core/email_template');

        foreach ($sendTo as $recipient) {
            $mailTemplate->setDesignConfig(array('area' => 'frontend', 'store' => $storeId))
                    ->sendTransactional(
                            $template, $senderEmailConfiguration, $recipient['email'], $recipient['name'], array(
                        'account' => $this->setPassword('******'),
                        'sender_name' => $senderEmailConfiguration['name'],
                            )
            );
        }

        $translate->setTranslateInline(true);
        return $this;
    }

}
