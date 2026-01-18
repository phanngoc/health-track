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
        <div class="mb-8 sticky top-0 bg-gradient-to-br from-blue-50 via-white to-green-50 pt-4 pb-2 z-10">
            <div class="flex items-center justify-between">
                <div>
                    <a href="{{ route('home') }}" class="inline-block mb-2 text-gray-600 hover:text-gray-900">
                        ←
                    </a>
                    <h1 class="text-3xl font-bold text-gray-900">Nhật ký sức khỏe</h1>
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

        <!-- Layer 3: Event Timeline with 3-Level Hierarchy -->
        <div class="space-y-6">
            <h2 class="text-lg font-semibold text-gray-900">Nhật ký sức khỏe</h2>
            
            @if(count($daysData) > 0)
                @foreach($daysData as $dayData)
                    <div class="space-y-4">
                        <!-- BLOCK 1: Daily Summary (Level 1) -->
                        @if(isset($dayData['daily_summary']) && $dayData['daily_summary'])
                            <div class="bg-white rounded-xl shadow-lg p-6 border-l-4
                                @if($dayData['daily_summary']['trend'] === 'improving') border-green-500
                                @elseif($dayData['daily_summary']['trend'] === 'worsening') border-red-500
                                @else border-gray-400
                                @endif">
                                <div class="mb-4">
                                    <h3 class="text-xl font-bold text-gray-900 mb-1">
                                        @if($dayData['date']->isToday())
                                            HÔM NAY
                                        @elseif($dayData['date']->isYesterday())
                                            HÔM QUA
                                        @else
                                            {{ $dayData['date']->format('d/m/Y') }}
                                        @endif
                                        @if(isset($dayData['daily_summary']['day_name']))
                                            · {{ $dayData['daily_summary']['day_name'] }}
                                        @endif
                                    </h3>
                                </div>

                                <div class="space-y-3">
                                    <!-- Trend -->
                                    @if(isset($dayData['daily_summary']['trend_icon']) && isset($dayData['daily_summary']['trend_label']))
                                        <div class="flex items-center gap-2">
                                            <span class="text-2xl">{{ $dayData['daily_summary']['trend_icon'] }}</span>
                                            <span class="text-lg font-semibold text-gray-900">
                                                {{ $dayData['daily_summary']['trend_label'] }}
                                            </span>
                                        </div>
                                    @endif

                                    <!-- Overall Feeling -->
                                    @if(isset($dayData['daily_summary']['overall_feeling']) && $dayData['daily_summary']['overall_feeling'])
                                        <div class="flex items-center gap-2">
                                            <span class="text-2xl">
                                                @if($dayData['daily_summary']['overall_feeling'] >= 8) 😄
                                                @elseif($dayData['daily_summary']['overall_feeling'] >= 6) 🙂
                                                @elseif($dayData['daily_summary']['overall_feeling'] >= 4) 😐
                                                @else 😣
                                                @endif
                                            </span>
                                            <span class="text-gray-700">
                                                Cảm giác chung: <span class="font-semibold">{{ $dayData['daily_summary']['overall_feeling'] }}/10</span>
                                            </span>
                                        </div>
                                    @endif

                                    <!-- Primary Symptoms -->
                                    @if(isset($dayData['daily_summary']['primary_symptoms']) && count($dayData['daily_summary']['primary_symptoms']) > 0)
                                        <div>
                                            <span class="text-gray-700 font-medium">🤧 Triệu chứng chính: </span>
                                            <div class="mt-1 flex flex-wrap gap-2">
                                                @foreach($dayData['daily_summary']['primary_symptoms'] as $symptom)
                                                    <span class="px-2 py-1 bg-gray-100 rounded text-sm text-gray-700">
                                                        {{ $symptom['name'] }} · {{ $symptom['severity'] }}/10
                                                    </span>
                                                @endforeach
                                            </div>
                                        </div>
                                    @endif

                                    <!-- Alert Count -->
                                    @if(isset($dayData['daily_summary']['alert_count']) && $dayData['daily_summary']['alert_count'] > 0)
                                        <div class="flex items-center gap-2">
                                            <span class="text-xl">⚠️</span>
                                            <span class="text-gray-700">
                                                {{ $dayData['daily_summary']['alert_count'] }} cảnh báo đang theo dõi
                                            </span>
                                        </div>
                                    @endif
                                </div>
                            </div>
                        @endif

                        <!-- BLOCK 2: Level 2 Events (Alerts, Daily Check-in, Important Symptoms) -->
                        @if(isset($dayData['level_2_events']) && count($dayData['level_2_events']) > 0)
                            <div class="space-y-3">
                                @foreach($dayData['level_2_events'] as $event)
                                    @if($event['type'] === 'alert')
                                        <!-- Alert Card -->
                                        <div class="bg-white rounded-xl shadow-lg p-4 border-l-4 border-red-500">
                                            <div class="flex items-start gap-3">
                                                <div class="text-2xl">{{ $event['icon'] }}</div>
                                                <div class="flex-1">
                                                    <div class="flex items-start justify-between mb-2">
                                                        <h4 class="font-bold text-gray-900">⚠️ CẢNH BÁO</h4>
                                                        <span class="text-xs text-gray-500">{{ $event['time']->format('H:i') }}</span>
                                                    </div>
                                                    <p class="text-sm text-gray-700 mb-3">{{ $event['message'] }}</p>
                                                    <div class="flex gap-2">
                                                        <button class="px-4 py-2 bg-blue-500 text-white text-sm rounded-lg hover:bg-blue-600 transition">
                                                            Theo dõi
                                                        </button>
                                                        <button class="px-4 py-2 bg-red-500 text-white text-sm rounded-lg hover:bg-red-600 transition">
                                                            Đi khám
                                                        </button>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    @elseif($event['type'] === 'checkin')
                                        <!-- Daily Check-in Card -->
                                        <div class="bg-white rounded-xl shadow-lg p-4 border-l-4 border-blue-500">
                                            <div class="flex items-start gap-3">
                                                <div class="text-2xl">📝</div>
                                                <div class="flex-1">
                                                    <div class="flex items-start justify-between mb-2">
                                                        <h4 class="font-bold text-gray-900">📝 CHECK-IN HẰNG NGÀY</h4>
                                                        <span class="text-xs text-gray-500">{{ $event['time']->format('H:i') }}</span>
                                                    </div>
                                                    
                                                    @if($event['data']->overall_feeling)
                                                        <div class="mb-2">
                                                            <span class="text-2xl">
                                                                @if($event['data']->overall_feeling >= 8) 😄
                                                                @elseif($event['data']->overall_feeling >= 6) 🙂
                                                                @elseif($event['data']->overall_feeling >= 4) 😐
                                                                @else 😣
                                                                @endif
                                                            </span>
                                                            <span class="text-gray-700 ml-2">
                                                                Cảm giác tổng thể: <span class="font-semibold">{{ $event['data']->overall_feeling }}/10</span>
                                                            </span>
                                                        </div>
                                                    @endif

                                                    @if($event['data']->sleep_hours)
                                                        <div class="mb-2 text-sm text-gray-600">
                                                            🛌 Giấc ngủ: Ảnh hưởng nhẹ ({{ $event['data']->sleep_hours }}h)
                                                        </div>
                                                    @endif

                                                    @if(isset($event['symptoms']) && $event['symptoms']->count() > 0)
                                                        <div class="mb-2">
                                                            <span class="text-sm text-gray-600">Triệu chứng: </span>
                                                            <div class="mt-1 flex flex-wrap gap-2">
                                                                @foreach($event['symptoms'] as $symptom)
                                                                    <span class="px-2 py-1 bg-gray-100 rounded text-xs text-gray-700">
                                                                        {{ $symptom->symptom->display_name ?? $symptom->symptom_code }} ({{ $symptom->severity }}/10)
                                                                    </span>
                                                                @endforeach
                                                            </div>
                                                        </div>
                                                    @endif

                                                    @if($event['data']->notes)
                                                        <div class="mt-2 text-sm text-gray-600">
                                                            🏷️ Ghi chú: {{ $event['data']->notes }}
                                                        </div>
                                                    @endif
                                                </div>
                                            </div>
                                        </div>
                                    @elseif($event['type'] === 'symptom')
                                        <!-- Important Symptom Card -->
                                        <div class="bg-white rounded-xl shadow-lg p-4 border-l-4
                                            @if($event['color'] === 'red') border-red-500
                                            @elseif($event['color'] === 'orange') border-orange-500
                                            @else border-yellow-500
                                            @endif">
                                            <div class="flex items-start gap-3">
                                                <div class="text-2xl">{{ $event['icon'] }}</div>
                                                <div class="flex-1">
                                                    <div class="flex items-start justify-between mb-1">
                                                        <h4 class="font-semibold text-gray-900">🤧 TRIỆU CHỨNG TRONG NGÀY</h4>
                                                        <span class="text-xs text-gray-500">{{ $event['time']->format('H:i') }}</span>
                                                    </div>
                                                    <p class="text-sm text-gray-700">
                                                        {{ $event['title'] }}
                                                    </p>
                                                </div>
                                            </div>
                                        </div>
                                    @elseif($event['type'] === 'insight')
                                        <!-- Insight Card -->
                                        <div class="bg-white rounded-xl shadow-lg p-4 border-l-4 border-blue-500">
                                            <div class="flex items-start gap-3">
                                                <div class="text-2xl">🧠</div>
                                                <div class="flex-1">
                                                    <div class="flex items-start justify-between mb-1">
                                                        <h4 class="font-semibold text-gray-900">{{ $event['title'] }}</h4>
                                                        <span class="text-xs text-gray-500">{{ $event['time']->format('H:i') }}</span>
                                                    </div>
                                                    <p class="text-sm text-gray-700 mb-3">{{ $event['message'] }}</p>
                                                    <a href="{{ route('insights.explain', $event['data']->id) }}" 
                                                       class="inline-flex items-center gap-2 px-3 py-1.5 text-xs font-medium text-blue-700 bg-blue-50 rounded-lg hover:bg-blue-100 transition">
                                                        <span>Vì sao tôi thấy insight này?</span>
                                                        <span>→</span>
                                                    </a>
                                                </div>
                                            </div>
                                        </div>
                                    @endif
                                @endforeach
                            </div>
                        @endif

                        <!-- BLOCK 3: Level 3 Events (Moment Check-ins - Collapsible) -->
                        @if(isset($dayData['level_3_events']) && isset($dayData['level_3_events']['count']) && $dayData['level_3_events']['count'] > 0)
                            <div class="bg-white rounded-xl shadow-lg overflow-hidden">
                                <div class="p-4">
                                    <div class="flex items-center justify-between mb-3">
                                        <h4 class="font-semibold text-gray-900">
                                            🕒 DIỄN BIẾN TRONG NGÀY ({{ $dayData['level_3_events']['count'] }})
                                        </h4>
                                        <button 
                                            class="moment-toggle-btn text-sm text-blue-600 hover:text-blue-800 font-medium"
                                            data-target="moment-group-{{ $dayData['date']->format('Y-m-d') }}">
                                            <span class="toggle-text">⌄ Xem chi tiết</span>
                                        </button>
                                    </div>

                                    <!-- Preview (always visible) -->
                                    <div class="space-y-2 mb-3">
                                        @foreach($dayData['level_3_events']['preview'] as $moment)
                                            <div class="flex items-center gap-2 text-sm text-gray-700">
                                                <span class="text-xl">{{ $moment['icon'] }}</span>
                                                <span class="text-gray-500">{{ $moment['time']->format('H:i') }}</span>
                                                <span>{{ $moment['message'] ?? 'Moment check-in' }}</span>
                                            </div>
                                        @endforeach
                                    </div>

                                    <!-- All moments (collapsed by default) -->
                                    <div 
                                        id="moment-group-{{ $dayData['date']->format('Y-m-d') }}" 
                                        class="moment-group hidden space-y-2 transition-all duration-300 ease-in-out overflow-hidden"
                                        style="max-height: 0; opacity: 0;">
                                        @foreach($dayData['level_3_events']['all'] as $moment)
                                            <div class="p-2 bg-gray-50 rounded">
                                                <div class="flex items-center gap-2 mb-1">
                                                    <span class="text-xl">{{ $moment['icon'] }}</span>
                                                    <span class="text-gray-500 text-sm">{{ $moment['time']->format('H:i') }}</span>
                                                    <span class="text-sm text-gray-700 font-medium">
                                                        @if($moment['data']->feeling_level)
                                                            Cảm giác lúc {{ $moment['time']->format('H:i') }}: {{ $moment['data']->feeling_level }}/10
                                                        @endif
                                                    </span>
                                                </div>
                                                @if($moment['data']->tags && is_array($moment['data']->tags) && count($moment['data']->tags) > 0)
                                                    <div class="mt-1">
                                                        <span class="text-xs text-gray-600">🏷️ </span>
                                                        <span class="text-xs text-gray-600">{{ implode(' · ', $moment['data']->tags) }}</span>
                                                    </div>
                                                @endif
                                                @if(isset($moment['symptoms']) && $moment['symptoms']->count() > 0)
                                                    <div class="mt-2 flex flex-wrap gap-2">
                                                        @foreach($moment['symptoms'] as $symptom)
                                                            <span class="px-2 py-1 bg-white rounded text-xs text-gray-700">
                                                                {{ $symptom->symptom->display_name ?? $symptom->symptom_code }} ({{ $symptom->severity }}/10)
                                                            </span>
                                                        @endforeach
                                                    </div>
                                                @endif
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                            </div>
                        @endif

                        <!-- Day Separator (for previous days) -->
                        @if(!$dayData['date']->isToday() && $loop->index < count($daysData) - 1)
                            <div class="border-t-2 border-gray-200 my-6"></div>
                        @endif
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

        <!-- JavaScript for collapse/expand -->
        <script src="{{ asset('js/timeline.js') }}"></script>
    </div>
</body>
</html>
