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
namespace Magestore\Affiliateplus\Observer\Frontend;
use Magento\Framework\Event\ObserverInterface;
use Magestore\Affiliateplus\Observer\AbtractObserver;

/**
 * Class ProductGetFinalPrice
 * @package Magestore\Affiliateplus\Observer
 */
class ControllerPredispatch extends AbtractObserver implements ObserverInterface
{
    /**
     * Set Final Price to product in product list
     * @param \Magento\Framework\Event\Observer $observer
     * @return $this
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        if(!$this->_helper->isAffiliateModuleEnabled())
            return $this;

        $controller = $observer['controller_action'];
        $request = $observer['request'];
        $accountCode = $request->getParam('acc');

        $this->_eventManager->dispatch('affiliateplus_controller_action_predispatch',
            [
                'request' => $request
            ]
        );
        $this->_saveClickAction($controller, $request);
        $expiredTime = $this->_helperConfig->getGeneralConfig('expired_time');
        $accountData = $this->_getAffiliateAccountByAccountCode($accountCode, $request);
        $account = isset($accountData['account']) ? $accountData['account'] : null;
        if($account && $account->getId()){
            $accountCode = $account->getIdentifyCode();
            $this->_helperCookie->saveCookie($accountCode, $expiredTime, false, $controller);
        }
    }

    /**
     * save click information
     * @param $controller
     * @param $request
     * @return true
     */
    protected function _saveClickAction($controller, $request)
    {
        $accountCode = $request->getParam('acc');
        $accountData = $this->_getAffiliateAccountByAccountCode($accountCode, $request);
        $account = isset($accountData['account']) ? $accountData['account'] : null;
        if($account && $account->getId()) {
            $ipAddress = $request->getClientIp();
            $banner_id = $request->getParam('bannerid');
            $storeId = $this->_storeManager->getStore()->getId();
            if ($banner_id) {
                $banner = $this->_objectManager->create('Magestore\Affiliateplus\Model\Banner')
                    ->load($banner_id);
                $banner->setStoreViewId($storeId);
                if ($banner->getStatus() != 1){
                    $banner_id = 0;
                }
            }
            $check = false;
            $param = isset($accountData['param']) ? $accountData['param'] : '';
            if ($this->_helper->exitedCookie($param)) {
                return $this;
            }
            if (!$check) {
                if ($this->_helper->isProxy()) {
                    return $this;
                }
            }
            if (!$check) {
                if ($this->_helper->isRobots()) {
                    return $this;
                }
            }

            $domain = isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : '';
            if (!$domain && $request->getParam('src')) {
                $domain = $request->getParam('src');
            }
            $landing_page = $request->getOriginalPathInfo();
            $actionModel = $this->_objectManager->create('Magestore\Affiliateplus\Model\Action');

            if ($check) {
                $isUnique = 0;
            } else {
                $isUnique = $actionModel->checkIpClick($ipAddress, $account->getId(), $domain, $banner_id, 2);
            }
            $action = $actionModel->saveAction($account->getId(), $banner_id, 2, $storeId, $isUnique, $ipAddress, $domain, $landing_page);
            if ($isUnique) {
                if ($this->_helperConfig->getActionConfig('detect_iframe')) {
                    $hashCode = md5($action->getCreatedDate() . $action->getId());
                    $this->_sessionManager->setData('transaction_checkiframe__action_id', $action->getId());
                    $this->_sessionManager->setData('transaction_checkiframe_hash_code', $hashCode);
                } else {
                    $action->setIsUnique(1)->save();
                    $this->_eventManager->dispatch('affiliateplus_save_action_before',
                        [
                            'action' => $action,
                            'is_unique' => $isUnique,
                        ]
                    );
                }
            }
        }
    }

    /**
     * Get the affiliate account by account code
     * @param $accountCode
     * @param $request
     * @return \Magestore\Affiliateplus\Model\Account
     */
    protected function _getAffiliateAccountByAccountCode($accountCode, $request)
    {
        $param = array();
        if (!$accountCode || ($accountCode == '')) {
            $paramList = $this->_helperConfig->getReferConfig('url_param_array');
            $paramArray = explode(',', $paramList);
            for ($i = (count($paramArray) - 1); $i >= 0; $i--) {
                $accountCode = $this->_request->getParam($paramArray[$i]);
                if ($accountCode && ($accountCode != '')){
                    $param = $paramArray[$i];
                    break;
                }
            }
        }

        /**
         * fix issue can't detect affiliate when customer click on the affiliate link on Facebook
         */
        if(strpos($accountCode, "?")) {
            $code = explode("?", $accountCode);
            $accountCode = $code[0];
        }

        if($this->_helperConfig->getGeneralConfig('url_param_value') == 2){
            $account = $this->_accountFactory->create()
                ->load($accountCode, 'account_id');
        } else{
            $account = $this->_accountFactory->create()
                ->load($accountCode, 'identify_code');
        }

        if ($account->getId()){
            $accountCode = $account->getIdentifyCode();
        }

        if (!$accountCode){
            return;
        }

        if ($account = $this->_getAffiliateSession()->getAccount()){
            if ($account->getIdentifyCode() == $accountCode){
                return;
            }
        }

        $storeId = $this->_storeManager->getStore()->getId();
        if (!$storeId)
            return;

        $account = $this->_accountFactory->create()->setStoreViewId($storeId)->loadByIdentifyCode($accountCode);
        if (!$account->getId() || ($account->getStatus() != 1)){
            return;
        }
        $accountData = [
            'param' => $param,
            'account' => $account
        ];

        return $accountData;
    }

    /**
     * Get Affiliate Session Model
     * @return mixed
     */
    protected function _getAffiliateSession(){
        return $this->_objectManager->create('Magestore\Affiliateplus\Model\Session');
    }
}