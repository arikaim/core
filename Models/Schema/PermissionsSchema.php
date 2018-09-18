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
 * Permissions database table schema definition.
*/
class PermissionsSchema extends Schema  
{    
    protected $table_name = "permissions";

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
            $table->bigInteger('user_id')->unsigned()->nullable(true);     
            $table->bigInteger('group_id')->unsigned()->nullable(true);   
            $table->integer('read')->nullable(false)->default(0);
            $table->integer('write')->nullable(false)->default(0);
            $table->integer('delete')->nullable(false)->default(0);
            $table->integer('execute')->nullable(false)->default(0);
            $table->string('uuid')->nullable(false);
            // unique indexes
            $table->index('name');
            $table->unique('uuid');
            $table->unique(array('name','user_id'));           
            $table->unique(array('name','group_id'));
            // foreign keys
            $table->foreign('user_id')->references('id')->on('users'); 
            $table->foreign('group_id')->references('id')->on('user_groups'); 
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
