<?php
/**
 * @link http://www.tintsoft.com/
 * @copyright Copyright (c) 2012 TintSoft Technology Co. Ltd.
 * @license http://www.tintsoft.com/license/
 */

namespace yuncms\trade;

use yii\base\Exception;

/**
 * Class PaymentException
 * @package yuncms\trade
 */
class PaymentException extends Exception
{
    /**
     * @return string the user-friendly name of this exception
     */
    public function getName()
    {
        return 'Payment Exception';
    }
}