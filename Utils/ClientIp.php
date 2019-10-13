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

use Arikaim\Core\Utils\Utils;

/**
 * Client Ip
 */
class ClientIp
{
    /**
     * Lookup in headers
     *
     * @var bool
     */
    protected static $look_in_headers = [
        'Forwarded',
        'X-Forwarded-For',
        'X-Forwarded',
        'X-Cluster-Client-Ip',
        'Client-Ip',
    ];
 
    /**
     * Return client Ip address.
     *
     * @param object $request
     * @return object
     */
    public static function getClientIpAddress($request)
    {       
        $server_params = $request->getServerParams();
        if (isset($server_params['REMOTE_ADDR']) && Utils::isValidIp($server_params['REMOTE_ADDR'])) {
            return $server_params['REMOTE_ADDR'];     
        }
            
        $ip_address = null;                 
        foreach (Self::$look_in_headers as $header) {
            if ($request->hasHeader($header)) {
                $ip = Self::getFromHeader($request, $header);
                if (Utils::isValidIp($ip) == true) {
                    return $ip;                       
                }
            }
        }
      
        return $ip_address;
    }
    
    /**
     * Return header value
     *
     * @param object $request
     * @param string $header
     * @return mixed
     */
    public static function getFromHeader($request, $header)
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
