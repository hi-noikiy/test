<?php

namespace Ktpl\General\Plugin;

class Dob
{
    protected $_request;

    public function __construct(
        \Magento\Framework\App\Request\Http $request
    ) {

        $this->_request = $request;
    }
    
    public function aftergetHtmlExtraParams(\Magento\Customer\Block\Widget\Dob $subject, $result)
    {
        $modulename = $this->_request->getModuleName();
        $controller = $this->_request->getControllerName();
        $action = $this->_request->getActionName();
        if($modulename == 'customer' && $controller == 'account' && $action == 'create')
        { 
            $replace = htmlspecialchars('{"validate-date-19":true,');
            return  $result = preg_replace('/{/', $replace, $result, 1);
        }    
        
        return $result;
    }
}