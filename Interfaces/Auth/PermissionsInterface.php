<?php
/**
 * Arikaim
 *
 * @link        http://www.arikaim.com
 * @copyright   Copyright (c)  Konstantin Atanasov <info@arikaim.com>
 * @license     http://www.arikaim.com/license
 * 
*/
namespace Arikaim\Core\Interfaces\Auth;

/**
 * Permissions interface
 */
interface PermissionsInterface
{    
    /**
     * Get user permission
     *
     * @param string $name
     * @param mixed $id
     * @param array $permissions
     * @return void
     */
    public function hasPermissions($name, $id, $permissions);
}
