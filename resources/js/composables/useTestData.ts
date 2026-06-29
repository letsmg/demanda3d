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

const marcasInsumos = ['3DLab', 'eSun', 'Creality', 'SUNLU', 'Polymaker', 'FiloPrint', 'Anycubic', 'Elegoo', 'Voolt3D', 'PrintaLot'];
const tiposFilamento = ['PLA', 'ABS', 'PETG', 'TPU', 'Nylon', 'PLA Silk', 'PETG Translúcido'];

function randomElement<T>(arr: T[]): T {
    return arr[Math.floor(Math.random() * arr.length)];
}

export function useTestData() {
    function randomProductName(): string {
        return randomElement(nomesProdutos) + ' ' + Math.floor(Math.random() * 900 + 100);
    }

    function randomProductDescription(): string {
        return randomElement(descricoesProdutos);
    }

    function randomPrice(): string {
        return (Math.random() * 200 + 9.90).toFixed(2);
    }

    function randomInputDescription(): string {
        return randomElement(tiposFilamento) + ' ' + (Math.random() * 2 + 1).toFixed(1) + 'mm 1kg ' + randomElement(['Preto', 'Branco', 'Transparente', 'Cinza', 'Azul', 'Vermelho']);
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

    return {
        randomProductName,
        randomProductDescription,
        randomPrice,
        randomInputDescription,
        randomBrand,
        randomQuantity,
        randomShippingCost,
        randomCostValue,
    };
}