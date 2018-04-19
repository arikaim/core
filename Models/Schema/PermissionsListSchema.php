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

/**
 * Permissions List database table schema definition.
*/
class PermissionsListSchema extends Schema  
{    
    protected $table_name = "permissions_list";

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
            $table->integer('parent_id')->nullable(true)->default(0);
            $table->string('name')->nullable(false);
            $table->string('extension_name')->nullable(true);
            $table->string('title')->nullable(true);
            $table->string('description')->nullable(true);
            $table->string('uuid')->nullable(false);
            // indexes
            $table->unique('uuid');
            $table->unique('name');
            $table->index('extension_name');
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
