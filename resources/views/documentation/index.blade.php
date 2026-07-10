@php
    $user = \App\Models\User::where('email', 'merchant@gmail.com')->select('app_id', 'app_key')->first();
    // dump($user->app_id);
@endphp

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>API Documentation</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet"
        integrity="sha384-EVSTQN3/azprG1Anm3QDgpJLIm9Nao0Yz1ztcQTwFspd3yD65VohhpuuCOmLASjC" crossorigin="anonymous">

    <style>
        body {
            display: flex;
            margin: 0;
            padding: 0;
        }

        .sidebar {
            width: 250px;
            position: fixed;
            padding: 10px;
            background: #f8f9fa;
            border-radius: 0;
            height: 100vh;
            overflow-y: auto;
            z-index: 1000;
            transition: transform 0.3s ease;
        }

        .sidebar a {
            display: block;
            margin: 5px 0;
            color: #111111;
            text-decoration: none;
            padding: 10px 8px;
            font-weight: 400;
            border-radius: 5px;
        }

        .sidebar a:hover {
            background-color: #ffffff;
        }

        .content {
            margin-left: 250px;
            flex: 1;
            padding: 15px;
            max-width: 100%;
            overflow-x: hidden;
        }

        .endpoint {
            margin-bottom: 30px;
        }

        .response {
            background: #f8f9fa;
            padding: 15px;
            border-radius: 5px;
            margin-top: 10px;
            max-width: 100%;
            width: 100%;
            overflow-x: auto;
        }

        .description {
            margin-bottom: 15px;
        }

        .parameters {
            margin-top: 10px;
            margin-bottom: 20px;
            overflow-x: auto;
        }

        .parameters table {
            width: 100%;
            border-collapse: collapse;
            min-width: 500px;
        }

        .parameters th,
        .parameters td {
            border: 1px solid #ddd;
            padding: 8px;
            word-wrap: break-word;
        }

        .parameters th {
            background-color: #f2f2f2;
        }

        code a {
            color: #de3394;
        }

        .post {
            background-color: rgb(78, 151, 78);
            color: #ffffff;
            padding: 2px 5px;
            border-radius: 3px;
        }

        .get {
            background-color: #007bff;
            color: #ffffff;
            padding: 2px 5px;
            border-radius: 3px;
        }

        .copy {
            background-color: #04a20b;
            color: #ffffff;
            padding: 2px 5px;
            border-radius: 3px;
            font-size: x-small;
            cursor: pointer;
        }

        .sidebar a.active {
            background-color: #f2f2f2;
        }

        /* Mobile menu toggle */
        .mobile-menu-toggle {
            display: none;
            position: fixed;
            top: 15px;
            left: 15px;
            z-index: 1001;
            background: #007bff;
            color: white;
            border: none;
            border-radius: 5px;
            padding: 10px;
            cursor: pointer;
        }

        /* Responsive Styles */
        @media (max-width: 768px) {
            .mobile-menu-toggle {
                display: block;
            }

            .sidebar {
                transform: translateX(-100%);
                width: 280px;
            }

            .sidebar.active {
                transform: translateX(0);
            }

            .content {
                margin-left: 0;
                padding: 60px 15px 15px 15px;
            }

            .content h1 {
                font-size: 1.5rem;
            }

            .response {
                padding: 10px;
                font-size: 0.9rem;
            }

            .parameters table {
                font-size: 0.8rem;
            }

            .parameters th,
            .parameters td {
                padding: 6px;
            }
        }

        @media (max-width: 576px) {
            .content {
                padding: 60px 10px 10px 10px;
            }

            .content h1 {
                font-size: 1.3rem;
            }

            .response {
                padding: 8px;
                font-size: 0.8rem;
            }

            .parameters table {
                font-size: 0.7rem;
            }

            .parameters th,
            .parameters td {
                padding: 4px;
            }

            .sidebar {
                width: 100%;
            }
        }

        /* Overlay for mobile */
        .overlay {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.5);
            z-index: 999;
        }

        @media (max-width: 768px) {
            .overlay.active {
                display: block;
            }
        }
    </style>

    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>

