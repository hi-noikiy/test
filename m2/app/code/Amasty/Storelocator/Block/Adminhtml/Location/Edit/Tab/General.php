<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2019 Amasty (https://www.amasty.com)
 * @package Amasty_Storelocator
 */


namespace Amasty\Storelocator\Block\Adminhtml\Location\Edit\Tab;

use Magento\Backend\Block\Widget\Form\Generic;
use Magento\Backend\Block\Widget\Tab\TabInterface;
use Magento\Config\Model\Config\Source\Yesno;
use Magento\Config\Model\Config\Source\Locale\Country;

class General extends Generic implements TabInterface
{
    /**
     * @var \Magento\Store\Model\System\Store
     */
    protected $store;

    /**
     * Store manager
     *
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $storeManager;

    protected $wysiwygConfig;

    /**
     * @var \Amasty\Storelocator\Helper\Data
     */
    protected $helper;

    /** @var Yesno */
    protected $yesno;

    /** @var Country */

    protected $country;

    /**
     * {@inheritdoc}
     */
    public function getTabLabel()
    {
        return __('General Information');
    }

    /**
     * {@inheritdoc}
     */
    public function getTabTitle()
    {
        return __('Location Settings');
    }

    /**
     * {@inheritdoc}
     */
    public function canShowTab()
    {
        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function isHidden()
    {
        return false;
    }

    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Data\FormFactory $formFactory,
        \Amasty\Storelocator\Model\WysiwygConfig $wysiwygConfig,
        \Magento\Store\Model\System\Store $store,
        \Amasty\Storelocator\Helper\Data $helper,
        Yesno $yesno,
        Country $country,
        array $data = []
    ) {
        $this->store = $store;
        $this->wysiwygConfig = $wysiwygConfig;
        $this->helper = $helper;
        $this->yesno = $yesno;
        $this->country = $country;
        parent::__construct($context, $registry, $formFactory, $data);
    }

    /**
     * Prepare form before rendering HTML
     *
     * @return $this
     * @SuppressWarnings(PHPMD.NPathComplexity)
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    protected function _prepareForm()
    {
        $model = $this->_coreRegistry->registry('current_amasty_storelocator_location');

        $yesno = $this->yesno->toOptionArray();

        /** @var \Magento\Framework\Data\Form $form */
        $form = $this->_formFactory->create();
        $form->setHtmlIdPrefix('location_');

        $fieldset = $form->addFieldset('base_fieldset', ['legend' => __('General')]);
        if ($model->getId()) {
            $fieldset->addField('id', 'hidden', ['name' => 'id']);
        }

        $fieldset->addField(
            'name',
            'text',
            [
                'label'    => __('Location name'),
                'required' => true,
                'name'     => 'name',
            ]
        );

        if (!$this->_storeManager->isSingleStoreMode()) {
            $fieldset->addField(
                'stores',
                'multiselect',
                [
                    'name'     => 'stores[]',
                    'label'    => __('Store View'),
                    'title'    => __('Store View'),
                    'required' => true,
                    'values'   => $this->store->getStoreValuesForForm(false, true)
                ]
            );
        } else {
            $fieldset->addField(
                'store_id',
                'hidden',
                [
                    'name'  => 'store_id[]',
                    'value' => $this->_storeManager->getStore(true)->getId()
                ]
            );
        }

        $fieldset->addField(
            'country',
            'select',
            [
                'name'     => 'country',
                'required' => true,
                'class'    => 'countries',
                'label'    => 'Country',
                'values'   => $this->country->toOptionArray(),
            ]
        );

        $fieldset->addField(
            'state_id',
            'select',
            [
                'name'  => 'state_id',
                'label' => 'State/Province',
            ]
        );

        $fieldset->addField(
            'state',
            'text',
            [
                'name'  => 'state',
                'label' => 'State/Province',

            ]
        );

        $fieldset->addField(
            'city',
            'text',
            [
                'label'    => __('City'),
                'required' => true,
                'name'     => 'city',
            ]
        );

        $fieldset->addField(
            'description',
            'editor',
            [
                'label'    => __('Description'),
                'required' => false,
                'config'   => $this->wysiwygConfig->getConfig(),
                'name'     => 'description',
            ]
        );

        $fieldset->addField(
            'zip',
            'text',
            [
                'label'    => __('Zip'),
                'required' => true,
                'name'     => 'zip',
            ]
        );

        $fieldset->addField(
            'address',
            'text',
            [
                'label'    => __('Address'),
                'required' => true,
                'name'     => 'address',
            ]
        );

        $fieldset->addField(
            'phone',
            'text',
            [
                'label' => __('Phone Number'),
                'name'  => 'phone',
            ]
        );

        $fieldset->addField(
            'email',
            'text',
            [
                'label' => __('E-mail Address'),
                'name'  => 'email',
            ]
        );

        $fieldset->addField(
            'website',
            'text',
            [
                'label' => __('Website URL'),
                'name'  => 'website',
            ]
        );

        $fieldset->addField(
            'status',
            'select',
            [
                'label'    => __('Status'),
                'required' => true,
                'name'     => 'status',
                'values'   => ['1' => 'Enabled', '0' => 'Disabled'],
            ]
        );

        $fieldset->addField(
            'show_schedule',
            'select',
            [
                'label'    => __('Show Schedule'),
                'required' => false,
                'name'     => 'show_schedule',
                'values'   => $yesno,
            ]
        );

        $fieldset->addField(
            'position',
            'text',
            [
                'class'    => 'validate-number',
                'label'    => __('Position'),
                'required' => false,
                'name'     => 'position',
            ]
        );

        $fieldset->addField(
            'store_img',
            'file',
            [
                'label'              => __('Image'),
                'name'               => 'store_img',
                'after_element_html' => $this->getImageHtml('store_img', $model->getStoreImg()),
            ]
        );

        $form->setValues($model->getData());
        $form->addValues(['id' => $model->getId()]);
        $this->setForm($form);

        return parent::_prepareForm();
    }

    protected function getImageHtml($field, $img)
    {
        $html = '';
        if ($img) {
            $html .= '<p style="margin-top: 5px">';
            $html .= '<img style="max-width:100px" src="' . $this->helper->getImageUrl($img) . '" />';
            $html .= '<br/><input type="checkbox" value="1" name="remove_' . $field . '"/> ' . __('Remove');
            $html .= '<input type="hidden" value="' . $img . '" name="old_' . $field . '"/>';
            $html .= '</p>';
        }

        return $html;
    }
}
