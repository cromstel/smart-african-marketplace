<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Product;
use App\Models\ProductQuestion;
use App\Models\User;
use App\Models\Vendor;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Carbon;
use Tests\TestCase;

class ProductQuestionTest extends TestCase
{
    use DatabaseTransactions;

    public function test_a_guest_question_can_be_created_for_a_product_and_persists(): void
    {
        $product = $this->makeProduct();

        $question = ProductQuestion::factory()->create([
            'product_id' => $product->id,
            'user_id' => null,
            'asker_name' => 'Guest Shopper',
            'asker_email' => 'guest.shopper@example.com',
            'question' => 'Is this product available?',
        ]);

        $this->assertDatabaseHas('product_questions', [
            'id' => $question->id,
            'product_id' => $product->id,
            'user_id' => null,
            'asker_name' => 'Guest Shopper',
            'asker_email' => 'guest.shopper@example.com',
            'question' => 'Is this product available?',
            'status' => 'pending',
        ]);

        $this->assertNull($question->fresh()->user_id);
    }

    public function test_an_authenticated_question_links_to_its_asker(): void
    {
        $product = $this->makeProduct();
        $user = User::factory()->create();

        $question = ProductQuestion::factory()->forUser($user)->create([
            'product_id' => $product->id,
        ]);

        $this->assertTrue($question->asker->is($user));
        $this->assertSame($user->id, $question->fresh()->asker->id);
    }

    public function test_a_product_questions_relationship_returns_only_its_questions(): void
    {
        $product = $this->makeProduct();
        $otherProduct = $this->makeProduct();

        $question = ProductQuestion::factory()->create(['product_id' => $product->id]);
        $otherQuestion = ProductQuestion::factory()->create(['product_id' => $otherProduct->id]);

        $this->assertTrue($product->questions->contains($question));
        $this->assertFalse($product->questions->contains($otherQuestion));
        $this->assertEqualsCanonicalizing(
            [$question->id],
            ProductQuestion::forProduct($product)->pluck('id')->all()
        );
        $this->assertEqualsCanonicalizing(
            [$question->id],
            ProductQuestion::forProduct($product->id)->pluck('id')->all()
        );
    }

    public function test_pending_scope_returns_only_pending_questions(): void
    {
        $product = $this->makeProduct();

        $pending = ProductQuestion::factory()->create(['product_id' => $product->id]);
        $pendingButPublic = ProductQuestion::factory()->publiclyVisible()->create([
            'product_id' => $product->id,
            'status' => 'pending',
        ]);
        $answered = ProductQuestion::factory()->answered()->create(['product_id' => $product->id]);
        $hidden = ProductQuestion::factory()->hidden()->create(['product_id' => $product->id]);

        $results = ProductQuestion::forProduct($product)->pending()->pluck('id')->all();

        $this->assertEqualsCanonicalizing([$pending->id, $pendingButPublic->id], $results);
        $this->assertNotContains($answered->id, $results);
        $this->assertNotContains($hidden->id, $results);
    }

    public function test_answered_scope_returns_only_answered_questions(): void
    {
        $product = $this->makeProduct();

        $pending = ProductQuestion::factory()->create(['product_id' => $product->id]);
        $answered = ProductQuestion::factory()->answered()->create(['product_id' => $product->id]);
        $hidden = ProductQuestion::factory()->hidden()->create(['product_id' => $product->id]);

        $results = ProductQuestion::forProduct($product)->answered()->pluck('id')->all();

        $this->assertEqualsCanonicalizing([$answered->id], $results);
        $this->assertNotContains($pending->id, $results);
        $this->assertNotContains($hidden->id, $results);
    }

    public function test_public_scope_returns_only_answered_and_public_questions(): void
    {
        $product = $this->makeProduct();

        $answeredAndPublic = ProductQuestion::factory()->publiclyVisible()->create([
            'product_id' => $product->id,
        ]);
        $answeredButPrivate = ProductQuestion::factory()->answered()->create([
            'product_id' => $product->id,
        ]);
        $pendingButPublic = ProductQuestion::factory()->publiclyVisible()->create([
            'product_id' => $product->id,
            'status' => 'pending',
        ]);
        $hiddenButPublic = ProductQuestion::factory()->hidden()->create([
            'product_id' => $product->id,
            'is_public' => true,
        ]);

        $results = ProductQuestion::forProduct($product)->public()->pluck('id')->all();

        $this->assertEqualsCanonicalizing([$answeredAndPublic->id], $results);
        $this->assertNotContains($answeredButPrivate->id, $results);
        $this->assertNotContains($pendingButPublic->id, $results);
        $this->assertNotContains($hiddenButPublic->id, $results);
    }

    public function test_for_vendor_scope_never_returns_another_vendors_questions(): void
    {
        [$vendorA] = $this->makeVendor('vendor-a');
        [$vendorB] = $this->makeVendor('vendor-b');

        $vendorAQuestion = ProductQuestion::factory()->create([
            'product_id' => $this->makeProduct($vendorA)->id,
        ]);
        $vendorBQuestion = ProductQuestion::factory()->create([
            'product_id' => $this->makeProduct($vendorB)->id,
        ]);

        $resultsByModel = ProductQuestion::forVendor($vendorA)->pluck('id')->all();
        $resultsById = ProductQuestion::forVendor($vendorA->id)->pluck('id')->all();

        $this->assertEqualsCanonicalizing([$vendorAQuestion->id], $resultsByModel);
        $this->assertEqualsCanonicalizing([$vendorAQuestion->id], $resultsById);
        $this->assertNotContains($vendorBQuestion->id, $resultsByModel);
        $this->assertNotContains($vendorBQuestion->id, $resultsById);
    }

