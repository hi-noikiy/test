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
 * Helper Data
 */
class Data extends HelperAbstract
{
    const XML_PATH_ADMIN_EMAIL_IDENTITY = 'trans_email/ident_general';
    /**
     * @var \Magestore\Affiliateplus\Model\Tracking
     */
    protected $_trackingModel;

    /**
     * @var \Magestore\Affiliateplus\Model\Transaction
     */
    protected $_transactionModel;

    /**
     * @var \Magento\Sales\Model\Order;
     */
    protected $_salesOrderModel;

    /**
     * @param $orderItemIds
     * @return string
     */
    public function getBackendProductHtml($orderItemIds)
    {
        $backendUrl = $this->_objectManager->create('Magento\Backend\Model\Url');
        $productHtmls = array();
        $productIds = explode(',', $orderItemIds);
        foreach ($productIds as $productId) {
            $productName = $this->_productFactory->create()
                                ->load($productId)
                                ->getName();

            $productUrl = $backendUrl->getUrl('catalog/product/edit', ['_current' => true, 'id' => $productId]);

            $productHtmls[] = '<a href="' . $productUrl . '" title="' . __('View Product Details') . '">' . $productName . '</a>';
        }
        return implode('<br />', $productHtmls);
    }

    /**
     * @return bool
     */
    public function isAffiliateModuleEnabled() {
        $storeId = $this->_storeManager->getStore()->getId();
        if($this->getConfig('affiliateplus/general/enable', $storeId)){
            return true;
        }
        return false;
    }

    /**
     * Check the affiliate information is existed on Cookie or not
     * @param null $parameter
     * @return bool
     */
    public function exitedCookie($parameter=null) {
        $helperConfig = $this->_objectManager->create('Magestore\Affiliateplus\Helper\Config');
        $useCookie = $helperConfig->getActionConfig('detect_cookie');
        if (!$useCookie){
            return false;
        }
        $check = false;
        $days = $helperConfig->getActionConfig('resetclickby');
        $expiredTime = $helperConfig->getGeneralConfig('expired_time');
        $params = $this->_request->getParams();
        $link = '';
        $account = '';

        if($parameter && isset($params[$parameter]) && $params[$parameter])
            $account = $this->_accountCollectionFactory->create()
                ->addFieldToFilter('account_id', $params[$parameter])
                ->getFirstItem();

        if ($account && $account->getId())
            $params[$parameter] = $account->getIdentifyCode();

        foreach ($params as $param) {
            $link .=$param;
        }

        if ($expiredTime)
            $this->_publicCookieMetadata->setDuration(intval($expiredTime) * 86400);
        $date = New \DateTime('now', new \DateTimeZone('UTC'));
        $date->modify(-$days . 'days');
        $dateModifyReset = $date->format('Y-m-d');
        $dateNow = date('Y-m-d');
        $dateSet = $this->_cookieManager->getCookie($link);
        if ($dateModifyReset <= $dateSet && $dateSet) {
            $check = true;
        } else {
            $this->_cookieManager->setPublicCookie($link, $dateNow, $this->getPublicCookieMetadata($domain=null, $path='/'));
        }
        return $check;
    }

    /**
     * Check the affiliate is proxy or not
     * @return bool
     */
    public function isProxy() {
        $helperConfig = $this->_objectManager->create('Magestore\Affiliateplus\Helper\Config');
        $useHeader = $helperConfig->getActionConfig('detect_proxy');
        $useHostByAddr = $helperConfig->getActionConfig('detect_proxy_hostbyaddr');
        $useBankIp = $helperConfig->getActionConfig('detect_proxy_bankip');
        if ($useHeader) {
            $header = $helperConfig->getActionConfig('detect_proxy_header');
            $arrIndex = explode(',', $header);
            $headerArr = $this->_objectManager->get('Magestore\Affiliateplus\Model\System\Config\Source\Headerdetectproxy')->getOptionList();

            foreach ($arrIndex as $index) {
                if (isset($_SERVER[$headerArr[$index]])) {
                    return true;
                }
            }
        }
        if ($useBankIp) {
            $arrBankIp = explode(';', $useBankIp);
            $ip = $_SERVER['REMOTE_ADDR'];
            foreach ($arrBankIp as $bankip) {
                if (preg_match('/' . $bankip . '/', $ip, $match))
                    return true;
            }
        }
        if ($useHostByAddr) {
            $host = gethostbyaddr($_SERVER['REMOTE_ADDR']);
            if ($host != $_SERVER['REMOTE_ADDR'])
                return true;
        }
        return false;
    }

