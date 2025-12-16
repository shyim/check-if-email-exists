<?php

namespace Shyim\CheckIfEmailExists;

class Result
{
    public function __construct(
        public readonly string $input,
        public readonly bool $isSyntaxValid,
        public readonly bool $hasMxRecords,
        public readonly bool $isReachable,
        public readonly bool $isCatchAll,
        public readonly bool $isDisposable,
        public readonly bool $isRoleAccount,
        public readonly bool $isDisabled,
        public readonly bool $hasFullInbox,
        public readonly string $mxHost = '',
        public readonly string $error = ''
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
            'is_role_account' => $this->isRoleAccount,
            'is_disabled' => $this->isDisabled,
            'has_full_inbox' => $this->hasFullInbox,
            'mx_host' => $this->mxHost,
            'error' => $this->error,
        ];
    }
}
