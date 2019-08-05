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
namespace Magestore\Affiliateplus\Controller\Banner;

/**
 * Action Index
 */
class Image extends \Magestore\Affiliateplus\Controller\AbstractAction
{
    /**
     * Execute action
     */
    public function execute()
    {

        if (!$this->_dataHelper->isAffiliateModuleEnabled()) {
            return $this->_redirect($this->getBaseUrl());
        }
        $banner_id = $this->getRequest()->getParam('id');
        $banner = $this->_objectManager->create('Magestore\Affiliateplus\Model\Banner')->load($banner_id);

        $request = $this->getRequest();
        $ipAddress = $request->getClientIp();
        $account_id = $this->getRequest()->getParam('account_id');
        $store_id = $this->getRequest()->getParam('store_id');
        $customer = $this->_sessionCustomer->getCustomer();
        $account = $this->_objectManager->create('Magestore\Affiliateplus\Model\Account')->setStoreViewId($store_id)->load($account_id);
        $date = date('Y-m-d');
        if(!$this->_dataHelper->isRobots()){
            if(($account->getStatus()==1) && ($account->getCustomerId()!= $customer->getId())){
                $domain = isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : '';
                $actionModel = $this->_objectManager->create('Magestore\Affiliateplus\Model\Action')->loadExist($account_id,$banner_id,1,$store_id,$date,$ipAddress,$domain);
                if(!$actionModel->getId()){
                    $actionModel->setData('account_id',$account_id);
                    $actionModel->setData('banner_id',$banner_id);
                    $actionModel->setData('account_email', $account->getEmail());
                    $actionModel->setData('type',1);
                    if ($directLink = $this->getRequest()->getParam('affiliateplus_direct_link')) {
                        $actionModel->setData('direct_link', $directLink);
                    }
                    $actionModel->setData('banner_title', $banner->getTitle());

                    $actionModel->setData('ip_address',$ipAddress);
                    $actionModel->setData('store_id',$store_id);
                    $actionModel->setData('created_date',date("Y-m-d"));
                    $actionModel->setData('domain',$domain);
                    if ($domain = $actionModel->getDomain()) {
                        $actionModel->setReferer($actionModel->refineDomain($domain));
                    }
                    $actionModel->setData('updated_time',date("Y-m-d H:i:s"));
                    $actionModel->setData('totals',1);
                    $resets = $this->_objectManager->get('Magestore\Affiliateplus\Helper\Config')->getActionConfig('resetclickby');
                    $col = $this->_objectManager->create('Magestore\Affiliateplus\Model\Action')->getCollection()
                        ->addFieldToFilter('account_id',$account_id)
                        ->addFieldToFilter('banner_id',$banner_id)
                        ->addFieldToFilter('ip_address',$ipAddress)
                        ->addFieldToFilter('store_id',$store_id)
                        ->addFieldToFilter('is_unique',1)
                        ->addFieldToFilter('type',1)
                    ;
                    if ($resets) {
                        $date = New \DateTime();
                        $date->modify(-$resets . 'days');
                        $col->addFieldToFilter('created_date', ['from' => $date->format('Y-m-d')]);
                    }
                    if($col->getSize()==0){
                        if(!$this->detectCookie()){
                            if(!$this->_dataHelper->isProxy())
                                $actionModel->setData('is_unique',1);
                        }
                    }
                    $actionModel->setId(null)
                        ->save();
                    $this->_eventManager->dispatch('affiliateplus_action_prepare_create_transaction',[
                        'affiliateplus_action'	=>  $actionModel,
                        'controller_action'     =>  $this
                    ]
                    );


                }else{
                    if($banner->getStatus()==1){
                        $actionModel->setTotals($actionModel->getTotals()+1);
                        $actionModel->setData('updated_time',date("Y-m-d H:i:s"));
                        $actionModel->save();
                    }
                }
            }
        }
        if ($this->getRequest()->getParam('type') == 'javascript') {
            return ;
        }
        if($banner->getSourceFile()){
            $bannerSrc = $this->getBaseUrlMedia().'affiliateplus/banner/'.$banner->getSourceFile();
            $fileext = $ext = pathinfo($banner->getSourceFile(), PATHINFO_EXTENSION);
            $mime = '';
            switch ($fileext){
                case 'swf':
                    $mime = 'application/x-shockwave-flash';
                    break;
                case 'jpg':
                case 'JPG':
                case 'jpeg':
                case 'JPEG':
                    $mime = 'image/jpeg';
                    break;
                case 'gif':
                case 'GIF':
                    $mime = 'image/gif';
                    break;
                case 'png':
                case 'PNG':
                    $mime = 'image/png';
                    break;
            }

//            $filesize = $this->_objectManager->get('Magento\Framework\File\Size')->getFileSizeInMb($bannerSrc);
            header("Content-Type: ".$mime,true);
//            header("Content-Length: ".$filesize,true);
            header("Accept-Ranges: bytes",true);
            header("Connection: keep-alive",true);
            $this->getResponse()->setBody(file_get_contents($bannerSrc));
        } else {
            header('Content-Type: text/javascript');
        }
    }

    /**
     * @return bool
     */
    public function detectCookie()
    {
        if (!$this->_dataHelper->isAffiliateModuleEnabled()) {
            return $this->_redirect($this->getBaseUrl());
        }

        $expiredTime = $this->_objectManager->get('Magestore\Affiliateplus\Helper\Config')->getGeneralConfig('expired_time');
        $cookie = $this->_objectManager->get('Magento\Framework\Session\Config');
        if ($expiredTime)
            $cookie->setCookieLifetime(intval($expiredTime)*86400);
        if($this->_dataHelper->getConfig('affiliateplus/action/detect_cookie')){
            if(!$cookie->get('cpm')){
                $cookie->set('cpm',1);
                return false;
            }else{
                return true;
            }
        }
        return false;
    }

}
