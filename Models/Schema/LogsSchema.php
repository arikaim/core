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
 * Logs database table schema definition.
 */
class LogsSchema extends Schema  
{    
    protected $table_name = "logs";

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
            $table->integer('level')->nullable(false);
            $table->string('message')->nullable(false)->default("");
            $table->string('channel')->nullable(false)->default("");            
            $table->string('url')->nullable(false);
            $table->string('method',40)->nullable(false)->default("");   
            $table->string('user_agent')->nullable(false)->default("");
            $table->string('ip_address',50)->nullable(false)->default("");
            $table->string('route_uuid',100)->nullable(true);
            // date time columns
            $table->bigInteger('created')->nullable(true);
            // indexes
            $table->index('url');
            $table->index('method');
            $table->index('level');
            $table->index('ip_address');    
            $table->index('created');             
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
