// constants/config.js
const isLocal = location.hostname === 'localhost' || location.hostname === '127.0.0.1';

export const CONFIG = isLocal
  ? {
    API_BASE_URL: 'http://localhost:8000/api',
    RECAPTCHA_SITE_KEY: '6LfxOYQrAAAAAEHV2_iJ8GmQWqAzjpn-Rg3nGEUF',
  }
  : {
    API_BASE_URL: 'https://mi-dominio.com/api',
    RECAPTCHA_SITE_KEY: '6LfxOYQrAAAAAEHV2_iJ8GmQWqAzjpn-Rg3nGEUF',
  };
