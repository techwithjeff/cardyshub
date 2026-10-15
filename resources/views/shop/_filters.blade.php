{{--
    Shared filter form fields, included in both the desktop sidebar (naturally nested
    inside #shop-filter-form) and the mobile drawer (teleported to <body> by x-drawer,
    so every control needs an explicit form="" attribute to stay associated with it).
--}}

<div class="space-y-8">
    <div>
        <h3 class="font-mono text-xs uppercase tracking-[0.06em] text-ink-soft mb-3">Category</h3>
        <ul class="space-y-2 text-sm">
            <li>
                <label class="flex items-center gap-2 cursor-pointer {{ request()->missing('category') ? 'text-pink font-medium' : 'text-ink' }}">
                    <input type="radio" name="category" value="" form="shop-filter-form" @checked(request()->missing('category')) onchange="this.form.submit()" class="text-pink-btn focus:ring-pink">
                    All products
                </label>
            </li>
            @foreach($categories as $category)
                <li>
                    <label class="flex items-center gap-2 cursor-pointer {{ request('category') === $category->slug ? 'text-pink font-medium' : 'text-ink' }}">
                        <input type="radio" name="category" value="{{ $category->slug }}" form="shop-filter-form" @checked(request('category') === $category->slug) onchange="this.form.submit()" class="text-pink-btn focus:ring-pink">
                        {{ $category->name }} <span class="text-ink-faint">({{ $category->products_count }})</span>
                    </label>
                </li>
            @endforeach
        </ul>
    </div>

    @if($brands->isNotEmpty())
    <div>
        <h3 class="font-mono text-xs uppercase tracking-[0.06em] text-ink-soft mb-3">Brand</h3>
        <ul class="space-y-2 text-sm">
            <li>
                <label class="flex items-center gap-2 cursor-pointer {{ request()->missing('brand') ? 'text-pink font-medium' : 'text-ink' }}">
                    <input type="radio" name="brand" value="" form="shop-filter-form" @checked(request()->missing('brand')) onchange="this.form.submit()" class="text-pink-btn focus:ring-pink">
                    All brands
                </label>
            </li>
            @foreach($brands as $brand)
                <li>
                    <label class="flex items-center gap-2 cursor-pointer {{ request('brand') === $brand->slug ? 'text-pink font-medium' : 'text-ink' }}">
                        <input type="radio" name="brand" value="{{ $brand->slug }}" form="shop-filter-form" @checked(request('brand') === $brand->slug) onchange="this.form.submit()" class="text-pink-btn focus:ring-pink">
                        {{ $brand->name }} <span class="text-ink-faint">({{ $brand->products_count }})</span>
                    </label>
                </li>
            @endforeach
        </ul>
    </div>
    @endif

    @if($concerns->isNotEmpty())
    <div>
        <h3 class="font-mono text-xs uppercase tracking-[0.06em] text-ink-soft mb-3">Concern</h3>
        <ul class="space-y-2 text-sm">
            @foreach($concerns as $concern)
                {{-- Compared folded, not exact: the active concern in the URL may
                     be spelled differently from the label, and an exact match
                     would leave nothing highlighted. --}}
                @php $isActive = mb_strtolower(trim((string) request('concern'))) === mb_strtolower($concern['label']); @endphp
                <li>
                    <label class="flex items-center gap-2 cursor-pointer {{ $isActive ? 'text-pink font-medium' : 'text-ink' }}">
                        <input type="radio" name="concern" value="{{ $concern['label'] }}" form="shop-filter-form" @checked($isActive) onchange="this.form.submit()" class="text-pink-btn focus:ring-pink">
                        {{ $concern['label'] }}
                        <span class="text-ink-faint">{{ $concern['count'] }}</span>
                    </label>
                </li>
            @endforeach
        </ul>
    </div>
    @endif

    @if($skinTypes->isNotEmpty())
    <div>
        <h3 class="font-mono text-xs uppercase tracking-[0.06em] text-ink-soft mb-3">Skin type</h3>
        <ul class="space-y-2 text-sm">
            @foreach($skinTypes as $skinType)
                <li>
                    <label class="flex items-center gap-2 cursor-pointer {{ request('skin_type') === $skinType ? 'text-pink font-medium' : 'text-ink' }}">
                        <input type="radio" name="skin_type" value="{{ $skinType }}" form="shop-filter-form" @checked(request('skin_type') === $skinType) onchange="this.form.submit()" class="text-pink-btn focus:ring-pink">
                        {{ $skinType }}
                    </label>
                </li>
            @endforeach
        </ul>
    </div>
    @endif

    <div>
        <h3 class="font-mono text-xs uppercase tracking-[0.06em] text-ink-soft mb-3">Price</h3>
        <div class="flex items-center gap-2">
            <input type="number" name="min_price" value="{{ request('min_price') }}" placeholder="£ Min" min="0" form="shop-filter-form"
                   class="w-full rounded border-line text-sm focus:border-pink focus:ring-pink">
            <span class="text-ink-faint">&ndash;</span>
            <input type="number" name="max_price" value="{{ request('max_price') }}" placeholder="£ Max" min="0" form="shop-filter-form"
                   class="w-full rounded border-line text-sm focus:border-pink focus:ring-pink">
        </div>
    </div>

    <div>
        <label class="flex items-center gap-2 cursor-pointer text-sm text-ink">
            <input type="checkbox" name="in_stock" value="1" form="shop-filter-form" @checked(request()->boolean('in_stock')) onchange="this.form.submit()" class="rounded border-line text-pink-btn focus:ring-pink">
            In stock only
        </label>
    </div>
</div>
