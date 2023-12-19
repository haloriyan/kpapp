@php
    use Carbon\Carbon;
@endphp
<table>
    <thead>
        <tr>
            <th style="font-weight: bold;">ID</th>
            <th style="font-weight: bold;">Nama</th>
            <th style="font-weight: bold;">Email</th>
            <th style="font-weight: bold;">Tanggal Terdaftar</th>
        </tr>
    </thead>
    <tbody>
        @foreach ($datas as $data)
            <tr>
                <td>{{ $data->id }}</td>
                <td>{{ $data->name }}</td>
                <td>{{ $data->email }}</td>
                <td>{{ Carbon::parse($data->created_at)->format('d M Y, H:i') }}</td>
            </tr>
        @endforeach
    </tbody>
</table>