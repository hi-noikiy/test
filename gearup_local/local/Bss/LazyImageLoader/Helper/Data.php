<?php
/**
* BSS Commerce Co.
*
* NOTICE OF LICENSE
*
* This source file is subject to the EULA
* that is bundled with this package in the file LICENSE.txt.
* It is also available through the world-wide-web at this URL:
* http://bsscommerce.com/Bss-Commerce-License.txt
*
* =================================================================
*                 MAGENTO EDITION USAGE NOTICE
* =================================================================
* This package designed for Magento COMMUNITY edition
* BSS Commerce does not guarantee correct work of this extension
* on any other Magento edition except Magento COMMUNITY edition.
* BSS Commerce does not provide extension support in case of
* incorrect edition usage.
* =================================================================
*
* @category   BSS
* @package    Bss_LazyImageLoad
* @author     Extension Team
* @copyright  Copyright (c) 2015-2016 BSS Commerce Co. ( http://bsscommerce.com )
* @license    http://bsscommerce.com/Bss-Commerce-License.txt
*/
class Bss_LazyImageLoader_Helper_Data extends Mage_Core_Helper_Abstract
{
	public function isEnabled(){
		$module = Mage::app()->getFrontController()->getRequest()->getModuleName();
		$controller = Mage::app()->getFrontController()->getRequest()->getControllerName();
		$action = Mage::app()->getFrontController()->getRequest()->getActionName();
		if(Mage::app()->getStore()->isAdmin() || 
			!Mage::getStoreConfig('lazyimageloader/general/active') || 
			Mage::helper('lazyimageloader')->regexMatchSimple(Mage::getStoreConfig('lazyimageloader/general/exclude_controllers'),"{$module}_{$controller}_{$action}",1) ||
			Mage::helper('lazyimageloader')->regexMatchSimple(Mage::getStoreConfig('lazyimageloader/general/exclude_path'),Mage::app()->getRequest()->getRequestUri(),2))
			return false;

		if(Mage::getStoreConfig('lazyimageloader/general/exclude_home_page')) {
			$is_homepage = Mage::getBlockSingleton('page/html_header')->getIsHomePage();
			if($is_homepage) return false;
		}

		return true;
	}

	public function isEnabledjQuery(){
		return Mage::getStoreConfigFlag('lazyimageloader/general/jquery');
	}

	public function getThreshold(){
		return Mage::getStoreConfig('lazyimageloader/general/threshold');
	}

	public function getLoadingWidth(){
		return Mage::getStoreConfig('lazyimageloader/general/loading_width');
	}

	public function getLazyImage(){
		$img =  Mage::getStoreConfig('lazyimageloader/general/loading');
		if(!$img || $img == '') {
			return $this->getLazyImg();
		}

		return Mage::getBaseUrl('media') .'lazyimage'.DS. $img;
	}

	public function lazyLoad($html) {
		$regex = '#<img([^>]*) src="([^"/]*/?[^".]*\.[^"]*)"(?!.*?notlazy)([^>]*)>#';
		if(preg_match('/MSIE/i',$_SERVER['HTTP_USER_AGENT'])) {
			$replace = '<noscript><img$1 src="$2" $3></noscript>';
			$replace .= '<img$1 src="data:image/gif;base64,R0lGODlhAQABAIAAAAAAAP///yH5BAEAAAAALAAAAAABAAEAAAIBRAA7" data-src="$2"$3 data-lazy="lazy">';
		}else {
			$replace = '<img$1 src="data:image/gif;base64,R0lGODlhAQABAIAAAAAAAP///yH5BAEAAAAALAAAAAABAAEAAAIBRAA7" data-src="$2"$3 data-lazy="lazy">';
		}
		$html = preg_replace($regex, $replace, $html);
		return $html;
	}


	public function regexMatchSimple($regex, $matchTerm,$type) {

		if (!$regex)
			return false;

		$rules = @unserialize($regex);

		if (empty($rules))
			return false;

		foreach ($rules as $rule) {
			$regex = trim($rule['bss_lazyimage'], '#');
			if($type == 1) {
				$regexs = explode('_', $regex);
				switch(count($regexs)) {
					case 1:
					$regex = $regex.'_index_index';
					break;
					case 2:
					$regex = $regex.'_index';
					break;
					default:
					break;
				}
			}

			$regexp = '#' . $regex . '#';
			if (@preg_match($regexp, $matchTerm))
				return true;

		}

		return false;

	}

	// public function lazyLoad2($html) {
	// 	$conditionalJsPattern = '/src\=\"([^\s]+(?=\.(bmp|gif|jpeg|jpg|png))\.\2)\"/';
	// 	preg_match_all($conditionalJsPattern,$html,$_matches);
	// 	foreach ($_matches[0] as $key => $match) {
	// 		$html = str_replace($match, 'src="data:image/gif;base64,R0lGODlhAQABAIAAAAAAAP///yH5BAEAAAAALAAAAAABAAEAAAIBRAA7" data-'.$match,$html);
	// 	}
	// 	return $html;
	// }

	protected function getLazyImg() {
		return Mage::getBaseUrl('skin')."frontend/base/default/images/bss/lazyload/loader.gif";
	}
}
