<?php

namespace PhpSqlintWrapperDiff\Filter\Rule;

use PhpSqlintWrapperDiff\Filter\Rule\Exception\InvalidArgumentException;
use PhpSqlintWrapperDiff\Filter\Rule\Exception\RuntimeException;

class FileRule implements RuleInterface
{
    /**
     * @param mixed $data
     * @throws \PhpSqlintWrapperDiff\Filter\Rule\Exception\RuleException
     */
    public function __invoke($data)
    {
        if (!is_string($data)) {
            throw new InvalidArgumentException('The data argument provided is not a string.');
        }

        if (!file_exists($data)) {
            throw new RuntimeException('The file provided does not exist.');
        }

        if (!is_file($data)) {
            throw new RuntimeException('The file provided is not a regular file.');
        }
    }
}
