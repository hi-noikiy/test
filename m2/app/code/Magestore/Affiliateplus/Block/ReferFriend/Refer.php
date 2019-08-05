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
namespace Magestore\Affiliateplus\Block\ReferFriend;

class Refer extends \Magestore\Affiliateplus\Block\AbstractTemplate{
    /**
     * @var
     */
    protected $_customUrl;
    /**
     * @var
     */
    protected $_personalUrl;
    /**
     * @var
     */
    protected $_account;
    /**
     * @var
     */
    protected $_personalPath;
    /**
     * @var
     */
    protected $_storeViewId;
    /**
     * @var
     */
    protected $_prefixUrl;
    /**
     * @var
     */
    protected $_suffixUrl;
    /**
     * @var
     */
    protected $_emailFormData;
    /**
     * @var
     */
    protected $_urlRewrite;

    /**
     * @return int
     */
    public function getStoreViewId(){
        if(!$this->_storeViewId){
            $this->_storeViewId = $this->_storeManager->getStore()->getId();
        }
        return $this->_storeViewId;
    }

    /**
     * @return \Magestore\Affiliateplus\Helper\Config
     */
    public function getHelperConfig(){
        return $this->_configHelper;
    }

    /**
     * @return mixed
     */
    public function getReferDescription(){
        return $this->getHelperConfig()->getReferConfig('refer_description', $this->getStoreViewId());
    }

    /**
     * @return mixed
     */
    public function getSharingDescription() {
        return $this->getHelperConfig()->getReferConfig('sharing_description', $this->getStoreViewId());
    }

    /**
     * @return mixed
     */
    public function getAccount() {
        if(!$this->_account){
            $this->_account = $this->_sessionModel->getAccount();
        }
        return $this->_account;
    }

    /**
     * @return mixed
     */
    public function getAccountEmail(){
        return $this->getAccount()->getEmail();
    }

    /**
     * @return mixed
     */
    protected function _getActionResourceModel(){
        return $this->_objectManager->create('Magestore\Affiliateplus\Model\ResourceModel\Action\Collection');
    }

    /**
     * @return array
     */
    public function getTrafficSources(){
        $accountId = $this->getAccount()->getId();
        $referers = $this->_getActionResourceModel()->addFieldToFilter('account_id', $accountId);
        $referers->getSelect()
            ->columns(array('total_clicks' => 'SUM(totals)'))
            ->group(array('referer', 'landing_page', 'store_id'));
        $trafficSources = array('facebook' => 0, 'twitter' => 0, 'google' => 0, 'email' => 0);
        foreach ($referers as $refer) {
            $referer = $refer->getData('referer');
            // Changed By Adam: 20/10/2014: Warning: strpos(): Empty needle  in C:\xampp\htdocs\project\magento1.5.0.1\app\code\local\Magestore\AffiliateplusReferFriend\Block\Refer.php on line 188
            if ($this->_getPersonalPath()
            && ($refer && trim($refer->getData('landing_page'), '/')
            && strpos($this->_getPersonalPath(), trim($refer->getData('landing_page'), '/'))) === false)
            {
                continue;
            }

            if (strpos($referer, 'facebook.com') !== false) {
                $trafficSources['facebook'] += $refer->getData('total_clicks');
            } elseif (strpos($referer, 'plus.url.google.com') !== false) {
                $trafficSources['google'] += $refer->getData('total_clicks');
            } elseif (strpos($referer, 't.co') !== false || strpos($referer, 'twitter.com') !== false) {
                $trafficSources['twitter'] += $refer->getData('total_clicks');
            } elseif ($this->_getPersonalPath()) {
                $trafficSources['email'] += $refer->getData('total_clicks');
            } elseif (strpos($referer, 'mail') !== false) {
                $trafficSources['email'] += $refer->getData('total_clicks');
            }
        }
        return $trafficSources;
    }

    /**
     * @return mixed
     */
    public function getCustomUrl() {
        if (!$this->_customUrl) {
            $this->_customUrl = $this->_sessionModel->getAffilateCustomUrl();
            $this->_sessionModel->setAffilateCustomUrl(null);
        }
        return $this->_customUrl;
    }

    /**
     * @param null $store
     * @return string
     */
    protected function _getDefaultPath($store = null) {
        $defaultPath = $this->_dataHelper->getConfig('web/default/front', $store);
        $p = explode('/', $defaultPath);
        switch (count($p)) {
            case 1: $p[] = 'index';
            case 2: $p[] = 'index';
        }
        return implode('/', $p);
    }

    /**
     * @return mixed
     */
    protected function _getRewriteUrlModel(){
        if(!$this->_urlRewrite){
            $this->_urlRewrite = $this->_objectManager->create('Magento\UrlRewrite\Model\UrlRewrite');
        }
        return $this->_urlRewrite;
    }

