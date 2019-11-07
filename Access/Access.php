<?php
/**
 * Arikaim
 *
 * @link        http://www.arikaim.com
 * @copyright   Copyright (c) 2017-2019 Konstantin Atanasov <info@arikaim.com>
 * @license     http://www.arikaim.com/license
 * 
 */
namespace Arikaim\Core\Access;

use Arikaim\Core\Utils\Arrays;
use Arikaim\Core\Arikaim;
use Arikaim\Core\Db\Model;
use Arikaim\Core\Interfaces\Auth\PermissionsInterface;

/**
 * Manage access.
 */
class Access 
{
    /**
     *  Full permissions
     */
    const FULL = ['read','write','delete','execute'];
    
    /**
     * Read
     */
    const READ      = ['read'];
    const WRITE     = ['write'];
    const DELETE    = ['delete'];
    const EXECUTE   = ['execute'];
    
    /**
     * Control panel permission
     */
    const CONTROL_PANEL = "ControlPanel";
    
    /**
     * Permissions provider
     *
     * @var PermissionsInterface
     */
    private $provider;

    /**
     * Constructor
     * 
     * @param PermissionsInterface $provider
     */
    public function __construct(PermissionsInterface $provider = null) 
    {
        $this->provider = ($this->provider == null) ? Model::PermissionRelations() : $provider;        
    }

    /**
     * Set permissions provider
     *
     * @param PermissionsInterface $provider
     * @return void
     */
    public function setProvider(PermissionsInterface $provider)
    {
        $this->provider = $provider;
    }

    /**
     * Get permissions provider
     *
     * @return PermissionsInterface
     */
    public function getProvider()
    {        
        return $this->provider;
    }
    
    /**
     * Check if current loged user have control panel access
     *
     * @return boolean
     */
    public function hasControlPanelAccess()
    {
        return $this->hasAccess(Access::CONTROL_PANEL,ACCESS::FULL);
    }
    
    /**
     * Check access 
     *
     * @param string $name Permission name
     * @param string|array $type PermissionType (read,write,execute,delete)
     * @return boolean
     */
    public function hasAccess($name, $type = null, $id = null)
    {
        $id = (empty($id) == true) ? Arikaim::auth()->getId() : $id; 
        if (empty($id) == true) {
            return false;
        }
        list($name, $permissionType) = $this->resolvePermissionName($name);
       
        if (is_array($permissionType) == false) {
            $permissionType = $this->resolvePermissionType($type);
        }
    
        return $this->getProvider()->hasPermissions($name,$id,$permissionType);            
    }

    /**
     * Resolve permission type
     *
     * @param string|array $type
     * @return array|null
     */
    public function resolvePermissionType($type)
    {
        if (is_array($type) == true) {
            return $type;
        }
    
        if (is_string($type) == true) {
            $type = Arrays::toArray($type,",");
        }
        return null;
    }

    /**
     * Resolve permission full name  name:type
     *
     * @param string $name
     * @return array
     */
    public function resolvePermissionName($name)
    {
        $tokens = explode(':',$name);
        $name = $tokens[0];
        $type = (isset($tokens[1]) == true) ? $tokens[1] : Self::FULL;     

        if (is_string($type) == true) {
            $type = (strtolower($type) == 'full') ? Self::FULL : Arrays::toArray($type,",");
        }
        return [$name,$type];
    }
}   
