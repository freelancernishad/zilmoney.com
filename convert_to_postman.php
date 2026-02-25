<?php

$jsonFile = 'routes_list.json';
if (!file_exists($jsonFile)) {
    die("Error: routes_list.json not found. Run 'php artisan route:list --json > routes_list.json' first.\n");
}

$routes = json_decode(file_get_contents($jsonFile), true);
if (!$routes) {
    die("Error: Failed to decode routes_list.json\n");
}

$collection = [
    'info' => [
        'name' => 'Project API Collection (100% Complete)',
        'description' => 'Fully comprehensive collection covering every controller, including Admin Management, Media, Email, and Support.',
        'schema' => 'https://schema.getpostman.com/json/collection/v2.1.0/collection.json'
    ],
    'item' => [],
    'variable' => [
        ['key' => 'baseUrl', 'value' => 'http://localhost:8000', 'type' => 'string'],
        ['key' => 'token', 'value' => '', 'type' => 'string']
    ]
];

$rootItems = [];

// =================================================================================
// THE ULTIMATE BODY MAPPING ARRAY
// =================================================================================
$bodies = [
    // -----------------------------------------------------------------------------
    // AUTH - USER
    // -----------------------------------------------------------------------------
    'AuthUserController@register' => [
        'name' => 'New User',
        'email' => 'newuser@example.com',
        'password' => 'password123',
        'password_confirmation' => 'password123',
        'verify_url' => 'http://frontend.app/verify'
    ],
    'AuthUserController@login' => [
        'email' => 'newuser@example.com',
        'password' => 'password123',
        'device_name' => 'Postman Client'
    ],
    'AuthUserController@changePassword' => [
        'current_password' => 'password123',
        'new_password' => 'newpassword123',
        'new_password_confirmation' => 'newpassword123'
    ],
    'UserPasswordResetController@sendResetLinkEmail' => [
        'email' => 'newuser@example.com',
        'redirect_url' => 'http://frontend.app/reset-password'
    ],
    'UserPasswordResetController@reset' => [
        'token' => 'token_from_email',
        'email' => 'newuser@example.com',
        'password' => 'newpassword123',
        'password_confirmation' => 'newpassword123'
    ],
    'UserController@update' => [
        'name' => 'User Updated Profile',
        'notes' => 'Updated via Postman'
    ],
    
    // -----------------------------------------------------------------------------
    // AUTH - ADMIN
    // -----------------------------------------------------------------------------
    'AdminAuthController@register' => [
        'name' => 'New Admin',
        'email' => 'admin@example.com',
        'password' => 'adminpass123',
        'password_confirmation' => 'adminpass123'
    ],
    'AdminAuthController@login' => [
        'email' => 'admin@example.com',
        'password' => 'adminpass123'
    ],
    'AdminPasswordResetController@sendResetLinkEmail' => [
        'email' => 'admin@example.com',
        'redirect_url' => 'http://frontend.app/admin/reset-password'
    ],
    'AdminPasswordResetController@reset' => [
        'token' => 'token_from_email',
        'email' => 'admin@example.com',
        'password' => 'newadminpass123',
        'password_confirmation' => 'newadminpass123'
    ],

    // -----------------------------------------------------------------------------
    // ADMIN USER MANAGEMENT (New additions)
    // -----------------------------------------------------------------------------
    'AdminUserController@update' => [
        'name' => 'Updated User Name',
        'email' => 'updated@example.com',
        'is_active' => true,
        'role' => 'user',
        'notes' => 'Updated by Admin',
        'phone' => '1234567890'
    ],
    'AdminUserController@resetPassword' => [
        'password' => 'NewUserPassword123!'
    ],
    'AdminUserController@updateNotes' => [
        'notes' => 'Important note about this user.'
    ],
    'AdminUserController@bulkAction' => [
        'action' => 'activate', // activate, deactivate, block, unblock
        'user_ids' => [1, 2, 3]
    ],
    
    // -----------------------------------------------------------------------------
    // ZILMONEY CORE
    // -----------------------------------------------------------------------------
    'BusinessController@store' => [
        'legal_business_name' => 'ACME Corp',
        'email' => 'business@acme.com',
        'address' => '123 Wall St',
        'phone' => '123-456-7890',
        'website' => 'https://acme.com'
    ],
    'PayeeController@store' => [
        'payee_name' => 'Vendor Inc.',
        'email' => 'vendor@example.com',
        'phone' => '555-0123',
        'type' => 'vendor',
        'address' => '456 Market St'
    ],
    'PaymentController@store' => [
        'account_id' => 1,
        'payee_id' => 1,
        'amount' => 500.00,
        'issue_date' => date('Y-m-d'),
        'check_number' => 1005,
        'memo' => 'Consulting Fees'
    ],
    'PlaidController@createLinkToken' => [
        'redirect_uri' => 'http://localhost:3000/plaid-callback',
        'item_id' => null
    ],

    // -----------------------------------------------------------------------------
    // ADMIN PLANS & TOOLS
    // -----------------------------------------------------------------------------
    'CouponController@store' => [
        'code' => 'SUMMER2026',
        'type' => 'percentage',
        'value' => 20,
        'valid_from' => date('Y-m-d'),
        'valid_until' => date('Y-m-d', strtotime('+3 months')),
        'usage_limit' => 100,
        'is_active' => true,
        'associations' => [['item_id' => 1, 'item_type' => 'plan']]
    ],
    'PlanController@store' => [
        'name' => 'Enterprise Plan',
        'duration' => 'yearly',
        'original_price' => 1200.00,
        'monthly_price' => 100.00,
        'discount_percentage' => 0,
        'features' => [['key' => 'max_users', 'value' => 'unlimited']]
    ],
    'FeatureController@store' => [
        'key' => 'new_feature_key',
        'title_template' => 'Feature: :value',
        'unit' => 'users'
    ],
    'EmailTemplateController@store' => [
        'name' => 'Welcome Email',
        'subject' => 'Welcome to our platform!',
        'content_html' => '<html><body>Welcome!</body></html>',
        'content_json' => '{}'
    ],
    'EmailSenderController@send' => [
        'template_id' => 1,
        'recipients' => ['all'], // or [1, 2, 3]
        'manual_emails' => 'extramail@example.com'
    ],
    'EmailSenderController@sendTest' => [
        'email' => 'admin@example.com',
        'subject' => 'Test Email',
        'content_html' => '<h1>This is a test</h1>'
    ],

    // -----------------------------------------------------------------------------
    // CONTENT & SUPPORT
    // -----------------------------------------------------------------------------
    'BlogPostController@store' => [
        'title' => 'New Blog Post',
        'slug' => 'new-blog-post',
        'content' => '<p>Content...</p>',
        'category_ids' => [1],
        'banner_image' => 'https://via.placeholder.com/800x400',
        'status' => 'published'
    ],
    'BlogCategoryController@store' => [
        'name' => 'Tech News',
        'slug' => 'tech-news',
        'parent_id' => null
    ],
    'AllowedOriginController@store' => [
        'origin_url' => 'https://trusted-partner.com'
    ],
    'ContactMessageController@send' => [
        'full_name' => 'John Visitor',
        'email' => 'visitor@example.com',
        'subject' => 'Inquiry',
        'message' => 'I have a question about pricing.'
    ],
    'SupportTicketApiController@store' => [
        'subject' => 'Issue with payment',
        'message' => 'I cannot process my payment.',
        'priority' => 'high'
    ],
    'AdminSupportTicketApiController@reply' => [
        'reply' => 'We are looking into this.',
        'status' => 'pending',
        'reply_id' => null
    ],
    'AdminSupportTicketApiController@updateStatus' => [
        'status' => 'closed'
    ],
    'NotificationController@createForUser' => [
        'user_id' => 1,
        'message' => 'System Alert',
        'type' => 'info',
        'related_model' => 'Ticket',
        'related_model_id' => 101
    ],
    'SystemSettingController@storeOrUpdate' => [
        ['key' => 'site_name', 'value' => 'Zilmoney App']
    ],
    'MediaController@upload' => [
        'file_url' => 'https://via.placeholder.com/500.png' 
    ]
];

