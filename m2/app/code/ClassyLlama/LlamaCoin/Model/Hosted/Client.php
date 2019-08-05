<?php

namespace ClassyLlama\LlamaCoin\Model\Hosted;

class Client extends \ClassyLlama\LlamaCoin\Model\Client\AbstractMethod {

    protected $_merchantRefNum = null;
    protected $_currencyCode = null;
    protected $_totalAmount = null;

    const LOG_FILE_NAME = 'optimal_error.log';
    const CONNECTION_RETRIES = 3;
    
    protected $_customerSession;
    protected $_helper;
    protected $_jsonHelper;
       
    /**
     * 
     * @param \ClassyLlama\LlamaCoin\Helper\Data $helper
     * @param \Magento\Customer\Model\Session $customerSession
     * @param \Magento\Framework\Encryption\EncryptorInterface $encryptor
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     * @param \Magento\Framework\Json\Helper\Data $jsonHelper
     * @param \Magento\Framework\Model\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Framework\Api\ExtensionAttributesFactory $extensionFactory
     * @param \Magento\Framework\Api\AttributeValueFactory $customAttributeFactory
     * @param \Magento\Payment\Helper\Data $paymentData
     * @param \Magento\Payment\Model\Method\Logger $logger
     * @param \Magento\Framework\Model\ResourceModel\AbstractResource $resource
     * @param \Magento\Framework\Data\Collection\AbstractDb $resourceCollection
     * @param array $data
     */
     
    public function __construct(
        \ClassyLlama\LlamaCoin\Helper\Data $helper,    
        \Magento\Customer\Model\Session $customerSession,
        \Magento\Framework\Encryption\EncryptorInterface $encryptor, 
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,    
        \Magento\Framework\Json\Helper\Data $jsonHelper,
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Api\ExtensionAttributesFactory $extensionFactory,
        \Magento\Framework\Api\AttributeValueFactory $customAttributeFactory,
        \Magento\Payment\Helper\Data $paymentData,
        \Magento\Payment\Model\Method\Logger $logger,
        \Magento\Framework\Model\ResourceModel\AbstractResource $resource = null,
        \Magento\Framework\Data\Collection\AbstractDb $resourceCollection = null,
        array $data = []
    ){
        $this->_helper = $helper;
        $this->_customerSession = $customerSession;
        $this->_jsonHelper = $jsonHelper;
        $this->_restEndpoints = array(
            'create' => 'hosted/v1/orders',
            'cancel' => 'hosted/v1/orders/%1',
            'update' => 'hosted/v1/orders/%1',
            'info' => 'hosted/v1/orders/%1',
            'refund' => 'hosted/v1/orders/%1/refund',
            'settle' => 'hosted/v1/orders/%1/settlement',
            'resend' => 'hosted/v1/orders/%1/resend_callback',
            'report' => 'hosted/v1/orders',
            'rebill' => 'hosted/v1/orders/%1',
        );
        parent::__construct(
            $encryptor,
            $scopeConfig,
            $context,
            $registry,
            $extensionFactory,
            $customAttributeFactory,
            $paymentData,
            $logger,
            $resource,
            $resourceCollection,
            $data
        );
    }
    
//    public function _construct() {
//
//        // Initialize methods array
//        
//        echo'<pre />'; print_r($this->_restEndpoints); exit;
//        
//    }

    /**
     *
     * Create an Order in Netbanks.
     *
     * @param $data (
     *    merchantRefNum = (string) MagentoOrderId
     *    currencyCode   = (ISO4217) Order currency code
     *    totalAmount    = (int) Order Grand Total
     *    customerIP     = (string) remote_ip
     *
     *    customerNotificationEmail = (string) Order customer email
     *    merchantNotificationEmail = (string) Order contact email
     * )
     * @return bool|mixed
     */
    public function createOrder($data) {
        $mode = 'POST';
        $url = $this->_getUrl('create');

        return $this->callApi($url, $mode, $data);
    }

    /**
     *
     * Cancel an Order in Netbanks
     *
     * @param $id
     * @internal param $data ( id = netbanksOrderId )
     *
     * @return bool|mixed
     */
    public function cancelOrder($id) {
        $mode = 'DELETE';
        $url = $this->_getUrl('cancel', $id);

        return $this->callApi($url, $mode);
    }

    /**
     *
     * Update Order in Netbanks
     *
     * @param $data
     */
    public function updateOrder($data, $id) {
        $mode = 'PUT';
        $url = $this->_getUrl('update', $id);

        return $this->callApi($url, $mode, $data);
    }

    /**
     *
     * Retrieve Order Information from Netbanks
     *
     * @param $id
     * @internal param $data
     * @return bool|mixed
     */
    public function retrieveOrder($id) {
        $mode = 'GET';
        $url = $this->_getUrl('info', $id);

        return $this->callApi($url, $mode);
    }

