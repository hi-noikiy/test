<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2019 Amasty (https://www.amasty.com)
 * @package Amasty_Storelocator
 */


namespace Amasty\Storelocator\Block\Adminhtml\Location\Edit\Tab;

use Magento\Backend\Block\Widget\Form\Generic;
use Magento\Backend\Block\Widget\Tab\TabInterface;

class Schedule extends Generic implements TabInterface
{
    /**
     * @var \Amasty\Storelocator\Helper\Data
     */
    protected $helper;

    /**
     * @var \Amasty\Storelocator\Model\ResourceModel\Schedule\Collection
     */
    private $scheduleCollection;

    /**
     * {@inheritdoc}
     */
    public function getTabLabel()
    {
        return __('Store Schedule');
    }

    /**
     * {@inheritdoc}
     */
    public function getTabTitle()
    {
        return __('Store Schedule');
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
        \Amasty\Storelocator\Helper\Data $helper,
        \Amasty\Storelocator\Model\ResourceModel\Schedule\Collection $scheduleCollection,
        array $data = []
    ) {
        $this->helper = $helper;
        parent::__construct($context, $registry, $formFactory, $data);
        $this->scheduleCollection = $scheduleCollection;
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

        /** @var \Magento\Framework\Data\Form $form */
        $form = $this->_formFactory->create();
        $form->setHtmlIdPrefix('location_');

        $fieldset = $form->addFieldset(
            'active_schedule',
            [
                'legend' => __('Active Schedule'),
                'class'     => 'fieldset-wide',
                'expanded'  => true,
            ]
        );

        $fieldset->addField(
            'schedule',
            'select',
            [
                'label'   => __('Schedule'),
                'name'    => 'schedule',
                'options' => $this->scheduleCollection->toOptionHash(),
                'value' => $model->getSchedule()
            ]
        );

        $form->addValues(['id' => $model->getId()]);
        $this->setForm($form);

        return parent::_prepareForm();
    }
}
