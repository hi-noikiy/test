<?php

/**
 * justselling Germany Ltd. EULA
 * http://www.justselling.de/
 * Read the license at http://www.justselling.de/lizenz
 *
 * Do not edit or add to this file, please refer to http://www.justselling.de for more information.
 *
 * @category    justselling
 * @package     justselling_configurator
 * @copyright   Copyright © 2012 justselling Germany Ltd. (http://www.justselling.de)
 * @license     http://www.justselling.de/lizenz
**/

class Justselling_Configurator_Helper_Thumbnail extends Mage_Core_Helper_Abstract
{
	protected $_imageUrl;

	 public function init($fileurl) {
	 	$this->_imageUrl = $fileurl;
	 	return $this;
	 }
	 
	 public function resize($width, $height = null) {
	 	return $this;
	 }

    public function setWatermarkSize() {
        return $this;
    }

	public function constrainOnly($flag)
	{
		return $this;
	}

	public function keepAspectRatio()
	{
		return $this;
	}
	public function keepFrame()
	{
		return $this;
	}

    public function __toString() {
        return $this->_imageUrl;
    }

}
