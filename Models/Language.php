<?php
/**
 * Arikaim
 *
 * @link        http://www.arikaim.com
 * @copyright   Copyright (c) 2017-2018 Konstantin Atanasov <info@arikaim.com>
 * @license     http://www.arikaim.com/license.html
 * 
*/
namespace Arikaim\Core\Models;

use Illuminate\Database\Eloquent\Model;
use Arikaim\Core\Db\UUIDAttribute;
use Arikaim\Core\Db\ToggleValue;
use Arikaim\Core\Db\Position;

class Language extends Model  
{
    use UUIDAttribute,ToggleValue,Position;

    protected $table = 'language';
    protected $fillable = ['id','code','title','position','uuid','default','status','native_title','code_3','country_code'];
   
    public $timestamps = false;
    
    public function getModel()
    {
        return $this;
    }

    public function hasLanguage($code)
    {
        $language = $this->where('code','=',$code)->get()->toArray();
        if (empty($language) == true) {
            return false;
        }
        return true;
    }

    public function getDefaultLanguage()
    {
        try {
            $model = $this->where('default','=','1')->first();
            if (is_object($model) == true) {
                return $model->code;
            } 
        } catch(\Exception $e) {
            return "en";
        }
        return "en";
    }
}
