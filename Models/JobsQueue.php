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
use Arikaim\Core\Db\UUIDAttribute;

class JobsQueue extends Model  
{
   // use DateTimeUpdate;

    protected $table = "jobs_queue";
    protected $fillable = [
        'name',
        'priority',
        'execution_time',
        'recuring_interval',
        'created','executed',
        'handler_class',
        'uuid',
        'status',
        'extension_name',
        'job_command',
        'schedule_time'];
        
    public $timestamps = false;
}
