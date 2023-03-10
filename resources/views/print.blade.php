<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cetak Data Pengaduan</title>
</head>
<body>
    <table>
        <tr>
            <th>No</th>
            <th>Nik</th>
            <th>Nama</th>
            <th>No Telp</th>
            <th>Tanggal</th>
            <th>Pengaduan</th>
            <th>Gambar</th>
            <th>Status Response</th>
            <th>Pesan Response</th>
        </tr>
        @php $no =1; @endphp
        @foreach ($reports as $report)
        <tr>
            <td>{{$no++}}</td>
            <td>{{$report['nik']}}</td>
            <td>{{$report['nama']}}</td>
            <td>{{$report['no_telp']}}</td>
            <td>{{\Carbon\Carbon::parse($report['created_at'])->format('j, F, Y')}}</td>
            <td>{{$report ['pengaduan']}}</td>
            <td><img src="assets/image/{{$report['foto']}}" width="80"></td>
            <td>
                @if ($report['response'])
                    {{ $report['response']['status']}}
                @else
                -
                @endif
            </td>
            <td>
                @if ($report['response'])
                    {{$report['response']['pesan']}}
                @else
                -
                @endif
            </td>
        </tr>
        @endforeach
    </table>
    
</body>
</html>