<?php

namespace Ktpl\General\Plugin\Model\Method;

class Available
{
    private $app_state;
    private $ktplHelper;

    public function __construct(
        \Magento\Framework\App\State $app_state,
        \Ktpl\General\Helper\Data $ktplHelper 
    ){
        $this->app_state = $app_state;
        $this->ktplHelper = $ktplHelper;
    }

    public function afterIsAvailable(\Magento\Payment\Model\Method\AbstractMethod $subject, $result)
    {
        $area_code  = $this->app_state->getAreaCode();
        if($area_code != \Magento\Backend\App\Area\FrontNameResolver::AREA_CODE){
            $configPath = 'payment/'.$subject->getCode().'/frontenable';
            $configValue = $this->ktplHelper->getConfig($configPath);
            if($configValue == "0"){
                    return false;
            }
        }
        return $result;
    }   
}