<?php

namespace Swoft\Admin;

class AdminEvents
{
    const BEFORE_CONTENT_RENDER = 'admin.before.content.render';
    const AFTER_CONTENT_RENDER = 'admin.after.content.render';

    const BEFORE_GRID_RENDER = 'admin.before.grid.render';
    const AFTER_GRID_RENDER = 'admin.after.grid.render';

    const BEFORE_FILTER_RENDER = 'admin.before.filter.render';
    const AFTER_FILTER_RENDER = 'admin.after.filter.render';

    const BEFORE_FORM_RENDER = 'admin.before.form.render';
    const AFTER_FORM_RENDER = 'admin.after.form.render';
}
