<?php

namespace Database\Seeders;

use Illuminate\Support\Str;
use App\Models\TicketCategory;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class TicketCategorySeeder extends Seeder
{
    public function run()
    {
        $categories = [
            // 'General Query' => [],
            // 'Refund Issue' => [],
            // 'Authenticity Issue' => [],
            // 'Skincare Suggestion' => [],
            // 'Exchange Product' => [],
            // 'Product Query' => [],
            // 'Restock Reminder' => [],
            'Product-Related Issues' => [
                'Product Query' => "Product Description Clarification\nProduct Usage Instructions\nSkin Type Suitability\nAge/Gender Suitability\nHow to Apply / Layer",
                'Product Authenticity' => "Authenticity Verification\nBrand Origin Inquiry\nBarcode / QR Code Scan Issues",
                'Ingredients & Conflicts' => "Ingredient List Request\nIngredient Allergies or Reactions\nIngredient Conflicts (e.g., Vitamin C + AHA)",
                'Product Pricing' => "Price Too High\nPrice Mismatch\nDiscount/Offer Not Applied",
                'Product Availability' => "Out of Stock\nRestock Reminder Request\nDiscontinued Product Query",
                'Product Expiry' => "Expiry Date Inquiry\nNear-Expiry Product Concern",
                'Others' => "Packaging Issue\nManufacturing Date Missing\nProduct Recommendation Request",
            ],
            'Order-Related Issues' => [
                'Order Status' => "Order in Process\nShipped but Not Received\nDelay in Delivery",
                'Order Changes' => "Add/Remove Product\nChange Delivery Address\nChange Contact Number",
                'Order Cancellation' => "Cancel Before Shipment\nCancel After Shipment\nCancel by Mistake",
                'Returns & Exchanges' => "Return Request (Reason: Damaged/Not Satisfied)\nExchange Request (Size/Type/Other)\nWrong Product Received",
                'Bulk Orders / Corporate Gifting' => "Custom Order Inquiry\nSpecial Discounts Request",
            ],
            'Payment & Refund' => [
                'Payment Issue' => "Payment Failed\nDouble Payment\nPayment Not Reflected",
                'Refund Issue' => "Refund Not Received\nRefund Timeline Inquiry\nRefund Method Change",
                'Pricing Issues' => "Discount Not Applied\nCharged Wrong Amount\nCart Total Mismatch",
                'Advance Payment' => "UPI/Bank Transfer Issues\nPayment Proof Submission",
            ],
            'Shipping & Delivery' => [
                'Delivery Status' => "Where is My Order?\nCourier Delay\nTracking Number Not Working",
                'Delivery Issues' => "Delivered to Wrong Address\nPackage Opened / Damaged\nPartial Delivery",
                'Delivery Options' => "Express Delivery Inquiry\nPincode Not Serviceable\nPickup Option Request",
            ],
            'Account & Profile' => [
                'Account Access' => "Login Problems\nForgot Password\nEmail/Phone Not Working",
                'Profile Update' => "Name Change\nAddress Update\nPhone Number Update",
                'Loyalty & Rewards' => "Points Not Reflected\nReferral Bonus Not Received",
            ],
            'Pre-Sale Inquiries' => [
                'Product Suggestions' => "Routine Planning Help\nSkin Type Recommendation\nBudget-Based Product Help",
                'Combo/Offer Clarification' => "Buy 1 Get 1 Doubts\nFree Gift Criteria",
                'Ingredient Compatibility' => "Routine Conflicts\nDoctor Advised Ingredients",
            ],
            'Invoices & Documentation' => [
                'Invoice Request' => "Invoice Not Received\nWrong Billing Info",
                'GST / Tax Invoice' => "GST Details Needed\nTax Invoice Correction",
                'Warranty/Guarantee' => "Brand Warranty Inquiry\nAuthenticity Proof",
            ],
            'Promotions & Campaigns' => [
                'Discounts & Coupons' => "Coupon Not Working\nCode Expired Too Soon",
                'Social Media Offers' => "Facebook/Instagram Code Issue\nGiveaway Participation",
                'Event / Live Sale' => "Missed Deals\nTechnical Issues During Live",
            ],
            'Technical Issues' => [
                'Website/App Bugs' => "Page Not Loading\nProduct Not Adding to Cart\nPayment Gateway Crash",
                'Account Dashboard Errors' => "Order History Not Showing\nWishlist Not Saving",
                'Live Chat / Bot Problem' => "Chatbot Not Responding\nStuck in Loop",
            ],
            'Feedback & Suggestions' => [
                'Product Feedback' => "Positive/Negative Reviews\nSuggestions for Improvement",
                'Service Feedback' => "Delivery Experience\nCustomer Support Feedback",
                'Feature Requests' => "New Product Category\nApp Feature Suggestion",
            ],
            'Urgent / Escalations' => [
                'Need Immediate Help' => "Medical Reaction from Product\nPackage Lost in Transit\nNo Response from Support",
                'Legal or Dispute' => "Consumer Complaint Threat\nLegal Notice or Action",
            ],
        ];

        // Delete existing categories
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        DB::table('ticket_categories')->truncate();
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');

        foreach ($categories as $categoryName => $subCategories) {
            $parent = TicketCategory::create([
                'name' => $categoryName,
                'slug' => Str::slug($categoryName),
                'status' => 1,
            ]);

            if (!empty($subCategories) && is_array($subCategories)) {
                foreach ($subCategories as $subName => $description) {
                    TicketCategory::create([
                        'name' => $subName,
                        'slug' => Str::slug($subName),
                        'parent_id' => $parent->id,
                        'description' => $description,
                        'status' => 1,
                    ]);
                }
            }
        }
    }
}
