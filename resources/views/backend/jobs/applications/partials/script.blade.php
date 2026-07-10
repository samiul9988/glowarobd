<script>
    /* Applicant Details — Interactions */
    (function() {
        'use strict';

        // ==== Modal open/close ====
        function openModal(id) {
            var m = document.getElementById(id);
            if (!m) return;
            m.classList.add('open');
            document.body.style.overflow = 'hidden';
        }

        function closeModal(m) {
            m.classList.remove('open');
            document.body.style.overflow = '';
        }
        document.querySelectorAll('[data-open-modal]').forEach(function(b) {
            b.addEventListener('click', function() {
                openModal(b.getAttribute('data-open-modal'));
            });
        });
        document.querySelectorAll('.modal-overlay').forEach(function(m) {
            m.addEventListener('click', function(e) {
                if (e.target === m || e.target.hasAttribute('data-close-modal')) closeModal(m);
            });
        });
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') {
                document.querySelectorAll('.modal-overlay.open').forEach(closeModal);
            }
        });
    })();
</script>
