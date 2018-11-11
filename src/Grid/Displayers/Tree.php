<?php

namespace Swoft\Admin\Grid\Displayers;

use Swoft\Admin\Grid\Tree as GridTree;

class Tree extends AbstractDisplayer
{
    /**
     * @var int
     */
    protected $hierarchy = 1;

    public function display()
    {
        if ($children = $this->row->get(GridTree::getChildrenColumn())) {
            $this->grid->buildTree(
                $this->hierarchy,
                $children
            );
        }

        return $this->value;
    }

    /**
     * @param int $num
     * @return $this
     */
    public function setHierarchy(int $num)
    {
        $this->hierarchy = $num;
        return $this;
    }
}
