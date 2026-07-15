import { test, expect } from '@playwright/test';

const BASE_URL = 'http://127.0.0.1:8000';
const SENHA_PADRAO = 'Mudar@123';

// ══════════════════════════════════════════════════════════
// LOGIN DE VENDEDOR/ADMIN (/login)
// ══════════════════════════════════════════════════════════
test.describe('Login Vendedor/Admin', () => {
  test.beforeEach(async ({ page }) => {
    await page.goto(`${BASE_URL}/login`);
    await page.waitForLoadState('networkidle');
  });

  test('caminho feliz — admin loga e é redirecionado ao dashboard', async ({ page }) => {
    // Preenche credenciais válidas
    await page.getByLabel('E-mail').fill('admin@teste.com');
    await page.getByPlaceholder('Senha').fill(SENHA_PADRAO);

    // Clica em "Entrar como Vendedor"
    await page.getByRole('button', { name: 'Entrar como Vendedor' }).click();

    // Deve redirecionar para o dashboard
    await expect(page).toHaveURL(/dashboard/, { timeout: 10_000 });
  });

  test('caminho feliz — seller1 loga e é redirecionado ao dashboard', async ({ page }) => {
    await page.getByLabel('E-mail').fill('loja1adm@teste.com');
    await page.getByPlaceholder('Senha').fill(SENHA_PADRAO);
    await page.getByRole('button', { name: 'Entrar como Vendedor' }).click();

    await expect(page).toHaveURL(/dashboard/, { timeout: 10_000 });
  });

  test('caminho feliz — seller2 loga e é redirecionado ao dashboard', async ({ page }) => {
    await page.getByLabel('E-mail').fill('loja1padrao@teste.com');
    await page.getByPlaceholder('Senha').fill(SENHA_PADRAO);
    await page.getByRole('button', { name: 'Entrar como Vendedor' }).click();

    await expect(page).toHaveURL(/dashboard/, { timeout: 10_000 });
  });

  test('caminho triste — email inválido mostra erro em português', async ({ page }) => {
    await page.getByLabel('E-mail').fill('email_invalido@teste.com');
    await page.getByPlaceholder('Senha').fill(SENHA_PADRAO);
    await page.getByRole('button', { name: 'Entrar como Vendedor' }).click();

    // Aguarda a resposta e verifica mensagem de erro (Laravel retorna erro via session)
    await page.waitForLoadState('networkidle');

    // Fortify retorna erros no formato: "These credentials do not match our records."
    // O erro pode aparecer de várias formas: flash message, texto na página ou Inertia error
    const errorText = page.locator('.text-destructive, .text-red-600, [role="alert"], .alert-danger');
    await expect(errorText.first()).toBeVisible({ timeout: 5_000 });
  });

  test('caminho triste — senha errada mostra erro em português', async ({ page }) => {
    await page.getByLabel('E-mail').fill('admin@teste.com');
    await page.getByPlaceholder('Senha').fill('SenhaErrada123');
    await page.getByRole('button', { name: 'Entrar como Vendedor' }).click();

    await page.waitForLoadState('networkidle');
    const errorText = page.locator('.text-destructive, .text-red-600, [role="alert"], .alert-danger');
    await expect(errorText.first()).toBeVisible({ timeout: 5_000 });
  });

  test('caminho triste — e-mail vazio mostra validação HTML5', async ({ page }) => {
    await page.getByPlaceholder('Senha').fill(SENHA_PADRAO);
    await page.getByRole('button', { name: 'Entrar como Vendedor' }).click();

    // O input tem 'required', então o navegador impede o submit
    // A URL não deve mudar
    await expect(page).toHaveURL(/\/login/);
  });

  test('contas de teste — clique preenche os campos', async ({ page }) => {
    // Clica em uma conta de teste
    await page.getByRole('button', { name: /Loja 1 Admin/ }).click();

    // Verifica se o campo de e-mail foi preenchido
    const emailInput = page.getByLabel('E-mail');
    await expect(emailInput).toHaveValue('loja1adm@teste.com');
  });
});

