<?php
/**
 * MageWorkshop
 * Copyright (C) 2016 MageWorkshop <info@mage-review.com>
 *
 * @category   MageWorkshop
 * @package    MageWorkshop_DetailedReview
 * @copyright  Copyright (c) 2016 MageWorkshop Co. (https://mage-review.com/)
 * @license    http://opensource.org/licenses/gpl-3.0.html GNU General Public License, version 3 (GPL-3.0)
 * @author     MageWorkshop <info@mage-review.com>
 */
class MageWorkshop_DetailedReview_Block_Adminhtml_Catalog_Category_Tab_Attributes extends Mage_Adminhtml_Block_Catalog_Category_Tab_Attributes
{

    /**
     * @inherit
     */
    protected function _prepareForm()
    {
        parent::_prepareForm();
        if ($element = $this->getForm()->getElement('use_parent_proscons_settings')) {
            $element->setData('onchange', 'onUseParentChangedHandler(this)');
            $element->setData('class', 'use_parent_proscons_settings');
        }
        return $this;
    }
}
