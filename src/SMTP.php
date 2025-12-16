<?php

namespace Shyim\CheckIfEmailExists;

class SMTP
{
    private const TIMEOUT = 10;
    private const FROM_EMAIL = 'verify@example.com';
    private const HELO_HOST = 'example.com';

    public function check(string $domain, string $mxHost, string $toEmail): array
    {
        $details = [
            'can_connect' => false,
            'is_deliverable' => false,
            'is_catch_all' => false,
            'has_full_inbox' => false,
            'is_disabled' => false,
            'error' => '',
        ];

        $socket = $this->connect($mxHost);
        if (!$socket) {
            $details['error'] = "Could not connect to $mxHost";
            return $details;
        }

        $details['can_connect'] = true;

        try {
            $randomEmail = bin2hex(random_bytes(8)) . '@' . $domain;
            $catchAllResult = $this->verifyEmail($socket, $randomEmail);

            if ($catchAllResult['is_deliverable']) {
                $details['is_catch_all'] = true;
                $details['is_deliverable'] = true;
            } else {
                 $this->sendCommand($socket, 'RSET');
                 $this->readResponse($socket);

                 $this->sendCommand($socket, 'MAIL FROM: <' . self::FROM_EMAIL . '>');
                 $this->readResponse($socket);

                 $emailResult = $this->verifyEmail($socket, $toEmail, false);

                 $details['is_deliverable'] = $emailResult['is_deliverable'];
                 $details['has_full_inbox'] = $emailResult['has_full_inbox'];
                 $details['is_disabled'] = $emailResult['is_disabled'];
                 $details['error'] = $emailResult['error'];
            }

        } catch (	hrowable $e) {
            $details['error'] = $e->getMessage();
        } finally {
            $this->disconnect($socket);
        }

        return $details;
    }

    private function connect(string $host)
    {
        $socket = @fsockopen($host, 25, $errno, $errstr, self::TIMEOUT);
        if (!$socket) {
            return null;
        }
        stream_set_timeout($socket, self::TIMEOUT);
        $this->readResponse($socket);

        $this->sendCommand($socket, 'EHLO ' . self::HELO_HOST);
        $this->readResponse($socket);
        
        $this->sendCommand($socket, 'MAIL FROM: <' . self::FROM_EMAIL . '>');
        $this->readResponse($socket);

        return $socket;
    }

    private function verifyEmail($socket, string $email, bool $sendMailFrom = false): array
    {
        $result = [
            'is_deliverable' => false,
            'has_full_inbox' => false,
            'is_disabled' => false,
            'error' => '',
        ];

        if ($sendMailFrom) {
             $this->sendCommand($socket, 'MAIL FROM: <' . self::FROM_EMAIL . '>');
             $this->readResponse($socket);
        }

        $this->sendCommand($socket, 'RCPT TO: <' . $email . '>');
        $response = $this->readResponse($socket);
        
        $code = (int)substr($response, 0, 3);
        if ($code >= 200 && $code < 300) {
            $result['is_deliverable'] = true;
        } else {
             if (stripos($response, 'full') !== false || stripos($response, 'quota') !== false) {
                 $result['has_full_inbox'] = true;
             }
             if (stripos($response, 'disabled') !== false || stripos($response, 'inactive') !== false) {
                 $result['is_disabled'] = true;
             }
             $result['error'] = $response;
        }
        return $result;
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
            if (substr($line, 3, 1) === ' ') {
                break;
            }
        }
        return $response;
    }
}