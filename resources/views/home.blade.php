<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Health Tracker - {{ config('app.name', 'Laravel') }}</title>
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=instrument-sans:400,500,600,700" rel="stylesheet" />
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        @keyframes slideUp {
            from {
                transform: translateY(100%);
                opacity: 0;
            }
            to {
                transform: translateY(0);
                opacity: 1;
            }
        }
        .modal-enter {
            animation: slideUp 0.3s ease-out;
        }
    </style>
</head>
<body class="bg-white min-h-screen">
    <div class="max-w-md mx-auto min-h-screen flex flex-col" style="max-width: 390px;">
        @auth
            <!-- Header Section -->
            <header class="px-4 pt-6 pb-4">
                <div>
                    <h1 class="text-2xl font-semibold text-gray-900 mb-1">
                        Chào {{ $user->name }} 👋
                    </h1>
                    <p class="text-gray-600 text-sm">
                        Hôm nay • {{ now()->locale('vi')->translatedFormat('l, d/m') }}
                    </p>
                </div>
            </header>

            <!-- Check-in CTA Section -->
            <div class="px-4 mb-6">
                <button 
                    id="checkin-btn"
                    class="w-full py-4 bg-blue-600 text-white rounded-xl font-semibold text-lg hover:bg-blue-700 transition shadow-lg"
                >
                    {{ $hasTodayCheckin ? 'Check-in thêm' : 'Check-in hôm nay' }}
                </button>
            </div>

            <!-- Timeline Preview Section -->
            <div class="flex-1 px-4 pb-6 overflow-y-auto">
                <h2 class="text-lg font-semibold text-gray-900 mb-4">Timeline</h2>
                
                @if($recentCheckins->count() > 0)
                    <div class="space-y-3">
                        @foreach($recentCheckins as $checkin)
                            <div class="bg-gray-50 rounded-lg p-3 border border-gray-200">
                                <div class="flex items-start gap-3">
                                    <!-- Time indicator -->
                                    <div class="flex-shrink-0">
                                        @if($checkin->checkin_date->isToday())
                                            <span class="text-green-500 text-sm">🟢</span>
                                        @else
                                            <span class="text-gray-400 text-sm">⚪</span>
                                        @endif
                                    </div>
                                    
                                    <!-- Content -->
                                    <div class="flex-1 min-w-0">
                                        <div class="flex items-center gap-2 mb-1">
                                            <span class="text-sm text-gray-500">
                                                {{ $checkin->created_at->format('H:i') }}
                                            </span>
                                            @if($checkin->checkin_date->isToday())
                                                <span class="text-xs text-gray-400">Hôm nay</span>
                                            @elseif($checkin->checkin_date->isYesterday())
                                                <span class="text-xs text-gray-400">Hôm qua</span>
                                            @else
                                                <span class="text-xs text-gray-400">
                                                    {{ $checkin->checkin_date->format('d/m') }}
                                                </span>
                                            @endif
                                        </div>
                                        
                                        <div class="flex items-center gap-2 mb-1">
                                            @if($checkin->mood)
                                                <span class="text-2xl">{{ $checkin->mood }}</span>
                                                <span class="text-sm text-gray-700">
                                                    @if($checkin->mood === '🙂') Cảm thấy ổn
                                                    @elseif($checkin->mood === '😐') Bình thường
                                                    @elseif($checkin->mood === '😴') Hơi mệt
                                                    @elseif($checkin->mood === '😣') Không khỏe
                                                    @elseif($checkin->mood === '😄') Rất tốt
                                                    @endif
                                                </span>
                                            @else
                                                <span class="text-sm text-gray-700">Check-in</span>
                                            @endif
                                        </div>
                                        
                                        @if($checkin->tags && count($checkin->tags) > 0)
                                            <div class="flex items-center gap-1 mt-1">
                                                @foreach($checkin->tags as $tag)
                                                    <span class="text-lg">{{ $tag }}</span>
                                                @endforeach
                                            </div>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                    
                    <div class="mt-6 text-center">
                        <a href="{{ route('timeline.index') }}" 
                           class="text-blue-600 text-sm font-medium hover:text-blue-700">
                            Xem timeline đầy đủ →
                        </a>
                    </div>
                @else
                    <div class="text-center py-12">
                        <div class="text-4xl mb-4">📅</div>
                        <p class="text-gray-600 mb-4">Chưa có check-in nào</p>
                        <p class="text-sm text-gray-500">Bắt đầu check-in để xem timeline</p>
                    </div>
                @endif
            </div>
        @else
            <!-- Guest view -->
            <div class="flex-1 flex flex-col items-center justify-center px-4 py-12">
                <h1 class="text-3xl font-bold text-gray-900 mb-4">Health Tracker</h1>
                <p class="text-gray-600 text-center mb-8">
                    Theo dõi sức khỏe của bạn mỗi ngày
                </p>
                <div class="w-full space-y-3">
                    <a href="{{ route('login') }}" 
                       class="block w-full py-4 bg-blue-600 text-white rounded-xl font-semibold text-center hover:bg-blue-700 transition">
                        Đăng nhập
                    </a>
                    <a href="{{ route('register') }}" 
                       class="block w-full py-4 border-2 border-gray-300 text-gray-700 rounded-xl font-semibold text-center hover:border-gray-400 transition">
                        Đăng ký
                    </a>
                </div>
            </div>
        @endauth
    </div>

    <!-- Check-in Modal -->
    @auth
    <div id="checkin-modal" class="fixed inset-0 bg-black bg-opacity-50 z-50 hidden">
        <div class="absolute bottom-0 left-0 right-0 bg-white rounded-t-3xl modal-enter max-w-md mx-auto" style="max-width: 390px;">
            <div id="modal-content" class="p-6">
                @include('checkins.modal', ['symptoms' => $symptoms ?? collect()])
            </div>
        </div>
    </div>

    <script>
        (function() {
            let currentStep = 1;
            let selectedMood = null;
            let selectedTags = [];
            let selectedSymptoms = [];
            const maxTags = 2;

            const modal = document.getElementById('checkin-modal');
            const checkinBtn = document.getElementById('checkin-btn');

            // Store event handlers to prevent duplicates
            let emojiClickHandler = null;
            let tagClickHandler = null;
            let symptomCheckboxHandler = null;
            let severityChangeHandler = null;
            let continueStep1Handler = null;
            let continueStep2Handler = null;
            let skipStep2Handler = null;
            let continueStep3Handler = null;
            let skipStep3Handler = null;

            function initializeModal() {
                const emojiBtns = document.querySelectorAll('.emoji-btn');
                const continueStep1 = document.getElementById('continue-step-1');

                // Remove old listeners if they exist
                if (emojiClickHandler) {
                    emojiBtns.forEach(btn => {
                        btn.removeEventListener('click', emojiClickHandler);
                    });
                }

                // Create new handler
                emojiClickHandler = function() {
                    emojiBtns.forEach(b => {
                        b.style.transform = 'scale(1)';
                        b.style.opacity = '1';
                    });

                    selectedMood = this.dataset.mood;
                    this.style.transform = 'scale(1.1)';
                    emojiBtns.forEach(b => {
                        if (b !== this) {
                            b.style.opacity = '0.4';
                        }
                    });

                    if (continueStep1) {
                        continueStep1.disabled = false;
                        continueStep1.classList.remove('bg-gray-300', 'text-gray-500');
                        continueStep1.classList.add('bg-blue-600', 'text-white', 'hover:bg-blue-700');
                    }
                };

                emojiBtns.forEach(btn => {
                    btn.addEventListener('click', emojiClickHandler);
                });

                const tagBtns = document.querySelectorAll('.tag-btn');
                const continueStep2 = document.getElementById('continue-step-2');
                const skipStep2 = document.getElementById('skip-step-2');

                // Remove old listeners if they exist
                if (tagClickHandler) {
                    tagBtns.forEach(btn => {
                        btn.removeEventListener('click', tagClickHandler);
                    });
                }

                // Create new handler
                tagClickHandler = function() {
                    const tag = this.dataset.tag;
                    const index = selectedTags.indexOf(tag);

                    if (index > -1) {
                        selectedTags.splice(index, 1);
                        this.classList.remove('bg-blue-600', 'text-white');
                        this.classList.add('bg-gray-100', 'text-gray-700');
                    } else {
                        if (selectedTags.length < maxTags) {
                            selectedTags.push(tag);
                            this.classList.remove('bg-gray-100', 'text-gray-700');
                            this.classList.add('bg-blue-600', 'text-white');
                        }
                    }
                };

                tagBtns.forEach(btn => {
                    btn.addEventListener('click', tagClickHandler);
                });

                // Remove old listeners
                if (continueStep1 && continueStep1Handler) {
                    continueStep1.removeEventListener('click', continueStep1Handler);
                }
                if (continueStep2 && continueStep2Handler) {
                    continueStep2.removeEventListener('click', continueStep2Handler);
                }
                if (skipStep2 && skipStep2Handler) {
                    skipStep2.removeEventListener('click', skipStep2Handler);
                }

                // Create new handlers
                if (continueStep1) {
                    continueStep1Handler = () => showStep(2);
                    continueStep1.addEventListener('click', continueStep1Handler);
                }

                if (continueStep2) {
                    continueStep2Handler = () => showStep(3);
                    continueStep2.addEventListener('click', continueStep2Handler);
                }

                if (skipStep2) {
                    skipStep2Handler = () => {
                        selectedTags = [];
                        showStep(3);
                    };
                    skipStep2.addEventListener('click', skipStep2Handler);
                }

                // Symptom selection handlers
                const symptomCheckboxes = document.querySelectorAll('.symptom-checkbox');
                const severitySelects = document.querySelectorAll('.severity-select');

                // Remove old listeners if they exist
                if (symptomCheckboxHandler) {
                    symptomCheckboxes.forEach(cb => {
                        cb.removeEventListener('change', symptomCheckboxHandler);
                    });
                }
                if (severityChangeHandler) {
                    severitySelects.forEach(select => {
                        select.removeEventListener('change', severityChangeHandler);
                    });
                }

                // Create new handlers
                symptomCheckboxHandler = function() {
                    const code = this.dataset.symptomCode;
                    const name = this.dataset.symptomName;
                    const severityDiv = this.closest('.symptom-item').querySelector('.symptom-severity');
                    const severitySelect = severityDiv.querySelector('.severity-select');

                    if (this.checked) {
                        severityDiv.classList.remove('hidden');
                        const severity = parseInt(severitySelect.value);
                        selectedSymptoms.push({
                            code: code,
                            name: name,
                            severity: severity
                        });
                    } else {
                        severityDiv.classList.add('hidden');
                        selectedSymptoms = selectedSymptoms.filter(s => s.code !== code);
                    }
                };

                severityChangeHandler = function() {
                    const code = this.dataset.symptomCode;
                    const severity = parseInt(this.value);
                    const symptom = selectedSymptoms.find(s => s.code === code);
                    if (symptom) {
                        symptom.severity = severity;
                    }
                };

                symptomCheckboxes.forEach(cb => {
                    cb.addEventListener('change', symptomCheckboxHandler);
                });

                severitySelects.forEach(select => {
                    select.addEventListener('change', severityChangeHandler);
                });

                // Step 3 handlers
                const continueStep3 = document.getElementById('continue-step-3');
                const skipStep3 = document.getElementById('skip-step-3');

                // Remove old listeners
                if (continueStep3 && continueStep3Handler) {
                    continueStep3.removeEventListener('click', continueStep3Handler);
                }
                if (skipStep3 && skipStep3Handler) {
                    skipStep3.removeEventListener('click', skipStep3Handler);
                }

                // Create new handlers
                if (continueStep3) {
                    continueStep3Handler = () => submitCheckIn();
                    continueStep3.addEventListener('click', continueStep3Handler);
                }

                if (skipStep3) {
                    skipStep3Handler = () => {
                        selectedSymptoms = [];
                        submitCheckIn();
                    };
                    skipStep3.addEventListener('click', skipStep3Handler);
                }
            }

            function showStep(step) {
                currentStep = step;
                const steps = document.querySelectorAll('.step-content');
                steps.forEach((s, index) => {
                    if (index + 1 === step) {
                        s.classList.remove('hidden');
                    } else {
                        s.classList.add('hidden');
                    }
                });
            }

            function submitCheckIn() {
                console.log('submitCheckIn called, selectedMood:', selectedMood, 'selectedTags:', selectedTags, 'selectedSymptoms:', selectedSymptoms);
                
                if (!selectedMood) {
                    console.error('No mood selected!');
                    alert('Vui lòng chọn mood trước khi hoàn tất.');
                    return;
                }

                const token = document.querySelector('meta[name="csrf-token"]')?.content || 
                             '{{ csrf_token() }}';

                // Disable buttons during submission
                const continueStep3 = document.getElementById('continue-step-3');
                const skipStep3 = document.getElementById('skip-step-3');
                if (continueStep3) continueStep3.disabled = true;
                if (skipStep3) skipStep3.disabled = true;

                // Format symptoms for submission
                const symptoms = selectedSymptoms.map(s => ({
                    code: s.code,
                    severity: s.severity
                }));

                fetch('/checkins/quick', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': token,
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({
                        mood: selectedMood,
                        tags: selectedTags,
                        symptoms: symptoms
                    })
                })
                .then(response => {
                    console.log('Response status:', response.status);
                    return response.json();
                })
                .then(result => {
                    console.log('Response result:', result);
                    if (result.success) {
                        showStep(4);
                        setTimeout(() => {
                            closeModal();
                            window.location.reload();
                        }, 600);
                    } else {
                        alert('Có lỗi xảy ra. Vui lòng thử lại.');
                        if (continueStep3) continueStep3.disabled = false;
                        if (skipStep3) skipStep3.disabled = false;
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('Có lỗi xảy ra. Vui lòng thử lại.');
                    if (continueStep3) continueStep3.disabled = false;
                    if (skipStep3) skipStep3.disabled = false;
                });
            }

            function openModal() {
                modal.classList.remove('hidden');
                document.body.style.overflow = 'hidden';
                currentStep = 1;
                selectedMood = null;
                selectedTags = [];
                selectedSymptoms = [];
                showStep(1);
                initializeModal();
            }

            function closeModal() {
                modal.classList.add('hidden');
                document.body.style.overflow = '';
            }

            if (checkinBtn) {
                checkinBtn.addEventListener('click', openModal);
            }

            if (modal) {
                modal.addEventListener('click', function(e) {
                    if (e.target === modal) {
                        closeModal();
                    }
                });
            }

            document.addEventListener('keydown', function(e) {
                if (e.key === 'Escape' && !modal.classList.contains('hidden')) {
                    closeModal();
                }
            });
        })();
    </script>
    @endauth
</body>
</html>
