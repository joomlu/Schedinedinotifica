import { test, expect, BASE_URL } from '../Support/playwright.js';

for (const width of [1440, 768, 390]) {
  test(`Sessione e logout con route in cache a ${width}px`, async ({ page, context }) => {
    await page.setViewportSize({ width, height: 950 });
    const fixture = await (await page.request.get(`${BASE_URL}/operative-session-fixture.json`)).json();
    for (const method of ['GET', 'POST']) {
      await context.clearCookies();
      await page.goto(`${BASE_URL}/login`);
      await expect(page.getByLabel(/Nome di accesso o email/i)).toBeVisible();
      const csrf = await page.locator('input[name="_token"]').first().inputValue();
      const login = await page.request.post(`${BASE_URL}/login`, {
        form: { _token: csrf, login: fixture.email, password: fixture.password }, maxRedirects: 0,
      });
      expect(login.status()).toBe(302);
      expect((await page.goto(`${BASE_URL}/gestione-operativa`)).status()).toBe(200);
      if (method === 'GET') {
        await page.goto(`${BASE_URL}/logout`);
        await expect(page).toHaveURL(`${BASE_URL}/login`);
      } else {
        const token = await page.locator('input[name="_token"]').first().inputValue();
        const logout = await page.request.post(`${BASE_URL}/logout`, { form: { _token: token }, maxRedirects: 0 });
        expect(logout.status()).toBe(302);
        expect(new URL(logout.headers().location, BASE_URL).pathname).toBe('/');
      }
      const denied = await page.request.get(`${BASE_URL}/gestione-operativa`, { maxRedirects: 0 });
      expect(denied.status()).toBe(302);
      expect(new URL(denied.headers().location, BASE_URL).pathname).toBe('/login');
    }
  });
}
