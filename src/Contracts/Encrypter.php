<?php

namespace Rareloop\Lumberjack\Contracts;

interface Encrypter
{
    public function encrypt($data);

    public function decrypt($data);
}
