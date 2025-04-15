<!-- resources/views/welcome.blade.php -->
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Welcome</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://cdn.jsdelivr.net/npm/swiper/swiper-bundle.min.js"></script>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/swiper/swiper-bundle.min.css">
    <style>
        .swiper-container {
            width: 100%;
            height: 300px;
        }
        .swiper-slide {
            background-color: white; /* Change the container background to white */
            color: #6B4F8D; /* Dark purple color for the text */
        }
        .upper-container {
            background-color: white; /* Upper container background white */
            color: #6B4F8D; /* Dark purple color for the text */
        }
    </style>
</head>
<body class="bg-gray-100">

    <!-- PTGEA Section with Logo and Info (Upper Container) -->
    <div class="py-12 upper-container">
        <div class="container mx-auto text-center">
            <img src="{{ asset('images/bisu_logo.png') }}" alt="PTGEA Logo" class="w-32 mx-auto mb-6">
            <h1 class="mb-4 text-2xl font-bold">Bohol Island State University - Calape Campus</h1>
            <h1 class="mb-4 text-4xl font-bold">Parents, Teachers, Guardians and Employees Association</h1>
            <h2 class="mb-4 text-3xl font-bold">Management System</h2>
        </div>
    </div>

    <!-- Sliding Text Container -->
    <div class="my-12 swiper-container">
        <div class="swiper-wrapper">
            <div class="p-10 text-center swiper-slide">
                <h2 class="text-2xl font-bold">Welcome to PTGEA</h2>
                <p class="mt-4">Empowering the youth through education and skills development.</p>
            </div>
            <div class="p-10 text-center swiper-slide">
                <h2 class="text-2xl font-bold">Join Us Today</h2>
                <p class="mt-4">Enroll now and start your journey with PTGEA.</p>
            </div>
            <div class="p-10 text-center swiper-slide">
                <h2 class="text-2xl font-bold">Our Mission</h2>
                <p class="mt-4">To provide quality education and training for a brighter future.</p>
            </div>
        </div>
        <!-- Add Pagination -->
        <div class="swiper-pagination"></div>
    </div>

    <!-- Login Button -->
    <div class="absolute mb-12 text-center top-4 right-4">
        <a href="{{ url('/admin/login') }}">
            <button class="px-6 py-2 text-white transition duration-300 bg-blue-700 rounded-lg hover:bg-blue-800">
                Login
            </button>
        </a>
    </div>

    <!-- Swiper JS Initialization -->
    <script>
        var swiper = new Swiper('.swiper-container', {
            loop: true,
            autoplay: {
                delay: 2500,
                disableOnInteraction: false,
            },
            pagination: {
                el: '.swiper-pagination',
                clickable: true,
            },
        });
    </script>
</body>
</html>
