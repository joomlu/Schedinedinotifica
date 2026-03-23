@php
    $geoNazioniMap = collect($nations ?? [])->map(function ($n) {
        $id = is_array($n) ? ($n['id'] ?? '') : ($n->id ?? '');
        $nome = is_array($n) ? ($n['nome'] ?? '') : ($n->nome ?? '');
        $cittadinanza = is_array($n)
            ? (($n['cittadinanza'] ?? '') ?: ($n['nome'] ?? ''))
            : (($n->cittadinanza ?? '') ?: ($n->nome ?? ''));
        $codiceIso2 = is_array($n) ? ($n['codice_iso2'] ?? '') : ($n->codice_iso2 ?? '');

        return [
            'id' => (string) $id,
            'nome' => $nome,
            'cittadinanza' => $cittadinanza,
            'codice_iso2' => strtoupper((string) $codiceIso2),
        ];
    })->values();
@endphp

<script>
    document.addEventListener('DOMContentLoaded', function () {
        const geoNazioniMap = @json($geoNazioniMap);
        const nextButtons = document.querySelectorAll('.nexttab');
        const prevButtons = document.querySelectorAll('.prevtab');

        const activateTab = (tabId) => {
            const tabTrigger = document.querySelector(`#${tabId}`);
            if (!tabTrigger) return;
            const tab = new bootstrap.Tab(tabTrigger);
            tab.show();
        };

        nextButtons.forEach((btn) => {
            btn.addEventListener('click', () => {
                const nextId = btn.dataset.nexttab;
                if (nextId) activateTab(nextId);
            });
        });

        prevButtons.forEach((btn) => {
            btn.addEventListener('click', () => {
                const prevId = btn.dataset.previous;
                if (prevId) activateTab(prevId);
            });
        });

        const searchInput = document.getElementById('search');
        const surnameInput = document.getElementById('surname');
        const customerIdInput = document.getElementById('customer_id');
        const customerNumeroInput = document.getElementById('customer-numero');
        const customerTypeHousedInput = document.getElementById('customer-type-housed');
        const resultsContainer = document.getElementById('results');
        const anagGeo = document.querySelector('[data-ui="geo-italia"][data-prefix="birth"]');
        const anagCountrySelect = document.querySelector('[name="oa_country"]');
        const anagCittadinanzaSelect = document.getElementById('schedina-oa-city-nac');
        const publishedCountrySelect = document.getElementById('or-published-country');
        const publishedCityWrap = document.getElementById('or-published-city-wrap');
        const publishedCitySelect = document.getElementById('or-published-city');
        let anagNationSelected = { id: '', label: '', cittadinanza: '' };
        let lastAutoCittadinanza = '';

        const setFieldValue = (name, value) => {
            if (value === null || value === undefined || value === '') return;
            const fields = document.querySelectorAll(`[name="${name}"]`);
            fields.forEach((field) => {
                if (field.tagName === 'SELECT') {
                    let option = Array.from(field.options).find((opt) => opt.value === String(value));
                    if (!option) {
                        option = document.createElement('option');
                        option.value = String(value);
                        option.textContent = String(value);
                        field.appendChild(option);
                    }
                    field.value = String(value);
                    field.dispatchEvent(new Event('change', { bubbles: true }));
                    return;
                }

                field.value = String(value);
                field.dispatchEvent(new Event('input', { bubbles: true }));
                field.dispatchEvent(new Event('change', { bubbles: true }));
            });
        };

        const setConsentField = (name, value, timestampName, timestampValue) => {
            const fields = document.querySelectorAll(`[name="${name}"]`);
            const checked = value === true || value === 1 || value === '1';

            fields.forEach((field) => {
                if (field.type === 'checkbox') {
                    field.checked = checked;
                    field.dispatchEvent(new Event('change', { bubbles: true }));
                    return;
                }

                if (field.type === 'hidden') {
                    field.value = field.value === '0' ? '0' : (checked ? '1' : '0');
                }
            });

            if (timestampName) {
                document.querySelectorAll(`[name="${timestampName}"]`).forEach((field) => {
                    field.value = checked && timestampValue ? String(timestampValue) : '';
                    field.dispatchEvent(new Event('change', { bubbles: true }));
                });
            }
        };

        const applyCustomerDataToSchedina = (customer) => {
            const mapping = {
                customer_id: customer.id,
                customer_type_housed: customer.type_housed,
                customer_group: customer.group,
                customer_subgroup: customer.subgroup,
                customer_subgroup1: customer.subgroup1,
                customer_email: customer.email,
                customer_phone: customer.phone,
                customer_cellphone: customer.cellphone,
                customer_fax: customer.fax,
                customer_observation: customer.observation,
                customer_anag_observation: customer.observation_reg,
                type: customer.type,
                name: customer.name,
                surname: customer.surname,
                sex: customer.sex,
                oa_country: customer.country_reg,
                oa_city: customer.city_reg,
                oa_region: customer.region_reg || customer.region,
                oa_prov: customer.prov_reg,
                oa_cap: customer.cap_reg,
                oa_city_nac: customer.ciudadania_reg,
                oa_date_nac: customer.nac_reg,
                or_country: customer.country,
                or_city: customer.city,
                or_region: customer.region,
                or_prov: customer.province,
                or_cap: customer.cap,
                or_typeaway: customer.typeaway,
                or_address: customer.address,
                or_num: customer.number,
                or_doc: customer.num_doc_reg,
                or_doctype: customer.type_doc_reg,
                or_published_date: customer.date_pub_reg,
                or_expire: customer.expire_reg,
                or_published: customer.rilasciato_reg,
                or_published_country: customer.country_doc_reg || customer.country_reg,
                or_published_city: customer.city_doc_reg || customer.city_reg,
            };

            Object.entries(mapping).forEach(([name, value]) => setFieldValue(name, value));
            setConsentField('customer_privacy_consent', customer.privacy_consent, 'customer_privacy_consent_at', customer.privacy_consent_at);
            setConsentField('customer_marketing_consent', customer.marketing_consent, 'customer_marketing_consent_at', customer.marketing_consent_at);
            setConsentField('customer_communication_consent', customer.communication_consent, 'customer_communication_consent_at', customer.communication_consent_at);

            if (searchInput) {
                const fullName = `${customer.surname || ''} ${customer.name || ''}`.trim();
                searchInput.value = fullName;
            }
            if (surnameInput) {
                surnameInput.value = customer.surname || '';
            }
            if (customerIdInput) {
                customerIdInput.value = customer.id || '';
            }
            if (customerNumeroInput) {
                customerNumeroInput.value = customer.numero_cliente || '';
            }
            if (customerTypeHousedInput) {
                customerTypeHousedInput.value = customer.type_housed || '';
            }

            const peopleInput = document.querySelector('[name="cant_people"]');
            if (peopleInput && (!peopleInput.value || Number(peopleInput.value) < 1)) {
                peopleInput.value = '1';
            }

            window.setTimeout(() => {
                syncCittadinanzaFromCountry();
                togglePublishedCity();
            }, 0);
        };

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

        const resolveAnagCountryMapping = () => {
            if (!anagCountrySelect || !anagCittadinanzaSelect) return null;
            const selected = anagCountrySelect.options[anagCountrySelect.selectedIndex] || null;
            const selectedValue = selected ? String(selected.value || '').trim() : '';
            const label = selected ? (selected.text || '').trim() : '';
            const norm = (v) => String(v || '').trim().toLowerCase();
            return geoNazioniMap.find((row) => {
                return norm(row.id) === norm(selectedValue)
                    || norm(row.nome) === norm(label)
                    || norm(row.codice_iso2) === norm(selectedValue);
            }) || null;
        };

        const applyCittadinanzaValue = (value) => {
            const autoCit = String(value || '').trim();
            if (!anagCittadinanzaSelect || !autoCit) return;

            const currentCit = String(anagCittadinanzaSelect.value || '').trim();
            const canOverride = !currentCit || !lastAutoCittadinanza || currentCit.toLowerCase() === lastAutoCittadinanza.toLowerCase();
            if (!canOverride) {
                return;
            }

            ensureSelectValue(anagCittadinanzaSelect, autoCit, autoCit);
            lastAutoCittadinanza = autoCit;

            if (window.jQuery) {
                window.jQuery(anagCittadinanzaSelect).trigger('change');
            } else {
                anagCittadinanzaSelect.dispatchEvent(new Event('change', { bubbles: true }));
            }
        };

        const syncCittadinanzaFromCountry = () => {
            if (!anagCountrySelect || !anagCittadinanzaSelect) return;
            const selected = anagCountrySelect.options[anagCountrySelect.selectedIndex] || null;
            const label = selected ? (selected.text || '').trim() : '';
            const mapped = resolveAnagCountryMapping();
            const fallbackLabel = anagNationSelected.cittadinanza || anagNationSelected.label || label;
            const autoCit = (mapped && mapped.cittadinanza) ? mapped.cittadinanza : fallbackLabel;
            applyCittadinanzaValue(autoCit);
        };

        if (anagCountrySelect) {
            anagCountrySelect.addEventListener('change', syncCittadinanzaFromCountry);
            if (window.jQuery) {
                window.jQuery(anagCountrySelect).on('select2:select', (event) => {
                    const data = event && event.params ? (event.params.data || {}) : {};
                    anagNationSelected = {
                        id: data.id ? String(data.id) : '',
                        label: data.text || '',
                        cittadinanza: data.cittadinanza || '',
                    };
                    applyCittadinanzaValue(anagNationSelected.cittadinanza || anagNationSelected.label);
                    syncCittadinanzaFromCountry();
                });
            }
        }

        const bindComponenteCittadinanza = (row) => {
            if (!row) return;

            const geoRoot = row.querySelector('[data-ui="geo-italia"][data-prefix^="componente_anag_"]');
            const countrySelect = row.querySelector('[name$="[country_nac]"]');
            const cittadinanzaSelect = row.querySelector('[name$="[city_nac]"]');
            if (!geoRoot || !countrySelect || !cittadinanzaSelect) return;

            if (row.dataset.cittadinanzaBound === '1') {
                const currentCountryName = countrySelect.getAttribute('name') || '';
                const currentCittName = cittadinanzaSelect.getAttribute('name') || '';
                if (row.dataset.boundCountryName === currentCountryName && row.dataset.boundCittName === currentCittName) {
                    return;
                }
            }

            row.dataset.cittadinanzaBound = '1';
            row.dataset.boundCountryName = countrySelect.getAttribute('name') || '';
            row.dataset.boundCittName = cittadinanzaSelect.getAttribute('name') || '';

            const resolveMapping = () => {
                const selected = countrySelect.options[countrySelect.selectedIndex] || null;
                const selectedValue = selected ? String(selected.value || '').trim() : '';
                const label = selected ? String(selected.text || '').trim() : '';
                const norm = (v) => String(v || '').trim().toLowerCase();

                return geoNazioniMap.find((item) => {
                    return norm(item.id) === norm(selectedValue)
                        || norm(item.nome) === norm(label)
                        || norm(item.codice_iso2) === norm(selectedValue);
                }) || null;
            };

            const applyValue = (value) => {
                const autoCit = String(value || '').trim();
                if (!autoCit) return;

                const lastAuto = String(row.dataset.autoCittadinanza || '').trim();
                const current = String(cittadinanzaSelect.value || '').trim();
                const canOverride = !current || !lastAuto || current.toLowerCase() === lastAuto.toLowerCase();
                if (!canOverride) return;

                ensureSelectValue(cittadinanzaSelect, autoCit, autoCit);
                row.dataset.autoCittadinanza = autoCit;

                if (window.jQuery) {
                    window.jQuery(cittadinanzaSelect).trigger('change');
                } else {
                    cittadinanzaSelect.dispatchEvent(new Event('change', { bubbles: true }));
                }
            };

            const sync = () => {
                const selected = countrySelect.options[countrySelect.selectedIndex] || null;
                const label = selected ? String(selected.text || '').trim() : '';
                const mapped = resolveMapping();
                const autoCit = (mapped && mapped.cittadinanza) ? mapped.cittadinanza : label;
                applyValue(autoCit);
            };

            countrySelect.addEventListener('change', sync);

            if (window.jQuery) {
                window.jQuery(countrySelect).on('select2:select', (event) => {
                    const data = event && event.params ? (event.params.data || {}) : {};
                    const autoCit = data.cittadinanza || data.text || '';
                    applyValue(autoCit);
                    sync();
                });
            }

            geoRoot.addEventListener('geoselect:change', (event) => {
                const detail = event && event.detail ? event.detail : {};
                const nazione = detail.nazione || {};
                const norm = (v) => String(v || '').trim().toLowerCase();
                const mapped = geoNazioniMap.find((item) => {
                    return norm(item.id) === norm(nazione.id || nazione.value)
                        || norm(item.nome) === norm(nazione.label);
                });

                applyValue((mapped && mapped.cittadinanza) ? mapped.cittadinanza : (nazione.label || ''));
            });

            cittadinanzaSelect.addEventListener('change', () => {
                const current = String(cittadinanzaSelect.value || '').trim();
                const lastAuto = String(row.dataset.autoCittadinanza || '').trim();
                if (!current) {
                    row.dataset.autoCittadinanza = '';
                    return;
                }

                if (lastAuto && current.toLowerCase() !== lastAuto.toLowerCase()) {
                    row.dataset.autoCittadinanza = '';
                }
            });

            window.setTimeout(() => {
                const current = String(cittadinanzaSelect.value || '').trim();
                const lastAuto = String(row.dataset.autoCittadinanza || '').trim();
                if (!current || (lastAuto && current.toLowerCase() === lastAuto.toLowerCase())) {
                    sync();
                }
            }, 0);
        };

        if (anagGeo) {
            anagGeo.addEventListener('geoselect:change', (event) => {
                const detail = event && event.detail ? event.detail : {};
                const nazione = detail.nazione || {};
                const mapped = geoNazioniMap.find((row) => {
                    const norm = (v) => String(v || '').trim().toLowerCase();
                    return norm(row.id) === norm(nazione.id || nazione.value)
                        || norm(row.nome) === norm(nazione.label);
                });

                anagNationSelected = {
                    id: String(nazione.id || nazione.value || ''),
                    label: String(nazione.label || ''),
                    cittadinanza: String((mapped && mapped.cittadinanza) || nazione.label || ''),
                };

                applyCittadinanzaValue(anagNationSelected.cittadinanza);
            });
        }

        if (anagCittadinanzaSelect) {
            anagCittadinanzaSelect.addEventListener('change', () => {
                const currentCit = String(anagCittadinanzaSelect.value || '').trim();
                if (!currentCit) {
                    lastAutoCittadinanza = '';
                    return;
                }

                if (lastAutoCittadinanza && currentCit.toLowerCase() !== lastAutoCittadinanza.toLowerCase()) {
                    lastAutoCittadinanza = '';
                }
            });
        }

        if (anagCountrySelect && anagCittadinanzaSelect) {
            window.setTimeout(() => {
                const currentCit = String(anagCittadinanzaSelect.value || '').trim();
                if (!currentCit) {
                    syncCittadinanzaFromCountry();
                    return;
                }

                const mapped = resolveAnagCountryMapping();
                const autoCit = mapped?.cittadinanza || '';
                if (autoCit && currentCit.toLowerCase() === autoCit.toLowerCase()) {
                    syncCittadinanzaFromCountry();
                }
            }, 0);
        }

        const togglePublishedCity = () => {
            if (!publishedCountrySelect || !publishedCityWrap) return;
            const countryRaw = String(publishedCountrySelect.value || '').trim();
            const country = countryRaw
                .normalize('NFD')
                .replace(/[\u0300-\u036f]/g, '')
                .toLowerCase();
            const isItalia = country === 'italia' || country.includes('italia');

            publishedCityWrap.classList.toggle('d-none', !isItalia);
            if (!isItalia && publishedCitySelect) {
                publishedCitySelect.value = '';
                publishedCitySelect.dispatchEvent(new Event('change', { bubbles: true }));
            }
        };

        const bindComponenteCapBehavior = (row) => {
            if (!row || !window.jQuery) return;

            row.querySelectorAll('[data-ui="geo-italia"] [data-role="cap"]').forEach((capSelect) => {
                const currentName = capSelect.getAttribute('name') || '';
                if (capSelect.dataset.capBehaviorBound === '1' && capSelect.dataset.boundCapName === currentName) {
                    return;
                }

                capSelect.dataset.capBehaviorBound = '1';
                capSelect.dataset.boundCapName = currentName;

                const $cap = window.jQuery(capSelect);
                $cap.off('.schedinaCapFix');

                $cap.on('select2:open.schedinaCapFix', () => {
                    window.setTimeout(() => {
                        const inst = $cap.data('select2');
                        const $dropdown = inst?.$dropdown || inst?.dropdown?.$dropdown || null;
                        const container = capSelect.nextElementSibling;
                        if (!$dropdown || !container) return;

                        const rect = container.getBoundingClientRect();
                        $dropdown.css({
                            top: `${window.scrollY + rect.bottom}px`,
                            left: `${window.scrollX + rect.left}px`,
                            width: `${rect.width}px`,
                        });
                    }, 0);
                });
            });
        };

        if (publishedCountrySelect) {
            publishedCountrySelect.addEventListener('change', togglePublishedCity);
            publishedCountrySelect.addEventListener('input', togglePublishedCity);
            if (window.jQuery) {
                window.jQuery(publishedCountrySelect).on('change select2:select select2:clear select2:close', togglePublishedCity);
            }
        }

        if (searchInput && resultsContainer) {
            let debounceTimer = null;

            searchInput.addEventListener('input', function () {
                const query = (searchInput.value || '').trim();
                if (query.length <= 1) {
                    resultsContainer.style.display = 'none';
                    resultsContainer.innerHTML = '';
                    return;
                }

                clearTimeout(debounceTimer);
                debounceTimer = setTimeout(() => {
                    fetch(`/search_customers?query=${encodeURIComponent(query)}`)
                        .then(response => response.json())
                        .then(data => {
                            resultsContainer.innerHTML = '';
                            if (Array.isArray(data) && data.length > 0) {
                                resultsContainer.style.display = 'block';
                                data.forEach(item => {
                                    const li = document.createElement('li');
                                    li.classList.add('list-group-item', 'list-group-item-action');
                                    const labelCode = item.numero_cliente ? `${item.numero_cliente} - ` : '';
                                    li.textContent = `${labelCode}${item.surname || ''} ${item.name || ''}`.trim();
                                    li.addEventListener('mousedown', () => {
                                        applyCustomerDataToSchedina(item);
                                        resultsContainer.style.display = 'none';
                                    });
                                    resultsContainer.appendChild(li);
                                });
                            } else {
                                resultsContainer.style.display = 'none';
                            }
                        })
                        .catch(error => console.error('Errore durante la ricerca clienti:', error));
                }, 180);
            });

            searchInput.addEventListener('blur', function () {
                setTimeout(() => {
                    resultsContainer.style.display = 'none';
                }, 120);
            });

            resultsContainer.addEventListener('mousedown', (e) => {
                e.preventDefault();
            });
        }

        const camereContainer = document.getElementById('camere-container');
        const addCameraBtn = document.getElementById('add-camera-row');
        const camereSection = document.getElementById('camere-section');
        const camereToggle = document.getElementById('camere-toggle');
        const componentiSection = document.getElementById('componenti-section');
        const componentiContainer = document.getElementById('componenti-container');
        const addComponenteBtn = document.getElementById('add-componente-row');
        const saveModeInput = document.getElementById('save-mode');
        const saveModeIntentInput = document.getElementById('save-mode-intent');
        const activeTabInput = document.getElementById('active-tab');
        const saveComponentiBtn = document.getElementById('save-componenti-btn');

        const schedinaGroupScope = document.querySelector('[data-ui="customer-group-cascade"][data-scope="schedina"]');
        const syncSchedinaCustomerType = () => {
            const visibleType = document.getElementById('customer-type-housed');
            const hiddenType = document.querySelector('[name="customer_type_housed"]');
            if (visibleType && hiddenType && hiddenType !== visibleType) {
                hiddenType.value = visibleType.value || '';
                hiddenType.dispatchEvent(new Event('change', { bubbles: true }));
            }
        };

        if (customerTypeHousedInput) {
            customerTypeHousedInput.addEventListener('change', syncSchedinaCustomerType);
            syncSchedinaCustomerType();
        }

        const templateRow = () => {
            const index = camereContainer.querySelectorAll('.camera-row').length;
            const wrapper = document.createElement('div');
            wrapper.className = 'row g-2 align-items-end mb-2 camera-row';
            wrapper.innerHTML = `
                <div class="col-md-3">
                    <label class="form-label">Numero camera</label>
                    <input type="text" class="form-control" name="camere[${index}][numero_camera]">
                </div>
                <div class="col-md-2">
                    <label class="form-label">Posti letto</label>
                    <input type="number" class="form-control" name="camere[${index}][posti_letto]" min="0" max="20">
                </div>
                <div class="col-md-4">
                    <label class="form-label">Note</label>
                    <input type="text" class="form-control" name="camere[${index}][note]" maxlength="255">
                </div>
                <div class="col-md-2 d-flex align-items-end">
                    <button type="button" class="btn btn-outline-danger w-100 remove-camera">Rimuovi</button>
                </div>
            `;
            return wrapper;
        };

        const showCamere = () => {
            if (camereSection) {
                camereSection.classList.remove('d-none');
                camereSection.dataset.active = 'true';
            }
            if (camereToggle) {
                camereToggle.textContent = 'Disconnetti';
                camereToggle.setAttribute('aria-expanded', 'true');
            }
        };

        const hideCamere = () => {
            if (camereSection) {
                camereSection.classList.add('d-none');
                camereSection.dataset.active = 'false';
            }
            if (camereToggle) {
                camereToggle.textContent = 'Connetti gestionale';
                camereToggle.setAttribute('aria-expanded', 'false');
            }
        };

        if (camereToggle && camereSection) {
            camereToggle.addEventListener('click', () => {
                const isActive = camereSection.dataset.active === 'true';
                if (isActive) {
                    hideCamere();
                } else {
                    showCamere();
                }
            });
            if (camereSection.dataset.active === 'true') {
                showCamere();
            }
        }

        if (addCameraBtn && camereContainer) {
            addCameraBtn.addEventListener('click', () => {
                showCamere();
                camereContainer.appendChild(templateRow());
            });

            camereContainer.addEventListener('click', (event) => {
                if (event.target && event.target.classList.contains('remove-camera')) {
                    const row = event.target.closest('.camera-row');
                    if (row) {
                        row.remove();
                    }
                }
            });
        }

        const normalizeComponenteRow = (row, index) => {
            row.dataset.index = String(index);
            const title = row.querySelector('h6');
            if (title) {
                title.textContent = '';
            }

            row.querySelectorAll('[name]').forEach((field) => {
                const name = field.getAttribute('name');
                if (!name) return;
                field.setAttribute('name', name.replace(/componenti\[\d+\]/, `componenti[${index}]`));
            });

            const geoBlocks = Array.from(row.querySelectorAll('[data-ui="geo-italia"]'));
            geoBlocks.forEach((geo, geoIdx) => {
                const oldPrefix = geo.getAttribute('data-prefix') || '';
                const newPrefix = geoIdx === 0 ? `componente_anag_${index}` : `componente_res_${index}`;
                geo.setAttribute('data-prefix', newPrefix);
                geo.removeAttribute('data-init-geo-italia');

                if (oldPrefix) {
                    geo.querySelectorAll(`[id^="${oldPrefix}_"]`).forEach((node) => {
                        node.id = node.id.replace(`${oldPrefix}_`, `${newPrefix}_`);
                    });
                }
            });
        };

        const updateComponenteSummary = (row) => {
            const line = row.querySelector('.componente-summary-line');
            if (!line) return;
            const rowIndex = Number(row.dataset.index || 0) + 1;
            const surname = row.querySelector('[name$="[surname]"]')?.value?.trim() || '';
            const name = row.querySelector('[name$="[name]"]')?.value?.trim() || '';
            const exent = row.querySelector('[name$="[exent]"]')?.value?.trim() || '';
            const citySelect = row.querySelector('[name$="[city]"]');
            const cityResidenzaRaw = citySelect
                ? (citySelect.options?.[citySelect.selectedIndex]?.text || citySelect.value || '').trim()
                : '';
            const cityFromDataset = String(row.dataset.cityLabel || '').trim();
            const cityResidenzaLabel = cityResidenzaRaw && !/^\d+$/.test(cityResidenzaRaw)
                ? cityResidenzaRaw
                : cityFromDataset;
            if (cityResidenzaRaw && !/^\d+$/.test(cityResidenzaRaw)) {
                row.dataset.cityLabel = cityResidenzaRaw;
            }
            const dateNac = row.querySelector('[name$="[date_nac]"]')?.value?.trim() || '';

            const computeAge = (rawDate) => {
                if (!rawDate) return '';
                const parsed = new Date(rawDate);
                if (Number.isNaN(parsed.getTime())) return '';
                const today = new Date();
                let age = today.getFullYear() - parsed.getFullYear();
                const m = today.getMonth() - parsed.getMonth();
                if (m < 0 || (m === 0 && today.getDate() < parsed.getDate())) {
                    age -= 1;
                }
                if (age < 0) return '';
                return `${Math.max(1, age)}`;
            };
            const age = computeAge(dateNac);

            const main = `${rowIndex}. ${`${surname} ${name}`.trim() || 'Nuovo componente'}`;
            const tail = [
                cityResidenzaLabel ? `Città: ${cityResidenzaLabel}` : '',
                age ? `Età: ${age}` : '',
                exent ? `Esente: ${exent}` : '',
            ].filter(Boolean).join(' · ');
            line.textContent = tail ? `${main} · ${tail}` : main;
        };

        const clearComponenteRow = (row) => {
            row.dataset.cityLabel = '';
            row.querySelectorAll('input, select, textarea').forEach((field) => {
                if (field.tagName === 'SELECT') {
                    field.selectedIndex = 0;
                    field.value = '';
                    return;
                }
                field.value = '';
            });
        };

        const applyDefaultComponenteValues = (row) => {
            const exent = row.querySelector('[name$="[exent]"]');
            if (exent) {
                const optNo = Array.from(exent.options || []).find((opt) => String(opt.value).toUpperCase() === 'NO');
                if (optNo) {
                    exent.value = optNo.value;
                }
            }
        };

        const resetGeoSelectPlaceholders = (row) => {
            row.querySelectorAll('[data-ui="geo-italia"]').forEach((geoRoot) => {
                geoRoot.setAttribute('data-initial', JSON.stringify({ manual: false }));
            });
            row.querySelectorAll('select[data-geo="1"][data-role]').forEach((select) => {
                const placeholder = select.dataset.placeholder || select.getAttribute('placeholder') || '';
                select.innerHTML = '';
                select.appendChild(new Option(placeholder, '', false, false));
                select.value = '';
            });
            row.querySelectorAll('[data-role="manual-flag"]').forEach((flag) => {
                flag.value = '0';
            });
        };

        const cleanupEnhancedSelect = (row) => {
            row.querySelectorAll('[data-init-geo-italia]').forEach((el) => el.removeAttribute('data-init-geo-italia'));
            row.querySelectorAll('[data-init-select2]').forEach((el) => el.removeAttribute('data-init-select2'));
            row.querySelectorAll('[data-init-calendario]').forEach((el) => el.removeAttribute('data-init-calendario'));
            row.querySelectorAll('.select2-container').forEach((el) => el.remove());
            row.querySelectorAll('input.flatpickr-input:not([data-ui="calendario"])').forEach((el) => el.remove());
            row.querySelectorAll('input[data-ui="calendario"][data-provider="flatpickr"]').forEach((input) => {
                // El clon puede arrastrar la data del calendario previo.
                // Se limpia todo para que el nuevo componente arranque vacío.
                input.removeAttribute('data-init-calendario');
                input.setAttribute('data-default-date', '');
                input.setAttribute('data-deafult-date', '');
                input.value = '';
                while (input.nextElementSibling && input.nextElementSibling.tagName === 'INPUT' && !input.nextElementSibling.name) {
                    input.nextElementSibling.remove();
                }
            });
            row.querySelectorAll('input[data-ui="calendario"][data-provider="flatpickr"]').forEach((input) => {
                input.type = 'text';
                input.classList.remove('flatpickr-input');
                input.removeAttribute('readonly');
                input.removeAttribute('aria-haspopup');
                input.removeAttribute('aria-expanded');
            });
            row.querySelectorAll('select.select2-hidden-accessible').forEach((select) => {
                select.classList.remove('select2-hidden-accessible');
                select.removeAttribute('data-select2-id');
                select.removeAttribute('aria-hidden');
                select.removeAttribute('tabindex');
            });
            row.querySelectorAll('option').forEach((opt) => {
                opt.removeAttribute('data-select2-id');
            });
        };

        const refreshRowWidgets = (row) => {
            if (!row) return;
            // Re-inizializza solo select2 NON-GEO della riga (es. cittadinanza),
            // per non alterare il comportamento del componente GEO.
            row.querySelectorAll('select[data-ui="select-search"]:not([data-geo="1"])').forEach((select) => {
                if (select.closest('[data-ui="geo-italia"]')) return;
                // Evita re-init aggressivo del "Tipo Via" che genera comportamento erratico.
                if (/\[typeaway\]$/.test(select.name || '')) return;
                if (window.jQuery && window.jQuery(select).data('select2')) {
                    window.jQuery(select).select2('destroy');
                }
                select.classList.remove('select2-hidden-accessible');
                select.removeAttribute('data-select2-id');
                select.removeAttribute('aria-hidden');
                select.removeAttribute('tabindex');
                select.removeAttribute('data-init-select2');
            });

            // Se un GEO è già stato inizializzato, forziamo solo il rebind del suo modulo.
            row.querySelectorAll('[data-ui="geo-italia"]').forEach((geoRoot) => {
                geoRoot.removeAttribute('data-init-geo-italia');
            });

            // Re-inizializza flatpickr della sola riga per uniformare il comportamento
            row.querySelectorAll('input[data-ui="calendario"][data-provider="flatpickr"]').forEach((input) => {
                if (input._flatpickr) {
                    input._flatpickr.destroy();
                }
                input.removeAttribute('data-init-calendario');
            });

            if (window.UI && typeof window.UI.init === 'function') {
                window.UI.init(row);
            }

            bindComponenteCittadinanza(row);
            bindComponenteCapBehavior(row);
        };

        const reindexComponentiRows = () => {
            if (!componentiContainer) return;
            const rows = Array.from(componentiContainer.querySelectorAll('.componente-row'));
            rows.forEach((row, idx) => normalizeComponenteRow(row, idx));
        };

        const rowHasData = (row) => {
            // Considera "compilato" solo se hay datos principales.
            // No usar GEO defaults (es. ITALIA) para decidir si crear una nueva fila.
            const keys = ['[name]', '[surname]', '[sex]', '[relationship]'];
            return keys.some((suffix) => {
                const field = row.querySelector(`[name$="${suffix}"]`);
                const value = String(field?.value || '').trim();
                return value !== '';
            });
        };

        const hasOpenComponenteDetails = () => {
            if (!componentiContainer) return false;
            return Array.from(componentiContainer.querySelectorAll('.componente-row'))
                .some((row) => {
                    if (row.classList.contains('d-none')) return false;
                    const details = row.querySelector('.componente-details');
                    return details && !details.classList.contains('d-none');
                });
        };

        const closeAllComponenteDetails = (exceptRow = null) => {
            if (!componentiContainer) return;
            Array.from(componentiContainer.querySelectorAll('.componente-row')).forEach((row) => {
                if (exceptRow && row === exceptRow) return;
                const details = row.querySelector('.componente-details');
                if (details) details.classList.add('d-none');
            });
        };

        const refreshAddComponenteVisibility = () => {
            if (!addComponenteBtn || !componentiContainer) return;
            const hasOpenDetails = hasOpenComponenteDetails();
            addComponenteBtn.classList.toggle('d-none', hasOpenDetails);
            if (saveComponentiBtn) {
                saveComponentiBtn.classList.toggle('d-none', !hasOpenDetails);
            }
        };

        document.querySelectorAll('[data-bs-toggle="pill"]').forEach((tabBtn) => {
            tabBtn.addEventListener('shown.bs.tab', () => {
                if (activeTabInput && tabBtn.id) {
                    activeTabInput.value = tabBtn.id;
                }
            });
        });

        if (saveComponentiBtn) {
            saveComponentiBtn.addEventListener('click', () => {
                if (saveModeInput) saveModeInput.value = 'componenti';
                if (saveModeIntentInput) saveModeIntentInput.value = 'componenti';
                if (activeTabInput) activeTabInput.value = 'schedina-step-comp';
            });
        }

        const schedinaForm = document.querySelector('form.form-steps');
        if (schedinaForm) {
            schedinaForm.querySelectorAll('button[type="submit"][name="save_mode"]').forEach((button) => {
                button.addEventListener('click', () => {
                    const mode = button.value || 'full';
                    if (saveModeInput) saveModeInput.value = mode;
                    if (saveModeIntentInput) saveModeIntentInput.value = mode;
                });
            });

            schedinaForm.addEventListener('submit', (event) => {
                const submitter = event.submitter;
                if (submitter?.id === 'save-componenti-btn') {
                    if (saveModeInput) saveModeInput.value = 'componenti';
                    if (saveModeIntentInput) saveModeIntentInput.value = 'componenti';
                } else {
                    const mode = submitter?.getAttribute('name') === 'save_mode'
                        ? (submitter?.getAttribute('value') || 'full')
                        : 'full';
                    if (saveModeInput) saveModeInput.value = mode;
                    if (saveModeIntentInput) saveModeIntentInput.value = mode;
                }

                if (activeTabInput) {
                    const activePane = document.querySelector('.tab-pane.active.show[id]');
                    if (activePane?.id) {
                        activeTabInput.value = activePane.id.replace('-pane', '');
                    }
                }
            });
        }

        if (componentiContainer) {
            componentiContainer.addEventListener('click', (event) => {
                const btn = event.target.closest('.remove-componente');
                const toggleBtn = event.target.closest('.toggle-componente-details');
                if (toggleBtn) {
                    const row = toggleBtn.closest('.componente-row');
                    const details = row ? row.querySelector('.componente-details') : null;
                    if (!details) return;
                    const isOpening = details.classList.contains('d-none');
                    closeAllComponenteDetails(row);
                    details.classList.toggle('d-none', !isOpening);
                    if (isOpening) {
                        refreshRowWidgets(row);
                    }
                    refreshAddComponenteVisibility();
                    return;
                }
                if (!btn) return;

                const rows = Array.from(componentiContainer.querySelectorAll('.componente-row'));
                const row = btn.closest('.componente-row');
                if (!row) return;

                if (rows.length <= 1) {
                    clearComponenteRow(row);
                    refreshAddComponenteVisibility();
                    return;
                }

                row.remove();
                reindexComponentiRows();
                refreshAddComponenteVisibility();
            });

            componentiContainer.addEventListener('input', (event) => {
                const row = event.target.closest('.componente-row');
                if (!row) return;
                updateComponenteSummary(row);
            });
            componentiContainer.addEventListener('change', (event) => {
                const row = event.target.closest('.componente-row');
                if (!row) return;
                updateComponenteSummary(row);
            });
        }

        if (addComponenteBtn && componentiContainer) {
            addComponenteBtn.dataset.bound = addComponenteBtn.dataset.bound || '0';
            if (addComponenteBtn.dataset.bound !== '1') {
                addComponenteBtn.dataset.bound = '1';

                addComponenteBtn.addEventListener('click', () => {
                    if (addComponenteBtn.dataset.busy === '1') return;
                    if (hasOpenComponenteDetails()) return;
                    addComponenteBtn.dataset.busy = '1';
                    setTimeout(() => {
                        addComponenteBtn.dataset.busy = '0';
                    }, 0);

                    const rows = Array.from(componentiContainer.querySelectorAll('.componente-row'));
                    if (!rows.length) return;

                    if (addComponenteBtn) addComponenteBtn.classList.add('d-none');

                    if (rows.length === 1 && !rowHasData(rows[0])) {
                        rows[0].classList.remove('d-none');
                        closeAllComponenteDetails(rows[0]);
                        clearComponenteRow(rows[0]);
                        resetGeoSelectPlaceholders(rows[0]);
                        applyDefaultComponenteValues(rows[0]);
                        const details = rows[0].querySelector('.componente-details');
                        if (details) details.classList.remove('d-none');
                        refreshRowWidgets(rows[0]);
                        reindexComponentiRows();
                        updateComponenteSummary(rows[0]);
                        refreshAddComponenteVisibility();
                        return;
                    }

                    const clone = rows[rows.length - 1].cloneNode(true);
                    cleanupEnhancedSelect(clone);
                    clearComponenteRow(clone);
                    resetGeoSelectPlaceholders(clone);
                    applyDefaultComponenteValues(clone);
                    closeAllComponenteDetails();
                    const details = clone.querySelector('.componente-details');
                    if (details) details.classList.remove('d-none');

                    componentiContainer.appendChild(clone);
                    reindexComponentiRows();
                    updateComponenteSummary(clone);
                    refreshAddComponenteVisibility();

                    refreshRowWidgets(clone);
                });
            }
        }

        const schedinaConsentSwitches = Array.from(document.querySelectorAll('.js-schedina-consent-switch'));
        const schedinaNowTimestamp = () => {
            const d = new Date();
            const pad = (v) => String(v).padStart(2, '0');
            return `${d.getFullYear()}-${pad(d.getMonth() + 1)}-${pad(d.getDate())} ${pad(d.getHours())}:${pad(d.getMinutes())}:${pad(d.getSeconds())}`;
        };

        schedinaConsentSwitches.forEach((switchEl) => {
            const targetName = switchEl.dataset.timestampTarget;
            const timestampInput = targetName ? document.querySelector(`input[name="${targetName}"]`) : null;
            if (!timestampInput) return;

            if (switchEl.checked && !timestampInput.value) {
                timestampInput.value = schedinaNowTimestamp();
            }

            switchEl.addEventListener('change', () => {
                if (switchEl.checked) {
                    if (!timestampInput.value) {
                        timestampInput.value = schedinaNowTimestamp();
                    }
                } else {
                    timestampInput.value = '';
                }
            });
        });

        syncCittadinanzaFromCountry();
        togglePublishedCity();
        reindexComponentiRows();
        Array.from((componentiContainer || document).querySelectorAll('.componente-row')).forEach(bindComponenteCittadinanza);
        Array.from((componentiContainer || document).querySelectorAll('.componente-row')).forEach(bindComponenteCapBehavior);
        Array.from((componentiContainer || document).querySelectorAll('.componente-row')).forEach(updateComponenteSummary);
        refreshAddComponenteVisibility();

        if (activeTabInput && activeTabInput.value && activeTabInput.value !== 'schedina-step-base') {
            activateTab(activeTabInput.value);
        }
    });
</script>
