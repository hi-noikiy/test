<?php
namespace Potato\AddressAutocomplete\Block;

use Magento\Framework\View\Element\Template;
use Potato\AddressAutocomplete\Model\Config;
use Magento\Directory\Helper\Data as DirectoryHelper;

/**
 * Class Init
 */
class Init extends Template
{
    /** @var Config  */
    protected $config;

    /** @var DirectoryHelper  */
    protected $directoryHelper;

    /**
     * Init constructor.
     * @param Template\Context $context
     * @param Config $config
     * @param DirectoryHelper $directoryHelper
     * @param array $data
     */
    public function __construct(
        Template\Context $context,
        Config $config,
        DirectoryHelper $directoryHelper,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->config = $config;
        $this->directoryHelper = $directoryHelper;
    }

    /**
     * @return bool
     */
    public function canShow()
    {
        $isEnabled = $this->config->isEnabled();
        $apiKey = $this->config->getGooglePlacesApiKey();
        return $isEnabled && $apiKey;
    }

    /**
     * @return string
     */
    public function getLocaleCode()
    {
        return $this->config->getLocaleCode();
    }

    /**
     * @return string
     */
    public function getRegionConfig()
    {
        return $this->directoryHelper->getRegionJson();
    }
    
    /**
     * @return Config
     */
    public function getConfig()
    {
        return $this->config;
    }
}