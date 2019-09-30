<?php
/**
 * Arikaim
 *
 * @link        http://www.arikaim.com
 * @copyright   Copyright (c) 2017-2019 Konstantin Atanasov <info@arikaim.com>
 * @license     http://www.arikaim.com/license.html
 * 
*/
namespace Arikaim\Core\Db\Prototypes\Table;

use Arikaim\Core\Interfaces\BlueprintPrototypeInterface;

/**
 * Translations table prototype class
*/
class Translations implements BlueprintPrototypeInterface
{
    /**
     * Build table
     *
     * @param Arikaim\Core\Db\TableBlueprint $table
     * @param mixed $options (source feild, source table)
     * @return void
     */
    public function build($table,...$options)
    {                    
        $callback = (isset($options[2]) == true) ? $options[2] : null;
        // columns
        $table->id();
        $table->prototype('uuid');      
        $table->language();       
        $table->relation($options[0],$options[1],false);
    
        if (is_callable($callback) == true) {         
            $call = function() use($callback,$table) {
                $callback($table);                                 
            };
            $call($table);
        }
        // indexes
        $table->unique([$options[0],'language']);     
    }
}
