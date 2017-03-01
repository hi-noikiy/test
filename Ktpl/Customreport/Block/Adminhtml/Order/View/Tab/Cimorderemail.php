<?php
namespace Ktpl\Customreport\Block\Adminhtml\Order\View\Tab;

class Cimorderemail extends  \Magento\Backend\Block\Template
    implements \Magento\Backend\Block\Widget\Tab\TabInterface
{    
    protected $_template = 'order/view/tab/cimorderemail.phtml';
    
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Framework\Registry $registry,
        array $data = []
    ) {
        $this->_coreRegistry = $registry;
        parent::__construct($context, $data);
    }

    public function getTabLabel() {
        return __('Send Emails');
    }

    public function getTabTitle() {
        return __('Send Emails');
    }

    public function canShowTab() {
         //return true;
        if($this->getOrder()->getIscimorder()) {
        return true;
        } else { return false; }
    }

    public function isHidden() {
        return false;
    }

   public function getOrder() {
        return $this->_coreRegistry->registry('current_order');
    }
} 
?>