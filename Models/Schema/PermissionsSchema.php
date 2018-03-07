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

class PermissionsSchema extends Schema  
{    
    protected $table_name = "permissions";

    public function create() 
    {
        $this->createTable(function($table) {       
            
            // columns
            $table->bigIncrements('id')->nullable(false);
            $table->string('object_uuid')->nullable(false);
            $table->string('key')->nullable(false);
            $table->string('title')->nullable(true);
            $table->string('description')->nullable(true);
            $table->string('object_type',50)->nullable(false)->default('user');
            $table->integer('read')->nullable(false)->default(0);
            $table->integer('write')->nullable(false)->default(0);
            $table->integer('delete')->nullable(false)->default(0);
            $table->integer('execute')->nullable(false)->default(0);
            // indexes
            $table->unique(array('key','object_uuid'));
            $table->index(array('key','object_uuid','object_type'));
            // storage engine           
            $table->engine = 'InnoDB';
            
        });
    }

    public function update() 
    {
        $this->updateTable(function($table) {            
        });
    }
}
