<?php
class EM_Apiios_Model_Api2_Mobapp_Rest_Abstract extends EM_Apiios_Model_Api2_Products
{
    protected function _retrieve(){
		$id	=	$this->getRequest()->getParam('id');
		$result = array();
		$model  = Mage::getModel('mobapp/store')->load($id);
		if ($model->getId() || $id != 0) {
			$result['result']	=	$model->getData();
			unset($result['result']['theme'],$result['result']['creation_time'],$result['result']['update_time']);
			$tmp['slideshow']['mobiles']['large'] 	= $this->buildslideshow($result['result']['slideshow'],"");
			$tmp['slideshow']['mobiles']['smaller'] = $this->buildslideshow($result['result']['slideshow2'],2);
			$tmp['slideshow']['tables']['large'] 	= $this->buildslideshow($result['result']['slideshow3'],3);
			$tmp['slideshow']['tables']['smaller'] 	= $this->buildslideshow($result['result']['slideshow4'],4);
			unset($result['result']['slideshow2'],$result['result']['slideshow3'],$result['result']['slideshow4']);
			$result['result']['slideshow']		=	$tmp['slideshow'];
			
			$result['result']['paygate']		=	$this->buildpaygate($result['result']['paygate']);
		}else{
			$result['result']['message']	=	"Not item";
		}
		return $result;
    }

	protected function buildslideshow($data,$level=""){
		$data	=	unserialize($data);
		if(count($data) > 0 ){
			foreach($data as $key=>$val)
				$data[$key]['url']	=	Mage::getBaseUrl(Mage_Core_Model_Store::URL_TYPE_MEDIA).'em_mobapp/slideshow'.$level.'/'.$val['url'];
		}
		return array_values($data);
	}

	protected function buildpaygate($data){
		$data	=	unserialize($data);
		return $data;
	}

}
?>