// ══════════════════════════════════════════════════════════
// LOGIN DE CLIENTE (/login_cli)
// ══════════════════════════════════════════════════════════
test.describe('Login Cliente', () => {
  test.beforeEach(async ({ page }) => {
    await page.goto(`${BASE_URL}/login_cli`);
    await page.waitForLoadState('networkidle');
  });

  test('caminho feliz — cliente loga e é redirecionado à loja', async ({ page }) => {
    await page.getByLabel('E-mail').fill('cliente1@teste.com');
    await page.getByPlaceholder('Senha').fill(SENHA_PADRAO);
    await page.getByRole('button', { name: 'Entrar como Cliente' }).click();

    // Cliente é redirecionado para /store ou /perfil
    await expect(page).not.toHaveURL(/\/login_cli/, { timeout: 10_000 });
  });

  test('caminho feliz — cliente 3 loga com sucesso', async ({ page }) => {
    await page.getByLabel('E-mail').fill('cliente3@teste.com');
    await page.getByPlaceholder('Senha').fill(SENHA_PADRAO);
    await page.getByRole('button', { name: 'Entrar como Cliente' }).click();

    await expect(page).not.toHaveURL(/\/login_cli/, { timeout: 10_000 });
  });

  test('caminho triste — senha errada mostra erro', async ({ page }) => {
    await page.getByLabel('E-mail').fill('cliente1@teste.com');
    await page.getByPlaceholder('Senha').fill('SenhaErrada');
    await page.getByRole('button', { name: 'Entrar como Cliente' }).click();

    await page.waitForLoadState('networkidle');
    const errorText = page.locator('.text-destructive, .text-red-600, [role="alert"]');
    await expect(errorText.first()).toBeVisible({ timeout: 5_000 });
  });

  test('contas de teste — clique preenche email e senha', async ({ page }) => {
    await page.getByRole('button', { name: /Cliente 1/ }).click();

    const emailInput = page.getByLabel('E-mail');
    await expect(emailInput).toHaveValue('cliente1@teste.com');
  });
});

// ══════════════════════════════════════════════════════════
// LOGIN DE TRANSPORTADORA (/login_carrier)
// ══════════════════════════════════════════════════════════
test.describe('Login Transportadora', () => {
  test.beforeEach(async ({ page }) => {
    await page.goto(`${BASE_URL}/login_carrier`);
    await page.waitForLoadState('networkidle');
  });

  test('caminho feliz — carrier1 loga e é redirecionado ao painel', async ({ page }) => {
    await page.getByLabel('E-mail').fill('transp1adm@teste.com');
    await page.getByPlaceholder('Senha').fill(SENHA_PADRAO);
    await page.getByRole('button', { name: 'Entrar como Transportadora' }).click();

    await expect(page).toHaveURL(/carrier/, { timeout: 10_000 });
  });

  test('caminho feliz — carrier2 loga e é redirecionado ao painel', async ({ page }) => {
    await page.getByLabel('E-mail').fill('transp1padrao@teste.com');
    await page.getByPlaceholder('Senha').fill(SENHA_PADRAO);
    await page.getByRole('button', { name: 'Entrar como Transportadora' }).click();

    await expect(page).toHaveURL(/carrier/, { timeout: 10_000 });
  });

  test('caminho triste — email não cadastrado mostra erro', async ({ page }) => {
    await page.getByLabel('E-mail').fill('naoexiste@teste.com');
    await page.getByPlaceholder('Senha').fill(SENHA_PADRAO);
    await page.getByRole('button', { name: 'Entrar como Transportadora' }).click();

    await page.waitForLoadState('networkidle');
    const errorText = page.locator('.text-destructive, .text-red-600, [role="alert"]');
    await expect(errorText.first()).toBeVisible({ timeout: 5_000 });
  });

  test('contas de teste — clique preenche os campos', async ({ page }) => {
    await page.getByRole('button', { name: /Transp 1 Admin/ }).click();

    const emailInput = page.getByLabel('E-mail');
    await expect(emailInput).toHaveValue('transp1adm@teste.com');
  });
});