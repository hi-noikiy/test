<?php

namespace Ess\M2ePro\Block\Adminhtml\Wizard\InstallationAmazon\Installation;

use Ess\M2ePro\Block\Adminhtml\Wizard\InstallationAmazon\Installation;

class ListingTutorial extends Installation
{
    //########################################

    protected function _construct()
    {
        parent::_construct();

        $this->updateButton('continue', 'label', $this->__('Create First Listing'));
        $this->updateButton('continue', 'class', 'primary');
    }

    protected function getStep()
    {
        return 'listingTutorial';
    }

    //########################################
}