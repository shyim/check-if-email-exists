<?php

namespace Shyim\CheckIfEmailExists\Tests;

use Shyim\CheckIfEmailExists\Misc;
use PHPUnit\Framework\TestCase;

class MiscTest extends TestCase
{
    public function testRoleAccount()
    {
        $misc = new Misc();
        $this->assertTrue($misc->isRoleAccount('admin'));
        $this->assertTrue($misc->isRoleAccount('support'));
        $this->assertFalse($misc->isRoleAccount('john.doe'));
    }

    public function testDisposable()
    {
        $misc = new Misc();
        $this->assertTrue($misc->isDisposable('test@mailinator.com'));
        $this->assertFalse($misc->isDisposable('test@gmail.com'));
    }

    public function testB2C()
    {
        $misc = new Misc();
        $this->assertTrue($misc->isB2C('gmail.com'));
        $this->assertTrue($misc->isB2C('yahoo.com'));
        $this->assertFalse($misc->isB2C('mycompany.com'));
    }
}
