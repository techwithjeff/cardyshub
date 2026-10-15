@php
    use App\Models\Setting;

    $support = Setting::get('contact_email', 'hello@cardyshub.com');
    $privacyEmail = trim((string) Setting::get('privacy_email', '')) ?: $support;
    $sessionCookie = config('session.cookie');
@endphp

<x-policy-page
    title="Cookie policy"
    eyebrow="Cookies"
    :lastUpdated="true"
    :requires="['policy_last_updated']"
    :toc="[
        'what' => 'What cookies are',
        'how' => 'How we use them',
        'which' => 'The cookies we use',
        'managing' => 'Managing your preferences',
        'third-party' => 'Third party cookies',
        'changes' => 'Changes',
    ]">

    <h2 id="what">What cookies are</h2>

    <p>Cookies are small text files placed on your device when you visit a website. They allow the site to remember things about your visit, such as what is in your basket or whether you are signed in. Similar technologies including local storage, pixels and tags work in comparable ways, and this policy covers those too.</p>

    <h2 id="how">How we use them</h2>

    <p>We use cookies to make the site work, to keep your basket and session intact, and to keep your account secure.</p>

    <p><strong>We do not currently use analytics or advertising cookies.</strong> Every cookie listed below is strictly necessary for the site to function, which is why you have not been asked to consent to any of them. If we add analytics or advertising in future, we will ask for your consent before setting those cookies and this page will be updated first.</p>

    <h2 id="which">The cookies we use</h2>

    <h3>Strictly necessary</h3>

    <p>These are required for the site to function and cannot be switched off. They do not require your consent.</p>

    <div class="policy-table-scroll">
        <table>
            <thead><tr><th>Cookie</th><th>Purpose</th><th>Duration</th></tr></thead>
            <tbody>
                <tr>
                    <td><code>{{ $sessionCookie }}</code></td>
                    <td>Maintains your session and basket contents</td>
                    <td>Session</td>
                </tr>
                <tr>
                    <td><code>XSRF-TOKEN</code></td>
                    <td>Protects against cross-site request forgery</td>
                    <td>Session</td>
                </tr>
                <tr>
                    <td><code>remember_web_*</code></td>
                    <td>Keeps you signed in if you choose Remember Me</td>
                    <td>Up to 1 year</td>
                </tr>
                <tr>
                    <td><code>__stripe_mid</code>, <code>__stripe_sid</code></td>
                    <td>Set by Stripe for payment processing and fraud prevention when you reach checkout</td>
                    <td>1 year and 30 minutes respectively</td>
                </tr>
            </tbody>
        </table>
    </div>

    <h3>Analytics and advertising</h3>

    <p>None. We set no analytics or advertising cookies, and there is no tracking pixel on this site.</p>

    <h2 id="managing">Managing your preferences</h2>

    <p>Because we only set strictly necessary cookies, there is no consent banner and nothing to switch off here. That will change if we introduce analytics or advertising, at which point you will be asked first.</p>

    <p>You can still control cookies through your browser settings, including deleting existing cookies and blocking new ones. Blocking essential cookies will prevent parts of the site, including your basket and checkout, from working.</p>

    <p>Browser instructions are available for Chrome, Safari, Firefox and Edge in each browser's help documentation.</p>

    <h2 id="third-party">Third party cookies</h2>

    <p>Stripe sets the cookies listed above when you reach checkout, in order to process your payment and prevent fraud. We do not control these and Stripe's own privacy policy applies to them.</p>

    <p>No other third party sets cookies on this site.</p>

    <h2 id="changes">Changes</h2>

    <p>We may update this policy as the site changes. The date at the top shows when it was last revised.</p>

    <h2 id="questions">Questions</h2>

    <p>Email <a href="mailto:{{ $privacyEmail }}">{{ $privacyEmail }}</a>.</p>

    <p class="mt-10"><a href="{{ route('privacy') }}">Privacy policy</a> &middot; <a href="{{ route('terms') }}">Terms and conditions</a></p>

</x-policy-page>
