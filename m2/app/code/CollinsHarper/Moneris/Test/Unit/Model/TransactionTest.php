<?php
/**
 * Copyright © 2015 Collins Harper. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace CollinsHarper\Moneris\Test\Unit\Model;

use Magento\Framework\Xml\Security;

class TransactionTest extends \PHPUnit_Framework_TestCase
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
        $this->_model = $objectManagerHelper->getObject('CollinsHarper\Moneris\Model\Transaction');
    }

    public function testUniqueNumber()
    {
        $order = new \Magento\Framework\DataObject();
        $order->setIncrementId('testing12332142');
        $payment = new \Magento\Framework\DataObject();
        $payment->setOrder($order);
        $this->_model->setPayment($payment);
        $this->assertTrue($this->_model->generateUniqueOrderId() != '');
    }

    public function testTransactionArray()
    {
        $value = array(
            \CollinsHarper\Moneris\Model\Transaction::ERROR_MESSAGE
        );
        $this->assertTrue($this->_model->buildTransactionArray() == $value);
    }

    public function testBuildArray()
    {
       $array = array('data');
        $this->assertTrue(get_class($this->_model->buildMpgTransaction($array)) == 'Moneris_MpgTransaction');
    }

    public function testIsSuccess()
    {
        $this->assertTrue($this->_model->getIsSuccessfulResponseCode(34) == true);
        $this->assertTrue($this->_model->getIsSuccessfulResponseCode(99) == false);
    }
}
