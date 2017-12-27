<?php
/**
 * FrontSendResponseBefore observer
 *
 * @author Beeketing <hi@beeketing.com>
 * @license MIT License (http://www.opensource.org/licenses/mit-license.php)
 */

namespace Beeketing\SalesPop\Observer\Controller;

class FrontSendResponseBefore implements \Magento\Framework\Event\ObserverInterface
{
    /**
     * Bridge api
     *
     * @var \Beeketing\MagentoCommon\Api\BridgeApi
     */
    private $bridgeApi;

    /**
     * Setting helper
     *
     * @var \Beeketing\MagentoCommon\Libraries\SettingHelper
     */
    private $settingHelper;

    /**
     * FrontSendResponseBefore constructor.
     *
     * @param \Beeketing\MagentoCommon\Api\BridgeApi $bridgeApi
     * @param \Beeketing\MagentoCommon\Libraries\SettingHelper $settingHelper
     */
    public function __construct(
        \Beeketing\MagentoCommon\Api\BridgeApi $bridgeApi,
        \Beeketing\MagentoCommon\Libraries\SettingHelper $settingHelper
    ) {
        $this->bridgeApi = $bridgeApi;
        $this->settingHelper = $settingHelper;
    }

    /**
     * Execute observer
     *
     * @param \Magento\Framework\Event\Observer $observer
     * @return void
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $this->settingHelper->setAppSettingKey(\Beeketing\SalesPop\Core\Data\Constant::APP_SETTING_KEY);
        $this->bridgeApi->setSettingHelper($this->settingHelper);
        $this->bridgeApi->handleRequest();
    }
}
