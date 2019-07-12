<?php

namespace PhpSqlintWrapperDiff\Validator;

abstract class AbstractValidator
{
    /**
     * @var mixed
     */
    protected $data;

    /**
     * @param mixed $data
     */
    public function __construct($data)
    {
        $this->data = $data;
    }
}
