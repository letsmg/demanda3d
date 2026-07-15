<?php
// Copyright (c) 2026 Luiz Eduardo T. Silva. Todos os direitos reservados.

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class StateSeeder extends Seeder
{
    /**
     * Faixas de CEP oficiais dos Correios por estado.
     *
     * @see https://www.correios.com.br/enviar/precificacao/cep-estrutura
     */
    public function run(): void
    {
        $states = [
            ['uf' => 'AC', 'name' => 'Acre',                'cep_start' => '69900-000', 'cep_end' => '69999-999'],
            ['uf' => 'AL', 'name' => 'Alagoas',              'cep_start' => '57000-000', 'cep_end' => '57999-999'],
            ['uf' => 'AP', 'name' => 'Amapá',                'cep_start' => '68900-000', 'cep_end' => '68999-999'],
            ['uf' => 'AM', 'name' => 'Amazonas',             'cep_start' => '69000-000', 'cep_end' => '69299-999'],
            ['uf' => 'AM', 'name' => 'Amazonas',             'cep_start' => '69400-000', 'cep_end' => '69899-999'],
            ['uf' => 'BA', 'name' => 'Bahia',                'cep_start' => '40000-000', 'cep_end' => '48999-999'],
            ['uf' => 'CE', 'name' => 'Ceará',                'cep_start' => '60000-000', 'cep_end' => '63999-999'],
            ['uf' => 'DF', 'name' => 'Distrito Federal',     'cep_start' => '70000-000', 'cep_end' => '72799-999'],
            ['uf' => 'DF', 'name' => 'Distrito Federal',     'cep_start' => '73000-000', 'cep_end' => '73699-999'],
            ['uf' => 'ES', 'name' => 'Espírito Santo',       'cep_start' => '29000-000', 'cep_end' => '29999-999'],
            ['uf' => 'GO', 'name' => 'Goiás',                'cep_start' => '72800-000', 'cep_end' => '72999-999'],
            ['uf' => 'GO', 'name' => 'Goiás',                'cep_start' => '73700-000', 'cep_end' => '76799-999'],
            ['uf' => 'MA', 'name' => 'Maranhão',             'cep_start' => '65000-000', 'cep_end' => '65999-999'],
            ['uf' => 'MT', 'name' => 'Mato Grosso',          'cep_start' => '78000-000', 'cep_end' => '78899-999'],
            ['uf' => 'MS', 'name' => 'Mato Grosso do Sul',   'cep_start' => '79000-000', 'cep_end' => '79999-999'],
            ['uf' => 'MG', 'name' => 'Minas Gerais',         'cep_start' => '30000-000', 'cep_end' => '39999-999'],
            ['uf' => 'PA', 'name' => 'Pará',                 'cep_start' => '66000-000', 'cep_end' => '68899-999'],
            ['uf' => 'PB', 'name' => 'Paraíba',              'cep_start' => '58000-000', 'cep_end' => '58999-999'],
            ['uf' => 'PR', 'name' => 'Paraná',               'cep_start' => '80000-000', 'cep_end' => '87999-999'],
            ['uf' => 'PE', 'name' => 'Pernambuco',           'cep_start' => '50000-000', 'cep_end' => '56999-999'],
            ['uf' => 'PI', 'name' => 'Piauí',                'cep_start' => '64000-000', 'cep_end' => '64999-999'],
            ['uf' => 'RJ', 'name' => 'Rio de Janeiro',       'cep_start' => '20000-000', 'cep_end' => '28999-999'],
            ['uf' => 'RN', 'name' => 'Rio Grande do Norte',  'cep_start' => '59000-000', 'cep_end' => '59999-999'],
            ['uf' => 'RS', 'name' => 'Rio Grande do Sul',    'cep_start' => '90000-000', 'cep_end' => '99999-999'],
            ['uf' => 'RO', 'name' => 'Rondônia',             'cep_start' => '76800-000', 'cep_end' => '76999-999'],
            ['uf' => 'RR', 'name' => 'Roraima',              'cep_start' => '69300-000', 'cep_end' => '69399-999'],
            ['uf' => 'SC', 'name' => 'Santa Catarina',       'cep_start' => '88000-000', 'cep_end' => '89999-999'],
            ['uf' => 'SP', 'name' => 'São Paulo',            'cep_start' => '01000-000', 'cep_end' => '19999-999'],
            ['uf' => 'SE', 'name' => 'Sergipe',              'cep_start' => '49000-000', 'cep_end' => '49999-999'],
            ['uf' => 'TO', 'name' => 'Tocantins',            'cep_start' => '77000-000', 'cep_end' => '77999-999'],
        ];

        $now = now();
        $rows = array_map(fn ($s) => array_merge($s, [
            'created_at' => $now,
            'updated_at' => $now,
        ]), $states);

        DB::table('states')->upsert($rows, ['uf', 'cep_start', 'cep_end'], ['name', 'updated_at']);
    }
}