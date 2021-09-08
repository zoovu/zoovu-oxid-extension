<?php

namespace Semknox\Productsearch\Application\Model;

use OxidEsales\Eshop\Application\Model\Article;
use OxidEsales\Eshop\Core\Registry;
use OxidEsales\Eshop\Application\Model\Category;
use Semknox\Core\Transformer\AbstractProductTransformer;
use OxidEsales\Eshop\Core\Field;
use Semknox\Productsearch\Application\Model\SxHelper;

class ArticleTransformer extends AbstractProductTransformer
{

    protected $_product;

    /**
     * Class constructor.
     */
    public function __construct(Article $oxProduct)
    {
        $this->_product = $oxProduct;
        $this->_sxHelper = new SxHelper;
    }


    /**
     * transform oxid article to semknox-product
     */
    public function transform($transformerArgs = array())
    {
        //https://docs.oxid-esales.com/sourcecodedocumentation/6.1.4/class_oxid_esales_1_1_eshop_community_1_1_application_1_1_model_1_1_article.html

        $oxArticle = $this->_product;
        $sxArticle = array();

        $sxArticle['identifier'] = $oxArticle->getId();
        $sxArticle['groupIdentifier'] = $oxArticle->getParentId() ? $oxArticle->getParentId() : $oxArticle->getId();

        $sxArticle['name'] = $oxArticle->oxarticles__oxtitle->value;

        $sxArticle['productUrl'] = $oxArticle->getMainLink(); //$oxArticle->getLink();

        $categories = array();
        if(!isset($transformerArgs['disableCategories']) || !$transformerArgs['disableCategories']){
           $categories = $this->_getCategories();
        }

        if(!count($categories)){
            $categories = [
                [
                    'path' => ['uncategorized']
                ]
            ];
        }
        $sxArticle['categories'] = $categories;
        
        $sxArticle['images'] = $this->_getImages($transformerArgs);

        $userGroups = isset($transformerArgs['userGroup']) ? $transformerArgs['userGroup'] : array();
        $userGroups = !is_array($userGroups) ? [$userGroups] : $userGroups;

        $lang = isset($transformerArgs['lang']) ? $transformerArgs['lang'] : null;
        $userGroups = array_merge($userGroups, $oxArticle->getLinkedSubshops($lang));
        $userGroups = array_unique($userGroups);
        $userGroups = array_values($userGroups); // because api expects array

        $sxArticle['attributes'] = $this->_getAttributes($userGroups, $transformerArgs);

        if ($userGroups) {
            $sxArticle['settings'] = [
                'includeUserGroups' => $userGroups,
            ];
        }

        return $sxArticle;
    }


    /**
     * get categories of product
     * 
     * @return array 
     */
    protected function _getCategories()
    {
        $oxArticle = $this->_product;

        $categories = [];

        foreach($oxArticle->getCategoryIds() as $oxCategoryId){

            $categoryPath = $this->_sxHelper->getCategoryPath($oxCategoryId);

            if(!count($categoryPath)) continue;

            $categories[] = [
                'path' => $categoryPath
            ];
            
        }

        return $categories;
    }

    /**
     * get images of product
     */
    protected function _getImages($transformerArgs = array())
    {
        $oxArticle = $this->_product;
        $sxImages = $oxArticle->getPictureGallery();
        $images = array();

        $imageSuffix = '';
        $imageTypeSuffix = array();
        if(isset($transformerArgs['imageUrlSuffix'])){
            if(!is_array($transformerArgs['imageUrlSuffix'])){
                $imageSuffix = (string) $transformerArgs['imageUrlSuffix'];
            } else {
                $imageTypeSuffix = $transformerArgs['imageUrlSuffix'];
            }
        } 

        if (isset($sxImages['Pics'])) {

            foreach ($sxImages['Pics'] as $image) {
                $images[] = [
                    'url' => $image,
                    'type' => 'SMALL'
                ];
            }
        }

        if (isset($sxImages['ZoomPics'])) {

            foreach ($sxImages['ZoomPics'] as $image) {
                $images[] = [
                    'url' => $image['file'],
                    'type' => 'LARGE'
                ];
            }
        }

        if (isset($sxImages['Icons'])) {

            foreach ($sxImages['Icons'] as $image) {
                $images[] = [
                    'url' => $image,
                    'type' => 'THUMB'
                ];
            }
        }

        //check for correct file type
        foreach($images as $key => $image){

            $fileExtension = strtolower(pathinfo($image['url'], PATHINFO_EXTENSION));

            if(!in_array($fileExtension, ['jpg','jpeg','png','gif'])){
                unset($images[$key]);
                continue;
            }

            if(isset($imageTypeSuffix[$image['type']])){
                $images[$key]['url'] .= (string) $imageTypeSuffix[$image['type']];
            } else {
                $images[$key]['url'] .= $imageSuffix;
            }
            
        }

        return array_values($images);
    }

