<?php

namespace Ktpl\Guestabandoned\Block\Adminhtml;

class Guestabandoned extends \Magento\Backend\Block\Widget\Grid\Container {

    /**
     * @return void
     */
    protected function _construct() {        
        $this->_blockGroup = 'Ktpl_Guestabandoned';
        $this->_controller = 'adminhtml_guestabandoned';
        $this->_headerText = __('Guest Abandoned');
        parent::_construct();
        $this->buttonList->remove('add');
    }

    protected function _prepareLayout() {
        $this->buttonList->add('add_new', array(
            'label' => __('Refresh'),
            'onclick' => "setLocation('{$this->getUrl('*/*/refresh')}')",
            'class' => 'scalable'
        ));

        return parent::_prepareLayout();
    }

}
