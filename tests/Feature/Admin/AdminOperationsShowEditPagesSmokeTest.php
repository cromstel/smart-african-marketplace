<?php

namespace Tests\Feature\Admin;

use App\Models\Auction;
use App\Models\AuctionBid;
use App\Models\Booking;
use App\Models\Campaign;
use App\Models\CampaignRecipient;
use App\Models\Category;
use App\Models\ChatbotMessage;
use App\Models\ChatbotSession;
use App\Models\MediaLibrary;
use App\Models\Salon;
use App\Models\SalonService;
use App\Models\SalonServiceCategory;
use App\Models\SalonStylist;
use App\Models\SupportMessage;
use App\Models\SupportTicket;
use App\Models\User;
use App\Models\Vendor;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;
use PHPUnit\Framework\Attributes\DataProvider;

/**
 * Extends the Phase 4 N+1 audit (see AdminIndexPagesSmokeTest) from admin
 * index pages to admin show/edit pages, covering the remaining "operations"
 * controllers: auctions, bookings, chatbot sessions, support tickets, media,
 * campaigns, and the salon family (salon/salon-services/salon-stylists).
 *
 * admin.auctions.edit surfaced a real bug: the route and controller method
 * existed, but resources/views/admin/auctions/edit.blade.php never did --
 * nothing in the admin UI actually links to it (the show page manages
 * everything via inline forms), so it 500'd unconditionally and had gone
 * unnoticed. A minimal edit view was added, modelled on the working
 * vendor.auctions.edit view.
 */
class AdminOperationsShowEditPagesSmokeTest extends TestCase
{
    use DatabaseTransactions;

    #[DataProvider('showEditRoutes')]
    public function test_admin_show_edit_page_loads_without_a_server_error(string $routeName, string $fixtureMethod): void
    {
        $admin = User::factory()->admin()->create();
        $routeParams = $this->$fixtureMethod($admin);

        $response = $this->actingAs($admin)->get(route($routeName, $routeParams));

        $response->assertOk();
    }

    public static function showEditRoutes(): array
    {
        return collect([
            'admin.auctions.edit' => 'auctionFixture',
            'admin.auctions.show' => 'auctionFixture',
            'admin.bookings.show' => 'bookingFixture',
            'admin.chatbot.sessions.show' => 'chatbotSessionFixture',
            'admin.support-tickets.show' => 'supportTicketFixture',
            'admin.media.edit' => 'mediaFixture',
            'admin.media.show' => 'mediaFixture',
            'admin.campaigns.edit' => 'campaignFixture',
            'admin.campaigns.show' => 'campaignFixture',
            'admin.salon.edit' => 'salonFixture',
            'admin.salon-services.edit' => 'salonServiceFixture',
            'admin.salon-stylists.edit' => 'salonStylistFixture',
        ])->map(fn ($fixtureMethod, $routeName) => [$routeName, $fixtureMethod])->all();
    }

    // --------------------------------------------------------------- Fixtures

    private function auctionFixture(): array
    {
        $vendorUser = User::factory()->vendor()->create();
        $vendor = Vendor::create([
            'user_id' => $vendorUser->id,
            'business_name' => 'Vendor '.uniqid(),
            'slug' => 'vendor-'.uniqid(),
            'business_email' => 'vendor'.uniqid().'@example.com',
            'business_phone' => '+15550000000',
            'business_address' => '1 Test Street',
            'status' => 'approved',
            'is_active' => true,
        ]);

        $bidder = User::factory()->create();

        $auction = Auction::create([
            'vendor_id' => $vendor->id,
            'title' => 'Auction '.uniqid(),
            'starting_price' => 100,
            'current_price' => 120,
            'bid_increment' => 10,
            'starts_at' => now()->subHour(),
            'ends_at' => now()->addDays(3),
            'status' => 'active',
            'type' => 'english',
            // Set so show()'s `winner.user` eager load actually has a row to
            // resolve -- winner() is a belongsTo(User), so this also proves
            // out the (slightly unusual) nested 'winner.user' path rather
            // than trivially passing on an always-null relation.
            'winner_id' => $bidder->id,
        ]);

        AuctionBid::create([
            'auction_id' => $auction->id,
            'user_id' => $bidder->id,
            'amount' => 120,
            'is_winning' => true,
        ]);

        return ['auction' => $auction];
    }

