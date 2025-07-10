<?php

namespace Chronologue\Security\Support\Attributes;

use Attribute;

#[Attribute]
class Permission
{
    public function __construct(string|array $permission)
    {
    }
}
