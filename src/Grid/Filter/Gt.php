<?php

namespace Swoft\Admin\Grid\Filter;

class Gt extends AbstractFilter
{
    public function variables()
    {
        $this->label .= ' (>)';

        return parent::variables();
    }

    /**
     * Get condition of this filter.
     *
     * @param array $inputs
     *
     * @return array|mixed|void
     */
    public function condition($inputs)
    {
        $value = array_get($inputs, $this->column);

        if ($this->isIgnoreValue($value)) {
            return;
        }

        $this->value = $value;

        return $this->buildCondition($this->column, $this->value, '>=');
    }
}
