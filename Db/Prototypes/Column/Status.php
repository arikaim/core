<?php
/**
 * Arikaim
 *
 * @link        http://www.arikaim.com
 * @copyright   Copyright (c)  Konstantin Atanasov <info@arikaim.com>
 * @license     http://www.arikaim.com/license
 * 
*/
namespace Arikaim\Core\Db\Prototypes\Column;

use Arikaim\Core\Db\BlueprintPrototypeInterface;
use Arikaim\Core\Traits\Db\Status as StatusTrait;

/**
 * Status column prototype class
*/
class Status implements BlueprintPrototypeInterface
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
        $default = (isset($options[0]) == false) ? StatusTrait::$ACTIVE : $options[0];

        $table->integer('status')->nullable(false)->default($default); 
        $table->index('status');   
    }
}
