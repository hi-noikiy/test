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
namespace Magestore\Affiliateplus\Controller\Refer;

/**
 * Action Index
 */
/**
 * Class Personal
 * @package Magestore\Affiliateplus\Controller\Refer
 */
class Personal extends \Magestore\Affiliateplus\Controller\AbstractAction
{
    /**
     * Execute action
     */
    public function execute()
    {
        if (!$this->_dataHelper->isAffiliateModuleEnabled()) {
            return $this->_redirect($this->getBaseUrl());
        }
        if (!$this->_dataHelper->getConfig('affiliateplus/refer/enable')) {
            return $this->_redirect('affiliateplus/index/index');
        }
        if ($this->_accountHelper->accountNotLogin()) {
            return $this->_redirect('affiliateplus/account/login');
        }
        if ($this->_accountHelper->isNotAvailableAccount()){
            return $this->_redirect('affiliateplus/index/index');
        }

        if ($data = $this->getRequest()->getPost()) {
            $session = $this->_affiliateSession;
            if (!$data['personal_url']) {
                $this->messageManager->addError(__('Please enter a valid custom url'));
                return $this->_redirect('*/*/index');
            }
            $requestPath = trim($data['personal_url']);
            $account = $session->getAccount();
            $store = $this->_storeManager->getStore();

            $idPath = 'affiliateplus/' . $store->getId() . '/' . $account->getId();

            /* Magic fix url include '@','©','®','À'... */
            $requestPath = $this->_objectManager->create('Magento\Catalog\Model\Product\Url')->formatUrlKey($data['personal_url']);
            $this->_objectManager->get('Magento\UrlRewrite\Helper\UrlRewrite')->validateRequestPath($requestPath);
            $requestPath = $this->_dataHelper->getConfig('affiliateplus/refer/url_prefix') . $requestPath;
            $requestPath = str_replace(" ", "", $requestPath);
            /* END */

            $targetPath = $this->_getDefaultPath($store);
            $targetPath .= '/acc/';
            $targetPath .= $account->getIdentifyCode();

            $existedRequestPath = $this->getUrlRewriteModel()->getCollection()
                ->addFieldToFilter('store_id', $store->getId())
                ->addFieldToFilter('request_path', $requestPath)
                ->getFirstItem()
            ;
            if ($existedRequestPath && $existedRequestPath->getId()) {
                $this->messageManager->addError(__('This url already exists. Please choose another custom url.'));
                $session->setAffilateCustomUrl($data['personal_url']);
                return $this->_redirect('*/*/index');
            }

            $rewrite = $this->getUrlRewriteModel();
            $rewrite = $this->getUrlRewriteModel()
                ->getCollection()
                ->addFieldToFilter('store_id', $store->getId())
                ->addFieldToFilter('target_path', array("like"=>"%".$account->getIdentifyCode()."%"))
                ->getFirstItem()
            ;
            if($rewrite && $rewrite->getId()){
                $rewrite->setRequestPath($requestPath);
            } else {
//                $rewrite = $this->getUrlRewriteModel();
                $rewrite->addData(array(
                    'store_id' => $store->getId(),
                    'id_path' => $idPath,
                    'request_path' => $requestPath,
                    'target_path' => $targetPath
                ));
            }
            try {
                $rewrite->save();
                $this->messageManager->addSuccess(__('Your custom url has been saved successfully!'));
            } catch (Exception $e) {
                $this->messageManager->addError($e->getMessage());
                $session->setAffilateCustomUrl($data['personal_url']);
            }
        }
        $this->_redirect('*/*/index');
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
    protected function getUrlRewriteModel(){
        return $this->_objectManager->create('Magento\UrlRewrite\Model\UrlRewrite');
    }
}
