@if($getState())
    <div class="progress-bar">
        <div class="progress-bar-value" style="width: {{ $getState() }}%;">
            %{{ $getState()}}
        </div>
    </div>
@else
    <div class="text-gray-400 text-sm">
        Henüz kitap eklenmemiş.
    </div>
@endif
