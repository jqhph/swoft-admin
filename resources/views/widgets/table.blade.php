<table {!! $attributes !!}>
    @if($headers)
    <thead>
    <tr>
        @foreach($headers as $header)
            <th>{{ $header }}</th>
        @endforeach
    </tr>
    </thead>
    @endif
    <tbody>
    @foreach($rows as $row)
    <tr>
        @foreach($row as $item)
        <td>{!! $item !!}</td>
        @endforeach
    </tr>
    @endforeach
    </tbody>
</table>