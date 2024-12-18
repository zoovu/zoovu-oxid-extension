<?php

namespace Semknox\Productsearch\Application\Model;

use Semknox\Productsearch\Application\Model\SxQueue;
use OxidEsales\Eshop\Core\Registry;

class Article extends Article_parent
{

    /**
     * This function is triggered whenever article is saved or deleted or after the stock is changed.
     * Originally we need to update the oxstock for possible article parent in case parent is not buyable
     * Plus you may want to extend this function to update some extended information.
     * Call \OxidEsales\Eshop\Application\Model\Article::onChange($sAction, $sOXID) with ID parameter when changes are executed over SQL.
     * (or use module class instead of oxArticle if such exists)
     *
     * @param string $action          Action constant
     * @param string $articleId       Article ID
     * @param string $parentArticleId Parent ID
     *
     * @return null
     */
    public function onChange($action = null, $articleId = null, $parentArticleId = null)
    {
        parent::onChange($action, $articleId, $parentArticleId);

        // from parent function
        if (!isset($articleId)) {
            if ($this->getId()) {
                $articleId = $this->getId();
            }
            if (!isset($articleId)) {
                $articleId = $this->oxarticles__oxid->value;
            }
            if ($this->oxarticles__oxparentid && $this->oxarticles__oxparentid->value) {
                $parentArticleId = $this->oxarticles__oxparentid->value;
            }
        }

        $sxHelper = new SxHelper();

        $lang = \OxidEsales\Eshop\Core\Registry::getLang()->getLanguageAbbr($this->getLanguage());

        $configValues = $sxHelper->getConfig(ucfirst($lang));
        $configValues = $sxHelper->getMasterConfig($configValues, ucfirst($lang));
        $incrementalUpdatesActive =  (isset($configValues['incrementalUpdatesActive']) && $configValues['incrementalUpdatesActive']);

        if (!isset($articleId) || !$incrementalUpdatesActive) return;

        $sxQueue = new SxQueue();
        if ($action == ACTION_DELETE) {
            $sxQueue->set('delete');
            $sxQueue->addArticle($articleId);

            $sxQueue->set('update');
            $sxQueue->removeArticle($articleId);
        } else {
            $sxQueue->set('update');
            $sxQueue->addArticle($articleId);

            $sxQueue->set('delete');
            $sxQueue->removeArticle($articleId);
        }
    }


    public function getLinkedSubshops($lang = null)
    {
        $subShopIds = [];
        $lang = $lang ? '-' . $lang : '';

        // articlaMapId
        $articleMapId = (string) $this->oxarticles__oxmapid;
        if (!$articleMapId) return $subShopIds;

        $sSelect = "SELECT oxshopid FROM oxarticles2shop WHERE oxmapobjectid = $articleMapId";

        $result = \OxidEsales\Eshop\Core\DatabaseProvider::getDb()->select($sSelect);
        foreach ($result->fetchAll() as $row) {
            $subShopIds[] = $row[0] . $lang;
        }

        return $subShopIds;
    }

    public function getUserGroupMainLinks($transformerArgs = [])
    {
        $subShopMainLinks = [];

        $oxConfig = \OxidEsales\Eshop\Core\Registry::getConfig();
        $shopIDs = $oxConfig->getShopIds();
        $currentShopId = $this->getShopId();

        foreach ($shopIDs as $shopId) {

            // set Context
            $this->setShopId($shopId);
            $oxConfig->setShopId($shopId);

            foreach ($transformerArgs['languages'] as $langId => $lang) {
                $userGroup = $shopId . '-' . $lang;

                $transformerArgs['langId'] = $langId;
                $transformerArgs['shopId'] = $shopId;

                $subShopMainLinks[$userGroup] = $this->getSxArticleUrl($transformerArgs);
            }
        }

        // go back to original Context
        $this->setShopId($currentShopId);
        $oxConfig->setShopId($currentShopId);

        return $subShopMainLinks;
    }

    public function getSxArticleUrl($transformerArgs = [])
    {
        $langId = isset($transformerArgs['langId']) ? $transformerArgs['langId'] : '';
        $shopId = isset($transformerArgs['shopId']) ? $transformerArgs['shopId'] : '';

        if (isset($transformerArgs['seoUrlsActive']) && $transformerArgs['seoUrlsActive']) {
            // take seo URL
            return $this->getBaseSeoLink($langId, true);
        } else {
            // take parameter URL
            return '/?cl=details&anid=' . $this->getId() . '&shp=' . $shopId . '&lang=' . $langId;
        }

    }
}
