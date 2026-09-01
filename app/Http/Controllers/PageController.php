<?php

namespace App\Http\Controllers;

use App\Models\SummerClubResource;
use App\Models\SummerClubSubscriptionRequest;
use Illuminate\Http\Request;

class PageController extends Controller
{
    public function about()
    {
        return view('pages.about');
    }

    public function contact()
    {
        return view('pages.contact');
    }

    public function contactSend(Request $request)
    {
        $request->validate([
            'name'    => ['required', 'string', 'max:120'],
            'email'   => ['required', 'email', 'max:180'],
            'subject' => ['required', 'string', 'max:200'],
            'message' => ['required', 'string', 'min:10', 'max:3000'],
        ]);

        // TODO: wire to a Mailable when SMTP is configured
        // Mail::to(config('mail.from.address'))->send(new ContactMail($request->all()));

        return back()->with('success', __('ui.pages.contact.sent'));
    }

    public function faq()
    {
        return view('pages.faq');
    }

    public function terms()
    {
        return view('pages.terms');
    }

    public function privacy()
    {
        return view('pages.privacy');
    }

    public function refunds()
    {
        return view('pages.refunds');
    }

    public function summerClub()
    {
        $resources = SummerClubResource::where('is_published', true)
            ->where('is_featured', true)
            ->orderBy('featured_sort_order')
            ->orderBy('sort_order')
            ->limit(4)
            ->get();

        $currency = \App\Services\CurrencyService::current();

        return view('club-ete', [
            'resources' => $resources,
            'packs' => SummerClubSubscriptionRequest::packDefinitionsFor($currency),
            'subjects' => SummerClubSubscriptionRequest::subjects(),
            'currency' => $currency,
        ]);
    }
}
