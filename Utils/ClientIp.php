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

/**
 * Client Ip
 */
class ClientIp
{
    /**
     * Check proxy headers 
     *
     * @var bool
     */
    private $check_proxy_headers;
    
    /**
     * Attribute name set in request 
     *
     * @var string
     */
    private $attribute_name;
    
    /**
     * Lookup in headers
     *
     * @var bool
     */
    private $look_in_headers; 

    /**
     * Constructor
     *
     * @param boolean $check_proxy_headers
     */
    public function __construct($check_proxy_headers = true, $attribute_name = 'client_ip') {
        $this->check_proxy_headers = $check_proxy_headers;
        $this->attribute_name = $attribute_name;
        
        $this->look_in_headers = [
            'Forwarded',
            'X-Forwarded-For',
            'X-Forwarded',
            'X-Cluster-Client-Ip',
            'Client-Ip',
        ];
    }
     
    /**
     * Return response with client Ip address attribute.
     *
     * @param object $request
     * @return object
     */
    public function getClientIpAddress($request)
    {       
        $server_params = $request->getServerParams();
        if (isset($server_params['REMOTE_ADDR']) && $this->isValid($server_params['REMOTE_ADDR'])) {
            return $request->withAttribute($this->attribute_name,$server_params['REMOTE_ADDR']);     
        }
            
        $ip_address = null;
        if ($this->check_proxy_headers == true) {           
            foreach ($this->look_in_headers as $header) {
                if ($request->hasHeader($header)) {
                    $ip = $this->getFromHeader($request, $header);
                    if ($this->isValid($ip)) {
                        $ip_address = $ip;
                        break;
                    }
                }
            }
        }
        return $request->withAttribute($this->attribute_name,$ip_address);
    }
    
    /**
     * Return true if ip is valid
     *
     * @param string $ip_address
     * @return boolean
     */
    protected function isValid($ip_address)
    {       
        return (filter_var($ip_address, FILTER_VALIDATE_IP,FILTER_FLAG_IPV4 | FILTER_FLAG_IPV6) === false) ? false : true;        
    }

    /**
     * Return header value
     *
     * @param [object] $request
     * @param string $header
     * @return mixed
     */
    private function getFromHeader($request, $header)
    {
        $items = explode(',', $request->getHeaderLine($header));
        $value = trim(reset($items));
        if (ucfirst($header) == 'Forwarded') {
            foreach (explode(';', $value) as $part) {
                if (strtolower(substr($part, 0, 4)) == 'for=') {
                    $for = explode(']', $part);
                    $value = trim(substr(reset($for), 4), " \t\n\r\0\x0B" . "\"[]");
                    break;
                }
            }
        }
        return $value;
    }
}
