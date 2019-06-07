<?php
/**
 * MageWorx
 * MageWorx SeoBase Extension
 *
 * @category   MageWorx
 * @package    MageWorx_SeoBase
 * @copyright  Copyright (c) 2015 MageWorx (http://www.mageworx.com/)
 */

class MageWorx_SeoBase_Model_Observer_Page extends Mage_Core_Model_Abstract
{
    /**
     * Add "Meta Robots", "Title", "Hreflang Key" fields
     *
     * @param Varien_Event_Observer $observer
     * @return this
     */
    public function addFormFieldsForCmsPage($observer)
    {
        //adminhtml_cms_page_edit_tab_meta_prepare_form
        $form      = $observer->getForm();
        $fieldset  = $form->getElements()->searchById('meta_fieldset');

        if(!$fieldset){
            return $this;
        }

        $metaRobotValues = Mage::getModel('seosuite/catalog_product_attribute_source_meta_robots')->getAllOptions();

        $fieldset->addField('meta_robots', 'select',
            array(
                'name'   => 'meta_robots',
                'label'  => __('Meta Robots'),
                'title'  => __('Meta Robots'),
                'values' => $metaRobotValues,
                'note'   => __('This setting was added by MageWorx SEO Suite')

            )
        );

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
            $message = Mage::helper('seosuite')->__('This setting works. You can see other options in <br><i>SEO Suite -> SEO Alternate URLs</i> config section.');
        }else{
            $message = Mage::helper('seosuite')->__('This setting is disabled. You can enable it in <br><i>SEO Suite -> SEO Alternate URLs</i> config section.');
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