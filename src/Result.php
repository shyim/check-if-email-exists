<?php

namespace Shyim\CheckIfEmailExists;

readonly class Result
{
    public function __construct(
        public string $input,
        public bool $isSyntaxValid,
        public bool $hasMxRecords,
        public bool $isReachable,
        public bool $isCatchAll,
        public bool $isDisposable,
        public bool $isB2C,
        public bool $isRoleAccount,
        public bool $isDisabled,
        public bool $hasFullInbox,
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
