<?php

namespace Sloths\Validation\Rule;

class GreaterThanOrEqual extends AbstractExpectedRule
{
    /**
     * @var OneOf
     */
    protected $rule;

    /**
     * @param $expected
     */
    public function __construct($expected)
    {
        parent::__construct($expected);
        $this->rule = new OneOf([new Equals($expected), new GreaterThan($expected)]);
    }

    /**
     * @param $input
     * @return bool
     */
    public function validate($input)
    {
        return $this->rule->validate($input);
    }
}