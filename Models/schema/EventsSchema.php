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

use Illuminate\Database\Capsule\Manager;
use Arikaim\Core\Db\Schema;

class EventsSchema extends Schema  
{    
    protected $table_name = "events";

    public function create() 
    {
        $this->createTable(function($table) {
            
            // columns
            $table->bigIncrements('id')->nullable(false);
            $table->string('name')->nullable(true);
            $table->string('handler_class')->nullable(false);
            $table->string('extension_name')->nullable(false)->default('');
            $table->integer('status')->nullable(false)->default(1);
            $table->integer('priority')->nullable(false)->default(0);
            $table->string('uuid')->nullable(false);
            // indexes
            $table->unique('uuid');
            $table->unique(['name','handler_class']);
            $table->index('status');
            $table->index('name');
            // storage engine
            $table->engine = 'InnoDB';    
                    
        });
    }

    public function update()
    {
        $this->updateTable(function($table) {
            
        });
    }
    
    public function addDefaultRows() 
    {
        
    }
}
