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
 * Language database table schema definition.
 */
class LanguageSchema extends Schema  
{    
    protected $table_name = "language";

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
            $table->string('code',10)->nullable(false);
            $table->string('code_3',3)->nullable(true);
            $table->string('country_code',20)->nullable(false);
            $table->string('title')->nullable(false);
            $table->string('native_title')->nullable(true)->default('');
            $table->integer('position')->nullable(true);
            $table->integer('status')->nullable(false)->default(1);
            $table->integer('default')->nullable(false)->default(0);
            $table->string('uuid')->nullable(false);       
            // indexes
            $table->unique('code');
            $table->unique('code_3');
            $table->unique('uuid');
            $table->unique('position');
            $table->index('status');
            $table->index('default');
            $table->index('country_code');
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
