<?php
/**
 * Arikaim
 *
 * @link        http://www.arikaim.com
 * @copyright   Copyright (c)  Konstantin Atanasov <info@arikaim.com>
 * @license     http://www.arikaim.com/license
 * 
*/
namespace Arikaim\Core\Middleware;

/**
 *  Middleware base class
 */
class Middleware
{
    /**
     * Middleware params
     *
     * @var array
     */
    protected $params = [];

    /**
     * Constructor
     *
     * @param array|null $params
     */
    public function __construct(?array $params = [])
    {
        $this->params = $params ?? [];
    }
    
    /**
     * Get param value
     *
     * @param string $name
     * @param mixed $default
     * @return mixed|null
     */
    public function getParam(string $name, $default = null)
    {
        return $this->params[$name] ?? $default;
    }

    /**
     * Set param
     *
     * @param string $name
     * @param mixed $value
     * @return void
     */
    public function setParam(string $name, $value): void
    {
        $this->params[$name] = $value;        
    }

    /**
     * Set param
     *
     * @param string $name
     * @param mixed $value
     * @return Middleware
     */
    public function withParam(string $name, $value)
    {
        $this->setParam($name,$value);
        
        return $this;
    }

    /**
     * Return all params
     *
     * @return array
     */
    public function getParams(): array
    {
        return $this->params;
    }
}
