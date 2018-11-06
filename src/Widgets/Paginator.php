<?php

namespace Swoft\Admin\Widgets;

use Swoft\Support\Collection;
use Swoft\Support\Contracts\Renderable;
use Swoft\Support\Input;
use Swoft\Support\Url;

class Paginator implements Renderable
{
    /**
     * @var Collection
     */
    protected $data;

    /**
     * 列表每页显示行数
     *
     * @var int
     */
    protected $perPage;

    /**
     * 页数跳转时要带的参数
     *
     * @var string
     */
    protected $parameter;

    /**
     * 分页总页面数
     *
     * @var int
     */
    protected $totalPages;

    /**
     * 总行数
     *
     * @var int
     */
    protected $total;

    /**
     * 当前页数
     *
     * @var int
     */
    protected $currentPage = 1;

    /**
     * 分页的栏的总页数
     *
     * @var int
     */
    protected $coolPages;

    /**
     * 分页栏按钮显示数量
     *
     * @var int
     */
    protected $pageButtonNumber;

    /**
     * 分页显示定制
     *
     * @var array
     */
    protected $config;

    /**
     * 分页跳转url
     *
     * @var string
     */
    protected $url;

    /**
     * 分页栏每页显示的页数的中间数
     *
     * @var int
     */
    protected $centerNum;

    /**
     * 生成分页字符串
     *
     * @param int $total 总行数
     * @param int $perPage 列表每页显示的行数
     * @param int $rollNum 分页栏每页显示的页数
     * @param string|array $parameter url参数
     */
    public function __construct(int $total, int $perPage = 10, int $pageButtonNumber = 8, $parameter = '')
    {
        $this->config = [
            'totalText' => '',
            'prev' => '<',
            'next' => '>',
            'first' => '<<',
            'last' => '>>',
            'ele' => 'a',
            'itemEle' => '<li class="paginate_button">',
            'activeItemEle' => '<li class="paginate_button active">',
            'itemEleEnd' => '</li>',
            'class' => '',
            'currentClass' => 'active',
            'pagekey' => '_p',
            'theme' => '%header%%pageinfo%%first%%upPage%%linkPage%%downPage%%end%'
        ];

        $this->total = $total;
        $this->parameter = $parameter;
        $this->pageButtonNumber = $pageButtonNumber;
        $this->perPage = $perPage;
    }

    /**
     * 当前页数据
     *
     * @param array $data
     * @return $this
     */
    public function setData(array $data)
    {
        $this->data = new Collection($data);
    }

    /**
     * 当前页数据
     * 
     * @return Collection
     */
    public function getCollection()
    {
        return $this->data ?? new Collection();
    }

    /**
     * 每页显示数量
     * 
     * @return int
     */
    public function getPerPage()
    {
        return $this->perPage;
    }

    /**
     * @param string $key
     * @return $this
     */
    public function setPageName(string $key)
    {
        return $this->set('pagekey', $key);
    }

    /**
     * @return string
     */
    public function getPageName()
    {
        return $this->config['pagekey'];
    }

    /**
     * @return int
     */
    public function lastPage()
    {
        return ceil($this->total / $this->perPage);
    }

    /**
     * @return int
     */
    public function currentPage()
    {
        return (int)Input::get($this->config['pagekey'], 1);
    }

    /**
     * @return int
     */
    public function total()
    {
        return $this->total;
    }

    /**
     * 生成分页字符串
     *
     * @return string
     */
    public function render()
    {
        $this->totalPages = $this->lastPage();     //总页数
        $this->currentPage = $this->currentPage();
        $this->coolPages = ceil($this->totalPages / $this->pageButtonNumber);

        if ($this->currentPage > $this->totalPages) {
            $this->currentPage = $this->totalPages;
        }

        if ($this->currentPage < 1) {
            $this->currentPage = 1;
        }

        // 计算分页栏每页显示的页数的中间数
        $temp = $this->pageButtonNumber % 2;

        if ($temp == 0) {
            $this->centerNum = $this->pageButtonNumber / 2;
        } else {
            $this->centerNum = ($this->pageButtonNumber - 1) / 2 + 1;
        }

        return $this->show();
    }

    /**
     * 设置配置参数
     *
     * @param string $name
     * @param mixed $value
     * @return $this
     */
    public function set($name, $value)
    {
        if (isset($this->config[$name])) {
            $this->config[$name] = &$value;
        }
        return $this;
    }

