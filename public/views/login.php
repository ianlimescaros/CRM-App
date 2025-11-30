<section class="min-h-[70vh] flex items-center justify-center bg-page" data-page="login">
    <div class="w-full max-w-md bg-white border border-border rounded-card shadow-card p-6">
        <h1 class="text-2xl font-semibold mb-1">Welcome back</h1>
        <p class="text-sm text-muted mb-4">Sign in to continue</p>
        <form id="loginForm" class="space-y-3">
            <div id="loginError" class="text-sm text-red-600 hidden"></div>
            <div>
                <label class="block text-sm">Email</label>
                <input type="email" name="email" class="w-full border border-border px-3 py-2 rounded focus:outline-none focus:ring-2 focus:ring-accent/40" required>
            </div>
            <div>
                <label class="block text-sm">Password</label>
                <input type="password" name="password" class="w-full border border-border px-3 py-2 rounded focus:outline-none focus:ring-2 focus:ring-accent/40" required>
            </div>
            <div class="flex gap-2">
                <button type="submit" class="px-4 py-2 bg-accent text-white rounded hover:bg-blue-700 transition">Login</button>
                <button type="button" id="showRegister" class="px-4 py-2 border border-border rounded hover:bg-gray-100 transition">Create account</button>
            </div>
        </form>

        <form id="registerForm" class="space-y-3 mt-6 hidden">
            <div id="registerError" class="text-sm text-red-600 hidden"></div>
            <h2 class="text-xl font-semibold">Register</h2>
            <div>
                <label class="block text-sm">Name</label>
                <input type="text" name="name" class="w-full border border-border px-3 py-2 rounded focus:outline-none focus:ring-2 focus:ring-accent/40">
            </div>
            <div>
                <label class="block text-sm">Email</label>
                <input type="email" name="email" class="w-full border border-border px-3 py-2 rounded focus:outline-none focus:ring-2 focus:ring-accent/40">
            </div>
            <div>
                <label class="block text-sm">Password</label>
                <input type="password" name="password" class="w-full border border-border px-3 py-2 rounded focus:outline-none focus:ring-2 focus:ring-accent/40">
            </div>
            <button type="submit" class="px-4 py-2 bg-emerald-600 text-white rounded hover:bg-emerald-700 transition">Register</button>
        </form>
    </div>
</section>
