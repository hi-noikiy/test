<?php
/**
 * Plugin helper
 *
 * @since      1.0.0
 * @author     Beeketing
 *
 */

namespace Beeketing\MagentoCommon\Libraries;


class Helper
{
    /** @var \Magento\Framework\UrlInterface $url */
    private $url;

    /**
     * Helper constructor.
     */
    public function __construct() {
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $this->url = $objectManager->get('\Magento\Framework\UrlInterface');
    }

    /**
     * Get shop domain
     *
     * @return string
     */
    public function getShopDomain()
    {
        $siteUrl = $this->url->getBaseUrl();
        $urlParsed = parse_url($siteUrl);
        $host = isset($urlParsed['host']) ? $urlParsed['host'] : '';

        // Config www
        if (isset($_GET['www'])) {
            if (in_array($_GET['www'], array(0, false))) {
                $host = preg_replace('/^www\./', '', $host);
            } elseif (!preg_match('/^www\./', $host) && in_array($_GET['www'], array(1, true))) {
                $host = 'www.' . $host;
            }
        }

        return $host;
    }

    /**
     * Get shop absolute path
     *
     * @return string
     */
    public function getShopAbsolutePath()
    {
        return $this->url->getBaseUrl();
    }

    /**
     * Get local file contents
     *
     * @param $filePath
     * @return string
     */
    public static function getLocalFileContents($filePath)
    {
        $contents = @file_get_contents($filePath);
        if (!$contents) {
            ob_start();
            @include_once($filePath);
            $contents = ob_get_clean();
        }

        return $contents;
    }

    /**
     * Is beeketing hidden name
     *
     * @param $name
     * @return bool
     */
    public static function isBeeketingHiddenName($name)
    {
        if ((bool)preg_match('/\(BK (\d+)\)/', $name, $matches)) {
            return true;
        }

        return false;
    }

    /**
     * Get media base url
     *
     * @return mixed
     */
    public static function getMediaBaseUrl()
    {
        $om = \Magento\Framework\App\ObjectManager::getInstance();
        $storeManager = $om->get('Magento\Store\Model\StoreManagerInterface');
        $currentStore = $storeManager->getStore();

        return $currentStore->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_MEDIA);
    }

    /**
     * Get product image url
     *
     * @param $imagePath
     * @return string
     */
    public static function getProductImageUrl($imagePath)
    {
        return self::getMediaBaseUrl() . 'catalog/product' . $imagePath;
    }

    /**
     * Format price
     *
     * @param $price
     * @return string
     */
    public static function formatPrice($price)
    {
        return number_format($price, 2);
    }

    /**
     * Generate access token
     *
     * @return string
     */
    public static function generateAccessToken()
    {
        try {
            $string = random_bytes( 16 );
            $token = bin2hex( $string );
        } catch ( \Exception $e ) {
            $token = md5( uniqid( rand(), true ) );
        }

        return $token;
    }

    /**
     * Generate access token
     *
     * @param $appKey
     * @return string
     */
    public static function generateAppSettingKey($appKey)
    {
        return sprintf('beeketing/%s/settings', $appKey);
    }
}