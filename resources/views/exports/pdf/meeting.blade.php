<!DOCTYPE html>
<html>
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>

    <title>Your PDF Title</title>
    <style>
        * {
            font-family: "DeJaVu Sans Mono", sans-serif;
        }

        /* Define the styles for your title and date */
        .header {
            width: 100%;
            text-align: center;
            margin: 0 0 2rem 0;
        }

        .title {
            float: left;
            font-weight: bold;
        }

        .date {
            float: right;
        }

        .content {
            clear: both;
        }
    </style>
</head>
<body>
<div class="header">
    <div class="title"><h2>{{ $meeting->order . '. ' . $meeting->title }}</h2></div>
    <div class="date">{{ $meeting->date->format('d/m/Y') }}</div>
</div>
<div class="content">
    <div class="label">
        <b>Toplantı Mekânı:</b>
        {{ $meeting->location }}
    </div>
    <div class="label">
        <b>Toplantıya Katılamayanlar:</b>
        {{ implode(', ', $notParticipatedUsers->pluck('name')->toArray()) }}
    </div>
    <div class="label">
        <b>Misafirler:</b>
        {{ implode(', ', $meeting->guests) }}
    </div>

    <h3>Gündem Maddeleri</h3>
    {!! $meeting->topics ?? "Gündem maddesi yok" !!}

    <h3>Sunumlar</h3>
    @forelse($meeting->presentations as $presentation)
        <div class="label">
            <b>{{ $presentation->user->name }}</b>
            {{ $presentation->title }}
        </div>
    @empty
        Sunum yok
    @endforelse

    <h3>Kararlar</h3>
    {!! $meeting->decisions ?? "Karar alınmadı" !!}

</div>
</body>
</html>
