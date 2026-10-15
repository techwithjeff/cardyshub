@php
    use App\Models\Setting;

    $support = Setting::get('contact_email', 'hello@cardyshub.com');
    $privacyEmail = trim((string) Setting::get('privacy_email', '')) ?: $support;
@endphp

<x-policy-page
    title="Privacy policy"
    eyebrow="Your data"
    :lastUpdated="true"
    :requires="['legal_company_name', 'policy_last_updated']"
    :toc="[
        'who-we-are' => 'Who we are',
        'what-we-collect' => 'What we collect',
        'why' => 'Why we use it',
        'marketing' => 'Marketing',
        'sharing' => 'Who we share it with',
        'transfers' => 'International transfers',
        'retention' => 'How long we keep it',
        'security' => 'Security',
        'rights' => 'Your rights',
        'children' => 'Children',
        'complaints' => 'Complaints',
        'changes' => 'Changes',
    ]">

    <h2 id="who-we-are">Who we are</h2>

    <p>
        {{ Setting::get('legal_company_name', "Cardy's Hub") }}, trading as Cardyshub,
        is the data controller for the personal information described in this policy.
        We are based in {{ Setting::get('legal_business_location', 'Coventry, United Kingdom') }}.
    </p>

    <p>
        Contact for data protection matters: <a href="mailto:{{ $privacyEmail }}">{{ $privacyEmail }}</a>
    </p>

    <p>This policy explains what we collect, why, what we do with it, and the rights you have. It applies to cardyshub.uk and to any dealings you have with us by email or social media.</p>

    <h2 id="what-we-collect">What we collect</h2>

    <p><strong>When you place an order:</strong> your name, billing and delivery address, email address, phone number, order contents and order history. Card details are entered directly into Stripe and are never received or stored by us. We see only the last four digits and the card type.</p>

    <p><strong>When you create an account:</strong> your name, email address, password in encrypted form, saved addresses and order history.</p>

    <p><strong>When you contact us:</strong> your name, email address, phone number if you give it, and the content of your message.</p>

    <p><strong>When you subscribe to marketing:</strong> your email address, your name if given, and a record of your consent.</p>

    <p><strong>When you make a wholesale enquiry:</strong> your name, business name, business address, contact details, website or social handles and the details of your enquiry.</p>

    <p><strong>When you submit a review:</strong> your name or display name, your rating and your review content.</p>

    <p><strong>When you report a skin reaction:</strong> the product, batch code and a description of what happened. We record these because we are required to monitor product safety.</p>

    <p><strong>Automatically, when you browse:</strong> IP address, browser type and version, device type, operating system, pages visited, time spent, referring site and approximate location derived from IP address. See our <a href="{{ route('cookies') }}">Cookie Policy</a> for detail.</p>

    <h2 id="why">Why we use it, and our legal basis</h2>

    <div class="policy-table-scroll">
        <table>
            <thead><tr><th>Purpose</th><th>Legal basis</th></tr></thead>
            <tbody>
                <tr><td>Processing and delivering your order</td><td>Performance of a contract</td></tr>
                <tr><td>Taking payment and preventing fraud</td><td>Contract and legitimate interests</td></tr>
                <tr><td>Managing your account</td><td>Performance of a contract</td></tr>
                <tr><td>Responding to enquiries and complaints</td><td>Contract and legitimate interests</td></tr>
                <tr><td>Sending marketing emails</td><td>Consent, or soft opt-in for existing customers</td></tr>
                <tr><td>Handling returns and refunds</td><td>Contract and legal obligation</td></tr>
                <tr><td>Recording and investigating product reactions</td><td>Legal obligation and vital interests</td></tr>
                <tr><td>Keeping accounting and tax records</td><td>Legal obligation</td></tr>
                <tr><td>Publishing reviews</td><td>Consent</td></tr>
                <tr><td>Assessing wholesale applications</td><td>Legitimate interests</td></tr>
            </tbody>
        </table>
    </div>

    <p>Where we rely on legitimate interests, we have considered whether our interest is overridden by your rights, and we have concluded that it is not. You may object at any time.</p>

    <h2 id="marketing">Marketing</h2>

    <p>We will send you marketing emails only where you have consented, or where you have bought from us and we are contacting you about similar products under the soft opt-in permitted by the Privacy and Electronic Communications Regulations.</p>

    <p>Every marketing email carries an unsubscribe link. You can also email <a href="mailto:{{ $privacyEmail }}">{{ $privacyEmail }}</a>. Unsubscribing does not stop transactional emails such as order and dispatch confirmations, which we must send in order to fulfil your order.</p>

    <h2 id="sharing">Who we share it with</h2>

    <p>We do not sell your personal information. We share it only with organisations that help us run the business:</p>

    <ul>
        <li><strong>Stripe</strong>, for payment processing and fraud prevention</li>
        <li><strong>Our delivery carriers</strong>, who receive your name, address and phone number in order to deliver your parcel</li>
        <li><strong>Our hosting provider</strong>, on whose servers the site and database sit</li>
        <li><strong>Our email provider</strong>, for transactional and marketing email</li>
        <li><strong>Professional advisers</strong>, such as accountants and lawyers, where necessary</li>
        <li><strong>Law enforcement or regulators</strong>, where we are legally required to do so</li>
    </ul>

    <p>Each of these acts under contract and may use your information only for the purpose we specify.</p>

    <p>If our business is sold or transferred, personal data may transfer as part of that sale. You would be notified.</p>

    <h2 id="transfers">International transfers</h2>

    <p>Some of our providers process data outside the UK. Where they do, we ensure appropriate safeguards are in place, such as UK adequacy regulations or the International Data Transfer Agreement. You may request details of these safeguards.</p>

    <h2 id="retention">How long we keep it</h2>

    <ul>
        <li><strong>Order and transaction records:</strong> six years after the end of the tax year in which the order was placed, as required by HMRC</li>
        <li><strong>Account data:</strong> for as long as your account is active, then 12 months after your last activity</li>
        <li><strong>Marketing consents:</strong> until you withdraw consent, then a suppression record indefinitely so that we do not contact you again</li>
        <li><strong>Enquiries and correspondence:</strong> two years</li>
        <li><strong>Wholesale enquiries:</strong> two years from last contact</li>
        <li><strong>Product reaction reports:</strong> ten years, for product safety purposes</li>
    </ul>

    <h2 id="security">Security</h2>

    <p>We use encryption in transit, hashed password storage, restricted administrative access and regularly updated software. Payment card data never reaches our systems.</p>

    <p>No system is completely secure. If a breach occurs that is likely to result in a risk to your rights, we will notify the ICO within 72 hours and inform you where required.</p>

    <h2 id="rights">Your rights</h2>

    <p>Under UK data protection law you have the right to:</p>

    <ul>
        <li><strong>Be informed</strong> about how your data is used, which is what this policy does</li>
        <li><strong>Access</strong> a copy of the personal data we hold about you</li>
        <li><strong>Rectify</strong> inaccurate or incomplete data</li>
        <li><strong>Erase</strong> your data, subject to records we must keep by law</li>
        <li><strong>Restrict</strong> processing in certain circumstances</li>
        <li><strong>Data portability</strong>, receiving your data in a structured, machine-readable format</li>
        <li><strong>Object</strong> to processing based on legitimate interests, and to direct marketing at any time</li>
        <li><strong>Withdraw consent</strong> at any time, where consent is the basis we rely on</li>
    </ul>

    <p>To exercise any of these, email <a href="mailto:{{ $privacyEmail }}">{{ $privacyEmail }}</a>. We will respond within one month. There is no charge unless a request is manifestly unfounded or excessive.</p>

    <h2 id="children">Children</h2>

    <p>Our site is not directed at children and we do not knowingly collect data from anyone under 18. If you believe we hold data about a child, contact us and we will delete it.</p>

    <h2 id="complaints">Complaints</h2>

    <p>If you are unhappy with how we have handled your data, please tell us first at <a href="mailto:{{ $privacyEmail }}">{{ $privacyEmail }}</a> so that we can put it right.</p>

    <p>You also have the right to complain to the Information Commissioner's Office at <a href="https://ico.org.uk" target="_blank" rel="noopener noreferrer">ico.org.uk</a>, by calling 0303 123 1113, or by writing to Information Commissioner's Office, Wycliffe House, Water Lane, Wilmslow, Cheshire SK9 5AF.</p>

    <h2 id="changes">Changes</h2>

    <p>We may update this policy. The date at the top shows when it last changed. Material changes will be notified on the site or by email.</p>

    <p class="mt-10"><a href="{{ route('terms') }}">Terms and conditions</a> &middot; <a href="{{ route('cookies') }}">Cookie policy</a></p>

</x-policy-page>
