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
        $syntax = new Syntax($email);
        if (!$syntax->isValid()) {
            return new Result(
                input: $email,
                isSyntaxValid: false,
                hasMxRecords: false,
                isReachable: false,
                isCatchAll: false,
                isDisposable: false,
                isB2C: false,
                isRoleAccount: false,
                isDisabled: false,
                hasFullInbox: false,
                error: 'Invalid syntax'
            );
        }

        $miscIsRole = $this->misc->isRoleAccount($syntax->username);
        $miscIsDisposable = $this->misc->isDisposable($email);
        $miscIsB2C = $this->misc->isB2C($syntax->domain);

        $mxRecords = $this->dns->getMxRecords($syntax->domain);
        if (empty($mxRecords)) {
            return new Result(
                input: $email,
                isSyntaxValid: true,
                hasMxRecords: false,
                isReachable: false,
                isCatchAll: false,
                isDisposable: $miscIsDisposable,
                isB2C: $miscIsB2C,
                isRoleAccount: $miscIsRole,
                isDisabled: false,
                hasFullInbox: false,
                error: 'No MX records found'
            );
        }

        $mxHost = $mxRecords[0];

        $smtpDetails = $this->smtp->check($syntax->domain, $mxHost, $email);

        $isReachable = $smtpDetails['can_connect'] && $smtpDetails['is_deliverable'];

        return new Result(
            input: $email,
            isSyntaxValid: true,
            hasMxRecords: true,
            isReachable: $isReachable,
            isCatchAll: $smtpDetails['is_catch_all'],
            isDisposable: $miscIsDisposable,
            isB2C: $miscIsB2C,
            isRoleAccount: $miscIsRole,
            isDisabled: $smtpDetails['is_disabled'],
            hasFullInbox: $smtpDetails['has_full_inbox'],
            mxHost: $mxHost,
            error: $smtpDetails['error']
        );
    }
}
