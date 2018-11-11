<?php

namespace Swoft\Admin\Grid\Tools;

use Swoft\Admin\Admin;

class BatchDelete extends BatchAction
{
    public function __construct($title)
    {
        $this->title = $title;
    }

    /**
     * Script of batch delete action.
     */
    public function script()
    {
        $deleteConfirm = t('Are you sure to delete this item?', 'admin');
        $confirm = t('Confirm', 'admin');
        $cancel = t('Cancel', 'admin');

        $url = Admin::url()->delete();

        return <<<EOF
$('{$this->getElementClass()}').on('click', function() {      
 var id = {$this->grid->getSelectedRowsName()}().join(), url = '$url';
 if (!id) return;  
    LA.confirm('$deleteConfirm', function () {
        $.ajax({
            method: 'delete',
            url: url.replace(':id', id),
            data: {
                _method:'delete',
                _token:'{$this->getToken()}'
            },
            success: function (data) {
                $.pjax.reload('#pjax-container');

                if (typeof data === 'object') {
                     if (data.status) {
                        LA.success(data.message);
                    } else {
                        LA.error(data.message);
                    }
                }
            }
        });
    }, '$confirm','$cancel');
});
EOF;

    }
}
