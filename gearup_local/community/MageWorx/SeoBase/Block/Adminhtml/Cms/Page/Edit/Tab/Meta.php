<?php
/**
 * MageWorx
 * MageWorx SeoBase Extension
 *
 * @category   MageWorx
 * @package    MageWorx_SeoBase
 * @copyright  Copyright (c) 2015 MageWorx (http://www.mageworx.com/)
 */


class MageWorx_SeoBase_Block_Adminhtml_Cms_Page_Edit_Tab_Meta extends Mage_Adminhtml_Block_Cms_Page_Edit_Tab_Meta
{

    protected function _prepareForm()
    {
        parent::_prepareForm();
        $form     = $this->getForm();
        $fieldset = $form->getElements()->searchById('meta_fieldset');

        $values = Mage::getModel('adminhtml/system_config_source_design_robots')->toOptionArray();
        array_unshift($values, array('label' => 'Use Config', 'value' => ''));
        $fieldset->addField('meta_robots', 'select',
                array(
            'name'   => 'meta_robots',
            'label'  => Mage::helper('seosuite')->__('Robots'),
            'values' => $values,
        ));

        $fieldset->addField('meta_title', 'text',
                array(
            'name'     => 'meta_title',
            'label'    => Mage::helper('cms')->__('Title'),
            'title'    => Mage::helper('cms')->__('Title'),
            'required' => false,
            'disabled' => false
                ), '^'
        );

        if(Mage::helper('seosuite/alternate')->getCmsPageRelationWay() == MageWorx_SeoBase_Helper_Alternate::CMS_RELATION_BY_IDENTIFIER){
            $message = Mage::helper('seosuite')->__('This setting works. You can see other options in <br><i>SEO Suiute -> SEO Alternate URLs</i> config section.');
        }else{
            $message = Mage::helper('seosuite')->__('This setting is disabled. You can enable it in <br><i>SEO Suiute -> SEO Alternate URLs</i> config section.');
        }

        $hint = '<p class="note entered">' . $message . '</p>';

        $fieldset->addField('mageworx_hreflang_identifier', 'text',
                array(
            'name'     => 'mageworx_hreflang_identifier',
            'label'    => Mage::helper('seosuite')->__('Hreflang Key'),
            'title'    => Mage::helper('seosuite')->__('Hreflang Key'),
            'required' => false,
            'disabled' => false,
            'class' => 'validate-data',
            'after_element_html' => $hint,
                )
        );

        $model = Mage::registry('cms_page');
        $form->setValues($model->getData());
        $this->setForm($form);

        return $this;
    }

}
