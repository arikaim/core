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

class View
{
    protected $loader;
    protected $environment;

    public function __construct($path, $settings = [])
    {
        $this->loader = $this->createLoader(is_string($path) ? [$path] : $path);
        $this->environment = new \Twig_Environment($this->loader, $settings);
    }

    public function addExtension(\Twig_ExtensionInterface $extension)
    {
        $this->environment->addExtension($extension);
    }

    public function fetch($template, $data = [])
    {
        return $this->environment->render($template, $data);
    }

    public function fetchBlock($template, $block, $data = [])
    {
        return $this->environment->loadTemplate($template)->renderBlock($block, $data);
    }

    public function fetchFromString($string, $data = [])
    {
        return $this->environment->createTemplate($string)->render($data);
    }

    public function render($response, $template, $data = [])
    {
         $response->getBody()->write($this->fetch($template, $data));
         return $response;
    }

    public function getLoader()
    {
        return $this->loader;
    }

    public function getEnvironment()
    {
        return $this->environment;
    }

    private function createLoader(array $paths)
    {
        $loader = new \Twig_Loader_Filesystem();
        foreach ($paths as $namespace => $path) {
            if (is_string($namespace)) {
                $loader->setPaths($path, $namespace);
            } else {
                $loader->addPath($path);
            }
        }
        return $loader;
    }
}
