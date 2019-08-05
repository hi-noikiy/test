<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2018 Amasty (https://www.amasty.com)
 * @package Amasty_SecurityAuth
 */

namespace Amasty\SecurityAuth\Block\User\Edit\Tab;

use Magento\Backend\Block\Widget\Grid\Column;
use Magento\Backend\Block\Widget\Tab\TabInterface;
use Magento\Framework\App\Console\Request;

class Auth extends \Magento\Backend\Block\Widget\Form\Generic implements TabInterface
{
    /**
     * Core registry
     *
     * @var \Magento\Framework\Registry
     */
    protected $_coreRegistry = null;

    /**
     * @var \Magento\Authorization\Model\ResourceModel\Role\CollectionFactory
     */
    protected $_userRolesFactory;

    /**
     * @var \Magento\Framework\Json\EncoderInterface
     */
    protected $_jsonEncoder;

    /**
     * @var Request
     */
    protected $_request;

    /**
     * @var \Amasty\SecurityAuth\Model\Auth
     */
    protected $authRepository;

    /**
     * @var \Amasty\SecurityAuth\Helper\Data
     */
    protected $helper;

    /**
     * @var \Magento\Config\Model\Config\Source\Yesno
     */
    protected $yesNo;

    /**
     * Auth constructor.
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Framework\Data\FormFactory $formFactory
     * @param \Magento\Framework\Json\EncoderInterface $jsonEncoder
     * @param \Magento\Authorization\Model\ResourceModel\Role\CollectionFactory $userRolesFactory
     * @param \Magento\Framework\Registry $coreRegistry
     * @param \Amasty\SecurityAuth\Model\AuthRepository $authRepository
     * @param \Amasty\SecurityAuth\Helper\Data $helper
     * @param \Magento\Config\Model\Config\Source\Yesno $yesno
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Data\FormFactory $formFactory,
        \Magento\Framework\Json\EncoderInterface $jsonEncoder,
        \Magento\Authorization\Model\ResourceModel\Role\CollectionFactory $userRolesFactory,
        \Magento\Framework\Registry $coreRegistry,
        \Amasty\SecurityAuth\Model\AuthRepository $authRepository,
        \Amasty\SecurityAuth\Helper\Data $helper,
        \Magento\Config\Model\Config\Source\Yesno $yesNo,
        array $data = []
    ) {
        $this->_jsonEncoder = $jsonEncoder;
        $this->_userRolesFactory = $userRolesFactory;
        $this->_coreRegistry = $coreRegistry;
        $this->_request = $context->getRequest();
        $this->authRepository = $authRepository;
        $this->helper = $helper;
        $this->yesNo = $yesNo;
        parent::__construct($context, $registry, $formFactory, $data);
    }

    /**
     * Class constructor
     *
     * @return void
     */
    protected function _construct()
    {
        parent::_construct();
        $this->setData('id', 'securityauth_edit_permissions');
        $this->setTitle(__('Two-Factor Settings'));
        $this->setUseAjax(true);
    }

    /**
     * Prepare form fields
     *
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     * @return \Magento\Backend\Block\Widget\Form
     */
    protected function _prepareForm()
    {
        parent::_prepareForm();
        $userId = $this->_request->getParam('user_id')
            ? $this->_request->getParam('user_id')
            : $this->_session->getUserIdTwoAuth();

        if (!$userId) {
            $this->_layout->getMessagesBlock()
                ->addNotice(__('Two-Factor Authentication available only for existing Users'));
        } else {
            $this->_session->setUserIdTwoAuth(null);
        }

        $userAuth = $this->authRepository->getByUserId($userId);
        if (!$userAuth->getUserId()) {
            $userAuth->setUserId($userId);
        }

        if (!$userAuth->getTwoFactorToken()) {
            $secret = $this->helper->createSecret();
        } else {
            $secret = $userAuth->getTwoFactorToken();
        }

        $qrCodeUrl = $this->helper->getQRCodeGoogleUrl($secret, $userAuth->getUserId());

        /** @var \Magento\Framework\Data\Form $form */
        $form = $this->_formFactory->create();
        $htmlIdPrefix = $form->getHtmlIdPrefix();
        $fieldset = $form->addFieldset(
            'securityauth_general',
            [
                'legend' => __('General')
            ]
        );

        $fieldset->addField(
            'securityauth_secret',
            'hidden',
            [
                'name' => 'securityauth_secret',
                'value' => $secret
            ]
        );

        $fieldset->addField(
            'securityauth_active',
            'select',
            [
                'name' => 'securityauth_active',
                'label' => __('Enable TFA'),
                'title' => __('Enable TFA'),
                'values' => $this->yesNo->toOptionArray(),
                'value' => $userAuth->getEnable()
            ]
        );

        $configured = ($userAuth->getTwoFactorToken() && $userAuth->getEnable()) ? 1 : 0;
        $fieldset->addField(
            'securityauth_configured',
            'note',
            [
                'name' => 'securityauth_configured',
                'label' => __('Status'),
                'title' => __('Status'),
                'text' => $configured ? __('Configured') : __('Not Configured')
            ]
        );
        $helpMsg = __('Insert this secret key into Google Authenticator or scan QR code to generate Security Code');
        $afterElementHtml = '<p id="twofactor_token" class="nm"><small>' . $helpMsg . '</small></p>';
        $fieldset->addField(
            'twofactor_token',
            'label',
            [
                'name' => 'twofactor_token',
                'label' => __('Secret Key'),
                'title' => __('Secret Key'),
                'value' => $secret,
                'after_element_html' => $afterElementHtml,
            ]
        );

        $fieldset->addField(
            'twofactor_token_qr',
            'label',
            [
                'name' => 'twofactor_token_qr',
                'label' => __('QR Code'),
                'title' => __('QR Code'),
                'after_element_html' => "<img id='twofactor_token_qr' src=\"$qrCodeUrl\" />"
            ]
        );

        $helpMsg = __('Scan QR code above with Google Authenticator application,
         then enter the security code in this field and click Check Code link');
        $afterElementHtml = '<p class="nm"><small>' . $helpMsg . '</small></p>';
        $fieldset->addField(
            'securityauth_code',
            'text',
            [
                'name' => 'securityauth_code',
                'label' => __('Security Code'),
                'title' => __('Security Code'),
                'after_element_html' => $afterElementHtml,
            ]
        );

        $fieldset->addField(
            'check_code',
            'link',
            [
                'name' => 'check_code',
                'style' => "cursor: pointer;",
                'label' => __('Security Code'),
                'title' => __('Security Code'),
                'onclick' => "verifyCode(
                    '" . $this->getVerifiedUrl() . "',
                    '" . $userId . "',
                    $('securityauth_secret').value,
                    $('securityauth_code').value
                )",
                'value' => __('Check Code'),
                'after_element_html' =>
                    "<span style='margin-left: 15px;'  id='code-verification-message'></span>" .
                    "<input type='hidden' id='is_configured'  class='validate-is-configured' value={$configured} />",
            ]
        );

        $this->setChild(
            'form_after',
            $this->getLayout()->createBlock(
                'Magento\Backend\Block\Widget\Form\Element\Dependence'
            )->addFieldMap(
                "{$htmlIdPrefix}securityauth_active",
                'securityauth_active'
            )->addFieldMap(
                "{$htmlIdPrefix}twofactor_token",
                'twofactor_token'
            )->addFieldMap(
                "{$htmlIdPrefix}twofactor_token_qr",
                'twofactor_token_qr'
            )->addFieldMap(
                "{$htmlIdPrefix}securityauth_code",
                'securityauth_code'
            )->addFieldMap(
                "{$htmlIdPrefix}check_code",
                'check_code'
            )->addFieldDependence(
                'twofactor_token',
                'securityauth_active',
                '1'
            )->addFieldDependence(
                'twofactor_token_qr',
                'securityauth_active',
                '1'
            )->addFieldDependence(
                'securityauth_code',
                'securityauth_active',
                '1'
            )->addFieldDependence(
                'check_code',
                'securityauth_active',
                '1'
            )
                /**
                 * For dependency hidden level
                 */
                ->addConfigOptions(['levels_up' => 2])
        );

        $this->setForm($form);
    }

    /**
     * @return string
     */
    protected function getVerifiedUrl()
    {
        return $this->getUrl('amasty_securityauth/securityauth/verify');
    }

    /**
     * Prepare label for tab
     *
     * @return string
     */
    public function getTabLabel()
    {
        return __('Two-Factor Settings');
    }

    /**
     * Prepare title for tab
     *
     * @return string
     */
    public function getTabTitle()
    {
        return __('Two-Factor Settings');
    }

    /**
     * Returns status flag about this tab can be shown or not
     *
     * @return true
     */
    public function canShowTab()
    {
        if (!$this->_request->getParam('user_id') && !$this->_session->getUserIdTwoAuth()) {
            $this->_layout->getMessagesBlock()->addNotice(
                __('Two-Factor Authentication Tab is only available when editing users.')
            );

            return false;
        }

        return true;
    }

    /**
     * Returns status flag about this tab hidden or not
     *
     * @return true
     */
    public function isHidden()
    {
        return false;
    }
}
