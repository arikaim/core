<?php
/**
 * Arikaim
 *
 * @link        http://www.arikaim.com
 * @copyright   Copyright (c) 2017-2018 Konstantin Atanasov <info@arikaim.com>
 * @license     http://www.arikaim.com/license.html
 * 
*/
namespace Arikaim\Core\View;

use Twig\Environment;
use Twig\Extension\GlobalsInterface;
use Twig\Loader\FilesystemLoader;

use Arikaim\Core\Arikaim;
use Arikaim\Core\View\Html\HtmlComponent;
use Arikaim\Core\Utils\Collection;
use Arikaim\Core\Cache\Cache;

/**
 * View
 */
class View
{
    private $loader;
    private $environment;
    private $components;

    public function __construct($path, $settings = [])
    {
        $this->loader = $this->createLoader($path);       
        $this->environment = new Environment($this->loader,$settings);
        
        $this->components = new Collection();
        $this->component = new HtmlComponent();
    }

    public function addExtension(GlobalsInterface $extension)
    {
        $this->getEnvironment()->addExtension($extension);
    }

    public function fetch($template, $params = [])
    {       
        return $this->getEnvironment()->render($template, $params);
    }

    public function fetchBlock($template, $block, $params = [])
    {
        return $this->getEnvironment()->loadTemplate($template)->renderBlock($block, $params);
    }

    public function fetchFromString($string, $params = [])
    {
        return $this->getEnvironment()->createTemplate($string)->render($params);
    }

    public function render($template, $params = [])
    {
        return Arikaim::response()->getBody()->write($this->fetch($template, $params));
    }

    public function getLoader()
    {
        return $this->loader;
    }

    public function getEnvironment()
    {
        return $this->environment;
    }

    public function addPath($path)
    {
        return $this->getEnvironment()->getLoader()->addPath($path); 
    }

    private function createLoader($paths)
    {
        if (is_string($paths) == true) {
            $paths = array($paths); 
        }
        $loader = new FilesystemLoader($paths);
        return $loader;
    }

    public function components()
    {
        return $this->components;
    }

    public function component()
    {
        return $this->component;
    }
}