    /**
     * gert attributes of product
     */
    protected function _getAttributes($userGroups = array(), $transformerArgs = array())
    {

        $oxRegistry = new Registry;
        $oxLanguage = $oxRegistry->getLang();

        $oxArticle = $this->_product;
        $attributes = array();

        $lang = [
            'OXUNITNAME' => $oxLanguage->translateString('OXUNITQUANTITY'),
            'OXUNITQUANTITY' => $oxLanguage->translateString('QUANTITY'),
        ];


        foreach ($oxArticle as $key => $field) {

            if (is_object($field) && $field instanceof Field) {
                $translationKey = strtoupper(str_replace('oxarticles__', '', $key));
                $translatedKey = isset($lang[$translationKey]) ? $lang[$translationKey] : $oxLanguage->translateString($translationKey);

                if ($translationKey == $translatedKey) continue;

                $attributes[$key] = [
                    'key' => $translatedKey,
                    'value' => isset($value[$translationKey]) ? $value[$translationKey] : (string) $field,
                ];
            } else {
    
                $attributes[$key] = [
                    'key' => $key,
                    'value' => (string) $field,
                ];
            }
        }

        // description
        $attributes['oxarticles__oxlongdesc'] = [
            'key' => $oxLanguage->translateString('DESCRIPTION'),
            'value' => (string) $oxArticle->getLongDescription()
        ];

        // price
        $sxPrice = $oxArticle->getPrice();
        $attributes['oxarticles__oxprice'] = [
            'key' => $oxLanguage->translateString('OXPRICE'),
            'value' => $sxPrice->getPrice()
        ];

        $attributes['oxarticles__oxbprice'] = [
            'key' => $oxLanguage->translateString('OXBPRICE'),
            'value' => $sxPrice->getBruttoPrice()
        ];

        $attributes['oxarticles__oxnprice'] = [
            'key' => $oxLanguage->translateString('SX_price_netto'),
            'value' => $sxPrice->getNettoPrice()
        ];

        $attributes['oxarticles__oxvat'] = [
            'key' => $oxLanguage->translateString('SX_price_vat'),
            'value' => $sxPrice->getVat()
        ];
        
        // oxid attributes (Varianten artikel)
        foreach ($oxArticle->getAttributes() as $oxAttribute) {

            $key = 'oxattribute_'.$oxAttribute->oxattribute__oxid;

            // todo: not working in other languages!
            $attributes[$key] = [
                'key' => $oxAttribute->oxattribute__oxtitle->value,
                'value' => $oxAttribute->oxattribute__oxvalue->value
            ];
        }

        // get manufacturer
        if ($manufacturer = $oxArticle->getManufacturer()) {
            $attributes['oxarticles__oxmanufacturer'] = [
                'key' => $oxLanguage->translateString('MANUFACTURER'),
                'value' => $manufacturer->oxmanufacturers__oxtitle
            ];
        }

        // get supplier / vendor
        if ($vendor = $oxArticle->getVendor()) {
            $attributes['oxarticles__oxvendor'] = [
                'key' => $oxLanguage->translateString('VENDOR'),
                'value' => $vendor->oxvendor__oxtitle
            ];
        }


        if ($userGroups) {
            foreach ($attributes as &$attribute) {
                $attribute['userGroups'] = $userGroups;
            }
        }


        // check if parent value exists for empty fields
        if($oxParentArticle = $oxArticle->getParentArticle()){

            foreach ($attributes as $key => $attribute) {

                // if value is not empty... do nothing
                if($attribute['value'] && $attribute['value'] != '0000-00-00') continue;

                $attribute['value'] = (string) $oxParentArticle->{$key} ? (string) $oxParentArticle->{$key} : $attribute['value'];

                $attributes[$key] = $attribute;
            }

        }

        // recheck if every attribute has a key
        foreach($attributes as $key => $value){
            if(!$value['key'] || !strlen(trim($value['key']))){
                unset($attributes[$key]);
                continue;
            }

            if(in_array($value['value'],["0000-00-00 00:00:00", "0000-00-00"])) $value['value'] = null;

            if ((!$value['value'] || !strlen(trim($value['value']))) && $value['value']  !== '0') {
                unset($attributes[$key]);
                continue;
            }

            $attributes[$key]['value'] = (string) $attributes[$key]['value']; // api expects always strings!!

        }

        // transform to boolean
        $isBoolean = ['oxarticles__oxactive', 'oxarticles__oxhidden', 'oxarticles__oxremindactive', 'oxarticles__oxissearch', 'oxarticles__oxisconfigurable', 'oxarticles__oxnonmaterial', 'oxarticles__oxfreeshipping', 'oxarticles__oxblfixedprice', 'oxarticles__oxskipdiscounts', 'oxarticles__oxshowcustomagreement'];
        foreach ($isBoolean as $key) {
            if(isset($attributes[$key])){
                $attributes[$key]['value'] = $attributes[$key]['value'] == 0 ? 'false' : 'true'; // api expects strings! boolval($attributes[$key]['value']);
            }
        }

        // ignore for semknox api (email 2020-08-04)
        $ignore = ['oxarticles__oxvarminprice', 'oxarticles__oxactive', 'oxarticles__oxmanufacturerid', 'oxarticles__oxissearch', 'oxarticles__oxremindactive', 'oxarticles__oxremindamount', 'oxarticles__oxskipdiscounts'];
        foreach($ignore as $key){
            if(isset($attributes[$key])) unset($attributes[$key]);
        }

        // add currency
        if(isset($transformerArgs['currency'])){
            $addCurrency = ['oxarticles__oxprice', 'oxarticles__oxnprice', 'oxarticles__oxpricea', 'oxarticles__oxpriceb', 'oxarticles__oxpricec', 'oxarticles__oxbprice', 'oxarticles__oxtprice', 'oxarticles__oxvarminprice', 'oxarticles__oxvarmaxprice' ];
            
            foreach($addCurrency as $key){
                if (isset($attributes[$key])) $attributes[$key]['value'] .= ' '. $transformerArgs['currency'];
            }
        }


        // get subshop MainLinks
        foreach($this->_product->getUserGroupMainLinks($transformerArgs) as $userGroup => $mainLink){
            $attributes[] = [
                'key' => 'shop-specific-url',
                'userGroups' => [$userGroup],
                'value' => $mainLink
            ];
        }


        return array_values($attributes); // array values... because removing elements makes transforms array to assoziative array => error in validator
    }
}
