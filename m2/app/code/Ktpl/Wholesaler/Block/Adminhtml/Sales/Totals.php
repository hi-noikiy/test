<?php

namespace Ktpl\Wholesaler\Block\Adminhtml\Sales;

class Totals extends \Magento\Framework\View\Element\Template
{

    /**
     * @var \Ktpl\Wholesaler\Helper\Data
     */
    protected $_dataHelper;
   

    /**
     * @var \Magento\Directory\Model\Currency
     */
    protected $_currency;
    protected $factory;

    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Ktpl\Wholesaler\Helper\Data $dataHelper,
        \Magento\Framework\DataObject\Factory  $factory,  
        \Magento\Directory\Model\Currency $currency,
        array $data = []
    ) { 
        parent::__construct($context, $data);
        $this->_dataHelper = $dataHelper;
        $this->_currency = $currency;
        $this->factory = $factory;
        
    }

    /**
     * Retrieve current order model instance
     *
     * @return \Magento\Sales\Model\Order
     */
    public function getOrder()
    {
        $parentBlock = $this->getParentBlock();
        if ($parentBlock) {
            return $parentBlock->getOrder();
        }
        return null;
    }

    /**
     * @return mixed
     */
    public function getSource()
    {
        $parentBlock = $this->getParentBlock();
        if ($parentBlock) {
            return $parentBlock->getSource();
        }
        return null;
    }

    /**
     * @return string
     */
    public function getCurrencySymbol()
    {
        return $this->_currency->getCurrencySymbol();
    }

    /**
     *
     *
     * @return $this
     */
    public function initTotals()
    { 
        $order = $this->getOrder();
        if ($order) {
            $source = $this->getSource();
        //echo 'aaaaaa'.$this->getSource()->getTierDiscount(); exit;
        if(!$this->getSource()->getTierDiscount()) {
            return $this;
        }
        $this->getParentBlock()->addTotal(
                        $this->factory->create(
                            [
                                'code'       => 'tier_discount',
                                'strong'     => false,
                                'label'      => $this->_dataHelper->getTierLabel(),
                                'value'      => $source->getTierDiscount(),
                                'base_value' => $source->getBaseTierDiscount(),
                            ]
                        )
                    );
                        
//        $total = new \Magento\Framework\DataObject(
//            [
//                'code' => 'tier_discount',
//                'value' => $this->getSource()->getTierDiscount(),
//                'label' => $this->_dataHelper->getTierLabel(),
//            ]
//        );
//        $this->getParentBlock()->addTotalBefore($total, 'grand_total');
                        
        }                  
        return $this;
    }
}
