<?php

namespace Shyim\CheckIfEmailExists\Tests;

use Shyim\CheckIfEmailExists\EmailChecker;
use Shyim\CheckIfEmailExists\DNS;
use Shyim\CheckIfEmailExists\Misc;
use Shyim\CheckIfEmailExists\SMTP;
use PHPUnit\Framework\TestCase;
use Shyim\CheckIfEmailExists\SMTPResult;

class EmailCheckerTest extends TestCase
{
    public function testInvalidSyntax()
    {
        $checker = new EmailChecker();
        $result = $checker->check('invalid-email');
        $this->assertFalse($result->isSyntaxValid);
        $this->assertEquals('Invalid syntax', $result->error);
    }

    public function testNoMxRecords()
    {
        $dns = $this->createStub(DNS::class);
        $dns->method('getMxRecords')->willReturn([]);

        $checker = new EmailChecker($dns);
        $result = $checker->check('test@example.com');

        $this->assertTrue($result->isSyntaxValid);
        $this->assertFalse($result->hasMxRecords);
        $this->assertEquals('No MX records found', $result->error);
    }

    public function testSmtpConnectFail()
    {
        $dns = $this->createStub(DNS::class);
        $dns->method('getMxRecords')->willReturn(['mx.example.com']);

        $smtp = $this->createStub(SMTP::class);
        $smtp->method('check')->willReturn(new SMTPResult(error: 'Connection refused'));

        $checker = new EmailChecker($dns, $smtp);
        $result = $checker->check('test@example.com');

        $this->assertTrue($result->hasMxRecords);
        $this->assertFalse($result->isReachable);
        $this->assertEquals('Connection refused', $result->error);
    }

    public function testDeliverable()
    {
        $dns = $this->createStub(DNS::class);
        $dns->method('getMxRecords')->willReturn(['mx.example.com']);

        $smtp = $this->createStub(SMTP::class);
        $smtp->method('check')->willReturn(new SMTPResult(canConnect: true, isDeliverable: true));

        $checker = new EmailChecker($dns, $smtp);
        $result = $checker->check('test@example.com');

        $this->assertTrue($result->isReachable);
        $this->assertFalse($result->isCatchAll);
        $this->assertEmpty($result->error);
    }

    public function testCatchAll()
    {
        $dns = $this->createStub(DNS::class);
        $dns->method('getMxRecords')->willReturn(['mx.example.com']);

        $smtp = $this->createStub(SMTP::class);
        $smtp->method('check')->willReturn(new SMTPResult(canConnect: true, isDeliverable: true, isCatchAll: true));

        $checker = new EmailChecker($dns, $smtp);
        $result = $checker->check('test@example.com');

        $this->assertTrue($result->isReachable);
        $this->assertTrue($result->isCatchAll);
    }
    
    public function testDisposableAndRole()
    {
        $dns = $this->createStub(DNS::class);
        $dns->method('getMxRecords')->willReturn(['mx.example.com']);

        $smtp = $this->createStub(SMTP::class);
        $smtp->method('check')->willReturn(new SMTPResult(canConnect: true, isDeliverable: true));
        
        $misc = $this->createStub(Misc::class);
        $misc->method('isRoleAccount')->willReturn(true);
        $misc->method('isDisposable')->willReturn(true);

        $checker = new EmailChecker($dns, $smtp, $misc);
        $result = $checker->check('admin@mailinator.com');

        $this->assertTrue($result->isRoleAccount);
        $this->assertTrue($result->isDisposable);
    }
}
