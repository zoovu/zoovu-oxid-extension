<?php

namespace Semknox\Productsearch\Application\Controller;

use OxidEsales\Eshop\Core\Registry;

use Semknox\Productsearch\Application\Model\SxHelper;


class ArticleListController extends ArticleListController_parent
{


    /**
     * Template variable getter. Returns sorting columns
     *
     * @return array
     */
    public function getSortColumns()
    {
        // fallback, if not active for current shop
        if (!$this->_aArticleList->isSxArticleList) return parent::getSortColumns();

        return $this->_aArticleList->getAvailableSortingOptions();
    }
   

    /**
     * Returns default category sorting for selected category
     *
     * @return array
     */
    public function getUserSelectedSorting()
    {
        $request = Registry::get(\OxidEsales\Eshop\Core\Request::class);
        $sortBy = $request->getRequestParameter($this->getSortOrderByParameterName());
        $sortOrder = $request->getRequestParameter($this->getSortOrderParameterName());

        if ($sortBy && $sortOrder && SxHelper::isEncodedOption($sortBy)) {
            return ['sortby' => $sortBy, 'sortdir' => $sortOrder];
        }

        return parent::getUserSelectedSorting();
    }



    public function getSortingSql($ident)
    {
        $sorting = $this->getSorting($this->getSortIdent());
        $sortBy = $sorting['sortby'];

        if (SxHelper::isEncodedOption($sortBy)) {
            // acitve semknox search shop
            return $sorting;
        }

        // fallback, if not active for current shop
        $sortingSql = parent::getSortingSql($ident);
        return $sortingSql == '``' ? false : $sortingSql;
    }


    /**
     * get attributeList for filtering
     * 
     * @return mixed 
     */
    public function getAttributes()
    {
        $this->isSxSearch = true;
        return $this->_aArticleList->getAvailableFilters();
    }

    /**
     * get attributeList for filtering range attributes
     * 
     * @return mixed 
     */
    public function getRangeAttributes()
    { 
        return $this->_aArticleList->getAvailableRangeFilters();
    }


    /**
     * get Active Multiselect Options for frontend 
     * 
     * @return mixed 
     */
    public function getAttributeOptions()
    {
        return $this->_aArticleList->getAttributeOptions();
    }


}
