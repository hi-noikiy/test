<?php

/**
 * Magestore.
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Magestore.com license that is
 * available through the world-wide-web at this URL:
 * http://www.magestore.com/license-agreement.html
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade this extension to newer
 * version in the future.
 *
 * @category    Magestore
 * @package     Magestore_Affiliateplus
 * @copyright   Copyright (c) 2012 Magestore (http://www.magestore.com/)
 * @license     http://www.magestore.com/license-agreement.html
 */
namespace Magestore\Affiliateplus\Block\Adminhtml\Banner\Edit\Tab;

use Magento\Backend\Block\Widget\Tab\TabInterface;


/**
 * Grid Grid
 */
class Form extends \Magento\Backend\Block\Widget\Form\Generic implements TabInterface
{
    const IMAGE_PREVIEW_TEMPLATE = 'Magestore_Affiliateplus::banner/imageview.phtml';

    const FLASH_PREVIEW_TEMPLATE = 'Magestore_Affiliateplus::banner/flashview.phtml';

    /**
     * @var \Magestore\Affiliateplus\Model\Banner
     */
    protected $_banner;
    /**
     * @var \Magestore\Affiliateplus\Model\ResourceModel\Banner\Value\CollectionFactory
     */
    protected $_valueCollectionFactory;

    /**
     * @var \Magento\Framework\DataObjectFactory
     */
    protected $_objectFactory;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $_storeManager;

    /**
     * @var \Magento\Backend\Model\Session
     */
    protected $_session;

    /**
     * @var \Magento\Framework\View\LayoutInterface
     */
    protected $_layout;

    /**
     * Form constructor.
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Framework\Data\FormFactory $formFactory
     * @param \Magestore\Affiliateplus\Model\Banner $banner
     * @param \Magento\Framework\DataObjectFactory $objectFactory
     * @param \Magestore\Affiliateplus\Model\ResourceModel\Banner\Value\CollectionFactory $bannerValueCollection
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Magento\Framework\ObjectManagerInterface $objectManager
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Data\FormFactory $formFactory,
        \Magestore\Affiliateplus\Model\Banner $banner,
        \Magento\Framework\DataObjectFactory $objectFactory,
        \Magestore\Affiliateplus\Model\ResourceModel\Banner\Value\CollectionFactory $bannerValueCollection,
        \Magento\Framework\ObjectManagerInterface $objectManager,
        array $data = array()
    )
    {
        parent::__construct($context, $registry, $formFactory, $data);
        $this->_banner = $banner;
        $this->_valueCollectionFactory = $bannerValueCollection;
        $this->_objectFactory = $objectFactory;
        $this->_storeManager = $context->getStoreManager();
        $this->_session = $context->getSession();
        $this->_objectManager = $objectManager;
        $this->_layout = $context->getLayout();
    }

    /**
     * get registry model.
     *
     * @return \Magento\Framework\Model\AbstractModel|null
     */
    public function getRegistryModel()
    {
        return $this->_coreRegistry->registry('banner_data');
    }

    /**
     * {@inheritdoc}
     */
    public function getTabLabel()
    {
        return __('General information');
    }

