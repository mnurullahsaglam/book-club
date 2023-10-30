@if(! $getState() && $getState() !== 0)
    <div class="text-gray-400 text-sm">
        Henüz kitap eklenmemiş.
    </div>
@else
    <div class="progress-bar">
        <div class="progress-bar-value" style="width: {{ $getState() }}%;">
            %{{ $getState()}}
        </div>
    </div>
@endif
