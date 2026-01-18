<!-- View template for the AI assistant page. -->

<section data-page="ai-assistant" class="max-w-4xl mx-auto">
    <h1 class="text-2xl font-semibold mb-4">AI Assistant</h1>

    <div class="grid gap-4 md:grid-cols-2">
        <div class="bg-gradient-to-r from-blue-500 via-purple-500 to-emerald-500 p-[1px] rounded-card shadow-card h-full">
            <div class="bg-white rounded-card p-4 h-full flex flex-col">
                <h2 class="font-semibold mb-2">Summarize Notes</h2>
                <form id="aiSummarizeForm" class="space-y-3">
                    <label class="sr-only" for="aiNotes">Notes</label>
                    <textarea id="aiNotes" name="notes" autocomplete="off" rows="6" class="w-full border border-border px-3 py-2 rounded focus:outline-none focus:ring-2 focus:ring-accent/40" placeholder="Paste notes here"></textarea>
                    <div class="flex items-center gap-3">
                        <button class="px-4 py-2 bg-blue-600 text-white rounded" type="submit">Summarize</button>
                        <div id="aiSummaryStatus" class="text-sm text-gray-500 flex items-center gap-2"></div>
                        <button id="aiSummaryClear" type="button" class="text-sm text-gray-600 underline">Clear</button>
                    </div>
                </form>
                <div class="flex items-center justify-between mt-2">
                    <span class="text-xs text-gray-500">Output</span>
                    <button id="aiSummaryCopy" type="button" class="text-xs text-blue-600">Copy</button>
                </div>
                <pre id="aiSummaryResult" class="mt-1 text-sm bg-gray-50 border rounded p-3 whitespace-pre-wrap break-words flex-1"></pre>
            </div>
        </div>

        <div class="bg-gradient-to-r from-blue-500 via-purple-500 to-emerald-500 p-[1px] rounded-card shadow-card h-full">
            <div class="bg-white rounded-card p-4 h-full flex flex-col">
                <h2 class="font-semibold mb-2">Suggest Follow-up</h2>
                <form id="aiFollowupForm" class="space-y-3">
                    <div>
                        <label class="block text-sm" for="aiLeadName">Lead Name</label>
                        <input id="aiLeadName" name="lead_name" autocomplete="name" class="w-full border border-border px-3 py-2 rounded focus:outline-none focus:ring-2 focus:ring-accent/40">
                    </div>
                    <div>
                        <label class="block text-sm" for="aiContext">Context</label>
                        <textarea id="aiContext" name="context" autocomplete="off" rows="4" class="w-full border border-border px-3 py-2 rounded focus:outline-none focus:ring-2 focus:ring-accent/40"></textarea>
                    </div>
                    <div class="flex items-center gap-3">
                        <button class="px-4 py-2 bg-blue-600 text-white rounded" type="submit">Generate</button>
                        <div id="aiFollowupStatus" class="text-sm text-gray-500 flex items-center gap-2"></div>
                        <button id="aiFollowupClear" type="button" class="text-sm text-gray-600 underline">Clear</button>
                    </div>
                </form>
                <div class="flex items-center justify-between mt-2">
                    <span class="text-xs text-gray-500">Output</span>
                    <button id="aiFollowupCopy" type="button" class="text-xs text-blue-600">Copy</button>
                </div>
                <pre id="aiFollowupResult" class="mt-1 text-sm bg-gray-50 border rounded p-3 whitespace-pre-wrap break-words flex-1"></pre>
            </div>
        </div>
    </div>
</section>
