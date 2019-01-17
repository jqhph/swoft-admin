<?php

namespace Swoft\Admin\Bean\Annotation;

/**
 *
 * @Annotation
 * @Target({"CLASS"})
 */
class AdminRepositoryListener
{
    /**
     * @var string
     */
    private $listener = '';

    public function __construct(array $values)
    {
        if (!empty($values['value'])) {
            $this->listener = $values['value'];
        }
        if (!empty($values['listener'])) {
            $this->listener = $values['listener'];
        }
    }

    /**
     * @return string
     */
    public function getValue()
    {
        return $this->listener;
    }

}