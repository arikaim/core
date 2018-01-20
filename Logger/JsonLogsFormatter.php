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

use Monolog\Formatter\FormatterInterface;
use Arikaim\Core\Utils\Utils;

class JsonLogsFormatter implements FormatterInterface
{
    public function __construct()
    {   
    }

    public function format(array $record)
    {
        return Utils::jsonEncode($record) . ",\n";
    }
    
    public function formatBatch(array $records)
    {
        foreach ($records as $key => $record) {
            $records[$key] = $this->format($record);
        }
        return $records;
    }
}
