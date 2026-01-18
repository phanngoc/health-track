(function() {
    let currentStep = 1;
    let selectedMood = null;
    let selectedTags = [];
    const maxTags = 2;

    // Modal elements
    const modal = document.getElementById('checkin-modal');
    const modalContent = document.getElementById('modal-content');
    const checkinBtn = document.getElementById('checkin-btn');

    // Load modal content
    function loadModalContent() {
        fetch('/checkins/modal-content')
            .then(response => response.text())
            .then(html => {
                modalContent.innerHTML = html;
                initializeModal();
            })
            .catch(error => {
                console.error('Error loading modal:', error);
                // Fallback: use inline content if available
                if (modalContent.querySelector('.step-content')) {
                    initializeModal();
                }
            });
    }

    // Initialize modal functionality
    function initializeModal() {
        // Emoji selection
        const emojiBtns = document.querySelectorAll('.emoji-btn');
        const continueStep1 = document.getElementById('continue-step-1');

        emojiBtns.forEach(btn => {
            btn.addEventListener('click', function() {
                // Remove previous selection
                emojiBtns.forEach(b => {
                    b.style.transform = 'scale(1)';
                    b.style.opacity = '1';
                });

                // Select this emoji
                selectedMood = this.dataset.mood;
                this.style.transform = 'scale(1.1)';
                emojiBtns.forEach(b => {
                    if (b !== this) {
                        b.style.opacity = '0.4';
                    }
                });

                // Enable continue button
                if (continueStep1) {
                    continueStep1.disabled = false;
                    continueStep1.classList.remove('bg-gray-300', 'text-gray-500');
                    continueStep1.classList.add('bg-blue-600', 'text-white', 'hover:bg-blue-700');
                }
            });
        });

        // Tag selection
        const tagBtns = document.querySelectorAll('.tag-btn');
        const continueStep2 = document.getElementById('continue-step-2');
        const skipStep2 = document.getElementById('skip-step-2');

        tagBtns.forEach(btn => {
            btn.addEventListener('click', function() {
                const tag = this.dataset.tag;
                const index = selectedTags.indexOf(tag);

                if (index > -1) {
                    // Deselect
                    selectedTags.splice(index, 1);
                    this.classList.remove('bg-blue-600', 'text-white');
                    this.classList.add('bg-gray-100', 'text-gray-700');
                } else {
                    // Select (if under limit)
                    if (selectedTags.length < maxTags) {
                        selectedTags.push(tag);
                        this.classList.remove('bg-gray-100', 'text-gray-700');
                        this.classList.add('bg-blue-600', 'text-white');
                    }
                }
            });
        });

        // Step navigation
        if (continueStep1) {
            continueStep1.addEventListener('click', () => {
                showStep(2);
            });
        }

        if (continueStep2) {
            continueStep2.addEventListener('click', () => {
                submitCheckIn();
            });
        }

        if (skipStep2) {
            skipStep2.addEventListener('click', () => {
                selectedTags = [];
                submitCheckIn();
            });
        }
    }

    // Show specific step
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

    // Submit check-in
    function submitCheckIn() {
        if (!selectedMood) {
            return;
        }

        const data = {
            mood: selectedMood,
            tags: selectedTags,
            _token: document.querySelector('meta[name="csrf-token"]')?.content || 
                   document.querySelector('input[name="_token"]')?.value
        };

        fetch('/checkins/quick', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': data._token,
                'Accept': 'application/json'
            },
            body: JSON.stringify({
                mood: data.mood,
                tags: data.tags
            })
        })
        .then(response => response.json())
        .then(result => {
            if (result.success) {
                showStep(3);
                // Auto-dismiss after 600ms
                setTimeout(() => {
                    closeModal();
                    // Reload page to show new check-in
                    window.location.reload();
                }, 600);
            } else {
                alert('Có lỗi xảy ra. Vui lòng thử lại.');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Có lỗi xảy ra. Vui lòng thử lại.');
        });
    }

    // Open modal
    function openModal() {
        modal.classList.remove('hidden');
        document.body.style.overflow = 'hidden';
        currentStep = 1;
        selectedMood = null;
        selectedTags = [];
        
        // Try to load content or use existing
        if (!modalContent.querySelector('.step-content')) {
            loadModalContent();
        } else {
            initializeModal();
            showStep(1);
        }
    }

    // Close modal
    function closeModal() {
        modal.classList.add('hidden');
        document.body.style.overflow = '';
    }

    // Event listeners
    if (checkinBtn) {
        checkinBtn.addEventListener('click', openModal);
    }

    // Close on backdrop click
    if (modal) {
        modal.addEventListener('click', function(e) {
            if (e.target === modal) {
                closeModal();
            }
        });
    }

    // Close on escape key
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape' && !modal.classList.contains('hidden')) {
            closeModal();
        }
    });
})();

