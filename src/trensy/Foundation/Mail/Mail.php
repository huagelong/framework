<?php
/**
 * Trensy Framework
 *
 * PHP Version 7
 *
 * @author          kaihui.wang <hpuwang@gmail.com>
 * @copyright      trensy, Inc.
 * @package         trensy/framework
 * @version         1.0.7
 */
namespace Trensy\Foundation\Mail;

class Mail
{
    
    public static function Load()
    {
        require_once __DIR__ . "/swift_required.php";
    }

}