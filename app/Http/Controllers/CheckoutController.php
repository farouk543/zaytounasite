<?php

namespace App\Http\Controllers;

use App\Models\Course;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Payment;
use App\Models\User;
use App\Notifications\ManualOrderPending;
use App\Services\CurrencyService;
use App\Services\PriceResolver;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class CheckoutController extends Controller
{
    public function __construct(private readonly PriceResolver $priceResolver)
    {
    }

    public function checkout(Request $request)
    {
        // Redirige vers le flux manuel (admin approval requis)
        return $this->requestManual($request);
    }

    /**
     * Paiement manuel — crée la commande en statut pending_manual
     * et attend la validation de l'admin dans le backoffice.
     */
    public function requestManual(Request $request)
    {
        $user = $request->user();

        $cart = session('cart.items', []);
        abort_if(empty($cart), 400, 'Cart empty');

        $courseIds = array_map('intval', array_keys($cart));

        $courses = Course::query()
            ->whereIn('id', $courseIds)
            ->where('is_published', true)
            ->with('prices')
            ->get();

        abort_if($courses->isEmpty(), 400, 'No valid courses');

        // Devise vue par le client au moment du checkout — on la verrouille.
        $currency = CurrencyService::current();

        $quotes = [];
        foreach ($courses as $c) {
            $quotes[$c->id] = $this->priceResolver->resolve($c, $currency);
        }

        $order = DB::transaction(function () use ($user, $courses, $currency, $quotes) {

            $subtotal = array_sum(array_map(fn ($q) => $q->priceCents, $quotes));

            $order = Order::create([
                'user_id'        => $user->id,
                'status'         => 'pending_manual',
                'subtotal_cents' => $subtotal,
                'discount_cents' => 0,
                'total_cents'    => $subtotal,
                'currency'       => $currency,
            ]);

            foreach ($courses as $c) {
                $quote = $quotes[$c->id];

                OrderItem::create([
                    'order_id'         => $order->id,
                    'course_id'        => $c->id,
                    'unit_price_cents' => $quote->priceCents,
                    'qty'              => 1,
                    'currency'         => $quote->currency,
                    'base_price_cents' => $quote->baseCents,
                    'base_currency'    => $quote->baseCurrency,
                ]);
            }

            Payment::create([
                'order_id'         => $order->id,
                'provider'         => 'manual',
                'status'           => 'pending',
                'transaction_id'   => null,
                'payment_url'      => null,
                'provider_payload' => [
                    'mode'     => 'manual',
                    'currency' => $currency,
                    'lines'    => array_map(fn ($q) => $q->toArray(), $quotes),
                ],
            ]);

            session()->forget('cart.items');

            return $order;
        });

        // Notifier les admins (hors transaction pour éviter tout rollback)
        try {
            $order->load('user', 'items.course');
            $admins = User::role('admin')->get();
            foreach ($admins as $admin) {
                $admin->notify(new ManualOrderPending($order));
            }
        } catch (\Throwable $e) {
            // Notification non bloquante
        }

        return redirect()
            ->route('dashboard')
            ->with('success', 'Votre demande a bien été envoyée. L\'équipe Zaytouna validera votre accès très prochainement.');
    }
}