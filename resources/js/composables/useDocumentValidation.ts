/**
 * Composable para validação e formatação de documentos brasileiros (CPF e CNPJ).
 *
 * Suporta o novo formato de CNPJ alfanumérico da Receita Federal (conversão ASCII),
 * mantendo compatibilidade com CPF e CNPJ numéricos tradicionais.
 */

/**
 * Remove todos os caracteres de formatação, mantendo apenas letras (A-Z) e números (0-9).
 */
export function cleanDocument(doc: string): string {
    return doc.replace(/[^A-Za-z0-9]/g, '').toUpperCase();
}

/**
 * Remove apenas caracteres não numéricos (para CPF e CNPJ numérico).
 */
export function digitsOnly(doc: string): string {
    return doc.replace(/\D/g, '');
}

/**
 * Detecta o tipo de documento baseado na quantidade de caracteres (após limpeza).
 */
export function detectDocType(doc: string): 'CPF' | 'CNPJ' {
    const clean = cleanDocument(doc);
    // Se houver letras, é CNPJ alfanumérico
    if (/[A-Za-z]/.test(clean)) {
        return 'CNPJ';
    }
    return clean.length <= 11 ? 'CPF' : 'CNPJ';
}

/**
 * Valida CPF (11 dígitos numéricos).
 */
export function isValidCpf(cpf: string): boolean {
    const digits = digitsOnly(cpf);
    if (digits.length !== 11) return false;

    // Rejeita sequências inválidas conhecidas
    if (/^(\d)\1{10}$/.test(digits)) return false;

    // Primeiro dígito verificador
    let sum = 0;
    for (let i = 0; i < 9; i++) {
        sum += parseInt(digits[i], 10) * (10 - i);
    }
    let remainder = sum % 11;
    const digit1 = remainder < 2 ? 0 : 11 - remainder;
    if (parseInt(digits[9], 10) !== digit1) return false;

    // Segundo dígito verificador
    sum = 0;
    for (let i = 0; i < 10; i++) {
        sum += parseInt(digits[i], 10) * (11 - i);
    }
    remainder = sum % 11;
    const digit2 = remainder < 2 ? 0 : 11 - remainder;

    return parseInt(digits[10], 10) === digit2;
}

/**
 * Valida CNPJ numérico tradicional (14 dígitos).
 */
export function isValidCnpjNumeric(cnpj: string): boolean {
    const digits = digitsOnly(cnpj);
    if (digits.length !== 14) return false;

    // Se contém letras, não é CNPJ numérico
    if (/[A-Za-z]/.test(cnpj)) return false;

    // Rejeita sequências inválidas conhecidas
    if (/^(\d)\1{13}$/.test(digits)) return false;

    // Primeiro dígito verificador
    const weights1 = [5, 4, 3, 2, 9, 8, 7, 6, 5, 4, 3, 2];
    let sum = 0;
    for (let i = 0; i < 12; i++) {
        sum += parseInt(digits[i], 10) * weights1[i];
    }
    let remainder = sum % 11;
    const digit1 = remainder < 2 ? 0 : 11 - remainder;
    if (parseInt(digits[12], 10) !== digit1) return false;

    // Segundo dígito verificador
    const weights2 = [6, 5, 4, 3, 2, 9, 8, 7, 6, 5, 4, 3, 2];
    sum = 0;
    for (let i = 0; i < 13; i++) {
        sum += parseInt(digits[i], 10) * weights2[i];
    }
    remainder = sum % 11;
    const digit2 = remainder < 2 ? 0 : 11 - remainder;

    return parseInt(digits[13], 10) === digit2;
}

/**
 * Valida CNPJ alfanumérico (novo formato da Receita Federal).
 *
 * Utiliza conversão ASCII para calcular os dígitos verificadores:
 *   - Caracteres alfabéticos (A-Z): valor = charCodeAt(0) - 48
 *   - Caracteres numéricos (0-9): valor = parseInt(caractere)
 *
 * Algoritmo baseado na Nota Técnica da RFB para CNPJ alfanumérico.
 */
export function isValidCnpjAlfanumeric(cnpj: string): boolean {
    const limpo = cleanDocument(cnpj);
    if (limpo.length !== 14) return false;

    // Se for 100% numérico, não valida por aqui (usa o validador tradicional)
    if (/^\d{14}$/.test(limpo)) return false;

    const calcularDigito = (comprimento: number): number => {
        let soma = 0;
        let peso = comprimento - 7;
        for (let i = 0; i < comprimento; i++) {
            const char = limpo[i];
            const code = char.charCodeAt(0);
            const valor = code >= 65 && code <= 90 ? code - 48 : parseInt(char, 10);
            soma += valor * peso;
            peso--;
            if (peso < 2) peso = 9;
        }
        const resto = soma % 11;
        return resto < 2 ? 0 : 11 - resto;
    };

    const dv1 = calcularDigito(12);
    const dv2 = calcularDigito(13);

    return parseInt(limpo[12], 10) === dv1 && parseInt(limpo[13], 10) === dv2;
}

