<?php
/**
 * Arikaim
 *
 * @link        http://www.arikaim.com
 * @copyright   Copyright (c)  Konstantin Atanasov <info@arikaim.com>
 * @license     http://www.arikaim.com/license
 * 
*/
namespace Arikaim\Core\App;

use Arikaim\Core\Utils\Curl;
use Arikaim\Core\Http\Url;
use Arikaim\Core\Utils\Path;
use Arikaim\Core\Utils\File;
use Arikaim\Core\Utils\Utils;
use Arikaim\Core\System\Config;

/**
 * Arikaim store
*/
class ArikaimStore 
{       
    const HOST          = 'http://arikaim.com';
    const SIGNUP_URL    = Self::HOST . '/signup';  
    const LOGIN_API_URL = '';

    const ORDER_TYPE_ENVATO = 'envato';

    /**
     * product
     *
     * @var array
     */
    protected $product = [];

    /**
     * Packages
     *
     * @var array
     */
    protected $packages = [];

    /**
     * Account
     *
     * @var array
     */
    protected $account;

    /**
     * Data config file name
     *
     * @var string
     */
    protected $configFile;

    /**
     * Config
     *
     * @var Config
     */
    protected $config;

    /**
     * Constructor
     * 
     * @param string $configfileName
     */
    public function __construct(string $configfileName = 'arikaim-store.php')
    {         
        $this->configFile = Path::CONFIG_PATH . $configfileName;
        $this->clear();

        $this->config = new Config($configfileName,null,Path::CONFIG_PATH);
        if ($this->config->hasConfigFile($configfileName) == false) {
            $this->config->withData($this->toArray());
            $this->config->save();
        }
    }

    public function getConfig()
    {
        return $this->config;
    }

    /**
     * Return true if cust have  account token
     *
     * @return boolean
     */
    public function isLogged(): bool
    {
        return (empty($this->account['token']) == false);
    }

    /**
     * Get orders
     *
     * @return array
     */
    public function getProduct(): array
    {
        return $this->product;
    }

    /**
     * Init data
     *
     * @return void
     */
    protected function clear(): void
    {
        $this->account = ['token' => ''];
        $this->product = [];
        $this->packages = [];
    }

    /**
     * Logout (deletes user token)
     *
     * @return boolean
     */
    public function logout(): bool
    {
        $this->account = ['token' => ''];
        
        return $this->saveConfig();
    }

    /**
     * Save data to file
     *
     * @return boolean
     */
    protected function saveConfig(): bool
    {
        $data = $this->toArray();

        return File::write($this->configFile,Utils::jsonEncode($data));       
    }

    /**
     * Convert config data to array
     *
     * @return array
     */
    protected function toArray(): array
    {
        return [
            'account'  => $this->account,
            'product'  => $this->product,
            'packages' => $this->packages
        ];
    }

    /**
     * Is curl installed
     *
     * @return boolean
     */
    public function hasCurl(): bool
    {
        return Curl::isInsatlled();
    }

    /**
     * Fetch packages list 
     *
     * @param string $type
     * @param string|null $page
     * @param string $search
     * @return mixed
     */
    public function fetchPackages(string $type, ?string $page = '1', string $search = '')
    {
        $page = (empty($search) == true) ? $page : '/' . $page;
        $url = Self::HOST . '/api/store/product/list/' . $type . '/' . $search . $page;
         
        return Curl::get($url);
    }

    /**
     * Fetch package details 
     *
     * @param string $uuid   
     * @return mixed
     */
    public function fetchPackageDetails(string $uuid)
    {
        $url = Url::BASE_URL . '/api/products/product/details/' . $uuid;
          
        return Curl::get($url);
    }

    /**
     * Get signup url
     */
    public function getSignupUrl(): string
    {
        return Self::SIGNUP_URL;
    }

    /**
     * Get signup url
    */
    public function getLoginUrl(): string
    {
        return Self::LOGIN_API_URL;
    }
}
