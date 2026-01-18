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

    public function check(string $domain, array $mxHosts, string $toEmail): SMTPResult
    {
        $smtpResult = new SMTPResult();

        $socket = $this->connect($mxHosts, $smtpResult);
        if ($socket === null) {
            return $smtpResult;
        }

        $smtpResult->canConnect = true;

        try {
            $randomEmail = hash('xxh3', $toEmail) . '-' . bin2hex(random_bytes(8)) . '@' . $domain;
            $this->verifyEmail($smtpResult, $socket, $randomEmail, false);

            if ($smtpResult->isDeliverable) {
                $smtpResult->isCatchAll = true;
            } else {
                $this->sendCommand($socket, 'RSET');
                $this->readResponse($socket);

                $this->verifyEmail($smtpResult, $socket, $toEmail);
            }
        } catch (\Throwable $e) {
            $smtpResult->addError('Exception: ' . $e->getMessage());
        } finally {
            $this->disconnect($socket);
        }

        return $smtpResult;
    }

    private function connect(array $hosts, SMTPResult $smtpResult)
    {
        $socket = null;

        foreach ($hosts as $host) {
            $socket = @fsockopen($host, 25, $errNo, $errStr, self::TIMEOUT);
            if (\is_resource($socket)) {
                $smtpResult->error = '';
                $smtpResult->mxHost = $host;

                break;
            }

            $smtpResult->addError("Could not connect to $host: $errStr ($errNo)");
        }

        if (!\is_resource($socket)) {
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
            $this->sendCommand($socket, 'MAIL FROM: <' . $this->fromEmail . '>');
            $this->readResponse($socket);
        }

        $smtpResult->error = '';

        $this->sendCommand($socket, 'RCPT TO: <' . $email . '>');
        $response = $this->readResponse($socket);

        $code = (int)substr($response, 0, 3);
        if ($code >= 200 && $code < 300) {
            $smtpResult->isDeliverable = true;

            return;
        }

        if (stripos($response, 'full') !== false || stripos($response, 'quota') !== false) {
            $smtpResult->hasFullInbox = true;
        }

        if (stripos($response, 'disabled') !== false || stripos($response, 'inactive') !== false) {
            $smtpResult->isDisabled = true;
        }

        $smtpResult->error = $response;
    }

    private function disconnect($socket): void
    {
        if (\is_resource($socket)) {
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