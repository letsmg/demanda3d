const nomesProdutos = [
    'Suporte Articulado para Notebook',
    'Vaso Geométrico Decorativo',
    'Organizador de Mesa Modular',
    'Porta-celular Ajustável',
    'Engrenagem Helicoidal 32 Dentes',
    'Carcaça para Drone FPV',
    'Suporte para Fone de Ouvido',
    'Luminária Articulada de Mesa',
    'Caixa Organizadora Empilhável',
    'Peça de Reposição para Impressora',
    'Adaptador de Rosca Universal',
    'Molde para Resina Epóxi',
    'Base Giratória para Monitor',
    'Gancho Multiuso para Parede',
    'Chaveiro Personalizado Geek',
];

const descricoesProdutos = [
    'Fabricado em PETG de alta resistência com acabamento premium. Ideal para uso diário.',
    'Design exclusivo em PLA com geometria otimizada para impressão sem suportes.',
    'Peça funcional impressa em ABS, suporta temperaturas de até 80°C.',
    'Produzido em Nylon reforçado com fibra de carbono para aplicações industriais.',
    'Acabamento liso em PLA Silk, disponível em diversas cores vibrantes.',
    'Protótipo funcional em PETG translúcido, ideal para validação de design.',
    'Peça otimizada para produção em massa, reduzindo tempo de impressão em 30%.',
    'Design paramétrico ajustável ao tamanho desejado pelo cliente.',
];

const marcasInsumos = [
    '3DLab',
    'eSun',
    'Creality',
    'SUNLU',
    'Polymaker',
    'FiloPrint',
    'Anycubic',
    'Elegoo',
    'Voolt3D',
    'PrintaLot',
];
const tiposFilamento = [
    'PLA',
    'ABS',
    'PETG',
    'TPU',
    'Nylon',
    'PLA Silk',
    'PETG Translúcido',
];

function randomElement<T>(arr: T[]): T {
    return arr[Math.floor(Math.random() * arr.length)];
}

export function useTestData() {
    function randomProductName(): string {
        return (
            randomElement(nomesProdutos) +
            ' ' +
            Math.floor(Math.random() * 900 + 100)
        );
    }

    function randomProductDescription(): string {
        return randomElement(descricoesProdutos);
    }

    function randomPrice(): string {
        return (Math.random() * 200 + 9.9).toFixed(2);
    }

    function randomInputDescription(): string {
        return (
            randomElement(tiposFilamento) +
            ' ' +
            (Math.random() * 2 + 1).toFixed(1) +
            'mm 1kg ' +
            randomElement([
                'Preto',
                'Branco',
                'Transparente',
                'Cinza',
                'Azul',
                'Vermelho',
            ])
        );
    }

    function randomBrand(): string {
        return randomElement(marcasInsumos);
    }

    function randomQuantity(): number {
        return Math.floor(Math.random() * 4000) + 500;
    }

    function randomShippingCost(): string {
        return (Math.random() * 40 + 10).toFixed(2);
    }

    function randomCostValue(): string {
        return (Math.random() * 400 + 50).toFixed(2);
    }

    /**
     * Gera um CNPJ aleatório válido com dígitos verificadores corretos.
     * Retorna no formato 00.000.000/0001-00
     */
    function randomCNPJ(): string {
        const n = Array.from({ length: 12 }, () =>
            Math.floor(Math.random() * 9),
        );

        // Raiz fixa: 0001 (filial)
        const raiz = [0, 0, 0, 1];
        const cnpj = [...n.slice(0, 8), ...raiz];

        let d1 = 0;
        let d2 = 0;
        const pesos1 = [5, 4, 3, 2, 9, 8, 7, 6, 5, 4, 3, 2];
        const pesos2 = [6, 5, 4, 3, 2, 9, 8, 7, 6, 5, 4, 3, 2];

        for (let i = 0; i < 12; i++) {
            d1 += cnpj[i] * pesos1[i];
            d2 += cnpj[i] * pesos2[i];
        }
        d1 = d1 % 11 < 2 ? 0 : 11 - (d1 % 11);
        d2 += d1 * pesos2[12];
        d2 = d2 % 11 < 2 ? 0 : 11 - (d2 % 11);

        const digits = [...cnpj, d1, d2];
        return digits
            .join('')
            .replace(/^(\d{2})(\d{3})(\d{3})(\d{4})(\d{2})$/, '$1.$2.$3/$4-$5');
    }

    /**
     * Gera um CPF aleatório válido com dígitos verificadores corretos.
     */
    function randomCPF(): string {
        const n = Array.from({ length: 9 }, () =>
            Math.floor(Math.random() * 9),
        );
        let d1 = 0;
        let d2 = 0;
        for (let i = 0; i < 9; i++) {
            d1 += n[i] * (10 - i);
            d2 += n[i] * (11 - i);
        }
        d1 = (d1 * 10) % 11;
        d1 = d1 >= 10 ? 0 : d1;
        d2 += d1 * 2;
        d2 = (d2 * 10) % 11;
        d2 = d2 >= 10 ? 0 : d2;
        return [...n, d1, d2]
            .join('')
            .replace(/^(\d{3})(\d{3})(\d{3})(\d{2})$/, '$1.$2.$3-$4');
    }

    return {
        randomProductName,
        randomProductDescription,
        randomPrice,
        randomInputDescription,
        randomBrand,
        randomQuantity,
        randomShippingCost,
        randomCostValue,
        randomCNPJ,
        randomCPF,
    };
}