    /**
     *
     * Refund order in Netbanks
     *
     * @param $data
     * @param $id
     * @return bool|mixed
     */
    public function refundOrder($data, $id) {
        $mode = 'POST';
        $url = $this->_getUrl('refund', $id);

        return $this->callApi($url, $mode, $data);
    }

    /**
     *
     * Settle an order in Netbanks
     *
     * @param $data
     * @param $id
     * @return bool|mixed
     */
    public function settleOrder($data, $id) {
        $mode = 'POST';
        $url = $this->_getUrl('settle', $id);

        return $this->callApi($url, $mode, $data);
    }

    /**
     *
     * Get an order report form Netbanks
     *
     */
    public function orderReport() {
        $mode = 'GET';
        $url = $this->_getUrl('report');

        return $this->callApi($url, $mode);
    }

    /**
     *
     * Resend Callback url to Netbanks
     *
     * @param $data
     * @return bool|mixed
     */
    public function resendCallback($data) {
        $mode = 'GET';
        $url = $this->_getUrl('resend');

        return $this->callApi($url, $mode);
    }

    /**
     *
     * Rebill an order in Netbanks
     *
     * @param $data
     * @return bool|mixed
     */
    public function rebillOrder($data) {
        $mode = 'POST';
        $url = $this->_getUrl('rebill');

        return $this->callApi($url, $mode);
    }

    /**
     * Mapping of the RESTFul Api
     *
     * Create an Order      - hosted/v1/orders                      [POST]
     * Cancel an Order      - hosted/v1/orders/{id}                 [DELETE]
     * Update an Order      - hosted/v1/orders/{id}                 [PUT]
     * Get an Order         - hosted/v1/orders/{id}                 [GET]
     * Refund an Order      - hosted/v1/orders/{id}/settlement      [POST]
     * Get an Order Report  - hosted/v1/orders                      [GET]
     * Resend Callbackk     - hosted/v1/orders/{id}/resend_callback [GET]
     * Process a Rebill     - hosted/v1/orders/{id}                 [POST]
     *
     * @param $method
     * @param $url
     * @param $data = Array(id,content)
     * @return bool|mixed
     */
    protected function callApi($url, $method, $data = array()) {
        $helper = $this->_helper;
        $session = $this->_customerSession;
        $response = $this->_jsonHelper->jsonDecode($this->_callApi($url, $method, $data));
        $this->_helper->logData('OPTIMAL RESPONSE (callApi):');
        $this->_helper->logData(print_r($response, true));
    
        $defaultMessage = 'Payment Gateway Error. Please contact the site admin.';

        if (isset($response->error) && !isset($response->error->code)) {
            $this->_helper->logData($response);
            $helper->cleanMerchantCustomerId($session->getId());
            throw new \ClassyLlama\LlamaCoin\Model\CustomException($defaultMessage);
            //throw new Op_Netbanx_Model_Hosted_Exception($defaultMessage);
        }

        if (isset($response->error)) {
            $message = $helper->getMsgByCode($response->error->code);

            if ($message === null && isset($response->error->message)) {
                $message = $response->error->message;
            } else {
                $message = $defaultMessage;
            }

            $helper->cleanMerchantCustomerId($session->getId());

            throw new \ClassyLlama\LlamaCoin\Model\CustomException($message);
        }

        if (isset($response->transaction->errorCode)) {
            $message = $helper->getMsgByCode($response->transaction->errorCode);

            if ($message === null && !isset($response->transaction->errorCode)) {
                $this->_helper->logData($response);
                throw new \ClassyLlama\LlamaCoin\Model\CustomException($defaultMessage);
            }

            if ($message === null) {
                $message = $response->transaction->errorMessage;
            }

            if (empty($message)) {
                $message = $defaultMessage;
            }

            if (!$session->getCustomerId()) {
                $session->addError($message);
            }

            $helper->cleanMerchantCustomerId($session->getId());

            throw new \ClassyLlama\LlamaCoin\Model\CustomException($message);
        }

        return $response;
    }

    /**
     * Returns 'Default' Error message if message by Code is not found
     *
     * @param null $code
     * @return null|string
     */
    protected function _getMsgByCode($code = null) {
        $message = $this->_helper->getMsgByCode($code);
        if ($message !== null) {
            return $message;
        }

        return null;
    }

    /**
     * Makes CURL requests to the netbanks api
     *
     * @param $url
     * @param $mode
     * @param array $data
     * @return mixed
     */
    protected function _callApi($url, $mode, $data = array()) {
        $data = $this->_jsonHelper->jsonEncode($data);


        try {
            $curl = curl_init($url);
            $headers[] = "Content-Type: application/json";
            $this->_checkCurlVerifyPeer($curl);
            curl_setopt($curl, CURLOPT_USERPWD, $this->_getUserPwd());
            curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);
            //$this->_helper->logData($data,1);

            curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);

