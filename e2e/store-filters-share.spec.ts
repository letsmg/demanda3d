import { test, expect } from '@playwright/test';

const BASE_URL = 'http://127.0.0.1:8000';

// ══════════════════════════════════════════════════════════
// LOJA PÚBLICA — Filtros e Compartilhamento
// ══════════════════════════════════════════════════════════

test.describe('Loja — Filtros', () => {
    test.beforeEach(async ({ page }) => {
        // Navega para a loja e aguarda carregamento
        await page.goto(`${BASE_URL}/store`);
        await page.waitForLoadState('networkidle');
    });

    test('filtro de busca funciona e atualiza a URL', async ({ page }) => {
        // Localiza o input de busca
        const searchInput = page.getByPlaceholder('Buscar produtos por nome ou descrição...');

        await expect(searchInput).toBeVisible();

        // Digita termo de busca
        await searchInput.fill('teste');
        await searchInput.press('Enter');

        // Aguarda a navegação Inertia
        await page.waitForTimeout(600);

        // A URL deve conter o parâmetro de busca
        await expect(page).toHaveURL(/search=teste/);
    });

    test('filtro de preço funciona e atualiza a URL', async ({ page }) => {
        const minInput = page.getByPlaceholder('Mín');
        const maxInput = page.getByPlaceholder('Máx');

        await expect(minInput).toBeVisible();
        await expect(maxInput).toBeVisible();

        // Preenche preço mínimo
        await minInput.fill('50');
        await minInput.blur();
        await page.waitForTimeout(400);

        await expect(page).toHaveURL(/min_price=50/);

        // Preenche preço máximo
        await maxInput.fill('200');
        await maxInput.blur();
        await page.waitForTimeout(400);

        await expect(page).toHaveURL(/max_price=200/);
    });

    test('filtro de ordenação funciona e atualiza a URL', async ({ page }) => {
        // Abre o select de ordenação
        const sortTrigger = page.locator('[data-radix-select-trigger]').first();
        await sortTrigger.click();

        // Aguarda dropdown
        await page.waitForTimeout(300);

        // Seleciona "Menor Preço"
        const menorPreco = page.getByText('Menor Preço');
        await menorPreco.click();

        await page.waitForTimeout(400);

        // URL deve refletir ordenação por preço ascendente
        await expect(page).toHaveURL(/sort=sale_price.*sort_dir=asc/);
    });

    test('filtro de categoria funciona e atualiza a URL', async ({ page }) => {
        // Pega um botão de categoria (não "Todas")
        const categoryButtons = page.locator('button', {
            has: page.locator('[class*="rounded-full"]'),
        });

        // Clica no segundo botão (primeira categoria depois de "Todas")
        const buttons = await categoryButtons.all();

        if (buttons.length > 1) {
            // Pega o texto da categoria para verificação
            const catText = await buttons[1].textContent();

            await buttons[1].click();
            await page.waitForTimeout(400);

            // URL deve conter o parâmetro category
            await expect(page).toHaveURL(/category=/);
        }
    });

    test('botão "Limpar filtros" remove todos os filtros da URL', async ({ page }) => {
        // Aplica alguns filtros primeiro
        await page.goto(`${BASE_URL}/store?search=teste&min_price=10&sort=sale_price&sort_dir=asc`);
        await page.waitForLoadState('networkidle');

        // Clica em "Limpar filtros"
        const clearBtn = page.getByRole('button', { name: 'Limpar filtros' });

        if (await clearBtn.isVisible()) {
            await clearBtn.click();
            await page.waitForTimeout(400);

            // URL deve voltar ao /store limpo
            await expect(page).toHaveURL(`${BASE_URL}/store`);
        }
    });
});

test.describe('Loja — Compartilhamento', () => {
    test('botão de compartilhar abre o diálogo na listagem', async ({ page }) => {
        await page.goto(`${BASE_URL}/store`);
        await page.waitForLoadState('networkidle');

        // Encontra o botão de compartilhar no card do produto (ícone ExternalLink)
        const shareBtn = page.locator('button[title^="Compartilhar"]');

        // Se houver produtos, testa o botão
        if (await shareBtn.first().isVisible()) {
            await shareBtn.first().click();
            await page.waitForTimeout(300);

            // O diálogo de compartilhamento deve aparecer
            const dialog = page.getByText('Compartilhar Produto');
            await expect(dialog).toBeVisible();

            // Opções de compartilhamento devem estar visíveis
            await expect(page.getByText('Copiar Link')).toBeVisible();
            await expect(page.getByText('WhatsApp')).toBeVisible();
            await expect(page.getByText('Facebook')).toBeVisible();
            await expect(page.getByText('Telegram')).toBeVisible();
        }
    });

    test('botão de compartilhar abre o diálogo na página de detalhes', async ({ page }) => {
        await page.goto(`${BASE_URL}/store`);
        await page.waitForLoadState('networkidle');

        // Clica em "Ver mais detalhes" no primeiro card (via gallery)
        const firstCard = page.locator('.grid a[href^="/store/"]').first();

        if (await firstCard.isVisible()) {
            const href = await firstCard.getAttribute('href');

            if (href) {
                await page.goto(`${BASE_URL}${href}`);
                await page.waitForLoadState('networkidle');

                // Clica no botão "Compartilhar" na página de detalhes
                const shareBtn = page.getByRole('button', { name: 'Compartilhar' });

                if (await shareBtn.isVisible()) {
                    await shareBtn.click();
                    await page.waitForTimeout(300);

                    // O diálogo deve aparecer
                    const dialog = page.getByText('Compartilhar Produto');
                    await expect(dialog).toBeVisible();
                }
            }
        }
    });
});