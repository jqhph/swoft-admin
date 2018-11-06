<?php

namespace Swoft\Admin\Form\Field;

use Swoft\Http\Message\Upload\UploadedFile;

class MultipleImage extends MultipleFile
{
    use ImageField;

    /**
     * {@inheritdoc}
     */
    protected $view = 'admin::form.multiplefile';

    /**
     *  Validation rules.
     *
     * @var string
     */
    protected $rules = 'image';

    /**
     * @var string
     */
    protected $disk = 'public';

    /**
     * Prepare for each file.
     *
     * @param UploadedFile $image
     *
     * @return mixed|string
     */
    protected function prepareForeach(UploadedFile $image = null)
    {
        $this->name = $this->getStoreName($image);

        $this->callInterventionMethods($image);

        return tap($this->upload($image), function () {
            $this->name = null;
        });
    }

    public function render()
    {
        $this->setPreviewType('image');

        return parent::render();
    }
}
