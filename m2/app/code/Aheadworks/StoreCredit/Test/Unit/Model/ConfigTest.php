<?php
/**
* Copyright 2016 aheadWorks. All rights reserved.
* See LICENSE.txt for license details.
*/

namespace Aheadworks\StoreCredit\Test\Unit\Model;

use Aheadworks\StoreCredit\Model\Config;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Store\Model\ScopeInterface;

/**
 * Class Aheadworks\StoreCredit\Test\Unit\Model\ConfigTest
 */
class ConfigTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var Config
     */
    private $object;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|ScopeConfigInterface
     */
    private $scopeConfigMock;

    protected function setUp()
    {
        $objectManager = new ObjectManager($this);

        $this->scopeConfigMock = $this->getMockBuilder(ScopeConfigInterface::class)
            ->disableOriginalConstructor()
            ->setMethods(['getValue', 'isSetFlag'])
            ->getMockForAbstractClass();

        $data = [
            'scopeConfig' => $this->scopeConfigMock,
        ];
        $this->object = $objectManager->getObject(Config::class, $data);
    }

    /**
     * Test isStoreCreditRefundAutomatically method
     */
    public function testIsStoreCreditRefundAutomaticallyMethod()
    {
        $this->scopeConfigMock->expects($this->once())
            ->method('isSetFlag')
            ->with(Config::XML_PATH_AW_STORECREDIT_GENERAL_IS_REFUND_AUTOMATICALLY, 'default')
            ->willReturn(true);

        $this->assertTrue($this->object->isStoreCreditRefundAutomatically());
    }

    /**
     * Test isApplyingStoreCreditOnTax method
     */
    public function testIsApplyingStoreCreditOnTaxMethod()
    {
        $this->scopeConfigMock->expects($this->once())
            ->method('isSetFlag')
            ->with(
                Config::XML_PATH_AW_STORECREDIT_GENERAL_IS_APPLYING_STORECREDIT_ON_TAX,
                ScopeInterface::SCOPE_WEBSITE,
                null
            )->willReturn(true);

        $this->assertTrue($this->object->isApplyingStoreCreditOnTax());
    }

    /**
     * Test isApplyingStoreCreditOnShipping method
     */
    public function testIsApplyingStoreCreditOnShippingMethod()
    {
        $this->scopeConfigMock->expects($this->once())
            ->method('isSetFlag')
            ->with(
                Config::XML_PATH_AW_STORECREDIT_GENERAL_IS_APPLYING_STORECREDIT_ON_SHIPPING,
                ScopeInterface::SCOPE_WEBSITE,
                null
            )->willReturn(true);

        $this->assertTrue($this->object->isApplyingStoreCreditOnShipping());
    }

    /**
     * Test isStoreCreditBalanceTopLinkAtFrontend method
     */
    public function testIsStoreCreditBalanceTopLinkAtFrontendMethod()
    {
        $this->scopeConfigMock->expects($this->once())
            ->method('isSetFlag')
            ->with(Config::XML_PATH_AW_STORECREDIT_FRONTEND_IS_TOP_LINK, ScopeInterface::SCOPE_WEBSITE)
            ->willReturn(true);

        $this->assertTrue($this->object->isStoreCreditBalanceTopLinkAtFrontend());
    }

    /**
     * Test isHideIfStoreCreditBalanceEmpty method
     */
    public function testIsHideIfStoreCreditBalanceEmptyMethod()
    {
        $this->scopeConfigMock->expects($this->once())
            ->method('isSetFlag')
            ->with(Config::XML_PATH_AW_STORECREDIT_FRONTEND_IS_HIDE_IF_BALANCE_EMPTY, ScopeInterface::SCOPE_WEBSITE)
            ->willReturn(true);

        $this->assertTrue($this->object->isHideIfStoreCreditBalanceEmpty());
    }

    /**
     * Test isDisplayPriceWithDiscount method
     */
    public function testIsDisplayPriceWithDiscount()
    {
        $this->scopeConfigMock->expects($this->once())
            ->method('isSetFlag')
            ->with(Config::XML_PATH_AW_STORECREDIT_IS_DISPLAY_DISCOUNT_PRICE, ScopeInterface::SCOPE_WEBSITE)
            ->willReturn(true);

        $this->assertTrue($this->object->isDisplayPriceWithDiscount());
    }

    /**
     * Test getEmailSender method
     */
    public function testGetEmailSender()
    {
        $senderName = 'test';

        $this->scopeConfigMock->expects($this->once())
            ->method('getValue')
            ->with(Config::XML_PATH_AW_STORECREDIT_SENDER_IDENTITY, ScopeInterface::SCOPE_WEBSITE, null)
            ->willReturn($senderName);

        $this->assertEquals($senderName, $this->object->getEmailSender());
    }

    /**
     * Test isSubscribeCustomersToNotificationsByDefault method
     */
    public function testIsSubscribeCustomersToNotificationsByDefault()
    {
        $this->scopeConfigMock->expects($this->once())
            ->method('getValue')
            ->with(
                Config::XML_PATH_AW_STORECREDIT_SUBSCRIBE_CUSTOMERS_TO_NOTIFICATIONS_BY_DEFAULT,
                ScopeInterface::SCOPE_WEBSITE,
                null
            )->willReturn(true);

        $this->assertTrue($this->object->isSubscribeCustomersToNotificationsByDefault());
    }

    /**
     * Test getEmailSenderName method
     */
    public function testGetEmailSenderName()
    {
        $senderName = 'test';
        $senderEmail = 'test@test.com';

        $this->scopeConfigMock->expects($this->at(0))
            ->method('getValue')
            ->with(Config::XML_PATH_AW_STORECREDIT_SENDER_IDENTITY, ScopeInterface::SCOPE_WEBSITE, null)
            ->willReturn($senderName);

        $this->scopeConfigMock->expects($this->at(1))
            ->method('getValue')
            ->with(
                'trans_email/ident_' . $senderName . '/name',
                ScopeInterface::SCOPE_WEBSITE,
                null
            )->willReturn($senderEmail);

        $this->assertEquals($senderEmail, $this->object->getEmailSenderName());
    }

    /**
     * Test getBalanceUpdateEmailTemplate method
     */
    public function testGetBalanceUpdateEmailTemplate()
    {
        $template = 'test';

        $this->scopeConfigMock->expects($this->once())
            ->method('getValue')
            ->with(
                Config::XML_PATH_AW_STORECREDIT_BALANCE_UPDATE_TEMPLATE_IDENTITY,
                ScopeInterface::SCOPE_STORE,
                null
            )->willReturn($template);

        $this->assertEquals($template, $this->object->getBalanceUpdateEmailTemplate());
    }

    /**
     * Test getBalanceUpdateActions method
     */
    public function testGetBalanceUpdateActions()
    {
        $value = '1,2';

        $this->scopeConfigMock->expects($this->once())
            ->method('getValue')
            ->with(
                Config::XML_PATH_AW_STORECREDIT_BALANCE_UPDATE_ACTIONS,
                ScopeInterface::SCOPE_STORE,
                null
            )->willReturn($value);

        $this->assertEquals($value, $this->object->getBalanceUpdateActions());
    }
}
