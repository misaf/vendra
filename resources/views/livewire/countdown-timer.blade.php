<div wire:poll.poll.visible>
    <span>
        @php
            $hours = intdiv($remainingTime, 3600);
            $minutes = intdiv($remainingTime % 3600, 60);
            $seconds = $remainingTime % 60;
        @endphp
        {{ sprintf('%02d:%02d:%02d', $hours, $minutes, $seconds) }}
    </span>
</div>
