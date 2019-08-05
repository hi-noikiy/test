<?php 

namespace Ktpl\General\Plugin;
use Magento\Store\Model\ScopeInterface;
class Config{
	protected $_request;

    public function __construct(
        \Magento\Framework\App\Request\Http $request,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
    ) {

        $this->_request = $request;
        $this->scopeConfig = $scopeConfig;
    }

	public  function afterEmailRecipient(\Magento\Contact\Model\Config $subject, $result){
		
		$form_wholeseller = $this->_request->getParam('form');
		$emailWholesale   = $this->scopeConfig->getValue(
           	 				  'contact/email/recipient_email_wholesale',
            				   ScopeInterface::SCOPE_STORE
        				    );

		if($form_wholeseller == "wholeseller" && !empty($emailWholesale))
		{
			return $emailWholesale;
        }

		return $result;
	}
}
?>