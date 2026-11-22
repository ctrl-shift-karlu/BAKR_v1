/* BAKR — Scholar login (scholarLogin): demo-account check, run by bakr-staff-login.js before it
   opens the dashboard. DEMO ACCOUNTS (sample data only): the scholar dashboard shows the scholar
   whose email is saved in "scholarSession" (sessionStorage, or localStorage with "Keep me signed in"). */
window.bakrLoginCheck = (email, password, remember) => {
  const DEMO_ACCOUNTS = [
    { email: 'karlangelo.gabaes@gmail.com', password: 'Gabaes@2026' }, // Karl Angelo T. Gabaes, CHED Merit (Half)
    { email: 'rochelle.abella@gmail.com', password: 'Abella@2026' },  // Rochelle D. Abella, BPMSP
  ];
  const match = DEMO_ACCOUNTS.find((a) => a.email === email && a.password === password);
  if (!match) return 'Incorrect email or password.';
  try {
    sessionStorage.removeItem('scholarSession');
    localStorage.removeItem('scholarSession');
    (remember ? localStorage : sessionStorage).setItem('scholarSession', match.email);
  } catch (err) { /* storage blocked: dashboard falls back to its default scholar */ }
  return '';
};
