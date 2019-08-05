<?php
/**
 * Copyright © 2015 Collins Harper. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace CollinsHarper\Moneris\Test\Unit\Model\Source;

use Magento\Framework\Xml\Security;

class PaymentActionTest extends \PHPUnit_Framework_TestCase
{
    /**
     * Model
     *
     * @var \CollinsHarper\Moneris\Model\Transaction
     */
    protected $_model;

    public function setUp()
    {
        $objectManagerHelper = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $this->_model = $objectManagerHelper->getObject('CollinsHarper\Moneris\Model\Source\PaymentAction');
    }

    public function testClass()
    {
        $returnData = $this->_model->toOptionArray();
        $this->assertTrue(isset($returnData[\CollinsHarper\Moneris\Model\Source\PaymentAction::PAYMENT_ACTION_AUTH]));
    }
}