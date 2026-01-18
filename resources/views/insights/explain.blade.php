<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Giải thích Insight - {{ config('app.name', 'Laravel') }}</title>
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=instrument-sans:400,500,600,700" rel="stylesheet" />
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gradient-to-br from-blue-50 via-white to-green-50 min-h-screen">
    <div class="container mx-auto px-4 py-8 max-w-4xl">
        <!-- Header -->
        <div class="mb-8">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-3xl font-bold text-gray-900 mb-2">Vì sao tôi thấy insight này?</h1>
                    <p class="text-gray-600">Giải thích đơn giản về insight của bạn</p>
                </div>
                <div class="flex gap-4">
                    <a href="{{ route('timeline.index') }}" class="px-4 py-2 text-gray-600 hover:text-gray-900">
                        ← Về Timeline
                    </a>
                </div>
            </div>
        </div>

        <!-- Insight Card -->
        <div class="bg-white rounded-xl shadow-lg p-6 mb-6 border-l-4 border-blue-500">
            <div class="flex items-start gap-4">
                <div class="text-4xl">🧠</div>
                <div class="flex-1">
                    <div class="mb-3">
                        <span class="px-3 py-1 bg-blue-100 text-blue-800 text-xs font-semibold rounded-full">
                            {{ $explanation['insight_summary']['type'] }}
                        </span>
                        <span class="ml-2 px-3 py-1 bg-gray-100 text-gray-800 text-xs font-semibold rounded-full">
                            {{ strtoupper($explanation['insight_summary']['priority']) }}
                        </span>
                    </div>
                    <h2 class="text-xl font-bold text-gray-900 mb-2">{{ $explanation['insight_summary']['message'] }}</h2>
                    <p class="text-sm text-gray-500">
                        Được tạo vào: {{ $explanation['insight_summary']['created_at']->format('d/m/Y H:i') }}
                    </p>
                </div>
            </div>
        </div>

        <!-- Story Section (NEW - Prominent) -->
        @if(isset($explanation['story']))
            <div class="bg-white rounded-xl shadow-lg p-6 mb-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">{{ $explanation['story']['headline'] }}</h3>
                
                <p class="text-gray-700 text-base leading-relaxed mb-4">
                    {{ $explanation['story']['narrative'] }}
                </p>

                @if(!empty($explanation['story']['key_facts']))
                    <div class="bg-blue-50 rounded-lg p-4 mt-4">
                        <h4 class="font-semibold text-gray-900 mb-2 text-sm">Điểm quan trọng:</h4>
                        <ul class="space-y-1">
                            @foreach($explanation['story']['key_facts'] as $fact)
                                <li class="text-sm text-gray-700 flex items-start">
                                    <span class="text-blue-600 mr-2">•</span>
                                    <span>{{ $fact }}</span>
                                </li>
                            @endforeach
                        </ul>
                    </div>
                @endif
            </div>
        @endif

        <!-- Data Summary Section (REDESIGNED) -->
        @if(isset($explanation['data_summary']))
            <div class="bg-white rounded-xl shadow-lg p-6 mb-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">Dữ liệu được sử dụng</h3>
                
                <div class="space-y-4">
                    <!-- Time Period -->
                    @if(isset($explanation['data_summary']['time_period']))
                        <div class="flex items-center gap-2 text-sm text-gray-600">
                            <span class="font-medium">Thời gian phân tích:</span>
                            <span>{{ $explanation['data_summary']['time_period'] }}</span>
                        </div>
                    @endif

                    <!-- Main Comparison -->
                    @if(isset($explanation['data_summary']['main_comparison']))
                        <div class="bg-green-50 rounded-lg p-4 border-l-4 border-green-500">
                            <h4 class="font-semibold text-gray-900 mb-2">So sánh chính</h4>
                            <p class="text-gray-700">
                                Mức độ triệu chứng hiện tại {{ $explanation['data_summary']['main_comparison'] }}
                            </p>
                        </div>
                    @endif

                    <!-- Supporting Data -->
                    @if(!empty($explanation['data_summary']['supporting_data']))
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                            @foreach($explanation['data_summary']['supporting_data'] as $data)
                                <div class="bg-gray-50 rounded-lg p-3">
                                    <div class="text-xs text-gray-500 mb-1">{{ $data['label'] }}</div>
                                    <div class="text-lg font-semibold text-gray-900">{{ $data['value'] }}</div>
                                </div>
                            @endforeach
                        </div>
                    @endif
                </div>
            </div>
        @endif

        <!-- What This Means (NEW - Actionable Insights) -->
        @if(!empty($explanation['actionable_insights']))
            <div class="bg-white rounded-xl shadow-lg p-6 mb-6 border-l-4 border-purple-500">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">Điều này có nghĩa là:</h3>
                <ul class="space-y-2">
                    @foreach($explanation['actionable_insights'] as $action)
                        <li class="flex items-start gap-3 text-gray-700">
                            <span class="text-purple-600 mt-1">→</span>
                            <span>{{ $action }}</span>
                        </li>
                    @endforeach
                </ul>
            </div>
        @endif

        <!-- Technical Details (COLLAPSIBLE) -->
        @if(isset($explanation['technical_details']))
            <div class="bg-white rounded-xl shadow-lg p-6 mb-6">
                <button 
                    onclick="toggleTechnicalDetails()"
                    class="w-full flex items-center justify-between text-left font-semibold text-gray-700 hover:text-gray-900 transition"
                    id="technical-toggle-btn">
                    <span>Chi tiết kỹ thuật</span>
                    <span id="technical-toggle-icon">⌄</span>
                </button>
                
                <div id="technical-details" class="hidden mt-4 pt-4 border-t border-gray-200">
                    @if(isset($explanation['technical_details']['rule_code']))
                        <div class="mb-3">
                            <span class="text-xs font-medium text-gray-500">Mã quy tắc:</span>
                            <span class="text-xs text-gray-700 ml-2 font-mono">{{ $explanation['technical_details']['rule_code'] }}</span>
                        </div>
                    @endif

                    @if(isset($explanation['technical_details']['raw_data']) && !empty($explanation['technical_details']['raw_data']))
                        <div class="bg-gray-50 rounded-lg p-4">
                            <span class="text-xs font-medium text-gray-500 block mb-2">Dữ liệu thô:</span>
                            <pre class="text-xs text-gray-700 overflow-x-auto">{{ json_encode($explanation['technical_details']['raw_data'], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) }}</pre>
                        </div>
                    @endif
                </div>
            </div>
        @endif

        <!-- Disclaimer -->
        <div class="bg-yellow-50 rounded-xl shadow-lg p-6 border-l-4 border-yellow-500">
            <div class="flex items-start gap-3">
                <span class="text-2xl">⚠️</span>
                <div>
                    <h4 class="font-semibold text-gray-900 mb-2">Lưu ý quan trọng</h4>
                    <p class="text-sm text-gray-700">
                        Insight này không phải là chẩn đoán y tế. Nó được tạo dựa trên dữ liệu bạn ghi nhận và các quy luật thống kê. 
                        Nếu bạn có lo ngại về sức khỏe, hãy trao đổi với bác sĩ.
                    </p>
                </div>
            </div>
        </div>
    </div>

    <script>
        function toggleTechnicalDetails() {
            const details = document.getElementById('technical-details');
            const icon = document.getElementById('technical-toggle-icon');
            
            if (details.classList.contains('hidden')) {
                details.classList.remove('hidden');
                icon.textContent = '⌃';
            } else {
                details.classList.add('hidden');
                icon.textContent = '⌄';
            }
        }
    </script>
</body>
</html>
