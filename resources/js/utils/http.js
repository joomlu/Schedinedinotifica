/*
  Simple Axios wrapper with CSRF, base config, and basic error logging.
  This file is copied to public/build/js/utils/http.js by Vite's copy step.
*/
(function () {
  if (typeof axios === 'undefined') {
    console.warn('Axios is not available on window. Please ensure axios is loaded.');
    return;
  }

  const token = document.querySelector('meta[name="csrf-token"]');
  const csrfToken = token ? token.getAttribute('content') : '';

  const instance = axios.create({
    headers: {
      'X-Requested-With': 'XMLHttpRequest',
      'X-CSRF-TOKEN': csrfToken,
      'Accept': 'application/json'
    },
    withCredentials: true
  });

  // Basic interceptors for logging; extend as needed
  instance.interceptors.response.use(
    (response) => response,
    (error) => {
      console.error('HTTP error:', error?.response?.status, error?.response?.data || error.message);
      return Promise.reject(error);
    }
  );

  window.http = instance;
})();
