<?php
/**
 * MageWorkshop
 * Copyright (C) 2016 MageWorkshop <info@mage-review.com>
 *
 * @category   MageWorkshop
 * @package    MageWorkshop_ReviewWall
 * @copyright  Copyright (c) 2016 MageWorkshop Co. (https://mage-review.com/)
 * @license    http://opensource.org/licenses/gpl-3.0.html GNU General Public License, version 3 (GPL-3.0)
 * @author     MageWorkshop <info@mage-review.com>
 */

/**
 * Class MageWorkshop_ReviewWall_Block_Widget_Part_Js
 */
class MageWorkshop_ReviewWall_Block_Widget_Part_Js extends Mage_Core_Block_Template
{
    protected function _construct()
    {
        $this->setTemplate('mageworkshop/reviewwall/widget/js.phtml');
        $this->setChild('template', Mage::app()->getLayout()->createBlock('reviewwall/widget_jsTemplate_default'));
        parent::_construct();
    }

    public function getFilter()
    {
        $helper = Mage::helper('reviewwall');
        $templateId = Mage::getStoreConfig($helper::REVIEWWALL_XML_PATH_TEMPLATE_ID);

        switch ($templateId) {
            case 1:
                $filter = null;
                break;
            case 2:
                $filter = MageWorkshop_ReviewWall_Model_Review::IMAGE_FILTER;
                break;
            default:
                $filter = null;
                break;
        }

        return $filter;
    }
}
