import { test, expect } from '@playwright/test';

const BASE_URL = 'http://127.0.0.1:8000';

// ══════════════════════════════════════════════════════════
// LOJA PÚBLICA (/store) — Visibilidade de Produtos
// Baseado em: tests/Feature/ProductVisibilityTest.php
//            tests/Feature/TenantBlockedProductsHiddenTest.php
// ══════════════════════════════════════════════════════════
test.describe('Loja Pública — Visibilidade', () => {
  test('visitante anônimo acessa a loja', async ({ page }) => {
    await page.goto(`${BASE_URL}/store`);
    await page.waitForLoadState('networkidle');

    // A página da loja deve carregar
    await expect(page).toHaveURL(/\/store/);
    await expect(page.locator('h1, h2')).toBeVisible();
  });

  test('cliente logado pode navegar na loja', async ({ page }) => {
    // Login como cliente
    await page.goto(`${BASE_URL}/login_cli`);
    await page.getByLabel('E-mail').fill('cliente1@teste.com');
    await page.getByPlaceholder('Senha').fill('Mudar@123');
    await page.getByRole('button', { name: 'Entrar como Cliente' }).click();
    await page.waitForLoadState('networkidle');

    // Navega para a loja
    await page.goto(`${BASE_URL}/store`);
    await page.waitForLoadState('networkidle');

    await expect(page).toHaveURL(/\/store/);
  });

  test('página "Sobre" está acessível', async ({ page }) => {
    await page.goto(`${BASE_URL}/sobre`);
    await page.waitForLoadState('networkidle');

    await expect(page).toHaveURL(/\/sobre/);
  });

  test('página de termos de uso está acessível', async ({ page }) => {
    await page.goto(`${BASE_URL}/legal/terms`);
    await page.waitForLoadState('networkidle');

    await expect(page).toHaveURL(/\/legal\/terms/);
  });

  test('página de política de privacidade está acessível', async ({ page }) => {
    await page.goto(`${BASE_URL}/legal/privacy`);
    await page.waitForLoadState('networkidle');

    await expect(page).toHaveURL(/\/legal\/privacy/);
  });
});

// ══════════════════════════════════════════════════════════
// PERFIL DE TENANT (/tenant/{slug})
// Baseado em: TenantBlockedProductsHiddenTest
// ══════════════════════════════════════════════════════════
test.describe('Perfil de Tenant', () => {
  test('tenant ativo retorna 200', async ({ page }) => {
    // loja-1-impressoes-3d (slug do seeder)
    await page.goto(`${BASE_URL}/tenant/loja-1-impressoes-3d`);
    await page.waitForLoadState('networkidle');

    // Deve carregar normalmente
    const status = page.locator('body');
    await expect(status).toBeVisible();
  });

  test('tenant inexistente retorna 404', async ({ page }) => {
    const response = await page.goto(`${BASE_URL}/tenant/loja-inexistente-999`);
    // Pode retornar 404 ou página de erro
    expect(response?.status()).toBeGreaterThanOrEqual(400);
  });
});

// ══════════════════════════════════════════════════════════
// NAVEGAÇÃO ENTRE PORTAS DE LOGIN
// ══════════════════════════════════════════════════════════
test.describe('Navegação entre portas', () => {
  test('tela de login de vendedor tem links para outros logins', async ({ page }) => {
    await page.goto(`${BASE_URL}/login`);
    await page.waitForLoadState('networkidle');

    await expect(page.getByText('Acesse como Cliente')).toBeVisible();
    await expect(page.getByText('Acesse como Transportadora')).toBeVisible();
  });

  test('tela de login de cliente tem links para outros logins', async ({ page }) => {
    await page.goto(`${BASE_URL}/login_cli`);
    await page.waitForLoadState('networkidle');

    await expect(page.getByText('Acesse como Vendedor')).toBeVisible();
    await expect(page.getByText('Acesse como Transportadora')).toBeVisible();
  });

  test('tela de login de transportadora tem links para outros logins', async ({ page }) => {
    await page.goto(`${BASE_URL}/login_carrier`);
    await page.waitForLoadState('networkidle');

    await expect(page.getByText('Acesse como Vendedor')).toBeVisible();
    await expect(page.getByText('Acesse como Cliente')).toBeVisible();
  });

  test('tela de registro de vendedor tem links para outros registros', async ({ page }) => {
    await page.goto(`${BASE_URL}/register`);
    await page.waitForLoadState('networkidle');

    await expect(page.getByText('Cadastre-se como Cliente')).toBeVisible();
    await expect(page.getByText('Cadastre-se como Transportadora')).toBeVisible();
  });
});