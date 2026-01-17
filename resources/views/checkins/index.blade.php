<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Lịch Sử Check-in - {{ config('app.name', 'Laravel') }}</title>
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
                    <h1 class="text-3xl font-bold text-gray-900 mb-2">Lịch Sử Check-in</h1>
                    <p class="text-gray-600">Xem lại các lần check-in của bạn</p>
                </div>
                <div class="flex gap-4">
                    <a href="{{ route('checkins.create') }}" class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition">
                        + Check-in mới
                    </a>
                    <a href="{{ route('home') }}" class="px-4 py-2 text-gray-600 hover:text-gray-900">
                        ← Về trang chủ
                    </a>
                </div>
            </div>
        </div>

        @if(session('success'))
            <div class="bg-green-50 border border-green-200 text-green-700 px-4 py-3 rounded-lg mb-6">
                {{ session('success') }}
            </div>
        @endif

        <!-- Check-ins List -->
        @if($checkins->count() > 0)
            <div class="space-y-4">
                @foreach($checkins as $checkin)
                    <div class="bg-white rounded-xl shadow-lg p-6">
                        <div class="flex items-start justify-between mb-4">
                            <div>
                                <h3 class="text-lg font-semibold text-gray-900">
                                    {{ $checkin->checkin_date->format('d/m/Y') }}
                                </h3>
                                <p class="text-sm text-gray-500">
                                    {{ $checkin->checkin_date->format('l') }}
                                </p>
                            </div>
                            @if($checkin->overall_feeling)
                                <div class="text-right">
                                    <span class="text-sm text-gray-600">Cảm giác:</span>
                                    <span class="text-2xl font-bold text-blue-600 ml-2">
                                        {{ $checkin->overall_feeling }}/10
                                    </span>
                                </div>
                            @endif
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-4">
                            @if($checkin->sleep_hours)
                                <div class="flex items-center gap-2">
                                    <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z"></path>
                                    </svg>
                                    <span class="text-gray-700">{{ $checkin->sleep_hours }} giờ ngủ</span>
                                </div>
                            @endif

                            @if($checkin->symptomLogs->count() > 0)
                                <div class="flex items-center gap-2">
                                    <svg class="w-5 h-5 text-yellow-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path>
                                    </svg>
                                    <span class="text-gray-700">{{ $checkin->symptomLogs->count() }} triệu chứng</span>
                                </div>
                            @endif
                        </div>

                        @if($checkin->symptomLogs->count() > 0)
                            <div class="mb-4">
                                <h4 class="text-sm font-medium text-gray-700 mb-2">Triệu chứng:</h4>
                                <div class="flex flex-wrap gap-2">
                                    @foreach($checkin->symptomLogs as $log)
                                        <span class="px-3 py-1 bg-yellow-100 text-yellow-800 rounded-full text-sm">
                                            {{ $log->symptom->display_name ?? $log->symptom_code }} ({{ $log->severity }}/10)
                                        </span>
                                    @endforeach
                                </div>
                            </div>
                        @endif

                        @if($checkin->notes)
                            <div class="mt-4 p-3 bg-gray-50 rounded-lg">
                                <p class="text-sm text-gray-700">{{ $checkin->notes }}</p>
                            </div>
                        @endif
                    </div>
                @endforeach
            </div>

            <!-- Pagination -->
            <div class="mt-6">
                {{ $checkins->links() }}
            </div>
        @else
            <div class="bg-white rounded-xl shadow-lg p-12 text-center">
                <svg class="w-16 h-16 text-gray-400 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                </svg>
                <h3 class="text-lg font-semibold text-gray-900 mb-2">Chưa có check-in nào</h3>
                <p class="text-gray-600 mb-6">Bắt đầu check-in để theo dõi sức khỏe của bạn</p>
                <a href="{{ route('checkins.create') }}" class="inline-block px-6 py-3 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition font-semibold">
                    Tạo Check-in đầu tiên
                </a>
            </div>
        @endif
    </div>
</body>
</html>

