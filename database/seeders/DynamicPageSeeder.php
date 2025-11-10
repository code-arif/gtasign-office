<?php

namespace Database\Seeders;

use App\Models\DynamicPage;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DynamicPageSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DynamicPage::insert([

            [
                "page_title" => "Privacy Policy",
                "page_slug" => "privacy-policy",
                "page_content" => "1. Introduction

This Privacy Policy explains how we collect, use, disclose, and protect your personal information when you use the Signage Booking Website ShaShh.

2. Information We Collect

We collect information that you provide to us when you create an account, make a booking, or upload content. This may include your name, email address, payment information, and any other information you choose to provide.

3. Use of Information

We use the information we collect to provide and improve our services, process payments, communicate with you, and for other customer service purposes. We may also use your information for marketing and promotional purposes.

4. Disclosure of Information

We may disclose your information to third parties in the following circumstances:

To service providers who perform services on our behalf.

To comply with legal obligations.

To protect our rights and property.

5. Security

We take reasonable measures to protect your personal information from unauthorized access, use, or disclosure. However, no internet-based service can be completely secure, and we cannot guarantee the absolute security of your information.

6. Your Rights

You have the right to access, correct, or delete your personal information. You can exercise these rights by contacting us at support@shashh.com.

7. Changes to Privacy Policy

We may update this Privacy Policy from time to time. Any changes will be posted on the App, and your continued use of the App after such changes have been posted will constitute your acceptance of the changes.

1. Introduction

This Privacy Policy explains how we collect, use, disclose, and protect your personal information when you use the Signage Booking Website ShaShh.

2. Information We Collect

We collect information that you provide to us when you create an account, make a booking, or upload content. This may include your name, email address, payment information, and any other information you choose to provide.

3. Use of Information

We use the information we collect to provide and improve our services, process payments, communicate with you, and for other customer service purposes. We may also use your information for marketing and promotional purposes.

4. Disclosure of Information

We may disclose your information to third parties in the following circumstances:

To service providers who perform services on our behalf.

To comply with legal obligations.

To protect our rights and property.

5. Security

We take reasonable measures to protect your personal information from unauthorized access, use, or disclosure. However, no internet-based service can be completely secure, and we cannot guarantee the absolute security of your information.

6. Your Rights

You have the right to access, correct, or delete your personal information. You can exercise these rights by contacting us at support@shashh.com.

7. Changes to Privacy Policy

We may update this Privacy Policy from time to time. Any changes will be posted on the App, and your continued use of the App after such changes have been posted will constitute your acceptance of the changes.",
            ],
            [
                "page_title" => "Terms & Condation",
                "page_slug" => "terms-and-condation",
                "page_content" => "1. Introduction

Welcome to the Signage Booking Platform ShaShh. These Terms and Conditions govern your use of the Website and the services provided through it. By using the Website, you agree to comply with these Terms. If you do not agree with any part of these Terms, please do not use the App.

2. Services

The App allows users to locate, book, and make payments for public signage spaces. Additional services include content display, artwork services, and promotional features.

3. User Accounts

The App allows users to locate, book, and make payments for public signage spaces. Additional services include content display, artwork services, and promotional features.

4. Booking and Payments

All bookings and payments made through the App are subject to availability and confirmation. Payment methods include Mada, Visa, Mastercard, Apple Pay, and STC Pay.

5. Content Upload

Users can upload content (images or short videos) for display on public signage. All content must comply with our content guidelines, and we reserve the right to remove any content that violates these guidelines.

6. Cancellations and Refunds

Bookings can be canceled according to our Refund Policy. Refunds will be processed as per the conditions outlined in the Refund Policy.

7. Intellectual Property

All content and materials available on the App, including but not limited to text, graphics, logos, and software, are the property of the App or its licensors and are protected by copyright and other intellectual property laws.

8. Limitation of Liability

Bookings can be canceled according to our Refund Policy. Refunds will be processed as per the conditions outlined in the Refund Policy.

9. Governing Law

These Terms are governed by and construed in accordance with the laws of Saudi Arabia. Any disputes arising under these Terms shall be subject to the exclusive jurisdiction of the courts of Saudi Arabia.

10. Changes to Terms

We reserve the right to modify these Terms at any time. Any changes will be posted on the App, and your continued use of the App after such changes have been posted will constitute your acceptance of the changes.",
            ],
        ]);
    }
}
