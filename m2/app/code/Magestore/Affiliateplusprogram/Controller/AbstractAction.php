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
 * @package     Magestore_Affiliateplusprogram
 * @copyright   Copyright (c) 2012 Magestore (http://www.magestore.com/)
 * @license     http://www.magestore.com/license-agreement.html
 */
namespace Magestore\Affiliateplusprogram\Controller;


/**
 * Class AbstractAction
 * @package Magestore\Affiliateplusprogram\Controller\Adminhtml
 */
abstract class AbstractAction extends \Magento\Framework\App\Action\Action
{
    /**
     * @var \Magento\Framework\View\Result\PageFactory
     */
    protected $_resultPageFactory;
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
     * AbstractAction constructor.
     * @param Context $context
     * @param \Magento\Framework\View\Result\PageFactory $resultPageFactory
     */
    public function __construct(
        \Magestore\Affiliateplusprogram\Controller\Context $context
        /* \Magento\Framework\View\Result\PageFactory $resultPageFactory */
    )
    {
        /* $this->_resultPageFactory = $resultPageFactory; */
        $this->_resultPageFactory = $context->getPageFactory();
        $this->_storeManager = $context->getStoreManager();
        $this->_coreRegistry = $context->getRegistry();
        $this->_objectManager = $context->getObjectManager();
        $this->_accountHelper = $context->getAccountHelper();
        $this->_helper = $context->getProgramHelper();
        $this->_pageFactory = $context->getPageFactory();
        $this->_programFactory = $context->getProgramFactory();
        $this->_programAccountFactory = $context->getProgramAccountFactory();
        $this->_programJoinedFactory = $context->getProgramJoinedFactory();
        parent::__construct($context);
    }

    /**
     * @param $model
     * @return mixed
     */
    public function getModel($model){
        return $this->_objectManager->create($model);
    }
}
