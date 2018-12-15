<?php
/**
 * Mirasvit
 *
 * This source file is subject to the Mirasvit Software License, which is available at https://mirasvit.com/license/.
 * Do not edit or add to this file if you wish to upgrade the to newer versions in the future.
 * If you wish to customize this module for your needs.
 * Please refer to http://www.magentocommerce.com for more information.
 *
 * @category  Mirasvit
 * @package   mirasvit/extension_helpdesk
 * @version   1.5.4
 * @copyright Copyright (C) 2017 Mirasvit (https://mirasvit.com/)
 */



class Mirasvit_Helpdesk_Block_Adminhtml_Sales_Order_View_Tabs_Ticket extends Mage_Adminhtml_Block_Widget
implements Mage_Adminhtml_Block_Widget_Tab_Interface
{
    /** @var Mirasvit_Helpdesk_Block_Adminhtml_Ticket_Grid $grid */
    protected $grid;
    protected $gridHtml;
    protected function _prepareLayout()
    {
        /** @var Mirasvit_Helpdesk_Block_Adminhtml_Ticket_Grid $grid */
        $grid = $this->getLayout()->createBlock('helpdesk/adminhtml_ticket_grid');
        $grid->setId('helpdesk_grid_order');
        $grid->addCustomFilter('order_id', $this->getOrderId());
        $grid->removeFilter('is_archived');
        $grid->setFilterVisibility(false);
        $grid->setPagerVisibility(0);
        $grid->setTabMode(true);
        $grid->setActiveTab('tickets');
        $this->grid = $grid;
        $this->gridHtml = $this->grid->toHtml();

        return parent::_prepareLayout();
    }

    public function getTabLabel()
    {
        return Mage::helper('helpdesk')->__('Help Desk Tickets (%s)', $this->grid->getFormattedNumberOfTickets());
    }

    public function getTabTitle()
    {
        return Mage::helper('helpdesk')->__('Help Desk Tickets');
    }

    public function canShowTab()
    {
        return true;
    }

    public function isHidden()
    {
        return false;
    }

    public function getOrderId()
    {
        return $this->getRequest()->getParam('order_id');
    }

    protected function _toHtml()
    {
        $id = $this->getOrderId();
        $ticketNewUrl = $this->getUrl('adminhtml/helpdesk_ticket/add', array('order_id' => $id));

        $button = $this->getLayout()->createBlock('adminhtml/widget_button');
        $button
            ->setClass('add')
            ->setType('button')
            ->setOnClick('window.location.href=\''.$ticketNewUrl.'\'')
            ->setLabel($this->__('Create ticket for this order'));

        return '<div>'.$button->toHtml().'<br><br>'.$this->gridHtml.'</div>';

        // return '<div class="content-buttons-placeholder" style="height:25px;">' .
        // '<p class="content-buttons form-buttons" >' . $button->toHtml() . '</p>' .
        // '</div>' . $grid->toHtml();
    }
}