    /**
     * Check the affiliate is proxy or not
     * @return bool
     */
    public function isRobots() {
        $storeId = $this->_storeManager->getStore()->getId();
        $helperConfig = $this->_objectManager->create('Magestore\Affiliateplus\Helper\Config');
        if (!$helperConfig->getActionConfig('detect_software'))
            return false;
        if (empty($_SERVER['HTTP_USER_AGENT']))
            return true;
        if (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && $_SERVER['HTTP_X_REQUESTED_WITH'])
            return true;
        define("UNKNOWN", 0);
        define("TRIDENT", 1);
        define("GECKO", 2);
        define("PRESTO", 3);
        define("WEBKIT", 4);
        define("VALIDATOR", 5);
        define("ROBOTS", 6);

        if (!isset($_SESSION["info"]['browser'])) {

            $_SESSION["info"]['browser']['engine'] = UNKNOWN;
            $_SESSION["info"]['browser']['version'] = UNKNOWN;
            $_SESSION["info"]['browser']['platform'] = 'Unknown';

            $navigator_user_agent = ' ' . strtolower($_SERVER['HTTP_USER_AGENT']);

            if (strpos($navigator_user_agent, 'linux')) :
                $_SESSION["info"]['browser']['platform'] = 'Linux';
            elseif (strpos($navigator_user_agent, 'mac')) :
                $_SESSION["info"]['browser']['platform'] = 'Mac';
            elseif (strpos($navigator_user_agent, 'win')) :
                $_SESSION["info"]['browser']['platform'] = 'Windows';
            endif;

            if (strpos($navigator_user_agent, "trident")) {
                $_SESSION["info"]['browser']['engine'] = TRIDENT;
                $_SESSION["info"]['browser']['version'] = floatval(substr($navigator_user_agent, strpos($navigator_user_agent, "trident/") + 8, 3));
            } elseif (strpos($navigator_user_agent, "webkit")) {
                $_SESSION["info"]['browser']['engine'] = WEBKIT;
                $_SESSION["info"]['browser']['version'] = floatval(substr($navigator_user_agent, strpos($navigator_user_agent, "webkit/") + 7, 8));
            } elseif (strpos($navigator_user_agent, "presto")) {
                $_SESSION["info"]['browser']['engine'] = PRESTO;
                $_SESSION["info"]['browser']['version'] = floatval(substr($navigator_user_agent, strpos($navigator_user_agent, "presto/") + 6, 7));
            } elseif (strpos($navigator_user_agent, "gecko")) {
                $_SESSION["info"]['browser']['engine'] = GECKO;
                $_SESSION["info"]['browser']['version'] = floatval(substr($navigator_user_agent, strpos($navigator_user_agent, "gecko/") + 6, 9));
            } elseif (strpos($navigator_user_agent, "robot"))
                $_SESSION["info"]['browser']['engine'] = ROBOTS;
            elseif (strpos($navigator_user_agent, "spider"))
                $_SESSION["info"]['browser']['engine'] = ROBOTS;
            elseif (strpos($navigator_user_agent, "bot"))
                $_SESSION["info"]['browser']['engine'] = ROBOTS;
            elseif (strpos($navigator_user_agent, "crawl"))
                $_SESSION["info"]['browser']['engine'] = ROBOTS;
            elseif (strpos($navigator_user_agent, "search"))
                $_SESSION["info"]['browser']['engine'] = ROBOTS;
            elseif (strpos($navigator_user_agent, "w3c_validator"))
                $_SESSION["info"]['browser']['engine'] = VALIDATOR;
            elseif (strpos($navigator_user_agent, "jigsaw"))
                $_SESSION["info"]['browser']['engine'] = VALIDATOR;
            else {
                $_SESSION["info"]['browser']['engine'] = ROBOTS;
            }
            if ($_SESSION["info"]['browser']['engine'] == ROBOTS)
                return true;
        }
        return false;
    }

