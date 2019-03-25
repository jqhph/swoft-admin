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
    private $repository = '*';

    public function __construct(array $values)
    {
        if (!empty($values['value'])) {
            $this->repository = $values['value'];
        }
        if (!empty($values['repository'])) {
            $this->repository = $values['repository'];
        }
    }

    /**
     * @return string
     */
    public function getValue()
    {
        return $this->repository;
    }

}