<?php

namespace Phpactor\WorseReflection;

interface Logger
{
    public function warning(string $message);

    public function debug(string $message);
}
