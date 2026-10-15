<?php

namespace App\Filament\Admin\Widgets;

use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Carbon;

/**
 * The four numbers worth seeing before anything else.
 *
 * Money only ever counts orders that were actually paid for. An order row is
 * created before payment, so counting every row would inflate revenue with
 * abandoned checkouts.
 */
class BusinessOverview extends StatsOverviewWidget
{
    protected static ?int $sort = 1;

    protected int | string | array $columnSpan = 'full';

    /** Refreshed on load rather than polled: this is a shop, not a trading desk. */
    protected ?string $pollingInterval = null;

    protected function getStats(): array
    {
        $now = Carbon::now();
        $periodStart = $now->copy()->subDays(30);
        $priorStart = $now->copy()->subDays(60);

        $paid = fn () => Order::query()->whereNotNull('paid_at');

        $revenue = (int) $paid()->where('paid_at', '>=', $periodStart)->sum('total_pence');
        $priorRevenue = (int) $paid()->whereBetween('paid_at', [$priorStart, $periodStart])->sum('total_pence');

        $orders = $paid()->where('paid_at', '>=', $periodStart)->count();
        $priorOrders = $paid()->whereBetween('paid_at', [$priorStart, $periodStart])->count();

        $customers = User::query()->where('is_admin', false)->count();
        $newCustomers = User::query()->where('is_admin', false)->where('created_at', '>=', $periodStart)->count();

        $awaitingPayment = Order::query()->where('status', 'pending')->count();
        $toFulfil = Order::query()->whereIn('status', ['paid', 'processing'])->count();

        $outOfStock = Product::query()->where('status', 'published')->where('stock_quantity', '<=', 0)->count();
        $lowStock = Product::query()->where('status', 'published')->whereBetween('stock_quantity', [1, 5])->count();

        return [
            Stat::make('Revenue, last 30 days', money($revenue))
                ->description($this->changeLabel($revenue, $priorRevenue, 'vs previous 30 days'))
                ->descriptionIcon($this->trendIcon($revenue, $priorRevenue))
                ->color($this->trendColour($revenue, $priorRevenue))
                ->chart($this->dailyRevenueSeries($periodStart)),

            Stat::make('Paid orders, last 30 days', (string) $orders)
                ->description($this->changeLabel($orders, $priorOrders, 'vs previous 30 days'))
                ->descriptionIcon($this->trendIcon($orders, $priorOrders))
                ->color($this->trendColour($orders, $priorOrders))
                ->chart($this->dailyOrderSeries($periodStart)),

            Stat::make('Customers', (string) $customers)
                ->description($newCustomers === 1 ? '1 new in the last 30 days' : "{$newCustomers} new in the last 30 days")
                ->descriptionIcon('heroicon-m-users')
                ->color($newCustomers > 0 ? 'success' : 'gray'),

            $this->attentionStat($awaitingPayment, $toFulfil, $outOfStock, $lowStock),
        ];
    }

    /**
     * One stat that answers "is there anything I need to do right now?".
     */
    protected function attentionStat(int $awaitingPayment, int $toFulfil, int $outOfStock, int $lowStock): Stat
    {
        $lines = [];

        if ($toFulfil > 0) {
            $lines[] = $toFulfil.' to fulfil';
        }

        if ($outOfStock > 0) {
            $lines[] = $outOfStock.' out of stock';
        }

        if ($lowStock > 0) {
            $lines[] = $lowStock.' low on stock';
        }

        if ($awaitingPayment > 0) {
            $lines[] = $awaitingPayment.' awaiting payment';
        }

        if ($lines === []) {
            return Stat::make('Needs attention', 'Nothing')
                ->description('No orders to fulfil, nothing out of stock')
                ->descriptionIcon('heroicon-m-check-circle')
                ->color('success');
        }

        return Stat::make('Needs attention', (string) ($toFulfil + $outOfStock))
            ->description(implode(' · ', $lines))
            ->descriptionIcon('heroicon-m-exclamation-triangle')
            ->color($outOfStock > 0 || $toFulfil > 0 ? 'warning' : 'gray');
    }

    /** @return array<int, int> */
    protected function dailyRevenueSeries(Carbon $from): array
    {
        return $this->dailySeries($from, fn ($orders) => $orders->sum('total_pence'));
    }

    /** @return array<int, int> */
    protected function dailyOrderSeries(Carbon $from): array
    {
        return $this->dailySeries($from, fn ($orders) => $orders->count());
    }

    /**
     * A 30-point series for the stat sparkline, one value per day.
     *
     * Bucketed in PHP rather than with SQL date functions, which differ between
     * MySQL and the SQLite the tests run on. Thirty days of orders is a trivial
     * amount of data to group in memory, and days with no orders still need a
     * zero so the sparkline keeps its shape.
     *
     * @param  callable(\Illuminate\Support\Collection): (int|float)  $aggregate
     * @return array<int, int>
     */
    protected function dailySeries(Carbon $from, callable $aggregate): array
    {
        $byDay = Order::query()
            ->whereNotNull('paid_at')
            ->where('paid_at', '>=', $from)
            ->get(['paid_at', 'total_pence'])
            ->groupBy(fn ($order) => Carbon::parse($order->paid_at)->toDateString());

        $series = [];

        for ($i = 0; $i < 30; $i++) {
            $day = $from->copy()->addDays($i)->toDateString();
            $series[] = (int) $aggregate($byDay->get($day, collect()));
        }

        return $series;
    }

    protected function changeLabel(int|float $current, int|float $prior, string $suffix): string
    {
        if ($prior <= 0) {
            return $current > 0 ? 'First activity in this period' : 'No activity yet';
        }

        $change = round((($current - $prior) / $prior) * 100);
        $sign = $change > 0 ? '+' : '';

        return "{$sign}{$change}% {$suffix}";
    }

    protected function trendIcon(int|float $current, int|float $prior): string
    {
        return $current >= $prior ? 'heroicon-m-arrow-trending-up' : 'heroicon-m-arrow-trending-down';
    }

    protected function trendColour(int|float $current, int|float $prior): string
    {
        if ($current === $prior) {
            return 'gray';
        }

        return $current > $prior ? 'success' : 'danger';
    }
}
