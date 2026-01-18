// NOC leasing form logic and preview mapping.

document.addEventListener('DOMContentLoaded', function () {
    var bindings = document.querySelectorAll('[data-bind]');

    // Simple number-to-words converter (integer part only, English)
    function numberToWords(num) {
        num = parseInt(num, 10);
        if (!isFinite(num) || num < 0) return '';
        if (num === 0) return 'zero';

        var below20 = ['','one','two','three','four','five','six','seven','eight','nine','ten',
            'eleven','twelve','thirteen','fourteen','fifteen','sixteen','seventeen','eighteen','nineteen'];
        var tens = ['','','twenty','thirty','forty','fifty','sixty','seventy','eighty','ninety'];

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

    function formatSpacedPairs(value, gapSize) {
        var digits = String(value || '').replace(/\D/g, '');
        if (!digits) return '';
        digits = digits.slice(0, 8);
        var size = typeof gapSize === 'number' ? gapSize : 6;
        var gap = new Array(size + 1).join('\u00A0');
        if (digits.length <= 2) {
            return digits;
        }
        if (digits.length <= 4) {
            return digits.slice(0, 2) + gap + digits.slice(2);
        }
        // Keep the last 4 digits together (no gaps within them).
        return digits.slice(0, 2) + gap + digits.slice(2, 4) + gap + digits.slice(4);
    }

    // Keep NOC preview overlay in sync with the left-side form
    bindings.forEach(function (el) {
        var key = el.getAttribute('data-bind');
        var input = document.querySelector('[name="' + key + '"]');
        if (!input) return;

        var placeholder = el.getAttribute('data-placeholder') || '';
        if (!placeholder) {
            placeholder = el.textContent || '';
            el.setAttribute('data-placeholder', placeholder);
        }

        var update = function () {
            var val = input.value || '';

            // Preview mode can be opted-in per-input using `data-preview="spaced"`.
            // - `data-preview="spaced"` forces the on-screen preview to show the
            //   PDF-style spaced representation (useful for boxed/segmented fields).
            // - Backwards-compatible behaviour: `expiry_date` and `Until` continue
            //   to show the spaced string by default to match the existing PDF logic.
            var previewMode = input.getAttribute('data-preview') || '';
            var wantsSpaced = previewMode === 'spaced' || key === 'expiry_date' || key === 'Until';

            // Special-case: show `vacating_date` in human-friendly slashed format
            // (dd/mm/yyyy) in the preview — this overrides `data-preview="spaced"`.
            // Example: input value `2026-02-14` -> preview shows `14/02/2026`.
            if (key === 'vacating_date' && val) {
                var mSlashIn = val.match(/^(\d{4})-(\d{2})-(\d{2})$/);
                if (mSlashIn) {
                    el.textContent = mSlashIn[3] + '/' + mSlashIn[2] + '/' + mSlashIn[1];
                    return;
                }
                // If user typed an already-slashed date, pass it through unchanged
                var mAlready = val.match(/^(\d{2})\/(\d{2})\/(\d{4})$/);
                if (mAlready) {
                    el.textContent = val;
                    return;
                }
            }

            if (wantsSpaced) {
                var formatted = val;
                var match = formatted.match(/^(\d{4})-(\d{2})-(\d{2})$/);
                if (match) {
                    formatted = match[3] + '-' + match[2] + '-' + match[1];
                }
                // Use the same grouping function as the PDF so preview resembles boxed output.
                var spaced = formatSpacedPairs(formatted, 6);
                el.textContent = spaced || placeholder;
                return;
            }

            // Format date fields as dd-mm-yyyy when entered as yyyy-mm-dd
            if ((
                    key === 'today_date' ||
                    key === 'contract_from' || // tenancy
                    key === 'contract_to'   || // tenancy
                    key === 'expiry_date'  || // NOC
                    key === 'vacating_date'||
                    key === 'Until'        ||
                    key === 'date'         // landlord sign-off
                ) && val) {
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

    // Annual rent full string (numeric + words)
    // For NOC we use the amount field, but keep a fallback
    // in case an explicit annual_rent field is added later.
    var annualRentInput = document.querySelector('[name="annual_rent"]') ||
        document.querySelector('[name="ammount"]');
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

    // NOC radio / checkbox dots in preview
    var nocDots = document.querySelectorAll('[data-noc-dot]');
    var nocRadioGroups = [
        'property_type_choice',
        'furnishing_status',
        'occupancy_status',
        'listing_type',
        'listing_duration',
    ];

    function updateGroupDots(groupName) {
        var selected = document.querySelector('input[name="' + groupName + '"]:checked');
        var value = selected ? selected.value : null;

        nocDots.forEach(function (dot) {
            var def = dot.getAttribute('data-noc-dot') || '';
            var parts = def.split(':');
            if (parts.length !== 2) return;
            var g = parts[0];
            var v = parts[1];
            if (g !== groupName) return;
            dot.style.opacity = (value && value === v) ? '1' : '0';
        });
    }

    nocRadioGroups.forEach(function (groupName) {
        var radios = document.querySelectorAll('input[name="' + groupName + '"]');
        if (!radios.length) return;
        radios.forEach(function (radio) {
            radio.addEventListener('change', function () {
                updateGroupDots(groupName);
            });
        });
        // Initial state
        updateGroupDots(groupName);
    });

    // Intercept NOC form submit to download PDF via fetch (attach CSRF header and show progress)
    var nocForm = document.getElementById('nocLeasingForm');
    if (nocForm) {
        // Ensure hidden csrf_token input exists so non-JS form submits also send it
        (function ensureCsrfInput() {
            try {
                var m = document.cookie.match('(?:^|; )' + 'csrf_token'.replace(/[$()*+.?[\\\]^{|}-]/g, '\\$&') + '=([^;]*)');
                var csrfVal = m ? decodeURIComponent(m[1]) : null;
                if (csrfVal) {
                    var existing = nocForm.querySelector('input[name="csrf_token"]');
                    if (!existing) {
                        var inp = document.createElement('input');
                        inp.type = 'hidden';
                        inp.name = 'csrf_token';
                        inp.value = csrfVal;
                        nocForm.appendChild(inp);
                    }
                }
            } catch (e) {
                // ignore
            }
        })();
        nocForm.addEventListener('submit', async function (ev) {
            ev.preventDefault();

            var submitBtn = document.querySelector('button[form="' + nocForm.id + '"]') || nocForm.querySelector('[type="submit"]');
            if (submitBtn) {
                submitBtn.disabled = true;
            }
            try { ui.showSpinner('Preparing PDF...'); } catch (e) { }

            try {
                var fd = new FormData(nocForm);
                var headers = {};
                var token = localStorage.getItem('crm_token');
                if (token) headers['Authorization'] = 'Bearer ' + token;
                var csrf = (document.cookie.match('(?:^|; )' + 'csrf_token'.replace(/[$()*+.?[\\\]^{|}-]/g, '\\$&') + '=([^;]*)') || [])[1];
                if (csrf) headers['X-CSRF-Token'] = decodeURIComponent(csrf);

                var res = await fetch(nocForm.action, {
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
});
