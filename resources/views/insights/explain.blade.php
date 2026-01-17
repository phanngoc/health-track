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
    <div class="container mx-auto px-4 py-8 max-w-6xl">
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
                            {{ $insight->type }}
                        </span>
                        <span class="ml-2 px-3 py-1 bg-gray-100 text-gray-800 text-xs font-semibold rounded-full">
                            {{ strtoupper($insight->priority) }}
                        </span>
                    </div>
                    <h2 class="text-xl font-bold text-gray-900 mb-2">{{ $insight->message }}</h2>
                    <p class="text-sm text-gray-500">
                        Được tạo vào: {{ $insight->generated_at->format('d/m/Y H:i') }}
                    </p>
                </div>
            </div>
        </div>

        <!-- Explanation -->
        <div class="bg-white rounded-xl shadow-lg p-6 mb-6">
            <h3 class="text-lg font-semibold text-gray-900 mb-4">Dữ liệu được sử dụng</h3>
            
            @if($insight->explanation_data && isset($insight->explanation_data['data_used']))
                @php
                    $dataUsed = $insight->explanation_data['data_used'];
                @endphp

                @if(isset($dataUsed['trend']))
                    <div class="mb-4 p-4 bg-blue-50 rounded-lg">
                        <h4 class="font-semibold text-gray-900 mb-2">Xu hướng (3 ngày gần đây)</h4>
                        <div class="space-y-1 text-sm text-gray-700">
                            <p><strong>Hướng:</strong> 
                                @if($dataUsed['trend']['direction'] === 'worsening')
                                    <span class="text-red-600">Nặng hơn</span>
                                @elseif($dataUsed['trend']['direction'] === 'improving')
                                    <span class="text-green-600">Cải thiện</span>
                                @else
                                    <span class="text-gray-600">Ổn định</span>
                                @endif
                            </p>
                            @if(isset($dataUsed['trend']['3d_avg']) && isset($dataUsed['trend']['7d_avg']))
                                <p><strong>Trung bình 3 ngày:</strong> {{ $dataUsed['trend']['3d_avg'] }}/10</p>
                                <p><strong>Trung bình 7 ngày:</strong> {{ $dataUsed['trend']['7d_avg'] }}/10</p>
                            @endif
                        </div>
                    </div>
                @endif

                @if(isset($dataUsed['pattern']))
                    <div class="mb-4 p-4 bg-purple-50 rounded-lg">
                        <h4 class="font-semibold text-gray-900 mb-2">Quy luật phát hiện</h4>
                        <div class="space-y-1 text-sm text-gray-700">
                            @if(isset($dataUsed['pattern']['pattern']))
                                <p><strong>Loại:</strong> {{ $dataUsed['pattern']['pattern'] }}</p>
                            @endif
                            @if(isset($dataUsed['pattern']['night_avg']) && isset($dataUsed['pattern']['day_avg']))
                                <p><strong>Trung bình ban đêm:</strong> {{ $dataUsed['pattern']['night_avg'] }}/10</p>
                                <p><strong>Trung bình ban ngày:</strong> {{ $dataUsed['pattern']['day_avg'] }}/10</p>
                            @endif
                        </div>
                    </div>
                @endif

                @if(isset($dataUsed['comparison']))
                    <div class="mb-4 p-4 bg-green-50 rounded-lg">
                        <h4 class="font-semibold text-gray-900 mb-2">So sánh</h4>
                        <div class="space-y-1 text-sm text-gray-700">
                            <p><strong>Cơ sở so sánh:</strong> {{ $dataUsed['comparison']['baseline'] === 'last_week' ? 'Tuần trước' : 'Trung bình cá nhân' }}</p>
                            <p><strong>Hiện tại:</strong> {{ $dataUsed['comparison']['current_avg'] }}/10</p>
                            <p><strong>Cơ sở:</strong> {{ $dataUsed['comparison']['baseline_avg'] }}/10</p>
                        </div>
                    </div>
                @endif
            @else
                <div class="p-4 bg-gray-50 rounded-lg">
                    <p class="text-sm text-gray-600">Không có dữ liệu giải thích chi tiết.</p>
                </div>
            @endif
        </div>

        <!-- Simple Rule Explanation -->
        <div class="bg-white rounded-xl shadow-lg p-6">
            <h3 class="text-lg font-semibold text-gray-900 mb-4">Quy tắc đơn giản</h3>
            <div class="prose max-w-none">
                <p class="text-gray-700">
                    Insight này được tạo dựa trên dữ liệu sức khỏe bạn ghi nhận trong 3 ngày gần đây.
                    Hệ thống so sánh xu hướng và phát hiện các quy luật để đưa ra nhận định.
                </p>
                <p class="text-gray-600 text-sm mt-4">
                    <strong>Lưu ý:</strong> Insight không phải là chẩn đoán y tế. 
                    Nếu bạn có lo ngại về sức khỏe, hãy trao đổi với bác sĩ.
                </p>
            </div>
        </div>
    </div>
</body>
</html>
