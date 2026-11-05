import { test, expect, BASE_URL } from '../Support/playwright.js';

for (const [name, body] of [['JSON senza ricevuta', '{}'], ['JSON negativo', '{"ok":false,"lead_id":12}'], ['risposta non JSON', '<html>Errore intermediario</html>']]) {
  test(`conferma contatto: HTTP200 con ${name} non simula un salvataggio`, async ({ page }) => {
    await page.setViewportSize({ width: 390, height: 1000 });
    await page.goto(`${BASE_URL}/contatti#demo`);
    await page.getByLabel('Struttura *', { exact: true }).fill('Hotel Esito Sintetico');
    await page.getByLabel('Nome e cognome *', { exact: true }).fill('Referente Sintetico');
    await page.getByLabel('Email *', { exact: true }).fill('esito-sintetico@example.invalid');
    await page.getByLabel('Messaggio *', { exact: true }).fill('Richiesta simulata senza scrittura.');
    await page.getByLabel('Autorizzo il contatto commerciale').check();
    await page.route('**/contatti/richiesta', route => route.fulfill({ status: 200, contentType: 'application/json', body }));
    await page.getByRole('button', { name: 'Invia la richiesta', exact: true }).click();
    await expect(page.locator('.form-status')).toContainText('La conferma di registrazione non è valida');
    await expect(page.getByRole('dialog')).not.toBeVisible();
    await expect(page.getByLabel('Email *', { exact: true })).toHaveValue('esito-sintetico@example.invalid');
    await expect(page.getByRole('button', { name: 'Invia la richiesta', exact: true })).toBeEnabled();
  });
}
