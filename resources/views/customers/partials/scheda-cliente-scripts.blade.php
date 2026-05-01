    <script src="{{ URL::asset('js/autofill-select.js') }}"></script>
    @php
        $geoNazioniMap = collect($geoNazioni ?? [])->map(function ($n) {
            return [
                'id' => (string) $n->id,
                'nome' => $n->nome,
                'cittadinanza' => $n->cittadinanza ?: $n->nome,
                'codice_iso2' => strtoupper((string) ($n->codice_iso2 ?? '')),
                'is_italia' => (bool) ($n->is_italia ?? false),
            ];
        })->values();
    @endphp
    <script>
        window.__geoNazioniMap = @json($geoNazioniMap);
    </script>
    <script>
    const customerForm = document.querySelector('form.form-steps');
    const customerMode = customerForm?.dataset?.mode || 'create';
    const activeTabInput = document.getElementById('customer_active_tab');
    const saveModeIntentInput = document.getElementById('customer_save_mode_intent');
    const draftKey = customerForm?.dataset?.draftKey || 'clienti.nuovo.draft.v1';

    const cssEscape = (value) => {
        if (window.CSS && typeof window.CSS.escape === 'function') {
            return window.CSS.escape(value);
        }
        return String(value).replace(/([ #;?%&,.+*~\':"!^$[\]()=>|\/@])/g, '\\$1');
    };

    const collectDraftData = (form) => {
        const data = {};
        const fields = form.querySelectorAll('input[name], select[name], textarea[name]');
        fields.forEach((field) => {
            const name = field.getAttribute('name');
            if (!name || name === '_token' || name === '_method') {
                return;
            }
            if (field.type === 'file') {
                return;
            }
            if (field.type === 'checkbox') {
                data[name] = field.checked ? (field.value || '1') : '';
                return;
            }
            if (field.type === 'radio') {
                if (field.checked) {
                    data[name] = field.value;
                } else if (typeof data[name] === 'undefined') {
                    data[name] = '';
                }
                return;
            }
            data[name] = field.value ?? '';
        });
        return data;
    };

    const hasNonEmptyValue = (form) => {
        const fields = form.querySelectorAll('input[name], select[name], textarea[name]');
        return Array.from(fields).some((field) => {
            const name = field.getAttribute('name');
            if (!name || name === '_token' || name === '_method') {
                return false;
            }
            if (field.type === 'checkbox' || field.type === 'radio') {
                return field.checked;
            }
            return String(field.value || '').trim() !== '';
        });
    };

    const saveDraft = () => {
        if (!customerForm) return;
        const payload = {
            savedAt: new Date().toISOString(),
            data: collectDraftData(customerForm),
        };
        localStorage.setItem(draftKey, JSON.stringify(payload));
    };

    const restoreDraft = () => {
        if (!customerForm) return;
        if (hasNonEmptyValue(customerForm)) return;

        const raw = localStorage.getItem(draftKey);
        if (!raw) return;

        let payload = null;
        try {
            payload = JSON.parse(raw);
        } catch (e) {
            localStorage.removeItem(draftKey);
            return;
        }

        if (!payload || typeof payload !== 'object' || !payload.data) {
            localStorage.removeItem(draftKey);
            return;
        }

        Object.entries(payload.data).forEach(([name, value]) => {
            const elements = customerForm.querySelectorAll(`[name="${cssEscape(name)}"]`);
            elements.forEach((el) => {
                if (el.type === 'checkbox') {
                    el.checked = String(value) !== '' && String(value) !== '0';
                } else if (el.type === 'radio') {
                    el.checked = el.value === value;
                } else {
                    el.value = value ?? '';
                }
                el.dispatchEvent(new Event('change', { bubbles: true }));
            });
        });

        const ts = document.createElement('small');
        ts.className = 'text-muted d-block mt-2';
        ts.textContent = 'Bozza ripristinata automaticamente.';
        const header = document.querySelector('.card-header');
        if (header) {
            header.appendChild(ts);
        }
    };

    const clearDraft = () => localStorage.removeItem(draftKey);

    const activateTabById = (tabId) => {
        if (!tabId) return;
        const tabButton =
            document.querySelector(`[data-bs-target="#${cssEscape(tabId)}"]`) ||
            document.getElementById(`${tabId}-tab`) ||
            document.getElementById(tabId);

        if (tabButton && window.bootstrap && window.bootstrap.Tab) {
            const tab = new window.bootstrap.Tab(tabButton);
            tab.show();
        }
    };

    if (customerForm) {
        restoreDraft();

        let saveTimer = null;
        const queueSave = () => {
            window.clearTimeout(saveTimer);
            saveTimer = window.setTimeout(saveDraft, 250);
        };

        customerForm.addEventListener('input', queueSave);
        customerForm.addEventListener('change', queueSave);
        customerForm.addEventListener('submit', clearDraft);

        document.querySelectorAll('.nexttab, [data-bs-toggle="pill"]').forEach((el) => {
            el.addEventListener('click', saveDraft);
        });

        document.querySelectorAll('[data-bs-toggle="pill"]').forEach((button) => {
            button.addEventListener('shown.bs.tab', () => {
                const target = button.getAttribute('data-bs-target') || '';
                if (activeTabInput && target.startsWith('#')) {
                    activeTabInput.value = target.slice(1);
                }
            });
        });

        customerForm.querySelectorAll('button[type="submit"][name="save_mode"]').forEach((button) => {
            button.addEventListener('click', () => {
                if (saveModeIntentInput) {
                    saveModeIntentInput.value = button.value || '';
                }
            });
        });

        customerForm.querySelectorAll('.nexttab').forEach((btn) => {
            btn.addEventListener('click', function (event) {
                event.preventDefault();

                if (customerMode === 'create' && !validateCurrentTab(btn)) {
                    return;
                }

                const nextId = btn.getAttribute('data-nexttab');
                if (!nextId) return;
                activateTabById(nextId);
            });
        });

        activateTabById(activeTabInput?.value || 'steparrow-gen-info');
        window.addEventListener('beforeunload', saveDraft);
    }

    if (customerForm) {
        ['email_az', 'phone_az', 'fax_az', 'cellphone_az'].forEach((fieldName) => {
            const mirroredFields = Array.from(customerForm.querySelectorAll(`[name="${fieldName}"]`));
            if (mirroredFields.length < 2) return;

            mirroredFields.forEach((field) => {
                field.addEventListener('input', () => {
                    mirroredFields.forEach((otherField) => {
                        if (otherField === field) return;
                        otherField.value = field.value;
                    });
                });
            });
        });
    }

    const birthGeo = document.querySelector('[data-ui="geo-italia"][data-prefix="birth"]');
    const birthCountryField = birthGeo ? birthGeo.querySelector('[name="country_reg"]') : null;
    const birthCountryFallback = document.getElementById('country_reg_fallback');
    const cittadinanzaInput = document.getElementById('customer-ciudadania-reg');
    let birthNationSelected = { id: '', label: '', cittadinanza: '' };
    let lastAutoCittadinanza = '';
    const geoNazioniMap = Array.isArray(window.__geoNazioniMap) ? window.__geoNazioniMap : [];

    const ensureSelectValue = (selectEl, value, label) => {
        if (!selectEl || !value) return;
        const normalized = String(value);
        let option = Array.from(selectEl.options).find((opt) => String(opt.value) === normalized);
        if (!option) {
            option = new Option(label || normalized, normalized, true, true);
            selectEl.appendChild(option);
        }
        selectEl.value = normalized;
    };

    const resolveCountryMapping = () => {
        if (!birthCountryField || !cittadinanzaInput) return;
        const selected = birthCountryField.options[birthCountryField.selectedIndex] || null;
        const selectedValue = selected ? String(selected.value || '').trim() : '';
        const label = selected ? (selected.text || '').trim() : '';
        const norm = (v) => String(v || '').trim().toLowerCase();
        return geoNazioniMap.find((row) => {
            return norm(row.id) === norm(selectedValue)
                || norm(row.nome) === norm(label)
                || norm(row.codice_iso2) === norm(selectedValue);
        });
    };

    const applyCittadinanzaValue = (value) => {
        const autoCit = String(value || '').trim();
        if (!cittadinanzaInput || !autoCit) return;

        const currentCit = String(cittadinanzaInput.value || '').trim();
        const canOverride = !currentCit || !lastAutoCittadinanza || currentCit.toLowerCase() === lastAutoCittadinanza.toLowerCase();

        if (!canOverride) {
            return;
        }

        ensureSelectValue(cittadinanzaInput, autoCit, autoCit);
        lastAutoCittadinanza = autoCit;

        if (window.jQuery) {
            window.jQuery(cittadinanzaInput).trigger('change');
        } else {
            cittadinanzaInput.dispatchEvent(new Event('change', { bubbles: true }));
        }
    };

    const syncCittadinanzaFromCountry = () => {
        if (!birthCountryField || !cittadinanzaInput) return;
        const selected = birthCountryField.options[birthCountryField.selectedIndex] || null;
        const label = selected ? (selected.text || '').trim() : '';
        const mapped = resolveCountryMapping();
        const fallbackLabel = birthNationSelected.cittadinanza || birthNationSelected.label || label;
        const autoCit = (mapped && mapped.cittadinanza) ? mapped.cittadinanza : fallbackLabel;
        applyCittadinanzaValue(autoCit);
    };

    if (birthCountryField && window.jQuery) {
        window.jQuery(birthCountryField).on('select2:select', (event) => {
            const data = event && event.params ? (event.params.data || {}) : {};
            birthNationSelected = {
                id: data.id ? String(data.id) : '',
                label: data.text || '',
                cittadinanza: data.cittadinanza || '',
            };
            if (birthCountryFallback) {
                birthCountryFallback.value = birthNationSelected.id || birthNationSelected.label || '';
            }

            applyCittadinanzaValue(birthNationSelected.cittadinanza || birthNationSelected.label);
            syncCittadinanzaFromCountry();
        });
    }

    if (birthCountryField) {
        birthCountryField.addEventListener('change', syncCittadinanzaFromCountry);
    }

    if (birthGeo) {
        birthGeo.addEventListener('geoselect:change', (event) => {
            const detail = event && event.detail ? event.detail : {};
            const nazione = detail.nazione || {};
            const mapped = geoNazioniMap.find((row) => {
                const norm = (v) => String(v || '').trim().toLowerCase();
                return norm(row.id) === norm(nazione.id || nazione.value)
                    || norm(row.nome) === norm(nazione.label);
            });

            birthNationSelected = {
                id: String(nazione.id || nazione.value || ''),
                label: String(nazione.label || ''),
                cittadinanza: String((mapped && mapped.cittadinanza) || nazione.label || ''),
            };

            if (birthCountryFallback) {
                birthCountryFallback.value = birthNationSelected.id || birthNationSelected.label || '';
            }

            applyCittadinanzaValue(birthNationSelected.cittadinanza);
        });
    }

    if (cittadinanzaInput) {
        cittadinanzaInput.addEventListener('change', () => {
            const currentCit = String(cittadinanzaInput.value || '').trim();
            if (!currentCit) {
                lastAutoCittadinanza = '';
                return;
            }

            if (lastAutoCittadinanza && currentCit.toLowerCase() !== lastAutoCittadinanza.toLowerCase()) {
                lastAutoCittadinanza = '';
            }
        });
    }

    if (birthCountryField && cittadinanzaInput) {
        window.setTimeout(() => {
            const currentCit = String(cittadinanzaInput.value || '').trim();
            if (!currentCit) {
                syncCittadinanzaFromCountry();
                return;
            }

            const mapped = resolveCountryMapping();
            const autoCit = mapped?.cittadinanza || '';
            if (autoCit && currentCit.toLowerCase() === autoCit.toLowerCase()) {
                syncCittadinanzaFromCountry();
            }
        }, 0);
    }

    if (customerForm && birthCountryField) {
        customerForm.addEventListener('submit', () => {
            if (!birthCountryField.value) {
                if (birthNationSelected.id) {
                    ensureSelectValue(birthCountryField, birthNationSelected.id, birthNationSelected.label || birthNationSelected.id);
                } else if (birthNationSelected.label) {
                    ensureSelectValue(birthCountryField, birthNationSelected.label, birthNationSelected.label);
                }
            }
            if (birthCountryFallback && !birthCountryFallback.value) {
                birthCountryFallback.value = birthCountryField.value || birthNationSelected.id || birthNationSelected.label || '';
            }
        });
    }

    const consentSwitches = Array.from(document.querySelectorAll('.js-consent-switch'));
    const nowTimestamp = () => {
        const d = new Date();
        const pad = (v) => String(v).padStart(2, '0');
        return `${d.getFullYear()}-${pad(d.getMonth() + 1)}-${pad(d.getDate())} ${pad(d.getHours())}:${pad(d.getMinutes())}:${pad(d.getSeconds())}`;
    };

    consentSwitches.forEach((switchEl) => {
        const targetName = switchEl.dataset.timestampTarget;
        const timestampInput = targetName ? customerForm?.querySelector(`input[name="${targetName}"]`) : null;
        if (!timestampInput) return;

        if (switchEl.checked && !timestampInput.value) {
            timestampInput.value = nowTimestamp();
        }

        switchEl.addEventListener('change', () => {
            if (switchEl.checked) {
                if (!timestampInput.value) {
                    timestampInput.value = nowTimestamp();
                }
            } else {
                timestampInput.value = '';
            }
        });
    });

    const hasAziendaSwitch = document.getElementById('has_azienda_switch');
    const aziendaScopes = customerForm ? customerForm.querySelectorAll('[data-azienda-scope]') : [];

    const clearAziendaField = (field) => {
        if (field.type === 'checkbox' || field.type === 'radio') {
            field.checked = false;
        } else if (field.tagName === 'SELECT') {
            field.value = '';
            if (window.jQuery) {
                window.jQuery(field).val(null).trigger('change.select2');
            } else {
                field.dispatchEvent(new Event('change', { bubbles: true }));
            }
        } else {
            field.value = '';
        }
    };

    const setAziendaEnabled = (enabled) => {
        aziendaScopes.forEach((scope) => {
            scope.dataset.aziendaDisabled = enabled ? '0' : '1';
            scope.style.opacity = enabled ? '' : '0.55';

            scope.querySelectorAll('input, select, textarea, button').forEach((field) => {
                if (field.id === 'has_azienda_switch' || field.id === 'has_azienda_hidden' || field.name === 'has_azienda') return;
                field.disabled = !enabled;
                if (!enabled) {
                    clearAziendaField(field);
                }
            });

            // Blocca anche elementi cliccabili non-form (es. controlli custom nel componente GEO).
            scope.querySelectorAll('a, [role="button"], [data-bs-toggle]').forEach((el) => {
                if (el.id === 'has_azienda_switch') return;
                if (!enabled) {
                    el.setAttribute('aria-disabled', 'true');
                    el.style.pointerEvents = 'none';
                } else {
                    el.removeAttribute('aria-disabled');
                    el.style.pointerEvents = '';
                }
            });
        });
    };

    if (hasAziendaSwitch) {
        hasAziendaSwitch.addEventListener('change', () => {
            setAziendaEnabled(hasAziendaSwitch.checked);
            applyTipoClienteRules();
        });

        setAziendaEnabled(hasAziendaSwitch.checked);
    }

    const lockCalendarTextEntry = (fieldName) => {
        const source = document.querySelector(`input[name="${fieldName}"]`);
        if (!source) return;

        const targets = [source];
        if (source._flatpickr && source._flatpickr.altInput) {
            targets.push(source._flatpickr.altInput);
        }

        const applyLock = (input, index) => {
            input.setAttribute('autocomplete', 'new-password');
            input.setAttribute('autocorrect', 'off');
            input.setAttribute('autocapitalize', 'off');
            input.setAttribute('spellcheck', 'false');
            input.setAttribute('data-lpignore', 'true');
            input.setAttribute('data-form-type', 'other');
            input.readOnly = true;

            // Evita suggerimenti browser sul campo visibile di flatpickr.
            if (index > 0) {
                input.removeAttribute('name');
                input.id = `${fieldName}_calendar_visible`;
            }

            ['keydown', 'keypress', 'keyup', 'paste', 'drop', 'input', 'beforeinput'].forEach((evt) => {
                input.addEventListener(evt, (e) => e.preventDefault());
            });
        };

        targets.forEach((input, index) => applyLock(input, index));
    };

    const lockCalendarsWithRetry = () => {
        let attempts = 0;
        const maxAttempts = 20;
        const timer = window.setInterval(() => {
            attempts += 1;
            lockCalendarTextEntry('date_pub_reg');
            lockCalendarTextEntry('expire_reg');

            const startReady = !!document.querySelector('input[name="date_pub_reg"]')?._flatpickr?.altInput;
            const expireReady = !!document.querySelector('input[name="expire_reg"]')?._flatpickr?.altInput;
            if ((startReady && expireReady) || attempts >= maxAttempts) {
                window.clearInterval(timer);
            }
        }, 100);
    };

    lockCalendarsWithRetry();

    const publishedCountrySelect = document.getElementById('customer-published-country');
    const publishedCityWrap = document.getElementById('customer-published-city-wrap');
    const publishedCitySelect = document.getElementById('customer-published-city');
    let publishedCityVisible = null;
    const togglePublishedCity = () => {
        if (!publishedCountrySelect || !publishedCityWrap) return;

        const countryRaw = String(publishedCountrySelect.value || '').trim();
        const country = countryRaw
            .normalize('NFD')
            .replace(/[\u0300-\u036f]/g, '')
            .toLowerCase();
        const isItalia = country === 'italia' || country.includes('italia');

        if (publishedCityVisible === isItalia) {
            return;
        }

        publishedCityVisible = isItalia;
        publishedCityWrap.classList.toggle('d-none', !isItalia);
        if (!isItalia && publishedCitySelect && String(publishedCitySelect.value || '').trim() !== '') {
            publishedCitySelect.value = '';
            if (window.jQuery) {
                window.jQuery(publishedCitySelect).val(null).trigger('change');
            } else {
                publishedCitySelect.dispatchEvent(new Event('change', { bubbles: true }));
            }
        }
    };

    if (publishedCountrySelect) {
        publishedCountrySelect.addEventListener('change', togglePublishedCity);
        publishedCountrySelect.addEventListener('input', togglePublishedCity);
        if (window.jQuery) {
            window.jQuery(publishedCountrySelect).on('change select2:select select2:clear select2:close', togglePublishedCity);
        }
        document.addEventListener('change', (event) => {
            if (event.target === publishedCountrySelect) {
                togglePublishedCity();
            }
        }, true);

        window.setTimeout(() => {
            togglePublishedCity();
            const rendered = document.getElementById('select2-customer-published-country-container');
            if (rendered && typeof MutationObserver !== 'undefined') {
                const observer = new MutationObserver(() => {
                    togglePublishedCity();
                });
                observer.observe(rendered, {
                    childList: true,
                    subtree: true,
                    characterData: true,
                });
            }
        }, 0);

    }

    // Selecciona todos los inputs con la clase "number"
    const numberInputs = document.querySelectorAll('input.number');
    const phoneInputs = document.querySelectorAll('input.phone');
    const emailInputs = document.querySelectorAll('input.email');
    const textOnlyInputs = document.querySelectorAll('input.customer-text-only');
    const tipoClienteSelect = document.getElementById('customer-type-cliente');

    const applyTipoClienteRules = () => {
        if (!tipoClienteSelect) return;
        const tipo = String(tipoClienteSelect.value || '').trim().toLowerCase();
        const isOspite = tipo === 'ospite';
        const isComponente = tipo === 'componente';
        const isRichiesta = tipo === 'richiesta';
        const isClienteCompleto = isOspite || isComponente;
        const isImportMode = customerMode === 'import';

        const setRequired = (name, required) => {
            const field = customerForm ? customerForm.querySelector(`[name="${name}"]`) : null;
            if (!field) return;
            if (required) {
                field.setAttribute('required', 'required');
            } else {
                field.removeAttribute('required');
            }
        };

        setRequired('name', true);
        setRequired('surname', true);
        setRequired('sex', isClienteCompleto);
        setRequired('country', isImportMode ? false : isClienteCompleto);
        setRequired('region', isImportMode ? false : isClienteCompleto);
        setRequired('province', isImportMode ? false : isClienteCompleto);
        setRequired('city', isImportMode ? false : isClienteCompleto);
        setRequired('typeaway', isImportMode ? false : isClienteCompleto);
        setRequired('address', isImportMode ? false : isClienteCompleto);
        setRequired('number', isImportMode ? false : isClienteCompleto);

        setRequired('country_reg', isClienteCompleto);
        setRequired('region_reg', false);
        setRequired('prov_reg', false);
        setRequired('city_reg', isClienteCompleto);
        setRequired('ciudadania_reg', isClienteCompleto);
        setRequired('nac_reg', isClienteCompleto);

        setRequired('type_doc_reg', isClienteCompleto);
        setRequired('num_doc_reg', isClienteCompleto);
        setRequired('date_pub_reg', isImportMode ? false : isClienteCompleto);
        setRequired('expire_reg', isImportMode ? false : isClienteCompleto);
        setRequired('rilasciato_reg', isImportMode ? false : isClienteCompleto);
        setRequired('country_doc_reg', isImportMode ? false : isClienteCompleto);

        const countryDocValue = String(customerForm?.querySelector('[name="country_doc_reg"]')?.value || '').trim().toLowerCase();
        const countryDocItalia = countryDocValue.normalize('NFD').replace(/[\u0300-\u036f]/g, '').includes('italia');
        setRequired('city_doc_reg', isImportMode ? false : (isClienteCompleto && countryDocItalia));

        setRequired('email', false);
        setRequired('phone', false);

        const aziendaEnabled = !!hasAziendaSwitch?.checked;
        setRequired('azienda', aziendaEnabled);
        setRequired('cf_az', aziendaEnabled);
        setRequired('sdi_az', aziendaEnabled);
        setRequired('email_az', aziendaEnabled);
        setRequired('phone_az', aziendaEnabled);
        setRequired('country_az', aziendaEnabled);
        setRequired('city_az', aziendaEnabled);
    };

    if (tipoClienteSelect) {
        tipoClienteSelect.addEventListener('change', applyTipoClienteRules);
        applyTipoClienteRules();
    }

    if (publishedCountrySelect) {
        publishedCountrySelect.addEventListener('change', applyTipoClienteRules);
    }


    numberInputs.forEach(input => {
        input.addEventListener('input', function (e) {
            // Remueve cualquier carácter que no sea numérico
            input.value = input.value.replace(/[^0-9]/g, '');
        });
    });
     // Validación para los teléfonos (permitir números, espacios, paréntesis, guiones)
    phoneInputs.forEach(input => {
        input.addEventListener('input', function (e) {
            // Permitir solo números, espacios, paréntesis, guiones
            input.value = input.value.replace(/[^0-9\-\(\)\s\+\/]/g, '');
        });
    });

    textOnlyInputs.forEach((input) => {
        input.addEventListener('input', () => {
            input.value = input.value.replace(/[0-9]/g, '');
        });
    });

    // Validazione email coerente con UX centralizzata (niente alert native).
    emailInputs.forEach(input => {
        input.addEventListener('input', function () {
            input.setCustomValidity('');
        });
        input.addEventListener('blur', function (e) {
            const emailPattern = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
            const value = String(input.value || '').trim();
            if (!value) {
                input.setCustomValidity('');
                return;
            }
            if (!emailPattern.test(value)) {
                input.setCustomValidity('Inserisci un indirizzo email valido.');
                return;
            }
            input.setCustomValidity('');
        });
    });

    const validateCurrentTab = (trigger) => {
        const pane = trigger.closest('.tab-pane');
        if (!pane) return true;

        const fields = pane.querySelectorAll('input, select, textarea');
        for (const field of fields) {
            if (field.disabled || field.type === 'hidden' || field.closest('[data-azienda-scope][data-azienda-disabled="1"]')) {
                continue;
            }
            if (typeof field.reportValidity === 'function' && !field.reportValidity()) {
                field.focus();
                return false;
            }
        }

        return true;
    };

    </script>
