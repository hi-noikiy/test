<?php
require_once dirname(dirname(dirname(dirname(__FILE__)))) . "/config/dotMailerConfig.php";
class Dotdigitalgroup_Dotmailer_Block_System_Config_Banner extends Mage_Adminhtml_Block_Abstract
{
    protected $_template = 'dotdigitalgroup/dotmailer/system/config/banner.phtml';
    public $bannerUrl;
    public function __construct()
    {
		$dotMailerConfig = new dotMailerConfig();
		$this->bannerUrl = $dotMailerConfig->getBannerUrl();
    }

    public function render(Varien_Data_Form_Element_Abstract $element)
    {
        return $this->toHtml();
    }
}
