document.addEventListener('DOMContentLoaded', function () {
  // Inizializza il range e imposta defaultDate da valori esistenti
  var inputRange = document.getElementById('stay-range-schedina-edit');
  if (inputRange) {
    var arrive = document.getElementById('schedina-edit-arrive-hidden').value;
    var departure = document.getElementById('schedina-edit-departure-hidden').value;
    var defaults = [];
    if (arrive) defaults.push(new Date(arrive));
    if (departure) defaults.push(new Date(departure));
    var fp = flatpickr(inputRange, {
      mode: 'range',
      dateFormat: inputRange.getAttribute('data-date-format') || 'd M, Y',
      locale: 'it',
      defaultDate: defaults,
    });
    function formatYMD(date) {
      const y = date.getFullYear();
      const m = String(date.getMonth() + 1).padStart(2, '0');
      const d = String(date.getDate()).padStart(2, '0');
      return `${y}-${m}-${d}`;
    }
    inputRange.addEventListener('change', function () {
      var start = fp && fp.selectedDates[0] ? formatYMD(fp.selectedDates[0]) : '';
      var end = fp && fp.selectedDates[1] ? formatYMD(fp.selectedDates[1]) : '';
      document.getElementById('schedina-edit-arrive-hidden').value = start;
      document.getElementById('schedina-edit-departure-hidden').value = end;
    });
  }
  const searchInput = document.getElementById('search');
  const surnameInput = document.getElementById('surname');
  const resultsContainer = document.getElementById('results');

  if (searchInput && surnameInput && resultsContainer) {
    searchInput.addEventListener('input', function () {
      const query = searchInput.value;

      if (query.length > 2) {
        // Buscar después de 3 caracteres
        fetch(`/search_customers?query=${encodeURIComponent(query)}`)
          .then((response) => response.json())
          .then((data) => {
            resultsContainer.innerHTML = '';
            if (data.length > 0) {
              resultsContainer.style.display = 'block';
              data.forEach((item) => {
                const li = document.createElement('li');
                li.classList.add('list-group-item');
                li.textContent = item.name + ' ' + item.surname; // Cambiar a lo que quieras mostrar
                li.addEventListener('mousedown', () => {
                  // Usar mousedown para evitar el blur prematuro
                  searchInput.value = item.name;
                  surnameInput.value = item.surname || ''; // Agrega el surname
                  resultsContainer.style.display = 'none';
                });
                resultsContainer.appendChild(li);
              });
            } else {
              resultsContainer.style.display = 'none';
            }
          })
          .catch((error) => console.error('Error en la búsqueda:', error));
      } else {
        resultsContainer.style.display = 'none';
      }
    });

    // Ocultar la lista cuando se pierde el foco del input
    searchInput.addEventListener('blur', function () {
      // Usar un pequeño retraso para que el evento mousedown se registre primero
      setTimeout(() => {
        resultsContainer.style.display = 'none';
      }, 100);
    });

    // Opcional: evitar que la lista desaparezca si el mouse está sobre ella
    resultsContainer.addEventListener('mousedown', (e) => {
      e.preventDefault(); // Previene que el blur se active al hacer clic en la lista
    });
  }

  // Inizializza GeoSelect per i campi di residenza (or_*) con filtri CAP e aggiorna il riepilogo/indirizzo inline
  if (window.GeoSelect) {
    try {
      // Funzione per renderizzare riepilogo e riga indirizzo
      function renderEditSummary(v) {
        var summaryEl = document.getElementById('or_geo_summary');
        var inlineEl = document.getElementById('or_address_inline');
        var typeaway = (document.getElementById('or_typeaway') && document.getElementById('or_typeaway').value || '').trim();
        var address = (document.getElementById('or_address') && document.getElementById('or_address').value || '').trim();
        var num = (document.getElementById('or_num') && document.getElementById('or_num').value || '').trim();
        var internal = (document.getElementById('or_internal') && document.getElementById('or_internal').value || '').trim();
        if (summaryEl) {
          summaryEl.innerHTML = `
                        <li>Stato: ${v.nation.label || '—'}</li>
                        <li>Regione: ${v.region.label || '—'}</li>
                        <li>Provincia, Città, CAP: ${v.province.label || '—'}, ${v.city.label || '—'}, ${v.cap.label || '—'}</li>
                        <li>Modalità: ${v.manualMode ? 'Inserimento manuale (non Italia)' : 'Selettori Italia'}</li>
                        <li>Tipo Via: ${typeaway || '—'}</li>
                        <li>Strada: ${address || '—'}</li>
                        <li>Num.: ${num || '—'}</li>
                        <li>Int.: ${internal || '—'}</li>
                    `;
        }
        // Riga indirizzo compatta sotto CAP
        var capLabel = v && v.cap ? v.cap.label || v.cap.value || '' : '';
        var partsLeft = [];
        var mainLeft = [typeaway, address].filter(Boolean).join(' ');
        if (mainLeft) partsLeft.push(mainLeft);
        if (num) partsLeft.push('Num. ' + num);
        if (internal) partsLeft.push('Int. ' + internal);
        var left = partsLeft.join(' · ');
        var right = capLabel ? 'CAP ' + capLabel : '';
        var line = [left, right].filter(Boolean).join(' — ');
        if (inlineEl) {
          inlineEl.textContent = line ? 'Indirizzo: ' + line : '';
        }
      }

      var geoSelect = new GeoSelect({
        nation: '#or_country',
        region: '#or_region',
        province: '#or_prov',
        city: '#or_city',
        cap: '#or_cap',
        // Flag opt-in richiesti
        filterCapByCity: true,
        autoSelectUniqueCap: true,
        backfillRegionFromProvince: true,
        // Modalità manuale per nazioni diverse da IT (default: true)
        manualForNonItaly: true,
        onChange: function (vals) {
          var manual = !!(vals && vals.manualMode);
          // Abilita i campi manuali solo in manual mode; disabilita altrimenti per evitare doppie submission
          ['#or_region_manual', '#or_prov_manual', '#or_city_manual', '#or_cap_manual'].forEach(function (sel) {
            var el = document.querySelector(sel);
            if (el) el.disabled = !manual;
          });
          renderEditSummary(vals);
        },
      });
      // Assicurati che i manual siano disabilitati al load
      ['#or_region_manual', '#or_prov_manual', '#or_city_manual', '#or_cap_manual'].forEach(function (sel) {
        var el = document.querySelector(sel);
        if (el) el.disabled = true;
      });
      // Render iniziale riepilogo
      renderEditSummary(geoSelect.getValues());
      // Aggiorna su input dei campi indirizzo o CAP manuale
      ['or_typeaway', 'or_address', 'or_num', 'or_internal', 'or_cap_manual'].forEach(function (id) {
        var el = document.getElementById(id);
        if (el) {
          el.addEventListener('input', function () {
            renderEditSummary(geoSelect.getValues());
          });
        }
      });
    } catch (e) {
      console.error('GeoSelect init error', e);
    }
  }

  // Vincoli documento centralizzati in resources/js/app.js tramite data-date-pair/doc
});
