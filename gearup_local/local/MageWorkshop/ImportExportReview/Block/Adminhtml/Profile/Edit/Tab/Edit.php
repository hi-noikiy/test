<?php

class MageWorkshop_ImportExportReview_Block_Adminhtml_Profile_Edit_Tab_Edit extends Mage_Adminhtml_Block_Widget_Form
{
    /**
     * @return $this
     */
    public function _prepareForm()
    {
        parent::_prepareForm();
        /** @var MageWorkshop_ImportExportReview_Model_Profile $profile */
        $profile = Mage::registry('current_drie_profile');
        /** @var MageWorkshop_ImportExportReview_Helper_Data $helper */
        $helper = Mage::helper('mageworkshop_importexportreview');

        $form = new Varien_Data_Form(array(
            'id' => 'edit_form',
            'action' => $this->getUrl(
                'adminhtml/drie_profile/edit',
                array(
                    '_current' => true,
                    'continue' => 0,
                )
            ),
            'method' => 'post',
        ));

        $fieldset = $form->addFieldset(
            'general',
            array(
                'legend' => $helper->__('Profile Configuration')
            )
        );

        $fieldset->addType('mapping', 'MageWorkshop_ImportExportReview_Block_Adminhtml_Profile_Edit_Form_Renderer_Mapping');
        $fieldset->addType('notice', 'MageWorkshop_ImportExportReview_Block_Adminhtml_Profile_Edit_Form_Renderer_Notice');

        /** @var MageWorkshop_ImportExportReview_Model_Profile $profileSingleton */
        $profileSingleton = Mage::getSingleton('mageworkshop_importexportreview/profile');


        $yesno = array(
            1 => $helper->__('Yes'),
            0 => $helper->__('No')
        );

        $fieldset->addField('name', 'text',
            array(
                'label'    => $helper->__('Name'),
                'class'    => 'required-entry',
                'required' => true,
                'name'     => 'profileData[name]'
            )
        );

        $fieldset->addField('type', 'select',
            array(
                'label'    => $helper->__('Type'),
                'class'    => 'required-entry',
                'required' => true,
                'name'     => 'profileData[type]',
                'options'  => $profileSingleton->getProfileTypes(),
            )
        );

        $fieldset->addField('store_id', 'select',
            array(
                'label'    => $helper->__('Store'),
                'class'    => 'required-entry',
                'required' => true,
                'name'     => 'profileData[store_id]',
                'options'  => $profileSingleton->getStoresForForm(),
                'note'     => $helper->__('Select what stores should be used for import/export process'),
            )
        );

        $fieldset->addField('use_full_image_path', 'select',
            array(
                'label'    => $helper->__('Use Full Path For Review Images'),
                'name'     => 'profileData[use_full_image_path]',
                'values'   => $yesno
            )
        );

        $fieldset->addField('max_width', 'text',
            array(
                'label'    => $helper->__('Max allowed image width (px)'),
                'class'    => '',
                'required' => false,
                'name'     => 'profileData[max_width]'
            )
        );
        $fieldset->addField('max_height', 'text',
            array(
                'label'    => $helper->__('Max allowed image height (px)'),
                'class'    => '',
                'required' => false,
                'name'     => 'profileData[max_height]'
            )
        );

        $fieldset->addField('full_path', 'notice',
            array(
                'name'  => 'full_path',
                'class' => 'notice-msg',
                'text'  => $helper->__(
                    'Values for review images will be imported/exported with full images path, there will be no need to move them while importing to another store. Please note that the website were the review images are originally placed should be working while importing'
                ),
            )
        );

        $fieldset->addField('default_path', 'notice',
            array(
                'name'  => 'default_path',
                'class' => 'notice-msg',
                'text'  => $helper->__(
                    'Values for review images will be imported/exported with relative images path. This means that review images should be placed in the Magento Root Media folder
                    using the same path that is described in the CSV file'
                ),
            )
        );

        $fieldset->addField('create_rating', 'select',
            array(
                'label'    => $helper->__('Create Rating if not exist'),
                'class'    => 'required-entry',
                'required' => true,
                'name'     => 'profileData[create_rating]',
                'values'  => $yesno
            )
        );

        /** @var Mage_Rating_Model_Resource_Rating_Collection $ratingCollection */
        $ratingCollection = Mage::getModel('rating/resource_rating_collection');

        if ($profile->getId()) {
            $ratingCollection->getSelect()->joinLeft(
                array('mapping' => $ratingCollection->getTable('mageworkshop_importexportreview/ratingMapping')),
                'main_table.rating_id = mapping.rating_id AND mapping.profile_id = ' . $profile->getId(),
                array(
                    'mapping_value' => 'mapping.mapping_value',
                    'mapping_id' => 'mapping.entity_id'
                )
            );
        }

        foreach ($ratingCollection as $rating) {
            $fieldset->addField('rating_'.$rating->getId(), 'mapping',
                array(
                    'label' => $this->__('Rating mapping for field:'),
                    'name' => 'ratingMapping[' . $rating->getId() . ']',
                    'rating_code' => $rating->getRatingCode(),
                    'mapping_value' => $rating->getMappingValue(),
                    'mapping_entity_id' => $rating->getMappingId()
                )
            );
        }

        $fieldset->addField('create_proscons', 'select',
            array(
                'label'    => $helper->__('Create Pros/Cons if not exist'),
                'class'    => 'required-entry',
                'required' => true,
                'name'     => 'profileData[create_proscons]',
                'values'   => $yesno
            )
        );
        
        $data = $profile->getData();
        $form->setValues($data);

        if (!$profile->getId()) {
            $values = array('create_proscons' => 1, 'create_rating' => 1);
            $form->addValues($values);
        }

        $this->setForm($form);

        $elementDependenceBlock = $this->getLayout()->createBlock('adminhtml/widget_form_element_dependence')
            ->addFieldMap('type', 'type')
            ->addFieldMap('create_rating', 'create_rating')
            ->addFieldMap('create_proscons', 'create_proscons')
            ->addFieldMap('rating_mapping', 'rating_mapping')
            ->addFieldMap('proscons_mapping', 'proscons_mapping')
            ->addFieldMap('use_full_image_path', 'use_full_image_path')
            ->addFieldMap('full_path', 'full_path')
            ->addFieldMap('default_path', 'default_path')
            ->addFieldMap('max_width', 'max_width')
            ->addFieldMap('max_height', 'max_height')
            ->addFieldDependence('create_rating', 'type', 0)
            ->addFieldDependence('create_proscons', 'type', 0)
            ->addFieldDependence('max_width', 'use_full_image_path', 1)
            ->addFieldDependence('max_height', 'use_full_image_path', 1)
            ->addFieldDependence('full_path', 'use_full_image_path', 1)
            ->addFieldDependence('default_path', 'use_full_image_path', 0);

        foreach ($ratingCollection as $rating) {
            $elementDependenceBlock->addFieldMap('rating_' . $rating->getId(), 'rating_' . $rating->getId());
            $elementDependenceBlock->addFieldDependence('rating_' . $rating->getId(), 'create_rating', 0);
            $elementDependenceBlock->addFieldDependence('rating_' . $rating->getId(), 'type', 0);
        }

        $this->setChild(
            'form_after',
            $elementDependenceBlock
        );

        return $this;
    }
}
