<?php

/**
 * Magestore.
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Magestore.com license that is
 * available through the world-wide-web at this URL:
 * http://www.magestore.com/license-agreement.html
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade this extension to newer
 * version in the future.
 *
 * @category    Magestore
 * @package     Magestore_Affiliateplus
 * @copyright   Copyright (c) 2012 Magestore (http://www.magestore.com/)
 * @license     http://www.magestore.com/license-agreement.html
 */
namespace Magestore\Affiliateplus\Controller\Account;

/**
 * Class Imagecaptcha
 * @package Magestore\Affiliateplus\Controller\Account
 */
class Imagecaptcha extends \Magestore\Affiliateplus\Controller\AbstractAction
{
    /**
     * Execute action
     */
    public function execute()
    {
        require_once($this->getBaseDir() .  '/lib/captcha/class.simplecaptcha.php');
        $config['BackgroundImage'] = $this->getBaseDir() . '/lib/captcha/white.png';
        $config['BackgroundColor'] = "FF0000";
        $config['Height'] = 30;
        $config['Width'] = 100;
        $config['Font_Size'] = 23;
        $config['Font'] = $this->getBaseDir() . '/lib/captcha/ARLRDBD.TTF';
        $config['TextMinimumAngle'] = 15;
        $config['TextMaximumAngle'] = 30;
        $config['TextColor'] = '2B519A';
        $config['TextLength'] = 4;
        $config['Transparency'] = 80;
        $captcha = new \SimpleCaptcha($config);
        $this->_getSession->setData('register_account_captcha_code', $captcha->Code);
    }





}
