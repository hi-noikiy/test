<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2018 Amasty (https://www.amasty.com)
 * @package Amasty_SecurityAuth
 */


namespace Amasty\SecurityAuth\Helper;

use Magento\Framework\App\Helper\AbstractHelper;

class Data extends AbstractHelper
{
    const CONFIG_PATH_GENERAL_ACTIVE_MODULE = 'securityauth/general/active';

    const CONFIG_PATH_GENERAL_DISCREPANCY = 'securityauth/general/discrepancy';

    const CONFIG_PATH_GENERAL_IP_WHITE_LIST = 'securityauth/general/ip_white_list';

    const CODE_NAME_FOR_INPUT_FORM = 'code_auth';

    const LOCAL_IP = '127.0.0.1';

    protected $addressPath = [
        'HTTP_X_REAL_IP',
        'HTTP_CLIENT_IP',
        'HTTP_X_FORWARDED_FOR'
    ];

    /**
     * @var int
     */
    protected $_codeLength = 6;

    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface|null
     */
    protected $_scopeConfig = null;

    /**
     * @var \Magento\Framework\HTTP\PhpEnvironment\RemoteAddress
     */
    protected $_remoteAddress;

    /**
     * @var \Magento\Framework\App\RequestInterface
     */
    protected $_httpRequest;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface|null
     */
    protected $_storeManager = null;

    /**
     * @var \Magento\User\Model\UserFactory
     */
    protected $userFactory;

    /**
     * Data constructor.
     * @param \Magento\Framework\App\Helper\Context $context
     */
    public function __construct(
        \Magento\Framework\App\Helper\Context $context,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\User\Model\UserFactory $userFactory
    ) {
        parent::__construct($context);
        $this->_scopeConfig = $context->getScopeConfig();
        $this->_remoteAddress = $context->getRemoteAddress();
        $this->_httpRequest = $context->getRequest();
        $this->_storeManager = $storeManager;
        $this->userFactory = $userFactory;
    }

    /**
     * @param $path
     * @param string $scope
     * @param null $storeId
     * @return mixed
     */
    public function getConfigValueByPath(
        $path,
        $scope = \Magento\Store\Model\ScopeInterface::SCOPE_WEBSITE,
        $storeId = null
    ) {
        return $this->_scopeConfig->getValue($path, $scope, $storeId);
    }

    /**
     * @return bool
     */
    public function isActive()
    {
        $isWhiteIp = false;
        $ipWhiteList = explode(',', $this->getConfigValueByPath(self::CONFIG_PATH_GENERAL_IP_WHITE_LIST));

        if ($ipWhiteList) {
            $map = function ($value) {
                return trim($value);
            };

            foreach ($ipWhiteList as $ipWhiteItem) {

                if (strpos($ipWhiteItem, '/') !== false) {
                    $value = explode('/', $ipWhiteItem);

                    if (filter_var($value[0], FILTER_VALIDATE_IP, FILTER_FLAG_IPV6)) {
                        $range = $this->getRangeIpv6($value[0] . '/' . $value[1]);

                        if ($this->ipv6InRange(
                            $range['first'],
                            $range['last'],
                            $this->getCurrentIp()
                        )) {
                            $isWhiteIp = true;
                            break;
                        }

                    }

                }

            }

            $ipWhiteList = array_map($map, $ipWhiteList);

            if (!$isWhiteIp) {
                $isWhiteIp = in_array($this->getCurrentIp(), $ipWhiteList);
            }

        }

        return ($this->getConfigValueByPath(self::CONFIG_PATH_GENERAL_ACTIVE_MODULE) && !$isWhiteIp);
    }

    /**
     * @param $ipv6
     * @return array
     */
    private function getRangeIpv6($ipv6)
    {
        list($firstAddrStr, $prefixLen) = explode('/', $ipv6);

        $firstAddrBin = inet_pton($firstAddrStr);
        $elem = unpack('H*', $firstAddrBin);
        $firstAddrHex = reset($elem);
        $firstAddrStr = inet_ntop($firstAddrBin);
        $flexBits = 128 - $prefixLen;
        $lastAddrHex = $firstAddrHex;
        $pos = 31;

        while ($flexBits > 0) {
            $orig = substr($lastAddrHex, $pos, 1);
            $origVal = hexdec($orig);
            $newVal = $origVal | (pow(2, min(4, $flexBits)) - 1);
            $new = dechex($newVal);
            $lastAddrHex = substr_replace($lastAddrHex, $new, $pos, 1);
            $flexBits -= 4;
            $pos--;
        }

        $lastAddrBin = pack('H*', $lastAddrHex);
        $lastAddrStr = inet_ntop($lastAddrBin);
        $range = ['first' => $firstAddrStr, 'last' => $lastAddrStr];

        return $range;
    }

    /**
     * @param $first
     * @param $last
     * @param $ipv6
     * @return bool
     */
    private function ipv6InRange($first, $last, $ipv6)
    {
        $result = false;

        $first_in_range = inet_pton($first);
        $last_in_range = inet_pton($last);

        $address = inet_pton($ipv6);

        if ((strlen($address) == strlen($first_in_range))
            &&  (($address >= $first_in_range) && ($address <= $last_in_range))) {
            $result = true;
        }

        return $result;
    }

    /**
     * Create new secret.
     * 16 characters, randomly chosen from the allowed base32 characters.
     *
     * @param int $secretLength
     * @return string
     */
    public function createSecret($secretLength = 16)
    {
        $validChars = $this->_getBase32LookupTable();
        unset($validChars[32]);

        $secret = '';
        for ($i = 0; $i < $secretLength; $i++) {
            $secret .= $validChars[array_rand($validChars)];
        }
        return $secret;
    }

    /**
     * Calculate the code, with given secret and point in time
     *
     * @param string $secret
     * @param int|null $timeSlice
     * @return string
     */
    public function getCode($secret, $timeSlice = null)
    {
        if ($timeSlice === null) {
            $timeSlice = floor(time() / 30);
        }

        $secretkey = $this->_base32Decode($secret);

        // Pack time into binary string
        $time = chr(0).chr(0).chr(0).chr(0).pack('N*', $timeSlice);
        // Hash it with users secret key
        $hm = hash_hmac('SHA1', $time, $secretkey, true);
        // Use last nipple of result as index/offset
        $offset = ord(substr($hm, -1)) & 0x0F;
        // grab 4 bytes of the result
        $hashpart = substr($hm, $offset, 4);

        // Unpak binary value
        $value = unpack('N', $hashpart);
        $value = $value[1];
        // Only 32 bits
        $value = $value & 0x7FFFFFFF;

        $modulo = pow(10, $this->_codeLength);
        return str_pad($value % $modulo, $this->_codeLength, '0', STR_PAD_LEFT);
    }

    /**
     * Helper class to decode base32
     *
     * @param $secret
     * @return bool|string
     */
    public function _base32Decode($secret)
    {
        if (!$secret) {
            return '';
        }

        $base32chars = $this->_getBase32LookupTable();
        $base32charsFlipped = array_flip($base32chars);

        $paddingCharCount = substr_count($secret, $base32chars[32]);
        $allowedValues = [6, 4, 3, 1, 0];
        if (!in_array($paddingCharCount, $allowedValues)) {
            return false;
        }
        for ($i = 0; $i < 4; $i++) {
            if ($paddingCharCount == $allowedValues[$i] &&
                substr($secret, -($allowedValues[$i])) != str_repeat($base32chars[32], $allowedValues[$i])) {
                return false;
            }
        }
        $secret = str_replace('=', '', $secret);
        $secret = str_split($secret);
        $binaryString = "";
        for ($i = 0; $i < count($secret); $i = $i+8) {
            $x = "";
            if (!in_array($secret[$i], $base32chars)) {
                return false;
            }
            for ($j = 0; $j < 8; $j++) {
                $x .= str_pad(base_convert(@$base32charsFlipped[@$secret[$i + $j]], 10, 2), 5, '0', STR_PAD_LEFT);
            }
            $eightBits = str_split($x, 8);
            for ($z = 0; $z < count($eightBits); $z++) {
                $binaryString .= ( ($y = chr(base_convert($eightBits[$z], 2, 10))) || ord($y) == 48 ) ? $y:"";
            }
        }
        return $binaryString;
    }

    /**
     * Helper class to encode base32
     *
     * @param string $secret
     * @param bool $padding
     * @return string
     */
    public function _base32Encode($secret, $padding = true)
    {
        if (!$secret) {
            return '';
        }

        $base32chars = $this->_getBase32LookupTable();

        $secret = str_split($secret);
        $binaryString = "";
        for ($i = 0; $i < count($secret); $i++) {
            $binaryString .= str_pad(base_convert(ord($secret[$i]), 10, 2), 8, '0', STR_PAD_LEFT);
        }
        $fiveBitBinaryArray = str_split($binaryString, 5);
        $base32 = "";
        $i = 0;
        while ($i < count($fiveBitBinaryArray)) {
            $base32 .= $base32chars[base_convert(str_pad($fiveBitBinaryArray[$i], 5, '0'), 2, 10)];
            $i++;
        }
        if ($padding && ($x = strlen($binaryString) % 40) != 0) {
            if ($x == 8) {
                $base32 .= str_repeat($base32chars[32], 6);
            } elseif ($x == 16) {
                $base32 .= str_repeat($base32chars[32], 4);
            } elseif ($x == 24) {
                $base32 .= str_repeat($base32chars[32], 3);
            } elseif ($x == 32) {
                $base32 .= $base32chars[32];
            }
        }
        return $base32;
    }

    /**
     * Check if the code is correct. This will accept codes starting from
     * $discrepancy*30sec ago to $discrepancy*30sec from now
     *
     * @param string $secret
     * @param string $code
     * @param int $discrepancy This is the allowed time drift in 30 second units (8 means 4 minutes before or after)
     * @return bool
     */
    public function verifyCode($secret, $code)
    {
        $discrepancy = (int) $this->getConfigValueByPath(self::CONFIG_PATH_GENERAL_DISCREPANCY);
        $currentTimeSlice = floor(time() / 30);

        for ($i = -$discrepancy; $i <= $discrepancy; $i++) {
            $calculatedCode = $this->getCode($secret, $currentTimeSlice + $i);
            if ($calculatedCode == $code) {
                return true;
            }
        }

        return false;
    }

    /**
     * @param $secret
     * @param $userAdminId
     * @return string
     */
    public function getQRCodeGoogleUrl($secret, $userAdminId)
    {
        $urlencoded = urlencode('otpauth://totp/'.$this->getStoreName($userAdminId).'?secret='.$secret);

        return 'https://chart.googleapis.com/chart?chs=200x200&chld=M|0&cht=qr&chl='.$urlencoded;
    }

    /**
     * @param $adminUserId
     * @return string
     */
    public function getStoreName($adminUserId)
    {
        $username = $this->userFactory->create()->load($adminUserId)->getUsername();
        $baseUrl = parse_url($this->_storeManager->getStore()->getBaseUrl());

        return $username . '@' . $baseUrl['host'];
    }

    /**
     * Get array with all 32 characters for decoding from/encoding to base32
     *
     * @return array
     */
    protected function _getBase32LookupTable()
    {
        return [
            'A', 'B', 'C', 'D', 'E', 'F', 'G', 'H',
            'I', 'J', 'K', 'L', 'M', 'N', 'O', 'P',
            'Q', 'R', 'S', 'T', 'U', 'V', 'W', 'X',
            'Y', 'Z', '2', '3', '4', '5', '6', '7',
            '='
        ];
    }

    private function getCurrentIp()
    {
        foreach ($this->addressPath as $path) {
            $ip = $this->_httpRequest->getServer($path);
            if ($ip) {
                if (strpos($ip, ',') !== false) {
                    $addresses = explode(',', $ip);
                    foreach ($addresses as $address) {
                        if (trim($address) != self::LOCAL_IP) {
                            return trim($address);
                        }
                    }
                } else {
                    if ($ip != self::LOCAL_IP) {
                        return $ip;
                    }
                }
            }
        }
        return $this->_remoteAddress->getRemoteAddress();
    }
}
