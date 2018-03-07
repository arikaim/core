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

class ExtensionsSchema extends Schema  
{    
    protected $table_name = "extensions";

    public function create() 
    {
        $this->createTable(function($table) {
            
            // columns
            $table->bigIncrements('id')->nullable(false);
            $table->string('name')->nullable(false);
            $table->string('title')->nullable(false)->default('');
            $table->text('description')->nullable(true)->default('');
            $table->string('short_description')->nullable(true)->default('');
            $table->string('version')->nullable(false);
            $table->integer('status')->nullable(false)->default(1);
            $table->string('class')->nullable(false);     
            $table->integer('type')->nullable(false)->default(0);
            $table->integer('admin_link_position')->nullable(true);           
            $table->string('admin_link_title')->nullable(false)->default('');
            $table->string('admin_link_icon')->nullable(false)->default('');
            $table->string('admin_link_sub_title')->nullable(false)->default('');
            $table->string('admin_link_component')->nullable(false)->default('');
            $table->string('uuid')->nullable(false);
            $table->string('license_key')->nullable(true);
            // indexes
            $table->unique('uuid');
            $table->unique('license_key');
            $table->unique('name');
            $table->index('status');
            $table->index('title');
            $table->index('admin_link_position');
            $table->index('class');
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
