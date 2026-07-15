import { test, expect } from '@playwright/test';

test('test', async ({ page }) => {
  await page.goto('http://127.0.0.1:8000/');
  await page.locator('div').filter({ hasText: /^Quero saber mais$/ }).click();
  await page.getByRole('link', { name: 'Sou Cliente' }).click();
  await page.getByRole('button', { name: 'Tech3D Soluções Ltda tech3d@' }).click();
  await page.getByRole('button', { name: 'Tech3D Soluções Ltda tech3d@' }).click();
  await page.locator('[data-test="login-client-button"]').click();
  await page.getByRole('img', { name: 'Engrenagem para protótipo' }).first().click();
  await page.getByRole('link', { name: 'Ver mais detalhes' }).click();
  await page.getByRole('button', { name: 'Adicionar ao carrinho' }).click();
  await page.getByRole('banner').getByRole('link').filter({ hasText: /^$/ }).click();
  await page.getByRole('button', { name: 'Finalizar Compra' }).click();
  await page.locator('.flex.h-5').click();
  await page.getByRole('button', { name: 'Próximo' }).click();
  await page.locator('div').filter({ hasText: /^Frete grátis$/ }).first().click();
  await page.getByRole('button', { name: 'Próximo' }).click();
  await page.getByRole('button', { name: 'Finalizar Compra' }).click();
  await page.getByTestId('checkout-container').click();
  await page.locator('div').nth(2).click();
});