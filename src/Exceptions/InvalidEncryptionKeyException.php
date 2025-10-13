<?php

namespace Rareloop\Lumberjack\Exceptions;

class InvalidEncryptionKeyException extends \Exception
{
    protected $message = 'APP_KEY value is not supported. Use "wp key generate" to create a new key';
}
