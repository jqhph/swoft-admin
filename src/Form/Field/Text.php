<?php

namespace Swoft\Admin\Form\Field;

use Swoft\Admin\Form\Field;

class Text extends Field
{
    use PlainInput;

    /**
     * Render this filed.
     *
     * @return string
     */
    public function render()
    {
        $this->initPlainInput();

        $this
            ->prepend('<i class="fa fa-pencil fa-fw"></i>')
            ->defaultAttribute('type', 'text')
            ->defaultAttribute('id', $this->id)
            ->defaultAttribute('name', $this->elementName ?: $this->formatName($this->column))
            ->defaultAttribute('value', old_input($this->column, $this->value()))
            ->defaultAttribute('class', 'form-control '.$this->getElementClassString())
            ->defaultAttribute('placeholder', $this->getPlaceholder());

        $this->addVariables([
            'prepend' => $this->prepend,
            'append'  => $this->append,
        ]);

        return parent::render();
    }
}
