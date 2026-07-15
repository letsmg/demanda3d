import { test, expect } from '@playwright/test';

const BASE_URL = 'http://127.0.0.1:8000';
const SENHA_PADRAO = 'Mudar@123';
const SELLER_EMAIL = 'loja1adm@teste.com';

/**
 * Helper: faz login como vendedor SELLER_1 e navega para uma rota.
 */
async function loginAsSeller(page: any, navigateTo: string = '/products') {
  await page.goto(`${BASE_URL}/login`);
  await page.waitForLoadState('networkidle');
  await page.getByLabel('E-mail').fill(SELLER_EMAIL);
  await page.getByPlaceholder('Senha').fill(SENHA_PADRAO);
  await page.getByRole('button', { name: 'Entrar como Vendedor' }).click();
  await expect(page).toHaveURL(/dashboard/, { timeout: 10_000 });

  // Navega para a rota desejada
  await page.goto(`${BASE_URL}${navigateTo}`);
  await page.waitForLoadState('networkidle');
}

/**
 * Helper: acessa a página de criação de produto (já autenticado).
 */
async function goToCreate(page: any) {
  await page.goto(`${BASE_URL}/products/create`);
  await page.waitForLoadState('networkidle');
}

/**
 * Helper: preenche o formulário de produto e submete.
 */
async function fillProductForm(page: any, name: string, price: string, description?: string) {
  if (name !== undefined) {
    await page.locator('#name').fill(name);
  }
  if (price !== undefined) {
    await page.locator('#sale_price').fill(price);
  }
  if (description !== undefined) {
    await page.locator('#description').fill(description);
  }
}

