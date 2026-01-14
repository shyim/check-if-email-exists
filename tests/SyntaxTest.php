<?php

namespace Shyim\CheckIfEmailExists\Tests;

use Shyim\CheckIfEmailExists\Syntax;
use PHPUnit\Framework\TestCase;

class SyntaxTest extends TestCase
{
    public function testValidEmail()
    {
        $syntax = new Syntax('test@example.com');
        $this->assertTrue($syntax->isValid());
        $this->assertEquals('test', $syntax->username);
        $this->assertEquals('example.com', $syntax->domain);
    }

    public function testIdnDomain()
    {
        $syntax = new Syntax('test@täst.de');
        $this->assertTrue($syntax->isValid());
        $this->assertEquals('test', $syntax->username);
        $this->assertEquals('xn--tst-qla.de', $syntax->domain);
    }

    public function testInvalidEmail()
    {
        $syntax = new Syntax('invalid-email');
        $this->assertFalse($syntax->isValid());
    }

    public function testMissingDomain()
    {
        $syntax = new Syntax('test@');
        $this->assertFalse($syntax->isValid());
        $this->assertEquals('test', $syntax->username);
        $this->assertEquals('', $syntax->domain);
    }

    public function testMissingUsername()
    {
        $syntax = new Syntax('@example.com');
        $this->assertFalse($syntax->isValid());
        $this->assertEquals('', $syntax->username);
        $this->assertEquals('example.com', $syntax->domain);
    }
}
