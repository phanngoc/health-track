// Timeline collapse/expand functionality
document.addEventListener('DOMContentLoaded', function() {
    const toggleButtons = document.querySelectorAll('.moment-toggle-btn');
    
    toggleButtons.forEach(button => {
        button.addEventListener('click', function() {
            const targetId = this.getAttribute('data-target');
            const targetGroup = document.getElementById(targetId);
            const toggleText = this.querySelector('.toggle-text');
            
            if (targetGroup) {
                // Toggle visibility with smooth transition
                if (targetGroup.classList.contains('hidden')) {
                    // Expand
                    targetGroup.classList.remove('hidden');
                    // Force reflow
                    targetGroup.offsetHeight;
                    // Set max-height for transition
                    targetGroup.style.maxHeight = targetGroup.scrollHeight + 'px';
                    targetGroup.style.opacity = '1';
                    toggleText.textContent = '⌃ Thu gọn';
                } else {
                    // Collapse
                    targetGroup.style.maxHeight = '0';
                    targetGroup.style.opacity = '0';
                    setTimeout(() => {
                        targetGroup.classList.add('hidden');
                    }, 300);
                    toggleText.textContent = '⌄ Xem chi tiết';
                }
            }
        });
    });
});

