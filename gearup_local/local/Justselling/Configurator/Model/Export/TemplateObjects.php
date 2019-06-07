<?php


class Justselling_Configurator_Model_Export_TemplateObjects {

	/** @var array */
	private $_objects = null;
	/**
	 * Constructor.
	 *
	 * @param array $objects
	 * @throws Mage_Exception on any initilization error
	 */
	public function __construct(array $objects) {
		if (empty($objects)) throw new Mage_Exception("TemplateObjects constructor failed - no object found!");
		$this->_objects = $objects;
	}

	/**
	 * Returns the template instance.
	 *
	 * @return Justselling_Configurator_Model_Template|bool
	 * @throws Mage_Exception
	 */
	public function getTemplate() {
		$instance = $this->getInstancesOfClass('Justselling_Configurator_Model_Template');
		if (count($instance) == 1) {
			return $instance[0];
		}
		throw new Mage_Exception("More than 1 template instance found in export!");
	}

	/**
	 * @return int The number of internal object items
	 */
	public function getCount() {
		return count($this->_objects);
	}

	/**
	 * Returns all items as an array.
	 * @return array|null
	 */
	public function getItems() {
		return $this->_objects;
	}

	/**
	 * @param $name
	 * @return array
	 */
	public function getAsArray($name) {
		return $this->getInstancesOfModel($name);
	}

	/**
	 * Returns the model for the given ID. In case it is not found for the given ID the 'blank' model is returned.
	 * @param $name
	 * @param $id
	 * @return false|Mage_Core_Model_Abstract
	 */
	public function getModel($name, $id) {
		$models = $this->getInstancesOfModel($name);
		foreach ($models as $model) {
			if ($model->getId() == $id) return $model;
		}
		return Mage::getModel($name);
	}

	/**
	 * Returns all instances matching the given mage model name (i.e. configurator/template).
	 *
	 * @param $name the Mage model name
	 * @return array array of class instances for the given model name
	 */
	private function getInstancesOfModel($name) {
		$instances = array();
		/* @var $object Mage_Core_Model_Abstract */
		foreach ($this->_objects as $object) {
			if ($object->getResourceName() == $name) {
				$instances[] = $object;
			}
		}
		return $instances;
	}

	/**
	 * Returns all instances matching the given mage model name (i.e. configurator/template).
	 *
	 * @param $class the class name
	 * @return array array of class instances for the given class
	 */
	private function getInstancesOfClass($class) {
		$instances = array();
		foreach ($this->_objects as $object) {
			if ($object instanceof $class) {
				$instances[] = $object;
			}
		}
		return $instances;
	}

	/**
	 * Returns an array of strings containing the path to all registered images of any (internal) template object.
	 * @return array
	 */
	public function getCollectImageFileNames() {
		$images = array();
		/** @var $object Mage_Core_Model_Abstract */
		foreach ($this->_objects as $object) {
			$keys = array_keys($object->getData());
			foreach ($keys as $key) {
				if (($key == 'image' || $this->stringEndsWith($key, 'image') ||
					$key == 'thumbnail' || $key == 'thumbnail_alt') && $key != 'combined_product_image') {
					$path = $object->getData($key);
					if (!empty($path)) {
						$images[] = (strpos($path, 'configurator/') === false ? 'configurator/' : '').$path;
					}
				}
			}
		}
		return $images;
	}

	/**
	 * @param $whole
	 * @param $end
	 * @return bool
	 */
	private function stringEndsWith($whole, $end) {
		if (strlen($end) == 0 || strlen($end) > strlen($whole)) return false;
		return (strpos($whole, $end, strlen($whole) - strlen($end)) !== false);
	}
}