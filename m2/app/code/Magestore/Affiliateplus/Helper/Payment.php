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

use Magento\Framework\App\Filesystem\DirectoryList;

/**
 * Class Payment
 * @package Magestore\Affiliateplus\Helper
 */
class Payment extends HelperAbstract
{
    const XML_PAYMENT_METHODS = 'affiliateplus_payment';

    const AFFILIATE_ICON_PATH = 'affiliateplus/payment';
    /**
     * @param $paymentMethodCode
     * @param null $storeId
     * @return mixed
     * @throws \Exception
     */
    public function getPaymentMethod($paymentMethodCode, $storeId = null){
        $modelPath = $this->getConfig(self::XML_PAYMENT_METHODS.'/'.$paymentMethodCode.'/model',$storeId);
        if (!$modelPath){
            throw new \Exception(__('Cannot find any payment method to configure'));
        }
        try{
            $paymentMethod = $this->_objectManager->create($modelPath);
        } catch(\Exception $e){
        }

        if (!($paymentMethod instanceof \Magestore\Affiliateplus\Model\Payment\AbstractPayment)){
            throw new \Exception(__('The required payment model is an abstract of class %1!','Magestore_Affiliateplus_Model_Payment_Abstract'));
        }

        if ($storeId){
            $paymentMethod->setStoreViewId($storeId);
        }
        return $paymentMethod;
    }

    /**
     * get All available payment method code
     *
     * @param null $storeId
     * @return array
     */
    public function getAvailablePaymentCode($storeId = null){
        $allPaymentConfig = $this->getConfig(self::XML_PAYMENT_METHODS,$storeId);
        $paymentCode = [];
        if($allPaymentConfig)
        foreach ($allPaymentConfig as $code => $config){
            if (isset($config['active']) && $config['active']){
                $paymentCode[] = $code;
            }
        }
        return $paymentCode;
    }

    /**
     * @param null $storeId
     * @return array
     */
    public function getAvailablePayment($storeId = null){
        $paymentCodes = $this->getAvailablePaymentCode($storeId);

        $payments = [];

        foreach ($paymentCodes as $paymentCode){
            try {
                $payments[$paymentCode] = $this->getPaymentMethod($paymentCode,$storeId);
            } catch (\Exception $e){
            }
        }

        return $payments;
    }

    /**
     * Get all payment method
     * @param null $storeId
     * @return array
     */
    public function getAllPaymentOptionArray($storeId = null){
        $allPaymentConfig = $this->getConfig(self::XML_PAYMENT_METHODS, $storeId);
        $payments = [];
        if($allPaymentConfig){
            foreach ($allPaymentConfig as $code => $config) {
                if (isset($config['model'])) {
                    $payments[$code] = $config['label'];
                }
            }
        } else {
            $payments['paypal'] = __('Paypal');
        }

        return $payments;
    }

    /**
     * get all payment methods as an options array
     * @param null $id
     * @return array
     */
    public function getPaymentOption($id = null) {
        $allPaymentConfig = $this->getConfig(self::XML_PAYMENT_METHODS);
        $payments = array();
        foreach ($allPaymentConfig as $code => $config)
            if (!$id) {
                if (isset($config['active']) && $config['active'])
                    $payments[] = array(
                        'value' => $code,
                        'label' => $config['label'],
                    );
            }else {
                if (isset($config['active']))
                    $payments[] = array(
                        'value' => $code,
                        'label' => $config['label'],
                    );
            }
        return $payments;
    }

    /**
     * @return mixed
     */
    public function getBalanceConfig()
    {
        return $this->getConfig('affiliateplus/account/balance');
    }

    /**
     * @return mixed
     */
    public function getPayFeeConfig()
    {
        return $this->getConfig('affiliateplus/payment/who_pay_fees');
    }

    /**
     * @return mixed
     */
    public function getPaymentDefaultMethod()
    {
         return $this->getConfig('affiliateplus/payment/default_method');
    }

    /**
     * @param $code
     * @return bool
     */
    public function isRequireAuthentication($code){
        $store = $this->_storeManager->getStore()->getId();
        $config = $this->getConfig('affiliateplus_payment/'.$code.'/require_authentication',$store);
        if($config) return true;
        return false;
    }

    /**
     * @param $field
     * @param $files
     */
    public function uploadVerifyImage($field, $files){
        if(isset($files[$field]['name']) && $files[$field]['name'] != '') {
            try {
                $uploader = $this->_fileUploaderFactory->create(['fileId' => $field]);
                $uploader->setAllowedExtensions(['jpg', 'jpeg', 'gif', 'png']);
                $uploader->setAllowRenameFiles(true);
                $uploader->setFilesDispersion(false);

                $path = $this->_filesystem->getDirectoryRead(DirectoryList::MEDIA)
                    ->getAbsolutePath(self::AFFILIATE_ICON_PATH);
                $file = $uploader->save($path);
                return $file['file'];
            } catch (\Exception $e) {

            }
        }
        return;
    }
    /**
     * get Base Url Media
     *
     * @return mixed
     */
    public function getBaseUrlMedia()
    {
        return $baseUrl = $this->_storeManager->getStore()->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_MEDIA);
    }
}