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

use Illuminate\Database\Capsule\Manager;
use Arikaim\Core\Db\Schema;

class RoutesSchema extends Schema  
{    
    protected $table_name = "routes";

    public function create() 
    {
        $this->createTable(function($table) {
       
            // columns
            $table->bigIncrements('id')->nullable(false);
            $table->string('name')->nullable(true);
            $table->string('path')->nullable(false);
            $table->string('pattern')->nullable(false);
            $table->string('method')->nullable(false);
            $table->string('handler_extension')->nullable(true); 
            $table->string('handler_class')->nullable(false);
            $table->string('handler_method')->nullable(true)->default('');
            $table->string('extension_name')->nullable(false);
            $table->integer('status')->nullable(false)->default(1);
            $table->integer('auth')->nullable(false)->default(0);
            $table->integer('type')->nullable(false)->default(0);
            $table->string('uuid')->nullable(false);
            // indexes           
            $table->unique(['path','method']);
            $table->unique('uuid');
            $table->unique('name');
            $table->index('status');
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
