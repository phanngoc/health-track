<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Health Tracker - {{ config('app.name', 'Laravel') }}</title>
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=instrument-sans:400,500,600,700" rel="stylesheet" />
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gradient-to-br from-blue-50 via-white to-green-50 min-h-screen">
    <div class="container mx-auto px-4 py-8 max-w-7xl">
        <!-- Header -->
        <header class="mb-8">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-4xl font-bold text-gray-900 mb-2">
                        Health Tracker
                    </h1>
                    <p class="text-gray-600">
                        Theo dõi sức khỏe của bạn mỗi ngày
                    </p>
                </div>
                @auth
                    <div class="flex items-center gap-4">
                        <span class="text-sm text-gray-600">Xin chào, {{ Auth::user()->name }}</span>
                        <form method="POST" action="{{ route('logout') }}" class="inline">
                            @csrf
                            <button type="submit" 
                               class="px-4 py-2 bg-red-500 text-white rounded-lg hover:bg-red-600 transition">
                                Đăng xuất
                            </button>
                        </form>
                    </div>
                @else
                    <div class="flex items-center gap-4">
                        <a href="{{ route('login') }}" 
                           class="px-4 py-2 text-gray-700 hover:text-gray-900 transition">
                            Đăng nhập
                        </a>
                        <a href="{{ route('register') }}" 
                           class="px-4 py-2 bg-blue-500 text-white rounded-lg hover:bg-blue-600 transition">
                            Đăng ký
                        </a>
                    </div>
                @endauth
            </div>
        </header>

        <!-- Stats Cards -->
        @auth
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
            <div class="bg-white rounded-xl shadow-lg p-6 border-l-4 border-blue-500">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm text-gray-600 mb-1">Tổng Check-in</p>
                        <p class="text-3xl font-bold text-gray-900">{{ $stats['total_checkins'] }}</p>
                    </div>
                    <div class="w-12 h-12 bg-blue-100 rounded-full flex items-center justify-center">
                        <svg class="w-6 h-6 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-xl shadow-lg p-6 border-l-4 border-yellow-500">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm text-gray-600 mb-1">Triệu chứng</p>
                        <p class="text-3xl font-bold text-gray-900">{{ $stats['total_symptoms'] }}</p>
                    </div>
                    <div class="w-12 h-12 bg-yellow-100 rounded-full flex items-center justify-center">
                        <svg class="w-6 h-6 text-yellow-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path>
                        </svg>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-xl shadow-lg p-6 border-l-4 border-red-500">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm text-gray-600 mb-1">Cảnh báo</p>
                        <p class="text-3xl font-bold text-gray-900">{{ $stats['active_alerts'] }}</p>
                    </div>
                    <div class="w-12 h-12 bg-red-100 rounded-full flex items-center justify-center">
                        <svg class="w-6 h-6 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path>
                        </svg>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-xl shadow-lg p-6 border-l-4 border-green-500">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm text-gray-600 mb-1">Sự kiện (7 ngày)</p>
                        <p class="text-3xl font-bold text-gray-900">{{ $stats['timeline_events'] }}</p>
                    </div>
                    <div class="w-12 h-12 bg-green-100 rounded-full flex items-center justify-center">
                        <svg class="w-6 h-6 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                    </div>
                </div>
            </div>
        </div>
        @endauth

        <!-- Main Features -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            <!-- Daily Check-in Card -->
            <div class="bg-white rounded-xl shadow-lg p-6 hover:shadow-xl transition-shadow">
                <div class="flex items-center mb-4">
                    <div class="w-10 h-10 bg-blue-100 rounded-lg flex items-center justify-center mr-3">
                        <svg class="w-6 h-6 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                    </div>
                    <h3 class="text-xl font-semibold text-gray-900">Check-in Hằng Ngày</h3>
                </div>
                <p class="text-gray-600 mb-4">
                    Ghi nhận tình trạng sức khỏe của bạn mỗi ngày trong vòng chưa đến 30 giây.
                </p>
                @auth
                    <a href="{{ route('checkins.create') }}" 
                       class="inline-block px-4 py-2 bg-blue-500 text-white rounded-lg hover:bg-blue-600 transition">
                        Bắt đầu Check-in
                    </a>
                @else
                    <a href="{{ route('login') }}" 
                       class="inline-block px-4 py-2 bg-blue-500 text-white rounded-lg hover:bg-blue-600 transition">
                        Đăng nhập để bắt đầu
                    </a>
                @endauth
            </div>

            <!-- Timeline Card -->
            <div class="bg-white rounded-xl shadow-lg p-6 hover:shadow-xl transition-shadow">
                <div class="flex items-center mb-4">
                    <div class="w-10 h-10 bg-purple-100 rounded-lg flex items-center justify-center mr-3">
                        <svg class="w-6 h-6 text-purple-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                    </div>
                    <h3 class="text-xl font-semibold text-gray-900">Timeline Y Tế</h3>
                </div>
                <p class="text-gray-600 mb-4">
                    Xem lại lịch sử sức khỏe của bạn theo thời gian, bao gồm triệu chứng, thuốc và sự kiện y tế.
                </p>
                @auth
                    <a href="{{ route('timeline.index') }}" 
                       class="inline-block px-4 py-2 bg-purple-500 text-white rounded-lg hover:bg-purple-600 transition">
                        Xem Timeline
                    </a>
                @else
                    <a href="{{ route('login') }}" 
                       class="inline-block px-4 py-2 bg-purple-500 text-white rounded-lg hover:bg-purple-600 transition">
                        Đăng nhập để xem
                    </a>
                @endauth
            </div>

            <!-- Alerts Card -->
            <div class="bg-white rounded-xl shadow-lg p-6 hover:shadow-xl transition-shadow">
                <div class="flex items-center mb-4">
                    <div class="w-10 h-10 bg-red-100 rounded-lg flex items-center justify-center mr-3">
                        <svg class="w-6 h-6 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path>
                        </svg>
                    </div>
                    <h3 class="text-xl font-semibold text-gray-900">Cảnh Báo Sớm</h3>
                </div>
                <p class="text-gray-600 mb-4">
                    Nhận cảnh báo thông minh khi phát hiện xu hướng bất thường trong sức khỏe của bạn.
                </p>
                @auth
                    <a href="/api/alerts" 
                       class="inline-block px-4 py-2 bg-red-500 text-white rounded-lg hover:bg-red-600 transition">
                        Xem Cảnh Báo
                    </a>
                @else
                    <a href="{{ route('login') }}" 
                       class="inline-block px-4 py-2 bg-red-500 text-white rounded-lg hover:bg-red-600 transition">
                        Đăng nhập để xem
                    </a>
                @endauth
            </div>

            <!-- Knowledge Cards -->
            <div class="bg-white rounded-xl shadow-lg p-6 hover:shadow-xl transition-shadow">
                <div class="flex items-center mb-4">
                    <div class="w-10 h-10 bg-green-100 rounded-lg flex items-center justify-center mr-3">
                        <svg class="w-6 h-6 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"></path>
                        </svg>
                    </div>
                    <h3 class="text-xl font-semibold text-gray-900">Kiến Thức Y Tế</h3>
                </div>
                <p class="text-gray-600 mb-4">
                    Tìm hiểu về các triệu chứng và cách xử lý với các thẻ kiến thức ngắn gọn, dễ hiểu.
                </p>
                @auth
                    <a href="/api/knowledge" 
                       class="inline-block px-4 py-2 bg-green-500 text-white rounded-lg hover:bg-green-600 transition">
                        Xem Kiến Thức
                    </a>
                @else
                    <a href="{{ route('login') }}" 
                       class="inline-block px-4 py-2 bg-green-500 text-white rounded-lg hover:bg-green-600 transition">
                        Đăng nhập để xem
                    </a>
                @endauth
            </div>

            <!-- Features Overview -->
            <div class="bg-gradient-to-br from-blue-500 to-purple-600 rounded-xl shadow-lg p-6 text-white md:col-span-2">
                <h3 class="text-2xl font-bold mb-4">Tính Năng Chính</h3>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div class="flex items-start">
                        <svg class="w-5 h-5 mr-2 mt-1 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                        </svg>
                        <div>
                            <p class="font-semibold">Theo dõi Triệu Chứng</p>
                            <p class="text-sm text-blue-100">Ghi nhận và theo dõi triệu chứng theo thời gian</p>
                        </div>
                    </div>
                    <div class="flex items-start">
                        <svg class="w-5 h-5 mr-2 mt-1 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                        </svg>
                        <div>
                            <p class="font-semibold">Cảnh Báo Thông Minh</p>
                            <p class="text-sm text-blue-100">Phát hiện xu hướng xấu và cảnh báo sớm</p>
                        </div>
                    </div>
                    <div class="flex items-start">
                        <svg class="w-5 h-5 mr-2 mt-1 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                        </svg>
                        <div>
                            <p class="font-semibold">Timeline Y Tế</p>
                            <p class="text-sm text-blue-100">Xem lại toàn bộ lịch sử sức khỏe</p>
                        </div>
                    </div>
                    <div class="flex items-start">
                        <svg class="w-5 h-5 mr-2 mt-1 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                        </svg>
                        <div>
                            <p class="font-semibold">Kiến Thức Nhanh</p>
                            <p class="text-sm text-blue-100">Học hỏi về triệu chứng và cách xử lý</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Call to Action -->
        @guest
        <div class="mt-12 bg-white rounded-xl shadow-lg p-8 text-center">
            <h2 class="text-3xl font-bold text-gray-900 mb-4">Bắt Đầu Theo Dõi Sức Khỏe Hôm Nay</h2>
            <p class="text-gray-600 mb-6 max-w-2xl mx-auto">
                Đăng ký ngay để bắt đầu theo dõi sức khỏe của bạn, nhận cảnh báo sớm và hiểu rõ hơn về tình trạng sức khỏe của mình.
            </p>
            <div class="flex items-center justify-center gap-4">
                <a href="{{ route('register') }}" 
                   class="px-6 py-3 bg-blue-500 text-white rounded-lg hover:bg-blue-600 transition font-semibold">
                    Đăng Ký Miễn Phí
                </a>
                <a href="{{ route('login') }}" 
                   class="px-6 py-3 border-2 border-gray-300 text-gray-700 rounded-lg hover:border-gray-400 transition font-semibold">
                    Đã Có Tài Khoản?
                </a>
            </div>
        </div>
        @endguest
    </div>
</body>
</html>

