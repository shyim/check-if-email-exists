<?php

namespace Shyim\CheckIfEmailExists;

class SMTPResult
{
    public function __construct(
        public bool $canConnect = false,
        public bool $isDeliverable = false,
        public bool $isCatchAll = false,
        public bool $hasFullInbox = false,
        public bool $isDisabled = false,
        public string $mxHost = '',
        public string $error = ''
    ) {}

    public function addError(string $error): void
    {
        $this->error = \implode('; ', array_filter([$this->error, $error]));
    }
}