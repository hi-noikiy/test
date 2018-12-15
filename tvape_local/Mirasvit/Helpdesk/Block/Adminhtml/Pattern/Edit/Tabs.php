<?php
/**
 * Mirasvit
 *
 * This source file is subject to the Mirasvit Software License, which is available at https://mirasvit.com/license/.
 * Do not edit or add to this file if you wish to upgrade the to newer versions in the future.
 * If you wish to customize this module for your needs.
 * Please refer to http://www.magentocommerce.com for more information.
 *
 * @category  Mirasvit
 * @package   mirasvit/extension_helpdesk
 * @version   1.5.4
 * @copyright Copyright (C) 2017 Mirasvit (https://mirasvit.com/)
 */



class Mirasvit_Helpdesk_Block_Adminhtml_Pattern_Edit_Tabs extends Mage_Adminhtml_Block_Widget_Tabs
{
    public function __construct()
    {
        parent::__construct();
        $this->setId('pattern_tabs');
        $this->setDestElementId('edit_form');
    }

    protected function _beforeToHtml()
    {
        $this->addTab('general_section', array(
            'label' => Mage::helper('helpdesk')->__('General Information'),
            'title' => Mage::helper('helpdesk')->__('General Information'),
            'content' => $this->getLayout()->createBlock('helpdesk/adminhtml_pattern_edit_tab_general')->toHtml(),
        ));

        return parent::_beforeToHtml();
    }
}
