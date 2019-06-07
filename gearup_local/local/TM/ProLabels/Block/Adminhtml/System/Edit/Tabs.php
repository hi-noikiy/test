<?php
/**
 * DO NOT REMOVE OR MODIFY THIS NOTICE
 *
 * EasyBanner module for Magento - product labels management
 *
 * @author Templates-Master Team <www.templates-master.com>
 */

class TM_ProLabels_Block_Adminhtml_System_Edit_Tabs
    extends TM_ProLabels_Block_Adminhtml_Rules_Edit_Tabs
{

    public function __construct()
    {
        parent::__construct();
        $this->setId('system_rules_tabs');
    }

    protected function _prepareLayout()
    {
        parent::_prepareLayout();
        $this->removeTab('conditions_section')->removeTab('products_section');
        return $this;
    }
}
