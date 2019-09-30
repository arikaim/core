<?php
/**
 * Arikaim
 *
 * @link        http://www.arikaim.com
 * @copyright   Copyright (c) 2017-2019 Konstantin Atanasov <info@arikaim.com>
 * @license     http://www.arikaim.com/license.html
 * 
*/
namespace Arikaim\Core\Utils;

use Arikaim\Core\Arikaim;

/**
 * Detect browser type
 */
class Mobile
{
    /**
     * User agent
     *
     * @var string|null
     */
    protected $user_agent;

    /**
     * Request headers
     *
     * @var array
     */
    protected $headers;

    /**
     * Cloud front headers
     *
     * @var array
     */
    protected $cloud_front_headers;
    
    /**
     * Rules
     *
     * @var array
     */
    protected $rules;

    /**
     * Mobile headers
     *
     * @var array
     */
    protected $mobile_headers = [
            'HTTP_ACCEPT' => ['matches' => [
                                    'application/x-obml2d',
                                    'application/vnd.rim.html',
                                    'text/vnd.wap.wml',
                                    'application/vnd.wap.xhtml+xml'
                                ]
                            ],
            'HTTP_X_WAP_PROFILE'           => null,
            'HTTP_X_WAP_CLIENTID'          => null,
            'HTTP_WAP_CONNECTION'          => null,
            'HTTP_PROFILE'                 => null,
            'HTTP_X_OPERAMINI_PHONE_UA'    => null,
            'HTTP_X_NOKIA_GATEWAY_ID'      => null,
            'HTTP_X_ORANGE_ID'             => null,
            'HTTP_X_VODAFONE_3GPDPCONTEXT' => null,
            'HTTP_X_HUAWEI_USERID'         => null,
            'HTTP_UA_OS'                   => null,
            'HTTP_X_MOBILE_GATEWAY'        => null,
            'HTTP_X_ATT_DEVICEID'          => null,
            'HTTP_UA_CPU'                  => ['matches' => ['ARM']],
    ];

    /**
     * Os
     *
     * @var array
     */
    protected $os = [
        'AndroidOS'         => 'Android',
        'BlackBerryOS'      => 'blackberry|\bBB10\b|rim tablet os',
        'PalmOS'            => 'PalmOS|avantgo|blazer|elaine|hiptop|palm|plucker|xiino',
        'SymbianOS'         => 'Symbian|SymbOS|Series60|Series40|SYB-[0-9]+|\bS60\b',
        'WindowsMobileOS'   => 'Windows CE.*(PPC|Smartphone|Mobile|[0-9]{3}x[0-9]{3})|Window Mobile|Windows Phone [0-9.]+|WCE;',
        'WindowsPhoneOS'    => 'Windows Phone 10.0|Windows Phone 8.1|Windows Phone 8.0|Windows Phone OS|XBLWP7|ZuneWP7|Windows NT 6.[23]; ARM;',
        'iOS'               => '\biPhone.*Mobile|\biPod|\biPad|AppleCoreMedia',
        'MeeGoOS'           => 'MeeGo',
        'MaemoOS'           => 'Maemo',
        'JavaOS'            => 'J2ME/|\bMIDP\b|\bCLDC\b',
        'webOS'             => 'webOS|hpwOS',
        'badaOS'            => '\bBada\b',
        'BREWOS'            => 'BREW',
    ];

    /**
     * Browsers
     *
     * @var array
     */
    protected $browsers = [
        'Chrome'          => '\bCrMo\b|CriOS|Android.*Chrome/[.0-9]* (Mobile)?',
        'Dolfin'          => '\bDolfin\b',
        'Opera'           => 'Opera.*Mini|Opera.*Mobi|Android.*Opera|Mobile.*OPR/[0-9.]+|Coast/[0-9.]+',
        'Skyfire'         => 'Skyfire',
        'Edge'            => 'Mobile Safari/[.0-9]* Edge',
        'IE'              => 'IEMobile|MSIEMobile',
        'Firefox'         => 'fennec|firefox.*maemo|(Mobile|Tablet).*Firefox|Firefox.*Mobile|FxiOS',
        'Bolt'            => 'bolt',
        'TeaShark'        => 'teashark',
        'Blazer'          => 'Blazer',
        'Safari'          => 'Version.*Mobile.*Safari|Safari.*Mobile|MobileSafari',
        'UCBrowser'       => 'UC.*Browser|UCWEB',
        'baiduboxapp'     => 'baiduboxapp',
        'baidubrowser'    => 'baidubrowser',
        'DiigoBrowser'    => 'DiigoBrowser',
        'Puffin'          => 'Puffin',
        'Mercury'         => '\bMercury\b',
        'ObigoBrowser'    => 'Obigo',
        'NetFront'        => 'NF-Browser',
        'GenericBrowser'  => 'NokiaBrowser|OviBrowser|OneBrowser|TwonkyBeamBrowser|SEMC.*Browser|FlyFlow|Minimo|NetFront|Novarra-Vision|MQQBrowser|MicroMessenger',
        'PaleMoon'        => 'Android.*PaleMoon|Mobile.*PaleMoon',
    ];

