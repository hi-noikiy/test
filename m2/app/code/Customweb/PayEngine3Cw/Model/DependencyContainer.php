<?php
/**
 * You are allowed to use this API in your web application.
 *
 * Copyright (C) 2018 by customweb GmbH
 *
 * This program is licenced under the customweb software licence. With the
 * purchase or the installation of the software in your application you
 * accept the licence agreement. The allowed usage is outlined in the
 * customweb software licence which can be found under
 * http://www.sellxed.com/en/software-license-agreement
 *
 * Any modification or distribution is strictly forbidden. The license
 * grants you the installation in one application. For multiuse you will need
 * to purchase further licences at http://www.sellxed.com/shop.
 *
 * See the customweb software licence agreement for more details.
 *
 *
 * @category	Customweb
 * @package		Customweb_PayEngine3Cw
 *
 */

namespace Customweb\PayEngine3Cw\Model;

class DependencyContainer implements \Customweb_DependencyInjection_IContainer {

	/**
	 * @var \Customweb_DependencyInjection_IContainer
	 */
	private $container;

	/**
	 * @var \Customweb\PayEngine3Cw\Model\Adapter\Endpoint
	 */
	private $_endpointAdapter;

	/**
	 * @var \Customweb\PayEngine3Cw\Model\Adapter\Configuration
	 */
	private $_configurationAdapter;

	/**
	 * @var \Customweb\PayEngine3Cw\Model\Adapter\StorageBackend
	 */
	private $_storageBackendAdapter;

	/**
	 * @var \Customweb\PayEngine3Cw\Model\Adapter\LayoutRenderer
	 */
	private $_layoutRenderer;

	/**
	 * @var \Customweb\PayEngine3Cw\Model\Authorization\TransactionHandler
	 */
	private $_transactionHandler;

	/**
	 * @var \Customweb\PayEngine3Cw\Model\Update\Handler
	 */
	private $_updateHandler;

	/**
	 * @var \Customweb\PayEngine3Cw\Model\Asset\CompositeResolver
	 */
	private $_assetResolver;

	/**
	 * @var \Customweb\PayEngine3Cw\Model\BackendOperation\CancelAdapter
	 */
	private $_cancelAdapter;

	/**
	 * @var \Customweb\PayEngine3Cw\Model\BackendOperation\CaptureAdapter
	 */
	private $_captureAdapter;

	/**
	 * @var \Customweb\PayEngine3Cw\Model\BackendOperation\RefundAdapter
	 */
	private $_refundAdapter;

	/**
	 * @var \Customweb\PayEngine3Cw\Model\ExternalCheckout\Service
	 */
	private $_externalCheckoutService;

	/**
	 * @param \Customweb\PayEngine3Cw\Model\Adapter\Endpoint $endpointAdapter
	 * @param \Customweb\PayEngine3Cw\Model\Adapter\Configuration $configurationAdapter
	 * @param \Customweb\PayEngine3Cw\Model\Adapter\StorageBackend $storageBackendAdapter
	 * @param \Customweb\PayEngine3Cw\Model\Adapter\LayoutRenderer $layoutRenderer
	 * @param \Customweb\PayEngine3Cw\Model\Authorization\TransactionHandler $transactionHandler
	 * @param \Customweb\PayEngine3Cw\Model\Update\Handler $updateHandler
	 * @param \Customweb\PayEngine3Cw\Model\Asset\CompositeResolver $assetResolver
	 * @param \Customweb\PayEngine3Cw\Model\BackendOperation\CancelAdapter $cancelAdapter
	 * @param \Customweb\PayEngine3Cw\Model\BackendOperation\CaptureAdapter $captureAdapter
	 * @param \Customweb\PayEngine3Cw\Model\BackendOperation\RefundAdapter $refundAdapter,
	 * @param \Customweb\PayEngine3Cw\Model\ExternalCheckout\Service $externalCheckoutService
	 */
	public function __construct(
			\Customweb\PayEngine3Cw\Model\Adapter\Endpoint $endpointAdapter,
			\Customweb\PayEngine3Cw\Model\Adapter\Configuration $configurationAdapter,
			\Customweb\PayEngine3Cw\Model\Adapter\StorageBackend $storageBackendAdapter,
			\Customweb\PayEngine3Cw\Model\Adapter\LayoutRenderer $layoutRenderer,
			\Customweb\PayEngine3Cw\Model\Authorization\TransactionHandler $transactionHandler,
			\Customweb\PayEngine3Cw\Model\Update\Handler $updateHandler,
			\Customweb\PayEngine3Cw\Model\Asset\CompositeResolver $assetResolver,
			\Customweb\PayEngine3Cw\Model\BackendOperation\CancelAdapter $cancelAdapter,
			\Customweb\PayEngine3Cw\Model\BackendOperation\CaptureAdapter $captureAdapter,
			\Customweb\PayEngine3Cw\Model\BackendOperation\RefundAdapter $refundAdapter,
			\Customweb\PayEngine3Cw\Model\ExternalCheckout\Service $externalCheckoutService
	) {
		$this->_endpointAdapter = $endpointAdapter;
		$this->_configurationAdapter = $configurationAdapter;
		$this->_storageBackendAdapter = $storageBackendAdapter;
		$this->_layoutRenderer = $layoutRenderer;
		$this->_transactionHandler = $transactionHandler;
		$this->_updateHandler = $updateHandler->setContainer($this);
		$this->_assetResolver = $assetResolver;
		$this->_cancelAdapter = $cancelAdapter;
		$this->_captureAdapter = $captureAdapter;
		$this->_refundAdapter = $refundAdapter;
		$this->_externalCheckoutService = $externalCheckoutService->setContainer($this);
		$this->initContainer();
	}

	public function getBean($identifier) {
		return $this->container->getBean($identifier);
	}

	public function getBeansByType($type) {
		return $this->container->getBeansByType($type);
	}

	public function hasBean($identifier) {
		return $this->container->hasBean($identifier);
	}

	private function initContainer() {
		\Customweb_Core_Util_Class::registerClassLoader(function($className) {
			return \Magento\Framework\Autoload\AutoloaderRegistry::getAutoloader()->loadClass($className);
		});

		$packages = array(
			0 => 'Customweb_PayEngine3',
 			1 => 'Customweb_Payment_Authorization',
 		);
		$packages[] = 'Customweb_PayEngine3Cw_Model_';
		$packages[] = 'Customweb_Mvc_Template_Php_Renderer';
		$packages[] = 'Customweb_Payment_SettingHandler';

		\Customweb_Core_Util_Class::loadLibraryClassByName('Customweb_DependencyInjection_Bean_Provider_Annotation_Bean');
		$provider = new \Customweb_DependencyInjection_Bean_Provider_Editable(new \Customweb_DependencyInjection_Bean_Provider_Annotation(
				$packages
		));
		$provider->addObject(\Customweb_Core_Http_ContextRequest::getInstance());
		$provider->addObject($this->_endpointAdapter);
		$provider->addObject($this->_configurationAdapter);
		$provider->addObject($this->_storageBackendAdapter);
		$provider->addObject($this->_layoutRenderer);
		$provider->addObject($this->_transactionHandler);
		$provider->addObject($this->_updateHandler);
		$provider->addObject($this->_assetResolver);
		$provider->addObject($this->_cancelAdapter);
		$provider->addObject($this->_captureAdapter);
		$provider->addObject($this->_refundAdapter);
		$provider->addObject($this->_externalCheckoutService);
		$this->container = new \Customweb_DependencyInjection_Container_Default($provider);
	}
}