<?php
/**
 * Arikaim
 *
 * @link        http://www.arikaim.com
 * @copyright   Copyright (c) 2017-2018 Konstantin Atanasov <info@arikaim.com>
 * @license     http://www.arikaim.com/license
 * 
*/
namespace Arikaim\Core\Models\Schema;

use Arikaim\Core\Db\Schema;


/**
 * Sessions classes registry
*/
class Sessions extends Schema  
{    
    /**
     * Db table name
     *
     * @var string
     */
    protected $tableName = 'sessions';

    /**
     * Create table
     *
     * @param \Arikaim\Core\Db\TableBlueprint $table
     * @return void
     */
    public function create($table) 
    {       
        // columns
        $table->id();
        $table->string('session_id')->nullable(false);      
        $table->longText('data')->nullable(true);
        $table->dateColumn('access_time');
        // indexes           
        $table->unique('session_id');
        // storage engine
        $table->engine = 'MyISAM';
    }

    /**
     * Update table
     *
     * @param \Arikaim\Core\Db\TableBlueprint $table
     * @return void
     */
    public function update($table)
    {       
    }
}
