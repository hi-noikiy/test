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

class Config extends HelperAbstract
{
    const URL_PARAM_VALUE_IDENTIFY_CODE = '1';
    const URL_PARAM_VALUE_AFFILIATE_ID = '2';

    /**
     * get store config
     *
     * @param $key
     * @param null $store
     * @return mixed
     */
    public function getConfig($key, $store = null) {
        return $this->_scopeConfig->getValue(
            $key,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
            $store
        );
    }

    /**
     * Get Sharing Configuration
     * @param $code
     * @param null $store
     * @return mixed
     */
    public function getSharingConfig($code, $store = null){

        return $this->getConfig('affiliateplus/account/'.$code,$store);
    }

    /**
     * Get General Configuration
     * @param $code
     * @param null $store
     * @return mixed
     */
    public function getGeneralConfig($code, $store = null){
        return $this->getConfig('affiliateplus/general/'.$code,$store);
    }

    /**
     * Get Account Configuration Value
     * @param $code
     * @param null $store
     * @return mixed
     */
    public function getAccountConfig($code, $store = null){
        return $this->getConfig('affiliateplus/account/'.$code,$store);
    }

    /**
     * Get Commission Configuration Value
     * @param $code
     * @param null $store
     * @return mixed
     */
    public function getCommissionConfig($code, $store = null){
        return $this->getConfig('affiliateplus/commission/'.$code,$store);
    }

    /**
     * Get Discount Configuration Value
     * @param $code
     * @param null $store
     * @return mixed
     */
    public function getDiscountConfig($code, $store = null){
        return $this->getConfig('affiliateplus/discount/'.$code,$store);
    }

    /**
     * Get Payment Configuration Value
     * @param $code
     * @param null $store
     * @return mixed
     */
    public function getPaymentConfig($code, $store = null){
        return $this->getConfig('affiliateplus/payment/'.$code,$store);
    }

    /**
     * Get Email Configuration Value
     * @param $code
     * @param null $store
     * @return mixed
     */
    public function getEmailConfig($code, $store = null){
        return $this->getConfig('affiliateplus/email/'.$code,$store);
    }

    /**
     * Get Action Configuration Value
     * @param $code
     * @param null $store
     * @return mixed
     */
    public function getActionConfig($code, $store = null){
        return $this->getConfig('affiliateplus/action/'.$code,$store);
    }

    /**
     * Get Discount Configuration Value
     * @param $code
     * @param null $store
     * @return mixed
     */
    public function getStyleConfig($code, $store = null){
        return $this->getConfig('affiliateplus/style_config/'.$code,$store);
    }

    /**
     * Get Discount Configuration Value
     * @param $code
     * @param null $store
     * @return mixed
     */
    public function getReferConfig($code, $store = null){
        return $this->getConfig('affiliateplus/refer/'.$code,$store);
    }

    /**
     * Get Responsive Configuration value
     * @return mixed
     */
    public function getResponsiveEnable($store = null)
    {
        return $this->getConfig('affiliateplus/style_config/responsive_enable',$store);
    }

    /**
     * get Default Country
     *
     * @param null $store
     * @return mixed
     */
    public function getCountryDefault($store = null)
    {
        return $this->getConfig('general/country/default',$store);
    }

    /**
     * get Material config
     * @param $code
     * @param null $store
     * @return mixed
     */
    public function getMaterialConfig($code, $store = null){
        return $this->getConfig('affiliateplus/general/material_'.$code,$store);
    }

    /**
     * @return bool
     */
    public function disableMaterials(){
        return (
            $this->_objectManager->create('Magestore\Affiliateplus\Helper\Account')->accountNotLogin()
            || !$this->getMaterialConfig('enable')
        );
    }
}