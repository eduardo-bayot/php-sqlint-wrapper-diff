<?php

namespace PhpSqlintWrapperDiff\Filter;

use PhpSqlintWrapperDiff\Filter\Exception\FilterException;
use PhpSqlintWrapperDiff\Filter\Exception\InvalidRuleException;
use PhpSqlintWrapperDiff\Filter\Rule\Exception\RuleException;
use PhpSqlintWrapperDiff\Filter\Rule\RuleInterface;
use PhpSqlintWrapperDiff\Validator\Exception\ValidatorException;
use PhpSqlintWrapperDiff\Validator\RuleValidator;

class Filter
{
    /**
     * @var RuleInterface[]
     */
    protected $rules;

    /**
     * @var array
     */
    protected $unfilteredData;

    /**
     * @var array
     */
    protected $filteredData = [];

    /**
     * @var array
     */
    protected $contaminatedData = [];

    /**
     * @param array $rules
     * @param array $unfilteredData
     * @throws InvalidRuleException
     */
    public function __construct(array $rules, array $unfilteredData)
    {
        try {
            (new RuleValidator($rules))->validate();
        } catch (ValidatorException $exception) {
            throw new InvalidRuleException('', 0, $exception);
        }

        $this->rules = $rules;
        $this->unfilteredData = $unfilteredData;
    }

    /**
     * @return $this
     */
    public function filter()
    {
        foreach ($this->unfilteredData as $key => $item) {
            foreach ($this->rules as $rule) {
                try {
                    $rule($item);
                    $this->filteredData[$key] = $item;
                } catch (RuleException $exception) {
                    $this->contaminatedData[$key] = $item;
                }
            }
        }

        return $this;
    }

    /**
     * @return array
     */
    public function getFilteredData()
    {
        return $this->filteredData;
    }

    /**
     * @return array
     */
    public function getContaminatedData()
    {
        return $this->contaminatedData;
    }
}
