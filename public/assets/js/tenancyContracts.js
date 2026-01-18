// Expose an initializer that `app.js` will call when the `tenancy-contracts` page is active
window.initTenancyContracts = function () {
    var bindings = document.querySelectorAll('[data-bind]');
    var usageDots = document.querySelectorAll('[data-usage-dot]');
    var usageRadios = document.querySelectorAll('input[name="property_usage"]');

    // Simple number-to-words converter (integer part only, English)
    function numberToWords(num) {
        num = parseInt(num, 10);
        if (!isFinite(num) || num < 0) return '';
        if (num === 0) return 'zero';

        var below20 = ['', 'one', 'two', 'three', 'four', 'five', 'six', 'seven', 'eight', 'nine', 'ten',
            'eleven', 'twelve', 'thirteen', 'fourteen', 'fifteen', 'sixteen', 'seventeen', 'eighteen', 'nineteen'];
        var tens = ['', '', 'twenty', 'thirty', 'forty', 'fifty', 'sixty', 'seventy', 'eighty', 'ninety'];

        function helper(n) {
            var words = '';
            if (n >= 1000000) {
                words += helper(Math.floor(n / 1000000)) + ' million ';
                n %= 1000000;
            }
            if (n >= 1000) {
                words += helper(Math.floor(n / 1000)) + ' thousand ';
                n %= 1000;
            }
            if (n >= 100) {
                words += below20[Math.floor(n / 100)] + ' hundred ';
                n %= 100;
            }
            if (n >= 20) {
                words += tens[Math.floor(n / 10)] + ' ';
                n %= 10;
            }
            if (n > 0) {
                words += below20[n] + ' ';
            }
            return words.trim();
        }

        return helper(num);
    }

    // Keep PDF as source of truth for coordinates:
    // preview overlay spans mirror TenancyContractController::downloadPdf SetXY positions,
    // scaled to an 800x1130px container (~A4 @ 96dpi).

    function formatSpacedPairs(value, gapSize) {
        var digits = String(value || '').replace(/\D/g, '');
        if (!digits) return '';
        digits = digits.slice(0, 8);
        var size = typeof gapSize === 'number' ? gapSize : 7;
        var gap = new Array(size + 1).join('\u00A0');
        if (digits.length <= 2) {
            return digits;
        }
        if (digits.length <= 4) {
            return digits.slice(0, 2) + gap + digits.slice(2);
        }
        return digits.slice(0, 2) + gap + digits.slice(2, 4) + gap + digits.slice(4);
    }

    bindings.forEach(function (el) {
        var key = el.getAttribute('data-bind');
        var input = document.querySelector('[name="' + key + '"]');
        if (!input) return;

        // Capture the original placeholder text once
        var placeholder = el.getAttribute('data-placeholder');
        if (!placeholder) {
            placeholder = el.textContent || '';
            el.setAttribute('data-placeholder', placeholder);
        }

        var update = function () {
            var val = input.value || '';
            var previewMode = input.getAttribute('data-preview') || '';
            var wantsSpaced = previewMode === 'spaced' && (key === 'contract_from' || key === 'contract_to' || key === 'today_date');

            // Format money fields with AED prefix (annual_rent handled separately)
            if ((key === 'contract_value' || key === 'security_deposit') && val) {
                el.textContent = 'AED ' + val;
                return;
            }

            // Special-case: show slashed date (dd/mm/yyyy) for contract period in the preview
            // Example: input value `2026-10-23` -> preview shows `23/10/2026`.
            if ((key === 'contract_from' || key === 'contract_to') && val) {
                var ms = val.match(/^(\d{4})-(\d{2})-(\d{2})$/);
                if (ms) {
                    el.textContent = ms[3] + '/' + ms[2] + '/' + ms[1];
                    return;
                }
                var ms2 = val.match(/^(\d{2})\/(\d{2})\/(\d{4})$/);
                if (ms2) {
                    el.textContent = val; // already slashed
                    return;
                }
            }

            // If an input explicitly requests the spaced/boxed preview, show grouped digits
            if (wantsSpaced) {
                var formatted = val;
                var m = formatted.match(/^(\d{4})-(\d{2})-(\d{2})$/);
                if (m) formatted = m[3] + '-' + m[2] + '-' + m[1];
                el.textContent = formatSpacedPairs(formatted, 7) || placeholder;
                return;
            }

            // Format date fields as dd-mm-yyyy when entered as yyyy-mm-dd
            if ((key === 'today_date' || key === 'contract_from' || key === 'contract_to') && val) {
                var m = val.match(/^(\d{4})-(\d{2})-(\d{2})$/);
                if (m) {
                    el.textContent = m[3] + '-' + m[2] + '-' + m[1];
                    return;
                }
            }

            el.textContent = val || placeholder;
        };

        input.addEventListener('input', update);
        update();
    });

    // Keep annual rent numeric + words in sync with annual_rent
    var annualRentInput = document.querySelector('[name="annual_rent"]');
    var annualRentWordsInput = document.querySelector('[name="annual_rent_words"]');
    var annualRentWordsSpans = document.querySelectorAll('[data-bind="annual_rent_words"]');

    function toTitleCase(str) {
        return str.replace(/\b\w/g, function (c) { return c.toUpperCase(); });
    }

    function updateAnnualRentCombined() {
        if (!annualRentInput) return;
        var baseVal = annualRentInput.value || '';
        var numeric = baseVal.split('.')[0];
        var words = numberToWords(numeric);

        var numericText = baseVal ? 'AED ' + baseVal + ' /--' : '';
        var wordsText = words ? '(' + toTitleCase(words) + ' Dirham Only)' : '';

        var combined = [numericText, wordsText].filter(Boolean).join(' ');

        annualRentWordsSpans.forEach(function (span) {
            span.textContent = combined;
        });

        if (annualRentWordsInput) {
            annualRentWordsInput.value = combined;
        }
    }

    if (annualRentInput) {
        annualRentInput.addEventListener('input', updateAnnualRentCombined);
        updateAnnualRentCombined();
    }

    function updateUsageDots() {
        var selected = document.querySelector('input[name="property_usage"]:checked');
        var value = selected ? selected.value : null;

        usageDots.forEach(function (dot) {
            var v = dot.getAttribute('data-usage-dot');
            dot.style.opacity = (value === v) ? '1' : '0';
        });
    }

    usageRadios.forEach(function (radio) {
        radio.addEventListener('change', updateUsageDots);
    });
    updateUsageDots();

    // --------------------------
    // Additional terms (page 2)
    // --------------------------
    var previewPagePrev = document.getElementById('previewPagePrev');
    var previewPageNext = document.getElementById('previewPageNext');
    var previewPageLabel = document.getElementById('previewPageLabel');
    var previewPage1Wrapper = document.getElementById('previewPage1Wrapper');
    var previewPage2Wrapper = document.getElementById('previewPage2Wrapper');

    var addBtn = document.getElementById('addAdditionalLineBtn');
    var linesContainer = document.getElementById('additionalTermsLines');
    var additionalTermsField = document.getElementById('additionalTermsField');

    var maxLines = 8;
    var pageIndex = 1;

    function updatePreviewPageUI() {
        if (!previewPage1Wrapper || !previewPage2Wrapper || !previewPageLabel) return;
        previewPage1Wrapper.classList.toggle('hidden', pageIndex !== 1);
        previewPage2Wrapper.classList.toggle('hidden', pageIndex !== 2);
        if (previewPagePrev) previewPagePrev.disabled = pageIndex === 1;
        if (previewPageNext) previewPageNext.disabled = pageIndex === 2;
        previewPageLabel.textContent = 'Page ' + pageIndex + ' of 2';
    }

    function renumberLines() {
        var rows = Array.from(linesContainer.querySelectorAll('[data-additional-line-row]'));
        rows.forEach(function (row, idx) {
            var numEl = row.querySelector('.line-number');
            if (numEl) numEl.textContent = (idx + 1) + '.';
            var input = row.querySelector('input[data-additional-input]');
            if (input) input.setAttribute('data-additional-input', idx);
            // Update preview span too
            var previewSpan = document.querySelector('[data-additional-line="' + idx + '"]');
            if (previewSpan) previewSpan.textContent = input ? input.value : '';
        });
        if (addBtn) addBtn.disabled = rows.length >= maxLines;
    }

    function createLine(value) {
        var idx = linesContainer.querySelectorAll('[data-additional-line-row]').length;
        if (idx >= maxLines) return null;
        var wrapper = document.createElement('div');
        wrapper.className = 'flex gap-2 items-start';
        wrapper.setAttribute('data-additional-line-row', '');

        var span = document.createElement('span');
        span.className = 'mt-2 text-xs text-gray-500 line-number';
        span.textContent = (idx + 1) + '.';

        var input = document.createElement('input');
        input.type = 'text';
        input.className = 'flex-1 rounded-md border border-gray-300 px-2 py-1 text-sm focus:border-indigo-500 focus:ring-indigo-500';
        input.placeholder = 'Additional term line ' + (idx + 1);
        input.setAttribute('data-additional-input', idx);
        input.setAttribute('form', 'tenancyContractForm');
        input.addEventListener('input', function () {
            var i = parseInt(input.getAttribute('data-additional-input'), 10);
            var previewSpan = document.querySelector('[data-additional-line="' + i + '"]');
            if (previewSpan) previewSpan.textContent = input.value;
        });

        var removeBtn = document.createElement('button');
        removeBtn.type = 'button';
        removeBtn.className = 'ml-2 text-xs text-red-600 hover:underline';
        removeBtn.textContent = 'Remove';
        removeBtn.addEventListener('click', function () {
            wrapper.remove();
            renumberLines();
        });

        wrapper.appendChild(span);
        wrapper.appendChild(input);
        wrapper.appendChild(removeBtn);

        linesContainer.appendChild(wrapper);
        renumberLines();

        return input;
    }

    // Initialize existing input rows (there is one in the template)
    (function initExistingLines() {
        var rows = Array.from(linesContainer.querySelectorAll('[data-additional-line-row]'));
        if (rows.length === 0) {
            createLine('');
            return;
        }

        // If hidden textarea has initial value (e.g., server-side prefill), populate lines from it
        var initialText = additionalTermsField ? additionalTermsField.value || '' : '';
        var initialLines = initialText ? initialText.split(/\r\n|\r|\n/) : [];

        // Ensure we have enough rows for initial lines
        for (var i = 0; i < initialLines.length - rows.length; i++) {
            createLine('');
            rows = Array.from(linesContainer.querySelectorAll('[data-additional-line-row]'));
        }

        rows.forEach(function (row, idx) {
            var input = row.querySelector('input[data-additional-input]');
            if (!input) {
                input = document.createElement('input');
                input.type = 'text';
                input.className = 'flex-1 rounded-md border border-gray-300 px-2 py-1 text-sm focus:border-indigo-500 focus:ring-indigo-500';
                input.setAttribute('form', 'tenancyContractForm');
                row.appendChild(input);
            }

            input.setAttribute('data-additional-input', idx);
            input.placeholder = 'Additional term line ' + (idx + 1);

            // If we have matching initial text, set it
            if (initialLines[idx]) {
                input.value = initialLines[idx].trim();
                var previewSpanInit = document.querySelector('[data-additional-line="' + idx + '"]');
                if (previewSpanInit) previewSpanInit.textContent = input.value;
            }

            input.addEventListener('input', function () {
                var i = parseInt(input.getAttribute('data-additional-input'), 10);
                var previewSpan = document.querySelector('[data-additional-line="' + i + '"]');
                if (previewSpan) previewSpan.textContent = input.value;
            });
        });

        renumberLines();
    })();

    if (addBtn) {
        addBtn.addEventListener('click', function () {
            createLine('');
        });
    }

    if (previewPagePrev) {
        previewPagePrev.addEventListener('click', function () {
            pageIndex = Math.max(1, pageIndex - 1);
            updatePreviewPageUI();
        });
    }

    if (previewPageNext) {
        previewPageNext.addEventListener('click', function () {
            pageIndex = Math.min(2, pageIndex + 1);
            updatePreviewPageUI();
            // focus first additional input when we show page 2
            if (pageIndex === 2 && linesContainer) {
                var first = linesContainer.querySelector('input[data-additional-input]');
                if (first) first.focus();
            }
        });
    }

    updatePreviewPageUI();

    // Populate hidden textarea on form submit
    var form = document.getElementById('tenancyContractForm');
    if (form) {
        // Ensure hidden csrf_token input exists so non-JS form submits also send it
        (function ensureCsrfInput() {
            try {
                var m = document.cookie.match('(?:^|; )' + 'csrf_token'.replace(/[$()*+.?[\\\]^{|}-]/g, '\\$&') + '=([^;]*)');
                var csrfVal = m ? decodeURIComponent(m[1]) : null;
                if (csrfVal) {
                    var existing = form.querySelector('input[name="csrf_token"]');
                    if (!existing) {
                        var inp = document.createElement('input');
                        inp.type = 'hidden';
                        inp.name = 'csrf_token';
                        inp.value = csrfVal;
                        form.appendChild(inp);
                    }
                }
            } catch (e) {
                // ignore
            }
        })();

        form.addEventListener('submit', function (ev) {
            var rows = Array.from(linesContainer.querySelectorAll('input[data-additional-input]'));
            var lines = rows.map(function (i) { return i.value.trim(); }).filter(Boolean);
            if (additionalTermsField) additionalTermsField.value = lines.join('\n');
        });
    }

    // Intercept form submit to download PDF via fetch so we can attach CSRF header and show progress
    if (form) {
        form.addEventListener('submit', async function (ev) {
            ev.preventDefault();

            // Ensure additional terms field is up-to-date
            var rows = Array.from(linesContainer.querySelectorAll('input[data-additional-input]'));
            var lines = rows.map(function (i) { return i.value.trim(); }).filter(Boolean);
            if (additionalTermsField) additionalTermsField.value = lines.join('\n');

            // Find the submit button (it may be outside the form with form="tenancyContractForm")
            var submitBtn = document.querySelector('button[form="' + form.id + '"]') || form.querySelector('[type="submit"]');
            if (submitBtn) {
                submitBtn.disabled = true;
            }
            // show global spinner
            try { ui.showSpinner('Preparing PDF...'); } catch (e) { /* ignore if ui unavailable */ }

            try {
                var fd = new FormData(form);
                // Include the CSRF token header if available in the cookie
                var headers = {};
                var token = localStorage.getItem('crm_token');
                if (token) headers['Authorization'] = 'Bearer ' + token;
                var csrf = (document.cookie.match('(?:^|; )' + 'csrf_token'.replace(/[$()*+.?[\\\]^{|}-]/g, '\\$&') + '=([^;]*)') || [])[1];
                if (csrf) headers['X-CSRF-Token'] = decodeURIComponent(csrf);

                var res = await fetch(form.action, {
                    method: 'POST',
                    headers: headers,
                    body: fd,
                    credentials: 'include',
                });

                if (!res.ok) {
                    var text = await res.text();
                    throw new Error('Failed to create PDF: ' + (text || res.statusText));
                }

                var blob = await res.blob();
                var url = URL.createObjectURL(blob);
                window.open(url, '_blank');
            } catch (err) {
                console.error(err);
                try { ui.showToast(err.message || 'PDF generation failed', 'error'); } catch (e) { alert(err.message || 'PDF generation failed'); }
            } finally {
                try { ui.hideSpinner(); } catch (e) { }
                if (submitBtn) {
                    submitBtn.disabled = false;
                }
            }
        });
    }
};


