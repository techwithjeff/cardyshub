{{--
    Shown instead of a bare 500 when PHP's intl extension is missing.

    Filament calls Illuminate\Support\Number on every admin page that renders a
    table or pagination, and Number throws outright without intl. With
    APP_DEBUG off that produced an unexplained "Server Error" on /admin while
    the storefront worked perfectly — which is a genuinely horrible thing to
    diagnose from the symptom. This page names it.

    Deliberately says nothing about the database, paths or versions: it can be
    reached by anyone who finds the URL.
--}}
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Admin unavailable — Cardyshub</title>
    <link rel="icon" href="{{ asset('brand/favicon-32.png') }}" sizes="32x32">
    <style>
        :root { color-scheme: light; }
        body {
            margin: 0;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 1.5rem;
            background: #FAF7F5;
            color: #14060E;
            font-family: "Plus Jakarta Sans", system-ui, -apple-system, sans-serif;
            line-height: 1.6;
        }
        .card {
            max-width: 34rem;
            width: 100%;
            background: #fff;
            border: 1px solid #EADFE4;
            border-radius: 12px;
            padding: 2rem 1.75rem;
            box-shadow: 0 1px 2px rgba(20,6,14,.04), 0 8px 24px rgba(20,6,14,.06);
        }
        h1 { margin: 0 0 .75rem; font-size: 1.4rem; line-height: 1.2; }
        p { margin: 0 0 1rem; color: #6B5A63; }
        ol { margin: 0 0 1rem; padding-left: 1.2rem; color: #6B5A63; }
        li { margin-bottom: .5rem; }
        code {
            background: #F3EEEB;
            border: 1px solid #EADFE4;
            border-radius: 4px;
            padding: .1em .38em;
            font-family: "Space Grotesk", ui-monospace, monospace;
            font-size: .9em;
            color: #14060E;
        }
        .flag {
            display: inline-block;
            font-size: .7rem;
            letter-spacing: .1em;
            text-transform: uppercase;
            color: #C40350;
            border: 1px solid #FFCCE0;
            background: #FFF0F6;
            border-radius: 4px;
            padding: .25rem .5rem;
            margin-bottom: 1rem;
        }
        a { color: #C40350; }
    </style>
</head>
<body>
    <div class="card">
        <span class="flag">Server configuration</span>
        <h1>The admin needs PHP's <code>intl</code> extension</h1>

        <p>
            The shop itself is fine and customers are unaffected. Only the admin
            area needs this, because it formats numbers with it.
        </p>

        <p>To fix it, on the hosting control panel:</p>

        <ol>
            <li>Open <strong>Select PHP Version</strong> (or <strong>MultiPHP Manager</strong>).</li>
            <li>Tick <code>intl</code> in the extensions list and save.</li>
            <li>If there is no extensions list, open <strong>MultiPHP INI Editor</strong>
                in editor mode and add <code>extension=intl</code>.</li>
            <li>If neither is available, ask the host to enable the PHP
                <code>intl</code> extension for this domain. It ships with
                virtually every PHP build, so it is a routine request.</li>
        </ol>

        <p>Reload this page once it is enabled. Nothing needs redeploying.</p>

        <p><a href="{{ url('/') }}">Back to the shop</a></p>
    </div>
</body>
</html>
