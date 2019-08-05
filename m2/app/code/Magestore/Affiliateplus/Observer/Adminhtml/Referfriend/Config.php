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
namespace Magestore\Affiliateplus\Observer\Adminhtml\Referfriend;
use Magento\Framework\Event\ObserverInterface;
use Magestore\Affiliateplus\Observer\AbtractObserver;
class Config extends AbtractObserver implements ObserverInterface{

    public function execute(\Magento\Framework\Event\Observer $observer){

        if(!$this->_helper->isAffiliateModuleEnabled()){
            return $this;
        }
        $website = $observer['website'];
        $store = $observer['store'];
        $scope = 'default';
        $scopeId = 0;
        if($store){
            $scope = 'stores';
            $scopeId = $store;
        } else {
            if($website){
                $scope = 'websites';
                $scopeId = $website;
            }
        }
        $pathUrlParamArray = 'affiliateplus/refer/url_param_array';

        $pathUrlParam = 'affiliateplus/general/url_param';
        $urlParam = $this->_helperConfig->getGeneralConfig('url_param', $store);
        if($urlParam == 'id'){
            $this->_removeIdParam($scope, $scopeId, $pathUrlParam, $store);
            throw new \Exception(__('This parameter is not allowed because it is able to override the system\'s core default parameter. '));
        }
        if (!$urlParam || ($urlParam == '')){
            $urlParam = 'acc';
        }
        $this->_saveUrlParamArray($scope, $scopeId, $pathUrlParamArray, $urlParam, $store);

    }

    protected function _removeIdParam($scope, $scopeId, $path, $store=0){
        $config = $this->_getConfigResouceModel();
        $urlParamArray = $this->_helperConfig->getReferConfig('url_param_array', $store);
        $value='';
        if($urlParamArray){
            $paramArray = explode(',', $urlParamArray);
            $value = $paramArray[count($paramArray)-1];
        }
        $config->saveConfig($path, $value, $scope, $scopeId);
    }

    protected function _saveUrlParamArray($scope, $scopeId, $pathUrlParamArray, $urlParam, $store){
        $urlParamArray = $this->_helperConfig->getReferConfig('url_param_array', $store);
        $newParam = '';
        if($urlParamArray){
            $paramArray = explode(',', $urlParamArray);
            for ($i = 0; $i < count($paramArray); $i++) {
                //Changed By Adam: 01/06/2015: solve the problem of using ID parameter
                if ($paramArray[$i] == $urlParam || $paramArray[$i] == 'id') {
                    unset($paramArray[$i]);
                }
            }
            $paramArray[] = $urlParam;
            $newParam = implode(',', $paramArray);
        }else {
            $newParam = $urlParam;
        }
        try{
            $config = $this->_getConfigResouceModel();
            $config->saveConfig($pathUrlParamArray, $newParam, $scope, $scopeId);
        } catch(\Exception $e){

        }
    }

    protected function _getConfigResouceModel(){
        return $this->_objectManager->create('Magento\Config\Model\ResourceModel\Config');
    }
}