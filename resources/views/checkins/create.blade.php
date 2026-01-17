<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Check-in Hằng Ngày - {{ config('app.name', 'Laravel') }}</title>
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
                    <h1 class="text-3xl font-bold text-gray-900 mb-2">Check-in Hằng Ngày</h1>
                    <p class="text-gray-600">Ghi nhận tình trạng sức khỏe của bạn hôm nay</p>
                </div>
                <a href="{{ route('home') }}" class="text-gray-600 hover:text-gray-900">
                    ← Về trang chủ
                </a>
            </div>
        </div>

        @if(session('success'))
            <div class="bg-green-50 border border-green-200 text-green-700 px-4 py-3 rounded-lg mb-6">
                {{ session('success') }}
            </div>
        @endif

        @if($todayCheckin)
            <div class="bg-blue-50 border border-blue-200 text-blue-700 px-4 py-3 rounded-lg mb-6">
                Bạn đã check-in hôm nay rồi. Bạn có thể cập nhật thông tin bên dưới.
            </div>
        @endif

        <!-- Form -->
        <form method="POST" action="{{ route('checkins.store') }}" class="bg-white rounded-xl shadow-lg p-6 space-y-6">
            @csrf

            @if ($errors->any())
                <div class="bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-lg">
                    <ul class="list-disc list-inside space-y-1">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <!-- Overall Feeling -->
            <div>
                <label for="overall_feeling" class="block text-sm font-medium text-gray-700 mb-2">
                    Cảm giác tổng thể hôm nay (1-10)
                </label>
                <div class="flex items-center gap-4">
                    <input
                        type="range"
                        id="overall_feeling"
                        name="overall_feeling"
                        min="1"
                        max="10"
                        value="{{ old('overall_feeling', $todayCheckin->overall_feeling ?? 5) }}"
                        class="flex-1"
                        oninput="document.getElementById('feeling_value').textContent = this.value"
                    >
                    <span id="feeling_value" class="text-2xl font-bold text-blue-600 w-12 text-center">
                        {{ old('overall_feeling', $todayCheckin->overall_feeling ?? 5) }}
                    </span>
                </div>
                <div class="flex justify-between text-xs text-gray-500 mt-1">
                    <span>Rất tệ</span>
                    <span>Bình thường</span>
                    <span>Rất tốt</span>
                </div>
            </div>

            <!-- Sleep Hours -->
            <div>
                <label for="sleep_hours" class="block text-sm font-medium text-gray-700 mb-2">
                    Số giờ ngủ đêm qua
                </label>
                <input
                    type="number"
                    id="sleep_hours"
                    name="sleep_hours"
                    min="0"
                    max="24"
                    step="0.5"
                    value="{{ old('sleep_hours', $todayCheckin->sleep_hours ?? '') }}"
                    class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                    placeholder="Ví dụ: 7.5"
                >
            </div>

            <!-- Symptoms -->
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-3">
                    Triệu chứng (nếu có)
                </label>
                <div id="symptoms-container" class="space-y-3">
                    <!-- Symptom template sẽ được thêm bằng JavaScript -->
                </div>
                <button
                    type="button"
                    onclick="addSymptom()"
                    class="mt-3 px-4 py-2 bg-gray-100 text-gray-700 rounded-lg hover:bg-gray-200 transition text-sm"
                >
                    + Thêm triệu chứng
                </button>
            </div>

            <!-- Notes -->
            <div>
                <label for="notes" class="block text-sm font-medium text-gray-700 mb-2">
                    Ghi chú thêm (tùy chọn)
                </label>
                <textarea
                    id="notes"
                    name="notes"
                    rows="4"
                    class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                    placeholder="Ghi chú về tình trạng sức khỏe, thuốc đã uống, hoạt động..."
                >{{ old('notes', $todayCheckin->notes ?? '') }}</textarea>
            </div>

            <!-- Submit Button -->
            <div class="flex gap-4">
                <button
                    type="submit"
                    class="flex-1 px-6 py-3 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition font-semibold"
                >
                    Lưu Check-in
                </button>
                <a
                    href="{{ route('checkins.index') }}"
                    class="px-6 py-3 border-2 border-gray-300 text-gray-700 rounded-lg hover:border-gray-400 transition font-semibold"
                >
                    Xem lịch sử
                </a>
            </div>
        </form>
    </div>

    <script>
        const symptoms = @json($symptoms);
        let symptomCount = 0;

        function addSymptom() {
            const container = document.getElementById('symptoms-container');
            const symptomId = `symptom_${symptomCount++}`;
            
            const symptomDiv = document.createElement('div');
            symptomDiv.className = 'flex gap-3 items-end p-3 bg-gray-50 rounded-lg';
            symptomDiv.id = symptomId;
            
            symptomDiv.innerHTML = `
                <div class="flex-1">
                    <label class="block text-sm font-medium text-gray-700 mb-1">Triệu chứng</label>
                    <select name="symptoms[${symptomCount - 1}][code]" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                        <option value="">Chọn triệu chứng...</option>
                        ${symptoms.map(s => `<option value="${s.code}">${s.display_name}</option>`).join('')}
                    </select>
                </div>
                <div class="w-32">
                    <label class="block text-sm font-medium text-gray-700 mb-1">Mức độ (0-10)</label>
                    <input type="number" name="symptoms[${symptomCount - 1}][severity]" min="0" max="10" value="5" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                </div>
                <button type="button" onclick="removeSymptom('${symptomId}')" class="px-3 py-2 bg-red-100 text-red-700 rounded-lg hover:bg-red-200 transition">
                    Xóa
                </button>
            `;
            
            container.appendChild(symptomDiv);
        }

        function removeSymptom(id) {
            document.getElementById(id).remove();
        }

        // Load existing symptoms if editing
        @if($todayCheckin && $todayCheckin->symptomLogs && $todayCheckin->symptomLogs->count() > 0)
            @foreach($todayCheckin->symptomLogs as $log)
                symptomCount++;
                const container = document.getElementById('symptoms-container');
                const symptomDiv = document.createElement('div');
                symptomDiv.className = 'flex gap-3 items-end p-3 bg-gray-50 rounded-lg';
                const symptomCode = '{{ $log->symptom_code }}';
                const symptomSeverity = {{ $log->severity }};
                symptomDiv.innerHTML = `
                    <div class="flex-1">
                        <label class="block text-sm font-medium text-gray-700 mb-1">Triệu chứng</label>
                        <select name="symptoms[${symptomCount - 1}][code]" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500" required>
                            <option value="">Chọn triệu chứng...</option>
                            ${symptoms.map(s => `<option value="${s.code}" ${s.code === symptomCode ? 'selected' : ''}>${s.display_name}</option>`).join('')}
                        </select>
                    </div>
                    <div class="w-32">
                        <label class="block text-sm font-medium text-gray-700 mb-1">Mức độ (0-10)</label>
                        <input type="number" name="symptoms[${symptomCount - 1}][severity]" min="0" max="10" value="${symptomSeverity}" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500" required>
                    </div>
                    <button type="button" onclick="this.parentElement.remove()" class="px-3 py-2 bg-red-100 text-red-700 rounded-lg hover:bg-red-200 transition">
                        Xóa
                    </button>
                `;
                container.appendChild(symptomDiv);
            @endforeach
        @endif
    </script>
</body>
</html>

