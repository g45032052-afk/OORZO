<?php

/*
|--------------------------------------------------------------------------
| Builder addon configuration (host-owned) — StackFood
|--------------------------------------------------------------------------
|
| Owned by the HOST project, not the Builder module. The module stays identical
| across projects and only READS these keys via config('builder.*') (each read
| carries an inline default, so a missing key never fatals).
|
| Tuned for StackFood: single-module (food) storefront. The module/presentation
| axes collapse vs 6amMart's multi-module baseline.
|
*/

return [
    // StackFood vendor (restaurant) panel dashboard route name. VERIFY against
    // routes/vendor.php group name prefix (the "back to dashboard" CMS link).
    'cms_dashboard_route' => env('BUILDER_CMS_DASHBOARD_ROUTE', 'vendor.dashboard'),

    'default_platform_name'  => 'StackFood',

    // Wallet-family storefront features master switch (host wallet logic untouched).
    'wallet_features_enabled' => false,

    // Storefront social login master switch (off until a cross-domain auth broker exists).
    'social_login_enabled' => false,

    // Master switch for ALL storefront-triggered email (see SuppressStorefrontMail).
    'storefront_mail_enabled' => false,

    /*
     * Host capability manifest — StackFood (food-only) baseline. Adjust per the
     * features StackFood actually ships. Read via CapabilityProvider /
     * useCapability / RequireCapability.
     */
    'capabilities' => [
        'schemaVersion' => 1,

        // Storefront profile-edit field editability. Phone is the account's
        // login identity → locked; email is editable. Read on the client
        // (EditProfileModal disables the field) and enforced server-side in
        // CustomerAuthProvider::updateProfile.
        'profile' => [
            'phoneEditable' => false,
            'emailEditable' => true,
        ],

        // StackFood is food-only → single module, no switcher, items always
        // presented as a modal (food detail modal), never a separate page.
        'modules' => ['mode' => 'single', 'switcher' => false, 'itemPresentation' => 'modal'],

        // Single currency.
        'currency' => ['mode' => 'single', 'switcher' => false],

        // Location / map / address book (StackFood is zone-based).
        'location' => [
            'enabled' => true, 'map' => true, 'currentLocation' => true,
            'zoneBased' => true, 'savedAddresses' => true,
        ],

        // Checkout surface.
        'checkout' => [
            'deliveryTypes' => ['home', 'takeaway', 'schedule'],
            'tips' => true, 'tipPresets' => [10, 15, 20, 40],
            'extraPackaging' => true, 'coupon' => true,
            'unavailableNote' => true, 'deliveryInstruction' => true,
            'orderNote' => false, 'savedAddress' => true,
        ],

        // Payment rails + flow.
        'payment' => [
            'cod' => true, 'digital' => true, 'offline' => true,
            'wallet' => true, 'partial' => true,
            'timing' => 'after', 'retryReminder' => true,
        ],

        // Cross-cutting commerce features. Flip each ON only after its adapter
        // is implemented (Tier-2); until then leave the Null default + OFF.
        'features' => [
            'wallet' => true, 'loyaltyPoint' => true, 'referral' => true,
            'reviews' => true, 'inbox' => true, 'pushNotif' => true,
            'guestCheckout' => true, 'reorder' => true, 'wishlist' => true, 'blog' => false,
            // Buy Now (instant checkout) button on the item modal / details page /
            // quick-view. Off for StackFood + 6amMart per product decision — the
            // storefront uses the add-to-cart flow only.
            'buyNow' => false,
        ],

        // Auth methods.
        'auth' => [
            'manual' => true, 'otp' => true, 'otpChannel' => 'sms',
            'social' => ['google' => true, 'facebook' => true, 'apple' => true],
        ],
    ],
];
