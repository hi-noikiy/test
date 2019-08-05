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

class Url extends HelperAbstract
{
    /**
     * get  Refer Parameter
     * @return string
     */
    public function getPersonalUrlParameter() {
        $paramArray = explode(',', $this->getConfig('affiliateplus/refer/url_param_array'));
        $referParam = $paramArray[count($paramArray) - 1];
        if (!$referParam && ($referParam == '')){
            $referParam = 'acc';
        }
        return $referParam;
    }

    /**
     * Get banner Url
     * @param $banner
     * @param null $store
     * @return string
     */
    public function getBannerUrl($banner, $store = null) {
        $url = $this->getUrlLink($banner->getLink());
        $urlParams = $this->getUrlParams($url, $store, $banner);
        return $urlParams;
    }

    /**
     * get Full link URL
     * @param string $url
     * @return string
     */
    public function getUrlLink($url) {
        if (!preg_match("/^http\:\/\/|https\:\/\//", $url))
            return $this->_urlBuilder->getUrl() . trim($url, '/');
        return rtrim($url, '/');
    }

    /**
     * Add account param to the link
     * @param string $url
     * @param null $store
     * @return string
     */
    public function addAccToUrl($url, $store = null) {
        $url = $this->getUrlLink($url);
        $urlParams = $this->getUrlParams($url, $store);
        return $urlParams;
    }

    public function getUrlParams($url, $store = null, $banner=null){
        if (is_null($store)){
            $store = $this->_storeManager->getStore();
        }
        $account = $this->getAffiliateAccount();
        $referParam = $this->getPersonalUrlParameter();
        $referParamValue = $account->getIdentifyCode();
        if ($this->getConfig('affiliateplus/general/url_param_value') == \Magestore\Affiliateplus\Helper\Config::URL_PARAM_VALUE_AFFILIATE_ID){
            $referParamValue = $account->getAccountId();
        }
        if (strpos($url, '?')){
            $url .= '&' . $referParam . '=' . $referParamValue;
        }else{
            $url .= '?' . $referParam . '=' . $referParamValue;
        }
        if ($this->_storeManager->getDefaultStoreView() && $store->getId() != $this->_storeManager->getDefaultStoreView()->getId()){
            $url .= '&___store=' . $store->getCode();
        }
        if($banner && $banner->getId()){
            $url .= '&bannerid=' . $banner->getId();
        }

        $urlParams = new \Magento\Framework\DataObject(array(
            'helper' => $this,
            'params' => array(),
        ));

        if($banner && $banner->getId()){
            $this->_eventManager->dispatch('affiliateplus_helper_get_banner_url', array(
                 'banner'	=> $banner,
                'url_params' => $urlParams,
            ));
        } else {
            $this->_eventManager->dispatch('affiliateplus_helper_add_acc_to_url', array(
                // 'banner'	=> $banner,
                'url_params' => $urlParams,
            ));

        }
        $params = $urlParams->getParams();
        if (count($params)){
            $url .= '&' . http_build_query($urlParams->getParams(), '', '&');
        }
        return $url;
    }
}