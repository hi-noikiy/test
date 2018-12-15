<?php
class EM_Mobapp_Helper_Data extends Mage_Core_Helper_Abstract
{
	public function __call($name, $args) {
		if (method_exists($this, $name))
			call_user_func_array(array($this, $name), $args);
		elseif (preg_match('/^get([^_][a-zA-Z0-9_]+)$/', $name, $m)) {
			$segs = explode('_', $m[1]);
			foreach ($segs as $i => $seg){
				//$segs[$i] = strtolower(preg_replace('/([^A-Z])([A-Z])/', '$1_$2', $seg));
				$seg = preg_replace('/([^A-Z])([A-Z])/', '$1_$2', $seg);
				$seg = preg_replace('/([A-Z])([A-Z])/', '$1_$2', $seg);
				$segs[$i] = strtolower(preg_replace('/([A-Z])([A-Z])/', '$1_$2', $seg));
			}
			$value = Mage::getStoreConfig('mobapp/'.implode('/', $segs));
			if (!$value) $value = @$args[0];
			return $value;
		}
		else 
			call_user_func_array(array($this, $name), $args);
	}

	public function getpopup($name,$text)
	{
		$div	=	'<span class="btn_icon icon_'.$name.'" onclick="return show_popup(\''.$name.'\')"></span>';
		$div	.=	'<div class="popup popup_'.$name.'" style="display:none">';
		$div	.=	$text;
		$div	.=	'</div>';
		
		return $div;
	}

	public function getlanguage(){
		$div	=	'<div class="moblang">';
		$div	.=		'<p><a href="'.Mage::getBaseUrl(Mage_Core_Model_Store::URL_TYPE_MEDIA).'em_mobapp/csv/appstore.csv" alt="">'.$this->__("Download file CSV").'</a></p>';
		$div	.=		'<p><input id="upload_lang" type="file" class="input-file required-entry required-entry moblang_input" disabled="disabled" value="" name="upload_lang"></p>';
		$div	.=	'</div>';

		return $div;
	}

	public function getExtensionVersion()
	{
		return (string) Mage::getConfig()->getNode()->modules->EM_Mobapp->version;
	}

	public function jsonEncode($data){
		if(!$data) return "";
		else{
			return json_encode($data,JSON_HEX_TAG);
		}
	}

	public function jsonDecode($data){
		if(!$data) return "";
		else{
			return json_decode($data,true);
		}
	}

	public function getGeneralInfo() {
		$link = $this->getServiceApi_Generalinfo();

		$ch = curl_init();
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_URL,$link);
		$result=curl_exec($ch);
		curl_close($ch);

		$data = json_decode($result, true);
		return $data;
	}

	public function checkVersion($glo_ver){
		$check = 0;
		$cur_ver = $this->getExtensionVersion();
		if(version_compare($cur_ver,$glo_ver) < 0 )	$check = 1;

		return $check;
	}
}