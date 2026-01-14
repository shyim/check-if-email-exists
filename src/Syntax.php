<?php

namespace Shyim\CheckIfEmailExists;

class Syntax
{
    public string $address;
    public string $domain;
    public string $username;

    public function __construct(string $address)
    {
        $parts = explode('@', $address);
        $this->username = $parts[0] ?? '';
        $this->domain = $parts[1] ?? '';

        if ($this->domain !== '') {
            $encodedDomain = idn_to_ascii($this->domain);

            if ($encodedDomain !== false && $encodedDomain !== $this->domain) {
                $this->domain = $encodedDomain;
            }
        }

        $this->address = $this->username . '@' . $this->domain;
    }

    public function isValid(): bool
    {
        return filter_var($this->address, FILTER_VALIDATE_EMAIL) !== false;
    }
}
