<?php
/**
 * Arikaim
 *
 * @link        http://www.arikaim.com
 * @copyright   Copyright (c) 2017-2019 Konstantin Atanasov <info@arikaim.com>
 * @license     http://www.arikaim.com/license.html
 * 
*/
namespace Arikaim\Core\View;

use Twig\Environment;
use Twig\Extension\ExtensionInterface;
use Twig\Loader\FilesystemLoader;

use Arikaim\Core\Arikaim;
use Arikaim\Core\View\Template\Extension;

/**
 * Twig View
 */
class View
{
    /**
     * Template loader
     *
     * @var Twig\Loader\FilesystemLoader
     */
    private $loader;
    
    /**
     * Twig env
     *
     * @var Twig\Environment
     */
    private $environment;

    /**
     * Constructor
     *
     * @param array $paths
     * @param array $settings
     */
    public function __construct($paths, $settings = [])
    {
        $this->loader = $this->createLoader($paths);       
        $this->environment = new Environment($this->loader,$settings);
        $this->environment->addGlobal('current_component_name','');
        
        // add template extensions
        $this->addExtension(new Extension());
    }

    /**
     * Add template extension
     *
     * @param ExtensionInterface $extension
     * @return void
     */
    public function addExtension(ExtensionInterface $extension)
    {
        $this->environment->addExtension($extension);
    }

    /**
     * Render template
     *
     * @param string $template
     * @param array $params
     * @return string
     */
    public function fetch($template, $params = [])
    {       
        return $this->environment->render($template,$params);
    }

    /**
     * Render template block
     *
     * @param string $template
     * @param string $block
     * @param array $params
     * @return string
     */
    public function fetchBlock($template, $block, $params = [])
    {
        return $this->environment->loadTemplate($template)->renderBlock($block,$params);
    }

    /**
     * Render template from string
     *
     * @param string $string
     * @param array $params
     * @return string
     */
    public function fetchFromString($string, $params = [])
    {
        return $this->environment->createTemplate($string)->render($params);
    }

    /**
     * Get Twig loader
     *
     * @return Twig\Loader\FilesystemLoader
     */
    public function getLoader()
    {
        return $this->loader;
    }

    /**
     * Get Twig environment
     *
     * @return Environment
     */
    public function getEnvironment()
    {
        return $this->environment;
    }

    /**
     * Add path to loader
     *
     * @param string $path
     * @return void
     */
    public function addPath($path)
    {
        return $this->environment->getLoader()->addPath($path); 
    }

    /**
     * Create template loader
     *
     * @param array $paths
     * @return FilesystemLoader
     */
    private function createLoader($paths)
    {
        $paths = (is_array($paths) == false) ? $paths = [$paths] : $paths;
        
        return new FilesystemLoader($paths);
    }
}