foreach ($routes as $route) {
    if (strpos($route['uri'], '_ignition') === 0) continue;
    if (strpos($route['uri'], 'sanctum/csrf-cookie') === 0) continue;
    
    // Always skip manual account creation
    if ($route['name'] === 'accounts.store' || strpos($route['action'], 'AccountController@store') !== false) continue;

    $methods = explode('|', $route['method']);
    $method = $methods[0];
    if ($method === 'HEAD' && isset($methods[1])) $method = $methods[1];

    $uri = ltrim($route['uri'], '/');
    if ($uri === '') $uri = '/'; 
    $routeName = $route['name'] ?? $uri;
    $action = $route['action'];

    // Basic request structure
    $request = [
        'name' => "[$method] " . $routeName,
        'request' => [
            'method' => $method,
            'header' => [
                ['key' => 'Accept', 'value' => 'application/json', 'type' => 'text'],
                ['key' => 'Content-Type', 'value' => 'application/json', 'type' => 'text'],
                ['key' => 'Authorization', 'value' => 'Bearer {{token}}', 'type' => 'text']
            ],
            'url' => [
                'raw' => '{{baseUrl}}/' . ($uri === '/' ? '' : $uri),
                'host' => ['{{baseUrl}}'],
                'path' => $uri === '/' ? [] : explode('/', $uri)
            ],
            'description' => "Action: " . $action . "\nMiddleware: " . implode(', ', $route['middleware'])
        ],
        'response' => []
    ];

    // Path Variables
    if ($uri !== '/') {
        preg_match_all('/\{(.*?)\}/', $uri, $matches);
        if (!empty($matches[1])) {
            $request['request']['url']['variable'] = [];
            foreach ($matches[1] as $param) {
                $request['request']['url']['variable'][] = [
                    'key' => $param,
                    'value' => '',
                    'description' => 'Path parameter: ' . $param
                ];
            }
        }
    }

    // Body Generation Logic
    if (in_array($method, ['POST', 'PUT', 'PATCH'])) {
        $bodyData = [];
        $found = false;

        // Priority 1: Exact Action Match
        foreach ($bodies as $key => $data) {
            // Strict check: Must match end of string AND be preceded by '\' or be the start
            // This prevents AdminUserController matching UserController
            if (str_ends_with($action, '\\' . $key) || $action === $key || str_ends_with($action, '/' . $key)) { 
                $bodyData = $data;
                $found = true;
                break;
            }
            // Fallback for simple short names if not conflicting, but the above is safer.
            // If we provided a full class name in keys, we wouldn't have this issue.
            // Let's try a regex for the key appearing at the end, preceded by \ or start
            $escapedKey = preg_quote($key, '/');
            if (preg_match('/(?:^|\\\\)' . $escapedKey . '$/', $action)) {
                $bodyData = $data;
                $found = true;
                break;
            }
        }

        // Priority 2: Fuzzy URI Match (Fallback)
        if (!$found) {
            foreach ($bodies as $key => $data) {
                if (strpos($key, '/') !== false && strpos($uri, $key) !== false) {
                    $bodyData = $data;
                    $found = true;
                    break;
                }
            }
        }

        $request['request']['body'] = [
            'mode' => 'raw',
            'raw' => json_encode($bodyData, JSON_PRETTY_PRINT),
            'options' => ['raw' => ['language' => 'json']]
        ];
    }

    // Folder Structure Logic
    $segments = $uri === '/' ? ['Home'] : explode('/', $uri);
    $currentLevel = &$rootItems;
    $folderSegments = [];
    
    // Custom Grouping based on URI prefix
    if (strpos($uri, 'api/admin') === 0) {
        $folderSegments[] = 'API';
        $folderSegments[] = 'Admin';
        $remaining = array_diff($segments, ['api', 'admin']);
        foreach($remaining as $seg) {
             if (preg_match('/^\{.*\}$/', $seg)) break;
             if ($seg !== '') $folderSegments[] = ucfirst($seg);
        }
    } elseif (strpos($uri, 'api/zilmoney') === 0) {
        $folderSegments[] = 'API';
        $folderSegments[] = 'Zilmoney';
        $remaining = array_diff($segments, ['api', 'zilmoney']);
        foreach($remaining as $seg) {
             if (preg_match('/^\{.*\}$/', $seg)) break;
             if ($seg !== '') $folderSegments[] = ucfirst($seg);
        }
    } elseif (strpos($uri, 'api/auth') === 0) {
        $folderSegments[] = 'API';
        $folderSegments[] = 'Auth';
        $remaining = array_diff($segments, ['api', 'auth']);
        foreach($remaining as $seg) {
             if (preg_match('/^\{.*\}$/', $seg)) break; // Stop at parameters
             if ($seg !== '') $folderSegments[] = ucfirst($seg);
        }
    } elseif (strpos($uri, 'api') === 0) {
        $folderSegments[] = 'API';
        $remaining = array_diff($segments, ['api']);
        foreach($remaining as $seg) {
             if (preg_match('/^\{.*\}$/', $seg)) break; // Stop at parameters
             if ($seg !== '') $folderSegments[] = ucfirst($seg);
        }
    } elseif (strpos($uri, 'admin') === 0) {
        $folderSegments[] = 'Admin Web';
        $remaining = array_diff($segments, ['admin']);
        foreach($remaining as $seg) {
             if (preg_match('/^\{.*\}$/', $seg)) break; // Stop at parameters
             if ($seg !== '') $folderSegments[] = ucfirst($seg);
        }
    } else {
        // Default handling
        foreach ($segments as $segment) {
            if (preg_match('/^\{.*\}$/', $segment)) break; 
            if ($segment !== '') $folderSegments[] = ucfirst($segment);
        }
    }
    
    if (empty($folderSegments)) $folderSegments = ['General'];

    // Create Folders recursively
    foreach ($folderSegments as $fName) {
        $found = false;
        foreach ($currentLevel as &$item) {
            if (isset($item['name']) && $item['name'] === $fName && isset($item['item'])) {
                $currentLevel = &$item['item'];
                $found = true;
                break;
            }
        }
        if (!$found) {
            $newFolder = ['name' => $fName, 'item' => []];
            $currentLevel[] = $newFolder;
            $currentLevel = &$currentLevel[count($currentLevel) - 1]['item'];
        }
    }

    $currentLevel[] = $request;
}

$collection['item'] = $rootItems;

file_put_contents('postman_collection.json', json_encode($collection, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
echo "Ultimate Postman collection generated: postman_collection.json\n";
