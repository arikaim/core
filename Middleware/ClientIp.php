<?php
/**
 * Arikaim
 *
 * @link        http://www.arikaim.com
 * @copyright   Copyright (c) 2017-2018 Konstantin Atanasov <info@arikaim.com>
 * @license     http://www.arikaim.com/license.html
 * 
*/
namespace Arikaim\Core\Middleware;

class ClientIp
{
    private $check_proxy_headers;
    private $attribute_name;
    private $look_in_headers; 

    public function __construct($check_proxy_headers = true) {
        $this->check_proxy_headers = $check_proxy_headers;
        $this->attribute_name = 'client_ip';
        $this->look_in_headers = [
            'Forwarded',
            'X-Forwarded-For',
            'X-Forwarded',
            'X-Cluster-Client-Ip',
            'Client-Ip',
        ];
    }
     
    public function getClientIpAddress($request)
    {       
        $server_params = $request->getServerParams();
        if (isset($server_params['REMOTE_ADDR']) && $this->isValid($server_params['REMOTE_ADDR'])) {
            $request = $request->withAttribute($this->attribute_name,$server_params['REMOTE_ADDR']);     
            return $request;    
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
        $request = $request->withAttribute($this->attribute_name,$ip_address);
        return $request;
    }
   
    protected function isValid($ip_address)
    {       
        if (filter_var($ip_address, FILTER_VALIDATE_IP,FILTER_FLAG_IPV4 | FILTER_FLAG_IPV6) === false) {
            return false;
        }
        return true;
    }

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
