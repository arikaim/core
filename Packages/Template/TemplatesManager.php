<?php
/**
 * Arikaim
 *
 * @link        http://www.arikaim.com
 * @copyright   Copyright (c)  Konstantin Atanasov <info@arikaim.com>
 * @license     http://www.arikaim.com/license
 * 
*/
namespace Arikaim\Core\Packages\Template;

use Arikaim\Core\Db\Model;
use Arikaim\Core\App\Path;
use Arikaim\Core\Packages\PackageManager;
use Arikaim\Core\Packages\Template\TemplatePackage;
use Arikaim\Core\Arikaim;

/**
 * Manage templates
*/
class TemplatesManager extends PackageManager
{
    /**
     * Constructor
     */
    public function __construct()
    {
       parent::__construct(Path::TEMPLATES_PATH);
    }

    /**
     * Get template packages
     *
     * @param boolean $cached
     * @param mixed $filter
     * @return array
     */
    public function getPackages($cached = false, $filter = null)
    {
        $result = ($cached == true) ? Arikaim::cache()->fetch('templates.list') : null;
        if (is_array($result) == false) {
            $result = $this->scan($filter);
            Arikaim::cache()->save('templates.list',$result,5);
        } 

        return $result;
    }

    /**
     * Create template package
     *
     * @param string $name
     * @return TemplatePackage
     */
    public function createPackage($name)
    {
        $propertes = $this->loadPackageProperties($name);

        return new TemplatePackage($propertes);
    }

    /**
     * Return template routes
     *
     * @param string $template Template name
     * @return void
     */
    public function getRoutesList($template)
    {
        $model = Model::Routes();
        $package = $this->createPackage($template);
        $routes = $package->getRoutes();

        if (is_array($routes) == false) {
            return [];
        }

        foreach ($routes as $key => $item) {
            $routes[$key]['method'] = "GET";
            $route = $model->getPageRoute('GET',$routes[$key]['path']);
            if ($route != false) {
                $routes[$key]['status'] = $route->status;
                $routes[$key]['auth'] = $route->auth;
            } else {
                $routes[$key]['status'] = 0;
                $routes[$key]['auth'] = 0;
            }
        }
        
        return $routes;
    }
}
