<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2018 Amasty (https://www.amasty.com)
 * @package Amasty_SecurityAuth
 */


class  Amasty_SecurityAuth_Block_Adminhtml_Permissions_User_Edit_Tab_Auth
    extends Mage_Adminhtml_Block_Widget_Form
    implements Mage_Adminhtml_Block_Widget_Tab_Interface
{

    /**
     * Constructor
     */
    public function __construct()
    {
        parent::__construct();

        $this->setId('amsecurityauth_edit_permissions')
             ->setTitle($this->__('Two-Factor Settings'))
             ->setUseAjax(true);
    }

    protected function _prepareForm()
    {
        parent::_prepareForm();

        $userId = Mage::app()->getRequest()->getParam('user_id')
            ? Mage::app()->getRequest()->getParam('user_id')
            : Mage::getSingleton('core/session')->getUserIdTwoAuth();
        if (!$userId) {
           $this->getMessagesBlock()->addNotice('Two-Factor Authentication available only for existing Users');
        } else {
            Mage::getSingleton('core/session')->setUserIdTwoAuth(null);
        }

        /**
         * @var Amasty_SecurityAuth_Model_Auth
         */
        $userAuth = Mage::getModel('amsecurityauth/auth')->load($userId);

        if (!$userAuth->getId()) {
            $userAuth->setId($userId);
        }

        if (!$userAuth->getTwoFactorToken()) {
            $secret = $userAuth->createSecret();
        } else {
            $secret = $userAuth->getTwoFactorToken();
        }

        $qrCodeUrl = $userAuth->getQRCodeGoogleUrl($secret);

        $form = new Varien_Data_Form();

        $fieldset = $form->addFieldset(
            'amsecurityauth_general',
            array('legend' => Mage::helper('adminhtml')->__('General'))
        );

        $fieldset->addField('amsecurityauth_secret', 'hidden', array(
            'name' => 'amsecurityauth_secret',
            'value' => $secret,
        ));

        $fieldset->addField(
            'amsecurityauth_active', 'checkbox', array(
                'name'    => 'amsecurityauth_active',
                'label'   => Mage::helper('amsecurityauth')->__(
                    'Enable Two-Factor Authentication'
                ),
                'title'   => Mage::helper('amsecurityauth')->__(
                    'Enable Two-Factor Authentication'
                ),
                'value' => '1',
                'checked' => (int)$userAuth->getEnable() > 0 ? 'checked' : '',
            )
        );

        $configured = ($userAuth->getTwoFactorToken() && $userAuth->getEnable())
            ? 1 : 0;
        $fieldset->addField('amsecurityauth_configured', 'note', array(
                'name'  => 'amsecurityauth_configured',
                'label' => Mage::helper('amsecurityauth')->__('Status'),
                'title' => Mage::helper('amsecurityauth')->__('Status'),
                'text' => $configured ? Mage::helper('amsecurityauth')->__('Configured')
                    : Mage::helper('amsecurityauth')->__('Not Configured'),
            )
        );

        $fieldset = $form->addFieldset(
            'amsecurityauth_configuration',
            array(
                'legend' => Mage::helper('adminhtml')->__('Configuration'),
                'class' => !$userAuth->getEnable() ? 'no-display' : '',
            )
        );

        $helpMsg = Mage::helper('amsecurityauth')->__('Insert this secret key into Google Authenticator or scan QR code to generate Security Code');
        $afterElementHtml = '<p class="nm"><small>' . $helpMsg . '</small></p>';

        $fieldset->addField('twofactor_token', 'label', array(
                'name'  => 'twofactor_token',
                'label' => Mage::helper('amsecurityauth')->__('Secret Key'),
                'title' => Mage::helper('amsecurityauth')->__('Secret Key'),
                'value'  => $secret,
                'after_element_html' => $afterElementHtml,
            )
        );
        $fieldset->addField('twofactor_token_qr', 'label', array(
                'name'  => 'twofactor_token_qr',
                'label' => Mage::helper('amsecurityauth')->__('QR Code'),
                'title' => Mage::helper('amsecurityauth')->__('QR Code'),
                'after_element_html' => "<img src=\"$qrCodeUrl\" />"
            )
        );

        $helpMsg = Mage::helper('amsecurityauth')->__('Scan QR code above with Google Authenticator application, then enter the security code in this field and click Check Code link');
        $afterElementHtml = '<p class="nm"><small>' . $helpMsg . '</small></p>';

        $fieldset->addField('amsecurityauth_code', 'text', array(
                'name'  => 'amsecurityauth_code',
                'label' => Mage::helper('amsecurityauth')->__('Security Code'),
                'title' => Mage::helper('amsecurityauth')->__('Security Code'),
                'after_element_html' => $afterElementHtml,
            )
        );

        $fieldset->addField('check_code', 'link', array(
                'name'  => 'check_code',
                'style'   => "cursor: pointer;",
                'value' => 'Check Code',
                'onclick' => "verifyCode(
                    '".Mage::getUrl('adminhtml/amsecurityauth_auth/ajaxVerifyCode')."',
                    '".$userId."',
                    $('amsecurityauth_secret').value,
                    $('amsecurityauth_code').value
                )",
                'after_element_html' => "<span style='margin-left: 15px;' id='code-verification-message'></span><input type='hidden' id='is_configured' class='validate-is-configured' value={$configured} />",
            )
        );

        $this->setForm($form);
    }

    /**
     * Prepare label for tab
     *
     * @return string
     */
    public function getTabLabel()
    {
        return $this->__('Two-Factor Settings');
    }

    /**
     * Prepare title for tab
     *
     * @return string
     */
    public function getTabTitle()
    {
        return $this->__('Two-Factor Settings');
    }

    /**
     * Returns status flag about this tab can be shown or not
     *
     * @return true
     */
    public function canShowTab()
    {
        if (!Mage::app()->getRequest()->getParam('user_id') && !Mage::getSingleton('core/session')->getUserIdTwoAuth()) {
            $this->getMessagesBlock()->addNotice('Two-Factor Authentication Tab is only available when editing users.');
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
	