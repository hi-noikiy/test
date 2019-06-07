<?php
class Oye_SocialAuth_Block_Facebook_Account extends Mage_Core_Block_Template
{
    protected $client = null;
    protected $userInfo = null;

    protected function _construct() {
        parent::_construct();

        $this->client = Mage::getSingleton('oye_socialauth/facebook_client');
        if(!($this->client->isEnabled())) {
            return;
        }

        $this->userInfo = Mage::registry('oye_socialauth_facebook_userinfo');

        $this->setTemplate('oye/socialauth/facebook/account.phtml');
    }

    protected function _hasUserInfo()
    {
        return (bool) $this->userInfo;
    }

    protected function _getFacebookId()
    {
        return $this->userInfo->id;
    }

    protected function _getStatus()
    {
        if(!empty($this->userInfo->link)) {
            $link = '<a href="'.$this->userInfo->link.'" target="_blank">'.
                    $this->htmlEscape($this->userInfo->name).'</a>';
        } else {
            $link = $this->userInfo->name;
        }

        return $link;
    }

    protected function _getEmail()
    {
        return $this->userInfo->email;
    }

    protected function _getPicture()
    {
        if(!empty($this->userInfo->picture)) {
            return Mage::helper('oye_socialauth/facebook')
                    ->getProperDimensionsPictureUrl($this->userInfo->id,
                            $this->userInfo->picture->data->url);
        }

        return null;
    }

    protected function _getName()
    {
        return $this->userInfo->name;
    }

    protected function _getGender()
    {
        if(!empty($this->userInfo->gender)) {
            return ucfirst($this->userInfo->gender);
        }

        return null;
    }

    protected function _getBirthday()
    {
        if(!empty($this->userInfo->birthday)) {
            $birthday = date('F j, Y', strtotime($this->userInfo->birthday));
            return $birthday;
        }

        return null;
    }

}