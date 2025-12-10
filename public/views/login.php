<section class="min-h-screen flex justify-center relative overflow-hidden bg-white" data-page="login">
    <img src="https://pagedone.io/asset/uploads/1702362010.png" alt="gradient background image" class="w-full h-full object-cover fixed inset-0">
    <div class="absolute inset-0 bg-white/70"></div>
    <div class="mx-auto max-w-lg px-6 lg:px-8 absolute py-20">
        <img src="https://pagedone.io/asset/uploads/1702362108.png" alt="logo" class="mx-auto lg:mb-11 mb-8 object-contain h-10">
        <div class="rounded-2xl bg-white shadow-xl border border-gray-200">
            <form id="loginForm" class="lg:p-11 p-7 mx-auto space-y-4">
                <div id="loginError" class="text-sm text-red-600 bg-red-50 border border-red-100 rounded-lg px-3 py-2 hidden"></div>
                <div class="mb-6 text-center">
                    <h1 class="text-gray-900 font-bold text-3xl leading-10 mb-2">Welcome Back</h1>
                    <p class="text-gray-500 text-base leading-6">Let’s get started</p>
                </div>
                <label class="sr-only" for="loginEmail">Email</label>
                <input id="loginEmail" type="email" name="email" autocomplete="email" class="w-full h-12 text-gray-900 placeholder:text-gray-400 text-lg rounded-full border-gray-300 border shadow-sm focus:outline-none focus:ring-2 focus:ring-indigo-400 px-4" placeholder="Email" required>
                <label class="sr-only" for="loginPassword">Password</label>
                <input id="loginPassword" type="password" name="password" autocomplete="current-password" class="w-full h-12 text-gray-900 placeholder:text-gray-400 text-lg rounded-full border-gray-300 border shadow-sm focus:outline-none focus:ring-2 focus:ring-indigo-400 px-4" placeholder="Password" required>
                <div class="flex justify-end">
                    <button type="button" id="showForgot" class="text-indigo-600 text-base hover:text-indigo-700">Forgot Password?</button>
                </div>
                <button type="submit" class="w-full h-12 text-white text-base font-semibold rounded-full hover:bg-indigo-800 transition bg-indigo-600 shadow-sm">Login</button>
                <div class="flex justify-center text-gray-900 text-base">
                    Don’t have an account?
                    <button type="button" id="showRegister" class="text-indigo-600 font-semibold pl-2 hover:text-indigo-700">Sign Up</button>
                </div>
            </form>

            <form id="registerForm" class="lg:p-11 p-7 mx-auto space-y-4 hidden">
                <div id="registerError" class="text-sm text-red-600 bg-red-50 border border-red-100 rounded-lg px-3 py-2 hidden"></div>
                <div class="mb-4 text-center">
                    <h2 class="text-gray-900 font-bold text-2xl">Create Account</h2>
                </div>
                <label class="sr-only" for="registerName">Name</label>
                <input id="registerName" type="text" name="name" autocomplete="name" class="w-full h-12 text-gray-900 placeholder:text-gray-400 text-lg rounded-full border-gray-300 border shadow-sm focus:outline-none focus:ring-2 focus:ring-indigo-400 px-4" placeholder="Name">
                <label class="sr-only" for="registerEmail">Email</label>
                <input id="registerEmail" type="email" name="email" autocomplete="email" class="w-full h-12 text-gray-900 placeholder:text-gray-400 text-lg rounded-full border-gray-300 border shadow-sm focus:outline-none focus:ring-2 focus:ring-indigo-400 px-4" placeholder="Email">
                <label class="sr-only" for="registerPassword">Password</label>
                <input id="registerPassword" type="password" name="password" autocomplete="new-password" class="w-full h-12 text-gray-900 placeholder:text-gray-400 text-lg rounded-full border-gray-300 border shadow-sm focus:outline-none focus:ring-2 focus:ring-indigo-400 px-4" placeholder="Password">
                <button type="submit" class="w-full h-12 text-white text-base font-semibold rounded-full hover:bg-emerald-700 transition bg-emerald-600 shadow-sm">Register</button>
                <div class="flex justify-center text-base text-indigo-600">
                    <button type="button" id="backToLoginFromRegister" class="hover:text-indigo-700">Back to login</button>
                </div>
            </form>

            <form id="forgotForm" class="lg:p-11 p-7 mx-auto space-y-4 hidden">
                <div id="forgotError" class="text-sm text-red-600 bg-red-50 border border-red-100 rounded-lg px-3 py-2 hidden"></div>
                <div id="forgotSuccess" class="text-sm text-emerald-700 bg-emerald-50 border border-emerald-100 rounded-lg px-3 py-2 hidden"></div>
                <div class="mb-4 text-center">
                    <h2 class="text-gray-900 font-bold text-2xl">Reset Password</h2>
                </div>
                <label class="sr-only" for="forgotEmail">Email</label>
                <input id="forgotEmail" type="email" name="email" autocomplete="email" class="w-full h-12 text-gray-900 placeholder:text-gray-400 text-lg rounded-full border-gray-300 border shadow-sm focus:outline-none focus:ring-2 focus:ring-indigo-400 px-4" placeholder="Email" required>
                <button type="submit" class="w-full h-12 text-white text-base font-semibold rounded-full hover:bg-blue-700 transition bg-blue-600 shadow-sm">Send reset link</button>
                <div class="flex justify-center text-base text-indigo-600 gap-4">
                    <button type="button" id="showResetForm" class="hover:text-indigo-700">Have a token?</button>
                    <button type="button" id="backToLoginFromForgot" class="hover:text-indigo-700">Back to login</button>
                </div>
            </form>

            <form id="resetForm" class="lg:p-11 p-7 mx-auto space-y-4 hidden">
                <div id="resetError" class="text-sm text-red-600 bg-red-50 border border-red-100 rounded-lg px-3 py-2 hidden"></div>
                <div id="resetSuccess" class="text-sm text-emerald-700 bg-emerald-50 border border-emerald-100 rounded-lg px-3 py-2 hidden"></div>
                <div class="mb-4 text-center">
                    <h2 class="text-gray-900 font-bold text-2xl">Set a new password</h2>
                </div>
                <input type="hidden" name="token">
                <label class="sr-only" for="resetEmail">Email</label>
                <input id="resetEmail" type="email" name="email" autocomplete="email" class="w-full h-12 text-gray-900 placeholder:text-gray-400 text-lg rounded-full border-gray-300 border shadow-sm focus:outline-none focus:ring-2 focus:ring-indigo-400 px-4" placeholder="Email" required>
                <div class="space-y-2">
                    <label class="block text-sm text-gray-700 text-center">Enter the 6-digit code</label>
                    <div id="resetCodeGroup" class="flex justify-center gap-2">
                        <?php for ($i = 0; $i < 6; $i++): ?>
                            <input type="text" maxlength="1" inputmode="numeric" pattern="\d" data-reset-code class="w-12 h-12 text-center text-lg font-semibold text-gray-900 rounded-lg border border-gray-300 shadow-sm focus:outline-none focus:ring-2 focus:ring-indigo-400" aria-label="Reset code digit <?php echo $i + 1; ?>" name="reset_digit_<?php echo $i; ?>">
                        <?php endfor; ?>
                    </div>
                </div>
                <label class="sr-only" for="resetPassword">New password</label>
                <input id="resetPassword" type="password" name="password" autocomplete="new-password" class="w-full h-12 text-gray-900 placeholder:text-gray-400 text-lg rounded-full border-gray-300 border shadow-sm focus:outline-none focus:ring-2 focus:ring-indigo-400 px-4" placeholder="New password" required>
                <button type="submit" class="w-full h-12 text-white text-base font-semibold rounded-full hover:bg-emerald-700 transition bg-emerald-600 shadow-sm">Reset password</button>
                <div class="flex justify-center text-base text-indigo-600">
                    <button type="button" id="backToLoginFromReset" class="hover:text-indigo-700">Back to login</button>
                </div>
            </form>
        </div>
    </div>
</section>
