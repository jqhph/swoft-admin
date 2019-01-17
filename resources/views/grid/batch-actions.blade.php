<div class="btn-group default">
    <button type="button" class="btn btn-default btn-sm dropdown-toggle" data-toggle="dropdown">
        &nbsp;<span class="hidden-xs">{{ t('Action', 'admin') }}</span>
        <span class="caret"></span>
        <span class="sr-only"></span>
    </button>
    <ul class="dropdown-menu" role="menu">
        @foreach($actions as $action)
            <li><a href="#" class="{{ $action->getElementClass(false) }}">{{ $action->getTitle() }}</a></li>
        @endforeach
    </ul>
</div>