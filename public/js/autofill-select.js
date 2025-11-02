// Clase que gestiona el auto-relleno de un select
class AutoFillSelect {
    /**
     * @param {HTMLElement} element - El elemento select.
     * @param {Object} config - Configuración con las siguientes propiedades:
     *    url: URL del servicio.
     *    placeholder: Texto a mostrar como placeholder.
     *    valueField: Nombre del campo del objeto JSON que se usará como value.
     *    textField: Nombre del campo del objeto JSON que se usará como texto.
     *    type: Tipo de datos (opcional, para debug).
     */
    constructor(element, config) {
      this.$element = $(element);
      this.config = config;
      this.init();
    }
  
    init() {
      // Helper: da ISO2 a emoji bandiera
      function countryCodeToEmoji(code) {
        if (!code || typeof code !== 'string') return '';
        const cc = code.trim().toUpperCase();
        if (cc.length !== 2) return '';
        const A = 0x1F1E6;
        const base = 'A'.charCodeAt(0);
        const chars = [cc.charCodeAt(0) - base + A, cc.charCodeAt(1) - base + A];
        return String.fromCodePoint(chars[0], chars[1]);
      }

      // Distruggi Select2 precedente se già inizializzato (evita conflitti con init globale)
      if (this.$element.data('select2')) {
        try { this.$element.select2('destroy'); } catch (e) {}
      }

      const self = this;
      const select2Options = {
        placeholder: this.config.placeholder,
        allowClear: true,
        width: '100%',
        language: 'it',
        minimumInputLength: 0,
        ajax: {
          transport: function (params, success, failure) {
            const client = (window.http && typeof window.http.get === 'function') ? window.http : axios;
            // Passa query 'q' e anche eventuali parametri correlati (es. provincia selezionata)
            const extraParams = {};
            if (self.config.dependentParam && typeof self.config.dependentParam === 'function') {
              Object.assign(extraParams, self.config.dependentParam.call(self.$element));
            }
            client.get(self.config.url, { params: Object.assign({ q: params.data.term || '' }, extraParams) })
              .then(resp => success(resp))
              .catch(err => failure(err));
          },
          processResults: (data) => {
            const items = Array.isArray(data.data) ? data.data : data; // axios wrapper può incapsulare in data
            return {
              results: items.map((item) => {
                const result = {
                  id: item[this.config.valueField],
                  text: item[this.config.textField],
                  selected: false
                };
                // Aggiungi emoji bandiera per nazioni se disponibile
                if (this.config.type === 'countries' && item.sigla_nazione) {
                  result.flagEmoji = countryCodeToEmoji(item.sigla_nazione);
                }
                return result;
              })
            };
          },
          delay: 150
        }
      };

      // Templating: mostra emoji bandiera accanto al testo per nazioni
      if (this.config.type === 'countries') {
        const formatCountry = function (state) {
          if (!state.id) return state.text;
          const flag = state.flagEmoji ? (state.flagEmoji + ' ') : '';
          return $('<span>' + flag + state.text + '</span>');
        };
        select2Options.templateResult = formatCountry;
        select2Options.templateSelection = formatCountry;
      }

      this.$element.select2(select2Options);
    }
  
    fetchData() {
      // Usa wrapper HTTP standard (Axios con CSRF) se disponibile
      const client = (window.http && typeof window.http.get === 'function') ? window.http : axios;
      client.get(this.config.url)
        .then((response) => {
          const data = response.data || [];
          this.$element.empty().append(`<option value="">${this.config.placeholder}</option>`);
          data.forEach((item) => {
            this.$element.append($('<option>', {
              value: item[this.config.valueField],
              text: item[this.config.textField]
            }));
          });
          // Aggiorna select2
          this.$element.trigger('change');
        })
        .catch((error) => {
          console.error(`Errore nel recupero di ${this.config.type}:`, error);
        });
    }
  }
  
  // Clase "manager" para obtener la configuración según el tipo de autollenado
  class AutoFillSelectManager {
    /**
     * Devuelve la configuración según el tipo.
     * @param {string} type - "countries", "regions", "provinces" o "cities"
     * @returns {Object|null} Configuración o null si el tipo no está definido.
     */
    static getConfigForType(type) {
      switch (type) {
        case 'countries':
          return {
            url: '/nations', // Endpoint disponibile
            placeholder: 'Seleziona una Nazione',
            valueField: 'denominazione_cittadinanza',
            textField: 'denominazione_cittadinanza',
            type: type
          };
        case 'regions':
          return {
            url: '/regions', // Endpoint per regioni
            placeholder: 'Seleziona una Regione',
            valueField: 'codice_regione',
            textField: 'denominazione_regione',
            type: type
          };
        case 'provinces':
          return {
            url: '/provinces-all', // Endpoint per province
            placeholder: 'Seleziona una Provincia',
            valueField: 'sigla_provincia',
            textField: 'denominazione_provincia',
            type: type
          };
        case 'cities':
          return {
            url: '/cities-by-province', // Endpoint per città (gi_comuni.json)
            placeholder: 'Seleziona una Città',
            valueField: 'codice_istat',
            textField: 'denominazione_ita',
            // dipende dalla provincia selezionata in prossimità (cerchiamo select con data-autofill="provinces" nello stesso form)
            dependentParam: function(){
              const $container = $(this).closest('form');
              const $prov = $container.find('select[data-autofill="provinces"]').first();
              const val = $prov.val() || '';
              return { sigla_provincia: val };
            },
            type: type
          };
        default:
          return null;
      }
    }
  }
  
  // Al cargar el documento, inicializa la funcionalidad en cada select que tenga la clase "autofill-select"
  $(document).ready(function () {
    $('.autofill-select').each(function () {
      let type = $(this).data('autofill'); // Por ejemplo: "countries", "regions", "provinces" o "cities"
      let config = AutoFillSelectManager.getConfigForType(type);
      if (config) {
        new AutoFillSelect(this, config);
      }
    });

    // Quando cambia la provincia, azzera e ricarica la città dipendente
    $(document).on('change', 'select[data-autofill="provinces"]', function(){
      const $form = $(this).closest('form');
      const $city = $form.find('select[data-autofill="cities"]').first();
      if ($city.length) {
        $city.val(null).trigger('change');
      }
    });
  });
  