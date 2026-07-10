<style>
    /* Floating Action Button */
    .fab {
        position: fixed;
        bottom: 10px;
        right: 30px;
        width: 55px;
        height: 55px;
        background: linear-gradient(45deg, #ff6b6b, #ee5a24);
        border: none;
        border-radius: 50%;
        box-shadow: 0 8px 25px rgba(238, 90, 36, 0.4);
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 21px;
        color: white;
        cursor: pointer;
        transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        z-index: 1060;
    }

    .fab:hover {
        transform: scale(1.1) rotate(90deg);
        box-shadow: 0 12px 35px rgba(238, 90, 36, 0.6);
    }

    .fab:active {
        transform: scale(0.95);
    }

    .fab i {
        transition: transform 0.3s ease;
    }

    .fab.active i {
        transform: rotate(45deg);
    }

    /* Custom Offcanvas for Bootstrap 4 */
    .offcanvas {
        position: fixed;
        top: 0;
        right: -500px;
        width: 500px;
        height: 100vh;
        background: white;
        box-shadow: -5px 0 15px rgba(0, 0, 0, 0.1);
        transition: right 0.3s ease;
        z-index: 1050;
        overflow-y: auto;
    }

    .offcanvas.show {
        right: 0;
    }

    .offcanvas-backdrop {
        position: fixed;
        top: 0;
        left: 0;
        width: 100vw;
        height: 100vh;
        background: rgba(0, 0, 0, 0.5);
        opacity: 0;
        visibility: hidden;
        transition: all 0.3s ease;
        z-index: 1030;
    }

    .offcanvas-backdrop.show {
        opacity: 1;
        visibility: visible;
    }

    .offcanvas-header {
        padding: 0.5rem;
        border-bottom: 1px solid #dee2e6;
        display: flex;
        align-items: center;
        justify-content: space-between;
        background: linear-gradient(135deg, #667eea, #764ba2);
        color: white;
    }

    .offcanvas-title {
        margin: 0;
        font-size: 1.25rem;
        font-weight: 600;
    }

    .btn-close {
        background: none;
        border: none;
        font-size: 1.5rem;
        color: white;
        opacity: 0.8;
        cursor: pointer;
        padding: 0;
        width: 30px;
        height: 30px;
        display: flex;
        align-items: center;
        justify-content: center;
        border-radius: 50%;
        transition: all 0.2s ease;
    }

    .btn-close:hover {
        opacity: 1;
        background: rgba(255, 255, 255, 0.1);
    }

    .offcanvas-body {
        padding: 1.5rem;
    }

    /* Sample content styling */
    .content-card {
        background: white;
        border-radius: 12px;
        padding: 2rem;
        margin-bottom: 2rem;
        box-shadow: 0 4px 20px rgba(0, 0, 0, 0.1);
    }

    .feature-item {
        display: flex;
        align-items: center;
        padding: 1rem;
        margin-bottom: 1rem;
        background: #f8f9fa;
        border-radius: 8px;
        transition: transform 0.2s ease;
    }

    .feature-item:hover {
        transform: translateX(5px);
    }

    .feature-icon {
        width: 40px;
        height: 40px;
        background: linear-gradient(45deg, #667eea, #764ba2);
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        color: white;
        margin-right: 1rem;
        font-size: 18px;
    }

    @media (max-width: 768px) {
        .offcanvas {
            width: 100%;
            right: -100%;
        }
    }
</style>
<!-- Floating Action Button -->
<button class="fab" id="fabBtn" aria-label="Open Canvas" style="display: {{ @$show ? 'block' : 'none' }};" title="{{ @$title }}">
    <i class="las la-history"></i>
</button>

<!-- Offcanvas Backdrop -->
<div class="offcanvas-backdrop" id="offcanvasBackdrop"></div>

<!-- Offcanvas -->
<div class="offcanvas" id="offcanvasMenu">
    <div class="offcanvas-header">
        <h5 class="offcanvas-title">
            <i class="las la-bars mr-2"></i>
            {{ @$header ?? 'Actions' }}
        </h5>
        <button class="btn-close" id="closeBtn" aria-label="Close">
            <i class="las la-times"></i>
        </button>
    </div>
    <div class="offcanvas-body" id="offcanvas-body">
        <div id="success-rate">
            @include('backend.components.customer-success-rate-preloader')
        </div>

        <div class="accordion" id="accordionExample">
            @include('backend.components.recent-orders-list-preloader')
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const fabBtn = document.getElementById('fabBtn');
        const offcanvasMenu = document.getElementById('offcanvasMenu');
        const offcanvasBackdrop = document.getElementById('offcanvasBackdrop');
        const closeBtn = document.getElementById('closeBtn');

        function openOffcanvas() {
            offcanvasMenu.classList.add('show');
            offcanvasBackdrop.classList.add('show');
            fabBtn.classList.add('active');
            document.body.style.overflow = 'hidden';
        }

        function closeOffcanvas() {
            offcanvasMenu.classList.remove('show');
            offcanvasBackdrop.classList.remove('show');
            fabBtn.classList.remove('active');
            document.body.style.overflow = '';
        }

        // Toggle offcanvas when FAB is clicked
        fabBtn.addEventListener('click', function() {
            if (offcanvasMenu.classList.contains('show')) {
                closeOffcanvas();
            } else {
                openOffcanvas();
            }
        });

        // Close offcanvas when close button is clicked
        closeBtn.addEventListener('click', closeOffcanvas);

        // Close offcanvas when backdrop is clicked
        offcanvasBackdrop.addEventListener('click', closeOffcanvas);

        // Close offcanvas when escape key is pressed
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape' && offcanvasMenu.classList.contains('show')) {
                closeOffcanvas();
            }
        });
    });
    function showFloatingButton(status) {
        fabBtn.style.display = status ? 'block' : 'none';
    }

    function offcanvasContent(content = '', type = '') {
        const offcanvasBody = document.getElementById('offcanvas-body');
        const successRateContainer = document.getElementById('success-rate');
        const recentOrdersContainer = document.getElementById('accordionExample');
        if(type == 'success-rate') {
            successRateContainer.innerHTML = '';
            if(content.trim() !== '') {
                successRateContainer.innerHTML += '<h6>Customer Success Rate</h6>';
                successRateContainer.innerHTML += content;
            }
        } else if(type == 'recent-orders') {
            recentOrdersContainer.innerHTML = '';
            if(content.trim() !== '') {
                recentOrdersContainer.innerHTML += '<h6>Recent Orders</h6>';
                recentOrdersContainer.innerHTML += content;
            }
        } else {
            offcanvasBody.innerHTML = '';
            offcanvasBody.innerHTML = content;
        }
    }
</script>
