<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Privacy Policy - {{ config('app.name') }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="antialiased bg-white">
    <!-- Navigation -->
    <nav class="border-b border-gray-200">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between items-center h-16">
                <div class="flex items-center">
                    <a href="{{ route('home') }}" class="text-2xl font-bold text-gray-900">DriveSpy</a>
                </div>
                <div class="flex items-center gap-4">
                    @auth
                        <a href="{{ url('/admin') }}" class="text-sm text-gray-700 hover:text-gray-900">Dashboard</a>
                    @else
                        <a href="{{ route('home') }}" class="text-sm text-gray-700 hover:text-gray-900">Home</a>
                    @endauth
                </div>
            </div>
        </div>
    </nav>

    <!-- Content -->
    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
        <h1 class="text-4xl font-bold text-gray-900 mb-4">Privacy Policy</h1>
        <p class="text-gray-600 mb-8">Last updated: {{ date('F d, Y') }}</p>

        <div class="prose prose-gray max-w-none">
            <section class="mb-8">
                <h2 class="text-2xl font-semibold text-gray-900 mb-4">1. Introduction</h2>
                <p class="text-gray-700 mb-4">
                    Welcome to DriveSpy ("we," "our," or "us"). We respect your privacy and are committed to protecting your personal data. This privacy policy explains how we collect, use, and safeguard your information when you use our Google Drive monitoring service.
                </p>
            </section>

            <section class="mb-8">
                <h2 class="text-2xl font-semibold text-gray-900 mb-4">2. Information We Collect</h2>

                <h3 class="text-xl font-semibold text-gray-900 mb-3">2.1 Account Information</h3>
                <p class="text-gray-700 mb-4">When you register for DriveSpy, we collect:</p>
                <ul class="list-disc pl-6 text-gray-700 mb-4">
                    <li>Email address</li>
                    <li>Name</li>
                    <li>Password (encrypted)</li>
                </ul>

                <h3 class="text-xl font-semibold text-gray-900 mb-3">2.2 Google Drive Data</h3>
                <p class="text-gray-700 mb-4">When you connect your Google account, we collect and store:</p>
                <ul class="list-disc pl-6 text-gray-700 mb-4">
                    <li>Google account email and profile information</li>
                    <li>OAuth access and refresh tokens (encrypted)</li>
                    <li>File and folder metadata from monitored Drive folders (names, sizes, modification dates, owners)</li>
                    <li>File activity logs (creates, updates, moves, deletions)</li>
                </ul>
                <p class="text-gray-700 mb-4">
                    <strong>Important:</strong> We do NOT access or store the actual content of your files. We only collect metadata about your files and folders.
                </p>

                <h3 class="text-xl font-semibold text-gray-900 mb-3">2.3 Usage Data</h3>
                <p class="text-gray-700 mb-4">We automatically collect:</p>
                <ul class="list-disc pl-6 text-gray-700 mb-4">
                    <li>IP addresses</li>
                    <li>Browser type and version</li>
                    <li>Pages visited and actions taken</li>
                    <li>Time and date of visits</li>
                </ul>
            </section>

            <section class="mb-8">
                <h2 class="text-2xl font-semibold text-gray-900 mb-4">3. How We Use Your Information</h2>
                <p class="text-gray-700 mb-4">We use your information to:</p>
                <ul class="list-disc pl-6 text-gray-700 mb-4">
                    <li>Provide and maintain our monitoring service</li>
                    <li>Monitor your selected Google Drive folders for changes</li>
                    <li>Generate audit logs and activity reports</li>
                    <li>Authenticate and authorize access to your account</li>
                    <li>Send important service notifications</li>
                    <li>Improve our service and user experience</li>
                    <li>Prevent fraud and enhance security</li>
                </ul>
            </section>

            <section class="mb-8">
                <h2 class="text-2xl font-semibold text-gray-900 mb-4">4. Data Storage and Security</h2>
                <p class="text-gray-700 mb-4">
                    We implement industry-standard security measures to protect your data:
                </p>
                <ul class="list-disc pl-6 text-gray-700 mb-4">
                    <li>All OAuth tokens are encrypted at rest using Laravel's encryption</li>
                    <li>Passwords are hashed using bcrypt</li>
                    <li>HTTPS encryption for all data in transit</li>
                    <li>Regular security updates and patches</li>
                    <li>Access controls and authentication requirements</li>
                </ul>
                <p class="text-gray-700 mb-4">
                    Your data is stored on secure servers. While we strive to protect your information, no method of transmission over the Internet is 100% secure.
                </p>
            </section>

            <section class="mb-8">
                <h2 class="text-2xl font-semibold text-gray-900 mb-4">5. Google API Services</h2>
                <p class="text-gray-700 mb-4">
                    DriveSpy's use and transfer of information received from Google APIs adheres to <a href="https://developers.google.com/terms/api-services-user-data-policy" class="text-blue-600 hover:text-blue-800 underline" target="_blank">Google API Services User Data Policy</a>, including the Limited Use requirements.
                </p>
                <p class="text-gray-700 mb-4">
                    We use the Google Drive API to:
                </p>
                <ul class="list-disc pl-6 text-gray-700 mb-4">
                    <li>Read file and folder metadata</li>
                    <li>Track changes in monitored folders</li>
                    <li>Display file activity to you</li>
                </ul>
                <p class="text-gray-700 mb-4">
                    <strong>We will NOT:</strong>
                </p>
                <ul class="list-disc pl-6 text-gray-700 mb-4">
                    <li>Read or access the content of your files</li>
                    <li>Share your Google Drive data with third parties</li>
                    <li>Use your data for advertising purposes</li>
                    <li>Transfer your data to others unless legally required</li>
                </ul>
            </section>

            <section class="mb-8">
                <h2 class="text-2xl font-semibold text-gray-900 mb-4">6. Data Sharing and Disclosure</h2>
                <p class="text-gray-700 mb-4">
                    We do not sell, trade, or rent your personal information to third parties. We may disclose your information only in the following circumstances:
                </p>
                <ul class="list-disc pl-6 text-gray-700 mb-4">
                    <li><strong>Legal Requirements:</strong> When required by law, subpoena, or court order</li>
                    <li><strong>Service Providers:</strong> To trusted third parties who assist in operating our service (e.g., hosting providers), under strict confidentiality agreements</li>
                    <li><strong>Business Transfers:</strong> In connection with a merger, acquisition, or sale of assets</li>
                    <li><strong>Protection:</strong> To protect our rights, property, or safety, or that of our users</li>
                </ul>
            </section>

            <section class="mb-8">
                <h2 class="text-2xl font-semibold text-gray-900 mb-4">7. Your Rights and Choices</h2>
                <p class="text-gray-700 mb-4">You have the right to:</p>
                <ul class="list-disc pl-6 text-gray-700 mb-4">
                    <li><strong>Access:</strong> Request a copy of your personal data</li>
                    <li><strong>Rectification:</strong> Correct inaccurate or incomplete data</li>
                    <li><strong>Deletion:</strong> Request deletion of your data</li>
                    <li><strong>Revoke Access:</strong> Disconnect your Google account at any time through your dashboard</li>
                    <li><strong>Data Portability:</strong> Request your data in a machine-readable format</li>
                    <li><strong>Object:</strong> Object to processing of your data</li>
                </ul>
                <p class="text-gray-700 mb-4">
                    To exercise these rights, contact us at: <a href="mailto:privacy{{ '@' }}{{ strtolower(parse_url(config('app.url'), PHP_URL_HOST)) }}" class="text-blue-600 hover:text-blue-800 underline">privacy{{ '@' }}{{ strtolower(parse_url(config('app.url'), PHP_URL_HOST)) }}</a>
                </p>
            </section>

            <section class="mb-8">
                <h2 class="text-2xl font-semibold text-gray-900 mb-4">8. Data Retention</h2>
                <p class="text-gray-700 mb-4">
                    We retain your personal data and Google Drive metadata for as long as your account is active or as needed to provide services. When you delete your account or disconnect a Google account:
                </p>
                <ul class="list-disc pl-6 text-gray-700 mb-4">
                    <li>Access tokens are immediately revoked</li>
                    <li>Personal data is deleted within 30 days</li>
                    <li>Backups are purged within 90 days</li>
                </ul>
                <p class="text-gray-700 mb-4">
                    We may retain certain data for longer periods if required by law or for legitimate business purposes (e.g., dispute resolution, fraud prevention).
                </p>
            </section>

            <section class="mb-8">
                <h2 class="text-2xl font-semibold text-gray-900 mb-4">9. Cookies and Tracking</h2>
                <p class="text-gray-700 mb-4">
                    We use cookies and similar tracking technologies to:
                </p>
                <ul class="list-disc pl-6 text-gray-700 mb-4">
                    <li>Maintain your session</li>
                    <li>Remember your preferences</li>
                    <li>Analyze usage patterns</li>
                    <li>Improve security</li>
                </ul>
                <p class="text-gray-700 mb-4">
                    You can control cookies through your browser settings. Note that disabling cookies may limit functionality.
                </p>
            </section>

            <section class="mb-8">
                <h2 class="text-2xl font-semibold text-gray-900 mb-4">10. Children's Privacy</h2>
                <p class="text-gray-700 mb-4">
                    DriveSpy is not intended for users under 18 years of age. We do not knowingly collect personal information from children. If you believe we have collected data from a child, please contact us immediately.
                </p>
            </section>

            <section class="mb-8">
                <h2 class="text-2xl font-semibold text-gray-900 mb-4">11. International Data Transfers</h2>
                <p class="text-gray-700 mb-4">
                    Your information may be transferred to and processed in countries other than your own. We ensure appropriate safeguards are in place to protect your data in accordance with this privacy policy.
                </p>
            </section>

            <section class="mb-8">
                <h2 class="text-2xl font-semibold text-gray-900 mb-4">12. Changes to This Policy</h2>
                <p class="text-gray-700 mb-4">
                    We may update this privacy policy from time to time. We will notify you of significant changes by:
                </p>
                <ul class="list-disc pl-6 text-gray-700 mb-4">
                    <li>Posting the new policy on this page</li>
                    <li>Updating the "Last updated" date</li>
                    <li>Sending an email notification for material changes</li>
                </ul>
                <p class="text-gray-700 mb-4">
                    Your continued use of DriveSpy after changes constitutes acceptance of the updated policy.
                </p>
            </section>

            <section class="mb-8">
                <h2 class="text-2xl font-semibold text-gray-900 mb-4">13. Contact Us</h2>
                <p class="text-gray-700 mb-4">
                    If you have questions or concerns about this privacy policy or our data practices, please contact us:
                </p>
                <ul class="list-none text-gray-700 mb-4">
                    <li class="mb-2"><strong>Email:</strong> <a href="mailto:privacy{{ '@' }}{{ strtolower(parse_url(config('app.url'), PHP_URL_HOST)) }}" class="text-blue-600 hover:text-blue-800 underline">privacy{{ '@' }}{{ strtolower(parse_url(config('app.url'), PHP_URL_HOST)) }}</a></li>
                    <li class="mb-2"><strong>Website:</strong> <a href="{{ route('home') }}" class="text-blue-600 hover:text-blue-800 underline">{{ config('app.url') }}</a></li>
                </ul>
            </section>

            <section class="mb-8">
                <h2 class="text-2xl font-semibold text-gray-900 mb-4">14. Compliance</h2>
                <p class="text-gray-700 mb-4">
                    We strive to comply with applicable data protection laws, including:
                </p>
                <ul class="list-disc pl-6 text-gray-700 mb-4">
                    <li>General Data Protection Regulation (GDPR) for EU users</li>
                    <li>California Consumer Privacy Act (CCPA) for California residents</li>
                    <li>Google API Services User Data Policy</li>
                </ul>
            </section>
        </div>
    </div>

    <!-- Footer -->
    <footer class="bg-white border-t border-gray-200 py-8 px-4 sm:px-6 lg:px-8 mt-12">
        <div class="max-w-7xl mx-auto text-center text-sm text-gray-600">
            <div class="mb-4">
                <a href="{{ route('home') }}" class="hover:text-gray-900 mx-2">Home</a>
                <a href="{{ route('privacy') }}" class="hover:text-gray-900 mx-2">Privacy Policy</a>
                <a href="{{ route('terms') }}" class="hover:text-gray-900 mx-2">Terms of Service</a>
            </div>
            <p>&copy; {{ date('Y') }} DriveSpy. All rights reserved.</p>
        </div>
    </footer>
</body>
</html>
