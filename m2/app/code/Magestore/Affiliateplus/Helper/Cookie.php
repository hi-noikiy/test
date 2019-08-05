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
namespace Magestore\Affiliateplus\Helper;

/**
 * Affiliateplus Helper Cookie
 */
class Cookie extends HelperAbstract
{
    /**
     * Affiliate information
     * @var object null
     */
    protected $_affiliateInfo = null;

    /**
     * The number order of each customer
     * @var int null
     */
    protected $_numberOrdered = null;

    /**
     * Get affiliate information
     * @return object
     */
    public function getAffiliateInfo() {
        if (!is_null($this->_affiliateInfo)){
            return $this->_affiliateInfo;
        }

        $info = [];
        if ($accountCode = $this->getAffiliateSession()->getTopAffiliateIndentifyCode()) {
            $account = $this->_accountFactory->create()
                ->setStoreViewId($this->getStoreViewId())
                ->loadByIdentifyCode($accountCode);
            if ($account && $account->getId() && $account->getStatus() == 1 && ($this->_sessionModel->getAccount() && $account->getId() != $this->_sessionModel->getAccount()->getId())) {
                $info[$accountCode] = [
                    'index' => 1,
                    'code' => $accountCode,
                    'account' => $account,
                ];
            }

            $infoObj = new \Magento\Framework\DataObject(
                [
                    'info' => $info,
                ]
            );
            $this->_affiliateInfo = $infoObj->getInfo();

            return $this->_affiliateInfo;
        }
        /**
         *  Check Life-Time sales commission
         */
        if ($this->_objectManager->create('\Magestore\Affiliateplus\Helper\Config')->getCommissionConfig('life_time_sales')) {
            $this->_affiliateInfo = $this->_getLifetimeAffiliate();
            if($this->_affiliateInfo != null){
                return $this->_affiliateInfo;
            }
        }

        /**
         * Get affiliate inforamtion from cookie
         */
        $map_index = $this->_cookieManager->getCookie('affiliateplus_map_index');
        for ($i = $map_index; $i > 0; $i--) {
            $accountCode = $this->_cookieManager->getCookie("affiliateplus_account_code_$i");
            $account = $this->_accountFactory->create()
                ->setStoreViewId($this->getStoreViewId())
                ->loadByIdentifyCode($accountCode);
            if ($account && $account->getStatus() == 1 && ($this->_sessionModel->getAccount() && $account->getId() != $this->_sessionModel->getAccount()->getId())) {
                $info[$accountCode] = [
                    'index' => $i,
                    'code' => $accountCode,
                    'account' => $account,
                ];
            }
        }
        $infoObj = new \Magento\Framework\DataObject(array(
            'info' => $info,
        ));
        $this->_eventManager->dispatch('affiliateplus_get_affiliate_info',
            [
                'cookie' => $this->_cookieManager,
                'info_obj' => $infoObj,
            ]

        );
        $this->_affiliateInfo = $infoObj->getInfo();
        return $this->_affiliateInfo;
    }

    /**
     * Get affiliate information from the tracking table when the lifetime commission feature is enabled.
     * @return array
     */
    protected function _getLifetimeAffiliate(){
        $tracksCollection = $this->_objectManager->create('Magestore\Affiliateplus\Model\ResourceModel\Tracking\Collection');
        $customer = $this->_objectManager->create('Magento\Customer\Model\Session')->getCustomer();
        $info = array();

        if ($customer && $customer->getId()) {
            $tracksCollection->getSelect()
                ->where("customer_id = {$customer->getId()} OR customer_email = ?", $customer->getEmail());
        } else {
            if ($this->_objectManager->create('Magento\Checkout\Model\Session')->hasQuote()) {
                $quote = $this->_objectManager->create('Magento\Checkout\Model\Session')->getQuote();
                $customerEmail = $quote->getCustomerEmail();
            } else {
                $customerEmail = "";
            }
            $tracksCollection->addFieldToFilter('customer_email', $customerEmail);
        }

        $track = $tracksCollection->getFirstItem();

        if ($track && $track->getId()) {
            $account = $this->_accountFactory->create()
                ->setStoreViewId($this->getStoreViewId())
                ->load($track->getAccountId());
            if($account && $account->getStatus() == 1){
                $info[$account->getIdentifyCode()] = array(
                    'index' => 1,
                    'code' => $account->getIdentifyCode(),
                    'account' => $account,
                );
            }
            return $info;
        }
        return null;
    }

