<?php

namespace Ktpl\Wholesaler\Observer;

use Magento\Framework\Event\ObserverInterface;

class CustomerLoginAfter implements ObserverInterface {

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
        $categoryurl=$this->_helper->Wholesalercategoryurl();
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $storeManager = $objectManager->create("\Magento\Store\Model\StoreManagerInterface");
        
        $customer = $observer->getEvent()->getCustomer();
        if ($customer->getGroupid() == 2) {
             if ($this->_storeManager->getStore()->getCode() == 'en_wholesaler') {
                $baseurl = $this->_storeManager->getStore()->getBaseUrl(); 
                //$categoryurl = $baseurl.$categoryurl;

                $redirectionUrl = $this->url->getUrl($categoryurl);
                    $this->responseFactory->create()->setRedirect($categoryurl)->sendResponse();
                    exit;
                
            }
            
        }
        
    }

}
