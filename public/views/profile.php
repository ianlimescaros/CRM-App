<section data-page="profile" class="relative">
    <div class="absolute inset-0 bg-gradient-to-br from-slate-50 via-white to-blue-50"></div>
    <div class="relative max-w-6xl mx-auto px-4 py-6">
        <div class="flex flex-col lg:flex-row gap-5">
            <div class="bg-white rounded-2xl shadow-md border border-slate-100 p-6 w-full lg:w-1/3">
                <div class="flex flex-col items-center text-center">
                    <div class="h-28 w-28 rounded-full overflow-hidden bg-slate-100 border border-slate-200 shadow-sm mb-3">
                        <img src="https://i.pravatar.cc/200" alt="avatar" class="w-full h-full object-cover">
                    </div>
                    <h2 id="profileNameDisplay" class="text-xl font-semibold text-slate-900">User Name</h2>
                    <span class="mt-2 inline-flex items-center px-3 py-1 rounded-full bg-emerald-50 text-emerald-700 text-xs font-semibold">Active</span>
                    <div class="w-full mt-4">
                        <p class="text-xs text-slate-500">Trust</p>
                        <div class="h-2 rounded-full bg-slate-100 overflow-hidden">
                            <div id="profileTrustBar" class="h-2 bg-emerald-500 rounded-full" style="width: 80%"></div>
                        </div>
                    </div>
                    <div class="mt-4 text-sm text-slate-600">
                        <div class="flex items-center gap-2">
                            <span class="font-medium">Email:</span>
                            <span id="profileEmailDisplay" class="text-slate-800">--</span>
                        </div>
                        <div class="flex items-center gap-2">
                            <span class="font-medium">Security Code:</span>
                            <span id="profileCodeDisplay" class="text-indigo-600">--</span>
                        </div>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-2xl shadow-md border border-slate-100 p-6 flex-1">
                <div class="flex items-center justify-between mb-4">
                    <div>
                        <p class="text-xs uppercase tracking-[0.25em] text-slate-500">Account</p>
                        <h1 class="text-2xl font-bold text-slate-900">User Profile</h1>
                    </div>
                    <span id="profileSaved" class="hidden text-xs text-emerald-700 bg-emerald-50 border border-emerald-100 rounded-full px-3 py-1">Saved</span>
                </div>
                <div class="grid sm:grid-cols-2 gap-4 mb-6 text-sm text-slate-700">
                    <div class="bg-slate-50 rounded-xl border border-slate-100 p-4">
                        <p class="text-xs text-slate-500 mb-1">Company Profile</p>
                        <div class="flex justify-between"><span>Company Name</span><span class="font-semibold text-slate-900">N/A</span></div>
                        <div class="flex justify-between"><span>Website</span><span class="font-semibold text-slate-900">N/A</span></div>
                        <div class="flex justify-between"><span>Group</span><span class="font-semibold text-slate-900">N/A</span></div>
                    </div>
                    <div class="bg-slate-50 rounded-xl border border-slate-100 p-4">
                        <p class="text-xs text-slate-500 mb-1">Settings</p>
                        <div class="flex items-center justify-between">
                            <span>Emergency</span><span class="text-indigo-600 font-semibold">8/11</span>
                        </div>
                        <div class="flex items-center justify-between mt-2">
                            <span>Notifications</span>
                            <span class="relative inline-flex h-5 w-10 items-center rounded-full bg-emerald-200">
                                <span class="absolute left-1 h-4 w-4 rounded-full bg-white shadow"></span>
                            </span>
                        </div>
                    </div>
                </div>
                <div class="border-t border-slate-200 pt-4">
                    <form id="profileForm" class="grid gap-4 sm:grid-cols-2">
                        <div class="sm:col-span-1">
                            <label class="block text-sm font-medium text-slate-700">Name</label>
                            <input type="text" name="name" class="w-full mt-1 border border-border px-3 py-3 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-400" placeholder="Your name">
                        </div>
                        <div class="sm:col-span-1">
                            <label class="block text-sm font-medium text-slate-700">Email</label>
                            <input type="email" name="email" class="w-full mt-1 border border-border px-3 py-3 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-400" placeholder="you@example.com">
                        </div>
                        <div class="sm:col-span-1">
                            <label class="block text-sm font-medium text-slate-700">New Password</label>
                            <input type="password" name="password" class="w-full mt-1 border border-border px-3 py-3 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-400" placeholder="Leave blank to keep current">
                        </div>
                        <div class="sm:col-span-1 flex items-end">
                            <button type="submit" class="w-full sm:w-auto px-5 py-3 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 transition shadow-sm">Save changes</button>
                        </div>
                        <div id="profileError" class="sm:col-span-2 text-sm text-red-600 bg-red-50 border border-red-100 rounded-lg px-3 py-2 hidden"></div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</section>
