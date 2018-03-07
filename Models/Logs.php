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
use Arikaim\Core\Db\DateTimeUpdate;

class Logs extends Model  
{
    use DateTimeUpdate;

    protected $fillable = ['message','url','user_agent','ip_address','level','channel','method','created','route_uuid'];
    public $timestamps = false;
}
