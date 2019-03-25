<?php
/**
 * Arikaim
 *
 * @link        http://www.arikaim.com
 * @copyright   Copyright (c) 2017-2018 Konstantin Atanasov <info@arikaim.com>
 * @license     http://www.arikaim.com/license.html
 * 
 */
namespace Arikaim\Core\Validator;

use Arikaim\Core\Utils\Factory;
use Arikaim\Core\Validator\Rule;

class RuleBuilder
{
    public function __construct() 
    {
    }

    public function createRule($name, $args = null)
    {              
        $class_name = ucfirst($name);
        return Factory::createInstance(Factory::getValidatorRuleClass($class_name),$args);            
    }

    public function createRules(array $descriptor)
    {       
        $rules = [];      
        foreach ($rules as $filed_name => $text) {
            $descriptor = $this->parseRuleDescriptor($descriptor);
           // array_push($rules,$rule);
        }
        return $rules;
    }

    public function parseRuleDescriptor($descriptor)
    {
        $result = [];
        $descriptor = trim($descriptor);
        $tokens = explode('>',$descriptor);      
        $result['class'] = Factory::getValidatorRuleClass(ucfirst($tokens[0]));
       
        $params = explode('/',$tokens[1]);
        foreach ($params as $key => $value) {
            # required, min: max: [], 
           // $result[]
        }
        return $result;
    }

    protected function parseRuleParam($text)
    {
        $tokens = explode(':',$text);    
    }
    
    public function __call($name, $args)
    {  
        return $this->createRule($name,$args);       
    }
}
