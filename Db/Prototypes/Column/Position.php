<?php
/**
 * Arikaim
 *
 * @link        http://www.arikaim.com
 * @copyright   Copyright (c) 2017-2019 Konstantin Atanasov <info@arikaim.com>
 * @license     http://www.arikaim.com/license.html
 * 
*/
namespace Arikaim\Core\Db\Prototypes\Column;

use Arikaim\Core\Interfaces\BlueprintPrototypeInterface;

/**
 * Position column prototype class
*/
class Position implements BlueprintPrototypeInterface
{
    /**
     * Build column
     *
     * @param Arikaim\Core\Db\TableBlueprint $table
     * @param mixed $options
     * @return void
     */
    public function build($table,...$options)
    {
        $default = (isset($options[0]) == false) ? 1 : $options[0];

        $table->integer('position')->nullable(false)->default($default); 
        $table->index('position');   
    }
}