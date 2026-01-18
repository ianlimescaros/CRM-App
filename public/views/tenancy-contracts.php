<!-- View template for the tenancy contracts page. -->

<section data-page="tenancy-contracts" class="w-full px-4">
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 lg:gap-16 items-start">
        <!-- Left: input form -->
        <div class="bg-white border border-border rounded-card shadow-card p-6 space-y-4 tenancy-form max-h-[1130px] overflow-y-auto">
            <div class="flex items-center justify-between gap-4">
                <h1 class="text-xl font-semibold">Tenancy Contract Details</h1>
                <button
                    type="submit"
                    form="tenancyContractForm"
                    class="inline-flex items-center justify-center rounded-md bg-indigo-600 px-4 py-2 text-sm font-medium text-white shadow hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2"
                >
                    Download filled PDF
                </button>
            </div>

            <form
                id="tenancyContractForm"
                action="/api.php/tenancy-contracts/pdf"
                method="POST"
                target="_blank"
                class="space-y-6"
            >
                <!-- Header -->
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Date</label>
                        <input
                            type="date"
                            name="today_date"
                            data-preview="spaced"
                            class="w-full rounded-md border border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                            placeholder="e.g. 28  10  2025"
                        >
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Contract No.</label>
                        <input
                            type="text"
                            name="no_name"
                            class="w-full rounded-md border border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                            placeholder="Contract number"
                        >
                    </div>
                </div>

                <!-- Property usage -->
                <div>
                    <span class="block text-sm font-medium text-gray-700 mb-1">Property usage</span>
                    <div class="flex flex-wrap gap-4 text-sm">
                        <label class="inline-flex items-center gap-2">
                            <input type="radio" name="property_usage" value="industrial" class="rounded border-gray-300 text-indigo-600">
                            <span>Industrial</span>
                        </label>
                        <label class="inline-flex items-center gap-2">
                            <input type="radio" name="property_usage" value="commercial" class="rounded border-gray-300 text-indigo-600">
                            <span>Commercial</span>
                        </label>
                        <label class="inline-flex items-center gap-2">
                            <input type="radio" name="property_usage" value="residential" class="rounded border-gray-300 text-indigo-600">
                            <span>Residential</span>
                        </label>
                    </div>
                </div>

                <!-- Owner / Landlord -->
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Owner name</label>
                        <input
                            type="text"
                            name="owner_name"
                            class="w-full rounded-md border border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                        >
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Landlord name</label>
                        <input
                            type="text"
                            name="landlord_name"
                            class="w-full rounded-md border border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                        >
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Landlord email</label>
                        <input
                            type="email"
                            name="landlord_email"
                            class="w-full rounded-md border border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                        >
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Landlord phone</label>
                        <input
                            type="text"
                            name="landlord_phone"
                            class="w-full rounded-md border border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                        >
                    </div>
                </div>

                <!-- Tenant -->
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Tenant name</label>
                        <input
                            type="text"
                            name="tenant_name"
                            class="w-full rounded-md border border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                        >
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Tenant email</label>
                        <input
                            type="email"
                            name="tenant_email"
                            class="w-full rounded-md border border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                        >
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Tenant phone</label>
                        <input
                            type="text"
                            name="tenant_phone"
                            class="w-full rounded-md border border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                        >
                    </div>
                </div>

                <!-- Property -->
                <div class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Building name</label>
                        <input
                            type="text"
                            name="building_name"
                            class="w-full rounded-md border border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                        >
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Location</label>
                        <input
                            type="text"
                            name="property_location"
                            class="w-full rounded-md border border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                            placeholder="Area / community"
                        >
                    </div>
                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Property size (S.M)</label>
                            <input
                                type="text"
                                name="property_size"
                                class="w-full rounded-md border border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                            >
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Property type</label>
                            <input
                                type="text"
                                name="property_type"
                                class="w-full rounded-md border border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                            >
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Property No.</label>
                            <input
                                type="text"
                                name="property_number"
                                class="w-full rounded-md border border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                            >
                        </div>
                    </div>
                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Premises No. (DEWA)</label>
                            <input
                                type="text"
                                name="dewa_number"
                                class="w-full rounded-md border border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                            >
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Plot No.</label>
                            <input
                                type="text"
                                name="plot_number"
                                class="w-full rounded-md border border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                            >
                        </div>
                    </div>
                </div>

                <!-- Contract / Rent -->
                <div class="space-y-4">
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Contract period from</label>
                            <input
                                type="date"
                                name="contract_from"
                                data-preview="spaced"
                                class="w-full rounded-md border border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                placeholder="DD-MM-YYYY"
                            >
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Contract period to</label>
                            <input
                                type="date"
                                name="contract_to"
                                data-preview="spaced"
                                class="w-full rounded-md border border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                placeholder="DD-MM-YYY"
                            >
                        </div>
                    </div>
                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Annual rent (AED)</label>
                            <input
                                type="number"
                                step="0.01"
                                name="annual_rent"
                                class="w-full rounded-md border border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                            >
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Contract value</label>
                            <input
                                type="number"
                                step="0.01"
                                name="contract_value"
                                class="w-full rounded-md border border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                            >
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Security deposit</label>
                            <input
                                type="number"
                                step="0.01"
                                name="security_deposit"
                                class="w-full rounded-md border border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                            >
                        </div>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Annual rent (in words)</label>
                        <input
                            type="text"
                            name="annual_rent_words"
                            class="w-full rounded-md border border-gray-300 bg-gray-50 text-gray-700 shadow-sm"
                            readonly
                        >
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Mode of payment</label>
                        <input
                            type="text"
                            name="mode_payment"
                            class="w-full rounded-md border border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                            placeholder="e.g. 4 cheques"
                        >
                    </div>
                </div>

                <!-- Hidden field that will be populated by JS for page 2 terms -->
                <textarea
                    name="additional_terms"
                    id="additionalTermsField"
                    class="hidden"
                ></textarea>
            </form>
        </div>

        <!-- Right: live preview + page toggle + additional terms -->
        <div class="bg-white border border-border rounded-card shadow-card p-6 text-sm text-gray-700 space-y-4 overflow-x-auto">
            <div class="flex items-center justify-between mb-4">
                <h2 class="text-lg font-semibold">Preview</h2>
                <div class="flex items-center gap-2">
                    <button
                        type="button"
                        id="previewPagePrev"
                        class="h-8 w-8 flex items-center justify-center rounded-full border border-gray-300 text-gray-500 hover:bg-gray-100 disabled:opacity-40 disabled:cursor-not-allowed"
                        disabled
                    >
                        ‹
                    </button>
                    <span id="previewPageLabel" class="text-xs text-gray-500">
                        Page 1 of 2
                    </span>
                    <button
                        type="button"
                        id="previewPageNext"
                        class="h-8 w-8 flex items-center justify-center rounded-full border border-gray-300 text-gray-500 hover:bg-gray-100"
                    >
                        ›
                    </button>
                </div>
            </div>

            <!-- PAGE 1 PREVIEW -->
            <div id="previewPage1Wrapper">
                <div
                    id="contract-preview"
                    class="relative inline-block"
                    style="
                        background: url('/assets/img/tenancy-contract-template_Page1.png') no-repeat top left;
                        background-size: 100% 100%;
                        width: 800px;
                        height: 1130px;
                        padding: 0;
                        font-family: Helvetica, Arial, sans-serif;
                        font-size: 14px;
                        line-height: 1;
                        white-space: nowrap;
                    "
                >
                    <!-- Usage dots -->
                    <span data-usage-dot="residential" class="absolute" style="top: 105px; left: 568px; font-size: 50px; opacity: 0;">•</span>
                    <span data-usage-dot="commercial"  class="absolute" style="top: 105px; left: 417px; font-size: 50px; opacity: 0;">•</span>
                    <span data-usage-dot="industrial"  class="absolute" style="top: 105px; left: 263px; font-size: 50px; opacity: 0;">•</span>

                    <span data-bind="today_date"        class="absolute" style="top: 60px;  left: 61px;"></span>
                    <span data-bind="no_name"           class="absolute" style="top: 80px;  left: 61px;"></span>

                    <span data-bind="owner_name"        class="absolute" style="top: 145px; left: 93px;"></span>
                    <span data-bind="landlord_name"     class="absolute" style="top: 175px; left: 105px;"></span>
                    <span data-bind="tenant_name"       class="absolute" style="top: 202px; left: 91px;"></span>
                    <span data-bind="tenant_email"      class="absolute" style="top: 229px; left: 91px;"></span>
                    <span data-bind="tenant_phone"      class="absolute" style="top: 259px; left: 95px;"></span>

                    <span data-bind="landlord_phone"    class="absolute" style="top: 259px; left: 487px;"></span>
                    <span data-bind="landlord_email"    class="absolute" style="top: 229px; left: 487px;"></span>

                    <span data-bind="building_name"     class="absolute" style="top: 286px; left: 100px;"></span>
                    <span data-bind="property_location" class="absolute" style="top: 288px; left: 452px;"></span>

                    <span data-bind="property_size"     class="absolute" style="top: 317px; left: 115px;"></span>
                    <span data-bind="property_type"     class="absolute" style="top: 317px; left: 391px;"></span>
                    <span data-bind="property_number"   class="absolute" style="top: 316px; left: 645px;"></span>

                    <span data-bind="dewa_number"       class="absolute" style="top: 345px; left: 127px;"></span>
                    <span data-bind="plot_number"       class="absolute" style="top: 345px; left: 447px;"></span>

                    <span data-bind="contract_to"       class="absolute" style="top: 372px; left: 135px;"></span>
                    <span data-bind="contract_from"     class="absolute" style="top: 372px; left: 440px;"></span>

                    <span data-bind="annual_rent_words" class="absolute" style="top: 397px; left: 87px;"></span>
                    <span data-bind="contract_value"    class="absolute" style="top: 426px; left: 99px;"></span>
                    <span data-bind="security_deposit"  class="absolute" style="top: 454px; left: 148px;"></span>
                    <span data-bind="mode_payment"      class="absolute" style="top: 454px; left: 502px;"></span>
                </div>
            </div>

            <!-- PAGE 2 PREVIEW + ADDITIONAL TERMS (initially hidden) -->
            <div id="previewPage2Wrapper" class="hidden space-y-4">
                <div
                    id="contract-preview-page2"
                    class="relative inline-block"
                    style="
                        background: url('/assets/img/tenancy-contract-template_Page2.png') no-repeat top left;
                        background-size: 100% 100%;
                        width: 800px;
                        height: 1130px;
                        padding: 0;
                        font-family: Helvetica, Arial, sans-serif;
                        font-size: 14px;
                        line-height: 1;
                        white-space: nowrap;
                    "
                >
                    <!-- Additional terms lines preview (8 lines example; tweak coordinates as needed) -->
                    <span data-additional-line="0" class="absolute" style="top: 380px; left: 60px;"></span>
                    <span data-additional-line="1" class="absolute" style="top: 404px; left: 60px;"></span>
                    <span data-additional-line="2" class="absolute" style="top: 428px; left: 60px;"></span>
                    <span data-additional-line="3" class="absolute" style="top: 452px; left: 60px;"></span>
                    <span data-additional-line="4" class="absolute" style="top: 476px; left: 60px;"></span>
                    <span data-additional-line="5" class="absolute" style="top: 500px; left: 60px;"></span>
                    <span data-additional-line="6" class="absolute" style="top: 524px; left: 60px;"></span>
                    <span data-additional-line="7" class="absolute" style="top: 548px; left: 60px;"></span>
                </div>

                <div id="additionalTermsUI" class="border-t border-gray-200 pt-4">
                    <div class="flex items-center justify-between mb-2">
                        <h3 class="text-sm font-semibold text-gray-800">Additional terms</h3>
                        <button
                            type="button"
                            id="addAdditionalLineBtn"
                            class="inline-flex items-center justify-center rounded-md border border-gray-300 px-2 py-1 text-xs font-medium text-gray-700 hover:bg-gray-50"
                        >
                            + Add line
                        </button>
                    </div>
                    <p class="text-xs text-gray-500 mb-2">
                        These lines will appear on page 2 of the tenancy contract.
                    </p>

                    <div id="additionalTermsLines" class="space-y-2">
                        <!-- Line template: JS will clone & number them; this is line 1 -->
                        <div class="flex gap-2 items-start" data-additional-line-row>
                            <span class="mt-2 text-xs text-gray-500 line-number">1.</span>
                            <input
                                type="text"
                                class="flex-1 rounded-md border border-gray-300 px-2 py-1 text-sm focus:border-indigo-500 focus:ring-indigo-500"
                                placeholder="Additional term line 1"
                                data-additional-input="0"
                                form="tenancyContractForm"
                            >
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
