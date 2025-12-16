<?php

namespace Shyim\CheckIfEmailExists;

class Syntax
{
    public string $address;
    public string $domain;
    public string $username;

    public function __construct(string $address)
    {
        $this->address = $address;
        $parts = explode('@', $address);
        $this->username = $parts[0] ?? '';
        $this->domain = $parts[1] ?? '';
    }

    public function isValid(): bool
    {
        return filter_var($this->address, FILTER_VALIDATE_EMAIL) !== false;
    }
}
