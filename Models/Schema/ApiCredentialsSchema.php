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
 * Api Credentials database table schema definition.
*/
class ApiCredentialsSchema extends Schema  
{    
    /**
     * Db table name
     *
     * @var string
     */ 
    public $tableName = "api_credentials";

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
        $table->string('key')->nullable(false);
        $table->string('secret')->nullable(false);
        $table->status();
        $table->dateCreated();
        $table->dateUpdated();
        $table->dateExpired();
        // unique indexes
        $table->unique('key');         
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
