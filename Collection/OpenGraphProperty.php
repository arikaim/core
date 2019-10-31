<?php
/**
 * Arikaim
 *
 * @link        http://www.arikaim.com
 * @copyright   Copyright (c) 2017-2019 Konstantin Atanasov <info@arikaim.com>
 * @license     http://www.arikaim.com/license
 * 
*/
namespace Arikaim\Core\Collection;

/**
 * Open Graph Property class
 */
class OpenGraphProperty
{
    /**
     * Property name list
     *
     * @var array
     */
    private static $names = [
        'title',
        'type',
        'image',
        'url',
        'description',
        'determiner',
        'audio',
        'locale',
        'site_name',
        'video'
    ];

    /**
     * Types list
     *
     * @var array
     */
    private static $types = [
        'article',
        'book',
        'books.author',
        'books.book',
        'books.genre',
        'website',
        'profile',
        'music.song',
        'music.album',
        'music.playlist',
        'video.movie',
        'video.episode'
    ];

    /**
     * Property options
     *
     * @var array
     */
    private static $optionNames = [
        'width',
        'height',
        'alt',
        'type',
        'secure_url',
        'locale',
        'alternate'
    ];

    /**
     * Property name
     *
     * @var string
     */
    protected $name;

    /**
     * Property value
     *
     * @var string
     */
    protected $value;

    /**
     * Property options
     *
     * @var array
     */
    protected $options;

    /**
     * Constructor
     *
     * @param array $data
     */
    public function __construct($name, $value, $options = []) 
    {  
        $this->name = $name;
        $this->value = $value;
        $this->options = $options;
    }

    /**
     * Set property option
     *
     * @param string $name
     * @param array $arguments
     * @return void
     */
    public function __call($name, $arguments)
    {
        $this->option($name,$arguments[0]);
    }

    /**
     * Set property option
     *
     * @param string $name
     * @param string $value
     * @return void
     */
    public function option($name, $value)
    {
        $this->options[$name] = $value;      
    }

    /**
     * Return property as array
     *
     * @return array
     */
    public function toArray() {
        return [
            'name'  => $this->name,
            'value' => $this->value,
            'options' => $this->options
        ];
    }

    /**
     * Return true if peroperty type is valid 
     *
     * @param string $type
     * @return boolean
     */
    public static function isValidType($type)
    {
        return in_array($type,Self::$types);
    }

    /**
     * Return true if peroperty name is valid 
     *
     * @param string $name
     * @return boolean
    */
    public static function isValidName($name)
    {
        return in_array($name,Self::$names);
    }

    /**
     * Return true if peroperty option name is valid 
     *
     * @param string $name
     * @return boolean
    */
    public static function isValidOptionName($name)
    {
        return in_array($name,Self::$optionNames);
    }
}
