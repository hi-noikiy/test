<?php

class MageWorkshop_ImportExportReview_Block_Adminhtml_Profile_Mapping_Rating extends MageWorkshop_ImportExportReview_Block_Adminhtml_Profile_Mapping_Abstract
{
    /**
     * Initialize block
     */
    public function __construct()
    {
        $this->setTemplate('mageworkshop/importexport/mapping/rating.phtml');
        parent::__construct();
    }

    /**
     * @return Mage_Core_Block_Abstract
     */
    protected function _prepareLayout()
    {
        $button = $this->getLayout()->createBlock('adminhtml/widget_button')
            ->setData(array(
                'label'   => Mage::helper('catalog')->__('Add Mapping Field'),
                'onclick' => 'return ratingControl.addItem()',
                'class'   => 'add'
            ));
        $button->setName('add_rating_item_button');
        $this->setChild('add_button', $button);
        return parent::_prepareLayout();
    }

    /**
     * @return array
     */
    public function getRatingMapping()
    {
        $profile = Mage::registry('current_drie_profile');
        $result = ($profile->getRatingMapping())? unserialize($profile->getRatingMapping()) : array() ;
        return $result;
    }
}