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
 * @package   mirasvit/extension_rma
 * @version   2.4.7
 * @copyright Copyright (C) 2017 Mirasvit (https://mirasvit.com/)
 */



class Mirasvit_Rma_Helper_DataTest extends EcomDev_PHPUnit_Test_Case
{
    protected $helper;
    protected function setUp()
    {
        parent::setUp();
        $this->helper = Mage::helper('rma/data');
    }

    /**
     * @test
     * @loadFixture data
     */
    public function generateIncrementIdTest()
    {
        $rma = Mage::getModel('rma/rma')->load(2);

        Mage::helper('msttest/mock')->mockSingletonMethod('rma/config', array(
//            'getNumberFormat' => 'NM-[store]-[order]-[counter]',
            'getNumberFormat' => 'NM-[store]-[counter]',
        ));
        $result = $this->helper->generateIncrementId($rma);
//        $this->assertEquals('NM-1-1000032-00000002', $result);
        $this->assertEquals('NM-1-00000002', $result);

        Mage::helper('msttest/mock')->mockSingletonMethod('rma/config', array(
            'getNumberFormat' => '[store][counter]',
        ));
        $result = $this->helper->generateIncrementId($rma);
        $this->assertEquals('100000002-2', $result);
    }
}