    /**
     * @return bool
     */
    public function affiliateTypeIsProfit() {
        if ($this->_objectManager->create('Magestore\Affiliateplus\Helper\Config')->getCommissionConfig('affiliate_type') == 'profit') {
            return true;
        }

        return false;
    }

    /**
     * @param null $moduleName
     * @return bool
     */
    public function isModuleOutputEnabled($moduleName = NULL)
    {
        return $this->isModuleOutputEnabled($moduleName);
    }

    /**
     * @param $productIds
     * @return string
     */
    public function getFrontendProductHtmls($productIds) {
        $productHtmls = [];
        $productIds = explode(',', $productIds);
        foreach ($productIds as $productId) {
            $product = $this->_objectManager->create('Magento\Catalog\Model\Product')->load($productId);
            $productName = $product->getName();
            $productUrl = $product->getProductUrl();
            $productHtmls[] = '<a href="' . $productUrl . '" title="' . __('View Product Details') . '">' . $productName . '</a>';
        }
        return implode('<br />', $productHtmls);
    }

    /**
     * @return bool
     */
    public function multilevelIsActive() {
//        $modules = $this->_config->getNode('modules')->children();
//        $modulesArray = (array) $modules;
//        if (isset($modulesArray['Magestore_Affiliatepluslevel']) && is_object($modulesArray['Magestore_Affiliatepluslevel'])) {
//            return $modulesArray['Magestore_Affiliatepluslevel']->is('active');
//        }

        return false;
    }

    /**
     * Get Sender information (email, name) to send email
     * @return array
     */
    public function getSenderContact() {
        $storeId = $this->_storeManager->getStore()->getId();
        $senderEmailConfiguration = [];
        $emailConfiguration = $this->getConfig('affiliateplus/email/email_sender', $storeId);
        $nameConfiguration = $this->getConfig('affiliateplus/email/name_sender', $storeId);
        $senderDefault = $this->getConfig(self::XML_PATH_ADMIN_EMAIL_IDENTITY, $storeId);
        if (!$emailConfiguration) {
            if (!$nameConfiguration) {
                $senderEmailConfiguration = $senderDefault;
            } else {
                $senderEmailConfiguration = [
                    'email' => $senderDefault['email'],
                    'name' => $nameConfiguration,
                ];
            }
        } else {
            if (!$nameConfiguration) {
                $senderEmailConfiguration = [
                    'email' => $emailConfiguration,
                    'name' => $senderDefault['name'],
                ];
            } else {
                $senderEmailConfiguration = [
                    'email' => $emailConfiguration,
                    'name' => $nameConfiguration,
                ];
            }
        }

        return $senderEmailConfiguration;
    }

    /**
     * Get Account and Program Data by Customer ID
     * @param $customerOrderId
     * @return \Magento\Framework\DataObject\
     */
    public function getAccountAndProgramData($customerOrderId){
        if($this->checkLifeTimeForOrderBackend($customerOrderId)){
            $account =  $this->checkLifeTimeForOrderBackend($customerOrderId);
            $lifeTimeAff = true;
        }
        else{
            $account = '';
            $lifeTimeAff = false;
        }
        $programData = $this->_getCheckoutSession()->getProgramData();
        if($programData){
            if($programData->getProgramId()){
                $programId = $programData->getProgramId();
                $programName = $programData->getName();
            } else {
                $programId = 0;
                $programName = 'Affiliate Program';
            }
        }else{
            $programId = '';
            $programName = '';
        }
        $accountAndProgramData = new \Magento\Framework\DataObject(array(
            'program_id' => '',
            'program_name' => '',
            'account' => $account,
            'lifetime_aff' => $lifeTimeAff,
        ));
        $accountAndProgramData->setAccount($account);
        $accountAndProgramData->setProgramId($programId);
        $accountAndProgramData->setProgramName($programName);
        $accountAndProgramData->setLifetimeAff($lifeTimeAff);
        return $accountAndProgramData;
    }

    /**
     * Check if the customer is assgned lifetime to an affiliate
     * @param $customerId
     * @return string|void
     */
    public function checkLifeTimeForOrderBackend($customerId){
        if(!$customerId){
            return;
        }
        $account = '';
        if ($this->getConfig('affiliateplus/commission/life_time_sales')) {
            $tracksCollection = $this->_getTrackingModel()->getCollection();
            $tracksCollection->addFieldToFilter('customer_id', $customerId);
            $track = $tracksCollection->getFirstItem();
            if ($track && $track->getId()) {
                $account = $this->_accountFactory->create()
                    ->setStoreId($this->_storeManager->getStore()->getId())
                    ->load($track->getAccountId());
                if($account && $account->getStatus() == 1){
                    return $account;
                }
            }
        }
        return $account;
    }

