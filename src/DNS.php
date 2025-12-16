<?php

namespace Shyim\CheckIfEmailExists;

class DNS
{
    public function getMxRecords(string $domain): array
    {
        $hosts = [];
        $weights = [];
        if (getmxrr($domain, $hosts, $weights)) {
            $records = array_combine($hosts, $weights);
            asort($records); // Sort by weight (priority)
            return array_keys($records);
        }
        return [];
    }
}
