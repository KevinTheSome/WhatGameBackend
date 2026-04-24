<!DOCTYPE html>
<html lang="en" class="scroll-smooth">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="Content-Security-Policy" content="upgrade-insecure-requests">
    <title>WhatGame - Decide What to Play</title>
    <link rel="icon" type="image/x-icon" href="/favicon.png">
    <meta name="description" content="WhatGame is the ultimate app to decide what game your group should play next. Group voting, lobbies, and gaming libraries in one place.">
     @vite('resources/css/app.css')
    <!-- Using Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;700;800&display=swap" rel="stylesheet">


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
            transition: transform 0.3s ease, border-color 0.3s ease;
        }
        .glass-panel:hover {
            transform: translateY(-5px);
            border-color: rgba(79, 216, 235, 0.3);
        }
        .hero-glow {
            position: absolute;
            width: 400px;
            height: 400px;
            background: radial-gradient(circle, rgba(79,216,235,0.15) 0%, rgba(0,0,0,0) 70%);
            border-radius: 50%;
            pointer-events: none;
            z-index: 0;
            top: -100px;
            left: -100px;
        }
        .app-mockup {
            box-shadow: 0 25px 50px -12px rgba(79, 216, 235, 0.25);
        }
        .delay-100 { animation-delay: 100ms; }
        .delay-200 { animation-delay: 200ms; }
        .delay-300 { animation-delay: 300ms; }
    </style>
