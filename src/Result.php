<?php

namespace Shyim\CheckIfEmailExists;

class Result
{
    public function __construct(
        public string $input,
        public bool $isSyntaxValid = false,
        public bool $hasMxRecords = false,
        public bool $isReachable = false,
        public bool $isCatchAll = false,
        public bool $isDisposable = false,
        public bool $isB2C = false,
        public bool $isRoleAccount = false,
        public bool $isDisabled = false,
        public bool $hasFullInbox = false,
        public string $mxHost = '',
        public string $error = ''
    ) {}

    public function toArray(): array
    {
        return [
            'input' => $this->input,
            'is_syntax_valid' => $this->isSyntaxValid,
            'has_mx_records' => $this->hasMxRecords,
            'is_reachable' => $this->isReachable,
            'is_catch_all' => $this->isCatchAll,
            'is_disposable' => $this->isDisposable,
            'is_b2c' => $this->isB2C,
            'is_role_account' => $this->isRoleAccount,
            'is_disabled' => $this->isDisabled,
            'has_full_inbox' => $this->hasFullInbox,
            'mx_host' => $this->mxHost,
            'error' => $this->error,
        ];
    }
}
