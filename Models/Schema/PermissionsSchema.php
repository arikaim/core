<?php
/**
 * Arikaim
 *
 * @link        http://www.arikaim.com
 * @copyright   Copyright (c) 2017-2019 Konstantin Atanasov <info@arikaim.com>
 * @license     http://www.arikaim.com/license
 * 
*/
namespace Arikaim\Core\Models\Schema;

use Arikaim\Core\Db\Schema;

/**
 * Permissions database table schema definition.
*/
class PermissionsSchema extends Schema  
{    
    /**
     * Db table name
     *
     * @var string
     */ 
    protected $tableName = "permissions";

    /**
     * Create table
     *
     * @param \Arikaim\Core\Db\TableBlueprint $table
     * @return void
     */
    public function create($table) 
    {            
        // columns
        $table->id();
        $table->prototype('uuid');            
        $table->userId();
        $table->relation('permission_id','permissions_list',false);
        $table->relation('group_id','user_groups',true);
        $table->integer('read')->nullable(false)->default(0);
        $table->integer('write')->nullable(false)->default(0);
        $table->integer('delete')->nullable(false)->default(0);
        $table->integer('execute')->nullable(false)->default(0);
        // unique indexes         
        $table->unique(['permission_id','user_id']);           
        $table->unique(['permission_id','group_id']);                     
    }

    /**
     * Update table
     *
     * @param \Arikaim\Core\Db\TableBlueprint $table
     * @return void
     */
    public function update($table) 
    {        
    }
}
