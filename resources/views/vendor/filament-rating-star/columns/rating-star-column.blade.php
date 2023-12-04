@php
    $isDisabled = $isDisabled();
    $state = $getState();
@endphp

<div class="flex flex-row-reverse justify-center p-10">
    @if($state == 0)
        <span class="text-gray-400 text-sm">{{ __('Henüz oy verilmedi.') }}</span>
    @else
        @for ( $i=1; $i <= count(config('filament-rating-star.stars')); $i++ )
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 30 30" width="25" height="25">
                <path
                    d="M15.765,2.434l2.875,8.512l8.983,0.104c0.773,0.009,1.093,0.994,0.473,1.455l-7.207,5.364l2.677,8.576 c0.23,0.738-0.607,1.346-1.238,0.899L15,22.147l-7.329,5.196c-0.63,0.447-1.468-0.162-1.238-0.899l2.677-8.576l-7.207-5.364 c-0.62-0.461-0.3-1.446,0.473-1.455l8.983-0.104l2.875-8.512C14.482,1.701,15.518,1.701,15.765,2.434z"
                    fill="{{ ( $state <= $i ) ? '#ffc107' : '#ddd' }}"/>
            </svg>
        @endfor
    @endif
</div>
