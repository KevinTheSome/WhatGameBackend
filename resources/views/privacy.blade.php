<!DOCTYPE html>
<html lang="en" class="scroll-smooth">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Privacy Policy - WhatGame</title>
    
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
            <h1 class="text-4xl md:text-5xl font-extrabold mb-8 text-transparent bg-clip-text bg-gradient-to-br from-white to-app-primary/80">Privacy Policy</h1>
            
            <div class="space-y-6 text-app-fg/80 leading-relaxed">
                <p>Welcome to the Privacy Policy for WhatGame. We are highly committed to retaining your trust. As such, we believe in full transparency regarding exactly what happens with your data.</p>
                
                <h2 class="text-2xl font-bold text-white mt-8 mb-4">1. No Data Selling</h2>
                <p><strong>We do not, and will never, sell your personal data to any third parties.</strong> Your privacy is a priority, and all collected data is strictly used to provide, improve, and secure the WhatGame service.</p>
                
                <h2 class="text-2xl font-bold text-white mt-8 mb-4">2. What Information We Collect</h2>
                <p>In order for WhatGame to function and serve your groups properly, we save the following information from our users:</p>
                <ul class="list-disc pl-6 space-y-2 mt-2">
                    <li><strong>Account Information:</strong> Your display name, email address, and profile picture (especially when utilizing Google Sign-In).</li>
                    <li><strong>Application Usage History:</strong> We store your historical interactions, including lobbies you have created and joined, as well as your game voting history.</li>
                    <li><strong>Social Connections:</strong> Your in-app friends list, allowing you to easily manage and create lobbies with people you know.</li>
                    <li><strong>Platform Statistics:</strong> Data pertaining to your general profile statistics shown in the application.</li>
                    <li><strong>Security and Analytical Data:</strong> Standard connection details, including your IP address and User Agent, are recorded for secure session management and fraud prevention.</li>
                </ul>

                <h2 class="text-2xl font-bold text-white mt-8 mb-4">3. How We Use the Information</h2>
                <p>The aforementioned data is strictly utilized for the following purposes:</p>
                <ul class="list-disc pl-6 space-y-2 mt-2">
                    <li>To authenticate you and keep your account secure.</li>
                    <li>To facilitate group creation, show historical gaming trends, and provide all interactive features.</li>
                    <li>To track system stability and ensure optimal performance for all users.</li>
                </ul>

                <h2 class="text-2xl font-bold text-white mt-8 mb-4">4. Your Control</h2>
                <p>You have the right to request access to and deletion of any personal data we have collected. Should you have any inquiries, feel free to reach out to our team.</p>

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
