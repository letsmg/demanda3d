<?php

/**
 * Configuração de níveis de segurança de entrega — Demanda3D
 *
 * Regras por faixa de preço:
 * - tier_1: até R$ 100,00 → foto do pacote + número da casa/prédio
 * - tier_2: R$ 100,01 a R$ 200,00 → foto do pacote + foto da casa + número do documento
 * - tier_3: acima de R$ 200,00 OU categoria +18 → foto do documento com data de nascimento
 *
 * Tipos de verificação:
 * - photo_package: foto do pacote
 * - photo_house_number: foto do número da casa/prédio
 * - photo_house: foto da casa
 * - doc_number: número do documento
 * - doc_photo_birthdate: foto do documento com data de nascimento
 */

return [
    'tiers' => [
        'tier_1' => [
            'price_max'     => 100.00,
            'requirements'  => ['photo_package', 'photo_house_number'],
        ],
        'tier_2' => [
            'price_min'     => 100.01,
            'price_max'     => 200.00,
            'requirements'  => ['photo_package', 'photo_house', 'doc_number'],
        ],
        'tier_3' => [
            'price_min'     => 200.01,
            'adult_category' => true,
            'requirements'  => ['doc_photo_birthdate'],
        ],
    ],

    'bloqueio_automatico_fraude' => 3,
];