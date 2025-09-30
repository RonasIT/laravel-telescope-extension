<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>{{ config('app.name') }} Telescope Report</title>
</head>
<body style="font-family: Arial, sans-serif; color: #333;">
<h2>{{ config('app.name') }} Telescope collected entries</h2>

@php
    $emojiMap = [
        'cache' => '📦',
        'client-requests'=> '📡',
        'requests' => '🌐',
        'commands' => '⌨️',
        'queries' => '📊',
        'mail' => '✉️',
        'views' => '🖥️',
        'redis' => '⚡',
        'exceptions' => '⚠️',
        'notifications' => '🔔',
        'jobs' => '💥',
        'schedule' => '🕒',
        'batches' => '🗂️',
        'logs' => '📑',
        'gates' => '🚪',
        'events' => '🎫',
        'models' => '🤖',
        'dumps' => '📝',
    ];
@endphp
<ul>
    @foreach ($entries as $type => $count)
        @if ($count > 0)
            <li>
                <a href="{{ $telescopeBaseUrl }}/{{ $type }}" style="color: #1a73e8; text-decoration: none;">
                    {!! $emojiMap[$type] !!}&nbsp;
                    {{ ucfirst($type) }} ({{ $count }})
                </a>
            </li>
        @endif
    @endforeach
</ul>

<p>More details in <a href="{{ $telescopeBaseUrl }}">Telescope</a>.</p>
</body>
</html>