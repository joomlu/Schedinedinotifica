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
      // Inicializa Select2 para tener búsqueda autoincremental
      this.$element.select2({
        placeholder: this.config.placeholder,
        allowClear: true,
        width: '100%'
      });
  
      // Al enfocarse, si aún no se han cargado las opciones, se hace la petición AJAX
      this.$element.on('focus', () => {
        if (this.$element.find('option').length <= 1) {
          this.fetchData();
        }
      });
    }
  
    fetchData() {
      $.ajax({
        url: this.config.url,
        type: 'GET',
        success: (data) => {
          this.$element.empty().append(`<option value="">${this.config.placeholder}</option>`);
          $.each(data, (i, item) => {
            this.$element.append($('<option>', {
              value: item[this.config.valueField],
              text: item[this.config.textField]
            }));
          });
          // Actualiza el select2
          this.$element.trigger('change');
        },
        error: (xhr, status, error) => {
          console.error(`Error fetching ${this.config.type}:`, status, error);
        }
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
            url: '/nations', // Asegúrate de tener definido este endpoint
            placeholder: 'Seleccione una Nación',
            valueField: 'denominazione_cittadinanza',
            textField: 'denominazione_cittadinanza',
            type: type
          };
        case 'regions':
          return {
            url: '/regions', // Endpoint para regiones
            placeholder: 'Seleccione una Región',
            valueField: 'codice_regione',
            textField: 'denominazione_regione',
            type: type
          };
        case 'provinces':
          return {
            url: '/provinces-all', // Endpoint para provincias
            placeholder: 'Seleccione una Provincia',
            valueField: 'sigla_provincia',
            textField: 'denominazione_provincia',
            type: type
          };
        case 'cities':
          return {
            url: '/cities-by-province', // Endpoint para ciudades (gi_comuni.json)
            placeholder: 'Seleccione una Ciudad',
            valueField: 'codice_istat',
            textField: 'denominazione_ita',
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
  });
  