    /**
     * Get Number Order of customer
     * @return int
     */
    public function getNumberOrdered() {
        if (is_null($this->_numberOrdered)) {
            $orderCollection = $this->_objectManager->create('Magento\Sales\Model\ResourceModel\Order\Collection');
            $customer = $this->_objectManager->create('Magento\Customer\Model\Session')->getCustomer();
            $backendQuote = $this->_backendQuoteSession->getQuote();
            $checkoutSession = $this->_objectManager->create('Magento\Checkout\Model\Session');

            if ($customer && $customer->getId()) {
                $orderCollection->addFieldToFilter('customer_id', $customer->getId());
            }else {
                if ($checkoutSession->hasQuote()) {
                    $quote = $checkoutSession->getQuote();
                    $orderCollection->addFieldToFilter('customer_email', $quote->getCustomerEmail());
                }
                else if($customerEmail = $backendQuote->getCustomerEmail()){
                    $currentOrderId = $this->_backendQuoteSession->getOrder()->getId();
                    $orderCollection->addFieldToFilter('customer_email', $customerEmail)
                        ->addFieldToFilter('status', ['nin' => ['canceled']])
                        ->setOrder('entity_id','ASC');
                    if($currentOrderId && ($currentOrderId == $orderCollection->getFirstItem()->getId())){
                        $this->_numberOrdered = 1;
                        return $this->_numberOrdered;
                    }
                } else{
                    $this->_numberOrdered = 0;
                    return $this->_numberOrdered;
                }
            }

            $this->_numberOrdered = $orderCollection->getSize();
        }

        return $this->_numberOrdered;
    }

    /**
     * Save affiliate information to cookie
     * @param $accountCode
     * @param $expiredTime
     * @param bool $toTop
     * @param null $controller
     * @return $this
     */
    public function saveCookie($accountCode, $expiredTime, $toTop = false, $controller = null) {
        if ($expiredTime){
            $this->_publicCookieMetadata->setDuration(intval($expiredTime) * 86400);
        }

        $current_index = $this->_cookieManager->getCookie('affiliateplus_map_index');
        $addCookie = new \Magento\Framework\DataObject(
            [
                'existed' => false,
            ]
        );

        for ($i = intval($current_index); $i > 0; $i--) {
            if ($this->_cookieManager->getCookie("affiliateplus_account_code_$i") == $accountCode) {
                $addCookie->setExisted(true);
                $addCookie->setIndex($i);
                $this->_eventManager->dispatch('affiliateplus_controller_action_predispatch_add_cookie',
                    [
                        'request' => $this->_request,
                        'add_cookie' => $addCookie,
                        'cookie' => $this->_cookieManager,
                    ]
                );

                if ($addCookie->getExisted()) {
                    /**
                     * change latest account
                     */
                    $curI = intval($current_index);
                    for ($j = $i; $j < $curI; $j++) {
                        $this->_cookieManager->setPublicCookie(
                            "affiliateplus_account_code_$j", $this->_cookieManager->getCookie("affiliateplus_account_code_" . intval($j + 1)), $this->getPublicCookieMetadata(null, "/")
                        );
                    }
                    $this->_cookieManager->setPublicCookie("affiliateplus_account_code_$curI", $accountCode, $this->getPublicCookieMetadata(null, "/"));
                    return $this;
                }
            }
        }
        $current_index = $current_index ? intval($current_index) + 1 : 1;
        $this->_cookieManager->setPublicCookie('affiliateplus_map_index', $current_index, $this->getPublicCookieMetadata(null, "/"));
        $this->_cookieManager->setPublicCookie("affiliateplus_account_code_$current_index", $accountCode, $this->getPublicCookieMetadata(null, "/"));
        $cookieParams = new \Magento\Framework\DataObject([
            'params' => [],
        ]);
        $this->_eventManager->dispatch('affiliateplus_controller_action_predispatch_observer',
            [
                'controller_action' => $controller,
                'cookie_params' => $cookieParams,
                'cookie' => $this->_cookieManager,
            ]
        );

        if ($toTop) {
            $datenow = date('Y-m-d');
            $this->_cookieManager->setPublicCookie($accountCode, $datenow, $this->getPublicCookieMetadata(null, "/"));
        }
    }
}
