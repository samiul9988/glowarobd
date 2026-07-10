<script type="application/ld+json">
    {
        "@context": "https://schema.org",
        "@type": "FAQPage",
        "mainEntity": [
            @php
                $faqData = [];
                foreach ($schemaFaqs as $faq) {
                    $faqData[] = [
                        "@type" => "Question",
                        "name" => addslashes($faq['title'] ?? 'N/A'),
                        "acceptedAnswer" => [
                            "@type" => "Answer",
                            "text" => $faq['description'] ?? 'N/A'
                        ]
                    ];
                }
                echo json_encode($faqData, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
            @endphp
        ]
    }
</script>
