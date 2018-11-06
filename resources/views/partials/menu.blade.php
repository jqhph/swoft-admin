@if($item)
    @php
        $titleName = Swoft\Admin\Auth\Database\Menu::getTitleColumn();
        $iconName = Swoft\Admin\Auth\Database\Menu::getIconColumn();
        $pathColumn = Swoft\Admin\Auth\Database\Menu::getPathColumn();
        $childrenColumn = Swoft\Admin\Auth\Database\Menu::getChildrenColumn();
    @endphp
    @if(!isset($item[$childrenColumn]))
        <li>
            @if(is_valid_url($item[$pathColumn]))
                <a href="{{ $item[$pathColumn] }}"  target="_blank" >
            @elseif(array_get($item, 'newpage'))
                <a href="{{ empty($item['useprefix']) ? $item[$pathColumn] : admin_base_path($item[$pathColumn]) }}"  target="_blank">
            @elseif($item[$pathColumn] && !empty($item['useprefix']))
                <a href="{{ admin_base_path($item[$pathColumn]) }}">
            @elseif($item[$pathColumn])
                <a href="{{ $item[$pathColumn] }}">
            @else
                <a>
            @endif
                <i class="fa {{$item[$iconName]}}"></i>
                <span>{{ t($item[$titleName], 'admin.menus') }}</span>
            </a>

        </li>
    @else
        <li class="treeview">
            <a href="#">
                <i class="fa {{ $item[$iconName] }}"></i>
                <span>{{ t($item[$titleName], 'admin.menus') }}</span>
                <i class="fa fa-angle-left pull-right"></i>
            </a>
            <ul class="treeview-menu">
                @foreach($item[$childrenColumn] as &$item)
                    @include('admin::partials.menu', $item)
                @endforeach
            </ul>
        </li>
    @endif
@endif