import { test, expect } from '@playwright/test';

test('test', async ({ page }) => {
  await page.goto('http://127.0.0.1:8000/');
  await page.getByRole('banner').getByRole('link', { name: 'Sou Cliente' }).click();
  await page.getByRole('button', { name: 'Tech3D Soluções Ltda tech3d@' }).click();
  await page.locator('[data-test="login-client-button"]').click();
  await page.locator('#inertia-error-dialog').click();
  await page.locator('#inertia-error-dialog').click();
  await page.locator('#inertia-error-dialog').click();
  await page.locator('#inertia-error-dialog').click();
  await page.locator('#inertia-error-dialog').click();
  await page.locator('#inertia-error-dialog').click();
  await page.getByRole('link', { name: 'Sou Transportadora' }).click();
  await page.getByRole('button', { name: 'Transportadora Rapidez Ltda' }).click();
  await page.getByRole('button', { name: 'Entrar como Transportadora' }).click();
});