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
 * Modules database table schema definition.
 */
class ModulesSchema extends Schema  
{    
    protected $table_name = "modules";

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
            $table->string('title')->nullable(true);
            $table->text('description')->nullable(true);
            $table->string('short_description')->nullable(true);
            $table->string('version')->nullable(false);
            $table->integer('status')->nullable(false)->default(Status::ACTIVE());
            $table->string('class')->nullable(false);    
            $table->string('facade_class')->nullable(true);   
            $table->string('facade_alias')->nullable(true);           
            $table->integer('type')->nullable(false)->default(0);
            $table->integer('bootable')->nullable(true);
            $table->text('console_commands')->nullable(true);
            $table->string('service_name')->nullable(true);  
            $table->string('uuid')->nullable(false);
           
            // unique indexes
            $table->unique('uuid');
            $table->unique('facade_alias');
            $table->unique('facade_class');
            $table->unique('name');
            // indexes
            $table->index('status');
            $table->index('title');
            $table->index('class');
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
