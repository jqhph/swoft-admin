<?php

namespace Swoft\Admin\Layout;

use Closure;
use Psr\Http\Message\ResponseInterface;
use Swoft\Admin\AdminEvents;
use Swoft\App;
use Swoft\Support\Contracts\Renderable;

class Content implements Renderable
{
    /**
     * @var string
     */
    protected $view = 'admin::content';

    /**
     * Content header.
     *
     * @var string
     */
    protected $header = '';

    /**
     * Content description.
     *
     * @var string
     */
    protected $description = '';

    /**
     * Page breadcrumb.
     *
     * @var array
     */
    protected $breadcrumb = [];

    /**
     * @var Row[]
     */
    protected $rows = [];

    /**
     * @var ResponseInterface
     */
    protected $response;

    /**
     * @var array
     */
    protected $options = [
        'allowNavbarAndSidebar' => true,
    ];

    /**
     * Content constructor.
     *
     * @param Closure|null $callback
     */
    public function __construct(\Closure $callback = null)
    {
        if ($callback instanceof Closure) {
            $callback($this);
        }
    }

    /**
     * 设置模板
     *
     * @param string $view
     * @return $this
     */
    public function view(string $view)
    {
        $this->view = $view;
        return $this;
    }

    /**
     * Set header of content.
     *
     * @param string $header
     *
     * @return $this
     */
    public function header(string $header = '')
    {
        $this->header = $header;

        return $this;
    }

    /**
     * Set description of content.
     *
     * @param string $description
     *
     * @return $this
     */
    public function description(string $description = '')
    {
        $this->description = $description;

        return $this;
    }

    /**
     * 面包屑导航
     *
     * 支持一下两种形式传参:
     *     $this->breadcrumb('菜单列表', '/menu', 'fa fa-align-justify');
     * 或
     *     $this->breadcrumb([
     *         ['text' => '菜单列表', 'url' => '/menu', 'icon' => 'fa fa-align-justify']
     *     ]);
     *
     * @param array ...$breadcrumb
     * @return $this
     */
    public function breadcrumb(...$breadcrumb)
    {
        $this->formatBreadcrumb($breadcrumb);

        $this->breadcrumb = array_merge($this->breadcrumb, $breadcrumb);

        return $this;
    }

    /**
     * 禁用导航栏和菜单
     *
     * @return $this
     */
    public function simple()
    {
        $this->options['allowNavbarAndSidebar'] = false;
        return $this;
    }

    /**
     * @param array $breadcrumb
     * @return void
     * @throws \Exception
     */
    protected function formatBreadcrumb(array &$breadcrumb)
    {
        $notArray = false;
        foreach ($breadcrumb as &$item) {
            $isArray = is_array($item);
            if ($isArray && !array_has($item, 'text')) {
                throw new  \Exception('Breadcrumb format error!');
            }
            if (!$isArray && $item) {
                $notArray = true;
            }
        }
        if (!$breadcrumb) {
            throw new  \Exception('Breadcrumb format error!');
        }

        if ($notArray) {
            $breadcrumb = [
                [
                    'text' => array_get($breadcrumb, 0),
                    'url' => array_get($breadcrumb, 1),
                    'icon' => array_get($breadcrumb, 2),
                ]
            ];
        }
    }

    /**
     * Alias of method row.
     *
     * @param mixed $content
     *
     * @return Content
     */
    public function body($content)
    {
        return $this->row($content);
    }

    /**
     * Add one row for content body.
     *
     * @param $content
     *
     * @return $this
     */
    public function row($content)
    {
        if ($content instanceof ResponseInterface) {
            $this->response = $content;
            return $this;
        }
        if ($content instanceof Closure) {
            $row = new Row();
            call_user_func($content, $row);
            $this->addRow($row);
        } else {
            $this->addRow(new Row($content));
        }

        return $this;
    }

    /**
     * Add Row.
     *
     * @param Row $row
     */
    protected function addRow(Row $row)
    {
        $this->rows[] = $row;
    }

    /**
     * Build html of content.
     *
     * @return string
     */
    public function build()
    {
        $html = '';
        foreach ($this->rows as $row) {
            $html .= $row->build();
        }

        return $html;
    }

    /**
     * Set success message for content.
     *
     * @param string $title
     * @param string $message
     *
     * @return $this
     */
    public function withSuccess($title = '', $message = '')
    {
        admin_success($title, $message);

        return $this;
    }

    /**
     * Set error message for content.
     *
     * @param string $title
     * @param string $message
     *
     * @return $this
     */
    public function withError($title = '', $message = '')
    {
        admin_error($title, $message);

        return $this;
    }

    /**
     * Set warning message for content.
     *
     * @param string $title
     * @param string $message
     *
     * @return $this
     */
    public function withWarning($title = '', $message = '')
    {
        admin_warning($title, $message);

        return $this;
    }

    /**
     * Set info message for content.
     *
     * @param string $title
     * @param string $message
     *
     * @return $this
     */
    public function withInfo($title = '', $message = '')
    {
        admin_info($title, $message);

        return $this;
    }

    /**
     * Render this content.
     *
     * @return string
     */
    public function render()
    {
        App::trigger(AdminEvents::BEFORE_CONTENT_RENDER, $this);

        $items = [
            'header'      => $this->header,
            'description' => $this->description,
            'breadcrumb'  => $this->breadcrumb,
            'content'     => $this->build(),

            'allowNavbarAndSidebar' => $this->options['allowNavbarAndSidebar']
        ];

        $content = blade($this->view, $items)->render();

        App::trigger(AdminEvents::AFTER_CONTENT_RENDER, $this);

        return $content;
    }

    /**
     * @return \Psr\Http\Message\ResponseInterface
     */
    public function response()
    {
        if ($this->response) {
            return $this->response;
        }

        return html_response($this);
    }
}
