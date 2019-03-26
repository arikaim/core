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
use Twig\Extension\ExtensionInterface;
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

    public function __construct($paths, $settings = [])
    {
        $this->loader = $this->createLoader($paths);       
        $this->environment = new Environment($this->loader,$settings);
        
        $this->components = new Collection();
        $this->component = new HtmlComponent();
    }

    public function addExtension(ExtensionInterface $extension)
    {
        $this->environment->addExtension($extension);
    }

    public function fetch($template, $params = [])
    {       
        return $this->environment->render($template, $params);
    }

    public function fetchBlock($template, $block, $params = [])
    {
        return $this->environment->loadTemplate($template)->renderBlock($block, $params);
    }

    public function fetchFromString($string, $params = [])
    {
        return $this->environment->createTemplate($string)->render($params);
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
        return $this->environment->getLoader()->addPath($path); 
    }

    private function createLoader($paths)
    {
        $paths = (is_array($paths) == false) ? $paths = [$paths] : $paths;
        return new FilesystemLoader($paths);
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
