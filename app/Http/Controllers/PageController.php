<?php

namespace App\Http\Controllers;

use App\Models\Setting;

class PageController extends Controller
{
    public function about()
    {
        // `?:` rather than relying on the Setting::get() default. That default
        // only applies when the key is absent — a key that exists but holds an
        // empty string comes back as '', and for the photo that rendered
        // asset('storage/'.'') as a broken /storage image on the About page.
        // Saving the admin Settings form can leave a file field blank, so an
        // empty value has to fall back the same way a missing one does.
        $founderName = trim((string) Setting::get('about_founder_name', '')) ?: 'Abibat Otaobayomi Cardoso';
        $founderPhoto = trim((string) Setting::get('about_founder_photo', '')) ?: 'images/founder.jpeg';
        $founderBody = (string) Setting::get('about_founder_body', '');

        return view('pages.about', compact('founderName', 'founderPhoto', 'founderBody'));
    }

    public function contact()
    {
        return view('pages.contact');
    }

    public function delivery()
    {
        return view('pages.delivery');
    }

    public function returns()
    {
        return view('pages.returns');
    }

    public function faqs()
    {
        return view('pages.faqs');
    }

    public function terms()
    {
        return view('pages.terms');
    }

    public function privacy()
    {
        return view('pages.privacy');
    }

    public function cookies()
    {
        return view('pages.cookies');
    }

    public function wholesale()
    {
        return view('pages.wholesale');
    }
}
