import { test, expect } from '@playwright/test';

const BASE_URL = 'http://127.0.0.1:8000';

// ══════════════════════════════════════════════════════════
// REGISTRO DE VENDEDOR (/register)
// ══════════════════════════════════════════════════════════
test.describe('Registro de Vendedor', () => {
  test.beforeEach(async ({ page }) => {
    await page.goto(`${BASE_URL}/register`);
    await page.waitForLoadState('networkidle');
  });

  test('caminho feliz — cadastro com sucesso redireciona ao dashboard', async ({ page }) => {
    const timestamp = Date.now();
    await page.getByPlaceholder('Seu e-mail de acesso').fill(`vendedor${timestamp}@teste.com`);
    await page.getByPlaceholder('Mínimo 8 caracteres').first().fill('Mudar@123');
    await page.getByPlaceholder('Repita a senha').first().fill('Mudar@123');

    // Checkboxes de aceite já vêm marcados
    await page.getByRole('button', { name: 'Criar Conta de Vendedor' }).click();

    // Deve redirecionar ao dashboard
    await expect(page).toHaveURL(/dashboard/, { timeout: 10_000 });
  });

  test('caminho triste — senhas não conferem mostra erro', async ({ page }) => {
    await page.getByPlaceholder('Seu e-mail de acesso').fill('vendedor@teste.com');
    await page.getByPlaceholder('Mínimo 8 caracteres').first().fill('Mudar@123');
    await page.getByPlaceholder('Repita a senha').first().fill('Diferente123');

    await page.getByRole('button', { name: 'Criar Conta de Vendedor' }).click();

    // Deve permanecer na página de registro
    await expect(page).toHaveURL(/\/register/, { timeout: 5_000 });
  });

  test('caminho triste — email inválido mostra erro', async ({ page }) => {
    await page.getByPlaceholder('Seu e-mail de acesso').fill('email-invalido');
    await page.getByPlaceholder('Mínimo 8 caracteres').first().fill('Mudar@123');
    await page.getByPlaceholder('Repita a senha').first().fill('Mudar@123');

    await page.getByRole('button', { name: 'Criar Conta de Vendedor' }).click();

    // Validação HTML5 do type="email" pode impedir submit
    await page.waitForLoadState('networkidle');
    await expect(page).toHaveURL(/\/register/);
  });

  test('formulário tem botão Preencher Teste e Limpar', async ({ page }) => {
    // Verifica que o FormTestHelper está presente
    await expect(page.getByText('Vendedor teste')).toBeVisible();
    await expect(page.getByRole('button', { name: 'Preencher Teste' })).toBeVisible();
    await expect(page.getByRole('button', { name: 'Limpar' })).toBeVisible();

    // Clica Preencher
    await page.getByRole('button', { name: 'Preencher Teste' }).click();

    // Verifica se preencheu o email
    const emailInput = page.getByPlaceholder('Seu e-mail de acesso');
    await expect(emailInput).not.toHaveValue('');
  });
});

// ══════════════════════════════════════════════════════════
// REGISTRO DE CLIENTE (/register_cli)
// ══════════════════════════════════════════════════════════
test.describe('Registro de Cliente', () => {
  test.beforeEach(async ({ page }) => {
    await page.goto(`${BASE_URL}/register_cli`);
    await page.waitForLoadState('networkidle');
  });

  test('caminho feliz — cadastro com sucesso redireciona à loja', async ({ page }) => {
    const timestamp = Date.now();
    await page.getByPlaceholder('Seu e-mail de acesso').fill(`cliente${timestamp}@teste.com`);
    await page.getByPlaceholder('Mínimo 8 caracteres').fill('Mudar@123');
    await page.getByPlaceholder('Repita a senha').fill('Mudar@123');

    await page.getByRole('button', { name: 'Criar Conta de Cliente' }).click();

    // Cliente é redirecionado para a loja
    await expect(page).not.toHaveURL(/\/register_cli/, { timeout: 10_000 });
  });

  test('caminho triste — email já cadastrado mostra erro', async ({ page }) => {
    await page.getByPlaceholder('Seu e-mail de acesso').fill('cliente1@teste.com');
    await page.getByPlaceholder('Mínimo 8 caracteres').fill('Mudar@123');
    await page.getByPlaceholder('Repita a senha').fill('Mudar@123');

    await page.getByRole('button', { name: 'Criar Conta de Cliente' }).click();

    await page.waitForLoadState('networkidle');
    await expect(page).toHaveURL(/\/register_cli/);
  });

  test('formulário tem botão Preencher Teste e Limpar', async ({ page }) => {
    await expect(page.getByRole('button', { name: 'Preencher Teste' })).toBeVisible();
    await expect(page.getByRole('button', { name: 'Limpar' })).toBeVisible();
  });

  test('checkboxes de aceite estão marcados', async ({ page }) => {
    const termsCheckbox = page.locator('#accept_terms');
    const privacyCheckbox = page.locator('#accept_privacy');

    await expect(termsCheckbox).toBeChecked();
    await expect(privacyCheckbox).toBeChecked();
  });
});

// ══════════════════════════════════════════════════════════
// REGISTRO DE TRANSPORTADORA (/register_carrier)
// ══════════════════════════════════════════════════════════
test.describe('Registro de Transportadora', () => {
  test.beforeEach(async ({ page }) => {
    await page.goto(`${BASE_URL}/register_carrier`);
    await page.waitForLoadState('networkidle');
  });

  test('caminho feliz — cadastro redireciona ao login da transportadora', async ({ page }) => {
    const timestamp = Date.now();
    await page.getByPlaceholder('Seu e-mail de acesso').fill(`transp${timestamp}@teste.com`);
    await page.getByPlaceholder('Mínimo 8 caracteres').fill('Mudar@123');
    await page.getByPlaceholder('Repita a senha').fill('Mudar@123');

    await page.getByRole('button', { name: 'Criar Conta de Transportadora' }).click();

    // Deve redirecionar ao login da transportadora com mensagem de sucesso
    await expect(page).toHaveURL(/\/login_carrier/, { timeout: 10_000 });
  });

  test('caminho triste — checkbox de termos desmarcado bloqueia', async ({ page }) => {
    const timestamp = Date.now();
    await page.getByPlaceholder('Seu e-mail de acesso').fill(`transp${timestamp}@teste.com`);
    await page.getByPlaceholder('Mínimo 8 caracteres').fill('Mudar@123');
    await page.getByPlaceholder('Repita a senha').fill('Mudar@123');

    // Desmarca os checkboxes
    const termsCheckbox = page.locator('#accept_terms');
    const privacyCheckbox = page.locator('#accept_privacy');
    await termsCheckbox.uncheck();
    await privacyCheckbox.uncheck();

    await page.getByRole('button', { name: 'Criar Conta de Transportadora' }).click();

    await page.waitForLoadState('networkidle');
    await expect(page).toHaveURL(/\/register_carrier/);
  });

  test('formulário tem botão Preencher Teste e Limpar', async ({ page }) => {
    await expect(page.getByRole('button', { name: 'Preencher Teste' })).toBeVisible();
    await expect(page.getByRole('button', { name: 'Limpar' })).toBeVisible();
  });
});