<!-- View template for the NOC leasing form. -->

<section data-page="noc-leasing" class="w-full px-4">
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 lg:gap-16 items-start">
        <!-- Left: input form (NOC / Leasing Form) -->
        <div class="bg-white border border-border rounded-card shadow-card p-6 space-y-4 tenancy-form max-h-[1130px] overflow-y-auto">
            <div class="flex items-center justify-between gap-4">
                <h1 class="text-xl font-semibold">NOC / Leasing Form</h1>
                <button type="submit" form="nocLeasingForm" class="inline-flex items-center justify-center rounded-md bg-indigo-600 px-4 py-2 text-sm font-medium text-white shadow hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2">
                    Download NOC PDF
                </button>
            </div>
            <form id="nocLeasingForm" action="/api.php/noc-leasing/pdf" method="POST" target="_blank" class="space-y-6">
                <!-- Header -->
                <!-- Landlord / listing details (as per NOC layout) -->
                <div class="space-y-4">
                    <h2 class="text-sm font-semibold text-gray-800">Landlord / Listing</h2>
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Listing consultant</label>
                            <input type="text" name="listing_consultant" class="w-full rounded-md border border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Property reference number</label>
                            <input type="text" name="property_reference_nuber" class="w-full rounded-md border border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Landlord's name</label>
                            <input type="text" name="landlords_name" class="w-full rounded-md border border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Passport number</label>
                            <input type="text" name="passport_number" class="w-full rounded-md border border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Passport expiry date</label>
                            <input type="date" name="expiry_date" class="w-full rounded-md border border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                        </div>
                    </div>
                </div>

                <!-- Property classification (type / furnishing / occupancy) -->
                <div class="space-y-3">
                    <h2 class="text-sm font-semibold text-gray-800">Property details</h2>

                    <!-- Property type -->
                    <div>
                        <span class="block text-sm font-medium text-gray-700 mb-1">Property type</span>
                        <div class="flex flex-wrap gap-4 text-sm">
                            <label class="inline-flex items-center gap-2">
                                <input type="radio" name="property_type_choice" value="villa" class="rounded border-gray-300 text-indigo-600">
                                <span>Villa</span>
                            </label>
                            <label class="inline-flex items-center gap-2">
                                <input type="radio" name="property_type_choice" value="apartment" class="rounded border-gray-300 text-indigo-600">
                                <span>Apartment</span>
                            </label>
                            <label class="inline-flex items-center gap-2">
                                <input type="radio" name="property_type_choice" value="office" class="rounded border-gray-300 text-indigo-600">
                                <span>Office</span>
                            </label>
                            <label class="inline-flex items-center gap-2">
                                <input type="radio" name="property_type_choice" value="retail" class="rounded border-gray-300 text-indigo-600">
                                <span>Retail</span>
                            </label>
                            <label class="inline-flex items-center gap-2">
                                <input type="radio" name="property_type_choice" value="warehouse" class="rounded border-gray-300 text-indigo-600">
                                <span>Warehouse</span>
                            </label>
                            <label class="inline-flex items-center gap-2">
                                <input type="radio" name="property_type_choice" value="land" class="rounded border-gray-300 text-indigo-600">
                                <span>Land</span>
                            </label>
                        </div>
                    </div>

                    <!-- Furnishing -->
                    <div>
                        <span class="block text-sm font-medium text-gray-700 mb-1">Furnishing</span>
                        <div class="flex flex-wrap gap-4 text-sm">
                            <label class="inline-flex items-center gap-2">
                                <input type="radio" name="furnishing_status" value="furnished" class="rounded border-gray-300 text-indigo-600">
                                <span>Furnished</span>
                            </label>
                            <label class="inline-flex items-center gap-2">
                                <input type="radio" name="furnishing_status" value="unfurnished" class="rounded border-gray-300 text-indigo-600">
                                <span>Unfurnished</span>
                            </label>
                        </div>
                    </div>

                    <!-- Occupancy -->
                    <div>
                        <span class="block text-sm font-medium text-gray-700 mb-1">Occupancy</span>
                        <div class="flex flex-wrap gap-4 text-sm">
                            <label class="inline-flex items-center gap-2">
                                <input type="radio" name="occupancy_status" value="vacant" class="rounded border-gray-300 text-indigo-600">
                                <span>Vacant</span>
                            </label>
                            <label class="inline-flex items-center gap-2">
                                <input type="radio" name="occupancy_status" value="tenanted" class="rounded border-gray-300 text-indigo-600">
                                <span>Tenanted</span>
                            </label>
                        </div>
                    </div>

                    <!-- Vacating date -->
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Vacating date</label>
                            <input type="date" name="vacating_date" data-preview="spaced" class="w-full rounded-md border border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" placeholder="DD-MM-YYYY">
                        </div>
                    </div>
                </div>

                <!-- Property address / size -->
                <div class="space-y-4">
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Building / Property name</label>
                            <input type="text" name="building_name" class="w-full rounded-md border border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Unit</label>
                            <input type="text" name="unit" class="w-full rounded-md border border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                        </div>
                    </div>
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Street name</label>
                            <input type="text" name="street_name" class="w-full rounded-md border border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Community</label>
                            <input type="text" name="community" class="w-full rounded-md border border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                        </div>
                    </div>
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">BUA (sqft)</label>
                            <input type="text" name="BUA_sqft" class="w-full rounded-md border border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Plot (sqft)</label>
                            <input type="text" name="plot_sqft" class="w-full rounded-md border border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                        </div>
                    </div>
                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Bedrooms</label>
                            <input type="text" name="bedrooms" class="w-full rounded-md border border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Bathrooms</label>
                            <input type="text" name="bathrooms" class="w-full rounded-md border border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Parking</label>
                            <input type="text" name="parking" class="w-full rounded-md border border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                        </div>
                    </div>
                    <div class="grid grid-cols-1 sm:grid-cols-1 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Amount (AED)</label>
                            <input type="text" name="ammount" class="w-full rounded-md border border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                        </div>
                    </div>
                </div>

                <!-- Listing terms (exclusive / duration / until) -->
                <div class="space-y-4">
                    <h2 class="text-sm font-semibold text-gray-800">Listing terms</h2>
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <span class="block text-sm font-medium text-gray-700 mb-1">Listing type</span>
                            <div class="flex flex-wrap gap-4 text-sm">
                                <label class="inline-flex items-center gap-2">
                                    <input type="radio" name="listing_type" value="exclusive" class="rounded border-gray-300 text-indigo-600">
                                    <span>Exclusive</span>
                                </label>
                                <label class="inline-flex items-center gap-2">
                                    <input type="radio" name="listing_type" value="non_exclusive" class="rounded border-gray-300 text-indigo-600">
                                    <span>Non-exclusive</span>
                                </label>
                            </div>
                        </div>
                        <div>
                            <span class="block text-sm font-medium text-gray-700 mb-1">Listing duration</span>
                            <div class="flex flex-wrap gap-4 text-sm">
                                <label class="inline-flex items-center gap-2">
                                    <input type="radio" name="listing_duration" value="1_month" class="rounded border-gray-300 text-indigo-600">
                                    <span>1 month</span>
                                </label>
                                <label class="inline-flex items-center gap-2">
                                    <input type="radio" name="listing_duration" value="2_month" class="rounded border-gray-300 text-indigo-600">
                                    <span>2 months</span>
                                </label>
                                <label class="inline-flex items-center gap-2">
                                    <input type="radio" name="listing_duration" value="3_month" class="rounded border-gray-300 text-indigo-600">
                                    <span>3 months</span>
                                </label>
                            </div>
                        </div>
                    </div>
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Until</label>
                            <input type="date" name="Until" data-preview="spaced" class="w-full rounded-md border border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" placeholder="DD-MM-YYYY">
                        </div>
                    </div>

                    <!-- Annual rent in words (reuse existing tenancy pattern) -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Annual rent (in words)</label>
                        <input type="text" name="annual_rent_words" class="w-full rounded-md border border-gray-300 bg-gray-50 text-gray-700 shadow-sm" readonly>
                    </div>
                </div>

                <!-- Landlord sign-off (copied from NOC) -->
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 pt-2 border-t border-gray-200 mt-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Landlord name (signature)</label>
                        <input type="text" name="landlord_name" class="w-full rounded-md border border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Date</label>
                        <input type="date" name="date" data-preview="spaced" class="w-full rounded-md border border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" placeholder="DD-MM-YYYY">
                    </div>
                </div>
            </form>
        </div>

        <!-- Right: live preview (uses NOC template background) -->
        <div class="bg-white border border-border rounded-card shadow-card p-6 text-sm text-gray-700 space-y-4 overflow-x-auto">
            <h2 class="text-lg font-semibold mb-4">Preview</h2>
            <!-- Preview coordinates are approximate; PDF SetXY remains source of truth. -->
            <div
                id="noc-preview"
                class="relative inline-block"
                style="
                    background: url('/assets/img/noc-template_Page1.png') no-repeat top left;
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
                <!-- Radio / checkbox dots -->
                <!-- Property type -->
                <span data-noc-dot="property_type_choice:villa" class="absolute" style="top: 372px; left: 105.4px; font-size: 50px; opacity: 0;">•</span>
                <span data-noc-dot="property_type_choice:apartment" class="absolute" style="top: 369px; left: 292.4px; font-size: 50px; opacity: 0;">•</span>
                <span data-noc-dot="property_type_choice:office" class="absolute" style="top: 407.5px; left: 106px; font-size: 50px; opacity: 0;">•</span>
                <span data-noc-dot="property_type_choice:retail" class="absolute" style="top: 405.2px; left: 296.6px; font-size: 50px; opacity: 0;">•</span>
                <span data-noc-dot="property_type_choice:warehouse" class="absolute" style="top: 408px; left: 503.4px; font-size: 50px; opacity: 0;">•</span>
                <span data-noc-dot="property_type_choice:land" class="absolute" style="top: 408.6px; left: 639.0px; font-size: 50px; opacity: 0;">•</span>

                <!-- Furnishing -->
                <span data-noc-dot="furnishing_status:furnished" class="absolute" style="top: 369.5px; left: 502.3px; font-size: 50px; opacity: 0;">•</span>
                <span data-noc-dot="furnishing_status:unfurnished" class="absolute" style="top: 373.2px; left: 634.7px; font-size: 50px; opacity: 0;">•</span>

                <!-- Occupancy -->
                <span data-noc-dot="occupancy_status:vacant" class="absolute" style="top: 439.5px; left: 105.5px; font-size: 50px; opacity: 0;">•</span>
                <span data-noc-dot="occupancy_status:tenanted" class="absolute" style="top: 440px; left: 291.6px; font-size: 50px; opacity: 0;">•</span>

                <!-- Listing type -->
                <span data-noc-dot="listing_type:exclusive" class="absolute" style="top: 759px; left: 59px; font-size: 50px; opacity: 0;">•</span>
                <span data-noc-dot="listing_type:non_exclusive" class="absolute" style="top: 759.3px; left: 167px; font-size: 50px; opacity: 0;">•</span>

                <!-- Listing duration -->
                <span data-noc-dot="listing_duration:1_month" class="absolute" style="top: 782.2px; left: 60.8px; font-size: 50px; opacity: 0;">•</span>
                <span data-noc-dot="listing_duration:2_month" class="absolute" style="top: 782.2px; left: 168.6px; font-size: 50px; opacity: 0;">•</span>
                <span data-noc-dot="listing_duration:3_month" class="absolute" style="top: 782.6px; left: 276.8px; font-size: 50px; opacity: 0;">•</span>

                <!-- Basic bindings so typing on the left updates here -->
                

                <span data-bind="listing_consultant" class="absolute" style="top: 146px; left: 142px;"></span>
                <span data-bind="property_reference_nuber" class="absolute" style="top: 146px; left: 520px;"></span>

                <span data-bind="landlords_name" class="absolute" style="top: 208.8px; left: 139px;"></span>
                <span data-bind="passport_number" class="absolute" style="top: 241.1px; left: 139px;"></span>
                <span data-bind="expiry_date" class="absolute" style="top: 271.5px; left: 161.9px;"></span>

                <span data-bind="vacating_date" class="absolute" style="top: 458.6px; left: 451.4px;"></span>

                <span data-bind="building_name" class="absolute" style="top: 489px; left: 123.8px;"></span>
                <span data-bind="unit" class="absolute" style="top: 491.3px; left: 411.5px;"></span>

                <span data-bind="street_name" class="absolute" style="top: 523.2px; left: 112.5px;"></span>
                <span data-bind="community" class="absolute" style="top: 523.2px; left: 440.1px;"></span>

                <span data-bind="BUA_sqft" class="absolute" style="top: 553.7px; left: 102.9px;"></span>
                <span data-bind="plot_sqft" class="absolute" style="top: 553.7px; left: 432.5px;"></span>

                <span data-bind="bedrooms" class="absolute" style="top: 586.0px; left: 102.9px;"></span>
                <span data-bind="bathrooms" class="absolute" style="top: 586.0px; left: 270.6px;"></span>
                <span data-bind="parking" class="absolute" style="top: 586.0px; left: 419.1px;"></span>
                <span data-bind="annual_rent_words" class="absolute" style="top: 619.5px; left: 95.3px;"></span>
                <span data-bind="Until" class="absolute" style="top: 802.9px; left: 438.2px;"></span>

                <span data-bind="landlord_name" class="absolute" style="top: 991.2px; left: 80.1px;"></span>
                <span data-bind="date" class="absolute" style="top: 991.2px; left: 636.3px;"></span>
            </div>
        </div>
    </div>
</section>
