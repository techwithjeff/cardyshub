@php
    $support = \App\Models\Setting::get('contact_email', 'hello@cardyshub.com');
@endphp
<x-policy-page
    title="Delivery"
    eyebrow="Getting it to you"
    intro="We dispatch from the UK and want your order with you quickly, safely and without any surprises at checkout."
    :requires="[]"
    :toc="[
        'options' => 'Options and pricing',
        'dispatch' => 'Dispatch times',
        'tracking' => 'Tracking your order',
        'where' => 'Where we deliver',
        'addresses' => 'Delivery addresses',
        'problems' => 'If something goes wrong',
        'packaging' => 'Packaging',
    ]">

    <h2 id="options">Options and pricing</h2>

    {{-- Driven from App\Support\Delivery::methods(), the same list the checkout
         renders, so this table cannot drift from what customers are charged. --}}
    <div class="policy-table-scroll">
        <table>
            <thead>
                <tr><th>Service</th><th>Cost</th><th>Estimated arrival</th></tr>
            </thead>
            <tbody>
                @foreach(App\Support\Delivery::methods() as $method)
                    <tr>
                        <td>{{ $method->name }}@if($method->isStandard) <em>(standard)</em>@endif</td>
                        <td>{{ $method->priceLabel() }}</td>
                        <td>{{ $method->arrival }}</td>
                    </tr>
                @endforeach
                <tr>
                    <td>{{ App\Support\Delivery::standardMethod()->name }} on orders over {{ App\Support\Delivery::freeThreshold() }}</td>
                    <td><strong>Free</strong></td>
                    <td>{{ App\Support\Delivery::standardMethod()->arrival }}</td>
                </tr>
            </tbody>
        </table>
    </div>

    <p>Free delivery over {{ App\Support\Delivery::freeThreshold() }} applies to {{ App\Support\Delivery::standardMethod()->name }}. You can still choose any other service on those orders, and you pay only that service's price.</p>

    <p>All prices include VAT where applicable. Delivery is chosen and shown at checkout before you pay, so you always see the total before confirming.</p>

    <h2 id="collection">Collection</h2>

    <p><strong>Prefer to collect? {{ App\Support\Delivery::pickupMethod()?->name }} is free.</strong> Choose it at checkout instead of a postage option and there is nothing to pay for delivery. No address is needed &mdash; just your name, email and a phone number if you would like one on the order.</p>

    <p>Collect from <strong>{{ App\Support\Delivery::pickupAddress() }}</strong>. We will email you as soon as your order is ready, usually within 2 working days. Please wait for that email before travelling, so you are not making the trip for something still being packed.</p>

    <h2 id="dispatch">Dispatch times</h2>

    {{-- Deliberately not the site tagline. This section is about speed, so the
         lead-in promises speed; "Your Glow starts here" lives in the banner. --}}
    <p><strong>Order today, glow tomorrow.</strong> UK orders placed before {{ App\Support\Delivery::cutOffTime() }}, Monday to Friday, arrive the next working day.</p>

    <p>Orders placed after <strong>{{ App\Support\Delivery::cutOffTime() }}</strong>, at weekends, or on UK bank holidays are dispatched on the next working day and arrive the day after that.</p>

    <p>Working days are Monday to Friday, excluding bank holidays. We do not deliver on Saturdays or Sundays, so an order placed on Friday afternoon arrives on Monday.</p>

    <p>During sale periods and around Christmas, dispatch can take an extra day. We will always say so on the site when this applies.</p>

    <h2 id="tracking">Tracking your order</h2>

    <p>You will receive two emails from us:</p>

    <ol>
        <li><strong>Order confirmation</strong>, immediately after you pay, listing what you bought.</li>
        <li><strong>Dispatch confirmation</strong>, once your parcel leaves us, with a tracking number and link.</li>
    </ol>

    <p>Tracking can take a few hours to activate after you receive the email. If it has not updated within 24 hours, please get in touch.</p>

    <p>You can also see the status of any order by signing in to your account and opening <a href="{{ route('account.orders.index') }}">My Orders</a>.</p>

    <h2 id="where">Where we deliver</h2>

    <p><strong>We ship worldwide.</strong> Wherever you are, we will get your routine to you.</p>

    <p>Across the United Kingdom that includes Northern Ireland, the Scottish Highlands and Islands, the Isle of Man and the Channel Islands. Some remote postcodes take an additional working day. This is a carrier limitation rather than a delay at our end.</p>

    <p>International orders are sent by tracked airmail. Delivery times vary by destination, typically 5 to 10 working days to Europe and 7 to 14 working days further afield. Customs clearance can add to this and is outside our control.</p>

    <p><strong>Duties and import taxes.</strong> Orders outside the UK may attract customs duty or import VAT on arrival, charged by your own country. These are payable by you, are not included in the price you pay us, and we cannot predict or refund them.</p>

    <h2 id="addresses">Delivery addresses</h2>

    <p>Please check your address carefully before confirming your order. Once a parcel has been dispatched we cannot redirect it.</p>

    <p>We can deliver to home addresses, workplaces and most parcel collection points. We cannot deliver to PO Boxes.</p>

    <p>If nobody is available, the carrier will either leave your parcel in a safe place, leave it with a neighbour, or take it to a local collection point. The card or notification they leave will tell you which.</p>

    <h2 id="problems">If something goes wrong</h2>

    <p><strong>Parcel marked delivered but not received.</strong> Check with neighbours and around your property first, then contact us at <a href="mailto:{{ $support }}">{{ $support }}</a> within 14 days of the delivery date and we will open an investigation with the carrier.</p>

    <p><strong>Parcel delayed.</strong> Occasionally carriers run behind. If your parcel has not arrived within 7 working days of dispatch, tell us and we will chase it or send a replacement.</p>

    <p><strong>Parcel damaged.</strong> Take a photograph of the packaging and the products before disposing of anything, and email it to us within 48 hours of delivery. We will replace the affected items.</p>

    <h2 id="packaging">Packaging</h2>

    <p>We pack in recyclable materials wherever possible. Boxes, paper padding and cartons can go in your household recycling. Where we use protective materials that cannot be recycled at kerbside, we say so on the insert.</p>

    <p class="mt-10"><a href="{{ route('returns') }}">Returns and refunds</a> &middot; <a href="{{ route('faqs') }}">Frequently asked questions</a> &middot; <a href="{{ route('contact') }}">Contact us</a></p>

</x-policy-page>
