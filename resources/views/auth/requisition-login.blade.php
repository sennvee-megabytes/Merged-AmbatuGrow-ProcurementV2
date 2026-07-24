<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login · Purchase Requisition & Approval</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        body{ font-family:'Inter',sans-serif; background:#e5e7eb; }
        .btn-primary{ background:#2563eb; }
        .btn-primary:hover{ background:#1d4ed8; }
    </style>
</head>
<body class="min-h-screen flex items-center justify-center px-4">

    <div class="w-full max-w-md bg-[#e9eaec] border border-slate-300/70 rounded-xl shadow-sm p-10">

        <div class="flex flex-col items-center text-center mb-6">
            <div class="w-14 h-14 rounded-xl bg-blue-50 flex items-center justify-center mb-4">
                <i class="fa-solid fa-clipboard-check text-blue-600 text-2xl"></i>
            </div>
            <h1 class="text-xl font-bold text-slate-800">Purchase Requisition &amp; Approval</h1>
            <p class="text-slate-500 font-medium mt-1">Authorized Approver Login</p>
            <p class="text-slate-400 text-xs mt-2 max-w-xs">
                Log in to review and approve purchase requisitions routed to you.
            </p>
        </div>

        @if ($errors->any())
            <div class="mb-4 rounded-lg bg-red-50 border border-red-200 text-red-600 text-sm px-4 py-2.5">
                {{ $errors->first() }}
            </div>
        @endif

        <form method="POST" action="{{ route('login.attempt') }}" class="space-y-3">
            @csrf
            <div class="relative">
                <i class="fa-regular fa-user absolute left-3 top-1/2 -translate-y-1/2 text-slate-400 text-sm"></i>
                <input type="text" name="username" value="{{ old('username') }}" placeholder="Username"
                       autofocus required
                       class="w-full pl-9 pr-3 py-2.5 rounded-md bg-white border border-slate-300 text-sm focus:outline-none focus:ring-2 focus:ring-blue-200">
            </div>

            <div class="relative" x-data="{ show: false }">
                <i class="fa-solid fa-lock absolute left-3 top-1/2 -translate-y-1/2 text-slate-400 text-sm"></i>
                <input :type="show ? 'text' : 'password'" name="password" placeholder="Password" required
                       class="w-full pl-9 pr-9 py-2.5 rounded-md bg-white border border-slate-300 text-sm focus:outline-none focus:ring-2 focus:ring-blue-200">
                <button type="button" @click="show = !show" class="absolute right-3 top-1/2 -translate-y-1/2 text-slate-400">
                    <i class="fa-regular" :class="show ? 'fa-eye-slash' : 'fa-eye'"></i>
                </button>
            </div>

            <div class="flex items-center justify-between text-xs text-slate-500 pt-1">
                <label class="flex items-center gap-1.5">
                    <input type="checkbox" name="remember" class="rounded border-slate-300">
                    Remember me
                </label>
                <a href="#" class="text-blue-600 hover:underline">Forgot password?</a>
            </div>

            <button type="submit" class="btn-primary w-full text-white text-sm font-semibold py-2.5 rounded-md mt-3 transition">
                Log in
            </button>
        </form>

        <div class="mt-6 pt-4 border-t border-slate-300/70 text-center">
            <p class="text-[11px] text-slate-400">Demo approver accounts (password: <code>password</code>)</p>
            <p class="text-[11px] text-slate-400 mt-1">jj.miranda &middot; johny.papa &middot; finance.manager</p>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js" defer></script>
</body>
</html>
