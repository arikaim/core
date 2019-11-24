<?php
/**
 * Arikaim
 *
 * @link        http://www.arikaim.com
 * @copyright   Copyright (c)  Konstantin Atanasov <info@arikaim.com>
 * @license     http://www.arikaim.com/license
 * 
*/
namespace Arikaim\Core\Packages\Module;

use Arikaim\Core\Packages\PackageRepository;
use Arikaim\Core\Packages\Interfaces\RepositoryInterface;

/**
 * Module package repository
*/
class ModuleRepository extends PackageRepository implements RepositoryInterface
{
    /**
     * Constructor
     *
     * @param string $repositoryUrl
     */
    public function __construct($repositoryUrl)
    {
        parent::__construct($repositoryUrl);
    }

    /**
     * Install package
     *
     * @param string|null $version
     * @return boolean
     */
    public function install($version = null)
    {
        return true;
    }
}