    /**
     * @param $customerId
     * @return bool
     */
    public function checkLifeTime($customerId){
        if(!$customerId){
            return false;
        }
        $isLifeTime = $this->checkLifeTimeForOrderBackend($customerId);
        if($isLifeTime){
            return true;
        }

        return false;
    }

    /**
     * Get Tracking Model
     * @return mixed
     */
    protected function _getTrackingModel(){
        if(!$this->_trackingModel){
            $this->_trackingModel = $this->_objectManager->create('Magestore\Affiliateplus\Model\Tracking');
        }
        return $this->_trackingModel;
    }

    /**
     * @return \Magento\Checkout\Model\Session
     */
    protected function _getCheckoutSession(){
        return $this->getCheckoutSession();
    }

    /**
     * @return mixed
     */
    protected function _getSalesOrderModel(){
        if(!$this->_salesOrderModel){
            $this->_salesOrderModel = $this->_objectManager->create('Magento\Sales\Model\Order');
        }
        return $this->_salesOrderModel;
    }

    /**
     * @return mixed
     */
    protected function _getAffiliateTransactionModel(){
        if(!$this->_transactionModel){
            $this->_transactionModel = $this->_objectManager->create('Magestore\Affiliateplus\Model\Transaction');
        }
        return $this->_transactionModel;
    }

    /**
     * @param $orderId
     * @return bool
     */
    public function getAffiliateInfoByOrderId($orderId){
        if($orderId) {
            $transaction = $this->_getAffiliateTransactionModel()->load($orderId, "order_id");
            if($transaction && $transaction->getId()) {
                $affiliate = $this->_accountFactory->create()
                    ->load($transaction->getAccountId());
                if($affiliate && $affiliate->getId()){
                    $info[$affiliate->getIdentifyCode()] = [
                        'index' => 1,
                        'code' => $affiliate->getIdentifyCode(),
                        'account' => $affiliate,
                    ];
                    return $info;
                }
                return false;
            }
            return false;
        }
    }


    /**
     * @param $banner
     * @param null $store
     * @return string
     */
    public function getBannerUrl($banner, $store = null) {
        if (is_null($store))
            $store = $this->_storeManager->getStore();
        $account = $this->_sessionModel->getAccount();

        $url = $this->getUrlLink($banner->getLink());

        $referParam = $this->getPersonalUrlParameter();
        $referParamValue = $account->getIdentifyCode();
        if ($this->getConfig('affiliateplus/general/url_param_value') == 2)
            $referParamValue = $account->getAccountId();
        if (strpos($url, '?')){
            $url .= '&' . $referParam . '=' . $referParamValue;
        } else{
            $url .= '?' . $referParam . '=' . $referParamValue;
        }
        if ($this->_storeManager->getDefaultStoreView() && $store->getId() != $this->_storeManager->getDefaultStoreView()->getId()){
            $url .= '&___store=' . $store->getCode();
        }
        if ($banner->getId()){
            $url .= '&bannerid=' . $banner->getId();
        }

        $urlParams = new \Magento\Framework\DataObject([
            'helper' => $this,
            'params' => [],
        ]
        );
        $this->_eventManager->dispatch('affiliateplus_helper_get_banner_url', [
            'banner' => $banner,
            'url_params' => $urlParams,
        ]
    );
        $params = $urlParams->getParams();
        if (count($params))
            $url .= '&' . http_build_query($urlParams->getParams(), '', '&');
        return $url;
    }

    /**
     * get Personal Url Parameter
     *
     * @return string
     */
    public function getPersonalUrlParameter() {
        $paramArray = explode(',', $this->getConfig('affiliateplus/refer/url_param_array'));
        $referParam = $paramArray[count($paramArray) - 1];
        if (!$referParam && ($referParam == ''))
            $referParam = 'acc';
        return $referParam;
    }

