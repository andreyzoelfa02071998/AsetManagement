(function () {
    function cleanNumber(value) {
        return String(value || '')
            .replace(/\s/g, '')
            .replace(/\./g, '')
            .replace(',', '.')
            .replace(/[^\d.-]/g, '');
    }

    function formatThousands(value) {
        var cleaned = cleanNumber(value);
        if (cleaned === '' || cleaned === '-' || cleaned === '.') {
            return value;
        }

        var parts = cleaned.split('.');
        var whole = parts[0];
        var decimal = parts.length > 1 ? ',' + parts.slice(1).join('') : '';
        var negative = whole.indexOf('-') === 0 ? '-' : '';
        whole = whole.replace('-', '');
        whole = whole.replace(/\B(?=(\d{3})+(?!\d))/g, '.');
        return negative + whole + decimal;
    }

    function formatField(field) {
        field.value = formatThousands(field.value);
    }

    document.addEventListener('input', function (event) {
        if (!event.target.classList.contains('money-input')) {
            return;
        }
        var cursorAtEnd = event.target.selectionStart === event.target.value.length;
        formatField(event.target);
        if (cursorAtEnd) {
            event.target.selectionStart = event.target.selectionEnd = event.target.value.length;
        }
    });

    document.addEventListener('submit', function (event) {
        var fields = event.target.querySelectorAll('.money-input');
        fields.forEach(function (field) {
            field.value = cleanNumber(field.value);
        });
    });

    document.addEventListener('DOMContentLoaded', function () {
        document.querySelectorAll('.money-input').forEach(formatField);
    });

    document.addEventListener('DOMContentLoaded', function () {
        var modal = document.querySelector('.js-price-reminder-modal');
        var openButtons = document.querySelectorAll('.js-price-reminder-open');
        if (!modal || !openButtons.length) {
            return;
        }
        var assetSelect = modal.querySelector('.js-price-reminder-asset');
        var assetIdInput = modal.querySelector('.js-price-reminder-asset-id');
        var context = modal.querySelector('.js-price-reminder-context');
        var lastOpener = null;

        function openModal(opener) {
            lastOpener = opener;
            if (assetSelect && opener.getAttribute('data-asset-id')) {
                assetSelect.value = opener.getAttribute('data-asset-id');
            }
            if (assetIdInput && opener.getAttribute('data-asset-id')) {
                assetIdInput.value = opener.getAttribute('data-asset-id');
            }
            if (context && opener.getAttribute('data-asset-name')) {
                context.textContent = 'Reminder untuk ' + opener.getAttribute('data-asset-name') + ' · harga acuan sekarang ' + (opener.getAttribute('data-current-price') || '-') + '.';
            }
            modal.hidden = false;
            document.body.classList.add('modal-open');
            var firstField = modal.querySelector('[name="price_alert_direction"], [name="price_alert_target"], button');
            if (firstField) {
                firstField.focus();
            }
        }

        function closeModal() {
            modal.hidden = true;
            document.body.classList.remove('modal-open');
            if (lastOpener) {
                lastOpener.focus();
            }
        }

        openButtons.forEach(function (button) {
            button.addEventListener('click', function () {
                openModal(button);
            });
        });

        modal.addEventListener('click', function (event) {
            if (event.target === modal || event.target.closest('.js-price-reminder-close')) {
                closeModal();
            }
        });

        document.addEventListener('keydown', function (event) {
            if (event.key === 'Escape' && !modal.hidden) {
                closeModal();
            }
        });
    });

    document.addEventListener('DOMContentLoaded', function () {
        var provider = document.querySelector('.js-ai-provider');
        var model = document.querySelector('.js-ai-model');
        var tokenLink = document.querySelector('.js-ai-token-link');
        var tokenHelp = document.querySelector('.js-ai-token-help');
        var modelNote = document.querySelector('.js-ai-model-note');
        if (!provider || !model) {
            return;
        }

        var tokenMeta = {
            chatgpt: {
                href: 'https://platform.openai.com/api-keys',
                text: 'Ambil token ChatGPT / OpenAI',
                help: 'Buka link OpenAI, login, klik Create new secret key, lalu salin key ke field API Key. Model OpenAI API umumnya berbayar sesuai pemakaian.',
                note: 'ChatGPT gratis di aplikasi tidak sama dengan OpenAI API. Untuk web app lewat API key, model ChatGPT/OpenAI umumnya berbayar/pay-as-you-go.'
            },
            gemini: {
                href: 'https://aistudio.google.com/app/apikey',
                text: 'Ambil token Gemini / Google AI Studio',
                help: 'Buka Google AI Studio, klik Create API key, salin tokennya, lalu tempel di field API Key. Beberapa model Gemini punya free tier dengan limit.',
                note: 'Gemini memakai token langsung dari Google AI Studio. Beberapa model punya free tier, lalu bisa berbayar kalau melewati limit atau billing aktif.'
            },
            claude: {
                href: 'https://console.anthropic.com/settings/keys',
                text: 'Ambil token Claude / Anthropic',
                help: 'Buka Anthropic Console, buat API key, salin tokennya, lalu tempel di field API Key. Claude API umumnya berbayar.',
                note: 'Claude memakai token langsung dari Anthropic Console dan umumnya berbayar/pay-as-you-go.'
            },
            qwen: {
                href: 'https://openrouter.ai/settings/keys',
                text: 'Ambil token Qwen via OpenRouter',
                help: 'Buka OpenRouter, buat API key, lalu pilih model Qwen. Model bertanda Free bisa punya limit dan ketersediaannya bisa berubah.',
                note: 'Qwen punya opsi Free lewat OpenRouter. Gratisnya bisa terbatas dan bisa berubah sewaktu-waktu.'
            },
            llama: {
                href: 'https://openrouter.ai/settings/keys',
                text: 'Ambil token Llama via OpenRouter',
                help: 'Buka OpenRouter, buat API key, lalu pilih model Llama. Model bertanda Free bisa punya limit dan ketersediaannya bisa berubah.',
                note: 'Llama punya opsi Free lewat OpenRouter. Gratisnya bisa terbatas dan bisa berubah sewaktu-waktu.'
            },
            custom: {
                href: 'https://platform.openai.com/api-keys',
                text: 'Ambil token provider custom',
                help: 'Gunakan halaman API key dari provider custom yang dipakai, lalu isi Base URL endpoint kompatibel OpenAI. Status gratis/berbayar tergantung provider itu.',
                note: 'Untuk Custom, status Free/Berbayar tergantung provider yang dipakai. Cek pricing provider sebelum upload gambar.'
            }
        };

        function syncAiFields() {
            var activeProvider = provider.value;
            var selectedVisible = false;
            var firstVisible = null;

            Array.prototype.forEach.call(model.options, function (option) {
                var visible = option.getAttribute('data-provider') === activeProvider;
                option.hidden = !visible;
                option.disabled = !visible;
                if (visible && !firstVisible) {
                    firstVisible = option;
                }
                if (visible && option.selected) {
                    selectedVisible = true;
                }
            });

            if (!selectedVisible && firstVisible) {
                firstVisible.selected = true;
            }

            if (tokenLink && tokenMeta[activeProvider]) {
                tokenLink.href = tokenMeta[activeProvider].href;
                tokenLink.textContent = tokenMeta[activeProvider].text;
            }
            if (tokenHelp && tokenMeta[activeProvider]) {
                tokenHelp.setAttribute('data-tooltip', tokenMeta[activeProvider].help);
            }
            if (modelNote && tokenMeta[activeProvider]) {
                modelNote.textContent = tokenMeta[activeProvider].note;
            }
        }

        provider.addEventListener('change', syncAiFields);
        syncAiFields();
    });

    document.addEventListener('change', function (event) {
        if (!event.target.classList.contains('js-asset-type') || event.target.value !== 'rdn') {
            return;
        }

        var form = event.target.closest('form');
        if (!form) {
            return;
        }

        var name = form.querySelector('[name="name"]');
        var symbol = form.querySelector('[name="symbol"]');
        var platform = form.querySelector('[name="platform"]');
        var unit = form.querySelector('[name="unit"]');
        var avg = form.querySelector('[name="avg_price"]');
        var market = form.querySelector('[name="market_price"]');
        var planned = form.querySelector('[name="is_planned"]');
        var alertEnabled = form.querySelector('[name="price_alert_enabled"]');
        var alertTarget = form.querySelector('[name="price_alert_target"]');

        if (name && !name.value) {
            name.value = 'Saldo RDN';
        }
        if (symbol && !symbol.value) {
            symbol.value = 'IDR';
        }
        if (platform && !platform.value) {
            platform.value = 'RDN';
        }
        if (unit) {
            unit.value = 'idr';
        }
        if (avg && !cleanNumber(avg.value)) {
            avg.value = '1';
        }
        if (market && !cleanNumber(market.value)) {
            market.value = '1';
        }
        if (planned) {
            planned.value = '0';
        }
        if (alertEnabled) {
            alertEnabled.value = '0';
        }
        if (alertTarget) {
            alertTarget.value = '';
        }
    });

    document.addEventListener('click', function (event) {
        var link = event.target.closest('.js-market-update');
        if (!link) {
            return;
        }

        event.preventDefault();
        var originalText = link.textContent;
        link.textContent = 'Updating...';
        link.style.pointerEvents = 'none';

        fetch(link.href, {
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json'
            }
        })
            .then(function (response) { return response.json(); })
            .then(function () { window.location.reload(); })
            .catch(function () {
                link.textContent = 'Gagal update';
                setTimeout(function () {
                    link.textContent = originalText;
                    link.style.pointerEvents = '';
                }, 1800);
            });
    });

    document.addEventListener('DOMContentLoaded', function () {
        var autoSync = document.querySelector('.js-auto-market-sync');
        if (!autoSync) {
            return;
        }

        var status = document.querySelector('.js-auto-market-status');
        var interval = parseInt(autoSync.getAttribute('data-interval'), 10) || 60000;
        var syncUrl = autoSync.getAttribute('data-sync-url');
        var storageKey = 'analisa_aset_last_market_sync';
        var timer = null;

        function setStatus(message) {
            if (status) {
                status.textContent = message;
            }
        }

        function scheduleNext(delay) {
            window.clearTimeout(timer);
            timer = window.setTimeout(runSync, Math.max(1000, delay));
        }

        function runSync() {
            setStatus('Update harga market berjalan...');

            fetch(syncUrl, {
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json'
                }
            })
                .then(function (response) { return response.json(); })
                .then(function (payload) {
                    sessionStorage.setItem(storageKey, String(Date.now()));
                    setStatus(payload.message || 'Harga market berhasil diupdate.');
                    window.setTimeout(function () {
                        window.location.reload();
                    }, 700);
                })
                .catch(function () {
                    sessionStorage.setItem(storageKey, String(Date.now()));
                    setStatus('Update otomatis gagal. Akan dicoba lagi 1 menit lagi.');
                    scheduleNext(interval);
                });
        }

        var lastSync = parseInt(sessionStorage.getItem(storageKey), 10) || 0;
        var elapsed = Date.now() - lastSync;
        if (elapsed >= interval) {
            scheduleNext(1200);
        } else {
            var nextDelay = interval - elapsed;
            setStatus('Update berikutnya otomatis dalam kurang dari 1 menit.');
            scheduleNext(nextDelay);
        }
    });
})();
