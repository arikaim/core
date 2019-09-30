<?php
/**
 * Arikaim
 *
 * @link        http://www.arikaim.com
 * @copyright   Copyright (c) 2017-2019 Konstantin Atanasov <info@arikaim.com>
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
    /**
     * Db table name
     *
     * @var string
     */ 
    protected $table_name = "permissions_list";

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
        $table->string('name')->nullable(false);
        $table->string('extension_name')->nullable(true);
        $table->string('title')->nullable(true);
        $table->string('description')->nullable(true);        
        // indexes         
        $table->unique('name');
        $table->index('extension_name');
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
