<?php
namespace Ktpl\Customreport\Block\Adminhtml\Order\View\Tab;

class Deliverynote extends \Magento\Backend\Block\Template
    implements \Magento\Backend\Block\Widget\Tab\TabInterface
{    
    //change _constuct to _construct()
    protected $_template = 'order/view/tab/deliverynote.phtml';
    
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Framework\Registry $registry,
        array $data = []
    ) {
        $this->_coreRegistry = $registry;
        parent::__construct($context, $data);
    }

    public function getTabLabel() {
        return __('Delivery Note');
    }

    public function getTabTitle() {
        return __('Delivery Note');
    }

    public function canShowTab() {
        return true;
    }

    public function isHidden() {
        return false;
    }

    public function getOrder() {
        return $this->_coreRegistry->registry('current_order');
    }
} 
?>