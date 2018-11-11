<?php

namespace Swoft\Admin\Form;

use Swoft\Admin\Widgets\Form;

class MultipleForm extends Form
{
    /**
     * @var Builder
     */
    protected $builder;

    public function __construct(Builder $builder)
    {
        $this->builder = $builder;

        $this->initFormAttributes();
    }

    /**
     * Add a form field to form.
     *
     * @param Field $field
     * @return $this
     */
    protected function pushField(Field &$field)
    {
        array_push($this->fields, $field);

        \Swoft\Admin\Form::collectField($field);
        $this->builder->addMultipleFormField($field);

        if ($this->style) {
            $field->style($this->style);
        }

        return $this;
    }
}
