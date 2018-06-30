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
use Arikaim\Core\Db\DateTimeAttribute;
use Arikaim\Core\Db\Uuid;

/**
 * JobsQueue database model
 */
class JobsQueue extends Model  
{
    use Uuid,
        DateTimeAttribute;

    protected $table = "jobs_queue";
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
