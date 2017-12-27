<?php
/**
 * App api
 *
 * @author Beeketing <hi@beeketing.com>
 * @license MIT License (http://www.opensource.org/licenses/mit-license.php)
 */

namespace Beeketing\SalesPop\Core\Api;

use Beeketing\MagentoCommon\Api\CommonApi;

class App extends CommonApi
{
    /**
     * Setting helper
     *
     * @var \Beeketing\MagentoCommon\Libraries\SettingHelper
     */
    private $settingHelper;

    /**
     * App constructor.
     *
     * @param \Beeketing\MagentoCommon\Libraries\SettingHelper $settingHelper
     * @param \Beeketing\SalesPop\Core\Data\Constant $constant
     */
    public function __construct(
        \Beeketing\MagentoCommon\Libraries\SettingHelper $settingHelper,
        \Beeketing\SalesPop\Core\Data\Constant $constant
    ) {
        parent::__construct(SALESPOP_PATH, SALESPOP_API, $constant::APP_CODE);
        $this->settingHelper = $settingHelper;
    }

    /**
     * Init app
     */
    public function init()
    {
        // Set setting helper
        $this->settingHelper->setAppSettingKey(\Beeketing\SalesPop\Core\Data\Constant::APP_SETTING_KEY);
        $this->setSettingHelper($this->settingHelper);

        // Set api key
        $apiKey = $this->settingHelper->getSettings(\Beeketing\MagentoCommon\Data\Setting::SETTING_API_KEY);
        $this->setApiKey($apiKey);
    }
}
