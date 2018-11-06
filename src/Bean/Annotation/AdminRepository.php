<?php

namespace Swoft\Admin\Bean\Annotation;

/**
 * The annotation of Repository
 *
 * @Annotation
 * @Target({"CLASS"})
 */
class AdminRepository
{
    /**
     * @var string
     */
    private $controller = '';

    public function __construct(array $values)
    {
        if (!empty($values['value'])) {
            $this->controller = $values['value'];
        }
        if (!empty($values['controller'])) {
            $this->controller = $values['controller'];
        }
    }

    /**
     * @return string
     */
    public function getValue()
    {
        return $this->controller;
    }

}