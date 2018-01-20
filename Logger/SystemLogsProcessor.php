<?php
/**
 * Arikaim
 *
 * @link        http://www.arikaim.com
 * @copyright   Copyright (c) Konstantin Atanasov <info@arikaim.com>
 * @license     http://www.arikaim.com/license.html
 * 
 */
namespace Arikaim\Core\Logger;

use Arikaim\Core\Utils\Utils;

class SystemLogsProcessor
{    
    public function __construct()
    {
    }

    public function __invoke(array $record)
    {
        $record['timestamp'] = time();
        return $record;
    }
}
