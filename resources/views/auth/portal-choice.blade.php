<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Choose Workspace — Portfolio CMS</title>
    @vite(['resources/css/app.css'])
</head>
<body class="min-h-screen bg-slate-950 text-slate-100 flex items-center justify-center p-6">
    <main class="w-full max-w-xl rounded-2xl border border-slate-800 bg-slate-900/90 p-8 shadow-2xl space-y-6">
        <div class="space-y-2">
            <p class="text-xs font-semibold uppercase tracking-widest text-indigo-400">Active Sessions Detected</p>
            <h1 class="text-2xl font-bold tracking-tight text-white">Choose a portal to continue</h1>
            <p class="text-sm text-slate-400">
                Both an Individual Admin session and a Super Admin session are currently active in this browser.
                Select the workspace you want to open.
            </p>
        </div>

        <div class="grid gap-4 sm:grid-cols-2">
            <div class="rounded-xl border border-slate-800 bg-slate-950/60 p-5 flex flex-col justify-between gap-4">
                <div>
                    <span class="text-xs font-semibold uppercase tracking-wider text-emerald-400">Super Admin</span>
                    <h2 class="mt-1 font-semibold text-white">{{ $superAdmin->name }}</h2>
                    <p class="text-xs text-slate-400 truncate">{{ $superAdmin->email }}</p>
                </div>
                <div class="space-y-2">
                    <a href="{{ route('admin.dashboard') }}" class="block w-full text-center rounded-lg bg-indigo-500 hover:bg-indigo-400 px-4 py-2 text-sm font-semibold text-white transition">
                        Open Super Admin
                    </a>
                    <form method="POST" action="{{ route('admin.logout') }}">
                        @csrf
                        <button type="submit" class="w-full rounded-lg border border-slate-700 hover:border-slate-500 px-3 py-1.5 text-xs text-slate-300 transition">
                            Sign out Super Admin
                        </button>
                    </form>
                </div>
            </div>

            <div class="rounded-xl border border-slate-800 bg-slate-950/60 p-5 flex flex-col justify-between gap-4">
                <div>
                    <span class="text-xs font-semibold uppercase tracking-wider text-sky-400">Individual Admin</span>
                    <h2 class="mt-1 font-semibold text-white">{{ $individualAdmin->name }}</h2>
                    <p class="text-xs text-slate-400 truncate">{{ $individualAdmin->email }}</p>
                </div>
                <div class="space-y-2">
                    <a href="{{ route('dashboard.index') }}" class="block w-full text-center rounded-lg bg-sky-500 hover:bg-sky-400 px-4 py-2 text-sm font-semibold text-white transition">
                        Open Dashboard
                    </a>
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit" class="w-full rounded-lg border border-slate-700 hover:border-slate-500 px-3 py-1.5 text-xs text-slate-300 transition">
                            Sign out Individual Admin
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </main>
</body>
</html>
