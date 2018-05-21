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
 * User groups database table schema definition.
*/
class UserGroupsSchema extends Schema  
{    
    protected $table_name = "user_groups";

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
            $table->string('title')->nullable(false);
            $table->string('description')->nullable(true);   
            $table->string('uuid')->nullable(false);
            // unique indexes
            $table->unique('uuid');
            $table->unique('title');           
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