<body>
    <!-- Mobile menu toggle -->
    <button class="mobile-menu-toggle" id="mobileMenuToggle">
        ☰ Menu
    </button>

    <!-- Overlay for mobile -->
    <div class="overlay" id="overlay"></div>

    <!-- Sidebar -->
    <div class="sidebar" id="sidebar">
        <div class="text-center">
            <h4 class="mb-4">API Documentation</h4>
        </div>
        <a href="#summary-token" class="sidebar-menu active">Summary</a>
        {{-- <a href="#generate-token" class="sidebar-menu">Generate Token</a> --}}
        {{-- <a href="#regenerate-token" class="sidebar-menu">Regenerate Token</a> --}}
        <a href="#all-categories" class="sidebar-menu">Get All Categories</a>
        <a href="#all-products" class="sidebar-menu">Get All Products</a>
        <a href="#products-stock" class="sidebar-menu">Get Products Stock</a>
        <a href="#place-order" class="sidebar-menu">Place Order</a>
        <a href="#update-order" class="sidebar-menu">Update Order</a>
        <a href="#update-order-status" class="sidebar-menu">Update Order Status</a>
    </div>

    <!-- Main Content -->
    <div class="content">
        <h1 class="mb-4">API Documentation</h1>
        @include('documentation.general.summary')
        {{-- @include('documentation.auth.generate_token') --}}
        {{-- @include('documentation.auth.regenerate_token') --}}
        @include('documentation.category.all_categories')
        @include('documentation.product.all_products')
        {{-- @include('documentation.product.products_by_category') --}}
        @include('documentation.product.products_stock')
        @include('documentation.order.place_order')
        @include('documentation.order.update_order')
        @include('documentation.order.update_order_status')
    </div>


    <script>
        const Toast = Swal.mixin({
            toast: true,
            position: "top-end",
            showConfirmButton: false,
            timer: 2000,
            timerProgressBar: false,
        });

        function showToast(type, message) {
            Toast.fire({
                icon: type,
                title: message
            });
        }

        document.addEventListener("DOMContentLoaded", function() {
            const currentUrl = window.location.href;
            const fragment = currentUrl.split("#")[1];
            if (fragment) {
                const targetLink = document.querySelector(`a[href="#${fragment}"]`);
                if (targetLink) {
                    document.querySelectorAll(".sidebar-menu").forEach(function(link) {
                        link.classList.remove("active");
                    });
                    targetLink.classList.add("active");
                }
            }

            document.querySelectorAll(".copy").forEach(function(copyBtn) {
                copyBtn.addEventListener("click", function() {
                    var textToCopy = this.closest("code").getAttribute("data-value");
                    navigator.clipboard.writeText(textToCopy).then(() => {
                        showToast("success", "Copied to clipboard");
                    }).catch(error => {
                        console.error("Failed to copy to clipboard", error);
                        showToast("error", "Failed to copy to clipboard");
                    });
                });
            });

            document.querySelectorAll(".section-count").forEach(function(el, index) {
                el.innerText = index + 1;
            });
            document.querySelectorAll(".sidebar-menu").forEach(function(menu) {
                menu.addEventListener("click", function() {
                    document.querySelectorAll(".sidebar-menu").forEach(function(m) {
                        m.classList.remove("active");
                    });
                    this.classList.add("active");

                    // Close mobile menu when item is clicked
                    if (window.innerWidth <= 768) {
                        closeMobileMenu();
                    }
                });
            });

            // Mobile menu functionality
            const mobileMenuToggle = document.getElementById('mobileMenuToggle');
            const sidebar = document.getElementById('sidebar');
            const overlay = document.getElementById('overlay');

            function openMobileMenu() {
                sidebar.classList.add('active');
                overlay.classList.add('active');
                document.body.style.overflow = 'hidden';
            }

            function closeMobileMenu() {
                sidebar.classList.remove('active');
                overlay.classList.remove('active');
                document.body.style.overflow = 'auto';
            }

            mobileMenuToggle.addEventListener('click', openMobileMenu);
            overlay.addEventListener('click', closeMobileMenu);

            // Close menu on escape key
            document.addEventListener('keydown', function(e) {
                if (e.key === 'Escape') {
                    closeMobileMenu();
                }
            });

            // Handle window resize
            window.addEventListener('resize', function() {
                if (window.innerWidth > 768) {
                    closeMobileMenu();
                }
            });
        });
    </script>
</body>

</html>
