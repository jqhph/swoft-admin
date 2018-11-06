<?php

namespace Swoft\Admin\Bean\Annotation;

/**
 * The annotation of form
 *
 * @Annotation
 * @Target({"CLASS"})
 */
class AdminForm
{
    /**
     * @var string
     */
    private $name = '';

    public function __construct(array $values)
    {
        if (!empty($values['value'])) {
            $this->name = $values['value'];
        }
        if (!empty($values['name'])) {
            $this->name = $values['name'];
        }
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

}