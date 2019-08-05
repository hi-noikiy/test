<?php
namespace CollinsHarper\Moneris\Block\Frontend;

class Redirect extends AbstractBlock
{
    protected function getRedirectForm()
    {
        return $this->checkoutSession->getMonerisccMpiForm();
    }
}
