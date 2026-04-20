<!DOCTYPE html>
<html lang="en" class="scroll-smooth">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Terms of Service - WhatGame</title>
    <link rel="icon" type="image/x-icon" href="/favicon.png">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;700;800&display=swap" rel="stylesheet">

    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: {
                        sans: ['Outfit', 'sans-serif'],
                    },
                    colors: {
                        app: {
                            bg: 'rgb(25, 28, 29)',
                            fg: 'rgb(225, 227, 227)',
                            primary: 'rgb(0, 179, 152)',
                            primaryDark: 'rgb(0, 120, 102)',
                            primaryContainer: 'rgb(0, 89, 76)',
                            onPrimaryContainer: 'rgb(102, 230, 203)',
                            surfaceVariant: 'rgb(63, 72, 74)',
                            tertiary: 'rgb(186, 198, 234)',
                        }
                    }
                }
            }
        }
    </script>
    <style>
        body {
            background-color: rgb(25, 28, 29);
            color: rgb(225, 227, 227);
        }
        .bg-pattern {
            background-size: 40px 40px;
            background-image:
                linear-gradient(to right, rgba(0, 179, 152, 0.05) 1px, transparent 1px),
                linear-gradient(to bottom, rgba(0, 179, 152, 0.05) 1px, transparent 1px);
        }
        .glass-panel {
            background: rgba(30, 34, 35, 0.6);
            backdrop-filter: blur(16px);
            -webkit-backdrop-filter: blur(16px);
            border: 1px solid rgba(255, 255, 255, 0.05);
            box-shadow: 0 4px 30px rgba(0, 0, 0, 0.1);
        }
    </style>
</head>
<body class="antialiased overflow-x-hidden relative flex flex-col min-h-screen bg-pattern">

    <!-- Navigation -->
    <nav class="fixed w-full z-50 transition-all duration-300 backdrop-blur-md bg-app-bg/80 border-b border-white/5">
        <div class="max-w-7xl mx-auto px-6 h-20 flex items-center justify-between">
            <a href="/" class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-xl bg-gradient-to-br from-app-primary to-app-primaryDark flex items-center justify-center font-bold text-app-bg text-xl">
                    W
                </div>
                <span class="text-2xl font-bold tracking-tight text-white">WhatGame</span>
            </a>
            <div>
                <a href="/" class="px-6 py-2.5 rounded-full bg-app-primary/10 text-app-primary border border-app-primary/20 hover:bg-app-primary hover:text-app-bg transition-all font-medium">Back to Home</a>
            </div>
        </div>
    </nav>

    <!-- Content -->
    <main class="relative pt-32 pb-20 px-6 flex-grow">
        <div class="max-w-4xl mx-auto glass-panel p-8 md:p-12 rounded-3xl">
            <h1 class="text-4xl md:text-5xl font-extrabold mb-8 text-transparent bg-clip-text bg-gradient-to-br from-white to-app-primary/80">Terms of Service</h1>

            <div class="space-y-6 text-app-fg/80 leading-relaxed">
                <p>Welcome to WhatGame. By accessing or using our application, you agree to be bound by these simple, generic Terms of Service. If you do not agree, please do not use WhatGame.</p>

                <h2 class="text-2xl font-bold text-white mt-8 mb-4">1. General Usage</h2>
                <p>WhatGame provides a platform for gaming groups to vote and decide on their next games. You agree to use the service only for lawful purposes and in a way that does not infringe on the rights of others or restrict their use and enjoyment of the application.</p>

                <h2 class="text-2xl font-bold text-white mt-8 mb-4">2. Account Responsibilities</h2>
                <p>You are responsible for safeguarding your account details and for any activities or actions under your account. You must notify us immediately upon becoming aware of any breach of security or unauthorized use of your account.</p>

                <h2 class="text-2xl font-bold text-white mt-8 mb-4">3. Prohibited Conduct</h2>
                <p>Users may not:</p>
                <ul class="list-disc pl-6 space-y-2 mt-2">
                    <li>Attempt to gain unauthorized access to any parts of the service or its related systems.</li>
                    <li>Interfere with or disrupt the functional integrity or performance of the platform.</li>
                    <li>Use the service to transmit malicious code, spam, or perform automated data scraping.</li>
                </ul>

                <h2 class="text-2xl font-bold text-white mt-8 mb-4">4. Provided "As Is"</h2>
                <p>WhatGame is provided to you on an "AS IS" and "AS AVAILABLE" basis without making warranties of any kind regarding correctness, performance, or availability. We assume no liability for any errors or disruptions in the service.</p>

                <h2 class="text-2xl font-bold text-white mt-8 mb-4">5. Change of Terms</h2>
                <p>We hold the right, at our sole discretion, to modify or replace these Terms at any given time. Continuing to use WhatGame after any revisions become effective indicates your agreement to those changes.</p>

                <p class="mt-8 text-sm opacity-50">Last Updated: April 2026</p>
            </div>
        </div>
    </main>

    <!-- Footer -->
    <footer class="py-8 relative z-10 border-t border-white/5 bg-app-bg">
        <div class="max-w-7xl mx-auto px-6 flex flex-col md:flex-row items-center justify-between gap-4 text-sm text-app-fg/50">
            <div>&copy; {{ date('Y') }} WhatGame. All rights reserved.</div>
            <div class="flex items-center gap-6">
                <a href="/privacy-policy" class="hover:text-white transition-colors">Privacy Policy</a>
                <a href="/terms-of-service" class="hover:text-white transition-colors">Terms of Service</a>
            </div>
        </div>
    </footer>
</body>
</html>
