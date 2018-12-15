<?php
class Thinkhigh_VPCpaymentgateway_Model_Standard extends Mage_Payment_Model_Method_Abstract {
	protected $_code = 'vpcpaymentgateway';
	
	protected $_isInitializeNeeded      = true;
	protected $_canUseInternal          = true;
	protected $_canUseForMultishipping  = false;
 //   protected $_formBlockType = 'vpcpaymentgateway/form_vpcpaymentgateway';
    protected $_infoBlockType = 'vpcpaymentgateway/info_vpcsave';
	
	public function getOrderPlaceRedirectUrl() {
		return Mage::getUrl('vpcpaymentgateway/payment/redirect', array('_secure' => true));
	}
    
    public function assignData($data){
        if (!($data instanceof Varien_Object)) {
            $data = new Varien_Object($data);
        }
        $info = $this->getInfoInstance();
        $info->setVpcCard($data->getVpcCard())
        ->setVpcCardNum($info->encrypt($data->getVpcCardNum()))
        ->setVpcCardExp($data->getVpcCardExp())
        ->setVpcCardSecurityCode($info->encrypt($data->getVpcCardSecurityCode()));
        return $this;
    }

    public function prepareSave()
    {/*
        $info = $this->getInfoInstance();
        $info->setVpcCardNum($info->encrypt($info->getVpcCardNum()));
        return $this;*/
    }
}
?>