@php
    use Carbon\Carbon;
@endphp
<table>
    <thead>
        <tr>
            <th style="font-weight: bold;">ID</th>
            <th style="font-weight: bold;">Pelatihan</th>
            <th style="font-weight: bold;">Nama Peserta</th>
            <th style="font-weight: bold;">Email Peserta</th>
            <th style="font-weight: bold;">Tanggal Bergabung</th>
        </tr>
    </thead>
    <tbody>
        @foreach ($datas as $data)
            <tr>
                <td>{{ $data->id }}</td>
                <td>{{ $data->course->title }}</td>
                <td>{{ $data->user->name }}</td>
                <td>{{ $data->user->email }}</td>
                <td>{{ Carbon::parse($data->created_at)->format('d M Y, H:i') }}</td>
            </tr>
        @endforeach
    </tbody>
</table>