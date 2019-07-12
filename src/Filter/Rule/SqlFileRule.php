<?php

namespace PhpSqlintWrapperDiff\Filter\Rule;

use PhpSqlintWrapperDiff\Filter\Rule\Exception\RuntimeException;

class SqlFileRule extends FileRule
{
    /**
     * @param mixed $data
     * @throws \PhpSqlintWrapperDiff\Filter\Rule\Exception\RuleException
     */
    public function __invoke($data)
    {
        parent::__invoke($data);

        if ('.sql' !== substr($data, -4)) {
            throw new RuntimeException('The file provided does not have a .sql extension.');
        }

        if (!in_array(mime_content_type($data), ['application/sql', 'text/sql', 'text/x-sql', 'text/plain'])) {
            throw new RuntimeException('The file provided does not have the application/sql, '
                .'text/sql, text/x-sql and text/plain mime type.');
        }
    }
}
