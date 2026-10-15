<?php

namespace Database\Seeders;

use App\Models\Setting;
use Illuminate\Database\Seeder;

class SettingsSeeder extends Seeder
{
    public function run(): void
    {
        Setting::set('site_tagline', 'Let your skin do the talking');
        Setting::set('homepage_hero_heading', 'Skincare that actually fits your skin');
        Setting::set('homepage_hero_subheading', 'Built by someone who spent years on trial and error, so you do not have to. Honest formulas, clear instructions, and a routine that makes sense.');

        Setting::set('about_founder_name', 'Abibat Otaobayomi Cardoso');
        Setting::set('about_founder_photo', 'images/founder.jpeg');
        Setting::set('about_founder_body', <<<'HTML'
<p>My name is Abibat Otaobayomi Cardoso, a Business Administration graduate of the University of Lagos (UNILAG) and the founder of Cardyshub.uk.</p>
<p>My skincare journey began with my own experience. After years of trying different products through plenty of trial and error, I discovered just how much the right skincare products could transform not only my skin, but also the way I felt about myself. That experience inspired me to create something that could help others on their own skincare journeys.</p>
<p>I launched Cardyshub.uk in March 2025, with so much love, passion and a genuine desire to help people feel better and more confident in their skin. I believe skincare is more than just products&mdash;it's about self-care, confidence and feeling comfortable in the skin you're in.</p>
<p>Cardyshub.uk was created to make that journey a little easier, more enjoyable and more empowering. 💕</p>
HTML);

        Setting::set('contact_email', 'hello@cardyshub.com');
        Setting::set('contact_phone', '');

        // Where new-order alerts go. setIfMissing, not set: once the owner
        // changes this in the admin, re-seeding on a deploy must not silently
        // put the old address back. Blank switches the alerts off.
        Setting::setIfMissing('order_notification_email', 'cardysplace1@gmail.com');

        // Drives the floating WhatsApp button. Stored in international format;
        // the layout strips everything but the digits for the wa.me link. Leave
        // this blank to hide the button entirely.
        Setting::set('whatsapp_number', '+447448255453');

        // ---- Policy page values -------------------------------------------
        // Blank entries are the ones only the client can supply. The policy
        // pages render a visible "to confirm" marker wherever a value is still
        // blank, rather than printing an invented figure or address.
        Setting::setIfMissing('wholesale_email', 'wholesales@cardyshub.com');
        Setting::setIfMissing('privacy_email', '');

        // 'ltd' or 'sole_trader'. Drives which "About us" clause the Terms show.
        Setting::setIfMissing('legal_entity_type', 'sole_trader');
        Setting::setIfMissing('legal_returns_address', '');
        Setting::setIfMissing('legal_ico_number', '');

        // Plain pound amounts. App\Support\Delivery is the single reader for
        // these: checkout, the cart progress bar and the copy all derive from it.
        Setting::setIfMissing('delivery_free_threshold', '100');
        Setting::setIfMissing('delivery_cutoff_time', '5pm');

        // Royal Mail rate card, per service offered at checkout. Update these
        // when Royal Mail repricing lands and the checkout, the Delivery page
        // table and every FAQ follow automatically.
        Setting::setIfMissing('delivery_rate_1st_class', '5.09');
        Setting::setIfMissing('delivery_rate_2nd_class', '3.99');
        Setting::setIfMissing('delivery_rate_signed_1st', '6.59');
        Setting::setIfMissing('delivery_rate_signed_2nd', '5.49');
        Setting::setIfMissing('delivery_rate_guaranteed_9am', '46.63');
        Setting::setIfMissing('delivery_rate_guaranteed_9am_sat', '49.13');
        Setting::setIfMissing('delivery_rate_guaranteed_1pm', '10.25');
        Setting::setIfMissing('delivery_rate_guaranteed_1pm_sat', '12.75');
        Setting::setIfMissing('delivery_rate_tracked_24', '4.50');
        Setting::setIfMissing('delivery_rate_tracked_24_sig', '5.75');
        Setting::setIfMissing('delivery_rate_tracked_48', '3.54');
        Setting::setIfMissing('delivery_rate_tracked_48_sig', '4.79');

        // Superseded by the per-service rates above. Removed so nothing can
        // quietly read a stale £3 "standard" that no service charges any more.
        Setting::query()->whereIn('key', ['delivery_standard_rate', 'delivery_express_rate'])->delete();

        Setting::setIfMissing('wholesale_moq', '30 units');

        // Shown as "Last updated" on Terms, Privacy and Cookies.
        Setting::setIfMissing('policy_last_updated', '21 August 2026');

        Setting::set('social_facebook', 'https://facebook.com/cardyshub.uk');
        Setting::set('social_instagram', 'https://instagram.com/cardyshub.uk');
        Setting::set('social_tiktok', 'https://tiktok.com/@cardyshub.uk');

        // No X account, so the row is cleared rather than left behind: seeding
        // is re-run on deploys, and a stale value would put the icon back the
        // moment anyone re-added it to a template.
        Setting::query()->where('key', 'social_x')->delete();
    }
}
