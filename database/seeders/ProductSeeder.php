<?php

namespace Database\Seeders;

use App\Models\Brand;
use App\Models\Category;
use App\Models\Product;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class ProductSeeder extends Seeder
{
    /**
     * Catalogue built around the supplied product photography.
     *
     * Every product carrying an `image` is the exact SKU shown in that photo,
     * under its real brand. Products without an `image` are catalogue filler so
     * category and brand pages are not empty — they intentionally fall back to
     * the brand mark on the product card rather than borrowing another SKU's
     * photograph, which would put the wrong bottle against the wrong name.
     *
     * Copy describes what is printed on each pack. Replace with the
     * manufacturer's own approved wording before launch.
     */
    public function run(): void
    {
        $categories = Category::pluck('id', 'name');
        $brands = Brand::pluck('id', 'name');

        $seen = [];

        foreach ($this->products() as $data) {
            $slug = Str::slug($data['name']);
            $seen[] = $slug;

            $product = Product::updateOrCreate(
                ['slug' => $slug],
                [
                    'category_id' => $categories[$data['category']] ?? null,
                    'brand_id' => $brands[$data['brand']] ?? null,
                    'name' => $data['name'],
                    'size' => $data['size'] ?? null,
                    'concern' => $data['concern'] ?? null,
                    'skin_type' => $data['skin_type'] ?? 'All skin types',
                    'key_active' => $data['key_active'] ?? null,
                    'short_description' => $data['short'],
                    'description' => $data['description'],
                    'how_to_use' => $data['how_to_use'] ?? null,
                    'ingredients_inci' => $this->placeholderInci($data['key_active'] ?? null),
                    'price_pence' => (int) round($data['price'] * 100),
                    'compare_at_price_pence' => isset($data['was']) ? (int) round($data['was'] * 100) : null,
                    'stock_quantity' => $data['stock'] ?? 30,
                    'status' => 'published',
                    'is_featured' => $data['featured'] ?? false,
                ]
            );

            if (! empty($data['image'])) {
                $product->images()->updateOrCreate(
                    ['sort_order' => 0],
                    ['path' => 'products/' . $data['image'], 'alt' => $data['name']]
                );
            } else {
                $product->images()->delete();
            }
        }

        // Remove the invented catalogue this replaced.
        Product::whereNotIn('slug', $seen)->each(function (Product $product) {
            $product->images()->delete();
            $product->delete();
        });

        // A brand is "featured" when it actually has something to sell, so the
        // homepage rail can never advertise an empty brand page.
        $stocked = Product::whereNotNull('brand_id')->where('status', 'published')->distinct()->pluck('brand_id');
        Brand::whereIn('id', $stocked)->update(['is_featured' => true]);
        Brand::whereNotIn('id', $stocked)->update(['is_featured' => false]);
    }

    protected function placeholderInci(?string $keyActive): string
    {
        $active = $keyActive ? Str::of($keyActive)->before(' ')->toString() : 'Niacinamide';

        return "Aqua, {$active}, Glycerin, Pentylene Glycol, Panthenol, Sodium Hyaluronate, Allantoin, Xanthan Gum, Citric Acid, Sodium Benzoate, Potassium Sorbate. (Placeholder — replace with the manufacturer's actual list before launch.)";
    }

    protected function products(): array
    {
        return [
            // ================= Photographed SKUs =================

            [
                'name' => 'Advanced Clinicals 10% Glycolic + Lactic Acid Exfoliating Body Cream',
                'brand' => 'Advanced Clinicals', 'category' => 'Body Lotions', 'size' => '454g / 16oz', 'price' => 18.50,
                'concern' => 'Uneven texture', 'key_active' => 'Glycolic + lactic acid 10%', 'skin_type' => 'All skin types',
                'featured' => true, 'image' => 'advanced-clinicals-glycolic-lactic-body-cream.webp',
                'short' => 'A 10% AHA body cream for rough, bumpy skin on arms, legs and elbows.',
                'description' => '<p>A dermatologist-tested body cream built around a 10% blend of glycolic and lactic acid, for skin that feels rough or looks bumpy from the neck down.</p><p>Rich enough to moisturise while it exfoliates, so you are not choosing between the two.</p>',
                'how_to_use' => 'Apply to clean, dry skin. Start every other day and build up as tolerated. Use SPF during the day.',
            ],
            [
                'name' => 'eos Shea Better 24H Moisture Body Lotion — Pomegranate Raspberry',
                'brand' => 'EOS', 'category' => 'Body Lotions', 'size' => '473ml / 16 fl oz', 'price' => 12.00,
                'concern' => 'Dryness', 'key_active' => 'Shea butter', 'skin_type' => 'Dry',
                'featured' => true, 'image' => 'eos-shea-better-body-lotion-pomegranate-raspberry.webp',
                'short' => '24-hour moisture from seven nourishing oils and butters, in pomegranate raspberry.',
                'description' => '<p>A shea butter body lotion with seven nourishing oils and butters, formulated for 24-hour moisture and soothing protection on dry skin.</p><p>Absorbs without the tacky finish that puts people off richer lotions.</p>',
                'how_to_use' => 'Apply generously all over, ideally straight after showering while skin is still damp.',
            ],
            [
                'name' => 'eos Shea Better 24H Moisture Body Lotion — Vanilla Cashmere',
                'brand' => 'EOS', 'category' => 'Body Lotions', 'size' => '473ml / 16 fl oz', 'price' => 12.00,
                'concern' => 'Dryness', 'key_active' => 'Shea butter', 'skin_type' => 'Dry',
                'featured' => false, 'image' => 'eos-shea-better-body-lotion-vanilla-cashmere.webp',
                'short' => 'The same 24-hour shea formula, in a warmer vanilla cashmere scent.',
                'description' => '<p>Seven nourishing oils and butters for 24-hour moisture, this time in a soft vanilla cashmere scent.</p><p>Same fast-absorbing finish as the rest of the Shea Better range.</p>',
                'how_to_use' => 'Apply generously all over, ideally straight after showering while skin is still damp.',
            ],
            [
                'name' => 'Olay Brightening Exfoliating Cleanser with Vitamin C',
                'brand' => 'Olay', 'category' => 'Skincare', 'size' => '150ml / 5.0 fl oz', 'price' => 9.50,
                'concern' => 'Dullness', 'key_active' => 'Vitamin C', 'skin_type' => 'All skin types',
                'featured' => true, 'image' => 'olay-brightening-exfoliating-cleanser.webp',
                'short' => 'A vitamin C face cleanser that exfoliates as it washes, for dull, tired skin.',
                'description' => '<p>An exfoliating daily cleanser with vitamin C, aimed at skin that reads dull or tired.</p><p>Sweeps away the day and leaves skin feeling fresh rather than stripped.</p>',
                'how_to_use' => 'Massage onto damp skin, avoiding the eye area, then rinse thoroughly.',
            ],
            [
                'name' => 'Olay Dark Spot Correcting Body Lotion',
                'brand' => 'Olay', 'category' => 'Body Lotions', 'size' => '502ml / 17 fl oz', 'price' => 15.00, 'was' => 18.00,
                'concern' => 'Dark spots', 'key_active' => 'AHA + Vitamin C', 'skin_type' => 'All skin types',
                'featured' => true, 'image' => 'olay-dark-spot-correcting-body-lotion.webp',
                'short' => 'An AHA and vitamin C body lotion targeting dark spots, with visible results in four weeks.',
                'description' => '<p>A body lotion pairing AHA with vitamin C to work on the look of dark spots and uneven tone below the neck.</p><p>Moisturises daily while it works, so it slots into a routine you already have.</p>',
                'how_to_use' => 'Apply daily to clean skin. Use sunscreen during the day while using AHAs.',
            ],
            [
                'name' => 'Abib Glutathiosome Dark Spot Serum Vita Drop',
                'brand' => 'Abib', 'category' => 'Serums', 'size' => '50ml', 'price' => 26.00,
                'concern' => 'Dark spots', 'key_active' => 'Glutathione', 'skin_type' => 'All skin types',
                'featured' => true, 'image' => 'abib-glutathiosome-dark-spot-serum.webp',
                'short' => 'A glutathione and niacinamide serum in a capsule-suspended vita drop texture.',
                'description' => '<p>A dark spot serum built on glutathione and niacinamide, suspended in a lightweight drop texture with visible capsules.</p><p>Aimed at uneven tone and the marks left behind after breakouts settle.</p>',
                'how_to_use' => 'Apply a few drops to clean skin morning and night, before moisturiser.',
            ],
            [
                'name' => 'Medix 5.5 Niacinamide + Shea Butter Body Lotion',
                'brand' => 'Medix 5.5', 'category' => 'Body Lotions', 'size' => '444ml / 15 fl oz', 'price' => 16.00,
                'concern' => 'Dryness', 'key_active' => 'Niacinamide', 'skin_type' => 'Dry',
                'featured' => false, 'image' => 'medix-niacinamide-shea-butter-body-lotion.webp',
                'short' => 'A nourishing niacinamide and shea butter body lotion for dry, uneven skin.',
                'description' => '<p>Niacinamide paired with shea butter in a body lotion aimed at dryness and uneven tone.</p><p>Part of the Medix 5.5 body range, formulated around a skin-friendly pH.</p>',
                'how_to_use' => 'Massage into clean skin daily, morning or night.',
            ],
            [
                'name' => 'Balance Active Formula Niacinamide Blemish Recovery Serum 15%',
                'brand' => 'Balance', 'category' => 'Serums', 'size' => '30ml / 1 fl oz', 'price' => 11.00,
                'concern' => 'Breakouts', 'key_active' => 'Niacinamide 15%', 'skin_type' => 'Oily',
                'featured' => true, 'image' => 'balance-niacinamide-blemish-recovery-serum.webp',
                'short' => 'A 15% niacinamide serum for blemish-prone skin, targeting texture and tone.',
                'description' => '<p>Fifteen percent active niacinamide in a lightweight serum, aimed at blemish-prone skin.</p><p>On-pack claims focus on targeting blemishes, improving skin texture and calming the skin.</p>',
                'how_to_use' => 'Apply to clean skin morning and night, before heavier serums and moisturiser.',
            ],
            [
                'name' => 'Olay Tone Brightening Serum',
                'brand' => 'Olay', 'category' => 'Serums', 'size' => '40ml / 1.3 fl oz', 'price' => 22.00,
                'concern' => 'Dullness', 'key_active' => 'Vitamin C', 'skin_type' => 'All skin types',
                'featured' => false, 'image' => 'olay-tone-brightening-serum.webp',
                'short' => 'A lightweight brightening serum for uneven tone and lacklustre skin.',
                'description' => '<p>A brightening face serum aimed at uneven tone, in a light texture that layers under moisturiser and SPF without pilling.</p>',
                'how_to_use' => 'Apply to clean skin morning and night before moisturiser.',
            ],
            [
                'name' => 'Medix 5.5 Vitamin C + Niacinamide Body Lotion',
                'brand' => 'Medix 5.5', 'category' => 'Body Lotions', 'size' => '444ml / 15 fl oz', 'price' => 16.00,
                'concern' => 'Dullness', 'key_active' => 'Vitamin C', 'skin_type' => 'All skin types',
                'featured' => false, 'image' => 'medix-vitamin-c-niacinamide-body-lotion.webp',
                'short' => 'Vitamin C and niacinamide in a body lotion built for dull, uneven skin tone.',
                'description' => '<p>A brightening body treatment pairing vitamin C with niacinamide, for skin that looks dull or uneven from the neck down.</p>',
                'how_to_use' => 'Apply daily to clean skin. Follow with SPF on exposed areas.',
            ],
            [
                'name' => 'Olay Retinol24 Smoothing Body Lotion',
                'brand' => 'Olay', 'category' => 'Body Lotions', 'size' => '502ml / 17 fl oz', 'price' => 17.00,
                'concern' => 'Fine lines', 'key_active' => 'Retinol', 'skin_type' => 'All skin types',
                'featured' => false, 'image' => 'olay-retinol24-smoothing-body-lotion.webp',
                'short' => 'A retinol body lotion that keeps working across 24 hours, for smoother-looking skin.',
                'description' => '<p>Olay Retinol24 in a body format: a smoothing lotion designed to hydrate across a full 24 hours while retinol works on texture and the look of fine lines.</p>',
                'how_to_use' => 'Apply at night to clean skin. Introduce gradually and use SPF during the day.',
            ],
            [
                'name' => 'Alpha Arbutin 3+ Collagen Soap',
                'brand' => 'Alpha Arbutin', 'category' => 'Bath & Body', 'size' => '100g', 'price' => 6.50,
                'concern' => 'Dark spots', 'key_active' => 'Alpha arbutin 3%', 'skin_type' => 'All skin types',
                'featured' => false, 'image' => 'alpha-arbutin-collagen-soap.webp',
                'short' => 'A cleansing bar with alpha arbutin and collagen, aimed at uneven tone.',
                'description' => '<p>A body cleansing bar combining alpha arbutin with collagen, aimed at dark spots and uneven tone.</p><p>A low-commitment way to work brightening actives into a shower routine.</p>',
                'how_to_use' => 'Lather onto damp skin in the shower, leave briefly, then rinse well.',
            ],
            [
                'name' => 'Glow Booster Lightening Oil SPF 30',
                'brand' => 'Glow influence', 'category' => 'Bath & Body', 'size' => '300ml / 11 fl oz', 'price' => 14.00,
                'concern' => 'Dark spots', 'key_active' => 'Alpha arbutin + kojic acid', 'skin_type' => 'All skin types',
                'featured' => false, 'image' => 'glow-booster-lightening-oil.webp',
                'short' => 'A hydroquinone-free body oil with alpha arbutin and kojic acid, at SPF 30.',
                'description' => '<p>A body oil enriched with alpha arbutin and kojic acid at SPF 30, marked hydroquinone free.</p><p>On-pack claims cover visibly reducing dark spots, evening skin tone and illuminating the complexion.</p>',
                'how_to_use' => 'Smooth over the body after showering. Reapply sun protection through the day as needed.',
            ],
            [
                'name' => 'Jumiso Niacinamide 20 Serum',
                'brand' => 'Jumiso', 'category' => 'Serums', 'size' => '30ml', 'price' => 19.00,
                'concern' => 'Dark spots', 'key_active' => 'Niacinamide 20%', 'skin_type' => 'All skin types',
                'featured' => true, 'image' => 'jumiso-niacinamide-20-serum.webp',
                'short' => 'A high-strength 20% niacinamide serum for dark spots and hyperpigmentation.',
                'description' => '<p>Twenty percent niacinamide in a dark spot correcting serum, aimed at hyperpigmentation and post-blemish marks.</p><p>A high dose — worth patch testing if niacinamide is new to your routine.</p>',
                'how_to_use' => 'Apply to clean skin at night. Patch test first and build up frequency slowly.',
            ],
            [
                'name' => 'Dr. Althea Pro Lab Gentle Vitamin C Serum 20%',
                'brand' => 'Dr. Althea', 'category' => 'Serums', 'size' => '30ml', 'price' => 28.00,
                'concern' => 'Dullness', 'key_active' => 'Vitamin C 20%', 'skin_type' => 'Sensitive',
                'featured' => true, 'image' => 'dr-althea-gentle-vitamin-c-serum.webp',
                'short' => 'A 20% vitamin C serum built on sea buckthorn fruit water rather than plain water.',
                'description' => '<p>A gentle-format vitamin C serum at 20%, formulated with Hippophae Rhamnoides (sea buckthorn) fruit water in place of a plain aqua base.</p><p>Aimed at people who want a high-dose vitamin C without the sting.</p>',
                'how_to_use' => 'Apply in the morning to clean skin, then follow with moisturiser and SPF.',
            ],
            [
                'name' => 'Medix 5.5 Retinol + Hyaluronic Acid Body Lotion',
                'brand' => 'Medix 5.5', 'category' => 'Body Lotions', 'size' => '444ml / 15 fl oz', 'price' => 16.00,
                'concern' => 'Fine lines', 'key_active' => 'Retinol', 'skin_type' => 'All skin types',
                'featured' => false, 'image' => 'medix-retinol-hyaluronic-body-lotion.webp',
                'short' => 'Retinol and hyaluronic acid in an age-rejuvenating body treatment.',
                'description' => '<p>Retinol paired with hyaluronic acid in a body lotion aimed at crepey texture and the look of fine lines.</p>',
                'how_to_use' => 'Apply at night to clean skin. Introduce slowly and use SPF during the day.',
            ],

            // ================= Awaiting photography =================
            // Real SKUs, no supplied photograph. The product card falls back to
            // the brand mark rather than showing another product's bottle.

            [
                'name' => 'CosRX Advanced Snail 96 Mucin Power Essence',
                'brand' => 'Cosrx', 'category' => 'Serums', 'size' => '100ml', 'price' => 21.00,
                'concern' => 'Dryness', 'key_active' => 'Snail secretion filtrate 96%', 'skin_type' => 'All skin types',
                'short' => 'A 96% snail mucin essence for hydration and barrier support.',
                'description' => '<p>A lightweight essence built around 96% snail secretion filtrate, used for hydration and to support a stressed barrier.</p>',
                'how_to_use' => 'Pat onto clean, damp skin before serums and moisturiser.',
            ],
            [
                'name' => 'Beauty of Joseon Relief Sun Rice + Probiotics SPF50+',
                'brand' => 'Beauty of Joseon', 'category' => 'Skincare', 'size' => '50ml', 'price' => 15.00,
                'concern' => null, 'key_active' => 'SPF50+ PA++++', 'skin_type' => 'All skin types',
                'short' => 'A lightweight organic rice and probiotics sunscreen at SPF50+ PA++++.',
                'description' => '<p>A daily chemical sunscreen at SPF50+ PA++++, formulated with rice extract and probiotics in a texture light enough to wear under makeup.</p>',
                'how_to_use' => 'Apply as the last step of your morning routine. Reapply through the day.',
            ],
            [
                'name' => 'The Ordinary Niacinamide 10% + Zinc 1%',
                'brand' => 'The Ordinary', 'category' => 'Serums', 'size' => '30ml', 'price' => 6.00,
                'concern' => 'Breakouts', 'key_active' => 'Niacinamide 10%', 'skin_type' => 'Oily',
                'short' => 'A high-strength niacinamide and zinc serum for blemish-prone, congested skin.',
                'description' => '<p>Ten percent niacinamide with one percent zinc PCA, aimed at the look of blemishes and congestion.</p>',
                'how_to_use' => 'Apply a few drops to clean skin morning and night before heavier creams.',
            ],
            [
                'name' => 'La Roche-Posay Cicaplast Baume B5+',
                'brand' => 'La Roche-Posay', 'category' => 'Skincare', 'size' => '40ml', 'price' => 13.50,
                'concern' => 'Sensitivity', 'key_active' => 'Panthenol 5%', 'skin_type' => 'Sensitive',
                'short' => 'A soothing multi-purpose balm for dry, irritated and compromised skin.',
                'description' => '<p>A repairing balm with 5% panthenol and madecassoside, for skin that is dry, irritated or recovering from over-exfoliation.</p>',
                'how_to_use' => 'Apply a thin layer to affected areas as often as needed.',
            ],
            [
                'name' => 'Anua Heartleaf 77% Soothing Toner',
                'brand' => 'Anua', 'category' => 'Facial Toners', 'size' => '250ml', 'price' => 18.00,
                'concern' => 'Sensitivity', 'key_active' => 'Houttuynia cordata 77%', 'skin_type' => 'Sensitive',
                'short' => 'A heartleaf toner for redness-prone skin that reacts to most things.',
                'description' => '<p>A soothing toner built on 77% heartleaf extract, aimed at calming redness and general reactivity.</p>',
                'how_to_use' => 'Sweep or pat over clean skin morning and night before serums.',
            ],
            [
                'name' => 'Isntree Hyaluronic Acid Toner',
                'brand' => 'Isntree', 'category' => 'Facial Toners', 'size' => '200ml', 'price' => 17.00,
                'concern' => 'Dryness', 'key_active' => 'Hyaluronic acid', 'skin_type' => 'Dry',
                'short' => 'A hydrating hyaluronic acid toner that layers well under everything else.',
                'description' => '<p>A watery hyaluronic acid toner formulated for layering, for skin that drinks up hydration and asks for more.</p>',
                'how_to_use' => 'Pat into damp skin, layering two or three times if skin is very dry.',
            ],
            [
                'name' => 'Acwell Licorice pH Balancing Cleansing Toner',
                'brand' => 'Acwell', 'category' => 'Facial Toners', 'size' => '150ml', 'price' => 19.00,
                'concern' => 'Dullness', 'key_active' => 'Licorice water', 'skin_type' => 'All skin types',
                'short' => 'A low-pH licorice toner that balances skin after cleansing.',
                'description' => '<p>A pH-balancing toner built on licorice water, used to reset skin after cleansing and prep it for the rest of a routine.</p>',
                'how_to_use' => 'Apply with hands or a cotton pad straight after cleansing.',
            ],
            [
                'name' => 'Some By Mi AHA BHA PHA 30 Days Miracle Toner',
                'brand' => 'Some By Mi', 'category' => 'Facial Toners', 'size' => '150ml', 'price' => 16.00,
                'concern' => 'Breakouts', 'key_active' => 'AHA / BHA / PHA', 'skin_type' => 'Oily',
                'short' => 'A gently exfoliating acid toner for congested, breakout-prone skin.',
                'description' => '<p>A daily exfoliating toner combining AHA, BHA and PHA with tea tree water, aimed at congestion and breakouts.</p>',
                'how_to_use' => 'Apply with a cotton pad in the evening. Start a few nights a week.',
            ],
            [
                'name' => 'Cosrx Low pH Good Morning Gel Cleanser',
                'brand' => 'Cosrx', 'category' => 'Skincare', 'size' => '150ml', 'price' => 11.00,
                'concern' => 'Sensitivity', 'key_active' => 'Tea tree oil', 'skin_type' => 'All skin types',
                'short' => 'A low-pH morning gel cleanser that will not strip your barrier.',
                'description' => '<p>A mild, low-pH gel cleanser with tea tree oil and botanical extracts, formulated for daily use on skin that dislikes foaming washes.</p>',
                'how_to_use' => 'Massage onto damp skin, then rinse with lukewarm water.',
            ],
            [
                'name' => 'Illiyoon Ceramide Ato Concentrate Cream',
                'brand' => 'Illiyoon', 'category' => 'Skincare', 'size' => '200ml', 'price' => 16.00,
                'concern' => 'Dryness', 'key_active' => 'Ceramides', 'skin_type' => 'Dry',
                'short' => 'A fragrance-free ceramide cream for sensitive, very dry skin.',
                'description' => '<p>A concentrated ceramide cream for skin barrier care, fragrance free and suitable for sensitive and dry skin on face and body.</p>',
                'how_to_use' => 'Apply generously wherever skin feels tight, morning and night.',
            ],
            [
                'name' => 'Numbuzin No.3 Skin Softening Serum',
                'brand' => 'Numbuzin', 'category' => 'Serums', 'size' => '50ml', 'price' => 24.00,
                'concern' => 'Uneven texture', 'key_active' => 'Niacinamide', 'skin_type' => 'All skin types',
                'short' => 'A softening serum for rough texture and enlarged-looking pores.',
                'description' => '<p>A lightweight serum aimed at rough texture and the look of pores, in the layerable format the Numbuzin range is built around.</p>',
                'how_to_use' => 'Apply to clean skin morning and night before moisturiser.',
            ],
            [
                'name' => 'Medicube Zero Pore Pad 2.0',
                'brand' => 'Medicube', 'category' => 'Facial Toners', 'size' => '70 pads', 'price' => 23.00,
                'concern' => 'Uneven texture', 'key_active' => 'AHA / BHA', 'skin_type' => 'Oily',
                'short' => 'Exfoliating toner pads for congestion, blackheads and rough texture.',
                'description' => '<p>Pre-soaked exfoliating pads combining AHA and BHA, aimed at blackheads, congestion and the look of pores.</p>',
                'how_to_use' => 'Sweep one pad over clean skin in the evening, two or three times a week.',
            ],
            [
                'name' => 'Missha Time Revolution The First Treatment Essence',
                'brand' => 'Missha', 'category' => 'Serums', 'size' => '150ml', 'price' => 32.00,
                'concern' => 'Fine lines', 'key_active' => 'Fermented yeast ferment', 'skin_type' => 'All skin types',
                'short' => 'A fermented first-step essence for softness, tone and fine lines.',
                'description' => '<p>A first-treatment essence built on fermented yeast ferment filtrate, applied straight after cleansing to prep skin for everything that follows.</p>',
                'how_to_use' => 'Pat into clean skin as the first step after cleansing.',
            ],
            [
                'name' => 'Tree Hut Shea Sugar Scrub — Coco Colada',
                'brand' => 'Tree Hut', 'category' => 'Bath & Body', 'size' => '510g / 18oz', 'price' => 11.50,
                'concern' => 'Uneven texture', 'key_active' => 'Shea butter', 'skin_type' => 'All skin types',
                'short' => 'A shea sugar body scrub that leaves skin smooth without a greasy film.',
                'description' => '<p>A certified shea butter sugar scrub for the shower, buffing away rough texture while leaving skin soft rather than slippery.</p>',
                'how_to_use' => 'Massage over damp skin in the shower, then rinse thoroughly.',
            ],
            [
                'name' => "Dr Teal's Pure Epsom Salt Soaking Solution — Lavender",
                'brand' => 'Dr Teals', 'category' => 'Bath & Body', 'size' => '1.36kg / 3lb', 'price' => 8.50,
                'concern' => null, 'key_active' => 'Magnesium sulphate', 'skin_type' => 'All skin types',
                'short' => 'Pure epsom salt with lavender essential oil, for a soak that actually helps.',
                'description' => '<p>Pure epsom salt combined with lavender essential oil, for soaking tired muscles at the end of a long week.</p>',
                'how_to_use' => 'Dissolve two cups under warm running water and soak for 20 minutes.',
            ],
            [
                'name' => 'Vaseline Intensive Care Advanced Repair Lotion',
                'brand' => 'Vaseline', 'category' => 'Body Lotions', 'size' => '400ml', 'price' => 6.00,
                'concern' => 'Dryness', 'key_active' => 'Glycerin', 'skin_type' => 'Dry',
                'short' => 'A fragrance-free everyday body lotion for very dry skin.',
                'description' => '<p>A fragrance-free body lotion with micro-droplets of Vaseline jelly, for skin that needs reliable daily moisture without any scent.</p>',
                'how_to_use' => 'Apply daily, ideally straight after showering.',
            ],
            [
                'name' => 'Aveeno Daily Moisturising Lotion',
                'brand' => 'Aveeno', 'category' => 'Body Lotions', 'size' => '300ml', 'price' => 8.00,
                'concern' => 'Sensitivity', 'key_active' => 'Colloidal oatmeal', 'skin_type' => 'Sensitive',
                'short' => 'A colloidal oatmeal lotion for dry, easily irritated skin.',
                'description' => '<p>A daily body lotion built on colloidal oatmeal, formulated for dry and sensitive skin and suitable for everyday use.</p>',
                'how_to_use' => 'Apply generously as often as needed, especially after bathing.',
            ],
            [
                'name' => 'Nivea Soft Refreshing Moisturising Cream',
                'brand' => 'Nivea', 'category' => 'Body Lotions', 'size' => '200ml', 'price' => 5.00,
                'concern' => 'Dryness', 'key_active' => 'Jojoba oil + Vitamin E', 'skin_type' => 'All skin types',
                'short' => 'A light all-purpose cream for face, hands and body.',
                'description' => '<p>A light, fast-absorbing cream with jojoba oil and vitamin E, intended for face, hands and body alike.</p>',
                'how_to_use' => 'Apply wherever skin feels dry, as often as needed.',
            ],
            [
                'name' => 'Garnier Vitamin C Brightening Serum',
                'brand' => 'Garnier', 'category' => 'Serums', 'size' => '30ml', 'price' => 12.00,
                'concern' => 'Dark spots', 'key_active' => 'Vitamin C', 'skin_type' => 'All skin types',
                'short' => 'An accessible daily vitamin C serum for dark spots and dullness.',
                'description' => '<p>A vitamin C serum with niacinamide and salicylic acid, aimed at dark spots and overall dullness at an everyday price.</p>',
                'how_to_use' => 'Apply in the morning to clean skin, then follow with SPF.',
            ],
            [
                'name' => 'Panoxyl Acne Foaming Wash 4% Benzoyl Peroxide',
                'brand' => 'Panoxyl', 'category' => 'Skincare', 'size' => '156g', 'price' => 12.50,
                'concern' => 'Breakouts', 'key_active' => 'Benzoyl peroxide 4%', 'skin_type' => 'Oily',
                'short' => 'A 4% benzoyl peroxide foaming wash for persistent breakouts.',
                'description' => '<p>A foaming acne wash with 4% benzoyl peroxide, the gentler of the two Panoxyl strengths, for face, chest and back.</p>',
                'how_to_use' => 'Lather on wet skin, leave for one to two minutes, then rinse. Bleaches fabric — use white towels.',
            ],
            [
                'name' => 'Good Molecules Discoloration Correcting Serum',
                'brand' => 'Good Molecules', 'category' => 'Serums', 'size' => '30ml', 'price' => 10.00,
                'concern' => 'Dark spots', 'key_active' => 'Tranexamic acid', 'skin_type' => 'All skin types',
                'short' => 'A tranexamic and niacinamide serum for stubborn discolouration.',
                'description' => '<p>A lightweight serum pairing tranexamic acid with niacinamide, aimed at post-inflammatory marks and stubborn discolouration.</p>',
                'how_to_use' => 'Apply morning and night to clean skin before moisturiser.',
            ],
            [
                'name' => 'Axis-Y Dark Spot Correcting Glow Serum',
                'brand' => 'Axis-Y', 'category' => 'Serums', 'size' => '50ml', 'price' => 20.00,
                'concern' => 'Dark spots', 'key_active' => 'Niacinamide 5%', 'skin_type' => 'All skin types',
                'short' => 'A squalane and niacinamide serum for dark spots and uneven glow.',
                'description' => '<p>A dark spot serum combining niacinamide with squalane and papaya extract, in a texture that leans hydrating rather than drying.</p>',
                'how_to_use' => 'Apply to clean skin morning and night.',
            ],
            [
                'name' => 'Haruharu Wonder Black Rice Hyaluronic Toner',
                'brand' => 'Haruharu', 'category' => 'Facial Toners', 'size' => '150ml', 'price' => 18.50,
                'concern' => 'Dryness', 'key_active' => 'Black rice extract', 'skin_type' => 'Sensitive',
                'short' => 'A naturally fermented black rice toner for hydration and calm.',
                'description' => '<p>A hydrating toner built on naturally fermented black rice extract, formulated without artificial fragrance for sensitive skin.</p>',
                'how_to_use' => 'Pat into clean skin morning and night.',
            ],
            [
                'name' => 'Mary & May Idebenone + Blackberry Complex Serum',
                'brand' => 'Mary & May', 'category' => 'Serums', 'size' => '30ml', 'price' => 25.00,
                'concern' => 'Fine lines', 'key_active' => 'Idebenone', 'skin_type' => 'All skin types',
                'short' => 'An antioxidant serum pairing idebenone with blackberry complex.',
                'description' => '<p>An antioxidant-led serum combining idebenone with blackberry extract, aimed at dullness and early fine lines.</p>',
                'how_to_use' => 'Apply to clean skin at night before moisturiser.',
            ],
        ];
    }
}
