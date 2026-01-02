document.addEventListener('DOMContentLoaded', function() {
    const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    tooltipTriggerList.map(function (tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl);
    });

    const animatedElements = document.querySelectorAll('.animate-on-scroll');
    const observer = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                entry.target.classList.add('animated');
            }
        });
    }, { threshold: 0.1 });

    animatedElements.forEach(el => observer.observe(el));

    const markCompleteButtons = document.querySelectorAll('.mark-complete');
    markCompleteButtons.forEach(btn => {
        btn.addEventListener('click', function() {
            const lessonId = this.dataset.lessonId;
            markLessonComplete(lessonId, this);
        });
    });

    const searchForm = document.querySelector('.search-form');
    if (searchForm) {
        searchForm.addEventListener('submit', function(e) {
            const searchInput = this.querySelector('input[name="q"]');
            if (!searchInput.value.trim()) {
                e.preventDefault();
            }
        });
    }

    const courseFilters = document.querySelectorAll('.course-filter');
    courseFilters.forEach(filter => {
        filter.addEventListener('change', function() {
            filterCourses();
        });
    });

    const paymentForm = document.querySelector('#payment-form');
    if (paymentForm) {
        paymentForm.addEventListener('submit', function(e) {
            const submitBtn = this.querySelector('button[type="submit"]');
            submitBtn.disabled = true;
            submitBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Processing...';
        });
    }

    const passwordToggles = document.querySelectorAll('.password-toggle');
    passwordToggles.forEach(toggle => {
        toggle.addEventListener('click', function() {
            const input = this.previousElementSibling;
            const icon = this.querySelector('i');
            if (input.type === 'password') {
                input.type = 'text';
                icon.classList.replace('fa-eye', 'fa-eye-slash');
            } else {
                input.type = 'password';
                icon.classList.replace('fa-eye-slash', 'fa-eye');
            }
        });
    });

    // Team Carousel Logic
    const teamCarousel = document.getElementById('teamCarousel');
    if (teamCarousel) {
        const cards = teamCarousel.querySelectorAll('.team-card-wrapper');
        const prevBtn = document.getElementById('prevTeam');
        const nextBtn = document.getElementById('nextTeam');
        let currentIndex = 4; // Start with card 5 active

        function updateCarousel() {
            cards.forEach((card, i) => {
                card.classList.remove('active', 'prev', 'next', 'far-prev', 'far-next');
                
                if (i === currentIndex) {
                    card.classList.add('active');
                } else if (i === currentIndex - 1) {
                    card.classList.add('prev');
                } else if (i === currentIndex + 1) {
                    card.classList.add('next');
                } else if (i === currentIndex - 2) {
                    card.classList.add('far-prev');
                } else if (i === currentIndex + 2) {
                    card.classList.add('far-next');
                } else {
                    card.style.opacity = '0';
                    card.style.pointerEvents = 'none';
                }
                
                if (Math.abs(i - currentIndex) <= 2) {
                    card.style.opacity = '';
                    card.style.pointerEvents = 'auto';
                }
            });
        }

        prevBtn.addEventListener('click', () => {
            if (currentIndex > 0) {
                currentIndex--;
                updateCarousel();
            }
        });

        nextBtn.addEventListener('click', () => {
            if (currentIndex < cards.length - 1) {
                currentIndex++;
                updateCarousel();
            }
        });

        updateCarousel();

        // Automatic Movement
        let autoMove = setInterval(() => {
            currentIndex = (currentIndex + 1) % cards.length;
            updateCarousel();
        }, 3000);

        teamCarousel.addEventListener('mouseenter', () => clearInterval(autoMove));
        teamCarousel.addEventListener('mouseleave', () => {
            autoMove = setInterval(() => {
                currentIndex = (currentIndex + 1) % cards.length;
                updateCarousel();
            }, 3000);
        });
    }
});

function markLessonComplete(lessonId, button) {
    const formData = new FormData();
    formData.append('lesson_id', lessonId);
    formData.append('action', 'mark_complete');

    fetch('/pages/api/lesson-progress.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            button.classList.remove('btn-outline-success');
            button.classList.add('btn-success');
            button.innerHTML = '<i class="fas fa-check me-1"></i> Completed';
            button.disabled = true;

            const progressBar = document.querySelector('.course-progress-bar');
            if (progressBar && data.progress) {
                progressBar.style.width = data.progress + '%';
                progressBar.setAttribute('aria-valuenow', data.progress);
                document.querySelector('.progress-text').textContent = data.progress + '%';
            }
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showToast('Error marking lesson complete', 'danger');
    });
}

function filterCourses() {
    const category = document.querySelector('#category-filter')?.value || '';
    const level = document.querySelector('#level-filter')?.value || '';
    const price = document.querySelector('#price-filter')?.value || '';
    
    const params = new URLSearchParams(window.location.search);
    if (category) params.set('cat', category);
    else params.delete('cat');
    if (level) params.set('level', level);
    else params.delete('level');
    if (price) params.set('price', price);
    else params.delete('price');
    
    window.location.href = window.location.pathname + '?' + params.toString();
}

function showToast(message, type = 'success') {
    const toastContainer = document.querySelector('.toast-container') || createToastContainer();
    const toast = document.createElement('div');
    toast.className = `toast align-items-center text-white bg-${type} border-0`;
    toast.setAttribute('role', 'alert');
    toast.innerHTML = `
        <div class="d-flex">
            <div class="toast-body">${message}</div>
            <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button>
        </div>
    `;
    toastContainer.appendChild(toast);
    const bsToast = new bootstrap.Toast(toast);
    bsToast.show();
}

function createToastContainer() {
    const container = document.createElement('div');
    container.className = 'toast-container position-fixed bottom-0 end-0 p-3';
    document.body.appendChild(container);
    return container;
}

function copyCertificateId(id) {
    navigator.clipboard.writeText(id).then(() => {
        showToast('Certificate ID copied!', 'success');
    });
}
