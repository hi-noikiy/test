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
namespace Magestore\Affiliateplus\Model;

/**
 * Model Account
 */
class Action extends AbtractModel
{


    /**
     * Model construct that should be used for object initialization
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('Magestore\Affiliateplus\Model\ResourceModel\Action');
    }

    /**
     * @param $accountId
     * @param $bannerId
     * @param $type
     * @param $storeId
     * @param $date
     * @param $ip
     * @param $domain
     * @return $this
     */
    public function loadExist($accountId, $bannerId, $type, $storeId, $date, $ip, $domain) {
        $collection = $this->getCollection()
            ->addFieldToFilter('account_id', $accountId)
            ->addFieldToFilter('banner_id', $bannerId)
            ->addFieldToFilter('type', $type)
            ->addFieldToFilter('store_id', $storeId)
            ->addFieldToFilter('created_date', $date)
            ->addFieldToFilter('ip_address', $ip)
            ->addFieldToFilter('domain', $domain)
        ;
        if ($collection->getSize()) {
            return $this->load($collection->getFirstItem()->getId());
        }
        return $this;
    }

    /**
     * @param $ipAddress
     * @param $account_id
     * @param $domain
     * @param $banner_id
     * @param $type
     * @param null $storeId
     * @return int
     */
    public function checkIpClick($ipAddress, $account_id, $domain, $banner_id, $type, $storeId = NULL) {
        $days = $this->_helperConfig->getActionConfig('resetclickby');
        if (!$storeId)
            $storeId = $storeId = $this->_storeManager->getStore()->getId();
        $collection = $this->getCollection()
            ->addFieldToFilter('type', $type)
            ->addFieldToFilter('account_id', $account_id)
            ->addFieldToFilter('domain', $domain)
            ->addFieldToFilter('banner_id', $banner_id)
            ->addFieldToFilter('ip_address', $ipAddress)
            ->addFieldToFilter('store_id', $storeId)
            ->addFieldToFilter('is_unique', 1);
        if ($days) {
            $date = New \DateTime('now', new \DateTimeZone('UTC'));
            $date->modify(-$days . 'days');
            $collection->addFieldToFilter('created_date', ['from' => $date->format('Y-m-d')]);
        }
        if ($collection->getSize()) {
            return 0;
        }
        return 1;
    }

    /**
     * @param $accountId
     * @param $bannerId
     * @param $type
     * @param $storeId
     * @param $isUnique
     * @param $ipAddress
     * @param $domain
     * @param $landing_page
     * @return Action
     */
    public function saveAction($accountId, $bannerId, $type, $storeId, $isUnique, $ipAddress, $domain, $landing_page) {
        $date = New \DateTime('now', new \DateTimeZone('UTC'));
        $collection = $this->getCollection()
            ->addFieldToFilter('account_id', $accountId)
            ->addFieldToFilter('banner_id', $bannerId)
            ->addFieldToFilter('type', $type)
            ->addFieldToFilter('store_id', $storeId)
            ->addFieldToFilter('domain', $domain)
            ->addFieldToFilter('landing_page', $landing_page)
            ->addFieldToFilter('ip_address', $ipAddress)
            ->addFieldToFilter('created_date', $date->format('Y-m-d'));
        if ($collection->getSize()) {
            $action = $collection->getFirstItem();
        } else {
            $action = $this;
        }
        $now = new \DateTime();
        $action->setAccountId($accountId)
            ->setBannerId($bannerId)
            ->setType($type)
            ->setIpAddress($ipAddress)
            ->setTotals($action->getTotals() + 1)
            ->setCreatedDate($date->format('Y-m-d'))
            ->setUpdatedTime($now)
            ->setDomain($domain)
            ->setLandingPage($landing_page)
            ->setStoreId($storeId);
        if ($isUnique)
            $action->setIsUnique($isUnique);
        $account = $this->getModel('Magestore\Affiliateplus\Model\Account')->load($accountId);
        $action->setAccountEmail($account->getEmail());
        if ($bannerId) {
            $banner = $this->getModel('Magestore\Affiliateplus\Model\Banner')->load($bannerId);
            $action->setBannerTitle($banner->getTitle());
        }

        try {
            $directLink = $this->_objectManager->create('Magento\Framework\App\RequestInterface')->getParam('affiliateplus_direct_link');
            if ($directLink) {
                $action->setDirectLink($directLink);
            }
            if ($domain = $action->getDomain()) {
                $action->setReferer($this->refineDomain($domain));
            }
            $action->save();
        } catch (\Exception $e) {
        }
        return $action;
    }

    /**
     * @param $domain
     * @return string
     */
    public function refineDomain($domain) {
        $parseUrl = parse_url(trim($domain));
      //  $domain = trim($parseUrl['host'] ? $parseUrl['host'] : array_shift(explode('/', $parseUrl['path'], 2)));
        if($parseUrl && isset($parseUrl['host']) && $parseUrl['host']){
            $domain = $parseUrl['host'];
        }else{
            $path_array = explode('/', $parseUrl['path'], 2); // need to create a temp array variable
            $domain = array_shift($path_array);
        }
        return $domain;
    }
}
