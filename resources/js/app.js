import 'sweetalert2/dist/sweetalert2.min.css';
import Swal from 'sweetalert2';

window.Swal = Swal;

const Toast = Swal.mixin({
    toast: true,
    position: 'top-end',
    showConfirmButton: false,
    timer: 3000,
    timerProgressBar: true,
    didOpen: (toast) => {
        toast.addEventListener('mouseenter', Swal.stopTimer);
        toast.addEventListener('mouseleave', Swal.resumeTimer);
    }
});

window.Toast = Toast;

window.showSuccess = function(message, title = 'Success!') {
    return Swal.fire({
        icon: 'success',
        title: title,
        text: message,
        confirmButtonText: 'OK',
        timer: 3000,
        timerProgressBar: true
    });
};

window.showError = function(message, title = 'Error!') {
    return Swal.fire({
        icon: 'error',
        title: title,
        text: message,
        confirmButtonText: 'OK'
    });
};

window.showWarning = function(message, title = 'Warning!') {
    return Swal.fire({
        icon: 'warning',
        title: title,
        text: message,
        confirmButtonText: 'OK'
    });
};

window.showInfo = function(message, title = 'Info') {
    return Swal.fire({
        icon: 'info',
        title: title,
        text: message,
        confirmButtonText: 'OK'
    });
};

window.showConfirm = function(message, title = 'Are you sure?') {
    return Swal.fire({
        title: title,
        text: message,
        icon: 'warning',
        showCancelButton: true,
        confirmButtonText: 'Yes, do it!',
        cancelButtonText: 'Cancel',
        reverseButtons: true
    });
};

window.showToast = function(message, type = 'success') {
    return Toast.fire({
        icon: type,
        title: message
    });
};

window.showLoading = function(message = 'Please wait...') {
    Swal.fire({
        title: message,
        allowOutsideClick: false,
        allowEscapeKey: false,
        showConfirmButton: false,
        didOpen: () => {
            Swal.showLoading();
        }
    });
};

window.closeLoading = function() {
    Swal.close();
};

document.addEventListener('DOMContentLoaded', function() {
    if (typeof $ !== 'undefined' && typeof jQuery !== 'undefined') {
        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            }
        });

        $(document).ajaxStart(function() {
            console.log('AJAX request started');
        });

        $(document).ajaxStop(function() {
            console.log('AJAX request completed');
        });

        $(document).ajaxError(function(event, jqxhr, settings, thrownError) {
            console.error('AJAX Error:', {
                url: settings.url,
                status: jqxhr.status,
                error: thrownError
            });

            if (jqxhr.status === 419) {
                showError('Your session has expired. Please refresh the page.', 'Session Expired');
            } else if (jqxhr.status === 500) {
                showError('An internal server error occurred. Please try again later.', 'Server Error');
            }
        });
    }

    const sidebar = document.getElementById('sidebar');
    const sidebarOverlay = document.getElementById('sidebar-overlay');
    const sidebarToggle = document.getElementById('sidebar-toggle');
    const sidebarMinimize = document.getElementById('sidebar-minimize');
    const sidebarCloseMobile = document.getElementById('sidebar-close-mobile');

    if (sidebarToggle) {
        sidebarToggle.addEventListener('click', function(e) {
            e.preventDefault();
            e.stopPropagation();
            
            if (sidebar) {
                sidebar.classList.remove('-translate-x-full');
            }
            if (sidebarOverlay) {
                sidebarOverlay.classList.remove('hidden');
                sidebarOverlay.classList.add('flex');
            }
            document.body.style.overflow = 'hidden';
        });
    }

    if (sidebarCloseMobile) {
        sidebarCloseMobile.addEventListener('click', function(e) {
            e.preventDefault();
            e.stopPropagation();
            closeMobileSidebar();
        });
    }

    if (sidebarOverlay) {
        sidebarOverlay.addEventListener('click', function(e) {
            e.preventDefault();
            e.stopPropagation();
            closeMobileSidebar();
        });
    }

    function closeMobileSidebar() {
        if (sidebar) {
            sidebar.classList.add('-translate-x-full');
        }
        if (sidebarOverlay) {
            sidebarOverlay.classList.remove('flex');
            sidebarOverlay.classList.add('hidden');
        }
        document.body.style.overflow = '';
    }

    if (sidebarMinimize && sidebar) {
        sidebarMinimize.addEventListener('click', function(e) {
            e.preventDefault();
            e.stopPropagation();
            
            const isMinimized = sidebar.classList.contains('minimized');
            
            if (isMinimized) {
                sidebar.classList.remove('minimized');
                localStorage.setItem('sidebarMinimized', 'false');
            } else {
                sidebar.classList.add('minimized');
                localStorage.setItem('sidebarMinimized', 'true');
            }
            
            const icon = sidebarMinimize.querySelector('svg');
            if (icon) {
                if (sidebar.classList.contains('minimized')) {
                    icon.style.transform = 'rotate(180deg)';
                } else {
                    icon.style.transform = 'rotate(0deg)';
                }
            }
        });

        const savedState = localStorage.getItem('sidebarMinimized');
        if (savedState === 'true' && window.innerWidth >= 768) {
            sidebar.classList.add('minimized');
            const icon = sidebarMinimize.querySelector('svg');
            if (icon) {
                icon.style.transform = 'rotate(180deg)';
            }
        }
    }

    window.addEventListener('resize', function() {
        if (window.innerWidth >= 768) {
            if (sidebarOverlay) {
                sidebarOverlay.classList.remove('flex');
                sidebarOverlay.classList.add('hidden');
            }
            document.body.style.overflow = '';
            
            if (sidebar) {
                sidebar.classList.remove('-translate-x-full');
            }
        } else {
            if (sidebar) {
                sidebar.classList.remove('minimized');
                sidebar.classList.add('-translate-x-full');
            }
            
            const icon = sidebarMinimize?.querySelector('svg');
            if (icon) {
                icon.style.transform = 'rotate(0deg)';
            }
        }
    });

    const navItems = document.querySelectorAll('.nav-item');
    navItems.forEach(item => {
        item.addEventListener('click', function() {
            navItems.forEach(nav => nav.classList.remove('active'));
            this.classList.add('active');
        });
    });
});