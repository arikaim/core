<?php
/**
 * Arikaim
 *
 * @link        http://www.arikaim.com
 * @copyright   Copyright (c) 2017-2018 Konstantin Atanasov <info@arikaim.com>
 * @license     http://www.arikaim.com/license.html
 * 
*/
namespace Arikaim\Core\Models;

use Illuminate\Database\Eloquent\Model;
use Arikaim\Core\Traits\Db\DateTimeAttribute;

/**
 * Options database model
 */
class Logs extends Model  
{
    use DateTimeAttribute;

    protected $fillable = [
        'message',
        'url',
        'user_agent',
        'ip_address',
        'level',
        'channel',
        'method',
        'route_uuid'];
        
    public $timestamps = false;
}
