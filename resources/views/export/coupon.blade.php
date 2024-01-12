@php
    use Carbon\Carbon;
@endphp
<table>
    <thead>
        <tr>
            <th style="font-weight: bold;">Kode</th>
            <th style="font-weight: bold;">Kursus</th>
            <th style="font-weight: bold;">Dibuat Pada</th>
        </tr>
    </thead>
    <tbody>
        @foreach ($coupons as $data)
            <tr>
                <td>{{ $data->code }}</td>
                <td>
                    @if ($data->for_courses_id == "[]")
                        -
                    @else
                        @foreach ($data->courses as $c => $course)
                            <a href="https://kelaspersonalia.com/course/{{ $course->id }}">
                                {{ $course->title }}
                            </a>
                            @if ($c != count($data->courses) - 1)
                                , &nbsp;
                            @endif
                        @endforeach
                    @endif
                </td>
                <td>{{ Carbon::parse($data->created_at)->format('d M Y, H:i') }}</td>
            </tr>
        @endforeach
    </tbody>
</table>