    /**
     * Utilities
     *
     * @var array
     */
    protected $utilities = [
        'Bot'         => 'Googlebot|facebookexternalhit|AdsBot-Google|Google Keyword Suggestion|Facebot|YandexBot|YandexMobileBot|bingbot|ia_archiver|AhrefsBot|Ezooms|GSLFbot|WBSearchBot|Twitterbot|TweetmemeBot|Twikle|PaperLiBot|Wotbox|UnwindFetchor|Exabot|MJ12bot|YandexImages|TurnitinBot|Pingdom',
        'MobileBot'   => 'Googlebot-Mobile|AdsBot-Google-Mobile|YahooSeeker/M1A1-R2D2',
        'DesktopMode' => 'WPDesktop',
        'TV'          => 'SonyDTV|HbbTV',
        'WebKit'      => '(webkit)[ /]([\w.]+)',
        'Console'     => '\b(Nintendo|Nintendo WiiU|Nintendo 3DS|PLAYSTATION|Xbox)\b',
        'Watch'       => 'SM-V700',
    ];

    /**
     * User agent headers
     *
     * @var array
     */
    protected $user_agent_headers = [
        'HTTP_USER_AGENT',
        'HTTP_X_OPERAMINI_PHONE_UA',
        'HTTP_X_DEVICE_USER_AGENT',
        'HTTP_X_ORIGINAL_USER_AGENT',
        'HTTP_X_SKYFIRE_PHONE',
        'HTTP_X_BOLT_PHONE_UA',
        'HTTP_DEVICE_STOCK_UA',
        'HTTP_X_UCBROWSER_DEVICE_UA'
    ];

    /**
     * Constructor
     */
    public function __construct() 
    {
        $this->initHeaders();
        $this->initUserAgent();

        $this->rules = array_merge(
            $this->os,
            $this->browsers,
            $this->utilities
        );
    }

    /**
     * Init headers
     *
     * @return void
     */
    public function initHeaders()
    {
        $headers = $_SERVER;
        $this->headers = [];
    
        foreach ($headers as $key => $value) {
            if (substr($key, 0, 5) === 'HTTP_') {
                $this->headers[$key] = $value;
            }
        }
        $this->initCloudFrontHeaders($headers);
    }

    /**
     * Init CloudFront headers
     *
     * @param array $headers
     * @return bool
     */
    public function initCloudFrontHeaders($headers) 
    {
        $this->cloud_front_headers = [];
      
        foreach ($headers as $key => $value) {
            if (substr(strtolower($key), 0, 16) === 'http_cloudfront_') {
                $this->cloud_front_headers[strtoupper($key)] = $value;
                return true;
            }
        }
        return false;
    }

    /**
     * Init user agent
     *
     * @return void
     */
    public function initUserAgent()
    {
        $this->user_agent = null;
        foreach ($this->user_agent_headers as $altHeader) {
            if (empty($this->headers[$altHeader]) == false) {
                $this->user_agent .= $this->headers[$altHeader] . " ";
            }
        }

        if (empty($this->user_agent) == false) {
            $this->user_agent = substr(trim($this->user_agent),0,500);
            return;
        }
        
        if (count($this->cloud_front_headers) > 0) {
            $this->user_agent = 'Amazon CloudFront';
            return;
        }
        $this->user_agent = null;
    }

    /**
     * Check for mobile headers 
     *
     * @return bool
     */
    public function checkHeadersForMobile()
    {
        foreach ($this->mobile_headers as $mobile_header => $match_type) {
            if (isset($this->headers[$mobile_header]) == true) {
                if (is_array($match_type['matches']) == true) {
                    foreach ($match_type['matches'] as $match) {
                        if (strpos($this->headers[$mobile_header],$match) !== false) {
                            return true;
                        }
                    }
                    return false;
                }
                return true;
            }
        }

        return false;
    }

    /**
     * Check user agent
     *
     * @return bool
     */
    protected function matchUserAgent()
    {
        foreach ($this->rules as $regex) {
            if (empty($regex) == true) {
                continue;
            }

            if ($this->match($regex) == true) {
                return true;
            }
        }
        return false;
    }

    /**
     * Return true if page is open from mobile browser
     *
     * @return bool
     */
    public static function mobile()
    {
        $obj = new Mobile();
        return $obj->isMobile();
    }

    /**
     * Return true if page is open from mobile browser
     *
     * @return boolean
     */
    public function isMobile()
    {
        $is_mobile = Arikaim::session()->get('mobile.mode');
        if ($is_mobile !== null) {
            return $is_mobile;
        }

        if ($this->isCloudFront() == true) {
            return true;
        }

        if ($this->checkHeadersForMobile() == true) {
            return true;
        }
        return $this->matchUserAgent();
    }

    /**
     * Set desktop mode
     *
     * @return void
     */
    public function setDesktopMode()
    {
        Arikaim::session()->get('mobile.mode',false);
    }

    /**
     * Set mobile mode
     *
     * @return void
     */
    public function setMobileMode()
    {
        Arikaim::session()->get('mobile.mode',true);
    }

    /**
     * Return true for Amazon CloudFront
     *
     * @return boolean
     */
    public function isCloudFront()
    {
        if ($this->user_agent === 'Amazon CloudFront') {
            if (array_key_exists('HTTP_CLOUDFRONT_IS_MOBILE_VIEWER', $this->cloud_front_headers) && $this->cloud_front_headers['HTTP_CLOUDFRONT_IS_MOBILE_VIEWER'] === 'true') {
                return true;
            }
        }
        return false;
    }

    /**
     * Match helper
     *
     * @param string $regex
     * @return bool
     */
    protected function match($regex)
    {
       return (bool)preg_match(sprintf('#%s#is', $regex),$this->user_agent,$matches);
    }
}
