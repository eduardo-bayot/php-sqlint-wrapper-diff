<?php

namespace PhpSqlintWrapperDiff\Validator;

interface ValidatorInterface
{
    /**
     * @return mixed
     * @throws \PhpSqlintWrapperDiff\Validator\Exception\ValidatorException
     */
    public function validate();
}
