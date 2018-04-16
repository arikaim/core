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
 * Options database table schema definition.
*/
class OptionsSchema extends Schema  
{    
    protected $table_name = "options";

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
            $table->string('key')->nullable(false);
            $table->text('value')->nullable(true);    
            $table->string('extension')->nullable(true);       
            $table->integer('auto_load')->nullable(false)->default(1);       
            // indexes
            $table->unique('key');
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
