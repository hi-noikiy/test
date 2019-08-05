<?php

namespace Ktpl\Wholesaler\Observer;

use Magento\Framework\Event\ObserverInterface;

class Checkstore implements ObserverInterface {

    protected $_helper;
    protected $_storeManager;
    protected $responseFactory;
    protected $url;
    protected $request;

    public function __construct(\Ktpl\Wholesaler\Helper\Data $_helper,
            \Magento\Store\Model\StoreManagerInterface $storeManager,
            \Magento\Framework\App\ResponseFactory $responseFactory, 
            \Magento\Framework\App\Request\Http $request,
            \Magento\Framework\UrlInterface $url) 
    {
        $this->_helper = $_helper;
        $this->request = $request;
        $this->_storeManager = $storeManager;
        $this->responseFactory = $responseFactory;
        $this->url = $url;
    }

    public function execute(\Magento\Framework\Event\Observer $observer) {
        $action = $this->request->getFullActionName();
        $act = explode('_', $action);
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $storeManager = $objectManager->create("\Magento\Store\Model\StoreManagerInterface");
        if ($storeManager->getStore()->getCode() == 'en_wholesaler') {
            $customerSession = $objectManager->get('Magento\Customer\Model\Session');
            if ($customerSession->isLoggedIn()) {
                if ($customerSession->getCustomerGroupId() != 2) {
                    $redirectionUrl = $this->url->getUrl($this->_storeManager->getStore(2)->getBaseUrl());
                    $this->responseFactory->create()->setRedirect($redirectionUrl)->sendResponse();
                    exit;
                }
                if ($action == 'catalog_product_view' || $action == 'checkout_cart_configure' || $action == 'cms_index_index') {
                    $redirectionUrl = $this->url->getUrl($this->_helper->Wholesalercategoryurl());
                    $this->responseFactory->create()->setRedirect($redirectionUrl)->sendResponse();
                    exit;
                }
            } else {
                if ($act[0] != 'customer' && $action != 'wholesaler_index_inquiry' && $action != 'wholesaler_index_inqueryPost' && $this->request->getParam('SS-UserName') == "") {
                    $redirectionUrl = $this->url->getUrl('customer/account');
                    $this->responseFactory->create()->setRedirect($redirectionUrl)->sendResponse();
                    exit;
                }
            }
        }
    }

}
