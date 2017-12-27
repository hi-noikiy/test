<?php
/**
 * Adminhtml index
 *
 * @author Beeketing <hi@beeketing.com>
 * @license MIT License (http://www.opensource.org/licenses/mit-license.php)
 */

namespace Beeketing\SalesPop\Block\Adminhtml\Index;

use Beeketing\MagentoCommon\Data\Setting;
use Magento\Framework\View\Element\Template;

class Index extends Template
{
    /**
     * Module app api
     *
     * @var \Beeketing\SalesPop\Core\Api\App
     */
    private $app;

    /**
     * Module setting helper
     *
     * @var \Beeketing\MagentoCommon\Libraries\SettingHelper
     */
    private $settingHelper;

    /**
     * Backend Auth model
     *
     * @var \Magento\Backend\Model\Auth
     */
    private $auth;

    /**
     * Index constructor.
     *
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param \Magento\Backend\Model\Auth $auth
     * @param \Beeketing\SalesPop\Core\Api\App $app
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Backend\Model\Auth $auth,
        \Beeketing\SalesPop\Core\Api\App $app,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->auth = $auth;
        $this->app = $app;
    }

    /**
     * Get js app data
     *
     * @return string
     */
    public function getJSAppData()
    {
        $this->app->init();
        $this->settingHelper = $this->app->getSettingHelper();

        $beeketingEmail = false;
        if (!$this->app->getApiKey()) {
            $beeketingEmail = $this->app->getBeeketingUserEmail();
        }

        return json_encode([
            'plugin_url' => $this->getViewFileUrl('Beeketing_SalesPop'),
            'api_urls' => $this->app->getApiUrls(),
            'api_key' => $this->settingHelper->getSettings(Setting::SETTING_API_KEY),
            'beeketing_email' => $beeketingEmail,
            'ajax_url' => $this->getUrl('salespop/index/ajax'),
            'user_display_name' => $this->auth->getUser()->getFirstName(),
            'oauth_sign_in_url' => $this->app->getOAuthSignInUrl(),
        ]);
    }
}
