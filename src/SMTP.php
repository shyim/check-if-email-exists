<?php

namespace Shyim\CheckIfEmailExists;


class SMTP
{
    private const TIMEOUT = 10;

    private readonly string $heloHost;

    public function __construct(
        private readonly string $fromEmail = "verify@example.com",
    )
    {
        $syntax = new Syntax($fromEmail);
        if ($syntax->isValid() === false) {
            throw new \InvalidArgumentException(\sprintf('Syntax of email address \'%s\' is not valid.', $fromEmail));
        }

        $this->heloHost = $syntax->domain;
    }

    public function check(string $domain, string $mxHost, string $toEmail): SMTPResult
    {
        $smtpResult = new SMTPResult();

        $socket = $this->connect($mxHost, $smtpResult);
        if ($socket === null) {
            return $smtpResult;
        }

        $smtpResult->canConnect = true;

        try {
            $randomEmail = bin2hex(random_bytes(8)) . '@' . $domain;
            $this->verifyEmail($smtpResult, $socket, $randomEmail, false);

            if ($smtpResult->isDeliverable) {
                $smtpResult->isCatchAll = true;
            } else {
                 $this->sendCommand($socket, 'RSET');
                 $this->readResponse($socket);

                 $this->verifyEmail($smtpResult, $socket, $toEmail);
            }
        } catch (\Throwable $e) {
            $smtpResult->error .= ' Exception: ' . $e->getMessage();
        } finally {
            $this->disconnect($socket);
        }

        return $smtpResult;
    }

    private function connect(string $host, SMTPResult $smtpResult)
    {
        $socket = @fsockopen($host, 25, $errNo, $errStr, self::TIMEOUT);
        if (!$socket) {
            $smtpResult->error = "Could not connect to $host: $errStr ($errNo)";

            return null;
        }

        stream_set_timeout($socket, self::TIMEOUT);
        $this->readResponse($socket);

        $this->sendCommand($socket, 'EHLO ' . $this->heloHost);
        $this->readResponse($socket);
        
        $this->sendCommand($socket, 'MAIL FROM: <' . $this->fromEmail . '>');
        $this->readResponse($socket);

        return $socket;
    }

    private function verifyEmail(SMTPResult $smtpResult, $socket, string $email, bool $setMailFrom = true): void
    {
        if ($setMailFrom) {
            $this->sendCommand($socket, 'MAIL FROM: <' . $this->fromEmail  . '>');
            $this->readResponse($socket);
        }

        $this->sendCommand($socket, 'RCPT TO: <' . $email . '>');
        $response = $this->readResponse($socket);
        
        $code = (int)substr($response, 0, 3);
        if ($code >= 200 && $code < 300) {
            $smtpResult->isDeliverable = true;
        } else {
             if (stripos($response, 'full') !== false || stripos($response, 'quota') !== false) {
                 $smtpResult->hasFullInbox = true;
             }
             if (stripos($response, 'disabled') !== false || stripos($response, 'inactive') !== false) {
                 $smtpResult->isDisabled = true;
             }

             $smtpResult->error = $response;
        }
    }

    private function disconnect($socket): void
    {
        if ($socket) {
            $this->sendCommand($socket, 'QUIT');
            fclose($socket);
        }
    }

    private function sendCommand($socket, string $command): void
    {
        fwrite($socket, $command . "\r\n");
    }

    private function readResponse($socket): string
    {
        $response = '';
        while ($line = fgets($socket)) {
            $response .= $line;
            if ($line[3] === ' ') {
                break;
            }
        }
        return $response;
    }
}