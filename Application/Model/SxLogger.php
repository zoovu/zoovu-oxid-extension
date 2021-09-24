<?php

namespace Semknox\Productsearch\Application\Model;

use Semknox\Core\Services\Logging\NullLoggingService;
use oxRegistry;

class SxLogger extends NullLoggingService {

    /**
     * @inheritDoc
     */
    public function info($message)
    {
        $this->log($message,'info');
        return true;
    }

    /**
     * @inheritDoc
     */
    public function warning($message)
    {
        $this->log($message,'warning');
        return true;
    }

    /**
     * @inheritDoc
     */
    public function error($message)
    {
        $this->log($message,'error');
        return true;
    }


    public function log($message, $logLevel = 'info')
    {
        $logLevel = \strtolower($logLevel);
        switch($logLevel){
            case 'error':
                $logLevel = 'err';
                break;
            case 'warning':
                $logLevel = 'warn';
                break;
            case 'debug':
                $logLevel = 'debug';
                break;
            default:
                if (!in_array($logLevel, ['info', 'alert', 'notice'])) {
                    $logLevel = 'info';
                }
                break;

        }

        $config = \OxidEsales\Eshop\Core\Registry::getConfig();
        $logFile = $config->getLogsDir(). 'semknox.log';

        $message = '['.date("y:m:d h:i:s").'] ' . $message .\PHP_EOL;
        file_put_contents($logFile, $message, FILE_APPEND | LOCK_EX);
    }

}