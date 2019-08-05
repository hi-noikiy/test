<?php
/**
 * Copyright � 2015 Collins Harper. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace CollinsHarper\CanadaPost\Block\Adminhtml\System\Config\Signup;

use \Magento\Framework\App\Helper\Context;

//class Form extends \Magento\Backend\Block\Widget\Container
class Form extends \Magento\Backend\Block\Template
{
    /**
     * @var string
     */
    protected $_template = 'system/config/signup/form.phtml';

    /**
     * @var \CollinsHarper\CanadaPost\Helper\Data
     */
    protected $cpHelper;

    /**
     * @var \Magento\Framework\View\Asset\Repository
     */
    protected $assetRepo;
    
    /**
     * @var \Magento\Backend\Model\Session 
     */
    protected $backendSession;


    /**
     * @param \Magento\Backend\Block\Widget\Context $context
     * @param \Magento\Framework\View\Asset\Repository $assetRepo
     * @param \CollinsHarper\CanadaPost\Helper\Data $cpHelper
     * @param \Magento\Backend\Model\Session $backendSession
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \CollinsHarper\CanadaPost\Helper\Data $cpHelper,
        array $data = [])
    {
        $this->assetRepo = $context->getAssetRepository();

        $this->cpHelper = $cpHelper;
        $this->backendSession = $context->getBackendSession();

        parent::__construct($context, $data);
    }

    /**
     * Prepare button and grid
     *
     * @return \CollinsHarper\CanadaPost\Block\Adminhtml\System\Config\Signup\Form
     */
    protected function _prepareLayout()
    {
        return parent::_prepareLayout();
    }

    /**
     * 
     * @return string
     */
    public function getPostUrl()
    {
        return $this->cpHelper->getAdminSignUpFormUrl();

    }

    /**
     * 
     * @return array
     */
    public function getPostData()
    {
        $registrationToken = (string)$this->cpHelper->getAdminSignUpRegistrationToken();
        $this->backendSession->setCanadapostRegistrationToken($registrationToken);
        return $this->cpHelper->getAdminSignUpFormData($this->getUrl('cpcanadapost/signup/back'), $registrationToken);
    }

    /**
     * 
     * @return string
     */
    public function getLoadingImageUrl()
    {
        return $this->assetRepo->getUrl('CollinsHarper_CanadaPost::ajax-loader.gif');
    }


}
