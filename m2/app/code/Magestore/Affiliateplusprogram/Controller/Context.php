<?php

/**
 * Magestore
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
 * @package     Magestore_Affiliateplusprogram
 * @copyright   Copyright (c) 2012 Magestore (http://www.magestore.com/)
 * @license     http://www.magestore.com/license-agreement.html
 */

namespace Magestore\Affiliateplusprogram\Controller;

use Magento\Framework\Controller\ResultFactory;

/**
 * Class Context
 * @package Magestore\Affiliateplusprogram\Controller
 */
class Context extends \Magento\Framework\App\Action\Context
{

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $_storeManager;
    /**
     * @var \Magento\Framework\Registry
     */
    protected $_coreRegistry;
    /**
     * @var \Magento\Framework\ObjectManagerInterface
     */
    protected $_objectManager;
    /**
     * @var \Magestore\Affiliateplus\Helper\Account $accountHelper
     */
    protected $_accountHelper;
    /**
     * @var \Magestore\Affiliateplusprogram\Helper\Data $helper
     */
    protected $_helper;
    /**
     * @var \Magento\Framework\View\Result\PageFactory
     */
    protected $_pageFactory;
    /**
     * @var \Magestore\Affiliateplusprogram\Model\ProgramFactory
     */
    protected $_programFactory;
    /**
     * @var \Magestore\Affiliateplusprogram\Model\AccountFactory
     */
    protected $_programAccountFactory;
    /**
     * @return \Magestore\Affiliateplusprogram\Model\JoinedFactory
     */
    protected $_programJoinedFactory;

    /**
     * Context constructor.
     * @param \Magento\Framework\App\RequestInterface $request
     * @param \Magento\Framework\App\ResponseInterface $response
     * @param \Magento\Framework\ObjectManagerInterface $objectManager
     * @param \Magento\Framework\Event\ManagerInterface $eventManager
     * @param \Magento\Framework\UrlInterface $url
     * @param \Magento\Framework\App\Response\RedirectInterface $redirect
     * @param \Magento\Framework\App\ActionFlag $actionFlag
     * @param \Magento\Framework\App\ViewInterface $view
     * @param \Magento\Framework\Message\ManagerInterface $messageManager
     * @param \Magento\Framework\Controller\Result\RedirectFactory $resultRedirectFactory
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Magento\Framework\Registry $registry
     * @param ResultFactory $resultFactory
     */
    public function __construct(
        \Magento\Framework\App\RequestInterface $request,
        \Magento\Framework\App\ResponseInterface $response,
        \Magento\Framework\ObjectManagerInterface $objectManager,
        \Magento\Framework\Event\ManagerInterface $eventManager,
        \Magento\Framework\UrlInterface $url,
        \Magento\Framework\App\Response\RedirectInterface $redirect,
        \Magento\Framework\App\ActionFlag $actionFlag,
        \Magento\Framework\App\ViewInterface $view,
        \Magento\Framework\Message\ManagerInterface $messageManager,
        \Magento\Framework\Controller\Result\RedirectFactory $resultRedirectFactory,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Framework\Registry $registry,
        \Magestore\Affiliateplus\Helper\Account $accountHelper,
        \Magestore\Affiliateplusprogram\Helper\Data $helper,
        \Magento\Framework\View\Result\PageFactory $pageFactory,
        \Magestore\Affiliateplusprogram\Model\ProgramFactory $programFactory,
        \Magestore\Affiliateplusprogram\Model\AccountFactory $programAccountFactory,
        \Magestore\Affiliateplusprogram\Model\JoinedFactory $programJoinedFactory,
        ResultFactory $resultFactory
    )
    {
        parent::__construct(
            $request,
            $response,
            $objectManager,
            $eventManager,
            $url,
            $redirect,
            $actionFlag,
            $view,
            $messageManager,
            $resultRedirectFactory,
            $resultFactory
        );
        $this->_storeManager = $storeManager;
        $this->_coreRegistry = $registry;
        $this->_objectManager =  $objectManager;
        $this->_accountHelper = $accountHelper;
        $this->_helper = $helper;
        $this->_pageFactory = $pageFactory;
        $this->_programFactory = $programFactory;
        $this->_programAccountFactory = $programAccountFactory;
        $this->_programJoinedFactory = $programJoinedFactory;
    }

    /**
     * @return \Magento\Store\Model\StoreManagerInterface
     */
    public function getStoreManager()
    {
        return $this->_storeManager;
    }

    /**
     * @return \Magento\Framework\Registry
     */
    public function getRegistry()
    {
        return $this->_coreRegistry;
    }

    /**
     * @return \Magento\Framework\ObjectManagerInterface
     */
    public function getObjectManager()
    {
        return $this->_objectManager;
    }
    /**
     * @return \Magestore\Affiliateplusprogram\Helper\Account
     */
    public function getAccountHelper()
    {
        return $this->_accountHelper;
    }
    /**
     * @return \Magestore\Affiliateplusprogram\Helper\Data
     */
    public function getProgramHelper()
    {
        return $this->_helper;
    }
    /**
     * @return \Magento\Framework\View\Result\PageFactory
     */
    public function getPageFactory()
    {
        return $this->_pageFactory;
    }
    /**
     * @return \Magestore\Affiliateplusprogram\Model\ProgramFactory
     */
    public function getProgramFactory()
    {
        return $this->_programFactory;
    }

    /**
     * @return \Magestore\Affiliateplusprogram\Model\AccountFactory
     */
    public function getProgramAccountFactory()
    {
        return $this->_programAccountFactory;
    }

    /**
     * @return \Magestore\Affiliateplusprogram\Model\JoinedFactory
     */
    public function getProgramJoinedFactory()
    {
        return $this->_programJoinedFactory;
    }
}