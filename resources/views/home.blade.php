{{-- @extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">{{ __('Dashboard') }}</div>

                <div class="card-body">
                    @if (session('status'))
                        <div class="alert alert-success" role="alert">
                            {{ session('status') }}
                        </div>
                    @endif

                    {{ __('You are logged in!') }}
                </div>
            </div>
        </div>
    </div>
</div>
@endsection --}}
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Family Hierarchy – A Digital Family Tree Builder</title>
    <!-- Tailwind CSS CDN -->
    <script src="https://cdn.tailwindcss.com"></script>
    <!-- Inter font -->
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap">
    <style>
        body {
            font-family: 'Inter', sans-serif;
            background-color: #f7f9fc;
        }
        .text-gradient {
            background-image: linear-gradient(to right, #6EE7B7, #3B82F6);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }
        .feature-icon {
            color: #3B82F6;
        }
    </style>
</head>
<body class="bg-gray-50 text-gray-800">

    <!-- Header & Navigation -->
    <header class="container mx-auto px-4 py-6 flex items-center justify-between">
        <div class="text-2xl font-bold text-gray-900">
            Family Hierarchy <span class="text-lg text-blue-600">🧬</span>
        </div>
        <a href="{{ config('app.url') }}" class="px-6 py-3 bg-blue-600 text-white rounded-full font-medium shadow-lg hover:bg-blue-700 transition duration-300">
            Try it out
        </a>
    </header>

    <!-- Hero Section -->
    <main class="py-16 md:py-24">
        <div class="container mx-auto px-4 text-center">
            <h1 class="text-4xl md:text-6xl font-extrabold text-gray-900 leading-tight mb-4">
                Build & Preserve Your <br> <span class="text-gradient">Family Legacy</span>
            </h1>
            <p class="text-xl md:text-2xl text-gray-500 max-w-2xl mx-auto mb-8">
                Family Hierarchy is a digital family tree builder that helps you visualize your lineage and honor your past.
            </p>
            <a href="{{ config('app.url') }}" class="inline-block px-8 py-4 text-lg font-bold text-white bg-blue-600 rounded-full shadow-xl hover:bg-blue-700 transition duration-300 transform hover:scale-105">
                Launch the App
            </a>
            <p class="mt-4 text-sm text-gray-400">Completely free to use.</p>
        </div>
    </main>

    <!-- Why Section -->
    <section class="bg-white py-16 md:py-24">
        <div class="container mx-auto px-4 max-w-3xl">
            <h2 class="text-3xl md:text-4xl font-bold text-center text-gray-900 mb-8">Why I Built This</h2>
            <div class="bg-gray-50 p-6 md:p-10 rounded-3xl shadow-lg border border-gray-100">
                <p class="text-base md:text-lg leading-relaxed text-gray-600">
                    Over 10 years ago, my grandfather passed away. He was from the old generation — the kind who kept everything in handwritten diaries. After his passing, I discovered a notebook where he had documented 5–6 generations of our ancestors. Names, relationships, stories — all preserved in ink.
                </p>
                <p class="mt-4 text-base md:text-lg leading-relaxed text-gray-600">
                    That diary, now tucked away somewhere in the house, is a treasure. But the sad truth is, it can get lost — and with it, an entire legacy. So I built **Family Hierarchy** to make sure that doesn’t happen again. It's a tool for honoring the past, preserving identities, and helping others stay connected to where they come from.
                </p>
            </div>
        </div>
    </section>

    <!-- Features Section -->
    <section class="py-16 md:py-24">
        <div class="container mx-auto px-4">
            <h2 class="text-3xl md:text-4xl font-bold text-center text-gray-900 mb-12">What It Does</h2>
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">

                <!-- Feature 1 -->
                <div class="bg-white p-6 rounded-2xl shadow-md flex items-start space-x-4">
                    <div class="flex-shrink-0 w-12 h-12 rounded-full bg-blue-100 flex items-center justify-center">
                        <span class="text-xl feature-icon">👨‍👩‍👧‍👦</span>
                    </div>
                    <div>
                        <h3 class="font-semibold text-xl text-gray-900 mb-2">Build Your Tree</h3>
                        <p class="text-gray-600">Add people and link them through father, mother, and spouse relationships to build your lineage.</p>
                    </div>
                </div>

                <!-- Feature 2 -->
                <div class="bg-white p-6 rounded-2xl shadow-md flex items-start space-x-4">
                    <div class="flex-shrink-0 w-12 h-12 rounded-full bg-blue-100 flex items-center justify-center">
                        <span class="text-xl feature-icon">🌳</span>
                    </div>
                    <div>
                        <h3 class="font-semibold text-xl text-gray-900 mb-2">Interactive B-tree Structure</h3>
                        <p class="text-gray-600">Visualize your family hierarchy in a beautiful, zoomable, and interactive tree structure.</p>
                    </div>
                </div>

                <!-- Feature 3 -->
                <div class="bg-white p-6 rounded-2xl shadow-md flex items-start space-x-4">
                    <div class="flex-shrink-0 w-12 h-12 rounded-full bg-blue-100 flex items-center justify-center">
                        <span class="text-xl feature-icon">📝</span>
                    </div>
                    <div>
                        <h3 class="font-semibold text-xl text-gray-900 mb-2">Store Key Details</h3>
                        <p class="text-gray-600">Store and edit vital information including name, gender, date of birth, date of death, and a personal avatar.</p>
                    </div>
                </div>

                <!-- Feature 4 -->
                <div class="bg-white p-6 rounded-2xl shadow-md flex items-start space-x-4">
                    <div class="flex-shrink-0 w-12 h-12 rounded-full bg-blue-100 flex items-center justify-center">
                        <span class="text-xl feature-icon">🔎</span>
                    </div>
                    <div>
                        <h3 class="font-semibold text-xl text-gray-900 mb-2">View and Filter</h3>
                        <p class="text-gray-600">Easily find who you're looking for with comprehensive viewing and filtering options.</p>
                    </div>
                </div>

                <!-- Feature 5 -->
                <div class="bg-white p-6 rounded-2xl shadow-md flex items-start space-x-4">
                    <div class="flex-shrink-0 w-12 h-12 rounded-full bg-blue-100 flex items-center justify-center">
                        <span class="text-xl feature-icon">✍️</span>
                    </div>
                    <div>
                        <h3 class="font-semibold text-xl text-gray-900 mb-2">Fully Editable</h3>
                        <p class="text-gray-600">You are in complete control of your family's history. Everything is fully editable for accuracy.</p>
                    </div>
                </div>

                <!-- Feature 6 -->
                <div class="bg-white p-6 rounded-2xl shadow-md flex items-start space-x-4">
                    <div class="flex-shrink-0 w-12 h-12 rounded-full bg-blue-100 flex items-center justify-center">
                        <span class="text-xl feature-icon">📜</span>
                    </div>
                    <div>
                        <h3 class="font-semibold text-xl text-gray-900 mb-2">Add Personal Stories</h3>
                        <p class="text-gray-600">Go beyond names and dates. Add personal anecdotes, memories, and stories to keep the legacy alive.</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Final Call to Action -->
    <section class="bg-blue-600 py-16 md:py-24">
        <div class="container mx-auto px-4 text-center text-white">
            <h2 class="text-3xl md:text-5xl font-bold mb-4">Ready to build your family tree?</h2>
            <p class="text-lg md:text-xl max-w-3xl mx-auto mb-8 opacity-90">
                Start preserving your family’s legacy today. It’s a tool for honoring the past and helping others stay connected to where they come from.
            </p>
            <a href="{{ config('app.url') }}" class="inline-block px-8 py-4 text-lg font-bold text-blue-600 bg-white rounded-full shadow-lg hover:bg-gray-100 transition duration-300 transform hover:scale-105">
                Start Building Now
            </a>
        </div>
    </section>

    <!-- Footer -->
    <footer class="bg-gray-900 text-gray-400 py-8">
        <div class="container mx-auto px-4 text-center">
            <p class="text-sm">&copy; 2025 Family Hierarchy. All rights reserved.</p>
        </div>
    </footer>

</body>
</html>
