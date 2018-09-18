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
 * User groups details database table schema definition.
*/
class UserGroupsDetailsSchema extends Schema  
{    
    protected $table_name = "user_groups_details";

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
            $table->bigInteger('user_id')->unsigned()->nullable(true);     
            $table->bigInteger('group_id')->unsigned()->nullable(true);  
            // date time   
            $table->bigInteger('date_created')->nullable(true);
            $table->bigInteger('date_updated')->nullable(true);
            $table->bigInteger('date_expired')->nullable(true);
            // indexes
            $table->index('date_created');
            // unique indexes
            $table->unique(['user_id','group_id']);   
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
