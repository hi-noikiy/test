<?php

namespace Ktpl\Wholesaler\Block;

use Magento\Framework\View\Element\Template;

/**
 * Main contact form block
 *
 * @api
 * @since 100.0.2
 */
class Tier extends Template {

    /**
     *
     * @var \Magento\Framework\Pricing\Helper\Data
     */
    protected $_priceHelper;
    protected $_cart;
    protected $_jsonHelper;

    /**
     * 
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param \Magento\Checkout\Model\CartFactory $cart
     * @param \Magento\Framework\Json\Helper\Data $jsonHelper
     * @param array $data
     */
    public function __construct(Template\Context $context, \Magento\Checkout\Model\Cart $cart, \Magento\Framework\Json\Helper\Data $jsonHelper, \Magento\Framework\Pricing\Helper\Data $priceHelper
    , array $data = []) {

        $this->_priceHelper = $priceHelper;
        $this->_cart = $cart;
        $this->_jsonHelper = $jsonHelper;

        parent::__construct($context, $data);
        $this->_isScopePrivate = true;
    }
    
    public function getSubtotal() {
        return $this->_cart->getQuote()->getSubtotal() ? $this->_cart->getQuote()->getSubtotal() : 0;
    }

    public function getPriceHelper() {
        return $this->_priceHelper;
    }

    public function getDiscounts() {
        return ($this->_jsonHelper->jsonDecode($this->_scopeConfig->getValue('ktpl_wholesaler_section/wholesale/wholesaler_discount', \Magento\Store\Model\ScopeInterface::SCOPE_STORE)));
    }

}
