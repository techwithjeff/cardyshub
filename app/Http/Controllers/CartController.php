<?php

namespace App\Http\Controllers;

use App\Models\CartItem;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Services\CartService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CartController extends Controller
{
    public function __construct(protected CartService $cartService) {}

    public function index()
    {
        $cart = $this->cartService->currentCart(create: false);
        $items = $cart ? $cart->items()->with(['product.images', 'variant'])->get() : collect();

        return view('cart.index', compact('cart', 'items'));
    }

    public function add(Request $request, Product $product)
    {
        $validated = $request->validate([
            'product_variant_id' => ['nullable', 'exists:product_variants,id'],
            'quantity' => ['nullable', 'integer', 'min:1', 'max:20'],
        ]);

        $variant = null;
        if (! empty($validated['product_variant_id'])) {
            $variant = ProductVariant::where('id', $validated['product_variant_id'])
                ->where('product_id', $product->id)
                ->firstOrFail();
        }

        abort_unless($product->status === 'published', 404);

        $this->cartService->addItem($product, $variant, $validated['quantity'] ?? 1);

        return $this->respond($request, "{$product->name} added to your cart.");
    }

    public function update(Request $request, CartItem $cartItem)
    {
        $this->authorizeItem($cartItem);

        $validated = $request->validate([
            'quantity' => ['required', 'integer', 'min:0', 'max:20'],
        ]);

        $this->cartService->updateQuantity($cartItem, $validated['quantity']);

        return $this->respond($request, 'Cart updated.');
    }

    public function remove(Request $request, CartItem $cartItem)
    {
        $this->authorizeItem($cartItem);

        $this->cartService->removeItem($cartItem);

        return $this->respond($request, 'Item removed.', ['removed_item_id' => $cartItem->id]);
    }

    protected function authorizeItem(CartItem $cartItem): void
    {
        $cart = $this->cartService->currentCart(create: false);

        abort_if(! $cart || $cartItem->cart_id !== $cart->id, 403);
    }

    /**
     * JSON for the fetch-driven storefront, a redirect for everyone else.
     *
     * The redirect branch is not a fallback nobody hits: it is what runs with
     * JavaScript disabled or still loading, which is why the cart forms remain
     * real forms posting to real routes.
     *
     * @param  array<string, mixed>  $extra
     */
    protected function respond(Request $request, string $message, array $extra = []): JsonResponse|\Illuminate\Http\RedirectResponse
    {
        if ($request->expectsJson()) {
            return response()->json([
                'message' => $message,
                'cart' => $this->cartService->summary(),
            ] + $extra);
        }

        return back()->with('success', $message);
    }
}
