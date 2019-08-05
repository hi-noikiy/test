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

namespace Customweb\PayEngine3Cw\Controller\Adminhtml\Customer;

class DeleteAlias extends \Magento\Backend\App\Action
{
	/**
	 * @var \Customweb\PayEngine3Cw\Model\Authorization\TransactionFactory
	 */
	protected $_transactionFactory;

	/**
	 * @var \Customweb\PayEngine3Cw\Model\Alias\Handler
	 */
	protected $_aliasHandler;

	/**
	 * @param \Magento\Backend\App\Action\Context $context
	 * @param \Customweb\PayEngine3Cw\Model\Authorization\TransactionFactory $transactionFactory
	 * @param \Customweb\PayEngine3Cw\Model\Alias\Handler $aliasHandler
	 */
	public function __construct(
			\Magento\Backend\App\Action\Context $context,
			\Customweb\PayEngine3Cw\Model\Authorization\TransactionFactory $transactionFactory,
			\Customweb\PayEngine3Cw\Model\Alias\Handler $aliasHandler
	) {
		parent::__construct($context);
		$this->_transactionFactory = $transactionFactory;
		$this->_aliasHandler = $aliasHandler;
	}

	public function execute()
	{
		/* @var $transaction \Customweb\PayEngine3Cw\Model\Authorization\Transaction */
		$transaction = $this->_transactionFactory->create()->load($this->getRequest()->getParam('id'));
		if (!$transaction->getId()) {
			throw new \Exception('The transaction has not been found.');
		}

		$this->_aliasHandler->removeAlias($transaction);

		$this->messageManager->addSuccess(__("The alias has been deleted."));

		$resultRedirect = $this->resultRedirectFactory->create();
		$resultRedirect->setPath('customer/index/edit', ['id' => $transaction->getCustomerId()]);
		return $resultRedirect;
	}

	protected function _isAllowed()
	{
		return $this->_authorization->isAllowed('Customweb_PayEngine3Cw::customer_aliases');
	}
}