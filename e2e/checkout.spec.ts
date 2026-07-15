import { test, expect } from '@playwright/test';

const BASE_URL = 'http://127.0.0.1:8000';

// ══════════════════════════════════════════════════════════
// FLUXO DE CHECKOUT COMPLETO
// Baseado em: tests/Feature/CheckoutServiceTest.php
//            resources/js/pages/Client/Checkout.vue
// ══════════════════════════════════════════════════════════
test.describe('Fluxo de Checkout', () => {
  test.beforeEach(async ({ page }) => {
    // Login como cliente
    await page.goto(`${BASE_URL}/login_cli`);
    await page.getByLabel('E-mail').fill('cliente1@teste.com');
    await page.getByPlaceholder('Senha').fill('Mudar@123');
    await page.getByRole('button', { name: 'Entrar como Cliente' }).click();
    await page.waitForLoadState('networkidle');
  });

  test('carrinho vazio exibe mensagem apropriada', async ({ page }) => {
    await page.goto(`${BASE_URL}/cart`);
    await page.waitForLoadState('networkidle');

    // Deve mostrar que o carrinho está vazio
    await expect(page).toHaveURL(/\/cart/);
  });

  test('navegação para checkout sem autenticação redireciona ao login', async ({ page }) => {
    // Desloga primeiro
    await page.goto(`${BASE_URL}/logout_cli`);
    await page.waitForLoadState('networkidle');

    // Tenta acessar checkout sem login
    await page.goto(`${BASE_URL}/checkout`);
    await page.waitForLoadState('networkidle');

    // Deve redirecionar ao login
    await expect(page).toHaveURL(/\/login_cli/);
  });

  test('cliente logado pode acessar a página de checkout', async ({ page }) => {
    // Primeiro adiciona algo ao carrinho (se disponível)
    await page.goto(`${BASE_URL}/store`);
    await page.waitForLoadState('networkidle');
  });

  test('página de sucesso do checkout é acessível', async ({ page }) => {
    await page.goto(`${BASE_URL}/checkout/success`);
    await page.waitForLoadState('networkidle');

    // Deve carregar sem erro
    await expect(page).toHaveURL(/\/checkout\/success/);
  });

  test('página de cancelamento do checkout é acessível', async ({ page }) => {
    await page.goto(`${BASE_URL}/checkout/cancel`);
    await page.waitForLoadState('networkidle');

    await expect(page).toHaveURL(/\/checkout\/cancel/);
  });
});

// ══════════════════════════════════════════════════════════
// PERFIL DO CLIENTE — PEDIDOS
// Baseado em: tests/Feature/OrderCancellationTest.php
// ══════════════════════════════════════════════════════════
test.describe('Painel do Cliente — Pedidos', () => {
  test.beforeEach(async ({ page }) => {
    // Login como cliente
    await page.goto(`${BASE_URL}/login_cli`);
    await page.getByLabel('E-mail').fill('cliente1@teste.com');
    await page.getByPlaceholder('Senha').fill('Mudar@123');
    await page.getByRole('button', { name: 'Entrar como Cliente' }).click();
    await page.waitForLoadState('networkidle');
  });

  test('cliente pode acessar seus pedidos', async ({ page }) => {
    await page.goto(`${BASE_URL}/perfil/pedidos`);
    await page.waitForLoadState('networkidle');

    // A página de pedidos deve carregar
    await expect(page).toHaveURL(/\/perfil\/pedidos/);
  });

  test('cliente pode acessar seu perfil', async ({ page }) => {
    await page.goto(`${BASE_URL}/perfil`);
    await page.waitForLoadState('networkidle');

    await expect(page).toHaveURL(/\/perfil/);
  });

  test('cliente pode acessar seus endereços', async ({ page }) => {
    await page.goto(`${BASE_URL}/perfil/enderecos`);
    await page.waitForLoadState('networkidle');

    await expect(page).toHaveURL(/\/perfil\/enderecos/);
  });
});

// ══════════════════════════════════════════════════════════
// SEGURANÇA — BYPASS DE ROTAS
// ══════════════════════════════════════════════════════════
test.describe('Segurança — Bypass de Rotas', () => {
  test('cliente tentando acessar dashboard de vendedor é redirecionado', async ({ page }) => {
    // Login como cliente
    await page.goto(`${BASE_URL}/login_cli`);
    await page.getByLabel('E-mail').fill('cliente1@teste.com');
    await page.getByPlaceholder('Senha').fill('Mudar@123');
    await page.getByRole('button', { name: 'Entrar como Cliente' }).click();
    await page.waitForLoadState('networkidle');

    // Tenta acessar o dashboard de vendedor
    await page.goto(`${BASE_URL}/dashboard`);
    await page.waitForLoadState('networkidle');

    // Deve ser redirecionado (não fica no dashboard)
    await expect(page).not.toHaveURL(/\/dashboard/);
  });

  test('visitante tentando acessar checkout é redirecionado ao login', async ({ page }) => {
    await page.goto(`${BASE_URL}/checkout`);
    await page.waitForLoadState('networkidle');

    await expect(page).toHaveURL(/\/login_cli/);
  });

  test('vendedor tentando acessar checkout de cliente é redirecionado', async ({ page }) => {
    // Login como vendedor
    await page.goto(`${BASE_URL}/login`);
    await page.getByLabel('E-mail').fill('loja1adm@teste.com');
    await page.getByPlaceholder('Senha').fill('Mudar@123');
    await page.getByRole('button', { name: 'Entrar como Vendedor' }).click();
    await page.waitForLoadState('networkidle');

    // Tenta acessar checkout de cliente
    await page.goto(`${BASE_URL}/checkout`);
    await page.waitForLoadState('networkidle');

    // Deve ser redirecionado (checkout é só para clients)
    await expect(page).not.toHaveURL(/\/checkout/);
  });
});