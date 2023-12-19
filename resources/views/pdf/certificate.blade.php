<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Document</title>
    <style>
        @page { margin: 0px; }
        body { margin: 0px; }
        .ImageContainer {
            position: absolute;
            top: 0px;left: 0px;right: 0px;bottom: 0px;
            z-index: 2;
            text-align: center;
        }
        .image {
            width: 100%;
        }
        .NameArea {
            position: absolute;
            left: 0px;right: 0px;bottom: 0px;
            text-align: center;
            z-index: 3;
        }
    </style>
</head>
<body>
    @php
        $certificate = $enroll->course->certificate;
        $fontProps = json_decode($certificate->font_properties, false);
    @endphp
    
    <div class="ImageContainer">
        <img class="image" src="data:image/png;base64,{{ base64_encode(file_get_contents(public_path('storage/certificate_templates/' . $certificate->filename))) }}" alt="Sertif">
    </div>

    <div class="NameArea" style="font-size: {{ $fontProps->fontSize }}px;top: {{ $certificate->position }}%;font-weight: {{ $fontProps->fontWeight }}">
        {{ $enroll->user->name }}
    </div>
    <div>
        {{ public_path('storage/certificate_templates/' . $enroll->course->certificate->filename) }}
    </div>

</body>
</html>