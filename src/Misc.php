<?php

namespace Shyim\CheckIfEmailExists;

use Fgribreau\MailChecker;

class Misc
{
    private array $roles = [];
    private array $b2c = [];

    public function __construct()
    {
        $this->roles = file(__DIR__ . '/../resources/roles.txt', FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        $this->b2c = file(__DIR__ . '/../resources/b2c.txt', FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);

        $this->roles = array_flip($this->roles);
        $this->b2c = array_flip($this->b2c);
    }

    public function isRoleAccount(string $username): bool
    {
        return isset($this->roles[strtolower($username)]);
    }

    public function isDisposable(string $email): bool
    {
        return !MailChecker::isValid($email);
    }
    
    public function isB2C(string $domain): bool
    {
        return isset($this->b2c[strtolower($domain)]);
    }
}
