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
use Arikaim\Core\Db\Status;

/**
 * EventSubscribers database table schema definition.
*/
class EventSubscribersSchema extends Schema  
{    
    protected $table_name = "event_subscribers";

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
            $table->string('handler_method')->nullable(true);            
            $table->string('extension_name')->nullable(true);
            $table->integer('status')->nullable(false)->default(Status::ACTIVE());
            $table->integer('priority')->nullable(false)->default(0);
            $table->string('uuid')->nullable(false);
            // indexes
            $table->unique('uuid');
            $table->unique(['name','handler_class']);
            $table->unique(['name','extension_name']);
            $table->index('status');
            $table->index('name');
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
