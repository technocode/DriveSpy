<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Terms of Service - {{ config('app.name') }}</title>
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
        <h1 class="text-4xl font-bold text-gray-900 mb-4">Terms of Service</h1>
        <p class="text-gray-600 mb-8">Last updated: {{ date('F d, Y') }}</p>

        <div class="prose prose-gray max-w-none">
            <section class="mb-8">
                <h2 class="text-2xl font-semibold text-gray-900 mb-4">1. Acceptance of Terms</h2>
                <p class="text-gray-700 mb-4">
                    By accessing or using DriveSpy ("Service"), you agree to be bound by these Terms of Service ("Terms"). If you do not agree to these Terms, do not use the Service.
                </p>
                <p class="text-gray-700 mb-4">
                    We reserve the right to modify these Terms at any time. Your continued use of the Service after changes constitutes acceptance of the modified Terms.
                </p>
            </section>

            <section class="mb-8">
                <h2 class="text-2xl font-semibold text-gray-900 mb-4">2. Description of Service</h2>
                <p class="text-gray-700 mb-4">
                    DriveSpy is a Google Drive monitoring and activity tracking application that:
                </p>
                <ul class="list-disc pl-6 text-gray-700 mb-4">
                    <li>Monitors selected Google Drive folders for file changes</li>
                    <li>Tracks file metadata and activity (creates, updates, moves, deletions)</li>
                    <li>Provides audit logs and activity reports</li>
                    <li>Offers a dashboard for managing monitored folders</li>
                </ul>
                <p class="text-gray-700 mb-4">
                    The Service does NOT access or store the actual content of your files.
                </p>
            </section>

            <section class="mb-8">
                <h2 class="text-2xl font-semibold text-gray-900 mb-4">3. User Accounts</h2>

                <h3 class="text-xl font-semibold text-gray-900 mb-3">3.1 Account Creation</h3>
                <p class="text-gray-700 mb-4">To use the Service, you must:</p>
                <ul class="list-disc pl-6 text-gray-700 mb-4">
                    <li>Be at least 18 years old</li>
                    <li>Provide accurate and complete registration information</li>
                    <li>Maintain the security of your account credentials</li>
                    <li>Promptly update your account information if it changes</li>
                </ul>

                <h3 class="text-xl font-semibold text-gray-900 mb-3">3.2 Account Responsibility</h3>
                <p class="text-gray-700 mb-4">
                    You are responsible for all activities that occur under your account. You must:
                </p>
                <ul class="list-disc pl-6 text-gray-700 mb-4">
                    <li>Keep your password confidential</li>
                    <li>Notify us immediately of any unauthorized access</li>
                    <li>Not share your account with others</li>
                    <li>Not create multiple accounts to circumvent restrictions</li>
                </ul>
            </section>

            <section class="mb-8">
                <h2 class="text-2xl font-semibold text-gray-900 mb-4">4. Google Account Integration</h2>

                <h3 class="text-xl font-semibold text-gray-900 mb-3">4.1 Authorization</h3>
                <p class="text-gray-700 mb-4">
                    By connecting your Google account, you authorize DriveSpy to:
                </p>
                <ul class="list-disc pl-6 text-gray-700 mb-4">
                    <li>Access metadata of files in your selected folders</li>
                    <li>Monitor changes to files and folders</li>
                    <li>Use the Google Drive API on your behalf</li>
                </ul>

                <h3 class="text-xl font-semibold text-gray-900 mb-3">4.2 Your Google Data</h3>
                <p class="text-gray-700 mb-4">
                    You must have the right to authorize access to the Google Drive folders you monitor. You are responsible for:
                </p>
                <ul class="list-disc pl-6 text-gray-700 mb-4">
                    <li>Ensuring you have permission to monitor shared folders</li>
                    <li>Complying with your organization's policies</li>
                    <li>Not monitoring folders containing sensitive or illegal content</li>
                </ul>

                <h3 class="text-xl font-semibold text-gray-900 mb-3">4.3 Revoking Access</h3>
                <p class="text-gray-700 mb-4">
                    You can revoke DriveSpy's access to your Google account at any time through:
                </p>
                <ul class="list-disc pl-6 text-gray-700 mb-4">
                    <li>Your DriveSpy dashboard</li>
                    <li>Google account security settings</li>
                </ul>
            </section>

            <section class="mb-8">
                <h2 class="text-2xl font-semibold text-gray-900 mb-4">5. Acceptable Use</h2>

                <h3 class="text-xl font-semibold text-gray-900 mb-3">5.1 You Agree NOT to:</h3>
                <ul class="list-disc pl-6 text-gray-700 mb-4">
                    <li>Use the Service for any illegal purpose</li>
                    <li>Violate any laws or regulations</li>
                    <li>Infringe on others' intellectual property rights</li>
                    <li>Monitor folders without proper authorization</li>
                    <li>Attempt to gain unauthorized access to the Service</li>
                    <li>Interfere with or disrupt the Service</li>
                    <li>Reverse engineer or attempt to extract source code</li>
                    <li>Use automated scripts to access the Service excessively</li>
                    <li>Resell or redistribute the Service</li>
                    <li>Upload malware or malicious code</li>
                </ul>

                <h3 class="text-xl font-semibold text-gray-900 mb-3">5.2 Compliance</h3>
                <p class="text-gray-700 mb-4">
                    You must comply with all applicable laws, including:
                </p>
                <ul class="list-disc pl-6 text-gray-700 mb-4">
                    <li>Data protection and privacy laws</li>
                    <li>Employment and workplace monitoring laws</li>
                    <li>Intellectual property laws</li>
                    <li>Google's Terms of Service</li>
                </ul>
            </section>

            <section class="mb-8">
                <h2 class="text-2xl font-semibold text-gray-900 mb-4">6. Service Availability</h2>

                <h3 class="text-xl font-semibold text-gray-900 mb-3">6.1 As-Is Basis</h3>
                <p class="text-gray-700 mb-4">
                    The Service is provided "as is" and "as available" without warranties of any kind. We do not guarantee:
                </p>
                <ul class="list-disc pl-6 text-gray-700 mb-4">
                    <li>Uninterrupted or error-free operation</li>
                    <li>Accuracy or completeness of data</li>
                    <li>That the Service will meet your requirements</li>
                    <li>Compatibility with all systems</li>
                </ul>

                <h3 class="text-xl font-semibold text-gray-900 mb-3">6.2 Maintenance</h3>
                <p class="text-gray-700 mb-4">
                    We may perform scheduled or emergency maintenance that temporarily interrupts access to the Service. We will attempt to provide advance notice when possible.
                </p>

                <h3 class="text-xl font-semibold text-gray-900 mb-3">6.3 Modifications</h3>
                <p class="text-gray-700 mb-4">
                    We reserve the right to modify or discontinue the Service (or any part thereof) at any time, with or without notice.
                </p>
            </section>

            <section class="mb-8">
                <h2 class="text-2xl font-semibold text-gray-900 mb-4">7. Intellectual Property</h2>

                <h3 class="text-xl font-semibold text-gray-900 mb-3">7.1 Our Property</h3>
                <p class="text-gray-700 mb-4">
                    The Service, including all software, designs, text, graphics, and other content, is owned by DriveSpy or our licensors and protected by copyright, trademark, and other intellectual property laws.
                </p>

                <h3 class="text-xl font-semibold text-gray-900 mb-3">7.2 Your Data</h3>
                <p class="text-gray-700 mb-4">
                    You retain all rights to your data. By using the Service, you grant us a limited license to process your data solely to provide the Service.
                </p>

                <h3 class="text-xl font-semibold text-gray-900 mb-3">7.3 Feedback</h3>
                <p class="text-gray-700 mb-4">
                    Any feedback, suggestions, or ideas you provide about the Service become our property and may be used without compensation or attribution.
                </p>
            </section>

            <section class="mb-8">
                <h2 class="text-2xl font-semibold text-gray-900 mb-4">8. Fees and Payment</h2>

                <h3 class="text-xl font-semibold text-gray-900 mb-3">8.1 Free Service</h3>
                <p class="text-gray-700 mb-4">
                    DriveSpy is currently provided free of charge. We reserve the right to introduce paid features or subscription plans in the future with advance notice.
                </p>

                <h3 class="text-xl font-semibold text-gray-900 mb-3">8.2 Future Pricing</h3>
                <p class="text-gray-700 mb-4">
                    If paid plans are introduced:
                </p>
                <ul class="list-disc pl-6 text-gray-700 mb-4">
                    <li>Existing users will receive advance notice</li>
                    <li>You may continue using free features (if available)</li>
                    <li>You may delete your account before paid plans take effect</li>
                </ul>
            </section>

            <section class="mb-8">
                <h2 class="text-2xl font-semibold text-gray-900 mb-4">9. Privacy and Data Protection</h2>
                <p class="text-gray-700 mb-4">
                    Your use of the Service is also governed by our <a href="{{ route('privacy') }}" class="text-blue-600 hover:text-blue-800 underline">Privacy Policy</a>, which is incorporated into these Terms by reference.
                </p>
                <p class="text-gray-700 mb-4">
                    We handle your data in accordance with:
                </p>
                <ul class="list-disc pl-6 text-gray-700 mb-4">
                    <li>Our Privacy Policy</li>
                    <li>Google API Services User Data Policy</li>
                    <li>Applicable data protection laws</li>
                </ul>
            </section>

            <section class="mb-8">
                <h2 class="text-2xl font-semibold text-gray-900 mb-4">10. Limitation of Liability</h2>

                <h3 class="text-xl font-semibold text-gray-900 mb-3">10.1 Disclaimer</h3>
                <p class="text-gray-700 mb-4">
                    TO THE MAXIMUM EXTENT PERMITTED BY LAW, DRIVESPY AND ITS AFFILIATES, OFFICERS, EMPLOYEES, AGENTS, AND LICENSORS SHALL NOT BE LIABLE FOR:
                </p>
                <ul class="list-disc pl-6 text-gray-700 mb-4">
                    <li>Any indirect, incidental, special, consequential, or punitive damages</li>
                    <li>Loss of profits, data, use, or goodwill</li>
                    <li>Service interruptions or errors</li>
                    <li>Unauthorized access to your data</li>
                    <li>Third-party actions or content</li>
                </ul>

                <h3 class="text-xl font-semibold text-gray-900 mb-3">10.2 Maximum Liability</h3>
                <p class="text-gray-700 mb-4">
                    Our total liability for any claims related to the Service shall not exceed the amount you paid us in the 12 months prior to the claim (currently $0).
                </p>
            </section>

            <section class="mb-8">
                <h2 class="text-2xl font-semibold text-gray-900 mb-4">11. Indemnification</h2>
                <p class="text-gray-700 mb-4">
                    You agree to indemnify and hold harmless DriveSpy, its affiliates, and their respective officers, employees, and agents from any claims, damages, losses, liabilities, and expenses (including attorney fees) arising from:
                </p>
                <ul class="list-disc pl-6 text-gray-700 mb-4">
                    <li>Your use of the Service</li>
                    <li>Your violation of these Terms</li>
                    <li>Your violation of any rights of another party</li>
                    <li>Your data or content</li>
                </ul>
            </section>

            <section class="mb-8">
                <h2 class="text-2xl font-semibold text-gray-900 mb-4">12. Termination</h2>

                <h3 class="text-xl font-semibold text-gray-900 mb-3">12.1 By You</h3>
                <p class="text-gray-700 mb-4">
                    You may terminate your account at any time by:
                </p>
                <ul class="list-disc pl-6 text-gray-700 mb-4">
                    <li>Deleting your account through your dashboard</li>
                    <li>Contacting us at support@drivespy.test</li>
                </ul>

                <h3 class="text-xl font-semibold text-gray-900 mb-3">12.2 By Us</h3>
                <p class="text-gray-700 mb-4">
                    We may suspend or terminate your account if:
                </p>
                <ul class="list-disc pl-6 text-gray-700 mb-4">
                    <li>You violate these Terms</li>
                    <li>You engage in fraudulent or illegal activity</li>
                    <li>Required by law</li>
                    <li>Your account is inactive for an extended period</li>
                </ul>

                <h3 class="text-xl font-semibold text-gray-900 mb-3">12.3 Effect of Termination</h3>
                <p class="text-gray-700 mb-4">
                    Upon termination:
                </p>
                <ul class="list-disc pl-6 text-gray-700 mb-4">
                    <li>Your access to the Service will cease</li>
                    <li>Your data will be deleted according to our Privacy Policy</li>
                    <li>Sections that should survive termination (e.g., limitation of liability) will remain in effect</li>
                </ul>
            </section>

            <section class="mb-8">
                <h2 class="text-2xl font-semibold text-gray-900 mb-4">13. Dispute Resolution</h2>

                <h3 class="text-xl font-semibold text-gray-900 mb-3">13.1 Informal Resolution</h3>
                <p class="text-gray-700 mb-4">
                    Before filing a claim, you agree to contact us at legal@drivespy.test to attempt to resolve the dispute informally.
                </p>

                <h3 class="text-xl font-semibold text-gray-900 mb-3">13.2 Governing Law</h3>
                <p class="text-gray-700 mb-4">
                    These Terms are governed by the laws of [Your Jurisdiction], without regard to conflict of law principles.
                </p>

                <h3 class="text-xl font-semibold text-gray-900 mb-3">13.3 Arbitration</h3>
                <p class="text-gray-700 mb-4">
                    Any disputes arising from these Terms or the Service shall be resolved through binding arbitration, except where prohibited by law.
                </p>
            </section>

            <section class="mb-8">
                <h2 class="text-2xl font-semibold text-gray-900 mb-4">14. General Provisions</h2>

                <h3 class="text-xl font-semibold text-gray-900 mb-3">14.1 Entire Agreement</h3>
                <p class="text-gray-700 mb-4">
                    These Terms, together with our Privacy Policy, constitute the entire agreement between you and DriveSpy regarding the Service.
                </p>

                <h3 class="text-xl font-semibold text-gray-900 mb-3">14.2 Severability</h3>
                <p class="text-gray-700 mb-4">
                    If any provision of these Terms is found invalid or unenforceable, the remaining provisions will remain in effect.
                </p>

                <h3 class="text-xl font-semibold text-gray-900 mb-3">14.3 Waiver</h3>
                <p class="text-gray-700 mb-4">
                    Our failure to enforce any provision does not waive our right to enforce it later.
                </p>

                <h3 class="text-xl font-semibold text-gray-900 mb-3">14.4 Assignment</h3>
                <p class="text-gray-700 mb-4">
                    You may not assign these Terms without our consent. We may assign these Terms to any affiliate or successor.
                </p>

                <h3 class="text-xl font-semibold text-gray-900 mb-3">14.5 No Agency</h3>
                <p class="text-gray-700 mb-4">
                    No agency, partnership, joint venture, or employment relationship is created by these Terms.
                </p>
            </section>

            <section class="mb-8">
                <h2 class="text-2xl font-semibold text-gray-900 mb-4">15. Contact Information</h2>
                <p class="text-gray-700 mb-4">
                    For questions about these Terms, please contact us:
                </p>
                <ul class="list-none text-gray-700 mb-4">
                    <li class="mb-2"><strong>General Inquiries:</strong> <a href="mailto:support@drivespy.test" class="text-blue-600 hover:text-blue-800 underline">support@drivespy.test</a></li>
                    <li class="mb-2"><strong>Legal Matters:</strong> <a href="mailto:legal@drivespy.test" class="text-blue-600 hover:text-blue-800 underline">legal@drivespy.test</a></li>
                    <li class="mb-2"><strong>Privacy Concerns:</strong> <a href="mailto:privacy@drivespy.test" class="text-blue-600 hover:text-blue-800 underline">privacy@drivespy.test</a></li>
                </ul>
            </section>

            <section class="mb-8">
                <p class="text-gray-700 text-sm italic">
                    By using DriveSpy, you acknowledge that you have read, understood, and agree to be bound by these Terms of Service.
                </p>
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
