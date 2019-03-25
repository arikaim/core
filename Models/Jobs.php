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
use Arikaim\Core\Traits\Db\Uuid;;
use Arikaim\Core\Traits\Db\Find;;
use Arikaim\Core\Traits\Db\Status;;

/**
 * Jobs database model
 */
class Jobs extends Model  
{
    use Uuid,
        Find,
        Status,
        DateTimeAttribute;

    protected $table = "jobs";
    
    protected $fillable = [
        'name',
        'priority',
        'execution_time',
        'recuring_interval',
        'executed',
        'handler_class',
        'uuid',
        'status',
        'extension_name',
        'job_command',
        'schedule_time'];
        
    public $timestamps = false;
}
