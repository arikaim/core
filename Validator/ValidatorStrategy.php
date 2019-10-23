<?php
/**
 * Arikaim
 *
 * @link        http://www.arikaim.com
 * @copyright   Copyright (c) 2017-2019 Konstantin Atanasov <info@arikaim.com>
 * @license     http://www.arikaim.com/license.html
 * 
 */
declare(strict_types=1);

namespace Arikaim\Core\Validator;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Slim\Interfaces\InvocationStrategyInterface;

/**
 * Response validator strategy
 */
class ValidatorStrategy implements InvocationStrategyInterface
{
    /**
     * Invoke a route callable with request, response, Validator with rote parameters.
     * 
     * @param array|callable         $callable
     * @param ServerRequestInterface $request
     * @param ResponseInterface      $response
     * @param array                  $route_arguments
     *
     * @return mixed
     */
    public function __invoke(callable $callable, ServerRequestInterface $request, ResponseInterface $response, array $route_arguments): ResponseInterface  
    {
        foreach ($route_arguments as $k => $v) {          
            $request = $request->withAttribute($k, $v);
        }
        $body_data = $request->getParsedBody();
        $body_data = (is_array($body_data) == false) ? [] : $body_data;
        $data = array_merge($route_arguments,$body_data);
      
        $validator = new Validator($data);
        return $callable($request, $response, $validator, $route_arguments);
    }
}
