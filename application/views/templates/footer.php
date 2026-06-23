</div>

</div> <!-- End of content div from header -->
    
    <!-- Bootstrap Bundle with Popper -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
    <!-- jQuery -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <!-- Custom JavaScript -->
    <script>
        $(document).ready(function() {
            // Activate tooltips
            var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'))
            var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
                return new bootstrap.Tooltip(tooltipTriggerEl)
            });
            
            // Auto-hide alerts after 5 seconds (except error alerts)
            setTimeout(function() {
                $('.alert:not(.persistent-error)').alert('close');
            }, 1800000);
            
            // Disable auto-dismiss for error alerts
            $('.persistent-error').each(function() {
                // Remove any auto-dismiss functionality
                $(this).css({
                    'animation': 'none',
                    'transition': 'none'
                });
                
                // Ensure the alert stays visible
                $(this).addClass('show').css({
                    'display': 'block',
                    'opacity': '1'
                });
                
                // Override Bootstrap's auto-dismiss if any
                if (typeof bootstrap !== 'undefined') {
                    const alertElement = this;
                    const bsAlert = new bootstrap.Alert(alertElement);
                    // Disable auto-dismiss
                    bsAlert._config.delay = 0;
                    bsAlert._config.autohide = false;
                }
            });
            
            // AJAX setup
            $.ajaxSetup({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                }
            });
            
            // Function to update dermaga status
            $('.update-status').on('click', function(e) {
                e.preventDefault();
                
                var id = $(this).data('id');
                var action = $(this).data('action');
                
                $.ajax({
                    url: '<?= base_url('dashboard/update_transaksi') ?>',
                    type: 'POST',
                    data: {
                        id_transaksi: id,
                        action: action
                    },
                    dataType: 'json',
                    success: function(response) {
                        if (response.status) {
                            location.reload();
                        } else {
                            alert(response.message);
                        }
                    },
                    error: function() {
                        alert('Terjadi kesalahan. Silakan coba lagi.');
                    }
                });
            });
            
            // Function to update volume
            $('.update-volume-form').on('submit', function(e) {
                e.preventDefault();
                
                var form = $(this);
                var id = form.find('input[name="id_transaksi"]').val();
                var volume = form.find('input[name="volume"]').val();
                var liter_per_menit = form.find('input[name="liter_per_menit"]').val();
                
                $.ajax({
                    url: '<?= base_url('dashboard/update_transaksi') ?>',
                    type: 'POST',
                    data: {
                        id_transaksi: id,
                        action: 'update_volume',
                        volume: volume,
                        liter_per_menit: liter_per_menit
                    },
                    dataType: 'json',
                    success: function(response) {
                        if (response.status) {
                            location.reload();
                        } else {
                            alert(response.message);
                        }
                    },
                    error: function() {
                        alert('Terjadi kesalahan. Silakan coba lagi.');
                    }
                });
            });
            
            // Mobile Sidebar Toggle Functionality
            const sidebarToggle = document.getElementById('sidebarToggle');
            const sidebar = document.querySelector('.sidebar');
            const sidebarOverlay = document.getElementById('sidebarOverlay');

            // Pindahkan modal ke body agar posisi tengah presisi di layar penuh
            function moveModalsToBody() {
                document.querySelectorAll('.modal').forEach(function(modalEl) {
                    if (modalEl.parentElement !== document.body) {
                        document.body.appendChild(modalEl);
                    }
                });
            }

            moveModalsToBody();

            document.addEventListener('show.bs.modal', function(e) {
                if (e.target && e.target.classList.contains('modal') && e.target.parentElement !== document.body) {
                    document.body.appendChild(e.target);
                }
                document.body.style.setProperty('padding-right', '0', 'important');
            });
            
            function toggleSidebar() {
                if (!sidebarToggle || !sidebar || !sidebarOverlay) return;
                sidebar.classList.toggle('show');
                sidebarOverlay.classList.toggle('show');
                
                // Change icon based on state
                const icon = sidebarToggle.querySelector('i');
                if (sidebar.classList.contains('show')) {
                    icon.className = 'fas fa-times';
                } else {
                    icon.className = 'fas fa-bars';
                }
            }
            
            function closeSidebar() {
                if (!sidebarToggle || !sidebar || !sidebarOverlay) return;
                sidebar.classList.remove('show');
                sidebarOverlay.classList.remove('show');
                const icon = sidebarToggle.querySelector('i');
                icon.className = 'fas fa-bars';
            }
            
            // Event listeners
            if (sidebarToggle) {
                sidebarToggle.addEventListener('click', toggleSidebar);
            }
            if (sidebarOverlay) {
                sidebarOverlay.addEventListener('click', closeSidebar);
            }
            
            // Close sidebar when clicking on a menu item (mobile)
            document.querySelectorAll('.sidebar a').forEach(link => {
                link.addEventListener('click', function() {
                    if (window.innerWidth <= 768) {
                        closeSidebar();
                    }
                });
            });
            
            // Handle window resize
            window.addEventListener('resize', function() {
                if (window.innerWidth > 768) {
                    closeSidebar();
                }
            });

            // Nonaktifkan kompensasi scrollbar Bootstrap yang merusak layout (html zoom)
            if (typeof bootstrap !== 'undefined' && bootstrap.Modal && bootstrap.Modal.prototype) {
                bootstrap.Modal.prototype._setScrollbar = function() {
                    document.body.classList.add('modal-open');
                    document.body.style.overflow = 'hidden';
                    document.body.style.paddingRight = '0px';
                    document.body.removeAttribute('data-bs-padding-right');
                };

                bootstrap.Modal.prototype._resetScrollbar = function() {
                    document.body.classList.remove('modal-open');
                    document.body.style.overflow = '';
                    document.body.style.paddingRight = '';
                    document.body.removeAttribute('data-bs-padding-right');
                };
            }

            document.addEventListener('hidden.bs.modal', function() {
                if (!document.querySelector('.modal.show')) {
                    document.body.style.removeProperty('padding-right');
                    document.body.style.overflow = '';
                }
            });
        });
    </script>
</body>
</html> 