<?php
class HN_Salesforce_Model_Connector {

	/**#@+
     * Configuration pathes use connection Salesforce
     */
	const XML_PATH_USER_ID = 'salesforce/auth/user_id';
	const XML_PATH_PASSWORD = 'salesforce/auth/password';
	const XML_PATH_CLIENT_ID = 'salesforce/auth/client_id';
	const XML_PATH_CLIENT_SECRET = 'salesforce/auth/client_secret';
	const XML_PATH_SECURITY_TOKEN = 'salesforce/auth/security_token';

	/**
	 * Type of mapping
	 *
	 * @var string
	 */
	protected $_type;

	/**
	 * Table on Magento
	 *
	 * @var string
	 */
	protected $_table;

	/**
	 * Data in Magento
	 *
	 * HN_Salesforce_Model_Data
	 */
	protected $_data;

	public function __construct()
	{
		$this->_data = Mage::getSingleton('salesforce/data');
	}
	
	/**
	 * Request to Server
	 *
	 * @param string $path
	 * @param string $paramter
	 * @return array
	 */
	public function curl($path, $paramter = null , $method = Zend_Http_Client::GET)
    {
    	$login = unserialize(Mage::getStoreConfig('salesforce/connection'));
    	if(!$login){
    		$login = $this->getAuth();
    	}

		again:
    	$instance_url = $login['instance_url'];
    	$access_token = $login['access_token'];
    	$url = $instance_url.$path;
    	$headers = array("Authorization: Bearer ".$access_token,					
							"Content-type: application/json");
		$client = new Zend_Http_Client($url);
		$client->setHeaders($headers);
		$client->setConfig([
			'timeout' => 300,
			]);
		if($paramter)
			$client->setRawData(json_encode($paramter), 'application/json');
    	$response = $client->request($method)->getBody();
    	$result = json_decode($response, true);
    	if (isset($result[0]['errorCode']) && $result[0]['errorCode'] == 'INVALID_SESSION_ID'){
	    	$login = $this->getAuth();
	    	goto again;
	    }
	    
    	return $result;
    }

	/**
	 * Get Access Token & Instance Url
	 *
	 * @return array
	 */
	public function getAuth() {
		try
		{	
			$username = Mage::getStoreConfig(self::XML_PATH_USER_ID);
			$password = Mage::getStoreConfig(self::XML_PATH_PASSWORD);
			$client_id = Mage::getStoreConfig(self::XML_PATH_CLIENT_ID);
			$client_secret = Mage::getStoreConfig(self::XML_PATH_CLIENT_SECRET);
			$security_token = Mage::getStoreConfig(self::XML_PATH_SECURITY_TOKEN);
	
			if(!$username || !$password || !$client_id || !$client_secret || !$security_token){
				 throw new Exception('Field not setup');
			}

			$url= "https://login.salesforce.com/services/oauth2/token";
			$params = "grant_type=password"
			. "&client_id=" . $client_id
			. "&client_secret=" . $client_secret
			. "&username=" . $username
			. "&password=". $password . $security_token;
			
	    	$curl = new Varien_Http_Adapter_Curl;
	    	$curl->setConfig(array(
	    			'timeout' => 15,
	    			'header' => false
	    		));
	    	$curl->write(Zend_Http_Client::POST, $url, '1.1', array(), $params);			
			$json_response =$curl->read();
			$curl->close();
			$response = json_decode($json_response, true);
			if(isset($response['error_description'])){
				throw new Exception($response['error_description'].'('.$response['error'].')');
			}else{
					$data = array(
								'instance_url' => $response['instance_url'],
								'access_token' => $response['access_token']
								);
				$result = serialize($data);
				$config = Mage::getModel('core/config');
 				$config ->saveConfig('salesforce/connection', $result, 'default', 0);
 				return $data;
			}			
		} catch ( Exception $exception ) {
			echo 'Exception Message: ' . $exception->getMessage() . '<br/>';
			exit;
		}
	}

	/**
	 * Create new Record in Salesforce
	 *
	 * @param string $table
	 * @param array $params
	 * @return string or false
	 */
	public function createRecords($table, $params) {

		$path = "/services/data/v34.0/sobjects/".$table."/";
		$response = $this->curl($path, $params, Zend_Http_Client::POST);
		try{
			if(!empty($response['id'])){
			$id = $response['id'];
        	$this->saveReport($id, 'create', $table);

			return $id;
			}else
				throw new Exception('Mapping not correct, please check mapping again !');
		}catch(Exception $e){
			echo 'Exception Message: ' . $e->getMessage() . '<br/>';			
			return false;
		}

	}
	
	/**
	 * Search recordId in Salesforce
	 *
	 * @param string $table
	 * @param string $field
	 * @param string $value
	 * @return string or false
	 */	
	public function searchRecords($table, $field, $value){

		$query = "SELECT Id FROM ".$table." WHERE ".$field." = '".$value."' ";
		if ($table == 'PricebookEntry'){
				$query .= ' ORDER BY Id ';
		}
		$query .= 'LIMIT 1';
		$path = "/services/data/v34.0/query/?q=" . urlencode($query);
		
	    $response = $this->curl($path);
	    if(isset($response['totalSize']) && $response['totalSize'] >= 1){
	       	$id = $response['records']['0']['Id'];
	    	return $id;
	    }
	    else {
	    	return false;
	    }
	}

	/**
	 * Get All Field of a table in Salesforce
	 *
	 * @param string $table
	 * @return string
	 */		
	public function getFields($table){
	
		$path = "/services/data/v34.0/sobjects/".$table."/describe/";	
		$response = $this->curl($path);
		$data = [];
        $_type = ['picklist', 'date', 'datetime', 'reference', 'address'];
		if(isset($response['fields'] )){
			foreach ($response['fields'] as $item => $value){
				$type = $value['type'];
				if($value['permissionable'] == 1 && !in_array($type, $_type))
                {
                    $label = $value['label'];
                    $name = $value['name'];
                    $data[$name] = $label.' ('.$type.')';
                }
			}
		}
		$field = serialize($data);

		return $field;	
	}

    /**
	 * Delete a record in salesforce
	 *
	 * @param string $table
	 * @param string $id
	 */	
	public function deleteRecords($table, $id){
		$path = "/services/data/v34.0/sobjects/".$table."/".$id;
        $result = $this->curl($path, null, 'DELETE');        
        $this->saveReport($id, 'delete', $table);
	}

    /**
	 * Update a record in salesforce
	 *
	 * @param string $table
	 * @param string $id
	 * @param array $params
	 */	
	public function updateRecords($table, $id, $params){	
		$path = "/services/data/v34.0/sobjects/".$table."/".$id;
        $this->curl($path, $params, 'PATCH');        
        $this->saveReport($id, 'update', $table);

        return;
	}
	public function saveReport($id, $action, $table){
		$model = Mage::getSingleton('salesforce/report');
		$model->saveReport($id, $action, $table);
		return;
	}
}
	
