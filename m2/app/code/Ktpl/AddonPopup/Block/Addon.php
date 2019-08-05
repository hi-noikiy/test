<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2018 Amasty (https://www.amasty.com)
 * @package Amasty_Promo
 */


namespace Ktpl\AddonPopup\Block;

use Magento\Framework\View\Element\Template;

class Addon extends \Magento\Framework\View\Element\Template
{
    const POPUP_ONE_BY_ONE = 0;
    const POPUP_MULTIPLE = 1;
    protected $_registry;
    protected $_productloader; 
    protected $_pricedata; 

    public function __construct(
        Template\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Pricing\Helper\Data $pricedata,
        \Magento\Catalog\Model\ProductFactory $_productloader,
        array $data = [])
    {
    	 $this->_registry = $registry;
    	 $this->_productloader = $_productloader;
    	 $this->_productloader = $_productloader;
        parent::__construct($context, $data);
        
    }
    
    public function getCrossSellProducts()
    {      

        $currentProdcut= $this->_registry->registry('current_product');
        return $crossSellProducts=$currentProdcut->getCrossSellProducts();
        
    } 

    public function getCrossSellProductById($id){
    		return $this->_productloader->create()->load($id);
    }

}
