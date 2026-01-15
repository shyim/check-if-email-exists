<?php

namespace Shyim\CheckIfEmailExists;

readonly class EmailChecker
{
    public function __construct(
        private DNS $dns = new DNS(),
        private SMTP $smtp = new SMTP(),
        private Misc $misc = new Misc()
    ) {}

    public function check(string $email): Result
    {
        $result = new Result(input: $email);

        $syntax = new Syntax($email);
        $result->isSyntaxValid = $syntax->isValid();

        if ($result->isSyntaxValid === false) {
            $result->error = 'Invalid syntax';

            return $result;
        }

        $mxRecords = $this->dns->getMxRecords($syntax->domain);
        $result->hasMxRecords = !empty($mxRecords);

        if ($result->hasMxRecords === false) {
            $result->error = 'No MX records found';

            return $result;
        }

        $mxHost = $mxRecords[0];
        $smtpDetails = $this->smtp->check($syntax->domain, $mxHost, $email);

        $result->mxHost = $mxHost;
        $result->isRoleAccount = $this->misc->isRoleAccount($syntax->username);
        $result->isDisposable = $this->misc->isDisposable($email);
        $result->isB2C = $this->misc->isB2C($syntax->domain);
        $result->isReachable = $smtpDetails->canConnect && $smtpDetails->isDeliverable;
        $result->isCatchAll = $smtpDetails->isCatchAll;
        $result->isDisabled = $smtpDetails->isDisabled;
        $result->hasFullInbox = $smtpDetails->hasFullInbox;
        $result->error = $smtpDetails->error;

        return $result;
    }
}
