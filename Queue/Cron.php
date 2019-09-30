<?php
/**
 * Arikaim
 *
 * @link        http://www.arikaim.com
 * @copyright   Copyright (c) 2017-2019 Konstantin Atanasov <info@arikaim.com>
 * @license     http://www.arikaim.com/license.html
 * 
*/
namespace Arikaim\Core\Queue;

use Arikaim\Core\System\Process;
use Arikaim\Core\Utils\Arrays;
use Arikaim\Core\Utils\StaticFacade;

/**
 * Cron jobs 
 */
class Cron extends StaticFacade
{
    /**
     * Return facade class name
     *
     * @return string
     */
    public static function getInstanceClass()
    {
        return 'Arikaim\Core\Queue\Cron';
    }

    /**
     * Get cron command
     *
     * @return string
     */
    public static function getCronCommand()
    {
        $php = (Process::findPhp() === false) ? 'php' : Process::findPhp();
        
        return "* * * * * " . $php . " " . ARIKAIM_ROOT_PATH . ARIKAIM_BASE_PATH . "/cli scheduler >> /dev/null 2>&1";
    }
    
    /**
     * Add cron entry for scheduler
     *
     * @return mixed
     */
    public function install()
    {    
        return $this->addItem(Self::getCronCommand());
    }

    /**
     * Remove cron entry for scheduler
     *
     * @return mixed
     */
    public function unInstall()
    {
        return $this->removeItem(Self::getCronCommand());   
    }

    /**
     * Return true if crontab entry is exists
     *
     * @return boolean
     */
    public function isInstalled()
    {
        $items = $this->getItems();
        return $this->hasItems($items);
    }

    /**
     * Return true if crontab have items
     *
     * @param array $items
     * @return boolean
     */
    public function hasItems($items)
    {
        $msg = "no crontab for";
        return (empty($items) == true || preg_match("/{$msg}/i", $items[0]) == true) ? false : true;
    }
    /**
     * Get crontab items
     *
     * @return array
     */
    public function getItems() {
        $output = Process::run('crontab -l');

        $output = (empty($output) == true) ? [] : $output;
        $items = Arrays::toArray($output);
        
        return  ($this->hasItems($items) == true) ? $items : [];
    }

    /**
     * Return true if crontab have item
     *
     * @param string $command
     * @return boolean
     */
    public function hasItem($command)
    {
        $commands = $this->getItems();
        return in_array($command, $commands);         
    }   

    /**
     * Add cron tab item
     *
     * @param string $command
     * @return void
     */
    public function addItem($command)
    {
        if ($this->hasItem($command) == true) {
            return true;
        }
    
        $commands = $this->getItems();
        array_push($commands,$command);
        return $this->addItems($commands);
    }

    /**
     * Add cron tab items
     *
     * @param array $commands
     * @return mixed
     */
    public function addItems(array $commands) 
    {
        return Process::run('echo "'. Arrays::toString($commands).'" | crontab -');
    }

    /**
     * Delete crontab item
     *
     * @param string $command
     * @return bool
     */
    public function removeItem($command) 
    {
        if ($this->hasItem($command) == true) {
            $commands = $this->getItems();
            unset($commands[array_search($command,$commands)]);
            return $this->addItems($commands);
        }
        return true;
    }

    /**
     * get cron details.
     *
     * @return array
     */
    public function getServiceDetails()
    {
        return [
            'name'       => "Cron",
            'installed'  => $this->isInstalled(),
            'items'      => $this->getItems(),
            'user'       => Process::getCurrentUser()['name']
        ];
    }
}
