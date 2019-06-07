<?php
/**
 * DO NOT REMOVE OR MODIFY THIS NOTICE
 *
 * EasyBanner module for Magento - flexible banner management
 *
 * @author Templates-Master Team <www.templates-master.com>
 */

class TM_ProLabels_Block_Adminhtml_Rules_Edit_Tab_Conditions
    extends TM_ProLabels_Block_Adminhtml_Rules_Edit_Tab_Abstract
{
    protected function _prepareForm()
    {
        $model = $this->_getLabelModel();
        $form = $this->getForm();
        $renderer = Mage::getBlockSingleton('adminhtml/widget_form_renderer_fieldset')
            ->setTemplate('prolabels/rules/filters.phtml')
            ->setNewChildUrl($this->getUrl('*/*/newConditionHtml/form/rules_conditions_fieldset'));

        $fieldset = $form->addFieldset(
            'conditions_fieldset',
            array(
            'legend' => Mage::helper('prolabels')->__('Conditions (leave blank to show on all pages)')
            )
        )->setRenderer($renderer);
        if ((int)$model->getId() > 3 || $model->getId() === NULL) {

            $fieldset->addField(
                'conditions',
                'text',
                array(
                    'name' => 'conditions',
                    'label' => Mage::helper('prolabels')->__('Conditions'),
                    'title' => Mage::helper('prolabels')->__('Conditions'),
                    'required'  => true,
                )
            )->setRule($model)
                ->setRenderer(Mage::getBlockSingleton('rule/conditions'));

            $form->setValues($model->getData());
        }

        return parent::_prepareForm();
    }
}
