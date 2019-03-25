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
 * Users database table schema definition.
*/
class UsersSchema extends Schema  
{    
    protected $table_name = "users";

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
            $table->string('email')->nullable(true)->default(null);
            $table->string('user_name')->nullable(false);
            $table->string('password')->nullable(false);           
            $table->string('api_key')->nullable(true);
            $table->string('api_secret')->nullable(true);
            $table->integer('status')->nullable(false)->default(Status::ACTIVE());
            $table->string('uuid')->nullable(false);
            $table->string('access_key')->nullable(true);
            $table->integer('access_key_expire')->nullable(true);
            // date time
            $table->bigInteger('date_created')->nullable(true);
            $table->bigInteger('date_updated')->nullable(true);
            $table->bigInteger('date_login')->nullable(true);
            $table->dateTime('deleted_at')->nullable(true); 
            // indexes
            $table->index('date_created');
            $table->index('date_login');
            $table->unique('email');
            $table->unique('user_name');
            $table->unique('uuid'); 
            $table->unique('api_key');   
            $table->unique('access_key');           
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
