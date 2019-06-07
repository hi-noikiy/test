<?php

/**
 * Gearup_EMI extension
 * 
 * NOTICE OF LICENSE
 * 
 * This source file is subject to the MIT License
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/mit-license.php
 * 
 * @category       Gearup
 * @package        Gearup_EMI
 * @copyright      Copyright (c) 2018
 * @license        http://opensource.org/licenses/mit-license.php MIT License
 */

/**
 * Bank edit form tab
 *
 * @category    Gearup
 * @package     Gearup_EMI
 * @author      Ultimate Module Creator
 */
class Gearup_EMI_Block_Adminhtml_Banks_Edit_Tab_ManageEMI extends Mage_Adminhtml_Block_Widget implements Varien_Data_Form_Element_Renderer_Interface {

    /**
     * Initialize block
     */
    public function __construct() {
        $this->setTemplate('gearup/emi/installments.phtml');
    }

    /**
     * Render HTML
     *
     * @param Varien_Data_Form_Element_Abstract $element
     * @return string
     */
    public function render(Varien_Data_Form_Element_Abstract $element) {
        $this->setElement($element);
        return $this->toHtml();
    }

    /**
     * Prepare global layout
     * Add "Add tier" button to layout
     *
     * @return Mage_Adminhtml_Block_Catalog_Product_Edit_Tab_Price_Tier
     */
    protected function _prepareLayout() {
        $button = $this->getLayout()->createBlock('adminhtml/widget_button')
                ->setData(array(
            'label' => Mage::helper('core')->__('Add Installment'),
            'onclick' => 'return emiControl.addItem()',
            'class' => 'add'
        ));
        $button->setName('add_emi_item_button');

        $this->setChild('add_button', $button);
        return parent::_prepareLayout();
    }

}
