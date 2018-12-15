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



class Mirasvit_Helpdesk_Helper_EmailTest extends EcomDev_PHPUnit_Test_Case
{
    protected $helper;
    protected function setUp()
    {
        parent::setUp();
        $this->helper = Mage::helper('helpdesk/email');
    }

    /**
     * @test
     * @loadFixture data
     */
    public function getEmailSubjectTest()
    {
        $ticket = Mage::getModel('helpdesk/ticket')->getCollection()
            ->addFieldToFilter('ticket_id', 2)
            ->joinFields()
            ->getFirstItem();

        Mage::helper('msttest/mock')->mockSingletonMethod('helpdesk/config', array(
            'getNotificationIsShowCode' => true,
        ));
        $this->assertEquals('[#abcdef] Test Ticket', $this->helper->getEmailSubject($ticket));
        $this->assertEquals('[#abcdef] New Ticket Created - Test Ticket', $this->helper->getEmailSubject($ticket, 'New Ticket Created'));

        Mage::helper('msttest/mock')->mockSingletonMethod('helpdesk/config', array(
            'getNotificationIsShowCode' => false,
        ));
        $this->assertEquals('Test Ticket', $this->helper->getEmailSubject($ticket));
        $this->assertEquals('New Ticket Created - Test Ticket', $this->helper->getEmailSubject($ticket, 'New Ticket Created'));
    }
}
