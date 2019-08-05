<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2018 Amasty (https://www.amasty.com)
 * @package Amasty_Affiliate
 */


namespace Amasty\Affiliate\Block\SocialButtons;

class Product extends \Amasty\Affiliate\Block\SocialButtons\AbstractButtons
{
    /**
     * @var string
     */
    protected $_template = 'social_buttons/buttons.phtml';


    public function showConfig()
    {
        return $this->_scopeConfig->getValue('amasty_affiliate/friends/on_product_details');
    }
}