test.describe('CRUD de Produtos', () => {
  // ────────────────────────────────────────────────────────────
  // CADASTRO (CREATE)
  // ────────────────────────────────────────────────────────────
  test.describe('Cadastro', () => {
    test.beforeEach(async ({ page }) => {
      await loginAsSeller(page);
      await goToCreate(page);
    });

    test('não permite cadastrar produto sem nome', async ({ page }) => {
      // Preenche apenas o preço
      await fillProductForm(page, '', '29.90');

      // Submete
      await page.getByRole('button', { name: 'Salvar Produto' }).click();
      await page.waitForLoadState('networkidle');

      // Deve permanecer na página de criação
      await expect(page).toHaveURL(/\/products\/create/);

      // Deve exibir mensagem de erro de validação
      await expect(page.getByText('Erro de validação')).toBeVisible();

      // Deve exibir erro específico do campo nome
      await expect(page.locator('.text-destructive').filter({ hasText: 'O nome do produto é obrigatório' })).toBeVisible();
    });

    test('não permite cadastrar produto sem preço', async ({ page }) => {
      // Preenche apenas o nome
      await fillProductForm(page, 'Produto sem preço', '');

      // Submete
      await page.getByRole('button', { name: 'Salvar Produto' }).click();
      await page.waitForLoadState('networkidle');

      // Deve permanecer na página de criação
      await expect(page).toHaveURL(/\/products\/create/);

      // Deve exibir mensagem de erro de validação
      await expect(page.getByText('Erro de validação')).toBeVisible();

      // Deve exibir erro específico do campo preço
      await expect(page.locator('.text-destructive').filter({ hasText: 'O preço de venda é obrigatório' })).toBeVisible();
    });

    test('não permite cadastrar produto sem nome e sem preço', async ({ page }) => {
      // Submete formulário vazio
      await page.getByRole('button', { name: 'Salvar Produto' }).click();
      await page.waitForLoadState('networkidle');

      // Deve permanecer na página de criação
      await expect(page).toHaveURL(/\/products\/create/);

      // Deve exibir mensagem de erro de validação
      await expect(page.getByText('Erro de validação')).toBeVisible();

      // Ambos os erros devem aparecer
      await expect(page.locator('.text-destructive').filter({ hasText: 'O nome do produto é obrigatório' })).toBeVisible();
      await expect(page.locator('.text-destructive').filter({ hasText: 'O preço de venda é obrigatório' })).toBeVisible();
    });

    test('permite cadastrar produto sem imagens', async ({ page }) => {
      const productName = `Produto sem foto ${Date.now()}`;

      // Preenche nome e preço, sem imagens
      await fillProductForm(page, productName, '49.90', 'Produto criado sem nenhuma imagem.');

      // Submete
      await page.getByRole('button', { name: 'Salvar Produto' }).click();
      await page.waitForLoadState('networkidle');

      // Deve redirecionar para a listagem
      await expect(page).toHaveURL(/\/products(?!\/(create|edit))/);

      // Deve exibir mensagem de sucesso
      await expect(page.getByText('Produto criado com sucesso')).toBeVisible();

      // O produto deve aparecer na listagem
      await expect(page.getByText(productName)).toBeVisible();
    });

    test('caminho feliz — cadastro completo', async ({ page }) => {
      const productName = `Produto feliz ${Date.now()}`;

      await fillProductForm(page, productName, '79.90', 'Descrição do produto feliz.');

      // Submete
      await page.getByRole('button', { name: 'Salvar Produto' }).click();
      await page.waitForLoadState('networkidle');

      // Deve redirecionar para a listagem
      await expect(page).toHaveURL(/\/products(?!\/(create|edit))/);

      // Deve exibir mensagem de sucesso
      await expect(page.getByText('Produto criado com sucesso')).toBeVisible();

      // O produto deve aparecer na listagem
      await expect(page.getByText(productName)).toBeVisible();
    });
  });

  // ────────────────────────────────────────────────────────────
  // EDIÇÃO (UPDATE)
  // ────────────────────────────────────────────────────────────
  test.describe('Edição', () => {
    test.beforeEach(async ({ page }) => {
      await loginAsSeller(page);
    });

    test('não permite salvar alteração sem nome', async ({ page }) => {
      // Cria um produto primeiro
      const originalName = `Produto para editar nome ${Date.now()}`;
      await goToCreate(page);
      await fillProductForm(page, originalName, '59.90');
      await page.getByRole('button', { name: 'Salvar Produto' }).click();
      await page.waitForLoadState('networkidle');
      await expect(page.getByText('Produto criado com sucesso')).toBeVisible();

      // Clica no botão de editar (ícone lápis)
      const editLink = page.locator('a[href*="/products/"][href*="/edit"]').first();
      await editLink.click();
      await page.waitForLoadState('networkidle');

      // Limpa o nome
      await page.locator('#name').fill('');

      // Submete
      await page.getByRole('button', { name: 'Salvar Alterações' }).click();
      await page.waitForLoadState('networkidle');

      // Deve permanecer na página de edição
      await expect(page).toHaveURL(/\/products\/\d+\/edit/);

      // Deve exibir erro de validação
      await expect(page.getByText('Erro de validação')).toBeVisible();
      await expect(page.locator('.text-destructive').filter({ hasText: 'O nome do produto é obrigatório' })).toBeVisible();
    });

    test('permite alterar nome e preço do produto', async ({ page }) => {
      // Cria um produto primeiro
      const originalName = `Produto original ${Date.now()}`;
      await goToCreate(page);
      await fillProductForm(page, originalName, '39.90');
      await page.getByRole('button', { name: 'Salvar Produto' }).click();
      await page.waitForLoadState('networkidle');
      await expect(page.getByText('Produto criado com sucesso')).toBeVisible();

      // Clica no botão de editar
      const editLink = page.locator('a[href*="/products/"][href*="/edit"]').first();
      await editLink.click();
      await page.waitForLoadState('networkidle');

      // Altera nome e preço
      const newName = `Produto alterado ${Date.now()}`;
      await page.locator('#name').fill(newName);
      await page.locator('#sale_price').fill('149.90');

      // Submete
      await page.getByRole('button', { name: 'Salvar Alterações' }).click();
      await page.waitForLoadState('networkidle');

      // Deve redirecionar para a listagem
      await expect(page).toHaveURL(/\/products(?!\/(create|edit))/);

      // Deve exibir mensagem de sucesso
      await expect(page.getByText('Produto atualizado com sucesso')).toBeVisible();

      // O novo nome deve aparecer na listagem
      await expect(page.getByText(newName)).toBeVisible();

      // O nome antigo não deve mais aparecer
      await expect(page.getByText(originalName)).not.toBeVisible();
    });
  });

  // ────────────────────────────────────────────────────────────
  // EXCLUSÃO (DELETE)
  // ────────────────────────────────────────────────────────────
  test.describe('Exclusão', () => {
    test.beforeEach(async ({ page }) => {
      await loginAsSeller(page);
    });

    test('permite excluir um produto da listagem', async ({ page }) => {
      // Cria um produto para ser excluído
      const productName = `Produto para excluir ${Date.now()}`;
      await goToCreate(page);
      await fillProductForm(page, productName, '19.90');
      await page.getByRole('button', { name: 'Salvar Produto' }).click();
      await page.waitForLoadState('networkidle');
      await expect(page.getByText('Produto criado com sucesso')).toBeVisible();

      // Confirma que o produto está visível na listagem
      await expect(page.getByText(productName)).toBeVisible();

      // Prepara para capturar o diálogo de confirmação
      page.on('dialog', async (dialog: any) => {
        expect(dialog.message()).toContain('Tem certeza que deseja excluir este produto?');
        await dialog.accept();
      });

      // Clica no botão de excluir (ícone lixeira) da linha do produto
      const deleteButton = page.locator('tr', { hasText: productName }).locator('button:has(.text-red-500)');
      await deleteButton.click();

      // Aguarda redirect
      await page.waitForLoadState('networkidle');

      // Deve exibir mensagem de sucesso
      await expect(page.getByText('Produto excluído com sucesso')).toBeVisible();

      // O produto não deve mais aparecer na listagem
      await expect(page.getByText(productName)).not.toBeVisible();
    });

    test('exibe confirmação antes de excluir', async ({ page }) => {
      // Cria um produto para excluir
      const productName = `Produto confirm dialog ${Date.now()}`;
      await goToCreate(page);
      await fillProductForm(page, productName, '25.00');
      await page.getByRole('button', { name: 'Salvar Produto' }).click();
      await page.waitForLoadState('networkidle');

      // Configura listener antes do clique
      let dialogShown = false;
      page.on('dialog', async (dialog: any) => {
        dialogShown = true;
        expect(dialog.type()).toBe('confirm');
        await dialog.accept();
      });

      // Clica no botão de excluir
      const deleteButton = page.locator('tr', { hasText: productName }).locator('button:has(.text-red-500)');
      await deleteButton.click();

      // Aguarda
      await page.waitForLoadState('networkidle');

      // O diálogo deve ter sido exibido
      expect(dialogShown).toBe(true);
    });
  });

  // ────────────────────────────────────────────────────────────
  // CONTROLE DE ACESSO
  // ────────────────────────────────────────────────────────────
  test.describe('Controle de acesso', () => {
    test('visitante não autenticado é redirecionado ao tentar acessar produtos', async ({ page }) => {
      await page.goto(`${BASE_URL}/products`);
      await page.waitForLoadState('networkidle');

      // Deve ser redirecionado para o login
      await expect(page).toHaveURL(/\/login/);
    });

    test('cliente (CUSTOMER) não consegue acessar o painel de produtos', async ({ page }) => {
      // Tenta logar como cliente na rota de vendedor — o guard deve barrar
      await page.goto(`${BASE_URL}/login/client`);
      await page.waitForLoadState('networkidle');
      await page.getByLabel('E-mail').fill('cliente1@teste.com');
      await page.getByPlaceholder('Senha').fill(SENHA_PADRAO);
      await page.getByRole('button', { name: 'Entrar como Cliente' }).click();
      await page.waitForLoadState('networkidle');

      // Tenta acessar /products diretamente
      await page.goto(`${BASE_URL}/products`);
      await page.waitForLoadState('networkidle');

      // Deve ser redirecionado para o login de vendedor (não autorizado)
      await expect(page).toHaveURL(/\/login/);
    });
  });
});