<?php

namespace App\Enums;

enum CustomerLabels
{
    public static function getLabels(): array
    {
        return [
            ['name' => 'Happy Customer', 'details' => 'Expresses high satisfaction with your service or product.', 'group' => 'success'],
            ['name' => 'Referrer', 'details' => 'Actively refers to friends and family, contributing to new customer acquisition.', 'group' => 'success'],
            ['name' => 'Positive Reviewer', 'details' => 'Leaves high-quality, positive reviews for products or services.', 'group' => 'success'],
            ['name' => 'Advisor', 'details' => 'Provides valuable feedback and suggestions for product or service improvements.', 'group' => 'success'],
            ['name' => 'Churn Risk', 'details' => 'Previously active customers showing signs of inactivity or dissatisfaction.', 'group' => 'success'],
            ['name' => 'Complaint Resolver', 'details' => 'Had a past issue but accepted resolution and stayed loyal.', 'group' => 'success'],
            ['name' => 'Freebie Seeker', 'details' => 'Contacts primarily for samples, free gifts, or promotions without serious buying intent.', 'group' => 'success'],
            ['name' => 'Bargainer', 'details' => 'Customer who frequently negotiates or complains about price, asking for discounts repeatedly.', 'group' => 'primary'],
            ['name' => 'NCR (No Call Response)', 'details' => 'Rarely or never answers calls from customer support after placing an order.', 'group' => 'primary'],
            ['name' => 'Event-Driven Buyer', 'details' => 'Orders mostly during offers, sales, or special events.', 'group' => 'primary'],
            ['name' => 'Sensitive Customer', 'details' => 'Requires extra care due to strong reactions to small inconveniences.', 'group' => 'primary'],
            ['name' => 'Loyal Supporter', 'details' => 'Consistently buys, promotes brands on social media, and supports campaigns.', 'group' => 'primary'],
            ['name' => 'Product-Specific Buyer', 'details' => 'Regularly buys the same product(s) but does not try others.', 'group' => 'primary'],
            ['name' => 'Conflict Prone', 'details' => 'Customer who often argues, disputes, or behaves rudely with customer support.', 'group' => 'danger'],
            ['name' => 'Authenticity Doubter', 'details' => 'Customer who regularly questions product authenticity or spreads doubts publicly.', 'group' => 'danger'],
            ['name' => 'Order Refusal', 'details' => 'Places an order but refuses to collect it upon delivery without valid reason.', 'group' => 'danger'],
            ['name' => 'Bad Experience', 'details' => 'Has reported dissatisfaction with product/service, possibly leaving negative feedback.', 'group' => 'danger'],
            ['name' => 'No Purchase Engager', 'details' => 'Frequently engages with customer support for inquiries and suggestions but never orders.', 'group' => 'danger']
        ];
    }

    public static function getLabel(int $index): string
    {
        return self::getLabels()[$index]['name'] ?? 'Unknown';
    }

    public static function getLabelDetails(int $index): string
    {
        return self::getLabels()[$index]['details'] ?? '';
    }

    public static function getLabelGroup(int $index): string
    {
        return self::getLabels()[$index]['group'] ?? 'info';
    }
}
