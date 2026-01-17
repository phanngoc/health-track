<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Timeline Y Tế - {{ config('app.name', 'Laravel') }}</title>
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=instrument-sans:400,500,600,700" rel="stylesheet" />
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gradient-to-br from-blue-50 via-white to-green-50 min-h-screen">
    <div class="container mx-auto px-4 py-8 max-w-6xl">
        <!-- Header -->
        <div class="mb-8">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-3xl font-bold text-gray-900 mb-2">Timeline Y Tế</h1>
                    <p class="text-gray-600">Nắm bắt tình trạng sức khỏe trong 5-10 giây</p>
                </div>
                <div class="flex gap-4">
                    <a href="{{ route('home') }}" class="px-4 py-2 text-gray-600 hover:text-gray-900">
                        ← Về trang chủ
                    </a>
                </div>
            </div>
        </div>

        <!-- Layer 1: Health Status Header -->
        <div class="bg-white rounded-xl shadow-lg p-6 mb-6 border-l-4 
            @if($insight['status']['color'] === 'red') border-red-500
            @elseif($insight['status']['color'] === 'orange') border-orange-500
            @elseif($insight['status']['color'] === 'yellow') border-yellow-500
            @else border-green-500
            @endif">
            <div class="flex items-start justify-between">
                <div class="flex-1">
                    <div class="flex items-center gap-3 mb-3">
                        <span class="text-3xl">{{ $insight['status']['badge'] }}</span>
                        <span class="text-xl font-bold text-gray-900">{{ $insight['status']['label'] }}</span>
                    </div>
                    
                    @if($insight['primary_symptom'])
                        <div class="mb-2">
                            <span class="text-gray-700 font-medium">Triệu chứng chính: </span>
                            <span class="text-gray-900 font-semibold">{{ $insight['primary_symptom']['name'] }}</span>
                            <span class="text-2xl ml-2">{{ $insightService->getTrendArrow($insight['trend']['direction']) }}</span>
                            @if($insight['trend']['direction'] === 'increasing')
                                <span class="text-orange-600 ml-1">tăng nhẹ</span>
                            @elseif($insight['trend']['direction'] === 'decreasing')
                                <span class="text-green-600 ml-1">giảm</span>
                            @else
                                <span class="text-gray-600 ml-1">ổn định</span>
                            @endif
                        </div>
                    @endif
                    
                    @if(isset($headerInsight))
                        <p class="text-gray-600 text-sm">{{ $headerInsight['message'] }}</p>
                    @else
                        <p class="text-gray-600 text-sm">{{ $insight['insight'] }}</p>
                    @endif
                </div>
            </div>
        </div>

        <!-- Layer 2: Visual Trend Strip (7 days) -->
        <div class="bg-white rounded-xl shadow-lg p-6 mb-6">
            <h2 class="text-lg font-semibold text-gray-900 mb-4">Xu hướng 7 ngày gần đây</h2>
            <div class="flex items-end justify-between gap-2">
                @foreach($trendStrip as $day)
                    <div class="flex-1 flex flex-col items-center">
                        <div class="w-full flex flex-col items-center mb-2">
                            <div 
                                class="w-full rounded-t transition-all duration-300
                                    @if($day['color'] === 'red') bg-red-500
                                    @elseif($day['color'] === 'orange') bg-orange-500
                                    @elseif($day['color'] === 'yellow') bg-yellow-500
                                    @else bg-green-500
                                    @endif"
                                style="height: {{ $day['height'] }}%; min-height: 20px;"
                                title="Severity: {{ $day['severity'] }}/10"
                            ></div>
                            @if($day['hasCheckin'])
                                <div class="w-2 h-2 rounded-full bg-blue-500 mt-1"></div>
                            @endif
                        </div>
                        <div class="text-xs text-gray-600 text-center mt-2">
                            <div class="font-medium">{{ $day['label'] }}</div>
                            <div class="text-gray-400">{{ $day['date']->format('d/m') }}</div>
                        </div>
                    </div>
                @endforeach
            </div>
            <div class="mt-4 flex items-center justify-center gap-4 text-xs text-gray-500">
                <div class="flex items-center gap-1">
                    <div class="w-3 h-3 rounded-full bg-blue-500"></div>
                    <span>Có check-in</span>
                </div>
                <div class="flex items-center gap-1">
                    <div class="w-3 h-3 rounded bg-green-500"></div>
                    <span>Ổn định</span>
                </div>
                <div class="flex items-center gap-1">
                    <div class="w-3 h-3 rounded bg-yellow-500"></div>
                    <span>Theo dõi</span>
                </div>
                <div class="flex items-center gap-1">
                    <div class="w-3 h-3 rounded bg-orange-500"></div>
                    <span>Cảnh báo</span>
                </div>
                <div class="flex items-center gap-1">
                    <div class="w-3 h-3 rounded bg-red-500"></div>
                    <span>Nguy hiểm</span>
                </div>
            </div>
        </div>

        <!-- Layer 3: Event Timeline (Meaningful Events Only) -->
        <div class="space-y-6">
            <h2 class="text-lg font-semibold text-gray-900">Sự kiện quan trọng</h2>
            
            @if(count($events) > 0)
                @foreach($events as $dayGroup)
                    <div class="bg-white rounded-xl shadow-lg overflow-hidden">
                        <!-- Date Header -->
                        <div class="bg-gradient-to-r from-purple-500 to-blue-500 text-white px-6 py-3">
                            <div class="flex items-center justify-between">
                                <div>
                                    <h3 class="text-lg font-bold">
                                        @if($dayGroup['date']->isToday())
                                            Hôm nay
                                        @elseif($dayGroup['date']->isYesterday())
                                            Hôm qua
                                        @else
                                            {{ $dayGroup['date']->format('d/m/Y') }}
                                        @endif
                                    </h3>
                                    <p class="text-sm text-purple-100">
                                        {{ $dayGroup['date']->locale('vi')->translatedFormat('l') }}
                                    </p>
                                </div>
                                <div class="text-sm text-purple-100">
                                    {{ count($dayGroup['items']) }} sự kiện
                                </div>
                            </div>
                        </div>

                        <!-- Events -->
                        <div class="p-4 space-y-3">
                            @foreach($dayGroup['items'] as $event)
                                <div class="flex gap-3 p-3 rounded-lg 
                                    @if($event['color'] === 'red') bg-red-50 border border-red-200
                                    @elseif($event['color'] === 'orange') bg-orange-50 border border-orange-200
                                    @elseif($event['color'] === 'yellow') bg-yellow-50 border border-yellow-200
                                    @else bg-blue-50 border border-blue-200
                                    @endif">
                                    <!-- Icon -->
                                    <div class="flex-shrink-0 text-2xl">{{ $event['icon'] }}</div>
                                    
                                    <!-- Content -->
                                    <div class="flex-1 min-w-0">
                                        <div class="flex items-start justify-between mb-1">
                                            <h4 class="font-semibold text-gray-900">{{ $event['title'] }}</h4>
                                            <span class="text-xs text-gray-500">{{ $event['time']->format('H:i') }}</span>
                                        </div>
                                        
                                        @if(isset($event['message']))
                                            <p class="text-sm text-gray-700">{{ $event['message'] }}</p>
                                        @endif
                                        
                                        @if($event['type'] === 'checkin' && isset($event['symptoms']) && $event['symptoms']->count() > 0)
                                            <div class="mt-2 flex flex-wrap gap-2">
                                                @foreach($event['symptoms'] as $symptom)
                                                    <span class="px-2 py-1 bg-white rounded text-xs text-gray-700">
                                                        {{ $symptom->symptom->display_name ?? $symptom->symptom_code }} ({{ $symptom->severity }}/10)
                                                    </span>
                                                @endforeach
                                            </div>
                                        @endif
                                        
                                        @if($event['type'] === 'checkin' && $event['data']->overall_feeling)
                                            <div class="mt-2 text-sm text-gray-600">
                                                Cảm giác: {{ $event['data']->overall_feeling }}/10
                                                @if($event['data']->sleep_hours)
                                                    • Ngủ: {{ $event['data']->sleep_hours }}h
                                                @endif
                                            </div>
                                        @endif

                                        @if($event['type'] === 'insight')
                                            <div class="mt-3">
                                                <a href="{{ route('insights.explain', $event['data']->id) }}" 
                                                   class="inline-flex items-center gap-2 px-3 py-1.5 text-xs font-medium text-blue-700 bg-blue-50 rounded-lg hover:bg-blue-100 transition">
                                                    <span>Vì sao tôi thấy insight này?</span>
                                                    <span>→</span>
                                                </a>
                                            </div>
                                        @endif
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endforeach
            @else
                <div class="bg-white rounded-xl shadow-lg p-12 text-center">
                    <div class="text-6xl mb-4">📅</div>
                    <h3 class="text-xl font-semibold text-gray-900 mb-2">Chưa có sự kiện</h3>
                    <p class="text-gray-600 mb-6">
                        Bắt đầu check-in để xem timeline y tế của bạn
                    </p>
                    <a href="{{ route('checkins.create') }}" 
                       class="inline-block px-6 py-3 bg-blue-500 text-white rounded-lg hover:bg-blue-600 transition">
                        Tạo Check-in đầu tiên
                    </a>
                </div>
            @endif
        </div>
    </div>
</body>
</html>
