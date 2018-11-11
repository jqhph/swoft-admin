<?php

namespace Swoft\Admin\Grid\Tools;

use Swoft\Admin\Admin;
use Swoft\Admin\Grid;
use Swoft\Core\RequestContext;
use Swoft\Support\Url;

class PerPageSelector extends AbstractTool
{
    /**
     * @var string
     */
    protected $perPage;

    /**
     * @var string
     */
    protected $perPageName = '';

    /**
     * Create a new PerPageSelector instance.
     *
     * @param Grid $grid
     */
    public function __construct(Grid $grid)
    {
        $this->grid = $grid;

        $this->initialize();
    }

    /**
     * Do initialize work.
     *
     * @return void
     */
    protected function initialize()
    {
        $this->perPageName = $this->grid->model()->getPerPageName();

        $request = RequestContext::getRequest();
        $this->perPage = (int) $request->query(
            $this->perPageName,
            $this->grid->model()->paginate()
        );
    }

    /**
     * Get options for selector.
     *
     * @return static
     */
    public function getOptions()
    {
        return collect($this->grid->perPages)
            ->push($this->grid->model()->paginate())
            ->push($this->perPage)
            ->unique()
            ->sort();
    }

    /**
     * Render PerPageSelector。
     *
     * @return string
     */
    public function render()
    {
        Admin::script($this->script());

        $options = $this->getOptions()->map(function ($option) {
            $selected = ($option == $this->perPage) ? 'selected' : '';
            $url = Url::full([$this->perPageName => $option]);

            return "<option value=\"$url\" $selected>$option</option>";
        })->implode("\r\n");

        $show = t('Show', 'admin');
        $entries = t('Entries', 'admin');

        return <<<EOT
<label class="control-label pull-right" style="margin-right: 10px; font-weight: 100;">
        <small>$show</small>&nbsp;
        <select class="input-sm form-shadow {$this->grid->getPerPageName()}" name="per-page">
            $options
        </select>
        &nbsp;<small>$entries</small>
    </label>

EOT;
    }

    /**
     * Script of PerPageSelector.
     *
     * @return string
     */
    protected function script()
    {
        return <<<EOT
$('.{$this->grid->getPerPageName()}').on("change", function(e) {
    $.pjax({url: this.value, container: '#pjax-container'});
});
EOT;
    }
}