    /**
     * 获取url
     *
     * @return string
     */
    public function url()
    {
        if ($this->url) {
            return $this->url;
        }

        $params = &$this->parameter;
        if ($this->parameter && is_string($this->parameter)) {
            $params = parse_str($this->parameter);
        }

        $query = Url::query();
        if (is_array($params) && !empty($params)) {
            $query->add($params);
        }

        $query->delete($this->config['pagekey']);
        $query->delete('_pjax');

        $url = $query->build();

        return $this->url = strpos($url, '?') === false ? ($url.'?') : $url;
    }

    /**
     * @return string
     */
    protected function show()
    {
        if (0 == $this->total) return '';

        return str_replace(
            [
                '%header%',
                '%pageinfo%',
                '%first%',
                '%upPage%',
                '%linkPage%',
                '%downPage%',
                '%end%'
            ],
            [
                $this->header(),
                $this->pageinfo(),
                $this->first(),
                $this->prev(),
                $this->pageList(),
                $this->next(),
                $this->last()
            ],
            $this->config['theme']
        );
    }

    /**
     * @return string
     */
    protected function header()
    {
        return "{$this->config['itemEle']}<span class='{$this->config['class']}'> &nbsp;{$this->config['totalText']} {$this->total}";
    }

    protected function pageinfo()
    {
        return "&nbsp;&nbsp;&nbsp; {$this->currentPage}/{$this->totalPages} &nbsp;</span>{$this->config['itemEleEnd']}";
    }

    /**
     * 1 2 3 4 5
     *
     * @return string
     */
    protected function pageList()
    {
        $p = $this->config['pagekey'];

        $url = $this->url();

        $linkPage = '';

        for ($i = 1; $i <= $this->pageButtonNumber; $i++) {
            $_ = $this->currentPage - $this->centerNum;

            if ($_ <= 0) {
                $_ = 0;
            } elseif ($this->currentPage + $this->centerNum >= $this->totalPages) {
                $_ = $this->totalPages - $this->pageButtonNumber;
                $_ = $_ < 0 ? 0 : $_;
            }
            $page = $_ + $i;

            if ($page != $this->currentPage) {
                if ($page <= $this->totalPages) {
                    $linkPage .= "{$this->config['itemEle']}<{$this->config['ele']} class='{$this->config['class']}' 
                    href='$url&$p=$page' data-page='$page'> $page </{$this->config['ele']}>{$this->config['itemEleEnd']}";
                } else {
                    break;
                }
            } else {
                if ($this->totalPages != 1) {
                    $linkPage .=
                        "{$this->config['activeItemEle']}<span class='{$this->config['class']}'>$page</span>{$this->config['itemEleEnd']}";
                }
            }
        }

        return $linkPage;
    }

    /**
     * @return string
     */
    protected function prev()
    {
        $p = $this->config['pagekey'];

        $url = $this->url();

        //上下翻页字符串
        $upRow = $this->currentPage - 1;

        if ($upRow > 0) {
            $upPage = "{$this->config['itemEle']}<{$this->config['ele']} class='{$this->config['class']}' 
                    data-page='$upRow' href='$url&$p=$upRow'>{$this->config['prev']}</{$this->config['ele']}>{$this->config['itemEleEnd']}";
        } else {
            $upPage = '';
        }
        return $upPage;
    }

    /**
     * @return string
     */
    protected function next()
    {
        $p = $this->config['pagekey'];

        $url = $this->url();

        //上下翻页字符串
        $downRow = $this->currentPage + 1;
        if ($downRow <= $this->totalPages) {
            $downPage = "{$this->config['itemEle']}<{$this->config['ele']} class='{$this->config['class']}'  data-page='$downRow' href='$url&$p=$downRow'>{$this->config['next']}</{$this->config['ele']}>{$this->config['itemEleEnd']}";
        } else {
            $downPage = '';
        }
        return $downPage;
    }

    /**
     * @return string
     */
    protected function first()
    {
        $p = $this->config['pagekey'];

        $url = $this->url();

        // << < > >>
        if ($this->currentPage - $this->centerNum <= 0) {
            $theFirst = '';
        } else {
            $theFirst = "{$this->config['itemEle']}<{$this->config['ele']} class='{$this->config['class']}'  data-page='1' href='$url&$p=1' > {$this->config['first']}</{$this->config['ele']}>{$this->config['itemEleEnd']}";
        }
        return $theFirst;
    }

    /**
     * @return string
     */
    protected function last()
    {
        $p = $this->config['pagekey'];

        $url = $this->url();

        if ($this->centerNum + $this->currentPage >= $this->totalPages) {
            $theEnd = '';
        } else {
            $theEnd = "{$this->config['itemEle']}<{$this->config['ele']} class='{$this->config['class']}'  data-page='{$this->totalPages}' href='$url&$p={$this->totalPages}' >
            {$this->config['last']}</{$this->config['ele']}>{$this->config['itemEle']}";
        }
        return $theEnd;
    }

}