    private function bookingFixture(): array
    {
        $vendorUser = User::factory()->vendor()->create();
        $customer = User::factory()->create();

        $category = Category::create(['name' => 'Cat '.uniqid(), 'slug' => 'cat-'.uniqid()]);

        $service = SalonService::create([
            'user_id' => $vendorUser->id,
            'category_id' => $category->id,
            'name' => 'Service '.uniqid(),
            'slug' => 'service-'.uniqid(),
            'description' => 'Fixture service.',
            'price' => 50,
            'duration_minutes' => 30,
            'is_active' => true,
        ]);

        $booking = Booking::create([
            'booking_number' => 'BK-'.strtoupper(uniqid()),
            'user_id' => $customer->id,
            'salon_service_id' => $service->id,
            'salon_owner_id' => $vendorUser->id,
            'booking_date' => now()->addDay(),
            'booking_end_date' => now()->addDay()->addMinutes($service->duration_minutes),
            'duration_minutes' => $service->duration_minutes,
            'price' => $service->price,
            'status' => 'confirmed',
        ]);

        return ['booking' => $booking];
    }

    private function chatbotSessionFixture(User $admin): array
    {
        $customer = User::factory()->create();

        $session = ChatbotSession::create([
            'session_key' => 'session-'.uniqid(),
            'user_id' => $customer->id,
            'last_active_at' => now(),
            'handoff_active' => true,
            'assigned_agent_id' => $admin->id,
        ]);

        ChatbotMessage::create([
            'chatbot_session_id' => $session->id,
            'role' => 'user',
            'content' => 'Fixture chatbot message.',
        ]);

        return ['session' => $session];
    }

    private function supportTicketFixture(User $admin): array
    {
        $customer = User::factory()->create();

        $session = ChatbotSession::create([
            'session_key' => 'session-'.uniqid(),
            'user_id' => $customer->id,
            'last_active_at' => now(),
        ]);

        $ticket = SupportTicket::create([
            'chatbot_session_id' => $session->id,
            'user_id' => $customer->id,
            'assigned_agent_id' => $admin->id,
            'subject' => 'Fixture ticket',
            'status' => 'open',
            'last_message_at' => now(),
        ]);

        SupportMessage::create([
            'support_ticket_id' => $ticket->id,
            'author_role' => 'user',
            'author_id' => $customer->id,
            'content' => 'Fixture ticket message.',
        ]);

        return ['supportTicket' => $ticket];
    }

    private function mediaFixture(User $admin): array
    {
        $media = MediaLibrary::create([
            'title' => 'Fixture image',
            'file_name' => 'fixture-'.uniqid().'.jpg',
            'original_name' => 'fixture.jpg',
            'file_path' => 'media/fixture-'.uniqid().'.jpg',
            'mime_type' => 'image/jpeg',
            'file_size' => 12345,
            'type' => 'image',
            'uploaded_by_type' => 'App\\Models\\User',
            'uploaded_by_id' => $admin->id,
        ]);

        return ['media' => $media];
    }

    private function campaignFixture(User $admin): array
    {
        $campaign = Campaign::create([
            'name' => 'Campaign '.uniqid(),
            'subject' => 'Fixture subject',
            'content' => 'Fixture campaign content.',
            'created_by' => $admin->id,
        ]);

        CampaignRecipient::create([
            'campaign_id' => $campaign->id,
            'email' => 'recipient'.uniqid().'@example.com',
            'token' => bin2hex(random_bytes(16)),
            'status' => 'pending',
        ]);

        return ['campaign' => $campaign];
    }

    private function salonFixture(): array
    {
        $owner = User::factory()->vendor()->create();

        $salon = Salon::create([
            'user_id' => $owner->id,
            'name' => 'Salon '.uniqid(),
            'slug' => 'salon-'.uniqid(),
            'is_active' => true,
        ]);

        return ['salon' => $salon];
    }

    private function salonServiceFixture(): array
    {
        $owner = User::factory()->vendor()->create();
        $category = Category::create(['name' => 'Cat '.uniqid(), 'slug' => 'cat-'.uniqid()]);
        SalonServiceCategory::create([
            'name' => 'Salon Category '.uniqid(),
            'slug' => 'salon-category-'.uniqid(),
            'is_active' => true,
        ]);

        $salonService = SalonService::create([
            'user_id' => $owner->id,
            'category_id' => $category->id,
            'name' => 'Service '.uniqid(),
            'slug' => 'service-'.uniqid(),
            'description' => 'Fixture service.',
            'price' => 40.00,
            'duration_minutes' => 45,
            'is_active' => true,
        ]);

        return ['salon_service' => $salonService];
    }

    private function salonStylistFixture(): array
    {
        $owner = User::factory()->vendor()->create();

        $salon = Salon::create([
            'user_id' => $owner->id,
            'name' => 'Salon '.uniqid(),
            'slug' => 'salon-'.uniqid(),
            'is_active' => true,
        ]);

        $stylist = SalonStylist::create([
            'salon_id' => $salon->id,
            'name' => 'Stylist '.uniqid(),
            'is_active' => true,
        ]);

        return ['salon_stylist' => $stylist];
    }
}
