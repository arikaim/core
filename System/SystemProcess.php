<?php
/**
 * Arikaim
 *
 * @link        http://www.arikaim.com
 * @copyright   Copyright (c) 2017-2018 Konstantin Atanasov <info@arikaim.com>
 * @license     http://www.arikaim.com/license.html
 * 
 */
namespace Arikaim\Core\System;

use Symfony\Component\Process\Process;
use Arikaim\Core\Arikaim;

/**
 * System Process
 */
class SystemProcess 
{
    public static function create($command, $cwd = null, array $env = null, $input = null, $timeout = 60, array $options = array())
    {
        return new Process($command,$cwd,$env,$input,$timeout,$options);
    }
}
