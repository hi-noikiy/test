<?php
/**
 * MageWorkshop
 * Copyright (C) 2016 MageWorkshop <info@mage-review.com>
 *
 * @category   MageWorkshop
 * @package    MageWorkshop_DRReminder
 * @copyright  Copyright (c) 2016 MageWorkshop Co. (https://mage-review.com/)
 * @license    http://opensource.org/licenses/gpl-3.0.html GNU General Public License, version 3 (GPL-3.0)
 * @author     MageWorkshop <info@mage-review.com>
 */

class MageWorkshop_DRReminder_Block_Adminhtml_Reminder extends Mage_Adminhtml_Block_Widget_Grid_Container
{
    public function __construct()
    {
        $this->_controller = 'adminhtml_reminder';
        $this->_blockGroup = 'drreminder';
        $this->_headerText = $this->__('Reviews Reminder');
        parent::__construct();
        $this->_removeButton('add');
        $this->_addButton('massCreate', array(
            'label'     => Mage::helper('drreminder')->__('Create Reminders for Old Orders'),
            'onclick'   => 'generateReminders( \'' . $this->getMassCreateUrl() . '\', this)',
        ));
    }

    public function getMassCreateUrl()
    {
        return $this->getUrl('*/*/massCreate');
    }
}
