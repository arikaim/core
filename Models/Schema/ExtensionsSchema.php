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
use Arikaim\Core\Traits\Db\Status;;

/**
 * Extensions database table schema definition.
 */
class ExtensionsSchema extends Schema  
{    
    protected $table_name = "extensions";

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
            $table->string('alias')->nullable(true);            
            $table->string('title')->nullable(false)->default('');
            $table->text('description')->nullable(true);
            $table->string('short_description')->nullable(true);
            $table->string('version')->nullable(false);
            $table->integer('status')->nullable(false)->default(Status::ACTIVE());
            $table->string('class')->nullable(false);     
            $table->integer('type')->nullable(false)->default(0);
            $table->integer('position')->nullable(false)->default(0);
            $table->text('admin_menu')->nullable(true);
            $table->text('console_commands')->nullable(true);
            $table->string('uuid')->nullable(false);
            $table->string('license_key')->nullable(true);
            // unique indexes
            $table->unique('uuid');
            $table->unique('alias');
            $table->unique('license_key');
            $table->unique('name');
            // indexes
            $table->index('status');
            $table->index('position');
            $table->index('title');
            $table->index('class');
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
