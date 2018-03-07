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

class UsersSchema extends Schema  
{    
    protected $table_name = "users";

    public function create() 
    {
        $this->createTable(function($table) {            
           
            // columns
            $table->bigIncrements('id')->nullable(false);
            $table->string('email')->nullable(true)->default(null);
            $table->string('user_name')->nullable(false);
            $table->string('password')->nullable(false);           
            $table->string('api_key')->nullable(true);
            $table->string('api_secret')->nullable(true);
            $table->integer('status')->nullable(false)->default(1);
            $table->string('uuid')->nullable(false);
            $table->string('last_login')->bigInteger(true);
            // indexes
            $table->unique('email');
            $table->unique('user_name');
            $table->unique('uuid'); 
            $table->unique('api_key');           
            // options
            $table->softDeletes();
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
