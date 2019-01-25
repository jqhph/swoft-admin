<?php

namespace Swoft\Admin\Grid\Filter;

class NotEqual extends AbstractFilter
{
    /**
     * {@inheritdoc}
     */
    public function condition($inputs)
    {
        $value = array_get($inputs, $this->column);

        if ($this->isIgnoreValue($value)) {
            return;
        }

        $this->value = $value;

        return $this->buildCondition($this->column, $this->value, '!=');
    }
}
