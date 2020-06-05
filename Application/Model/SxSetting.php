<?php

namespace Semknox\Productsearch\Application\Model;

class SxSetting {

    protected $_sxFolder = "log/semknox/";
    protected $_sxUploadBatchSize = 200;
    protected $_sxCollectBatchSize = 100;
    protected $_sxRequestTimeout = 15;

    protected $_sxSandboxApiUrl = "https://stage-oxid-v3.semknox.com/";
    protected $_sxApiUrl = "https://api-v3.semknox.com/";

    /**
     * Get a value
     *
     * @param $key
     * @param null $default
     *
     * @return mixed|null
     */
    public function get($key, $default = null)
    {
        $sxKey = '_sx'. $key;

        return isset($this->$sxKey)
            ? $this->$sxKey
            : $default;
    }


}