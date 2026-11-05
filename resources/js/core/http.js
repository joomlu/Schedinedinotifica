import axios from 'axios';

const http = axios.create({
    headers: {
        'X-Requested-With': 'XMLHttpRequest',
    },
});

const token = typeof document !== 'undefined' ? document.querySelector('meta[name="csrf-token"]') : null;
if (token) {
    http.defaults.headers.common['X-CSRF-TOKEN'] = token.getAttribute('content');
}

if (typeof window !== 'undefined') {
    window.http = http;
}

export default http;
