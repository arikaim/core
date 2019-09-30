<?php
/**
 *  Arikaim
 *
 * @link        http://www.arikaim.com
 * @copyright   Copyright (c) 2017-2019 Konstantin Atanasov <info@arikaim.com>
 * @license     http://www.arikaim.com/license.html
 * 
*/
namespace Arikaim\Core\Traits\Db;

use Arikaim\Core\View\Template\Template;

/**
 *  Translations trait      
*/
trait Translations 
{           
    /**
     * Get translation refernce attribute name 
     *
     * @return string
     */
    public function getTranslationReferenceAttributeName()
    {
        return (isset($this->translation_reference_attribute) == true) ? $this->translation_reference_attribute : null;
    }

    /**
     * HasMany relation
     *
     * @return void
     */
    public function translations()
    {
        $translation_model_class = (isset($this->translation_model_class) == true) ? $this->translation_model_class : null;
        return $this->hasMany($translation_model_class);
    }

    /**
     * Get translation model
     *
     * @param string $language
     * @return Model|false
     */
    public function translation($language = null)
    {
        $language = (empty($language) == true) ? Template::getLanguage() : $language;
        $model = $this->translations()->getQuery()->where('language','=',$language)->first(); 

        return (is_object($model) == false) ? false : $model;
    }

    /**
     * Create or update translation 
     *
     * @param string|integer|null $id
     * @param array $data
     * @param string $language
     * @return Model|boolean
     */
    public function saveTranslation(array $data, $language = null, $id = null)
    {
        $language = (empty($language) == true) ? Template::getLanguage() : $language;
        $model = (empty($id) == true) ? $this : $this->findById($id);     
        $reference = $this->getTranslationReferenceAttributeName();

        $data['language'] = $language;
        $data[$reference] = $model->id;
        $translation = $model->translation($language);

        return ($translation === false) ? $model->translations()->create($data) : $translation->update($data);        
    }

    /**
     * Delete translation
     *
     * @param string|integer|null $id
     * @param string $language
     * @return boolean
     */
    public function removeTranslation($id = null, $language = null)
    {
        $language = (empty($language) == true) ? Template::getLanguage() : $language;
        $model = (empty($id) == true) ? $this : $this->findById($id);     
        $model = $model->translation($language);

        return (is_object($model) == true) ? $model->delete() : false;
    }

    /**
     * Delete all translations
     *
     * @param string|integer|null $id
     * @return boolean
     */
    public function removeTranslations($id = null)
    {
        $model = (empty($id) == true) ? $this : $this->findById($id);
        $model = $model->translations();

        return (is_object($model) == true) ? $model->delete() : false;
    }
}
