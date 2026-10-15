<?php

namespace Tests\Feature;

use App\Filament\Admin\Widgets\BusinessOverview;
use App\Filament\Admin\Widgets\RecentOrders;
use App\Filament\Admin\Widgets\StockAlerts;
use App\Models\Category;
use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

/**
 * The admin dashboard widgets.
 *
 * The money assertions are the ones that matter. An order row exists before it
 * is paid for, so anything counting revenue has to filter on paid_at or it will
 * report abandoned checkouts as income.
 */
class DashboardWidgetsTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        \Filament\Facades\Filament::setCurrentPanel('admin');
        $this->actingAs(User::factory()->create(['is_admin' => true]));
    }

    protected function order(int $totalPence, ?string $paidDaysAgo, string $status = 'paid'): Order
    {
        $order = Order::create([
            'order_number' => 'T'.fake()->unique()->numberBetween(1000, 9999),
            'email' => 'buyer@example.test',
            'status' => $status,
            'currency' => 'GBP',
            'subtotal_pence' => $totalPence,
            'discount_pence' => 0,
            'shipping_pence' => 0,
            'total_pence' => $totalPence,
            'shipping_address' => ['full_name' => 'B', 'line1' => '1 St', 'city' => 'London', 'postcode' => 'E1 6AN', 'country' => 'GB'],
            'billing_address' => ['full_name' => 'B', 'line1' => '1 St', 'city' => 'London', 'postcode' => 'E1 6AN', 'country' => 'GB'],
        ]);

        if ($paidDaysAgo !== null) {
            $when = now()->subDays((int) $paidDaysAgo);
            $order->forceFill(['paid_at' => $when, 'created_at' => $when])->save();
        }

        return $order;
    }

    protected function product(string $name, int $stock, string $status = 'published'): Product
    {
        return Product::create([
            'category_id' => Category::firstOrCreate(['slug' => 'serums'], ['name' => 'Serums'])->id,
            'name' => $name,
            'slug' => str($name)->slug()->value(),
            'price_pence' => 1999,
            'stock_quantity' => $stock,
            'status' => $status,
        ]);
    }

    public function test_revenue_counts_only_orders_that_were_paid_for(): void
    {
        $this->order(5_000, paidDaysAgo: '3');
        $this->order(2_500, paidDaysAgo: '10');

        // Never paid — an abandoned checkout. Must not appear in revenue.
        $this->order(99_999, paidDaysAgo: null, status: 'pending');

        Livewire::test(BusinessOverview::class)
            ->assertSee(money(7_500))
            ->assertDontSee(money(107_499));
    }

    public function test_revenue_ignores_orders_outside_the_window(): void
    {
        $this->order(4_000, paidDaysAgo: '5');    // inside 30 days
        $this->order(9_000, paidDaysAgo: '45');   // outside

        Livewire::test(BusinessOverview::class)
            ->assertSee(money(4_000))
            ->assertDontSee(money(13_000));
    }

    public function test_the_overview_renders_with_no_data_at_all(): void
    {
        // A brand new shop must not show a broken dashboard.
        Livewire::test(BusinessOverview::class)
            ->assertOk()
            ->assertSee('Revenue, last 30 days')
            ->assertSee(money(0));
    }

    public function test_recent_orders_lists_newest_first_and_caps_at_ten(): void
    {
        foreach (range(1, 12) as $i) {
            $this->order(1_000 + $i, paidDaysAgo: (string) $i);
        }

        Livewire::test(RecentOrders::class)
            ->assertOk()
            ->assertCanSeeTableRecords(Order::query()->latest('created_at')->limit(10)->get())
            ->assertCanNotSeeTableRecords(Order::query()->oldest('created_at')->limit(2)->get());
    }

    public function test_stock_alerts_shows_low_and_out_of_stock_published_products(): void
    {
        $out = $this->product('Out Of Stock Serum', 0);
        $low = $this->product('Low Stock Serum', 3);
        $fine = $this->product('Well Stocked Serum', 50);

        Livewire::test(StockAlerts::class)
            ->assertOk()
            ->assertCanSeeTableRecords([$out, $low])
            ->assertCanNotSeeTableRecords([$fine]);
    }

    public function test_stock_alerts_ignores_drafts(): void
    {
        // A draft running low is not a problem, and listing drafts would bury
        // the products customers can actually try to buy.
        $draft = $this->product('Draft Serum', 0, status: 'draft');
        $published = $this->product('Published Serum', 0);

        Livewire::test(StockAlerts::class)
            ->assertCanSeeTableRecords([$published])
            ->assertCanNotSeeTableRecords([$draft]);
    }

    public function test_the_dashboard_page_loads_with_the_new_widgets(): void
    {
        $this->get('/admin')
            ->assertOk()
            ->assertSeeLivewire(BusinessOverview::class)
            ->assertSeeLivewire(RecentOrders::class)
            ->assertSeeLivewire(StockAlerts::class);
    }
}
