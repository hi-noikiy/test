<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2019 Amasty (https://www.amasty.com)
 * @package Amasty_Storelocator
 */


namespace Amasty\Storelocator\Model;

use Amasty\Storelocator\Ui\DataProvider\Form\ScheduleDataProvider;
use Amasty\Storelocator\Helper\Data;
use Amasty\Storelocator\Api\ReviewRepositoryInterface;
use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\UrlInterface;
use Magento\Directory\Model\CountryFactory;
use Magento\Directory\Model\RegionFactory;
use Magento\Framework\Escaper;

class Location extends \Magento\Rule\Model\AbstractModel
{
    /**
     * cache tag
     */
    const CACHE_TAG = 'amlocator_location';

    /**
     * Store rule actions model
     *
     * @var \Magento\Rule\Model\Action\Collection
     */
    protected $_actions;

    /**
     * @var \Magento\SalesRule\Model\Rule\Condition\Product\CombineFactory
     */
    protected $condProdCombineF;

    /**
     * @var \Magento\SalesRule\Model\Rule\Condition\Product\Combine
     */
    protected $combineProduct;

    /**
     * Store manager
     *
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @var \Amasty\Base\Model\Serializer
     */
    protected $serializer;

    /**
     * @var \Amasty\Storelocator\Model\Rule\Condition\Product\Combine
     */
    protected $locatorCondition;

    /**
     * @var ImageProcessor
     */
    private $imageProcessor;

    /**
     * @var ConfigProvider
     */
    private $configProvider;

    /**
     * @var Data
     */
    private $dataHelper;

    /**
     * @var array
     */
    public $dayNames;

    /**
     * @var ReviewRepositoryInterface
     */
    private $reviewRepository;

    /**
     * @var CustomerRepositoryInterface
     */
    private $customerRepository;

    /**
     * @var UrlInterface
     */
    private $urlBuilder;

    /**
     * @var CountryFactory
     */
    private $countryFactory;

    /**
     * @var RegionFactory
     */
    private $regionFactory;

    /**
     * @var Escaper
     */
    private $escaper;

    public function __construct(
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Data\FormFactory $formFactory,
        \Magento\Framework\Stdlib\DateTime\TimezoneInterface $localeDate,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Amasty\Base\Model\Serializer $serializer,
        \Magento\SalesRule\Model\Rule\Condition\Product\CombineFactory $_condProdCombineF,
        Rule\Condition\Product\CombineFactory $locatorConditionFactory,
        ImageProcessor $imageProcessor,
        ConfigProvider $configProvider,
        Data $dataHelper,
        ReviewRepositoryInterface $reviewRepository,
        CustomerRepositoryInterface $customerRepository,
        UrlInterface $urlBuilder,
        CountryFactory $countryFactory,
        RegionFactory $regionFactory,
        Escaper $escaper,
        array $data = []
    ) {
        $this->storeManager = $storeManager;
        $this->serializer = $serializer;
        $this->combineProduct = $_condProdCombineF->create();
        $this->locatorCondition = $locatorConditionFactory->create();
        $this->imageProcessor = $imageProcessor;
        parent::__construct(
            $context,
            $registry,
            $formFactory,
            $localeDate,
            null,
            null,
            $data
        );
        $this->configProvider = $configProvider;
        $this->dataHelper = $dataHelper;
        $this->dayNames = $this->dataHelper->getDaysNames();
        $this->reviewRepository = $reviewRepository;
        $this->customerRepository = $customerRepository;
        $this->urlBuilder = $urlBuilder;
        $this->countryFactory = $countryFactory;
        $this->regionFactory = $regionFactory;
        $this->escaper = $escaper;
    }

    public function _construct()
    {
        parent::_construct();
        $this->_init('Amasty\Storelocator\Model\ResourceModel\Location');
    }

    public function getConditionsInstance()
    {
        return $this->combineProduct;
    }

    public function getActionsInstance()
    {
        return $this->locatorCondition;
    }

    /**
     * @return string
     */
    public function getMarkerMediaUrl()
    {
        if ($this->getMarkerImg()) {
            return $this->imageProcessor->getImageUrl(
                [ImageProcessor::AMLOCATOR_MEDIA_PATH, $this->getId(), $this->getMarkerImg()]
            );
        }
    }

    /**
     * Getting working time for location
     *
     * @param string $dayName
     *
     * @return array
     */
    public function getWorkingTime($dayName)
    {
        $scheduleArray = $this->getDaySchedule($dayName);
        $periods = [];
        if (array_shift($scheduleArray) == 0) {
            return [$this->getDayName($dayName) => $this->configProvider->getClosedText()];
        }

        $periods[$this->getDayName($dayName)] = $this->getFromToTime(
            $scheduleArray[ScheduleDataProvider::OPEN_TIME],
            $scheduleArray[ScheduleDataProvider::CLOSE_TIME]
        );

        // not show similar from/to times for break
        if ($scheduleArray[ScheduleDataProvider::START_BREAK_TIME] != $scheduleArray[ScheduleDataProvider::END_BREAK_TIME]) {
            $periods[$this->configProvider->getBreakText()] = $this->getFromToTime(
                $scheduleArray[ScheduleDataProvider::START_BREAK_TIME],
                $scheduleArray[ScheduleDataProvider::END_BREAK_TIME]
            );
        }

        return $periods;
    }

    /**
     * @return string
     */
    public function getWorkingTimeToday()
    {
        // getting current day
        $currentDate = $this->_localeDate->date();
        $currentDay = strtolower($this->_localeDate->convertConfigTimeToUtc($currentDate, 'l'));
        $todaySchedule = $this->getDaySchedule($currentDay);

        if (array_shift($todaySchedule) == 0) {
            return $this->configProvider->getClosedText();
        }

        return $this->getFromToTime(
            $todaySchedule[ScheduleDataProvider::OPEN_TIME],
            $todaySchedule[ScheduleDataProvider::CLOSE_TIME]
        );
    }

    /**
     * @param string $dayName
     *
     * @return array
     */
    public function getDaySchedule($dayName)
    {
        $schedule = $this->getSchedule();

        if (array_key_exists($dayName, $schedule)) {
            $scheduleKey = strtolower($this->dayNames[$dayName]->getText());
        } else {
            // getting day of the week for compatibility with old module versions
            $scheduleKey = date("N", strtotime($dayName));
        }

        return $schedule[$scheduleKey];
    }

    /**
     * @param string $dayName
     *
     * @return string
     */
    public function getDayName($dayName)
    {
        if (array_key_exists($dayName, $this->dayNames)) {
            $dayName = $this->dayNames[$dayName]->getText();
        } else {
            $dayName = date('l', strtotime("Sunday + $dayName days"));
        }

        return $dayName;
    }

    /**
     * Getting from/to time
     *
     * @param array $from
     * @param array $to
     *
     * @return string
     */
    public function getFromToTime($from, $to)
    {
        $from = implode(':', $from);
        $to = implode(':', $to);
        $needConvertTime = $this->configProvider->getConvertTime();
        if ($needConvertTime) {
            $from = date("g:i a", strtotime($from));
            $to = date("g:i a", strtotime($to));
        }

        return implode(' - ', [$from, $to]);
    }

    private function getSchedule()
    {
        if ($this->getScheduleString()) {
            return $this->serializer->unserialize($this->getScheduleString());
        }
    }

    /**
     * @return array|bool
     */
    public function getLocationReviews()
    {
        $locationId = $this->getId();

        $reviews = $this->reviewRepository->getApprovedByLocationId($locationId);
        $result = [];

        if ($reviews) {
            /** @var \Amasty\Storelocator\Model\Review $review */
            foreach ($reviews as $review) {
                try {
                    $customer = $this->customerRepository->getById($review->getCustomerId());
                    $customerName = $customer->getFirstname() . ' ' . $customer->getLastname();
                } catch (NoSuchEntityException $e) {
                    $customerName = 'Anonymus';
                    continue;
                }
                array_push(
                    $result,
                    [
                        'name'         => $customerName,
                        'review'       => $review->getReviewText(),
                        'rating'       => $review->getRating(),
                        'published_at' => $review->getPublishedAt()
                    ]
                );
            }

            return $result;
        } else {
            return false;
        }
    }

    /**
     * @return bool|int
     */
    public function getLocationAverageRating()
    {
        $locationId = $this->getId();

        $reviews = $this->reviewRepository->getApprovedByLocationId($locationId);
        $rating = 0;
        $count = 0;

        if ($reviews) {
            /** @var \Amasty\Storelocator\Model\Review $review */
            foreach ($reviews as $review) {
                $rating += (int)$review->getRating();
                $count++;
            }

            return $rating / $count;
        } else {
            return false;
        }
    }

    /**
     * return string
     */
    public function getDateFormat()
    {
        $this->_localeDate->getDateFormat();
    }

    /**
     * Set templates html
     */
    public function setTemplatesHtml()
    {
        $this->getResource()->setAttributesData($this);
        $this->setStoreListHtml($this->getStoreListHtml());
        $this->setPopupHtml($this->getPopupHtml());
    }

    /**
     * Get store list html
     */
    public function getStoreListHtml()
    {
        $storeListTemplate = $this->configProvider->getStoreListTemplate();

        return $this->replaceLocationValues($storeListTemplate);
    }

    /**
     * Get popup html
     */
    public function getPopupHtml()
    {
        $baloon = $this->configProvider->getLocatorTemplate();

        return $this->replaceLocationValues($baloon);
    }

    /**
     * Return html with replaced values
     *
     * @param string $template
     *
     * @return string $html
     */
    public function replaceLocationValues($template)
    {
        $locationData = $this->getData();
        $template = preg_replace_callback(
            '/{{if(.*)}}(.*){{\/\if(.*)}}/miU',
            function ($match) use ($locationData) {
                if (isset($locationData[$match['1']])) {
                    $value = $this->getPreparedValue($match['1']);

                    return str_replace('{{' . $match['1'] . '}}', $value, $match['2']);
                } else {
                    return '';
                }
            },
            $template
        );

        $html = preg_replace_callback(
            '/{{(.*)}}/miU',
            function ($match) use ($locationData) {
                if (isset($locationData[$match['1']]) || isset($locationData['attributes'][$match['1']])) {
                    if (isset($locationData['attributes'][$match['1']])) {
                        return $this->convertAttributeData($locationData['attributes'][$match['1']]);
                    }

                    return $this->getPreparedValue($match['1']);
                } else {
                    return '';
                }
            },
            $template
        );

        return $html;
    }

    /**
     * Get prepared value by key
     *
     * @param string $key
     *
     * @return string
     */
    public function getPreparedValue($key)
    {
        switch ($key) {
            case 'name':
                if ($this->getUrlKey() && $this->configProvider->getEnablePages()) {
                    return '<div class="amlocator-title"><a class="amlocator-link" href="' . $this->getLocationUrl()
                        . '" title="' . $this->escaper->escapeHtml($this[$key]) . '" target="_blank">'
                        . $this->escaper->escapeHtml($this[$key]) . '</a></div>';
                }

                return '<div class="amlocator-title">' . $this->escaper->escapeHtml($this[$key]) . '</div>';
            case 'description':
                return $this->getPreparedDescription();
            case 'country':
                return $this->getCountryName();
            case 'state':
                return $this->getStateName();
            case 'rating':
                return $this->getData($key);
            default:
                return $this->escaper->escapeHtml($this->getData($key));
        }
    }

    /**
     * Get location url
     *
     * @return string
     */
    private function getLocationUrl()
    {
        return $this->escaper->escapeHtml(
            $this->urlBuilder->getUrl($this->configProvider->getUrl() . '/' . $this->getUrlKey())
        );
    }

    /**
     * Get prepared description
     *
     * @return string
     */
    public function getPreparedDescription()
    {
        $descriptionLimit = $this->configProvider->getDescriptionLimit();
        $description = $this->escaper->escapeHtml(strip_tags($this->getDescription()));
        if (strlen($description) < $descriptionLimit) {
            return $description;
        }

        if ($descriptionLimit) {
            $description = substr($description, 0, $descriptionLimit) . '...';

            if ($this->configProvider->getEnablePages()) {
                $description .= '<a href="' . $this->getLocationUrl() . '" title="read more" target="_blank"> '
                    . __('Read More') . '</a>';
            }
        }

        return '<div class="amlocator-description">' . $description . '</div>';
    }

    /**
     * Convert attributes data to html
     *
     * @param array $attributesData
     *
     * @return string $html
     */
    private function convertAttributeData($attributesData)
    {
        $html = '';
        $html .= $this->escaper->escapeHtml($attributesData['frontend_label']) . ':<br>';
        if (isset($attributesData['option_title']) && is_array($attributesData['option_title'])) {
            foreach ($attributesData['option_title'] as $option) {
                $html .= '- ' . $this->escaper->escapeHtml($option) . '<br>';
            }
            return $html;
        } else {
            return $html . $this->escaper->escapeHtml($attributesData['option_title']) . '<br>';
        }
    }

    /**
     * Get country name
     *
     * @return string
     */
    public function getCountryName()
    {
        return $this->countryFactory->create()->loadByCode($this->getCountry())->getName();
    }

    /**
     * Get state name
     *
     * @return string
     */
    public function getStateName()
    {
        return $this->regionFactory->create()->load($this->getState())->getName();
    }

    /**
     * Retrieve rule actions model
     *
     * @return \Magento\Rule\Model\Action\Collection
     */
    public function getActions()
    {
        if (!$this->_actions) {
            $this->_resetActions();
        }

        // Load rule actions if it is applicable
        if ($this->hasActionsSerialized()) {
            $actions = $this->getActionsSerialized();
            if (!empty($actions)) {
                $actions = $this->serializer->unserialize($actions);
                if (is_array($actions) && !empty($actions)) {
                    $this->_actions->loadArray($actions);
                }
            }
            $this->unsActionsSerialized();
        }

        return $this->_actions;
    }

    public function activate()
    {
        $this->setStatus(1);
        $this->save();

        return $this;
    }

    public function inactivate()
    {
        $this->setStatus(0);
        $this->save();

        return $this;
    }

    /**
     * Set flags for saving new location
     */
    public function setModelFlags()
    {
        $this->getResource()->setResourceFlags();
    }
}