            switch ($mode) {
                case "POST":
                    curl_setopt($curl, CURLOPT_POST, true);
                    curl_setopt($curl, CURLOPT_POSTFIELDS, $data);
                    break;
                case "DELETE":
                    curl_setopt($curl, CURLOPT_CUSTOMREQUEST, $mode);
                    curl_setopt($curl, CURLOPT_POSTFIELDS, $data);
                    break;
                case "PUT":
                    curl_setopt($curl, CURLOPT_CUSTOMREQUEST, $mode);
                    curl_setopt($curl, CURLOPT_POSTFIELDS, $data);
                    break;
                case "GET":
                    //hosted/v1/orders/{id}
                    break;
                default:
                    throw new \Magento\Framework\Exception\LocalizedException\Exception("{$mode} mode was not recognized. Please one of the valid REST actions GET, POST, PUT, DELETE");
                    break;
            }

            $curl_response = curl_exec($curl);
            curl_close($curl);

            // Check if the response is false
            if ($curl_response === false) {
                throw new \Magento\Framework\Exception\LocalizedException\Exception("Something went wrong while trying to retrieve the response from the REST api");
            }
        } catch (\Exception $e) {
            $this->_helper->logData($e->getMessage(),1);
            return false;
        }
        $this->_helper->logData('OPTIMAL RESPONSE (_callApi):');
        //$this->_helper->logData($curl_response);
        return $curl_response;
    }

    /**
     * @param $url
     * @param $data
     * @return bool
     */
    public function submitPayment($url, $data) {
        $data_string = '';
        
        try {
            $curl = curl_init($url);

            //url-ify the data for the POST
            foreach ($data as $key => $value) {
                $data_string .= $key . '=' . $value . '&';
            }
            
            $data_string = rtrim($data_string, '&');
            curl_setopt($curl, CURLOPT_HTTPHEADER, array());

            $this->_checkCurlVerifyPeer($curl);
            
            //set the url, number of POST vars, POST data
            curl_setopt($curl, CURLOPT_URL, $url);
            curl_setopt($curl, CURLOPT_POST, true);
            curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($curl, CURLOPT_HEADER, true);
            curl_setopt($curl, CURLOPT_POSTFIELDS, $data_string);
            $curl_response = curl_exec($curl);

            $headers = substr($curl_response, 0, curl_getinfo($curl, CURLINFO_HEADER_SIZE));
            $headers = explode("\n", $headers);
            
            $this->_helper->logData('OPTIMAL RESPONSE (submitPayment):');
            //$this->_helper->logData($curl_response);
            //$this->_helper->logData(print_r($headers, true));
                       
            $status = curl_getinfo($curl, CURLINFO_HTTP_CODE);
            $redirectLocation = curl_getinfo($curl, CURLINFO_REDIRECT_URL);
            //$this->_helper->logData(print_r($redirectLocation, true));
            curl_close($curl);

            return true;
        } catch (\Exception $e) {
            $this->_helper->logData($e->getMessage(),1);
            return false;
        }
    }

    /**
     * Build the RESTful url
     *
     * @param $method
     * @param null $id
     * @return string
     */
    protected function _getUrl($method, $id = null) {
        $url= '';
            switch($method){
                case 'create':
                    $url = $this->_apiUrl . '/' . $this->_restEndpoints['create'];
                    break;
                case 'report':
                    $url = $this->_apiUrl . '/' . $this->_restEndpoints['create'];
                    break;
                case 'rebill':
                    $url = $this->_apiUrl . '/' . $this->_restEndpoints['create'] .'/'.$id;
                    break;
                case 'cancel':
                    $url = $this->_apiUrl . '/' . $this->_restEndpoints['create'] .'/'.$id;
                    break;
                case 'update':
                    $url = $this->_apiUrl . '/' . $this->_restEndpoints['create'] .'/'.$id;
                    break;
                case 'info':
                    $url = $this->_apiUrl . '/' . $this->_restEndpoints['create'] .'/'.$id;
                    break;
                case 'refund':
                    $url = $this->_apiUrl . '/' . $this->_restEndpoints['create'] .'/'.$id .'/refund';
                    break;
                case 'settle':
                    $url = $this->_apiUrl . '/' . $this->_restEndpoints['create'] .'/'.$id.'/settlement';
                    break;
                case 'resend':
                    $url = $this->_apiUrl . '/' . $this->_restEndpoints['create'] .'/'.$id.'/resend_callback';
                    break;
                
                default:
                    throw new \Magento\Framework\Exception\LocalizedException(__("There is no method set, please contact the website administrator."));
                    break;
            }    
        return $url;
    }

}

  function generateRandomString($length = 10) {
        $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $charactersLength = strlen($characters);
        $randomString = '';
        for ($i = 0; $i < $length; $i++) {
            $randomString .= $characters[rand(0, $charactersLength - 1)];
        }
        return MD5($randomString);
    }