<x-mail::message>
# Merhaba

Geçtiğimiz toplantının özetini aşağıda bulabilirsiniz:

## {{ $meeting->ordered_title }}

**Tarih:** {{ $meeting->date->format('d/m/Y') }}
**Yer:** {{ $meeting->location }}

@if($meeting->meetable instanceof \App\Models\Book)
**Kitap/Yazar:** {{ $meeting->meetable->name }} / {{ $meeting->meetable->writer->name }}
@elseif($meeting->meetable instanceof \App\Models\Writer)
**Yazar:** {{ $meeting->meetable->name }}
@endif

### Katılımcılar
@foreach($meeting->users as $user)
1. {{ $user->name }}
@endforeach

@if($meeting->has('abstainedUsers'))
@foreach($meeting->abstainedUsers as $user)
{{ $user->name . "({$user->pivot->reason_for_not_participating})" }}
@endforeach
@endif

@if($meeting->has('presentations'))
### Sunumlar
@foreach($meeting->presentations as $presentation)
- {{ $presentation->citation }}
@endforeach
@endif

### Toplantı Konuları
{!! $meeting->topics ?? 'Konu yok.' !!}

### Kararlar
{!! $meeting->decisions ?? 'Bir karar alınmadı.'!!}

Keyifli okumalar,
</x-mail::message>
