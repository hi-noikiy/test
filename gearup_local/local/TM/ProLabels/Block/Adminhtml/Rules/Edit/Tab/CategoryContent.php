<?php
/**
 * DO NOT REMOVE OR MODIFY THIS NOTICE
 *
 * EasyBanner module for Magento - flexible banner management
 *
 * @author Templates-Master Team <www.templates-master.com>
 */

class TM_ProLabels_Block_Adminhtml_Rules_Edit_Tab_CategoryContent
    extends TM_ProLabels_Block_Adminhtml_Rules_Edit_Tab_Content
{
    /**
     * Class constructor
     *
     */
    protected function _construct()
    {
        parent::_construct();
        $this->setFieldPrefix('category_');
    }

}