    public function test_a_soft_deleted_question_is_excluded_from_normal_queries(): void
    {
        $product = $this->makeProduct();
        $question = ProductQuestion::factory()->create(['product_id' => $product->id]);

        $question->delete();

        $this->assertNull(ProductQuestion::find($question->id));
        $this->assertNotNull(ProductQuestion::withTrashed()->find($question->id));
        $this->assertSoftDeleted('product_questions', ['id' => $question->id]);
    }

    public function test_question_workflow_fields_are_mass_assignable_and_relations_are_hydrated(): void
    {
        $product = $this->makeProduct();
        $answeredBy = User::factory()->create();
        $moderator = User::factory()->create();
        $answeredAt = now();
        $moderatedAt = now()->addMinute();

        $question = ProductQuestion::factory()->create([
            'product_id' => $product->id,
            'status' => 'answered',
            'is_public' => true,
            'answer' => 'Yes, it is available.',
            'answered_by' => $answeredBy->id,
            'answered_at' => $answeredAt,
            'moderated_by' => $moderator->id,
            'moderated_at' => $moderatedAt,
            'moderation_note' => 'Approved for the product page.',
        ]);

        $fresh = $question->fresh();

        $this->assertDatabaseHas('product_questions', [
            'id' => $question->id,
            'status' => 'answered',
            'is_public' => true,
            'answer' => 'Yes, it is available.',
            'answered_by' => $answeredBy->id,
            'moderated_by' => $moderator->id,
            'moderation_note' => 'Approved for the product page.',
        ]);
        $this->assertTrue($fresh->is_public);
        $this->assertTrue($fresh->product->is($product));
        $this->assertTrue($fresh->answeredBy->is($answeredBy));
        $this->assertTrue($fresh->moderator->is($moderator));

        // Fill-guarding cannot be tested through a factory, because Laravel
        // wraps every factory make/create inside Model::unguarded(), which
        // makes every attribute fillable. A direct instantiation goes through
        // fill() with guards on, where a non-empty $fillable means
        // non-fillable keys are dropped by fillableFromArray() rather than
        // written. Asserting they stay null keeps a future unguarded model
        // from silently accepting a request-supplied primary key or timestamps.
        $guarded = new ProductQuestion([
            'id' => 99999,
            'created_at' => '2000-01-01 00:00:00',
            'question' => 'A fillable attribute reaches the model.',
        ]);
        $this->assertNull($guarded->id);
        $this->assertNull($guarded->created_at);
        $this->assertSame('A fillable attribute reaches the model.', $guarded->question);
    }

    public function test_deleting_a_related_user_nulls_the_foreign_key_and_keeps_the_question(): void
    {
        $product = $this->makeProduct();
        $asker = User::factory()->create();
        $answerer = User::factory()->create();
        $moderator = User::factory()->create();

        $question = ProductQuestion::factory()
            ->forUser($asker)
            ->create([
                'product_id' => $product->id,
                'answered_by' => $answerer->id,
                'moderated_by' => $moderator->id,
            ]);

        $answerer->delete();
        $moderator->delete();
        $asker->delete();

        $fresh = $question->fresh();

        // The question must survive every related-user deletion, with the
        // nullable foreign keys nulled rather than cascading the row away.
        $this->assertNotNull($fresh);
        $this->assertNull($fresh->user_id);
        $this->assertNull($fresh->answered_by);
        $this->assertNull($fresh->moderated_by);
        $this->assertNull($fresh->asker);
        $this->assertNull($fresh->answeredBy);
        $this->assertNull($fresh->moderator);
    }

    public function test_datetime_columns_are_cast_to_carbon_instances(): void
    {
        $product = $this->makeProduct();

        $question = ProductQuestion::factory()->create([
            'product_id' => $product->id,
            'answered_at' => now(),
            'moderated_at' => now(),
        ]);

        $fresh = $question->fresh();

        $this->assertInstanceOf(Carbon::class, $fresh->answered_at);
        $this->assertInstanceOf(Carbon::class, $fresh->moderated_at);
    }

    private function makeProduct(?Vendor $vendor = null): Product
    {
        $vendor ??= $this->makeVendor('product-owner')[0];

        $category = Category::create([
            'name' => 'Category '.uniqid(),
            'slug' => 'category-'.uniqid(),
        ]);

        return Product::create([
            'vendor_id' => $vendor->id,
            'category_id' => $category->id,
            'name' => 'Product '.uniqid(),
            'slug' => 'product-'.uniqid(),
            'sku' => 'SKU-'.strtoupper(uniqid()),
            'description' => 'Product question test fixture.',
            'price' => 25.00,
            'stock_quantity' => 10,
            'stock_status' => 'in_stock',
            'is_active' => true,
        ]);
    }

    /**
     * @return array{0: Vendor}
     */
    private function makeVendor(string $suffix): array
    {
        $user = User::factory()->vendor()->create();

        $vendor = Vendor::create([
            'user_id' => $user->id,
            'business_name' => 'Vendor '.$suffix.' '.uniqid(),
            'slug' => 'vendor-'.$suffix.'-'.uniqid(),
            'business_email' => 'vendor-'.$suffix.'-'.uniqid().'@example.com',
            'business_phone' => '+15550000000',
            'business_address' => '1 Test Street',
            'status' => 'approved',
            'is_active' => true,
        ]);

        return [$vendor->fresh()];
    }
}
