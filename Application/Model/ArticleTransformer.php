<?php

namespace Semknox\Productsearch\Application\Model;

use InvalidArgumentException;
use OxidEsales\Eshop\Application\Model\Article;
use OxidEsales\Eshop\Application\Model\CategoryList;
use OxidEsales\Eshop\Core\Exception\DatabaseConnectionException;
use OxidEsales\Eshop\Core\Exception\DatabaseErrorException;
use OxidEsales\Eshop\Core\Registry;

use Semknox\Core\Transformer\AbstractProductTransformer;

class ArticleTransformer extends AbstractProductTransformer
{

    protected $_product;

    /**
     * Class constructor.
     */
    public function __construct(Article $oxProduct)
    {
        $this->_product = $oxProduct;
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

        $sxArticle['productUrl'] = $oxArticle->getLink();

        if(!isset($transformerArgs['disableCategories'])){
            $sxArticle['categories'] = $this->_getCategories();
        }
        
        $sxArticle['images'] = $this->_getImages();

        $userGroups = isset($transformerArgs['userGroup']) ? $transformerArgs['userGroup'] : array();
        $userGroups = !is_array($userGroups) ? [$userGroups] : $userGroups;

        $lang = isset($transformerArgs['lang']) ? $transformerArgs['lang'] : null;
        $userGroups = array_merge($userGroups, $oxArticle->getLinkedSubshops($lang));

        $sxArticle['attributes'] = $this->_getAttributes($userGroups);

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
     * @throws DatabaseConnectionException 
     * @throws DatabaseErrorException 
     * @throws InvalidArgumentException 
     */
    protected function _getCategories()
    {
        $oxArticle = $this->_product;
        $oxCategoryList = new CategoryList;
        $categories = array();

        $categorieIds = $oxArticle->getCategoryIds();

        foreach ($categorieIds as $oxid) {

            $oxCategoryList->buildTree($oxid);
            $path = array();

            foreach ($oxCategoryList->getPath() as $oxCategory) {
                $title = $oxCategory->getTitle();
                $path[] = $title;
            }

            if (empty($path)) continue;

            $categories[] = [
                'path' => $path,
            ];
        }

        return $categories;
    }

    /**
     * get images of product
     */
    protected function _getImages()
    {
        $oxArticle = $this->_product;
        $sxImages = $oxArticle->getPictureGallery();
        $images = array();

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

        return $images;
    }

    /**
     * gert attributes of product
     */
    protected function _getAttributes($userGroups = array())
    {
        $oxRegistry = new Registry;
        $oxLanguage = $oxRegistry->getLang();

        $oxArticle = $this->_product;
        $attributes = array();

        // description
        $attributes[] = [
            'key' => $oxLanguage->translateString('DESCRIPTION'),
            'value' => (string) $oxArticle->getLongDescription()
        ];

        // price
        $sxPrice = $oxArticle->getPrice();
        $attributes[] = [
            'key' => $oxLanguage->translateString('PRICE'),
            'value' => $sxPrice->getPrice()
        ];

        $attributes[] = [
            'key' => $oxLanguage->translateString('SX_price_brutto'),
            'value' => $sxPrice->getBruttoPrice()
        ];

        $attributes[] = [
            'key' => $oxLanguage->translateString('SX_price_netto'),
            'value' => $sxPrice->getNettoPrice()
        ];

        $attributes[] = [
            'key' => $oxLanguage->translateString('SX_price_vat'),
            'value' => $sxPrice->getVat()
        ];

        // weight
        $attributes[] = [
            'key' => $oxLanguage->translateString('WEIGHT'),
            'value' => $oxArticle->getWeight()
        ];

        // width
        $attributes[] = [
            'key' => $oxLanguage->translateString('OXWIDTH'),
            'value' => $oxArticle->oxarticles__oxwidth->value
        ];

        // height
        $attributes[] = [
            'key' => $oxLanguage->translateString('OXHEIGHT'),
            'value' => $oxArticle->oxarticles__oxheight->value
        ];

        // length
        $attributes[] = [
            'key' => $oxLanguage->translateString('OXLENGTH'),
            'value' => $oxArticle->oxarticles__oxlength->value
        ];

        // unitquantity ... kg/euro
        $attributes[] = [
            'key' => $oxLanguage->translateString('OXUNITQUANTITY'),
            'value' => $oxArticle->oxarticles__oxunitname->value
        ];

        // quantity ... kg/euro
        $attributes[] = [
            'key' => $oxLanguage->translateString('QUANTITY'),
            'value' => $oxArticle->oxarticles__oxunitquantity->value
        ];

        // oxid attributes
        foreach ($oxArticle->getAttributes() as $oxAttribute) {

            // todo: not working in other languages!
            $attributes[] = [
                'key' => $oxAttribute->oxattribute__oxtitle->value,
                'value' => $oxAttribute->oxattribute__oxvalue->value
            ];
        }

        if ($userGroups) {
            foreach ($attributes as &$attribute) {
                $attribute['userGroups'] = $userGroups;
            }
        }

        return $attributes;
    }
}
