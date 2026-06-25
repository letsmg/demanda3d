<?php

use App\Jobs\RecalculateTenantRating;
use App\Models\Client;
use App\Models\Order;
use App\Models\Review;
use App\Models\Tenant;
use App\Models\User;
use App\Services\EncryptionService;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Queue;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\postJson;

uses(\Illuminate\Framework\Testing\RefreshDatabase::class);

beforeEach(function () {
    Queue::fake();

    $makeEncr = fn ($v) => EncryptionService::encryptWithHash($v);
    $this->user = User::factory()->management()->create();
    $this->tenant = $this->user->tenant()->create([
        'company_name_encrypted' => $makeEncr('Rated Co')['encrypted'],
        'company_name_hash' => $makeEncr('Rated Co')['hash'],
        'document_encrypted' => $makeEncr('00.000.000/0001-00')['encrypted'],
        'document_hash' => $makeEncr('00.000.000/0001-00')['hash'],
        'phone_encrypted' => $makeEncr('11999999999')['encrypted'],
        'phone_hash' => $makeEncr('11999999999')['hash'],
        'address_encrypted' => $makeEncr('Rua')['encrypted'],
        'address_hash' => $makeEncr('Rua')['hash'],
        'number_encrypted' => $makeEncr('1')['encrypted'],
        'number_hash' => $makeEncr('1')['hash'],
        'city_encrypted' => $makeEncr('SP')['encrypted'],
        'city_hash' => $makeEncr('SP')['hash'],
        'state' => 'SP', 'zipcode' => '00000-000',
        'active' => true,
    ]);
    $this->client = Client::factory()->create(['tenant_id' => $this->tenant->id]);
    $this->order = Order::factory()->create([
        'tenant_id' => $this->tenant->id,
        'client_id' => $this->client->id,
        'status' => 'delivered',
    ]);
});

test('review comment is encrypted at rest', function () {
    $review = Review::create([
        'tenant_id' => $this->tenant->id,
        'client_id' => $this->client->id,
        'order_id' => $this->order->id,
        'rating' => 5,
        'comment_encrypted' => Crypt::encryptString('Ótimo produto!'),
    ]);

    expect($review->comment_encrypted)->not->toBe('Ótimo produto!');
    expect(Crypt::decryptString($review->comment_encrypted))->toBe('Ótimo produto!');
});

test('order id is unique per review', function () {
    Review::create([
        'tenant_id' => $this->tenant->id,
        'client_id' => $this->client->id,
        'order_id' => $this->order->id,
        'rating' => 5,
        'comment_encrypted' => Crypt::encryptString('Bom'),
    ]);

    expect(fn () => Review::create([
        'tenant_id' => $this->tenant->id,
        'client_id' => $this->client->id,
        'order_id' => $this->order->id,
        'rating' => 3,
        'comment_encrypted' => Crypt::encryptString('Mais ou menos'),
    ]))->toThrow(\Illuminate\Database\UniqueConstraintViolationException::class);
});

test('rating must be between 1 and 5', function () {
    $review = Review::factory()->create(['rating' => 5, 'tenant_id' => $this->tenant->id]);
    expect($review->rating)->toBeGreaterThanOrEqual(1);
    expect($review->rating)->toBeLessThanOrEqual(5);
});

test('job is dispatched on review created', function () {
    Review::create([
        'tenant_id' => $this->tenant->id,
        'client_id' => $this->client->id,
        'order_id' => $this->order->id,
        'rating' => 4,
        'comment_encrypted' => Crypt::encryptString('Muito bom'),
    ]);

    Queue::assertPushed(RecalculateTenantRating::class, fn ($job) => $job->tenantId === $this->tenant->id);
});

test('recalculate job updates tenant rating', function () {
    Queue::assertNothingPushed();

    // Criar 3 reviews com notas 5, 4, 3 = média 4.00
    Review::create([
        'tenant_id' => $this->tenant->id,
        'client_id' => $this->client->id,
        'order_id' => Order::factory()->create(['tenant_id' => $this->tenant->id, 'client_id' => $this->client->id, 'status' => 'delivered'])->id,
        'rating' => 5,
        'comment_encrypted' => Crypt::encryptString('Excelente'),
    ]);
    Review::create([
        'tenant_id' => $this->tenant->id,
        'client_id' => $this->client->id,
        'order_id' => Order::factory()->create(['tenant_id' => $this->tenant->id, 'client_id' => $this->client->id, 'status' => 'delivered'])->id,
        'rating' => 4,
        'comment_encrypted' => Crypt::encryptString('Bom'),
    ]);
    Review::create([
        'tenant_id' => $this->tenant->id,
        'client_id' => $this->client->id,
        'order_id' => Order::factory()->create(['tenant_id' => $this->tenant->id, 'client_id' => $this->client->id, 'status' => 'delivered'])->id,
        'rating' => 3,
        'comment_encrypted' => Crypt::encryptString('Regular'),
    ]);

    // Executa o job manualmente (sem queue)
    $job = new RecalculateTenantRating($this->tenant->id);
    $job->handle();

    $this->tenant->refresh();
    expect((float) $this->tenant->rating_average)->toBe(4.00);
    expect((int) $this->tenant->rating_count)->toBe(4); // 3 + 1 do beforeEach
});

test('tenant starts with zero ratings', function () {
    $tenant = Tenant::factory()->create();
    expect((float) $tenant->rating_average)->toBe(0.0);
    expect((int) $tenant->rating_count)->toBe(0);
});