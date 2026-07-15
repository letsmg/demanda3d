<?php

use App\Models\State;
use Illuminate\Support\Facades\DB;

beforeEach(function () {
    // Garante que a tabela states existe e está populada (StateSeeder já executado)
    if (State::count() === 0) {
        $this->artisan('db:seed', ['--class' => 'StateSeeder']);
    }
});

// ══════════════════════════════════════════════════════════
// INTEGRIDADE DOS DADOS
// ══════════════════════════════════════════════════════════
test('todos os 27 estados brasileiros estão cadastrados', function () {
    $ufs = State::select('uf')->distinct()->pluck('uf')->toArray();

    expect($ufs)->toHaveCount(27);
});

test('cada UF possui pelo menos uma faixa de CEP', function () {
    $distinctUfs = State::select('uf')->distinct()->count();

    // Deve haver 27 UFs distintas (ou mais, considerando múltiplas faixas por UF)
    expect($distinctUfs)->toBe(27);

    // Cada UF deve ter no mínimo 1 registro
    $grouped = State::select('uf', DB::raw('COUNT(*) as faixas'))
        ->groupBy('uf')
        ->pluck('faixas', 'uf');

    foreach ($grouped as $uf => $count) {
        expect($count)->toBeGreaterThanOrEqual(1, "UF {$uf} deveria ter pelo menos 1 faixa de CEP");
    }
});

test('faixas de CEP são válidas e não invertidas', function () {
    $states = State::all();

    foreach ($states as $state) {
        // cep_start deve ser <= cep_end
        $start = (int) str_replace('-', '', $state->cep_start);
        $end   = (int) str_replace('-', '', $state->cep_end);

        expect($start)->toBeLessThanOrEqual($end, "Faixa invertida em {$state->uf}: {$state->cep_start} > {$state->cep_end}");
    }
});

test('faixas de CEP não se sobrepõem na mesma UF', function () {
    $ufs = State::select('uf')->distinct()->pluck('uf')->toArray();

    foreach ($ufs as $uf) {
        $ranges = State::where('uf', $uf)
            ->orderBy('cep_start')
            ->get()
            ->map(fn ($s) => [
                'start' => (int) str_replace('-', '', $s->cep_start),
                'end'   => (int) str_replace('-', '', $s->cep_end),
            ])
            ->toArray();

        // Se há mais de uma faixa para a mesma UF, elas não devem sobrepor
        for ($i = 0; $i < count($ranges); $i++) {
            for ($j = $i + 1; $j < count($ranges); $j++) {
                // range[i] termina antes de range[j] começar?
                $noOverlap = $ranges[$i]['end'] < $ranges[$j]['start'];
                expect($noOverlap)->toBeTrue(
                    "Faixas sobrepostas em {$uf}: [{$ranges[$i]['start']}-{$ranges[$i]['end']}] e [{$ranges[$j]['start']}-{$ranges[$j]['end']}]"
                );
            }
        }
    }
});

// ══════════════════════════════════════════════════════════
// BUSCA POR CEP
// ══════════════════════════════════════════════════════════
test('encontra UF correta para CEPs conhecidos', function () {
    // CEP de São Paulo capital: 01000-000 → SP
    $cepSP = '01000-000';
    $foundSP = State::where('cep_start', '<=', $cepSP)
        ->where('cep_end', '>=', $cepSP)
        ->first();

    expect($foundSP)->not->toBeNull();
    expect($foundSP->uf)->toBe('SP');

    // CEP do Rio de Janeiro: 20000-000 → RJ
    $cepRJ = '20000-000';
    $foundRJ = State::where('cep_start', '<=', $cepRJ)
        ->where('cep_end', '>=', $cepRJ)
        ->first();

    expect($foundRJ)->not->toBeNull();
    expect($foundRJ->uf)->toBe('RJ');
});

test('CEP inexistente não retorna nenhum estado', function () {
    // CEP fora de qualquer faixa brasileira
    $found = State::where('cep_start', '<=', '00000-000')
        ->where('cep_end', '>=', '00000-000')
        ->first();

    expect($found)->toBeNull();
});

// ══════════════════════════════════════════════════════════
// IDEMPOTÊNCIA DO SEEDER (upsert)
// ══════════════════════════════════════════════════════════
test('seeder de estados é idempotente — executar duas vezes não duplica registros', function () {
    $countBefore = State::count();

    // Executa o seeder novamente
    $this->artisan('db:seed', ['--class' => 'StateSeeder']);

    $countAfter = State::count();

    expect($countAfter)->toBe($countBefore);
});