    /**
     * {@inheritdoc}
     */
    public function getTabTitle()
    {
        return __('General information');
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

    protected function _prepareLayout()
    {
        $this->getLayout()->getBlock('page.title')->setPageTitle($this->getPageTitle());

        \Magento\Framework\Data\Form::setFieldsetElementRenderer(
            $this->getLayout()->createBlock(
                'Magestore\Affiliateplus\Block\Adminhtml\Banner\Form\Renderer\Fieldset\Element',
                $this->getNameInLayout().'_banner_fieldset_element'
            )
        );
        return $this;
    }

    /**
     * {@inheritdoc}
     */
    protected function _prepareForm()
    {
        $bannerAttributes = $this->_banner->getStoreAttributes();
        $bannerAttributesInStores = ['store_id' => ''];

        foreach ($bannerAttributes as $bannerAttribute) {
            $bannerAttributesInStores[$bannerAttribute.'_in_store'] = '';
        }

        $dataObj = $this->_objectFactory->create(
            ['data' => $bannerAttributesInStores]
        );

        /** @var \Magento\Framework\Data\Form $form */
        $form = $this->_formFactory->create();

        $form->setHtmlIdPrefix('banner_');

        if ($this->_session->getBannerData()){
            $data = $this->_session->getBannerData();
            $this->_session->setBannerData(null);
        } elseif ($this->getRegistryModel()) {
            $data = $this->getRegistryModel()->getData();
        }

        $form->setTransationData($data);

        $model = $this->getRegistryModel();

        $fieldset = $form->addFieldset('general_fieldset', ['legend' => __('General Information')]);

        if (isset($data['banner_id']) && $data['banner_id']) {
            $fieldset->addField('banner_id', 'hidden', ['name' => 'banner_id']);
        }

        $elements = [];

        $elements['title'] = $fieldset->addField(
            'title',
            'text',
            [
                'name' => 'title',
                'label' => __('Title'),
                'title' => __('Title'),
                'required' => true,
            ]
        );

        /**
         * Add this event to insert program here
         */
        $this->_eventManager->dispatch('affiliateplus_adminhtml_add_field_banner_form', array('fieldset' => $fieldset, 'form' => $form, 'element'=>$elements));


        $elements['type_id'] = $fieldset->addField(
            'type_id',
            'select',
            [
                'name' => 'type_id',
                'label' => __('banner Type'),
                'title' => __('banner Type'),
                'options' => \Magestore\Affiliateplus\Model\Banner::getAvailableTypes(),
                'onchange' => 'showFileField()',
            ]
        );

        if(isset($data['source_file']) && $data['source_file']){
            $isRequired = false;
        } else {
            $isRequired = true;
        }

        $elements['source_file'] = $fieldset->addField(
            'source_file',
            'file',
            [
                'name' => 'source_file',
                'label' => __('Source File'),
                'title' => __('Source File'),
                'required'=> $isRequired,
                'note' =>__('Supported format: .jpeg, .jpg, .png, .gif, .swf.'),
            ]
        );

        //show view link if banner is image or flash
        if ($data && $data['banner_id'] && $data['type_id'] != 3 && $data['source_file']) {
            $html = $this->createElementHtmlOutputBlock();
            $elements['banner_view'] = $fieldset->addField('banner_view', 'note', array(
                'label' => __('banner View'),
                'text' => $html,
            ));
        }

        $elements['width'] = $fieldset->addField('width', 'text', array(
            'label' => __('Width (px)'),
            'required' => true,
            'name' => 'width',
        ));

        $elements['height'] = $fieldset->addField('height', 'text', array(
            'label' => __('Height (px)'),
            'required' => true,
            'name' => 'height',
            'note' => __('Specify the size of the banner showed for better display.'),
        ));

        $elements['link'] = $fieldset->addField('link', 'text', array(
            'label' => __('Link'),
            'note' => __('The target URL from this banner is used for redirection.'),
            'name' => 'link',
        ));

        $elements['rel_nofollow'] = $fieldset->addField('rel_nofollow', 'select',[
                'label' => __('Rel Nofollow'),
                'name' => 'rel_nofollow',
                'note' => __('Put the rel="nofollow" attribute on the link.'),
                'options' => \Magestore\Affiliateplus\Model\Status::getYesNoOption()
            ]
        );

        $elements['status'] = $elements['status'] = $fieldset->addField(
            'status',
            'select',
            [
                'name' => 'status',
                'label' => __('Status'),
                'title' => __('Status'),
                'options' => \Magestore\Affiliateplus\Model\Status::getAvailableStatuses(),
            ]
        );

        if (isset($data['banner_id']) && $data['banner_id']) {
            $actionCollection = $this->_objectManager->create('Magestore\Affiliateplus\Model\ResourceModel\Action\Collection');

            $actionCollection->getSelect()
                ->columns(array(
                    'raw_total' => 'SUM(totals)',
                    'uni_total' => 'SUM(is_unique)',
                ))
                ->group('type')
                ->where('banner_id = ?', $data['banner_id']);
            $traffics = [
                'raw_click' => 0,
                'uni_click' => 0,
                'raw_view' => 0,
                'uni_view' => 0
            ];
            foreach ($actionCollection as $item) {
                if ($item->getType() == '1') {
                    $traffics['raw_view'] = $item->getRawTotal();
                    $traffics['uni_view'] = $item->getUniTotal();
                } else {
                    $traffics['raw_click'] = $item->getRawTotal();
                    $traffics['uni_click'] = $item->getUniTotal();
                }
            }
            $elements['clicks'] = $fieldset->addField('clicks', 'note', array(
                'label' => __('Clicks (unique/raw)'),
                'text' => $traffics['uni_click'] . ' / ' . $traffics['raw_click'],
            ));

            $elements['views'] = $fieldset->addField('views', 'note', array(
                'label' => __('Impressions (unique/ raw)'),
                'text' => $traffics['uni_view'] . ' / ' . $traffics['raw_view'],
            ));
        }

        $form->setValues($model->getData());
        $this->setForm($form);
        return parent::_prepareForm();
    }

    /**
     * @return \Magento\Framework\Phrase
     */
    public function getPageTitle()
    {
        return $this->getRegistryModel()->getId()
            ? __("Edit banner '%1'", $this->escapeHtml($this->getRegistryModel()->getTitle())) : __('Add New banner');
    }

    /**
     * Generate html to preview banner image
     * @return string
     */
    public function createElementHtmlOutputBlock(){
        $banner = $this->getRegistryModel();
        if($banner && $banner->getId() && $banner->getSourceFile()){
            $url = $this->_storeManager->getStore()->getBaseUrl(
                    \Magento\Framework\UrlInterface::URL_TYPE_MEDIA
                ).'affiliateplus/banner/';

            $block = $this->_layout->createBlock(
                'Magento\Framework\View\Element\Template',
                'affiliate.banner.form.preview.image.element'
            );
            $block->setBanner($banner)
                ->setBannerType($banner->getTypeId())
                ->setFile($url . $banner->getSourceFile())
                ->setWidth($banner->getWidth())
                ->setHeight($banner->getHeight());

            if($banner->getTypeId() == 1) {
                $block->setTemplate(self::IMAGE_PREVIEW_TEMPLATE);
            } elseif($banner->getTypeId() == 2) {
                $block->setTemplate(self::FLASH_PREVIEW_TEMPLATE);
            }
        }
        return $block->toHtml();
    }
}
