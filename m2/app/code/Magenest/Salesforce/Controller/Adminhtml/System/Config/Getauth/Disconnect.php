<?php
/**
 * * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magenest\Salesforce\Controller\Adminhtml\System\Config\Getauth;

use Magenest\Salesforce\Model\Connector;
use Magento\Backend\App\Action;
use Magento\Config\Model\ResourceModel\Config as ConfigModel;
use Magento\Framework\App\Config\ScopeConfigInterface;

/**
 * Class GetAuth
 * @package Magenest\Salesforce\Controller\Adminhtml\System\Config\Getauth
 */
class Disconnect extends Action
{
    protected $_configModel;

    protected $_scopeConfig;

    /**
     * Disconnect constructor.
     * @param Action\Context $context
     * @param ScopeConfigInterface $scopeConfig
     * @param ConfigModel $configModel
     */
    public function __construct(
        Action\Context $context,
        ScopeConfigInterface $scopeConfig,
        ConfigModel $configModel
    ) {
        parent::__construct($context);
        $this->_configModel = $configModel;
        $this->_scopeConfig = $scopeConfig;
    }

    public function execute()
    {
        $this->_configModel->saveConfig(Connector::XML_PATH_SALESFORCE_IS_CONNECTED, 0, 'default', 0);
        $this->_configModel->saveConfig(Connector::XML_PATH_SALESFORCE_ACCESS_TOKEN, null, 'default', 0);
        $this->_configModel->saveConfig(Connector::XML_PATH_SALESFORCE_INSTANCE_URL, null, 'default', 0);
        $this->_scopeConfig->clean();
    }

    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('Magenest_Salesforce::config_salesforce');
    }
}
