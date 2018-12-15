<?php
class EM_Mobapp_Block_Adminhtml_Appstore_Edit_Tab_Store extends Mage_Adminhtml_Block_Widget_Form
{
	public function _prepareLayout()
    {
		return parent::_prepareLayout();
    }

	public function _toHtml(){
		$this->setTemplate('em_mobapp/manage_store.phtml');

		$data	=	Mage::registry('mobapp_data');
		$data	=	$data->getData();
		$data['theme']		=	Mage::helper("mobapp")->jsonDecode($data['theme']);
		$data['paygate']	=	Mage::helper("mobapp")->jsonDecode($data['paygate']);

		$selecthtml	=	$this->getSelect($data['color'],$data['theme']);
		$checkboxhtml	=	$this->getcheckbox($data['paygate']);

		$this->assign('selecthtml', $selecthtml);
		$this->assign('checkboxhtml', $checkboxhtml);
		$this->assign('data', $data);

		return parent::_toHtml();
	}

	protected function getSelect($color,$theme){
		$html = "";
		if($theme){
			foreach($theme	as $key=>$value){
				if($color == $value['label'])
					$html .= '<span style="background-color:'.$value['code'].';width:20px;height:20px">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</span>'; 
			}
		}else	$html	.=	"No Theme";

		return $html;
	}
	
	protected function getcheckbox($paygate){
		$method	=	Mage::Helper('payment')->getStoreMethods();
		$check1	= $check2 = $check3	= $check4 = $check5	= $check6 = $check7	= $check8 = "";
		$dis1	= $dis2 = $dis3	= $dis4 = $dis5	= $dis6 = $dis7	= $dis8 = 'disabled="disabled"';
		//echo '<pre>';print_r($method);exit;

		$html = '';
		if($method){
			foreach($method as $key=>$val){
				if($val->getCode() == "paypal_mecl"){	
					$dis1	=	'';
					if($paygate){
						if(in_array("paypal_mecl",$paygate) == 1)	$check1	= "checked";
					}
				}
				elseif($val->getCode() == "ccsave"){
					$dis2	=	'';
					if($paygate){
						if(in_array("ccsave",$paygate) == 1)	$check2	= "checked";
					}
				}
				elseif($val->getCode() == "checkmo"){
					$dis3	=	'';
					if($paygate){
						if(in_array("checkmo",$paygate) == 1)	$check3	= "checked";
					}
				}
				elseif($val->getCode() == "free"){
					$dis4	=	'';
					if($paygate){
						if(in_array("free",$paygate) == 1)	$check4	= "checked";
					}
				}
				elseif($val->getCode() == "banktransfer"){
					$dis5	=	'';
					if($paygate){
						if(in_array("banktransfer",$paygate) == 1)	$check5	= "checked";
					}
				}
				elseif($val->getCode() == "cashondelivery"){
					$dis6	=	'';
					if($paygate){
						if(in_array("cashondelivery",$paygate) == 1)	$check6	= "checked";
					}
				}
				elseif($val->getCode() == "purchaseorder"){
					$dis7	=	'';
					if($paygate){
						if(in_array("purchaseorder",$paygate) == 1)	$check7	= "checked";
					}
				}
				/*elseif($val->getCode() == "cc"){
					$dis8	=	'';
					if($paygate){
						if(in_array("cc",$paygate) == 1)	$check8	= "checked";
					}
				}*/
			}
			$html	.= '<input type="checkbox" name="paygate[]" class="input-text" '.$dis1.' '.$check1.' value="paypal_mecl" />'.Mage::helper('mobapp')->__("PayPal Express Checkout").'<br />';
			$html	.= '<input type="checkbox" name="paygate[]" class="input-text" '.$dis2.' '.$check2.' value="ccsave" />'.Mage::helper('mobapp')->__("Saved CC").'<br />';
			/*$html	.= '<input type="checkbox" name="paygate[]" class="input-text" '.$dis8.' '.$check8.' value="cc" />'.Mage::helper('mobapp')->__("Credit Card").'<br />';
			$html	.= '<input type="checkbox" name="paygate[]" class="input-text" '.$dis3.' '.$check3.' value="checkmo" />'.Mage::helper('mobapp')->__("Check / Money Order").'<br />';
			$html	.= '<input type="checkbox" name="paygate[]" class="input-text" '.$dis4.' '.$check4.' value="free" />'.Mage::helper('mobapp')->__("Zero Subtotal Checkout").'<br />';
			$html	.= '<input type="checkbox" name="paygate[]" class="input-text" '.$dis5.' '.$check5.' value="banktransfer" />'.Mage::helper('mobapp')->__("Bank Transfer Payment").'<br />';
			$html	.= '<input type="checkbox" name="paygate[]" class="input-text" '.$dis6.' '.$check6.' value="cashondelivery" />'.Mage::helper('mobapp')->__("Cash On Delivery Payment").'<br />';
			$html	.= '<input type="checkbox" name="paygate[]" class="input-text" '.$dis7.' '.$check7.' value="purchaseorder" />'.Mage::helper('mobapp')->__("Purchase Order");*/
		}else
			$html == Mage::helper('mobapp')->__("No paygate is active");

		return $html;
	}
}