    /**
     * @return bool
     */
    protected function _getPersonalPath(){
        if (!$this->_personalPath) {
            $store = $this->_storeManager->getStore();
            $account = $this->getAccount();
            $targetPath = $this->_getDefaultPath($store);
            $targetPath .= '/acc/';
            $targetPath .= $account->getIdentifyCode();
            $rewrite = $this->_getRewriteUrlModel()->getCollection()
                ->addFieldToFilter('store_id', $store->getId())
                ->addFieldToFilter('target_path', $targetPath)
                ->getFirstItem()
            ;
            if ($rewrite->getId()){
                $this->_personalPath = $rewrite->getRequestPath();
            }else{
                $this->_personalPath = false;
            }
        }
        return $this->_personalPath;
    }

    /**
     * @return string
     */
    public function getPersonalUrl() {
        if (!$this->_personalUrl) {
            if ($personalPath = $this->_getPersonalPath()){
                $this->_personalUrl = $this->getUrl(null,
                    [
                        '_direct' => $personalPath,
                        '_store_to_url' => ($this->_storeManager->getDefaultStoreView() && $this->getStoreViewId() != $this->_storeManager->getDefaultStoreView()->getId()),
                    ]
                );
            }else{
                $this->_personalUrl = $this->_objectManager->create('Magestore\Affiliateplus\Helper\Url')->addAccToUrl($this->getBaseUrl());
            }
        }
        return $this->_personalUrl;
    }

    /**
     * @return mixed
     */
    public function getPrefixUrl(){
        if(!$this->_prefixUrl){
            $this->_prefixUrl = str_replace(" ", "", $this->getBaseUrl() . $this->_configHelper->getReferConfig('url_prefix', $this->getStoreViewId()));
        }
        return $this->_prefixUrl;
    }

    /**
     * @return string
     */
    public function getSuffixUrl(){
        if(!$this->_suffixUrl){
            $this->_suffixUrl = $this->getUrl(null,
                [
                    '_store_to_url' => ($this->_storeManager->getDefaultStoreView() && $this->getStoreViewId() != $this->_storeManager->getDefaultStoreView()->getId())
                ]
            );
        }
        return $this->_suffixUrl;
    }

    /**
     * @param $tab
     * @return bool
     */
    public function isActiveTab($tab){
        if ($tmpTab = $this->getRequest()->getParam('tab')) {
            return ($tmpTab == $tab);
        }
        return false;
    }

    /**
     * @return \Magento\Framework\DataObject
     */
    public function getEmailFormData() {
        if (!$this->_emailFormData) {
            $data = $this->_sessionModel->getEmailFormData();
            $this->_sessionModel->setEmailFormData(null);
            if(!is_array($data)){
                $dataObj = new \Magento\Framework\DataObject([$data]);
            } else {
                $dataObj = new \Magento\Framework\DataObject($data);
            }
            $this->_emailFormData = $dataObj;
        }
        return $this->_emailFormData;
    }

    /**
     * @return mixed
     */
    public function getDefaultEmailContent() {
        $content = $this->_configHelper->getReferConfig('email_content', $this->getStoreViewId());
        if ($this->_getPersonalPath()) {
            $personalUrl = $this->getPersonalUrl();
        } else {
            $personalUrl = $this->_objectManager->create('Magestore\Affiliateplus\Helper\Url')
                ->addAccToUrl($this->getUrl(null, array('_query' => array('src' => 'email'))));
        }
        return str_replace(
            array(
                '{{store_name}}',
                '{{personal_url}}',
                '{{account_name}}'
            ),
            array(
                $this->_storeManager->getStore()->getFrontendName(),
                $personalUrl,
                $this->getAccount()->getName()
            ),
            $content
        );
    }

    /**
     * @return mixed
     */
    public function getDefaultSharingContent(){
        $content = $this->_configHelper->getReferConfig('sharing_message', $this->getStoreViewId());
        return str_replace(
            array(
                '{{store_name}}',
                '{{personal_url}}'
            ),
            array(
                $this->_storeManager->getStore()->getFrontendName(),
                $this->getPersonalUrl()
            ),
            $content
        );
    }

    /**
     * @return mixed
     */
    public function getDefaultTwitterContent(){
        $content = $this->_configHelper->getReferConfig('twitter_message', $this->getStoreViewId());
        return str_replace(
            array(
                '{{store_name}}',
                '{{personal_url}}'
            ),
            array(
                $this->_storeManager->getStore()->getFrontendName(),
                $this->getPersonalUrl()
            ),
            $content
        );
    }

    /**
     * @return mixed
     */
    public function isEnableResponsive(){
        return $this->_configHelper->getStyleConfig('responsive_enable', $this->getStoreViewId());
    }
}