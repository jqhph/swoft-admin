<?php

namespace Swoft\Admin\Form\Field;

/**
 * Class ListBox.
 *
 * @see https://github.com/istvan-ujjmeszaros/bootstrap-duallistbox
 */
class Listbox extends MultipleSelect
{
    protected $settings = [];

    protected static $css = [
        '@admin/bootstrap-duallistbox/dist/bootstrap-duallistbox.min.css',
    ];

    protected static $js = [
        '@admin/bootstrap-duallistbox/dist/jquery.bootstrap-duallistbox.min.js',
    ];

    public function settings(array $settings)
    {
        $this->settings = $settings;

        return $this;
    }

    public function render()
    {
        $settings = array_merge($this->settings, [
            'infoText'          => t('Showing all {0}', 'admin.listbox'),
            'infoTextEmpty'     => t('Empty list', 'admin.listbox'),
            'infoTextFiltered'  => t('{0} / {1}', 'admin.listbox'),
            'filterTextClear'   => t('Show all', 'admin.listbox'),
            'filterPlaceHolder' => t('Filter', 'admin.listbox'),
        ]);

        $settings = json_encode($settings);

        $this->script = "$(\"{$this->getElementClassSelector()}\").bootstrapDualListbox($settings);";

        return parent::render();
    }

}
