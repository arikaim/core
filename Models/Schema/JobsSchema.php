<?php
/**
 * Arikaim
 *
 * @link        http://www.arikaim.com
 * @copyright   Copyright (c) 2017-2018 Konstantin Atanasov <info@arikaim.com>
 * @license     http://www.arikaim.com/license.html
 * 
*/
namespace Arikaim\Core\Models\Schema;

use Arikaim\Core\Db\Schema;
use Arikaim\Core\Traits\Db\Status;;

/**
 * Jobs database table schema definition.
 */
class JobsSchema extends Schema  
{    
    protected $table_name = "jobs";

    /**
     * Create table
     *
     * @return void
     */
    public function create() 
    {
        $this->createTable(function($table) {
            
            // columns
            $table->bigIncrements('id')->nullable(false);
            $table->string('name')->nullable(false);
            $table->string('handler_class')->nullable(false);
            $table->biginteger('execution_time')->nullable(true);
            $table->string('recuring_interval',50)->nullable(true);
            $table->biginteger('schedule_time')->nullable(true);
            $table->string('extension_name')->nullable(true);
            $table->string('job_command')->nullable(true);
            $table->integer('priority')->nullable(false)->default(0);
            $table->integer('status')->nullable(false)->default(Status::ACTIVE());
            // date time
            $table->bigInteger('date_created')->nullable(true);
            $table->biginteger('date_executed')->nullable(true);
            $table->string('uuid')->nullable(false);            
            // indexes
            $table->unique('uuid');
            $table->index('name');
            $table->index('execution_time');
            $table->index('recuring_interval');
            $table->index('schedule_time');
            // storage engine
            $table->engine = 'InnoDB';    
                    
        });
    }

    /**
     * Modify table
     *
     * @return void
     */
    public function update()
    {
        $this->updateTable(function($table) {
        });
    }
}