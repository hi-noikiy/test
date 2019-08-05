<?php
namespace Ktpl\CustomizeConfigurable\Block;

use Magento\Framework\View\Element\Template;

class ConfigurablePopup extends \Magento\Framework\View\Element\Template
{
	public $_storeManager;
	protected $_registry; 
	public function __construct(
    	\Magento\Framework\View\Element\Template\Context $context,
    	\Magento\Store\Model\StoreManagerInterface $storeManager,
    	 \Magento\Framework\Registry $registry
      
    ){
    			$this->_storeManager=$storeManager;
    			$this->_registry = $registry;

				parent::__construct($context);
	}

	public function getBaseUrl()
	{
		return $this->_storeManager->getStore()->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_MEDIA);
	}

	public function getCurrentProduct()
    {        
        return $this->_registry->registry('current_product');
    }
}

?>