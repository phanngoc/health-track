<!-- Step 1: Mood Selection -->
<div id="step-1" class="step-content">
    <div class="text-center py-8">
        <h2 class="text-xl font-semibold text-gray-900 mb-8">Hôm nay bạn thế nào?</h2>
        
        <div class="flex justify-center items-center gap-6 mb-8">
            <button 
                type="button"
                class="emoji-btn w-16 h-16 text-5xl flex items-center justify-center rounded-full transition-all hover:scale-110"
                data-mood="🙂"
            >
                🙂
            </button>
            <button 
                type="button"
                class="emoji-btn w-16 h-16 text-5xl flex items-center justify-center rounded-full transition-all hover:scale-110"
                data-mood="😐"
            >
                😐
            </button>
            <button 
                type="button"
                class="emoji-btn w-16 h-16 text-5xl flex items-center justify-center rounded-full transition-all hover:scale-110"
                data-mood="😴"
            >
                😴
            </button>
            <button 
                type="button"
                class="emoji-btn w-16 h-16 text-5xl flex items-center justify-center rounded-full transition-all hover:scale-110"
                data-mood="😣"
            >
                😣
            </button>
            <button 
                type="button"
                class="emoji-btn w-16 h-16 text-5xl flex items-center justify-center rounded-full transition-all hover:scale-110"
                data-mood="😄"
            >
                😄
            </button>
        </div>
        
        <button 
            id="continue-step-1"
            disabled
            class="w-full py-4 bg-gray-300 text-gray-500 rounded-xl font-semibold disabled:opacity-50 disabled:cursor-not-allowed"
        >
            Tiếp tục
        </button>
    </div>
</div>

<!-- Step 2: Quick Context -->
<div id="step-2" class="step-content hidden">
    <div class="py-8">
        <h2 class="text-lg font-semibold text-gray-900 mb-6 text-center">Điều gì đang ảnh hưởng bạn?</h2>
        
        <div class="grid grid-cols-2 gap-3 mb-6">
            <button 
                type="button"
                class="tag-btn px-4 py-3 bg-gray-100 text-gray-700 rounded-full font-medium transition-all hover:bg-gray-200"
                data-tag="🏃‍♂️"
            >
                🏃‍♂️ Vận động
            </button>
            <button 
                type="button"
                class="tag-btn px-4 py-3 bg-gray-100 text-gray-700 rounded-full font-medium transition-all hover:bg-gray-200"
                data-tag="🍺"
            >
                🍺 Rượu bia
            </button>
            <button 
                type="button"
                class="tag-btn px-4 py-3 bg-gray-100 text-gray-700 rounded-full font-medium transition-all hover:bg-gray-200"
                data-tag="😴"
            >
                😴 Thiếu ngủ
            </button>
            <button 
                type="button"
                class="tag-btn px-4 py-3 bg-gray-100 text-gray-700 rounded-full font-medium transition-all hover:bg-gray-200"
                data-tag="💼"
            >
                💼 Công việc
            </button>
            <button 
                type="button"
                class="tag-btn px-4 py-3 bg-gray-100 text-gray-700 rounded-full font-medium transition-all hover:bg-gray-200"
                data-tag="🤒"
            >
                🤒 Sức khỏe
            </button>
            <button 
                type="button"
                class="tag-btn px-4 py-3 bg-gray-100 text-gray-700 rounded-full font-medium transition-all hover:bg-gray-200"
                data-tag="❤️"
            >
                ❤️ Gia đình
            </button>
        </div>
        
        <p class="text-xs text-gray-500 text-center mb-6">(chọn tối đa 2)</p>
        
        <div class="space-y-3">
            <button 
                id="continue-step-2"
                class="w-full py-4 bg-blue-600 text-white rounded-xl font-semibold hover:bg-blue-700"
            >
                Hoàn tất
            </button>
            <button 
                id="skip-step-2"
                type="button"
                class="w-full py-4 bg-gray-100 text-gray-700 rounded-xl font-semibold hover:bg-gray-200"
            >
                Bỏ qua
            </button>
        </div>
    </div>
</div>

<!-- Step 3: Confirmation -->
<div id="step-3" class="step-content hidden">
    <div class="text-center py-16">
        <div class="text-6xl mb-6">✅</div>
        <h2 class="text-xl font-semibold text-gray-900 mb-2">Đã lưu check-in</h2>
    </div>
</div>

