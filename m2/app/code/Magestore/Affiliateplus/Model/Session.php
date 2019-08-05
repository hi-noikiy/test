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
 * Customer session model
 * @method string getNoReferer()
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class Session extends \Magento\Framework\Session\SessionManager
{
    /**
     * @var \Magento\Framework\Event\ManagerInterface
     */
    protected $_eventManager;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $_storeManager;
    /**
     * @var AccountFactory
     */
    protected $_accountFactory;
    /**
     * @var \Magento\Customer\Model\Session
     */
    protected $_sessionCustomer;
    /**
     * @var \Magento\Framework\ObjectManagerInterface
     */
    protected $_objectManager;

    /**
     * Account model
     *
     * @var \Magestore\Affiliateplus\Model\Account
     */
    protected $_accountModel;

    /**
     * Session constructor.
     * @param \Magento\Framework\App\Request\Http $request
     * @param \Magento\Framework\Session\SidResolverInterface $sidResolver
     * @param \Magento\Framework\Session\Config\ConfigInterface $sessionConfig
     * @param \Magento\Framework\Session\SaveHandlerInterface $saveHandler
     * @param \Magento\Framework\Session\ValidatorInterface $validator
     * @param \Magento\Framework\Session\StorageInterface $storage
     * @param \Magento\Framework\Stdlib\CookieManagerInterface $cookieManager
     * @param \Magento\Framework\Stdlib\Cookie\CookieMetadataFactory $cookieMetadataFactory
     * @param \Magento\Framework\App\State $appState
     * @param \Magento\Framework\ObjectManagerInterface $objectManager
     * @param \Magento\Customer\Model\Session $sessionCustomer
     * @param AccountFactory $accountFactory
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Magestore\Affiliateplus\Helper\Config $configHelper
     * @param \Magento\Framework\Event\ManagerInterface $eventManager
     */
    public function __construct(
        \Magento\Framework\App\Request\Http $request,
        \Magento\Framework\Session\SidResolverInterface $sidResolver,
        \Magento\Framework\Session\Config\ConfigInterface $sessionConfig,
        \Magento\Framework\Session\SaveHandlerInterface $saveHandler,
        \Magento\Framework\Session\ValidatorInterface $validator,
        \Magento\Framework\Session\StorageInterface $storage,
        \Magento\Framework\Stdlib\CookieManagerInterface $cookieManager,
        \Magento\Framework\Stdlib\Cookie\CookieMetadataFactory $cookieMetadataFactory,
        \Magento\Framework\App\State $appState,
        \Magento\Framework\ObjectManagerInterface $objectManager,
        \Magento\Customer\Model\Session $sessionCustomer,
        \Magestore\Affiliateplus\Model\AccountFactory $accountFactory,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Framework\Event\ManagerInterface $eventManager
    ) {
        $this->_objectManager = $objectManager;
        $this->_sessionCustomer = $sessionCustomer;
        $this->_accountFactory = $accountFactory;
        $this->_storeManager = $storeManager;
        $this->_eventManager = $eventManager;
        parent::__construct(
            $request,
            $sidResolver,
            $sessionConfig,
            $saveHandler,
            $validator,
            $storage,
            $cookieManager,
            $cookieMetadataFactory,
            $appState
        );

        $this->_eventManager->dispatch('account_session_init', ['account_session' => $this]);
    }

    /**
     * @return mixed
     */
    public function getAccount(){

        $storeId = $this->_objectManager->get('Magento\Framework\App\RequestInterface')->getParams('store');
        if (!$this->_accountModel){
            $customerId = $this->_sessionCustomer->getCustomerId();
            $account =  $this->_accountFactory->create()
                ->setStoreViewId($storeId);
            if ($this->_getConfigHelper()->getSharingConfig('balance') == 'global')
            {
                $account->setBalanceIsGlobal(true);
            }
            if ($customerId) {
                $account->loadByCustomerId($customerId);
            }
            $this->_accountModel = $account;
        }
        return $this->_accountModel;
    }
    /**
     * @return bool
     */
    public function isRegistered(){

        if ($this->getAccount() && $this->getAccount()->getId())
            return true;
        return false;
    }

    /**
     * @return bool
     */
    public function isLoggedIn(){
        if ($this->isRegistered()){
            if ($this->getAccount()->getStatus() == '1')
                return true;
        }
         return false;
    }

    protected function _getConfigHelper(){
        return $this->_objectManager->create('Magestore\Affiliateplus\Helper\Config');
    }

    public function getCustomer(){
        return $this->_sessionCustomer->getCustomer();
    }
}
