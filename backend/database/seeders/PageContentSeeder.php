<?php

namespace Database\Seeders;

use App\Models\PageContent;
use Illuminate\Database\Seeder;

class PageContentSeeder extends Seeder
{
    public function run(): void
    {
        $pages = [
            [
                'key'     => 'hero-banner',
                'title'   => 'Home Hero Banner',
                'section' => 'hero',
                'is_active' => true,
                'content' => [
                    'slides' => [
                        [
                            'desktop' => '/assets/large-image.png',
                            'mobile'  => '/assets/luxbeyond-mobile.png',
                            'alt'     => 'Global Shopping Simplified',
                            'heading' => 'Import the Best with Ease',
                            'subheading' => 'Your trusted gateway to premium global products.',
                            'cta_text' => 'Shop Now',
                            'cta_link' => '/shop',
                        ],
                        [
                            'desktop' => '/assets/hero-2.png',
                            'mobile'  => '/assets/hero-2-mobile.png',
                            'alt'     => 'Shop from Anywhere',
                            'heading' => 'Exclusive Global Brands',
                            'subheading' => 'Bringing the world\'s best brands to your doorstep.',
                            'cta_text' => 'Explore Now',
                            'cta_link' => '/shop',
                        ]
                    ]
                ],
            ],
            [
                'key'     => 'about-us',
                'title'   => 'About Us',
                'section' => 'page',
                'is_active' => true,
                'content' => [
                    'heading' => 'Greetings from LuxBeyond',
                    'body'    => '<p class="text-lg mb-6">Your reliable supplier of premium imports from the USA, UK, and Korea.</p>

<h2 class="text-2xl font-bold mt-8 mb-4">Who We Are</h2>
<p class="mb-6">LuxBeyond provides a smooth purchasing experience for premium foreign products, bridging the gap between local consumers and worldwide markets. We promise authentic products acquired from reliable vendors, whether you\'re shopping for technology, clothes, or lifestyle necessities.</p>

<h2 class="text-2xl font-bold mt-8 mb-4">Our Commitment</h2>

<h3 class="text-xl font-semibold mt-6 mb-2">Authenticity Guaranteed</h3>
<p class="mb-4">We only import 100% original products.</p>

<h3 class="text-xl font-semibold mt-6 mb-2">Secure Ordering</h3>
<p class="mb-4">We follow a 50% advance payment policy, with the remaining balance paid upon delivery.</p>

<h3 class="text-xl font-semibold mt-6 mb-2">Reliable Delivery</h3>
<p class="mb-4">Orders typically arrive within 3 to 4 weeks, with updates provided throughout the shipping process.</p>

<h3 class="text-xl font-semibold mt-6 mb-2">Customer Satisfaction</h3>
<p class="mb-6">We prioritize transparency and customer support, ensuring a hassle-free shopping experience.</p>

<h2 class="text-2xl font-bold mt-8 mb-4">Why Choose LuxBeyond?</h2>
<ul class="list-disc pl-6 space-y-2 mb-6">
    <li>Direct imports from Korea, USA &amp; UK</li>
    <li>High-quality, original products</li>
    <li>Fair pricing with a secure payment structure</li>
    <li>Dedicated customer support for a smooth experience</li>
</ul>
<p class="mb-4">Experience the best of international shopping with LuxBeyond!</p>
<p>For inquiries, contact us.</p>',
                    'meta_title'       => 'About Us - LuxBeyond',
                    'meta_description' => 'Learn about LuxBeyond and our mission to bring premium international products to your doorstep.',
                ],
            ],
            [
                'key'     => 'privacy-policy',
                'title'   => 'Privacy Policy',
                'section' => 'page',
                'is_active' => true,
                'content' => [
                    'heading' => 'Privacy Policy',
                    'body'    => '<p class="mb-6">Our goal at LuxBeyond is to safeguard your privacy and make sure that your personal data is secure. When you use our services, we gather, utilize, and safeguard your data as described in this privacy policy.</p>

<h2 class="text-2xl font-bold mt-8 mb-4">1. Information We Collect</h2>
<p class="mb-3">We may collect the following types of information when you place an order or interact with our website:</p>
<ul class="list-disc pl-6 space-y-2 mb-6">
    <li><strong>Personal Information:</strong> Name, phone number, email address, and shipping address.</li>
    <li><strong>Payment Information:</strong> Transaction details (we do not store payment card details).</li>
    <li><strong>Order Details:</strong> Products purchased and order history.</li>
    <li><strong>Communication Data:</strong> Any inquiries or feedback provided via email or customer support.</li>
</ul>

<h2 class="text-2xl font-bold mt-8 mb-4">2. How We Use Your Information</h2>
<p class="mb-3">Your personal information is used for:</p>
<ul class="list-disc pl-6 space-y-2 mb-6">
    <li>Processing and fulfilling orders.</li>
    <li>Providing customer support and updates on order status.</li>
    <li>Improving our services and customer experience.</li>
    <li>Preventing fraud and ensuring transaction security.</li>
    <li>Sending promotional offers (only with customer consent).</li>
</ul>

<h2 class="text-2xl font-bold mt-8 mb-4">3. Data Protection &amp; Security</h2>
<p class="mb-6">We take the necessary precautions to guard against misuse, loss, and unauthorized access to your personal information. To protect data, our payment methods and website use secure servers and encryption.</p>

<h2 class="text-2xl font-bold mt-8 mb-4">4. Sharing Your Information</h2>
<p class="mb-3">Except in the following circumstances, we never sell or give away your personal information to outside parties:</p>
<ul class="list-disc pl-6 space-y-2 mb-6">
    <li>To deliver your orders, work with shipping partners.</li>
    <li>In order to handle transactions with payment providers.</li>
    <li>Whenever mandated by the law or other legal authorities.</li>
</ul>

<h2 class="text-2xl font-bold mt-8 mb-4">5. Cookies &amp; Tracking Technologies</h2>
<p class="mb-6">To improve your surfing experience, we could employ cookies and other tracking technologies. If you would want to disable cookies, you can change your browser\'s settings.</p>

<h2 class="text-2xl font-bold mt-8 mb-4">6. Your Rights &amp; Choices</h2>
<p class="mb-3">You have the right to:</p>
<ul class="list-disc pl-6 space-y-2 mb-6">
    <li>Access, update, or correct your personal information.</li>
    <li>Opt out of marketing communications.</li>
    <li>Request the deletion of your data (subject to legal and operational requirements).</li>
</ul>

<h2 class="text-2xl font-bold mt-8 mb-4">7. Changes to This Policy</h2>
<p class="mb-6">This Privacy Policy may be updated or modified by LuxBeyond at any time. We will notify you of any changes on our website.</p>
<p>Please contact us with any questions you may have about privacy.</p>',
                    'meta_title'       => 'Privacy Policy - LuxBeyond',
                    'meta_description' => 'Read our privacy policy to understand how we collect and handle your personal data.',
                ],
            ],
            [
                'key'     => 'refund-policy',
                'title'   => 'Refund Policy',
                'section' => 'page',
                'is_active' => true,
                'content' => [
                    'heading' => 'Refund Policy',
                    'body'    => '<p class="mb-6">We at LuxBeyond cherish our clients and work hard to deliver premium imported goods from the UK, the USA, Dubai, India and Korea. Before making a purchase, please carefully read our refund policy.</p>

<h2 class="text-2xl font-bold mt-8 mb-4">1. Refund Eligibility for the USA, Dubai, India and Korea</h2>
<ul class="list-disc pl-6 space-y-2 mb-6">
    <li>Customers are eligible for a full refund of the advance payment (50% of the total product value) before purchasing the item.</li>
    <li>Once the item has been purchased and the order is confirmed, refunds cannot be processed.</li>
    <li>If we are unable to deliver your order within the promised timeframe (typically 3 to 4 weeks, but may vary due to shipment delays), you are eligible for a full refund within 7 days of the delay notification.</li>
    <li>If you cancel your order before the shipment is processed, we will refund the full amount paid. Once the shipment is in transit, cancellations will not be accepted.</li>
    <li>A refund will only be processed if LuxBeyond fails to deliver within the expected timeframe.</li>
    <li>Refunds are not applicable for change-of-mind requests or if the order is already shipped.</li>
    <li>Damaged or defective items will be replaced (USA, Korea only), but refunds are not provided unless the item is out of stock.</li>
</ul>

<h2 class="text-2xl font-bold mt-8 mb-4">2. Refund for UK Products</h2>
<ul class="list-disc pl-6 space-y-2 mb-6">
    <li>If a product imported from the UK arrives damaged or defective, we cannot process a refund due to the nature of air cargo shipments.</li>
    <li>Customers are advised to consider this policy before purchasing UK-imported products.</li>
</ul>

<h2 class="text-2xl font-bold mt-8 mb-4">3. Refund Processing Time</h2>
<ul class="list-disc pl-6 space-y-2 mb-6">
    <li>Approved refunds will be processed within 7 working days after a valid refund request.</li>
    <li>Refunds will be issued via the original payment method.</li>
    <li>Refunds will only apply to the advance amount.</li>
</ul>

<h2 class="text-2xl font-bold mt-8 mb-4">4. Exceptions</h2>
<ul class="list-disc pl-6 space-y-2 mb-6">
    <li>Items damaged due to customer mishandling are not eligible for a refund.</li>
    <li>Refund requests made after 7 days of receiving the product will not be accepted.</li>
</ul>

<h2 class="text-2xl font-bold mt-8 mb-4">5. Policy Changes</h2>
<p class="mb-6">LuxBeyond reserves the right to modify or update this refund policy as necessary. Any changes will be communicated through our official channels.</p>
<p>For any questions or refund requests, please contact our support team.</p>',
                    'meta_title'       => 'Refund Policy - LuxBeyond',
                    'meta_description' => 'Read our refund and return policy for orders from the USA, UK, Korea, Dubai, and India.',
                ],
            ],
            [
                'key'     => 'terms-of-service',
                'title'   => 'Terms of Service',
                'section' => 'page',
                'is_active' => true,
                'content' => [
                    'heading'          => 'Terms of Service',
                    'body'             => '<p>By using LuxBeyond, you agree to our terms and conditions outlined below.</p>',
                    'meta_title'       => 'Terms of Service - LuxBeyond',
                    'meta_description' => 'Read our terms of service.',
                ],
            ],
            [
                'key'     => 'help-center',
                'title'   => 'Help Center & FAQs',
                'section' => 'help',
                'is_active' => true,
                'content' => [
                    'heading'    => 'How can we help you?',
                    'subheading' => 'Find answers to common questions or reach out to our dedicated support team directly.',
                    'contact'    => [
                        'email'    => 'info.luxbeyond@gmail.com',
                        'phone'    => '+880 1404-458662',
                        'facebook' => 'https://www.facebook.com/luxbeyond.store',
                    ],
                    'sections' => [
                        [
                            'title' => 'General Information',
                            'icon'  => 'Info',
                            'faqs'  => [
                                [
                                    'q' => 'What is LuxBeyond?',
                                    'a' => 'LuxBeyond is a premium platform connecting buyers with global products. We specialize in importing authentic, high-quality items primarily from the USA, UK, and Korea—bringing the world right to your doorstep.',
                                ],
                                [
                                    'q' => 'Are the products authentic?',
                                    'a' => 'Absolutely. We guarantee 100% authenticity for every product. All items are acquired directly from reliable international vendors and authorized retailers.',
                                ],
                                [
                                    'q' => 'How does the service work?',
                                    'a' => 'We operate on a pre-order system. You place an order (or request a specific product), provide a 50% advance payment, and we arrange the international shipping. The remaining balance is paid upon successful delivery in Bangladesh.',
                                ]
                            ]
                        ],
                        [
                            'title' => 'Ordering & Shipping',
                            'icon'  => 'Package',
                            'faqs'  => [
                                [
                                    'q' => 'How long does shipping take?',
                                    'a' => 'Orders typically arrive within 3 to 4 weeks. Once your order is placed, you will receive updates throughout the shipping process from origin to your doorstep.',
                                ],
                                [
                                    'q' => 'Can I request a product not listed on the website?',
                                    'a' => 'Yes! You can use our <a href="/request-product" class="text-primary hover:underline">Product Request</a> feature to ask for any item from our supported countries, and our team will provide a cost estimate for importing it.',
                                ],
                                [
                                    'q' => 'How do I track my order?',
                                    'a' => 'You can track the status of your order through the <a href="/track-order" class="text-primary hover:underline">Track Order</a> page using your Request Number/Order ID, or check the My Orders section in your dashboard.',
                                ]
                            ]
                        ],
                        [
                            'title' => 'Payments & Refunds',
                            'icon'  => 'CreditCard',
                            'faqs'  => [
                                [
                                    'q' => 'What are the payment terms?',
                                    'a' => 'We require a 50% advance payment to confirm and process your import order. The remaining 50% balance must be paid upon delivery.',
                                ],
                                [
                                    'q' => 'What payment methods do you accept?',
                                    'a' => 'We accept bKash and direct bank transfers. Detailed instructions are provided during checkout.',
                                ],
                                [
                                    'q' => 'What is your refund policy?',
                                    'a' => 'You are eligible for a full refund of the advance payment if you cancel <strong>before</strong> the item is purchased internationally. If we fail to deliver within the promised timeframe, you are also eligible for a full refund. For full details, please review our <a href="/refund-policy" class="text-primary hover:underline">Refund Policy</a>.',
                                ]
                            ]
                        ]
                    ]
                ],
            ],
        ];

        foreach ($pages as $page) {
            PageContent::updateOrCreate(
                ['key' => $page['key']],
                $page
            );
        }
    }
}
