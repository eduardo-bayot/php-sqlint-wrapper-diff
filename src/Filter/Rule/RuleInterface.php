<?php

namespace PhpSqlintWrapperDiff\Filter\Rule;

use PhpSqlintWrapperDiff\Filter\Rule\Exception\RuleException;

interface RuleInterface
{
    /**
     * Throws a RuleException if the data passed into the method is not allowed based on the rule.
     *
     * @param mixed $data
     * @throws RuleException
     */
    public function __invoke($data);
}
