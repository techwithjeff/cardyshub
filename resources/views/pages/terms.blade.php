@php
    use App\Models\Setting;

    $support = Setting::get('contact_email', 'hello@cardyshub.com');
    $entity = trim((string) Setting::get('legal_entity_type', ''));
    $phone = trim((string) Setting::get('contact_phone', ''));
@endphp

<x-policy-page
    title="Terms and conditions"
    eyebrow="The legal bit"
    :lastUpdated="true"
    :requires="['legal_entity_type', 'legal_company_name', 'policy_last_updated']"
    :toc="[
        'about-us' => '1. About us',
        'these-terms' => '2. These terms',
        'eligibility' => '3. Eligibility',
        'products' => '4. Products',
        'orders' => '5. Orders and the contract',
        'price' => '6. Price and payment',
        'delivery' => '7. Delivery',
        'cancellation' => '8. Cancellation and returns',
        'statutory' => '9. Your statutory rights',
        'account' => '10. Your account',
        'acceptable-use' => '11. Acceptable use',
        'reviews' => '12. Reviews and user content',
        'ip' => '13. Intellectual property',
        'liability' => '14. Our liability',
        'force-majeure' => '15. Events outside our control',
        'complaints' => '16. Complaints',
        'law' => '17. Governing law',
        'general' => '18. General',
    ]">

    <h2 id="about-us">1. About us</h2>

    @if($entity === 'ltd')
        <p>
            This website is operated by <x-policy-value key="legal_company_name" label="legal name to confirm" />,
            a company registered in <x-policy-value key="legal_jurisdiction" label="jurisdiction to confirm" />
            under company number <x-policy-value key="legal_company_number" label="company number to confirm" />,
            with its registered office at <x-policy-value key="legal_registered_office" label="business address to confirm" />.
            We trade as Cardyshub.
        </p>
    @elseif($entity === 'sole_trader')
        <p>
            This website is operated by {{ Setting::get('about_founder_name', 'Abibat Otaobayomi Cardoso') }},
            trading as Cardyshub, a business established in England, based in
            {{ Setting::get('legal_business_location', 'Coventry, United Kingdom') }}.
        </p>
    @else
        <p class="policy-draft p-4">
            <strong>Not yet set.</strong> This clause has two versions, one for a limited company and one
            for a sole trader, and only the correct one may be published. Set <code>legal_entity_type</code>
            to either <code>ltd</code> or <code>sole_trader</code> and the right wording will appear here.
        </p>
    @endif

    @if(Setting::filled('legal_vat_number'))
        <p>VAT registration number: {{ Setting::get('legal_vat_number') }}.</p>
    @endif

    <p>
        You can contact us at <a href="mailto:{{ $support }}">{{ $support }}</a>@if($phone !== '') or {{ $phone }}@endif.
        We aim to respond to all enquiries within one working day.
    </p>

    <h2 id="these-terms">2. These terms</h2>

    <p>These terms apply to your use of cardyshub.uk and to every order you place with us. By using the site or placing an order you accept them. Please read them before ordering.</p>

    <p>We may change these terms. The version that applies to your order is the version published at the time you placed it.</p>

    <h2 id="eligibility">3. Eligibility</h2>

    <p>You must be at least 18 years old and legally capable of entering into a binding contract to place an order.</p>

    <h2 id="products">4. Products</h2>

    <p>We take care to describe and photograph our products accurately. Colours may appear differently on different screens, and packaging is occasionally updated, so slight variation between the image and the item you receive is possible.</p>

    <p>Our products are cosmetics. They are not medicines and are not intended to diagnose, treat, cure or prevent any medical condition. Nothing on this site is medical advice. If you have a skin condition, consult a doctor or dermatologist.</p>

    <p>Full ingredient lists are published on each product page. It is your responsibility to check these against any known allergy or sensitivity, and we recommend patch testing before full use.</p>

    <p>All products are subject to availability.</p>

    <h2 id="orders">5. Orders and the contract between us</h2>

    <p>Placing an order is an offer to buy. Our order confirmation email acknowledges receipt of that offer but does not accept it.</p>

    <p>A contract is formed only when we send you a dispatch confirmation email. Until that point we may decline your order, for example because an item is out of stock, because we have identified a pricing error, or because we have been unable to authorise payment.</p>

    <p>If we decline an order after you have paid, we will refund you in full.</p>

    <h2 id="price">6. Price and payment</h2>

    <p>Prices are shown in pounds sterling and include VAT where applicable. Delivery is charged separately and shown at checkout before you pay.</p>

    <p>We take payment at the point you place your order. Payments are processed by Stripe, and by paying you also accept Stripe's terms. We do not store your full card details.</p>

    <p>Prices may change, but changes will not affect an order we have already accepted.</p>

    <p>If a product is listed at an obviously incorrect price and the error would have been reasonably apparent to you, we are not obliged to supply it at that price. We will contact you to ask whether you wish to proceed at the correct price or cancel.</p>

    <h2 id="delivery">7. Delivery</h2>

    <p>Delivery options, costs and timescales are set out on our <a href="{{ route('delivery') }}">Delivery page</a>, which forms part of these terms.</p>

    <p>Delivery estimates are estimates, not guarantees. We are not liable for delays caused by circumstances outside our reasonable control, though we will always help you resolve them.</p>

    <p>Goods become your responsibility when they are delivered to the address you gave us. Ownership passes to you once we have received payment in full.</p>

    <h2 id="cancellation">8. Cancellation, returns and refunds</h2>

    <p>Your rights to cancel and return, including the hygiene exemption that applies to unsealed skincare, are set out in full on our <a href="{{ route('returns') }}">Returns and Refunds page</a>, which forms part of these terms.</p>

    <p>In summary, you have 14 days from delivery to cancel an order of sealed goods, and a further 14 days to return them. Once a skincare product has been opened or unsealed, we cannot accept it back for a change of mind, though your rights in respect of faulty, damaged or misdescribed goods are unaffected.</p>

    <h2 id="statutory">9. Your statutory rights</h2>

    <p>Nothing in these terms affects your legal rights under the Consumer Rights Act 2015, which entitles you to goods that are of satisfactory quality, fit for purpose and as described.</p>

    <h2 id="account">10. Your account</h2>

    <p>If you create an account you are responsible for keeping your password confidential and for activity carried out under your account. Tell us immediately if you believe someone else has accessed it.</p>

    <p>We may suspend or close an account where we reasonably believe these terms have been breached.</p>

    <h2 id="acceptable-use">11. Acceptable use</h2>

    <p>You may not use this site unlawfully, attempt to gain unauthorised access to it, introduce malicious code, extract data from it by automated means, or use it in any way that damages or impairs it.</p>

    <h2 id="reviews">12. Reviews and user content</h2>

    <p>If you submit a review or other content, you confirm it is your own honest opinion and does not infringe anyone else's rights.</p>

    <p>You grant us a non-exclusive, royalty-free licence to use, publish and display that content in connection with our business.</p>

    <p>We may decline or remove content that is unlawful, offensive, misleading or irrelevant.</p>

    <h2 id="ip">13. Intellectual property</h2>

    <p>All content on this site, including the Cardyshub name and logo, text, photography, graphics and design, belongs to us or our licensors and is protected by intellectual property law. You may not reproduce or use it commercially without our written permission.</p>

    <h2 id="liability">14. Our liability</h2>

    <p>We are responsible for loss you suffer that is a foreseeable result of our breaking these terms or failing to use reasonable care and skill. We are not responsible for unforeseeable loss.</p>

    <p>We do not limit our liability in any way that would be unlawful, including for death or personal injury caused by our negligence, for fraud, or for any breach of your statutory rights as a consumer.</p>

    <p>We supply our products for domestic and private use. We have no liability for loss of profit, loss of business or business interruption.</p>

    <h2 id="force-majeure">15. Events outside our control</h2>

    <p>We are not liable for failure or delay in performing our obligations where it results from an event outside our reasonable control. If such an event occurs we will contact you, and you may cancel and receive a refund for goods not yet delivered.</p>

    <h2 id="complaints">16. Complaints</h2>

    <p>Email <a href="mailto:{{ $support }}">{{ $support }}</a> with the details and we will acknowledge your complaint within two working days and aim to resolve it within 14 days.</p>

    <h2 id="law">17. Governing law</h2>

    <p>These terms are governed by the law of England and Wales, and disputes will be subject to the exclusive jurisdiction of the courts of England and Wales. If you live in Scotland or Northern Ireland, you may also bring proceedings in your own country.</p>

    <h2 id="general">18. General</h2>

    <p>If any provision of these terms is found to be unlawful or unenforceable, the remaining provisions continue in force.</p>

    <p>If we do not insist that you perform an obligation, or delay in enforcing our rights, that does not prevent us from enforcing them later.</p>

    <p>The contract is between you and us. No other person has rights to enforce it.</p>

    <p class="mt-10"><a href="{{ route('privacy') }}">Privacy policy</a> &middot; <a href="{{ route('cookies') }}">Cookie policy</a> &middot; <a href="{{ route('returns') }}">Returns and refunds</a></p>

</x-policy-page>
