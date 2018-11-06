<?php

namespace Swoft\Admin\Bootstrap;

use Swoft\Admin\Form;
use Swoft\Admin\Grid\Column;
use Swoft\Admin\Grid\Displayers;
use Swoft\Admin\Form\Field;

class Register
{
    /**
     * Register column displayers.
     *
     * @return void.
     */
    public static function registerGridColumnDisplayer()
    {
        $map = [
            'editable'    => Displayers\Editable::class,
            'switch'      => Displayers\SwitchDisplay::class,
            'image'       => Displayers\Image::class,
            'label'       => Displayers\Label::class,
            'button'      => Displayers\Button::class,
            'link'        => Displayers\Link::class,
            'badge'       => Displayers\Badge::class,
            'progressBar' => Displayers\ProgressBar::class,
            'checkbox'    => Displayers\Checkbox::class,
            'radio'       => Displayers\Radio::class,
            'table'       => Displayers\Table::class,
            'expand'      => Displayers\Expand::class,
            'tree'        => Displayers\Tree::class,
        ];

        foreach ($map as $abstract => $class) {
            Column::extend($abstract, $class);
        }
    }

    /**
     * Register builtin fields.
     *
     * @return void
     */
    public static function registerFormBuiltinFields()
    {
        $map = [
            'button'         => Field\Button::class,
            'checkbox'       => Field\Checkbox::class,
            'color'          => Field\Color::class,
            'currency'       => Field\Currency::class,
            'date'           => Field\Date::class,
            'dateRange'      => Field\DateRange::class,
            'datetime'       => Field\Datetime::class,
            'dateTimeRange'  => Field\DatetimeRange::class,
            'datetimeRange'  => Field\DatetimeRange::class,
            'decimal'        => Field\Decimal::class,
            'display'        => Field\Display::class,
            'divider'        => Field\Divide::class,
            'divide'         => Field\Divide::class,
            'embeds'         => Field\Embeds::class,
            'editor'         => Field\Editor::class,
            'email'          => Field\Email::class,
            'file'           => Field\File::class,
            'hasMany'        => Field\HasMany::class,
            'hidden'         => Field\Hidden::class,
            'id'             => Field\Id::class,
            'image'          => Field\Image::class,
            'ip'             => Field\Ip::class,
            'map'            => Field\Map::class,
            'mobile'         => Field\Mobile::class,
            'month'          => Field\Month::class,
            'multipleSelect' => Field\MultipleSelect::class,
            'number'         => Field\Number::class,
            'password'       => Field\Password::class,
            'radio'          => Field\Radio::class,
            'rate'           => Field\Rate::class,
            'select'         => Field\Select::class,
            'slider'         => Field\Slider::class,
            'switch'         => Field\SwitchField::class,
            'text'           => Field\Text::class,
            'textarea'       => Field\Textarea::class,
            'time'           => Field\Time::class,
            'timeRange'      => Field\TimeRange::class,
            'url'            => Field\Url::class,
            'year'           => Field\Year::class,
            'html'           => Field\Html::class,
            'tags'           => Field\Tags::class,
            'icon'           => Field\Icon::class,
            'multipleFile'   => Field\MultipleFile::class,
            'multipleImage'  => Field\MultipleImage::class,
            'captcha'        => Field\Captcha::class,
            'listbox'        => Field\Listbox::class,
            'tree'           => Field\Tree::class,
        ];

        foreach ($map as $abstract => $class) {
            Form::extend($abstract, $class);
        }
    }
}
