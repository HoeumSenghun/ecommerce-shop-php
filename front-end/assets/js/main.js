// Main JavaScript for Bun Deth Eco Shop

document.addEventListener('DOMContentLoaded', function() {
    // Initialize tooltips
    var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl);
    });

    // Initialize popovers
    var popoverTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="popover"]'));
    var popoverList = popoverTriggerList.map(function (popoverTriggerEl) {
        return new bootstrap.Popover(popoverTriggerEl);
    });

    // Auto-hide alerts after 5 seconds
    setTimeout(function() {
        var alerts = document.querySelectorAll('.alert.alert-dismissible');
        alerts.forEach(function(alert) {
            bootstrap.Alert.getOrCreateInstance(alert).close();
        });
    }, 5000);

    // Handle quantity input changes in cart
    var quantitySelects = document.querySelectorAll('.quantity-select');
    quantitySelects.forEach(function(select) {
        select.addEventListener('change', function() {
            this.closest('form').submit();
        });
    });

    // Product image gallery
    var productThumbs = document.querySelectorAll('.product-thumb');
    productThumbs.forEach(function(thumb) {
        thumb.addEventListener('click', function() {
            var mainImg = document.querySelector('.product-main-image');
            mainImg.src = this.dataset.image;
            
            // Update active state
            productThumbs.forEach(t => t.classList.remove('active'));
            this.classList.add('active');
        });
    });

    // Form validation
    var forms = document.querySelectorAll('.needs-validation');
    Array.prototype.slice.call(forms).forEach(function(form) {
        form.addEventListener('submit', function(event) {
            if (!form.checkValidity()) {
                event.preventDefault();
                event.stopPropagation();
            }
            form.classList.add('was-validated');
        }, false);
    });

    // Password strength indicator
    var passwordInputs = document.querySelectorAll('input[type="password"]');
    passwordInputs.forEach(function(input) {
        input.addEventListener('input', function() {
            var strength = 0;
            var value = this.value;

            if (value.length >= 8) strength++;
            if (value.match(/[a-z]+/)) strength++;
            if (value.match(/[A-Z]+/)) strength++;
            if (value.match(/[0-9]+/)) strength++;
            if (value.match(/[^a-zA-Z0-9]+/)) strength++;

            var strengthBar = this.parentElement.querySelector('.password-strength');
            if (strengthBar) {
                strengthBar.className = 'password-strength progress-bar';
                switch(strength) {
                    case 0:
                    case 1:
                        strengthBar.style.width = '20%';
                        strengthBar.classList.add('bg-danger');
                        break;
                    case 2:
                        strengthBar.style.width = '40%';
                        strengthBar.classList.add('bg-warning');
                        break;
                    case 3:
                        strengthBar.style.width = '60%';
                        strengthBar.classList.add('bg-info');
                        break;
                    case 4:
                        strengthBar.style.width = '80%';
                        strengthBar.classList.add('bg-primary');
                        break;
                    case 5:
                        strengthBar.style.width = '100%';
                        strengthBar.classList.add('bg-success');
                        break;
                }
            }
        });
    });

    // Preview uploaded image
    var imageInputs = document.querySelectorAll('input[type="file"][accept*="image"]');
    imageInputs.forEach(function(input) {
        input.addEventListener('change', function() {
            var preview = this.parentElement.querySelector('.image-preview');
            if (preview && this.files && this.files[0]) {
                var reader = new FileReader();
                reader.onload = function(e) {
                    preview.src = e.target.result;
                };
                reader.readAsDataURL(this.files[0]);
            }
        });
    });

    // Handle address formatting
    var addressTextareas = document.querySelectorAll('textarea[name="address"]');
    addressTextareas.forEach(function(textarea) {
        textarea.addEventListener('blur', function() {
            var value = this.value.trim();
            if (value) {
                // Ensure each part is on a new line
                value = value.replace(/,\s*/g, ',\n');
                this.value = value;
            }
        });
    });

    // Confirm delete/cancel actions
    var dangerButtons = document.querySelectorAll('[data-confirm]');
    dangerButtons.forEach(function(button) {
        button.addEventListener('click', function(event) {
            if (!confirm(this.dataset.confirm)) {
                event.preventDefault();
            }
        });
    });

    // Initialize cancel order modals with improved stability
    var cancelModals = document.querySelectorAll('[id^="cancelModal"]');
    var modalStates = new Map();
    var modalDebounceTimers = new Map();

    cancelModals.forEach(function(modal) {
        var modalInstance = new bootstrap.Modal(modal);
        modalStates.set(modal.id, false);

        // Handle modal show event with debouncing
        modal.addEventListener('show.bs.modal', function(event) {
            var modalId = this.id;
            if (modalStates.get(modalId)) {
                event.preventDefault();
                return;
            }

            if (modalDebounceTimers.has(modalId)) {
                clearTimeout(modalDebounceTimers.get(modalId));
            }

            modalDebounceTimers.set(modalId, setTimeout(function() {
                modalStates.set(modalId, true);
            }, 100));
        });

        // Handle modal hide event with debouncing
        modal.addEventListener('hide.bs.modal', function(event) {
            var modalId = this.id;
            if (!modalStates.get(modalId)) {
                event.preventDefault();
                return;
            }

            if (modalDebounceTimers.has(modalId)) {
                clearTimeout(modalDebounceTimers.get(modalId));
            }

            modalDebounceTimers.set(modalId, setTimeout(function() {
                modalStates.set(modalId, false);
            }, 100));
        });

        // Reset form when modal is hidden
        modal.addEventListener('hidden.bs.modal', function() {
            var form = this.querySelector('form');
            if (form) {
                form.reset();
            }
            modalStates.set(this.id, false);
        });

        // Confirm cancellation before submit
        var form = modal.querySelector('form');
        if (form) {
            form.addEventListener('submit', function(e) {
                if (!confirm('Are you sure you want to cancel this order?')) {
                    e.preventDefault();
                }
            });
        }
    });

    // Handle responsive tables
    function adjustTables() {
        var tables = document.querySelectorAll('.table-responsive');
        tables.forEach(function(table) {
            var wrapper = table.parentElement;
            if (table.scrollWidth > wrapper.clientWidth) {
                wrapper.classList.add('has-scroll');
            } else {
                wrapper.classList.remove('has-scroll');
            }
        });
    }

    window.addEventListener('resize', adjustTables);
    adjustTables();
});

    // Handle cancel order buttons and forms
    document.querySelectorAll('[data-cancel-order]').forEach(function(button) {
        button.addEventListener('click', function() {
            const orderId = this.dataset.cancelOrder;
            const container = document.getElementById('cancelContainer' + orderId);
            
            // Toggle the cancel form with animation
            if (container.style.display === 'none') {
                container.style.display = 'block';
                container.style.maxHeight = '0';
                setTimeout(() => {
                    container.style.maxHeight = container.scrollHeight + 'px';
                }, 10);
            } else {
                container.style.maxHeight = '0';
                setTimeout(() => {
                    container.style.display = 'none';
                }, 300);
            }
        });
    });

    // Handle cancel order form submission
    document.querySelectorAll('.cancel-order-form').forEach(function(form) {
        form.addEventListener('submit', function(e) {
            e.preventDefault();
            
            if (!this.checkValidity()) {
                e.stopPropagation();
                this.classList.add('was-validated');
                return;
            }
            
            // Submit the form
            this.submit();
        });
    });
