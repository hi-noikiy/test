<?php

class Admitad_Tracking_Helper_Api
{
    protected $container;
    protected $host = 'https://api.admitad.com';
    protected $accessToken;
    protected $refreshToken;
    protected $expiresIn;

    public function get($method, $params = array())
    {
        $content = $this->send($this->host . $method, $params, 'GET', $this->getRequestHeaders());

        return json_decode($content, JSON_UNESCAPED_UNICODE);
    }

    public function post($method, $params = array())
    {
        $content = $this->send($this->host . $method, $params, 'POST', $this->getRequestHeaders());

        return json_decode($content, JSON_UNESCAPED_UNICODE);
    }

    protected function getRequestHeaders()
    {
        if (!$this->accessToken) {
            return array();
        }

        return array('Authorization: Bearer ' . $this->accessToken);
    }

    public function isAuthorized()
    {
        return $this->accessToken;
    }

    public function authorize($clientId = null, $clientSecret = null)
    {
        if ($this->accessToken) {
            return $this;
        }

        if ($clientId && $clientSecret) {
            $this->selfAuthorize($clientId, $clientSecret, 'advertiser_info');
        }

        return $this;
    }

    public function refreshToken($clientId, $clientSecret, $refreshToken)
    {
        $response = $this->requestRefreshToken($clientId, $clientSecret, $refreshToken);

        if (!isset($response['refresh_token'])) {
            return false;
        }

        $this
            ->setAccessToken($response['access_token'])
            ->setRefreshToken($response['refresh_token'])
            ->setExpiresIn($response['expires_in']);

        return true;
    }

    private function selfAuthorize($clientId, $clientSecret, $scope)
    {
        $response = $this->authorizeClient($clientId, $clientSecret, $scope);
        if (!isset($response['access_token'])) {
            return $response;
        }

        $this
            ->setAccessToken($response['access_token'])
            ->setRefreshToken($response['refresh_token'])
            ->setExpiresIn($response['expires_in']);

        return $response;
    }

    private function authorizeClient($clientId, $clientSecret, $scope)
    {
        $query = array(
            'client_id'  => $clientId,
            'scope'      => $scope,
            'grant_type' => 'client_credentials',
        );

        $headers = array('Authorization: Basic ' . base64_encode($clientId . ':' . $clientSecret));

        $result = $this->send($this->host . '/token/', $query, 'POST', $headers);

        return json_decode($result, true);
    }

    public function requestRefreshToken($clientId, $clientSecret, $refreshToken)
    {
        $query = array(
            'refresh_token' => $refreshToken,
            'client_id'     => $clientId,
            'client_secret' => $clientSecret,
            'grant_type'    => 'refresh_token',
        );

        $headers = array('Authorization: Basic ' . base64_encode($clientId . ':' . $clientSecret));

        $result = $this->send($this->host . '/token/', $query, 'POST', $headers);

        return json_decode($result, true);
    }

    public function send($url, $params = array(), $method = 'GET', $headers = array())
    {
        if (function_exists('curl_init')) {
            return $this->sendCurl($url, $params, $method, $headers);
        }

        return $this->sendFileGetContents($url, $params, $method, $headers);
    }

    public function getAccessToken()
    {
        return $this->accessToken;
    }

    public function setAccessToken($accessToken)
    {
        $this->accessToken = $accessToken;

        return $this;
    }

    public function getRefreshToken()
    {
        return $this->refreshToken;
    }

    public function setRefreshToken($refreshToken)
    {
        $this->refreshToken = $refreshToken;

        return $this;
    }

    public function getExpiresIn()
    {
        return $this->expiresIn;
    }

    public function setExpiresIn($expiresIn)
    {
        $this->expiresIn = $expiresIn;

        return $this;
    }

    public function isExpired()
    {
        return Mage::getStoreConfig(
            'admitadtracking/general/expires_in',
            Mage::app()->getStore()
        ) <= time();
    }

    protected function sendCurl($url, $params = array(), $method = 'GET', $headers = array())
    {
        $cl = curl_init($url);

        curl_setopt($cl, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($cl, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($cl, CURLOPT_RETURNTRANSFER, true);


        curl_setopt($cl, CURLOPT_HTTPHEADER, $headers);

        if ($method == 'POST') {
            curl_setopt($cl, CURLOPT_POST, 1);
            curl_setopt($cl, CURLOPT_POSTFIELDS, $params);
        }

        return curl_exec($cl);
    }

    protected function sendFileGetContents($url, $params = array(), $method = 'GET', $headers = array())
    {
        $context = stream_context_create(
            array(
                'http' => array(
                    'method'  => $method,
                    'header'  => implode(PHP_EOL, $headers),
                    'content' => http_build_query($params),
                ),
            )
        );

        return file_get_contents($url, null, $context);
    }

    public function getAdvertiserInfo()
    {
        if (!$this->isAuthorized()) {
            return false;
        }

        $result = $this->get('/advertiser/info/');

        return reset($result);
    }

}
