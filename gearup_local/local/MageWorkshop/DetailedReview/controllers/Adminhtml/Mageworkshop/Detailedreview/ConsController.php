<?php
/**
 * MageWorkshop
 * Copyright (C) 2016 MageWorkshop <info@mage-review.com>
 *
 * @category   MageWorkshop
 * @package    MageWorkshop_DetailedReview
 * @copyright  Copyright (c) 2016 MageWorkshop Co. (https://mage-review.com/)
 * @license    http://opensource.org/licenses/gpl-3.0.html GNU General Public License, version 3 (GPL-3.0)
 * @author     MageWorkshop <info@mage-review.com>
 */

require_once Mage::getModuleDir('controllers', 'MageWorkshop_DetailedReview') . DS . 'Adminhtml/Mageworkshop/Detailedreview/UserprosconsController.php';

class MageWorkshop_DetailedReview_Adminhtml_Mageworkshop_Detailedreview_ConsController extends MageWorkshop_DetailedReview_Adminhtml_Mageworkshop_Detailedreview_UserprosconsController
{
    /**
     * Class constructor
     */
    protected function _construct()
    {
        $this->_entityType = MageWorkshop_DetailedReview_Model_Source_EntityType::CONS;
        parent::_construct();
    }

    /**
     * @return bool
     */
    protected function _isAllowed()
    {
        return Mage::getSingleton('admin/session')->isAllowed('catalog/reviews_ratings/reviews/cons');
    }
}

