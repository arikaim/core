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

use Arikaim\Core\Utils\Utils;
use Illuminate\Database\Capsule\Manager;
use Arikaim\Core\Db\Schema;
use Arikaim\Core\Db\Model;

class LogsSchema extends Schema  
{    
    protected $table_name = "logs";

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

    public function update() 
    {
        $this->updateTable(function($table) {
            
        });
    }
    
    public function addDefaultRows() 
    {
        
    }
}