/**
 * Valida um CNPJ — tenta primeiro o formato numérico, depois o alfanumérico.
 */
export function isValidCnpj(cnpj: string): boolean {
    return isValidCnpjNumeric(cnpj) || isValidCnpjAlfanumeric(cnpj);
}

/**
 * Gera um CPF aleatório válido com dígitos verificadores corretos.
 */
export function generateValidCpf(): string {
    const n = Array.from({ length: 9 }, () => Math.floor(Math.random() * 10));

    let sum = 0;
    for (let i = 0; i < 9; i++) sum += n[i] * (10 - i);
    let remainder = sum % 11;
    n[9] = remainder < 2 ? 0 : 11 - remainder;

    sum = 0;
    for (let i = 0; i < 10; i++) sum += n[i] * (11 - i);
    remainder = sum % 11;
    n[10] = remainder < 2 ? 0 : 11 - remainder;

    return n.join('');
}

/**
 * Gera um CNPJ numérico aleatório válido com dígitos verificadores corretos.
 */
export function generateValidCnpj(): string {
    const raiz = [0, 0, 0, 1];
    const n = Array.from({ length: 8 }, () => Math.floor(Math.random() * 10));
    const base = [...n.slice(0, 8), ...raiz];

    const pesos1 = [5, 4, 3, 2, 9, 8, 7, 6, 5, 4, 3, 2];
    let d1 = 0;
    for (let i = 0; i < 12; i++) d1 += base[i] * pesos1[i];
    d1 = d1 % 11;
    d1 = d1 < 2 ? 0 : 11 - d1;

    const pesos2 = [6, 5, 4, 3, 2, 9, 8, 7, 6, 5, 4, 3, 2];
    let d2 = 0;
    for (let i = 0; i < 12; i++) d2 += base[i] * pesos2[i];
    d2 += d1 * pesos2[12];
    d2 = d2 % 11;
    d2 = d2 < 2 ? 0 : 11 - d2;

    return [...base, d1, d2].join('');
}

/**
 * Aplica máscara de formatação ao documento.
 *
 * - CPF: 000.000.000-00
 * - CNPJ (alfanumérico ou numérico): 00.000.000/0000-00
 */
export function applyDocMask(value: string, type: 'CPF' | 'CNPJ'): string {
    const clean = type === 'CPF' ? digitsOnly(value) : cleanDocument(value);

    if (type === 'CPF') {
        return clean
            .replace(/\D/g, '')
            .substring(0, 11)
            .replace(/(\d{3})(\d)/, '$1.$2')
            .replace(/(\d{3})(\d)/, '$1.$2')
            .replace(/(\d{3})(\d{1,2})$/, '$1-$2');
    }

    // CNPJ — suporta caracteres alfanuméricos
    return clean
        .substring(0, 14)
        .replace(/^([A-Z0-9]{2})([A-Z0-9])/, '$1.$2')
        .replace(/^([A-Z0-9]{2})\.([A-Z0-9]{3})([A-Z0-9])/, '$1.$2.$3')
        .replace(/\.([A-Z0-9]{3})([A-Z0-9])/, '.$1/$2')
        .replace(/([A-Z0-9]{4})([A-Z0-9]{1,2})$/, '$1-$2');
}

/**
 * Valida um documento com base no tipo informado.
 * Retorna uma mensagem de erro ou null se for válido.
 */
export function validateDoc(doc: string, docType: 'CPF' | 'CNPJ'): string | null {
    const clean = cleanDocument(doc);

    // Verifica se há caracteres inválidos (apenas para CNPJ alfanumérico)
    if (docType === 'CNPJ' && /[^A-Za-z0-9]/.test(doc.replace(/[.\-\/]/g, ''))) {
        return 'CNPJ contém caracteres inválidos. Use apenas letras (A-Z) e números.';
    }

    if (docType === 'CPF') {
        const digits = digitsOnly(doc);
        if (digits.length !== 11) {
            return 'CPF deve ter 11 dígitos.';
        }
        if (!isValidCpf(digits)) {
            return 'CPF inválido.';
        }
        return null;
    }

    // CNPJ
    if (clean.length !== 14) {
        return 'CNPJ deve ter 14 caracteres.';
    }
    if (!isValidCnpj(clean)) {
        return 'CNPJ inválido.';
    }
    return null;
}