<?php
class Gearup_Tooltip_Block_Config_ShippingTooltip extends Mage_Adminhtml_Block_System_Config_Form_Field_Array_Abstract
{
    protected $_itemRenderer;

    public function _prepareToRender()
    {
        $this->addColumn('shipping_code', array(
            'label' => Mage::helper('tooltip')->__('Select Shipping Method'),
            'renderer' => $this->_getRenderer(),
        ));

        $this->addColumn('tooltip_text', array(
            'label' => Mage::helper('tooltip')->__('Tooltip Text'),
            'style' => 'width:300px',
        ));

        $this->_addAfter = false;
        $this->_addButtonLabel = Mage::helper('tooltip')->__('Add');
    }

    protected function _getRenderer() 
    {
        if (!$this->_itemRenderer) {
            $this->_itemRenderer = $this->getLayout()->createBlock(
                'tooltip/config_adminhtml_form_field_shipping', '',
                array('is_render_to_js_template' => true)
            );
        }
        return $this->_itemRenderer;
    }
 
    protected function _prepareArrayRow(Varien_Object $row)
    {
        $row->setData(
            'option_extra_attr_' . $this->_getRenderer()
                ->calcOptionHash($row->getData('shipping_code')),
            'selected="selected"'
        );
    }
}