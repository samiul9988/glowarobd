<!-- Get All Products -->
<div class="endpoint section" id="all-products">
    <h2><span class="section-count">4</span>. Get All Products</h2>
    <div class="description">
        <p>
            <strong>Description:</strong> This API retrieves a list
            of all products available in the system.
        </p>
        <p>
            <strong>Usage:</strong> Include the <code>App-ID</code> and <code>Secret-Key</code> in the request headers for authentication with each request. The response will include a paginated list of products.
        </p>
        <p>
            <strong>Note:</strong> Pagination is supported. Use
            query parameters like <code>page</code> and
            <code>per_page</code> to control the response size. Maximum response size is 100.
        </p>
    </div>
    <p>
        <strong>Endpoint:</strong>
        <code><span class="fw-bold get">GET</span> <?='{{base_url}}'?>/products</code>
    </p>
    <div class="parameters">
        <h5>Request Headers:</h5>
        <table>
            <thead>
                <tr>
                    <th>Parameter</th>
                    <th>Type</th>
                    <th>Value</th>
                    <th>Required</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td><code>App-ID</code></td>
                    <td>String</td>
                    <td>
                        <code>{{ $user->app_id }}</code>
                    </td>
                    <td>Yes</td>
                </tr>
                <tr>
                    <td><code>Secret-Key</code></td>
                    <td>String</td>
                    <td>
                        <code>{{ limit_text($user->app_key) }}</code>
                    </td>
                    <td>Yes</td>
                </tr>
                <tr>
                    <td><code>Content-Type</code></td>
                    <td>String</td>
                    <td>
                        <code>application/json</code>
                    </td>
                    <td>Yes</td>
                </tr>
            </tbody>
        </table>
        <pre>

        </pre>
        <h5>Query Parameters:</h5>
        <table>
            <thead>
                <tr>
                    <th>Parameter</th>
                    <th>Type</th>
                    <th>Value</th>
                    <th>Required</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td><code>page</code></td>
                    <td>Integer</td>
                    <td>Page number (e.g., 1)</td>
                    <td>No</td>
                </tr>
                <tr>
                    <td><code>per_page</code></td>
                    <td>Integer</td>
                    <td>Number of items per page (e.g., 10)</td>
                    <td>No</td>
                </tr>
                <tr>
                    <td><code>category_ids</code></td>
                    <td>Array</td>
                    <td>Array of category IDs. Must be integer. (e.g., [1,2])</td>
                    <td>No</td>
                </tr>
                <tr>
                    <td><code>product_ids</code></td>
                    <td>Array</td>
                    <td>Array of product IDs. Must be integer. (e.g., [1,2])</td>
                    <td>No</td>
                </tr>
            </tbody>
        </table>
    </div>
    <p>
        <strong>Sample Request:</strong>
        <code><span class="fw-bold get">GET</span> <?='{{base_url}}'?>/products?category_ids[]=1&category_ids[]=2&product_ids[]=1&product_ids[]=4</code>
    </p>
    <p><strong>Response:</strong></p>
    <div class="response" style="max-height: 400px; overflow-y:scroll;">
        <pre>
{
    "success": true,
    "message": "Products fetched successfully",
    "data": [
        {
            "id": 1,
            "name": "3W CLINIC Snail Eye Cream 40ml",
            "slug": "3w-clinic-snail-eye-cream-40ml",
            "wholesale_price": "511.50",
            "mrp_price": "511.50",
            "stock": 1,
            "short_description": "<ul><li>Gradually dark circles around the eyes go away.\r\n</li><li>This eye cream revitalize and firm up the thin skin around the eyes.\r\n</li><li>Including Snail complex.\r\n</li><li>Snail extract helps to smoothing wrinkle your eye rims.\r\n</li><li>It gives nourishing and whitening effect to your skin.\r\n</li><li>Made in Korea.</li></ul>",
            "description": "This is cream for the skin around the eyes against wrinkles with snail mucus extract. This cream is created specifically for delicate skin around the eyes. Snail mucus extract helps restore cell structure.",
            "skin_types": [],
            "key_ingredients": [],
            "good_for": [],
            "ratings": 0,
            "reviews": 0,
            "product_categories": ["Toner & Serum"],
            "product_brands": ["3W Clinic"],
            "product_tags": [
                ""
            ],
            "thumbnail": "https://glowarobd.com/uploads/all/ePpW1B59JiQTQUddKbmDdCcTLoQXu8woXjYGwcFQ.jpg",
            "pictures": [
                "https://glowarobd.com/uploads/all/ePpW1B59JiQTQUddKbmDdCcTLoQXu8woXjYGwcFQ.jpg"
            ],
            "meta_title": "3W CLINIC Snail Eye Cream 40ml",
            "meta_description": "This is cream for the skin around the eyes against wrinkles with snail mucus extract. This cream is created specifically for delicate skin around the eyes. Snail mucus extract helps restore cell structure.",
            "meta_img": "https://glowarobd.com/uploads/all/ePpW1B59JiQTQUddKbmDdCcTLoQXu8woXjYGwcFQ.jpg",
            "meta_tags": []
        },
        {
            "id": 4,
            "name": "Boots Vitamin C Brightening Toning Water 100ml",
            "slug": "boots-vitamin-c-toner-100ml",
            "wholesale_price": "792.00",
            "mrp_price": "792.00",
            "stock": 2,
            "short_description": "<ul><li>Skin looks brighter within 14 days.\r\n</li><li>Suitable for all skin types.\r\n</li><li>Not Tested on Animals\r\n</li><li>Vegan – No animal derived or by-products</li></ul>",
            "description": "<p style=\"text-align: justify;\">With vitamin C and YUZU extract, this alcohol free toner helps reduce residue on skin for tightened looking and refined feeling pores.\r\n</p><p style=\"text-align: justify; \"><b>Ingredients:</b> Aqua (Water), PEG-40 hydrogenated castor oil, Propylene glycol, Methyl gluceth-20, Niacinamide, Sodium PCA, Hamamelis virginiana (Witch hazel) water, PEG-12 dimethicone, Sodium gluconate, Sodium lactate, Sodium benzoate, Potassium sorbate, Disodium EDTA, PEG-12 allyl ether, Sodium citrate, 3-O-Ethyl ascorbic acid, Citric acid, Butylene glycol, Aloe barbadensis leaf juice, Maltodextrin, Parfum (Fragrance), Limonene, Dipropylene glycol, Ascorbyl tetraisopalmitate, Sodium hyaluronate, Tocopheryl acetate, Benzyl benzoate, Citrus junos fruit extract, Hexyl cinnamal, Linalool, Butylphenyl methylpropional, Benzyl salicylate, Benzoic acid, Denatonium benzoate, Octadecyl di-t-butyl-4- hydroxyhydrocinnamate.</p>",
            "skin_types": [],
            "key_ingredients": [],
            "good_for": [],
            "ratings": 3.5,
            "reviews": 2,
            "product_categories": ["Toner"],
            "product_brands": ["Boots]",
            "product_tags": [
                "boots",
                "boots vitamin c",
                "Vitamin C Brightening Toner",
                "Brightening Toner",
                "Brightening Toning Water"
            ],
            "thumbnail": "https://glowarobd.com/uploads/all/jCvNcbJNuRXmFPTChlWcjO4DZA6OIUZeMSrtOn0Q.jpg",
            "pictures": [
                "https://glowarobd.com/uploads/all/M9PubW0wdfZEUfEdxA46X2Ww2R4WSP3w37e63jK6.jpg"
            ],
            "meta_title": "Boots Vitamin C Toner 100ml",
            "meta_description": "With vitamin C and YUZU extract, this alcohol free toner helps reduce residue on skin for tightened looking and refined feeling pores.\r\nIngredients: Aqua (Water), PEG-40 hydrogenated castor oil, Propylene glycol, Methyl gluceth-20, Niacinamide, Sodium PCA, Hamamelis virginiana (Witch hazel) water, PEG-12 dimethicone, Sodium gluconate, Sodium lactate, Sodium benzoate, Potassium sorbate, Disodium EDTA, PEG-12 allyl ether, Sodium citrate, 3-O-Ethyl ascorbic acid, Citric acid, Butylene glycol, Aloe barbadensis leaf juice, Maltodextrin, Parfum (Fragrance), Limonene, Dipropylene glycol, Ascorbyl tetraisopalmitate, Sodium hyaluronate, Tocopheryl acetate, Benzyl benzoate, Citrus junos fruit extract, Hexyl cinnamal, Linalool, Butylphenyl methylpropional, Benzyl salicylate, Benzoic acid, Denatonium benzoate, Octadecyl di-t-butyl-4- hydroxyhydrocinnamate.",
            "meta_img": "https://glowarobd.com/uploads/all/M9PubW0wdfZEUfEdxA46X2Ww2R4WSP3w37e63jK6.jpg",
            "meta_tags": []
        }
    ],
    "pagination": {
        "total": 2,
        "per_page": 10,
        "current_page": 1,
        "last_page": 1
    }
}</pre>
    </div>
</div>
