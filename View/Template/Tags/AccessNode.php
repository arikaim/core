<?php
/**
 * Arikaim
 *
 * @link        http://www.arikaim.com
 * @copyright   Copyright (c) 2017-2019 Konstantin Atanasov <info@arikaim.com>
 * @license     http://www.arikaim.com/license
 * 
*/
namespace Arikaim\Core\View\Template\Tags;

use Twig\Compiler;
use Twig\Node\Node;
use Twig\Node\NodeOutputInterface;

use Arikaim\Core\Arikaim;

/**
 * Access tag node
 */
class AccessNode extends Node implements NodeOutputInterface
{
    /**
     * Constructor
     *
     * @param Node $body
     * @param array $params
     * @param integer $line
     * @param string $tag
     */
    public function __construct(Node $body, $params = [], $line = 0, $tag = 'access')
    {
        parent::__construct(['body' => $body],$params,$line,$tag);
    }

    /**
     * Compile node
     *
     * @param Compiler $compiler
     * @return void
     */
    public function compile(Compiler $compiler)
    {
        $compiler->addDebugInfo($this);    
        $permission = $this->getAttribute('permission');
        if (Arikaim::access()->hasAccess($permission) == false) {
            $compiler->raw("echo \\Arikaim\\Core\\View\\Html\\HtmlComponent::getErrorMessage('Access denied! Permission required <b>$permission</b>');" . PHP_EOL);        
            return;
        }     
        $compiler->subcompile($this->getNode('body'),true);  
    }
}
