<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2018 Amasty (https://www.amasty.com)
 * @package Amasty_SecurityAuth
 */


namespace Amasty\SecurityAuth\Controller\Adminhtml\Securityauth;

use Amasty\SecurityAuth\Controller\Adminhtml\Auth as AuthAction;
use Magento\Backend\App\Action\Context;
use Magento\Framework\Registry;
use Magento\Framework\View\Result\PageFactory;

class Verify extends AuthAction
{
    /**
     * @var PageFactory
     */
    protected $resultPageFactory;

    /**
     * @var Registry
     */
    protected $coreRegistry;

    /**
     * @var \Magento\Framework\App\RequestInterface
     */
    protected $_request;

    /**
     * @var \Amasty\SecurityAuth\Model\AuthRepository
     */
    protected $authRepository;

    /**
     * @var \Amasty\SecurityAuth\Helper\Data
     */
    protected $helper;

    /**
     * @var \Magento\Framework\Json\Helper\Data
     */
    protected $jsonHelper;

    /**
     * @param Context     $context
     * @param Registry    $coreRegistry
     * @param PageFactory $resultPageFactory
     */
    public function __construct(
        Context $context,
        Registry $coreRegistry,
        PageFactory $resultPageFactory,
        \Amasty\SecurityAuth\Model\AuthRepository $authRepository,
        \Amasty\SecurityAuth\Helper\Data $helper,
        \Magento\Framework\Json\Helper\Data $jsonHelper
    ) {
        parent::__construct($context);
        $this->_request = $context->getRequest();
        $this->resultPageFactory = $resultPageFactory;
        $this->coreRegistry = $coreRegistry;
        $this->authRepository = $authRepository;
        $this->helper = $helper;
        $this->jsonHelper = $jsonHelper;
    }

    /**
     * Index action
     *
     * @return \Magento\Backend\Model\View\Result\Page
     */
    public function execute()
    {
        $secret = $this->_request->getParam('secret');
        $code = $this->_request->getParam('code', null);
        $valid = $this->helper->verifyCode(
            $secret,
            $code
        );

        $this->getResponse()->setBody($this->jsonHelper->jsonEncode(['result' =>$valid]));
    }
}
