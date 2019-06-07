<?php


class Admitad_Tracking_Adminhtml_Admitad_SettingsController extends Mage_Adminhtml_Controller_Action
{
    public function indexAction()
    {
        $this->_title($this->__('System'))->_title($this->__('Admitad Settings'));

        $this->loadLayout();
        $this->_setActiveMenu('admitad/settings');
        $this->_validateToken();
        $this->_addContent($this->getLayout()->createBlock('tracking/adminhtml_settings_edit'));
        $this->renderLayout();
    }


    public function saveAction()
    {
        /** @var Admitad_Tracking_Helper_Api $api */
        $api = Mage::helper('tracking/api');
        $clientId = $this->getRequest()->getParam('client_id', false);
        $clientSecret = $this->getRequest()->getParam('client_secret', false);

        Mage::getConfig()->saveConfig('admitadtracking/general/param_name', $this->getRequest()->getParam('param_name', false), 'default', 0);

        if ($clientSecret && $clientId) {
            $api->authorize($clientId, $clientSecret);
            if ($api->getAccessToken() && $api->getRefreshToken()) {
                $info = $api->getAdvertiserInfo();
                if (isset($info['postback_key']) && isset($info['campaign_code'])) {
                    Mage::getConfig()->saveConfig('admitadtracking/general/client_id', $clientId, 'default', 0);
                    Mage::getConfig()->saveConfig('admitadtracking/general/client_secret', $clientSecret, 'default', 0);
                    Mage::getConfig()->saveConfig('admitadtracking/general/access_token', $api->getAccessToken(), 'default', 0);
                    Mage::getConfig()->saveConfig('admitadtracking/general/refresh_token', $api->getRefreshToken(), 'default', 0);
                    Mage::getConfig()->saveConfig('admitadtracking/general/expires_in', $api->getExpiresIn() + time(), 'default', 0);
                    Mage::getConfig()->saveConfig('admitadtracking/general/campaign_code', $info['campaign_code'], 'default', 0);
                    Mage::getConfig()->saveConfig('admitadtracking/general/postback_key', $info['postback_key'], 'default', 0);
                } else {
                    Mage::getSingleton('adminhtml/session')->addError(Mage::helper('tracking')->__('Check your credentials'));
                    $this->getResponse()->setRedirect($this->getUrl("*/*/"));

                    return;
                }
            } else {
                Mage::getSingleton('adminhtml/session')->addError(Mage::helper('tracking')->__('Check your credentials'));
                $this->getResponse()->setRedirect($this->getUrl("*/*/"));

                return;
            }
        } else {
            Mage::getConfig()->saveConfig('admitadtracking/general/configuration', json_encode($this->getRequest()->getParam('actions', array())), 'default', 0);
        }

        Mage::getSingleton('adminhtml/session')->addSuccess(Mage::helper('tracking')->__('The settings has been saved.'));
        Mage::app()
            ->getCacheInstance()
            ->cleanType('config');
        $this->getResponse()->setRedirect($this->getUrl("*/*/"));
    }

    public function revokeAction()
    {
        Mage::getConfig()->deleteConfig('admitadtracking/general/client_id');
        Mage::getConfig()->deleteConfig('admitadtracking/general/client_secret');
        Mage::getConfig()->deleteConfig('admitadtracking/general/access_token');
        Mage::getConfig()->deleteConfig('admitadtracking/general/refresh_token');
        Mage::getConfig()->deleteConfig('admitadtracking/general/expires_in');
        Mage::getConfig()->deleteConfig('admitadtracking/general/campaign_code');
        Mage::getConfig()->deleteConfig('admitadtracking/general/postback_key');

        Mage::getSingleton('adminhtml/session')->addSuccess(Mage::helper('tracking')->__('All keys have been revoked.'));
        Mage::app()
            ->getCacheInstance()
            ->cleanType('config');

        $this->getResponse()->setRedirect($this->getUrl("*/*/"));
    }

    protected function _isAllowed()
    {
        return Mage::getSingleton('admin/session')->isAllowed('admitad/settings');
    }

    protected function _validateToken()
    {
        /** @var Admitad_Tracking_Helper_Api $api */
        $api = Mage::helper('tracking/api');

        $accessToken = Mage::getStoreConfig(
            'admitadtracking/general/access_token',
            Mage::app()->getStore()
        );
        $refreshToken = Mage::getStoreConfig(
            'admitadtracking/general/refresh_token',
            Mage::app()->getStore()
        );
        $clientId = Mage::getStoreConfig(
            'admitadtracking/general/client_id',
            Mage::app()->getStore()
        );

        $clientSecret = Mage::getStoreConfig(
            'admitadtracking/general/client_secret',
            Mage::app()->getStore()
        );

        if ($accessToken) {
            $api->setAccessToken($accessToken);

            if ($api->isExpired()) {
                if ($api->refreshToken($clientId, $clientSecret, $refreshToken)) {
                    Mage::getConfig()->saveConfig('admitadtracking/general/access_token', $api->getAccessToken(), 'default', 0);
                    Mage::getConfig()->saveConfig('admitadtracking/general/refresh_token', $api->getRefreshToken(), 'default', 0);
                    Mage::getConfig()->saveConfig('admitadtracking/general/expires_in', $api->getExpiresIn() + time(), 'default', 0);
                    Mage::app()
                        ->getCacheInstance()
                        ->cleanType('config');
                } else {
                    Mage::getConfig()->deleteConfig('admitadtracking/general/client_id');
                    Mage::getConfig()->deleteConfig('admitadtracking/general/client_secret');
                    Mage::getConfig()->deleteConfig('admitadtracking/general/access_token');
                    Mage::getConfig()->deleteConfig('admitadtracking/general/refresh_token');
                    Mage::getConfig()->deleteConfig('admitadtracking/general/expires_in');
                    Mage::getSingleton('adminhtml/session')->addError(Mage::helper('tracking')->__('Check your credentials'));
                    Mage::app()
                        ->getCacheInstance()
                        ->cleanType('config');
                    $this->getResponse()->setRedirect($this->getUrl("*/*/"));

                    return;
                }
            }
        }
    }

}
