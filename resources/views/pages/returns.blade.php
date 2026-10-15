@php
    $support = \App\Models\Setting::get('contact_email', 'hello@cardyshub.com');
@endphp
<x-policy-page
    title="Returns and refunds"
    eyebrow="Where you stand"
    intro="We want you to be pleased with what you have bought. If something is not right, here is exactly where you stand."
    :requires="['legal_returns_address']"
    :toc="[
        'sealed' => 'Sealed skincare',
        'changing' => 'Changing your mind',
        'faulty' => 'Faulty or incorrect items',
        'reactions' => 'Reactions and sensitivity',
        'refunds' => 'How refunds are paid',
        'cancelling' => 'Cancelling before dispatch',
        'exchanges' => 'Exchanges',
        'address' => 'Where to send returns',
        'excluded' => 'Items we cannot accept',
    ]">

    <h2 id="sealed">Please read this first: sealed skincare</h2>

    <p>Skincare is a hygiene product, and this affects your return rights in a specific way.</p>

    <p><strong>Unopened and sealed:</strong> you have a full right to change your mind and return the item.</p>

    <p><strong>Opened or seal broken:</strong> for hygiene reasons we cannot accept a return simply because you have changed your mind. This is permitted under the Consumer Contracts (Information, Cancellation and Additional Charges) Regulations 2013, which exempt sealed goods that are not suitable for return once unsealed for reasons of health protection or hygiene.</p>

    <p><strong>This does not affect your rights if a product is faulty, damaged or not as described.</strong> Those rights apply whether the product is sealed or not, and are set out further down this page.</p>

    <h2 id="changing">Changing your mind</h2>

    <p>You have <strong>14 days from the day you receive your order</strong> to tell us you wish to cancel, and a further <strong>14 days</strong> from telling us to send the goods back.</p>

    <p>To qualify, items must be:</p>

    <ul>
        <li>Unopened, with any seal, shrink wrap or tamper band intact</li>
        <li>In their original packaging</li>
        <li>In a resaleable condition</li>
    </ul>

    <p>To start a return, email <a href="mailto:{{ $support }}">{{ $support }}</a> with your order number and which items you are returning, or use the returns section in <a href="{{ route('account.orders.index') }}">your account</a>.</p>

    <p>Return postage for a change of mind is paid by you. We recommend a tracked service, because the parcel remains your responsibility until it reaches us.</p>

    <p>Once we have received and checked the items, we will refund you within <strong>14 days</strong>, to the original payment method. Where you chose a delivery upgrade, we refund the cost of our standard delivery rather than the upgrade.</p>

    <h2 id="faulty">Faulty, damaged or incorrect items</h2>

    <p>If a product arrives damaged, develops a fault, or is not what you ordered, we will put it right at no cost to you.</p>

    <p><strong>Please tell us within 3 days of delivery</strong> if your order arrives damaged, is missing an item, or is not what you ordered. Reporting quickly lets us claim against the carrier and get a replacement to you sooner, and it is the fastest route to a resolution.</p>

    <p>Reporting later does not remove your legal rights. <strong>Within 30 days of delivery,</strong> you have the right to reject a faulty item and receive a full refund under the Consumer Rights Act 2015, whenever you tell us.</p>

    <p><strong>After 30 days,</strong> we will offer a repair, replacement or refund as appropriate.</p>

    <p>Email <a href="mailto:{{ $support }}">{{ $support }}</a> with:</p>

    <ul>
        <li>Your order number</li>
        <li>A description of the problem</li>
        <li>Photographs of the item and its batch code, which is printed on the base or crimp of the packaging</li>
    </ul>

    <p>We will not ask you to pay return postage for a faulty or incorrect item. We will send you a prepaid label or refund your postage.</p>

    <h2 id="reactions">Reactions and sensitivity</h2>

    <p>Skin is individual, and occasionally a product that suits most people does not suit you.</p>

    <p>We always recommend patch testing a new product on a small area of skin for 24 hours before full use, particularly if your skin is sensitive or reactive.</p>

    <p>If you experience a reaction, stop using the product immediately and contact us at <a href="mailto:{{ $support }}">{{ $support }}</a>. Tell us which product, the batch code, and what happened. We take every report seriously, we record it, and we will discuss a resolution with you. Please seek medical advice if a reaction is severe or persistent.</p>

    <h2 id="refunds">How refunds are paid</h2>

    <p>Refunds go back to the card or payment method you originally used. We cannot refund to a different card or account.</p>

    <p>Once we process a refund, your bank typically takes <strong>3 to 5 working days</strong> to show it. Some banks take up to 10. The delay is at their end, not ours.</p>

    <p>You will receive an email confirming the refund at the point we issue it.</p>

    <h2 id="cancelling">Cancelling before dispatch</h2>

    <p>If your order has not yet been dispatched, email <a href="mailto:{{ $support }}">{{ $support }}</a> as quickly as possible and we will cancel it and refund you in full.</p>

    <p>Once a parcel has left us, we cannot recall it, and the returns process above applies instead.</p>

    <h2 id="exchanges">Exchanges</h2>

    <p>We do not operate a direct exchange process, because it slows things down for you. Return the unwanted item for a refund and place a new order for what you want instead. If the item you want is low in stock, tell us and we will hold it while your return is in transit.</p>

    <h2 id="address">Where to send returns</h2>

    <p>
        Cardyshub Returns<br>
        <x-policy-value key="legal_returns_address" label="returns address to confirm" />
    </p>

    <p>Please include a note with your order number inside the parcel. Returns arriving without an order number take considerably longer to process.</p>

    <p>Do not send anything back before contacting us. Unannounced returns are difficult to match to an order and slow your refund down.</p>

    <h2 id="excluded">Items we cannot accept back</h2>

    <ul>
        <li>Opened or unsealed skincare, unless faulty</li>
        <li>Items returned outside the 14 day cancellation window, then a further 14 days to return, described above</li>
        <li>Gift cards</li>
        <li>Sale items marked as final sale at the point of purchase</li>
    </ul>

    <h2 id="questions">Questions</h2>

    <p>Email <a href="mailto:{{ $support }}">{{ $support }}</a> and a person will reply. Nothing on this page affects your statutory rights.</p>

    <p class="mt-10"><a href="{{ route('delivery') }}">Delivery</a> &middot; <a href="{{ route('faqs') }}">Frequently asked questions</a> &middot; <a href="{{ route('contact') }}">Contact us</a></p>

</x-policy-page>
