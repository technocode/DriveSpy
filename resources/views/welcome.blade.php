<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ config('app.name') }} - Monitor Your Google Drive Activity</title>
    <link rel="icon" type="image/svg+xml" href="{{ asset('favicon.svg') }}">
    <link rel="alternate icon" href="{{ asset('favicon.ico') }}">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="antialiased bg-white">
    <!-- Navigation -->
    <nav class="border-b border-gray-200">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between items-center h-16">
                <div class="flex items-center">
                    <h1 class="text-2xl font-bold text-gray-900">DriveSpy</h1>
                </div>
                <div class="flex items-center gap-4">
                    @auth
                        <a href="{{ url('/admin') }}" class="text-sm text-gray-700 hover:text-gray-900">Dashboard</a>
                    @else
                        <a href="{{ route('login') }}" class="text-sm text-gray-700 hover:text-gray-900">Log in</a>
                        @if (Route::has('register'))
                            <a href="{{ route('register') }}" class="text-sm bg-gray-900 text-white px-4 py-2 rounded-lg hover:bg-gray-800">Get Started</a>
                        @endif
                    @endauth
                </div>
            </div>
        </div>
    </nav>

    <!-- Hero Section -->
    <section class="py-20 px-4 sm:px-6 lg:px-8">
        <div class="max-w-7xl mx-auto text-center">
            <h2 class="text-5xl font-bold text-gray-900 mb-6">
                Monitor Your Google Drive<br>
                <span class="text-gray-600">Track Every Change</span>
            </h2>
            <p class="text-xl text-gray-600 mb-8 max-w-3xl mx-auto">
                DriveSpy helps you track all file activities in your Google Drive folders.
                Monitor creates, updates, moves, deletions, and more with detailed audit logs.
            </p>
            <div class="flex gap-4 justify-center">
                @auth
                    <a href="{{ url('/admin') }}" class="bg-gray-900 text-white px-8 py-3 rounded-lg text-lg font-semibold hover:bg-gray-800">
                        Go to Dashboard
                    </a>
                @else
                    <a href="{{ route('register') }}" class="bg-gray-900 text-white px-8 py-3 rounded-lg text-lg font-semibold hover:bg-gray-800">
                        Get Started Free
                    </a>
                    <a href="#features" class="border-2 border-gray-900 text-gray-900 px-8 py-3 rounded-lg text-lg font-semibold hover:bg-gray-50">
                        Learn More
                    </a>
                @endauth
            </div>
        </div>
    </section>

    <!-- Features Section -->
    <section id="features" class="py-20 bg-gray-50 px-4 sm:px-6 lg:px-8">
        <div class="max-w-7xl mx-auto">
            <div class="text-center mb-16">
                <h3 class="text-3xl font-bold text-gray-900 mb-4">Powerful Features</h3>
                <p class="text-lg text-gray-600">Everything you need to monitor your Google Drive</p>
            </div>

            <div class="grid md:grid-cols-3 gap-8">
                <!-- Feature 1 -->
                <div class="bg-white p-8 rounded-lg border border-gray-200">
                    <div class="w-12 h-12 bg-gray-900 rounded-lg flex items-center justify-center mb-4">
                        <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                        </svg>
                    </div>
                    <h4 class="text-xl font-semibold text-gray-900 mb-2">Real-time Monitoring</h4>
                    <p class="text-gray-600">Track all file activities including creates, updates, moves, and deletions in real-time.</p>
                </div>

                <!-- Feature 2 -->
                <div class="bg-white p-8 rounded-lg border border-gray-200">
                    <div class="w-12 h-12 bg-gray-900 rounded-lg flex items-center justify-center mb-4">
                        <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                        </svg>
                    </div>
                    <h4 class="text-xl font-semibold text-gray-900 mb-2">Detailed Audit Logs</h4>
                    <p class="text-gray-600">Complete history of all changes with before/after snapshots and event metadata.</p>
                </div>

                <!-- Feature 3 -->
                <div class="bg-white p-8 rounded-lg border border-gray-200">
                    <div class="w-12 h-12 bg-gray-900 rounded-lg flex items-center justify-center mb-4">
                        <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-6l-2-2H5a2 2 0 00-2 2z"/>
                        </svg>
                    </div>
                    <h4 class="text-xl font-semibold text-gray-900 mb-2">Multiple Folders</h4>
                    <p class="text-gray-600">Monitor multiple Google Drive folders across different accounts simultaneously.</p>
                </div>

                <!-- Feature 4 -->
                <div class="bg-white p-8 rounded-lg border border-gray-200">
                    <div class="w-12 h-12 bg-gray-900 rounded-lg flex items-center justify-center mb-4">
                        <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                        </svg>
                    </div>
                    <h4 class="text-xl font-semibold text-gray-900 mb-2">Secure OAuth</h4>
                    <p class="text-gray-600">Secure authentication via Google OAuth 2.0 with encrypted token storage.</p>
                </div>

                <!-- Feature 5 -->
                <div class="bg-white p-8 rounded-lg border border-gray-200">
                    <div class="w-12 h-12 bg-gray-900 rounded-lg flex items-center justify-center mb-4">
                        <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                        </svg>
                    </div>
                    <h4 class="text-xl font-semibold text-gray-900 mb-2">Auto Sync</h4>
                    <p class="text-gray-600">Automatic synchronization using Google Drive Changes API for efficient updates.</p>
                </div>

                <!-- Feature 6 -->
                <div class="bg-white p-8 rounded-lg border border-gray-200">
                    <div class="w-12 h-12 bg-gray-900 rounded-lg flex items-center justify-center mb-4">
                        <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
                        </svg>
                    </div>
                    <h4 class="text-xl font-semibold text-gray-900 mb-2">Beautiful Dashboard</h4>
                    <p class="text-gray-600">Modern admin interface built with Filament for easy management and insights.</p>
                </div>
            </div>
        </div>
    </section>

    <!-- How It Works -->
    <section class="py-20 px-4 sm:px-6 lg:px-8">
        <div class="max-w-7xl mx-auto">
            <div class="text-center mb-16">
                <h3 class="text-3xl font-bold text-gray-900 mb-4">How It Works</h3>
                <p class="text-lg text-gray-600">Get started in minutes</p>
            </div>

            <div class="grid md:grid-cols-4 gap-8">
                <div class="text-center">
                    <div class="w-16 h-16 bg-gray-900 rounded-full flex items-center justify-center text-white text-2xl font-bold mx-auto mb-4">1</div>
                    <h4 class="text-lg font-semibold text-gray-900 mb-2">Create Account</h4>
                    <p class="text-gray-600">Sign up with your email in seconds</p>
                </div>

                <div class="text-center">
                    <div class="w-16 h-16 bg-gray-900 rounded-full flex items-center justify-center text-white text-2xl font-bold mx-auto mb-4">2</div>
                    <h4 class="text-lg font-semibold text-gray-900 mb-2">Connect Google</h4>
                    <p class="text-gray-600">Authorize access to your Google Drive</p>
                </div>

                <div class="text-center">
                    <div class="w-16 h-16 bg-gray-900 rounded-full flex items-center justify-center text-white text-2xl font-bold mx-auto mb-4">3</div>
                    <h4 class="text-lg font-semibold text-gray-900 mb-2">Add Folders</h4>
                    <p class="text-gray-600">Select which folders to monitor</p>
                </div>

                <div class="text-center">
                    <div class="w-16 h-16 bg-gray-900 rounded-full flex items-center justify-center text-white text-2xl font-bold mx-auto mb-4">4</div>
                    <h4 class="text-lg font-semibold text-gray-900 mb-2">Track Changes</h4>
                    <p class="text-gray-600">View all activity in real-time</p>
                </div>
            </div>
        </div>
    </section>

    <!-- CTA Section -->
    <section class="py-20 bg-gray-900 text-white px-4 sm:px-6 lg:px-8">
        <div class="max-w-4xl mx-auto text-center">
            <h3 class="text-4xl font-bold mb-4">Ready to Monitor Your Drive?</h3>
            <p class="text-xl text-gray-300 mb-8">Start tracking your Google Drive activity today</p>
            @guest
                <a href="{{ route('register') }}" class="bg-white text-gray-900 px-8 py-3 rounded-lg text-lg font-semibold hover:bg-gray-100 inline-block">
                    Get Started Free
                </a>
            @endguest
        </div>
    </section>

    <!-- Footer -->
    <footer class="bg-white border-t border-gray-200 py-12 px-4 sm:px-6 lg:px-8">
        <div class="max-w-7xl mx-auto">
            <div class="grid md:grid-cols-4 gap-8 mb-8">
                <div>
                    <h4 class="font-bold text-gray-900 mb-4">DriveSpy</h4>
                    <p class="text-gray-600 text-sm">Monitor your Google Drive activity with detailed audit logs.</p>
                </div>

                <div>
                    <h5 class="font-semibold text-gray-900 mb-4">Product</h5>
                    <ul class="space-y-2 text-sm text-gray-600">
                        <li><a href="#features" class="hover:text-gray-900">Features</a></li>
                        <li><a href="{{ url('/admin') }}" class="hover:text-gray-900">Dashboard</a></li>
                        <li><a href="https://github.com/yourusername/drivespy" class="hover:text-gray-900" target="_blank">Documentation</a></li>
                    </ul>
                </div>

                <div>
                    <h5 class="font-semibold text-gray-900 mb-4">Legal</h5>
                    <ul class="space-y-2 text-sm text-gray-600">
                        <li><a href="{{ route('privacy') }}" class="hover:text-gray-900">Privacy Policy</a></li>
                        <li><a href="{{ route('terms') }}" class="hover:text-gray-900">Terms of Service</a></li>
                    </ul>
                </div>

                <div>
                    <h5 class="font-semibold text-gray-900 mb-4">Connect</h5>
                    <ul class="space-y-2 text-sm text-gray-600">
                        <li><a href="https://github.com/yourusername/drivespy" class="hover:text-gray-900" target="_blank">GitHub</a></li>
                        <li><a href="mailto:support@drivespy.test" class="hover:text-gray-900">Contact</a></li>
                    </ul>
                </div>
            </div>

            <div class="border-t border-gray-200 pt-8 text-center text-sm text-gray-600">
                <p>&copy; {{ date('Y') }} DriveSpy. Built with Laravel & Filament.</p>
            </div>
        </div>
    </footer>
</body>
</html>