</head>
<body class="antialiased overflow-x-hidden relative bg-pattern">

    <!-- Navigation -->
    <nav class="fixed w-full z-50 transition-all duration-300 backdrop-blur-md bg-app-bg/80 border-b border-white/5">
        <div class="max-w-7xl mx-auto px-6 h-20 flex items-center justify-between">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-xl bg-gradient-to-br from-app-primary to-app-primaryDark flex items-center justify-center font-bold text-app-bg text-xl">
                    W
                </div>
                <span class="text-2xl font-bold tracking-tight text-white">WhatGame</span>
            </div>
            <div class="hidden md:flex items-center gap-8 text-sm font-medium text-app-fg/80">
                <a href="#features" class="hover:text-app-primary transition-colors">Features</a>
                <a href="#how-it-works" class="hover:text-app-primary transition-colors">How it Works</a>
            </div>
            <div>
                <a href="#download" class="px-6 py-2.5 rounded-full bg-app-primary/10 text-app-primary border border-app-primary/20 hover:bg-app-primary hover:text-app-bg transition-all font-medium hidden md:inline-block">Get the App</a>
                <a href="#download" class="md:hidden px-3 py-2.5 rounded-full bg-app-primary/10 text-app-primary border border-app-primary/20 hover:bg-app-primary hover:text-app-bg transition-all flex items-center justify-center" aria-label="Download App">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path></svg>
                </a>
            </div>
        </div>
    </nav>

    <!-- Hero Section -->
    <main class="relative pt-32 pb-20 lg:pt-48 lg:pb-32 px-6">
        <div class="max-w-7xl mx-auto">
            <div class="flex flex-col lg:flex-row items-center gap-16">
                <!-- Text Content -->
                <div class="flex-1 text-center lg:text-left z-10">

                    <h1 class="text-5xl lg:text-7xl font-extrabold leading-tight tracking-tight mb-6 animate-fade-in-up delay-100 text-transparent bg-clip-text bg-gradient-to-br from-white to-app-fg/50">
                        Stop Arguing.<br/>Start <span class="text-app-primary">Playing.</span>
                    </h1>

                    <p class="text-xl text-app-fg/70 mb-10 max-w-2xl mx-auto lg:mx-0 animate-fade-in-up delay-200 leading-relaxed">
                        The ultimate companion app for gaming groups. Create lobbies, build your library, and democratically vote on what to play next in seconds.
                    </p>

                    <div class="flex flex-col sm:flex-row items-center gap-4 justify-center lg:justify-start animate-fade-in-up delay-300">
                        <a href="#download" class="w-full sm:w-auto px-8 py-4 rounded-xl bg-app-primary text-app-bg font-bold text-lg hover:shadow-[0_0_30px_rgba(79,216,235,0.4)] transition-all flex items-center justify-center gap-2 group">
                            Download Now
                            <svg class="w-5 h-5 group-hover:translate-x-1 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"></path></svg>
                        </a>
                        <a href="#features" class="w-full sm:w-auto px-8 py-4 rounded-xl glass-panel font-bold text-lg text-white flex items-center justify-center">
                            Explore Features
                        </a>
                    </div>
                </div>

                <!-- App Mockup Image Placeholder -->
                <div class="flex-1 relative w-full max-w-md mx-auto animate-fade-in-up delay-300 z-10 lg:pl-10">
                    <div class="relative w-full aspect-[1/2] rounded-[2.5rem] bg-app-surfaceVariant border-4 border-app-surfaceVariant overflow-hidden app-mockup shadow-2xl">
                        <!-- Simulated App UI in place of an image -->
                        <div class="absolute inset-0 bg-app-bg flex flex-col pt-8 px-6">
                            <div class="flex justify-between items-center mb-8">
                                <div class="w-10 h-10 rounded-full bg-app-primaryContainer/50"></div>
                                <div class="w-8 h-8 rounded-full bg-app-surfaceVariant"></div>
                            </div>
                            <h3 class="text-2xl font-bold text-white mb-2">Game Night Lobby</h3>
                            <p class="text-app-fg/60 text-sm mb-6">Waiting for 4/5 players...</p>

                            <div class="space-y-3">
                                <div class="bg-app-primaryContainer/30 p-4 rounded-xl flex items-center justify-between border border-app-primary/20">
                                    <div class="flex items-center gap-3">
                                        <div class="w-10 h-10 bg-gray-800 rounded-lg"></div>
                                        <div>
                                            <div class="text-white font-medium text-sm">Valorant</div>
                                            <div class="text-app-primary text-xs">Winning (3 votes)</div>
                                        </div>
                                    </div>
                                </div>
                                <div class="bg-app-surfaceVariant/30 p-4 rounded-xl flex items-center justify-between">
                                    <div class="flex items-center gap-3">
                                        <div class="w-10 h-10 bg-gray-800 rounded-lg"></div>
                                        <div>
                                            <div class="text-white font-medium text-sm">Minecraft</div>
                                            <div class="text-app-fg/60 text-xs">1 vote</div>
                                        </div>
                                    </div>
                                </div>
                                <div class="bg-app-surfaceVariant/30 p-4 rounded-xl flex items-center justify-between">
                                    <div class="flex items-center gap-3">
                                        <div class="w-10 h-10 bg-gray-800 rounded-lg"></div>
                                        <div>
                                            <div class="text-white font-medium text-sm">Among Us</div>
                                            <div class="text-app-fg/60 text-xs">0 votes</div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="mt-auto pb-6">
                                <div class="w-full bg-app-primary text-app-bg py-3 rounded-xl font-bold text-center">Cast Your Vote</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </main>



    <!-- Features Section -->
    <section id="features" class="py-24 relative z-10 border-t border-white/5 bg-black/20">
        <div class="max-w-7xl mx-auto px-6">
            <div class="text-center mb-16">
                <h2 class="text-sm font-bold text-app-primary uppercase tracking-widest mb-2">Everything You Need</h2>
                <h3 class="text-3xl md:text-5xl font-bold text-white">Your Gaming Hub</h3>
            </div>

            <div class="grid md:grid-cols-3 gap-8">
                <!-- Feature 1 -->
                <div class="glass-panel p-8 rounded-3xl">
                    <div class="w-14 h-14 rounded-2xl bg-app-primary/10 flex items-center justify-center mb-6 text-app-primary">
                        <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path></svg>
                    </div>
                    <h4 class="text-xl font-bold text-white mb-3">Group Lobbies</h4>
                    <p class="text-app-fg/70 leading-relaxed">Instantly gather your friends into dynamic lobbies. See who's online, ready to play, and invite missing players with one tap.</p>
                </div>

                <!-- Feature 2 -->
                <div class="glass-panel p-8 rounded-3xl relative overflow-hidden group border-app-primary/30">
                    <div class="absolute inset-0 bg-gradient-to-br from-app-primary/5 to-transparent opacity-0 group-hover:opacity-100 transition-opacity"></div>
                    <div class="w-14 h-14 rounded-2xl bg-app-primary flex items-center justify-center mb-6 text-app-bg relative z-10 shadow-[0_0_15px_rgba(79,216,235,0.5)]">
                        <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                    </div>
                    <h4 class="text-xl font-bold text-white mb-3 relative z-10">Live Voting</h4>
                    <p class="text-app-fg/70 leading-relaxed relative z-10">No more endless debate. Propose games, cast your votes, and let democracy decide the next adventure for your squad.</p>
                </div>

                <!-- Feature 3 -->
                <div class="glass-panel p-8 rounded-3xl">
                    <div class="w-14 h-14 rounded-2xl bg-app-tertiary/10 flex items-center justify-center mb-6 text-app-tertiary">
                        <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 8h14M5 8a2 2 0 110-4h14a2 2 0 110 4M5 8v10a2 2 0 002 2h10a2 2 0 002-2V8m-9 4h4"></path></svg>
                    </div>
                    <h4 class="text-xl font-bold text-white mb-3">Game Library</h4>
                    <p class="text-app-fg/70 leading-relaxed">Keep track of everyone's owned games. Cross-reference libraries automatically to only vote on games everyone has installed.</p>
                </div>
            </div>
        </div>
    </section>

     <!-- How it Works Section -->
    <section id="how-it-works" class="py-24 relative z-10">
        <div class="max-w-7xl mx-auto px-6">
            <div class="text-center mb-16">
                <h2 class="text-sm font-bold text-app-tertiary uppercase tracking-widest mb-2">3 Simple Steps</h2>
                <h3 class="text-3xl md:text-5xl font-bold text-white">How WhatGame Works</h3>
            </div>

            <div class="flex flex-col md:flex-row gap-8 relative">
                <!-- Connecting Line (hidden on mobile) -->
                <div class="hidden md:block absolute top-1/2 left-[10%] right-[10%] h-0.5 bg-gradient-to-r from-app-primary/0 via-app-primary/30 to-app-primary/0 -translate-y-1/2 z-0 hidden"></div>

                <!-- Step 1 -->
                <div class="flex-1 text-center relative z-10 group">
                    <div class="w-20 h-20 mx-auto rounded-full glass-panel flex items-center justify-center mb-6 text-2xl font-bold text-white border-2 border-app-primary/20 group-hover:border-app-primary transition-colors shadow-[0_0_20px_rgba(79,216,235,0.1)] group-hover:shadow-[0_0_30px_rgba(79,216,235,0.3)] bg-app-bg">
                        1
                    </div>
                    <h4 class="text-xl font-bold text-white mb-3">Create a Lobby</h4>
                    <p class="text-app-fg/70">Connect with friends to see everyone's current game libraries in one place.</p>
                </div>

                <!-- Step 2 -->
                <div class="flex-1 text-center relative z-10 group">
                    <div class="w-20 h-20 mx-auto rounded-full glass-panel flex items-center justify-center mb-6 text-2xl font-bold text-white border-2 border-app-tertiary/20 group-hover:border-app-tertiary transition-colors shadow-[0_0_20px_rgba(186,198,234,0.1)] group-hover:shadow-[0_0_30px_rgba(186,198,234,0.3)] bg-app-bg">
                        2
                    </div>
                    <h4 class="text-xl font-bold text-white mb-3">Filter Options</h4>
                    <p class="text-app-fg/70">Only games that everyone in the lobby owns will show up as options.</p>
                </div>

                <!-- Step 3 -->
                <div class="flex-1 text-center relative z-10 group">
                    <div class="w-20 h-20 mx-auto rounded-full glass-panel flex items-center justify-center mb-6 text-2xl font-bold text-white border-2 border-app-primaryDark/40 group-hover:border-app-primaryDark transition-colors shadow-[0_0_20px_rgba(0,104,116,0.1)] group-hover:shadow-[0_0_30px_rgba(0,104,116,0.4)] bg-app-bg">
                        3
                    </div>
                    <h4 class="text-xl font-bold text-white mb-3">Vote & Play</h4>
                    <p class="text-app-fg/70">Democratically decide the game, then jump straight into the action.</p>
                </div>
            </div>
        </div>
    </section>

    <!-- Download CTA Section -->
    <section id="download" class="py-24 relative z-10 bg-black/20">
        <div class="max-w-4xl mx-auto px-6 text-center">
            <h2 class="text-3xl md:text-5xl font-bold text-white mb-6">Ready to end the debate?</h2>
            <p class="text-xl text-app-fg/70 mb-10">Download WhatGame today and get straight to gaming.</p>

            <div class="flex justify-center">
                <a href="https://drive.google.com/file/d/1I2GtcQ3RvgmtzSB-pFx-ttH9XRBcaK_N/view?usp=sharing" class="px-8 py-4 rounded-xl bg-app-primary text-app-bg font-bold text-lg hover:shadow-[0_0_30px_rgba(79,216,235,0.4)] transition-all flex items-center justify-center gap-3 group">
                    <span>Download App</span>
                    <svg class="w-5 h-5 group-hover:translate-y-1 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path></svg>
                </a>
            </div>
        </div>
    </section>

    <!-- Footer -->
    <footer class="py-8 relative z-10 border-t border-white/5">
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
