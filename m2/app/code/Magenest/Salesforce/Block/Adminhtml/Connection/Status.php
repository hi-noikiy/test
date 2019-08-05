<?php
namespace Magenest\Salesforce\Block\Adminhtml\Connection;

use Magento\Backend\Block\Template;
use Magento\Config\Model\ResourceModel\Config;
use Magento\Framework\App\Cache\TypeListInterface;
use Magenest\Salesforce\Model\Connector;

class Status extends Template
{
    /**
     * @var Config
     */
    protected $config;

    /**
     * Status constructor.
     * @param Template\Context $context
     * @param Connector $connector
     * @param Config $config
     * @param array $data
     */
    public function __construct(
        Template\Context $context,
        Connector $connector,
        Config $config,
        array $data = []
    ) {
        $this->config = $config;
        $this->connector = $connector;
        parent::__construct($context, $data);
    }

    protected $connector;

    /**
     * Set Template
     *
     * @var string
     */
    protected $_template = 'system/config/connection/status.phtml';

    protected $_isConnected = 'salesforcecrm/config/is_connected';

    public function isConnected()
    {
        $isConnected = $this->_scopeConfig->getValue($this->_isConnected);
        if ($isConnected === null) {
            $this->config->saveConfig($this->_isConnected, '0', 'default', 0);
            $this->_scopeConfig->clean();
            return 0;
        } elseif ($isConnected == '0') {
            return 1;
        } else {
            return 2;
        }
    }

    public function checkAccess()
    {
        if (!$this->_scopeConfig->getValue(Connector::XML_PATH_SALESFORCE_IS_CONNECTED)) {
            return true;
        }
        try {
            $this->connector->getAccessToken();
            return true;
        } catch (\Exception $e) {
            $this->config->saveConfig(Connector::XML_PATH_SALESFORCE_IS_CONNECTED, '0', 'default', 0);
            $this->_scopeConfig->clean();
            return false;
        }
    }
}
