// Adresa Laravel API-ja.
// Lokalno (localhost) koristi se lokalni backend, a na internetu Render adresa.
// !!! Posle deploya backenda na Renderu zameni ADRESA ispod svojom Render adresom !!!
const RENDER_API = 'https://ZAMENI-ME.onrender.com/api';

const isLocal = typeof location !== 'undefined' &&
  (location.hostname === 'localhost' || location.hostname === '127.0.0.1');

export const API_URL = isLocal ? 'http://localhost:8000/api' : RENDER_API;