    /**
     * @param $url
     * @param null $store
     * @return string
     */
    public function addAccToUrl($url, $store = null) {
        if (is_null($store))
            $store = $this->_storeManager->getStore();
        $account = $this->_sessionModel->getAccount();
        $url = $this->getUrlLink($url);
        $referParam = $this->getPersonalUrlParameter();
        $referParamValue = $account->getIdentifyCode();
        if ($this->getConfig('affiliateplus/general/url_param_value') == 2)
            $referParamValue = $account->getAccountId();

        if (strpos($url, '?'))
            $url .= '&' . $referParam . '=' . $referParamValue;
        else
            $url .= '?' . $referParam . '=' . $referParamValue;

        if ($this->_storeManager->getDefaultStoreView() && $store->getId() != $this->_storeManager->getDefaultStoreView()->getId())
            $url .= '&___store=' . $store->getCode();

        $urlParams = new \Magento\Framework\DataObject([
            'helper' => $this,
            'params' => [],
        ]);
        $this->_eventManager->dispatch('affiliateplus_helper_add_acc_to_url', [
            'url_params' => $urlParams,
        ]);
        $params = $urlParams->getParams();
        if (count($params))
            $url .= '&' . http_build_query($urlParams->getParams(), '', '&');
        return $url;
    }

    /**
     * @param $url
     * @return string
     */
    public function getUrlLink($url) {
        if (!preg_match("/^http\:\/\/|https\:\/\//", $url)){
            return $this->_urlBuilder->getUrl() . trim($url, '/');
        }
        return rtrim($url, '/');
    }

    /**
     * @return int
     */
    public function getStoreId(){
        return $this->_storeManager->getStore()->getId();
    }

    /**
     * Edit by Jack
     * @param $couponBySession
     * @return bool
     */
    public function isAllowUseCoupon($couponBySession) {
        if(!$this->isModuleEnabled('Magestore_Affiliatepluscoupon')){
            return false;
        }
        $isEnableCouponPlugin = $this->getConfig('affiliateplus/coupon/enable');
        if($isEnableCouponPlugin == 0){
            return false;
        }
        $accountInfo = $this->_objectManager->create('Magestore\Affiliatepluscoupon\Model\Coupon')->getAccountByCoupon($couponBySession);
        if(!$accountInfo || $accountInfo && $accountInfo->getId() && $accountInfo->getStatus() == 2){
            return false;
        }
        return true;
    }

    /**
     * @param $orderId
     * @param $couponCodeBySession
     * @param $dataProcessing
     * @return bool
     */
    public function isEditOrder($orderId, $couponCodeBySession, $dataProcessing){
        if(isset($dataProcessing['customer_id'])){
            $customerId = $dataProcessing['customer_id'];
        }else{
            $customerId = '';
        }
        if(isset($dataProcessing['program_id'])){
            $programId = $dataProcessing['program_id'];
        }else{
            $programId = '';
        }
        if(isset($dataProcessing['program_name'])){
            $programName = $dataProcessing['program_name'];
        }else{
            $programName = '';
        }
        if($orderId && !$couponCodeBySession && ($programId != '' || $programName != '')){
            return true;
        }
        if((!$orderId && $customerId && !$couponCodeBySession)){
            return true;
        }
        return false;
    }

    /**
     * @param $orderId
     * @return bool
     */
    public function showAffiliateDiscount($orderId){
        if($orderId){
            $currentOrderEdit = $this->_getSalesOrderModel()->load($orderId);
            $baseAffiliateDiscount = $currentOrderEdit->getAffiliateplusDiscount();
            $currentShippingMethod = $currentOrderEdit->getData('shipping_method');
        }else{
            $currentShippingMethod = $this->_backendQuoteSession
                ->getQuote()
                ->getShippingAddress()->getShippingMethod();
        }
        if(!$currentShippingMethod) {
            $currentShippingMethod = '';
            return false;
        }

        $html = $this->_layout
            ->createBlock('\Magento\Framework\View\Element\Template')
            ->setId($currentShippingMethod)
            ->setTemplate('Magestore_Affiliateplus::js/updatediscount.phtml')
            ->toHtml();
        \Magento\Framework\App\ObjectManager::getInstance()->create(
            'Magento\Framework\App\ResponseInterface'
        )->setBody($html);
        /* end reload session */
    }
}
