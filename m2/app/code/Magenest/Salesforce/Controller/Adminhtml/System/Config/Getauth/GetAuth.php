<?php
/**
 * * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magenest\Salesforce\Controller\Adminhtml\System\Config\Getauth;

use Magento\Backend\App\Action;
use Magenest\Salesforce\Model\Connector;
use Magenest\Salesforce\Model\Sync\Product;
use Magento\Framework\App\Config\ScopeConfigInterface;

/**
 * Class GetAuth
 * @package Magenest\Salesforce\Controller\Adminhtml\System\Config\Getauth
 */
class GetAuth extends Action
{
    const ERROR_CONNECT_TO_SALESFORCECRM = 'INVALID_PASSWORD';

    /**
     * @var \Magenest\Salesforce\Model\Connector
     */
    protected $_connector;

    /**
     * @var Product
     */
    protected $_syncProduct;

    /**
     * @var ScopeConfigInterface
     */
    protected $scopeConfig;

    /**
     * GetAuth constructor.
     * @param Action\Context $context
     * @param Connector $connector
     * @param ScopeConfigInterface $scopeConfig
     * @param Product $syncProduct
     */
    public function __construct(
        Action\Context $context,
        Connector $connector,
        ScopeConfigInterface $scopeConfig,
        Product $syncProduct
    ) {
        parent::__construct($context);
        $this->_connector   = $connector;
        $this->_syncProduct = $syncProduct;
        $this->scopeConfig = $scopeConfig;
    }

    /**
     * Check whether vat is valid
     *
     * @return \Magento\Framework\Controller\Result\Raw
     */
    public function execute()
    {
        $data = $this->getRequest()->getPostValue();
        if ($data) {
            if (empty($data['username']) || empty($data['password']) || empty($data['client_id']) || empty($data['client_secret']) || empty($data['security_token'])) {
                $result['error']       = 1;
                $result['message'] = "Please enter all information";
                $this->getResponse()->setBody(json_encode($result));
                return;
            }
            try {
                $response = $this->_connector->getAccessToken($data, true);
                if (!empty($response['error'])) {
                    $result['error'] = 1;
                    $result['message'] = $response['error_description'];
                    $this->getResponse()->setBody(json_encode($result));
                    return;
                } else {
                    $result = $response;
                    $result['error'] = 0;
                    try {
                        $this->_syncProduct->setCredential($data);
                        $this->_syncProduct->syncShippingProduct();
                        $this->_syncProduct->syncTaxProduct();
                    } catch (\Exception $e) {
                        $result['error'] = 1;
                        $result['message'] = $e->getMessage();
                    }
                    $this->getResponse()->setBody(json_encode($result));
                    $this->scopeConfig->clean();
                    return;
                }
            } catch (\InvalidArgumentException $e) {
                $result['error'] = 1;
                $result['message'] = $e->getMessage();
                $this->getResponse()->setBody(json_encode($result));
                return;
            }
        }
    }

    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('Magenest_Salesforce::config_salesforce');
    }
}
