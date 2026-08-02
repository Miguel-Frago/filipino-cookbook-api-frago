<?php

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\Factory\AppFactory;
use Slim\Routing\RouteCollectorProxy;
use Dotenv\Dotenv;

require __DIR__ . '/../vendor/autoload.php';

// ---------- Load Environment Variables ----------
$dotenv = Dotenv::createImmutable(__DIR__ . '/..');
$dotenv->load();

// ---------- Database configuration ----------
$dbHost = $_ENV['DB_HOST'] ?? 'localhost';
$dbName = $_ENV['DB_NAME'] ?? 'filipino_cookbook_api';
$dbUser = $_ENV['DB_USER'] ?? 'root';
$dbPass = $_ENV['DB_PASS'] ?? '';

try {
    $db = new PDO("mysql:host=$dbHost;dbname=$dbName;charset=utf8mb4", $dbUser, $dbPass);
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die('Database connection failed: ' . $e->getMessage());
}

// ---------- Token constant ----------
define('API_TOKEN', $_ENV['API_TOKEN'] ?? 'dmmmsu-cookbook-token-2026');

// ---------- Create Slim app ----------
$app = AppFactory::create();

// Add JSON body parsing middleware
$app->addBodyParsingMiddleware();

// ============================================================
// CORS MIDDLEWARE - Allow cross-origin requests
// ============================================================

$app->add(function ($request, $handler) {
    $response = $handler->handle($request);
    
    // Allow all origins (or specify specific ones)
    $response = $response
        ->withHeader('Access-Control-Allow-Origin', '*')
        ->withHeader('Access-Control-Allow-Methods', 'GET, POST, PUT, DELETE, OPTIONS')
        ->withHeader('Access-Control-Allow-Headers', 'Content-Type, Authorization, X-Requested-With');
    
    // Handle preflight OPTIONS requests
    if ($request->getMethod() === 'OPTIONS') {
        return $response->withStatus(200);
    }
    
    return $response;
});

// ---------- Middleware for token validation ----------
$tokenMiddleware = function (Request $request, $handler) {
    $authHeader = $request->getHeaderLine('Authorization');
    if (preg_match('/Bearer\s+(.*)/i', $authHeader, $matches)) {
        $token = $matches[1];
        if ($token === API_TOKEN) {
            return $handler->handle($request);
        }
    }
    // Unauthorized
    $response = new \Slim\Psr7\Response();
    $response->getBody()->write(json_encode([
        'status'  => 'error',
        'message' => 'Unauthorized access. Valid API token is required.'
    ]));
    return $response->withStatus(401)->withHeader('Content-Type', 'application/json');
};

// ---------- Public welcome route (no token) ----------
$app->get('/', function (Request $request, Response $response) {
    $response->getBody()->write(json_encode([
        'message' => 'Welcome to the Secured Filipino Cookbook API',
        'note'    => 'Use a valid Bearer token to access /api endpoints.'
    ]));
    return $response->withHeader('Content-Type', 'application/json');
});

// ---------- Protected API routes (require token) ----------
$app->group('/api', function (RouteCollectorProxy $group) use ($db) {

    // 1. Get all foods with category, origin, and ingredients
    $group->get('/foods', function (Request $request, Response $response) use ($db) {
        $stmt = $db->query("
            SELECT f.food_id, f.food_name, f.instructions,
                   c.category_name, o.origin_name
            FROM foods f
            JOIN categories c ON f.category_id = c.category_id
            JOIN origins o ON f.origin_id = o.origin_id
            ORDER BY f.food_id
        ");
        $foods = $stmt->fetchAll(PDO::FETCH_ASSOC);

        foreach ($foods as &$food) {
            $ingStmt = $db->prepare("
                SELECT i.ingredient_name
                FROM food_ingredients fi
                JOIN ingredients i ON fi.ingredient_id = i.ingredient_id
                WHERE fi.food_id = ?
            ");
            $ingStmt->execute([$food['food_id']]);
            $food['ingredients'] = $ingStmt->fetchAll(PDO::FETCH_COLUMN);
        }

        $response->getBody()->write(json_encode($foods));
        return $response->withHeader('Content-Type', 'application/json');
    });

    // 2. Get random food (STATIC ROUTE - comes BEFORE {id})
    $group->get('/foods/random', function (Request $request, Response $response) use ($db) {
        $stmt = $db->query("
            SELECT f.food_id, f.food_name, f.instructions,
                   c.category_name, o.origin_name
            FROM foods f
            JOIN categories c ON f.category_id = c.category_id
            JOIN origins o ON f.origin_id = o.origin_id
            ORDER BY RAND()
            LIMIT 1
        ");
        $food = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$food) {
            $response->getBody()->write(json_encode([
                'status'  => 'error',
                'message' => 'No foods found'
            ]));
            return $response->withStatus(404)->withHeader('Content-Type', 'application/json');
        }

        $ingStmt = $db->prepare("
            SELECT i.ingredient_name
            FROM food_ingredients fi
            JOIN ingredients i ON fi.ingredient_id = i.ingredient_id
            WHERE fi.food_id = ?
        ");
        $ingStmt->execute([$food['food_id']]);
        $food['ingredients'] = $ingStmt->fetchAll(PDO::FETCH_COLUMN);

        $response->getBody()->write(json_encode([
            'status' => 'success',
            'data' => $food
        ]));
        return $response->withHeader('Content-Type', 'application/json');
    });

    // 3. Search food by name (STATIC ROUTE - comes BEFORE {id})
    $group->get('/foods/search/{name}', function (Request $request, Response $response, array $args) use ($db) {
        $name = '%' . $args['name'] . '%';
        $stmt = $db->prepare("
            SELECT f.food_id, f.food_name, f.instructions,
                   c.category_name, o.origin_name
            FROM foods f
            JOIN categories c ON f.category_id = c.category_id
            JOIN origins o ON f.origin_id = o.origin_id
            WHERE f.food_name LIKE ?
            ORDER BY f.food_id
        ");
        $stmt->execute([$name]);
        $foods = $stmt->fetchAll(PDO::FETCH_ASSOC);

        foreach ($foods as &$food) {
            $ingStmt = $db->prepare("
                SELECT i.ingredient_name
                FROM food_ingredients fi
                JOIN ingredients i ON fi.ingredient_id = i.ingredient_id
                WHERE fi.food_id = ?
            ");
            $ingStmt->execute([$food['food_id']]);
            $food['ingredients'] = $ingStmt->fetchAll(PDO::FETCH_COLUMN);
        }

        $response->getBody()->write(json_encode($foods));
        return $response->withHeader('Content-Type', 'application/json');
    });

    // 4. Get food by ID (VARIABLE ROUTE - comes AFTER static routes)
    $group->get('/foods/{id}', function (Request $request, Response $response, array $args) use ($db) {
        $id = (int)$args['id'];
        $stmt = $db->prepare("
            SELECT f.food_id, f.food_name, f.instructions,
                   c.category_name, o.origin_name
            FROM foods f
            JOIN categories c ON f.category_id = c.category_id
            JOIN origins o ON f.origin_id = o.origin_id
            WHERE f.food_id = ?
        ");
        $stmt->execute([$id]);
        $food = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$food) {
            $response->getBody()->write(json_encode([
                'status'  => 'error',
                'message' => 'Food not found'
            ]));
            return $response->withStatus(404)->withHeader('Content-Type', 'application/json');
        }

        $ingStmt = $db->prepare("
            SELECT i.ingredient_name
            FROM food_ingredients fi
            JOIN ingredients i ON fi.ingredient_id = i.ingredient_id
            WHERE fi.food_id = ?
        ");
        $ingStmt->execute([$id]);
        $food['ingredients'] = $ingStmt->fetchAll(PDO::FETCH_COLUMN);

        $response->getBody()->write(json_encode($food));
        return $response->withHeader('Content-Type', 'application/json');
    });

    // ============================================================
    // 4b. Get ingredients of a specific food (NEW ENDPOINT)
    // ============================================================
    $group->get('/foods/{id}/ingredients', function (Request $request, Response $response, array $args) use ($db) {
        $id = (int)$args['id'];
        
        // Check if food exists
        $foodCheck = $db->prepare("SELECT food_id, food_name FROM foods WHERE food_id = ?");
        $foodCheck->execute([$id]);
        $food = $foodCheck->fetch(PDO::FETCH_ASSOC);
        
        if (!$food) {
            $response->getBody()->write(json_encode([
                'status'  => 'error',
                'message' => 'Food not found'
            ]));
            return $response->withStatus(404)->withHeader('Content-Type', 'application/json');
        }

        // Get ingredients
        $stmt = $db->prepare("
            SELECT i.ingredient_id, i.ingredient_name
            FROM food_ingredients fi
            JOIN ingredients i ON fi.ingredient_id = i.ingredient_id
            WHERE fi.food_id = ?
            ORDER BY i.ingredient_name
        ");
        $stmt->execute([$id]);
        $ingredients = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $response->getBody()->write(json_encode([
            'food_id' => $id,
            'food_name' => $food['food_name'],
            'ingredient_count' => count($ingredients),
            'ingredients' => $ingredients
        ]));
        return $response->withHeader('Content-Type', 'application/json');
    });

    // 5. Get all categories
    $group->get('/categories', function (Request $request, Response $response) use ($db) {
        $stmt = $db->query("SELECT category_id, category_name FROM categories ORDER BY category_id");
        $categories = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $response->getBody()->write(json_encode($categories));
        return $response->withHeader('Content-Type', 'application/json');
    });

    // 6. Get foods by category ID
    $group->get('/categories/{id}/foods', function (Request $request, Response $response, array $args) use ($db) {
        $category_id = (int)$args['id'];
        
        $catCheck = $db->prepare("SELECT category_id FROM categories WHERE category_id = ?");
        $catCheck->execute([$category_id]);
        if (!$catCheck->fetch()) {
            $response->getBody()->write(json_encode([
                'status'  => 'error',
                'message' => 'Category not found'
            ]));
            return $response->withStatus(404)->withHeader('Content-Type', 'application/json');
        }

        $stmt = $db->prepare("
            SELECT f.food_id, f.food_name, f.instructions,
                   c.category_name, o.origin_name
            FROM foods f
            JOIN categories c ON f.category_id = c.category_id
            JOIN origins o ON f.origin_id = o.origin_id
            WHERE f.category_id = ?
            ORDER BY f.food_id
        ");
        $stmt->execute([$category_id]);
        $foods = $stmt->fetchAll(PDO::FETCH_ASSOC);

        foreach ($foods as &$food) {
            $ingStmt = $db->prepare("
                SELECT i.ingredient_name
                FROM food_ingredients fi
                JOIN ingredients i ON fi.ingredient_id = i.ingredient_id
                WHERE fi.food_id = ?
            ");
            $ingStmt->execute([$food['food_id']]);
            $food['ingredients'] = $ingStmt->fetchAll(PDO::FETCH_COLUMN);
        }

        $response->getBody()->write(json_encode([
            'status' => 'success',
            'data' => $foods
        ]));
        return $response->withHeader('Content-Type', 'application/json');
    });

    // 7. Get all ingredients
    $group->get('/ingredients', function (Request $request, Response $response) use ($db) {
        $stmt = $db->query("SELECT ingredient_id, ingredient_name FROM ingredients ORDER BY ingredient_id");
        $ingredients = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $response->getBody()->write(json_encode($ingredients));
        return $response->withHeader('Content-Type', 'application/json');
    });

    // 8. Get foods by origin ID
    $group->get('/origins/{id}/foods', function (Request $request, Response $response, array $args) use ($db) {
        $origin_id = (int)$args['id'];
        
        $origCheck = $db->prepare("SELECT origin_id FROM origins WHERE origin_id = ?");
        $origCheck->execute([$origin_id]);
        if (!$origCheck->fetch()) {
            $response->getBody()->write(json_encode([
                'status'  => 'error',
                'message' => 'Origin not found'
            ]));
            return $response->withStatus(404)->withHeader('Content-Type', 'application/json');
        }

        $stmt = $db->prepare("
            SELECT f.food_id, f.food_name, f.instructions,
                   c.category_name, o.origin_name
            FROM foods f
            JOIN categories c ON f.category_id = c.category_id
            JOIN origins o ON f.origin_id = o.origin_id
            WHERE f.origin_id = ?
            ORDER BY f.food_id
        ");
        $stmt->execute([$origin_id]);
        $foods = $stmt->fetchAll(PDO::FETCH_ASSOC);

        foreach ($foods as &$food) {
            $ingStmt = $db->prepare("
                SELECT i.ingredient_name
                FROM food_ingredients fi
                JOIN ingredients i ON fi.ingredient_id = i.ingredient_id
                WHERE fi.food_id = ?
            ");
            $ingStmt->execute([$food['food_id']]);
            $food['ingredients'] = $ingStmt->fetchAll(PDO::FETCH_COLUMN);
        }

        $response->getBody()->write(json_encode([
            'status' => 'success',
            'data' => $foods
        ]));
        return $response->withHeader('Content-Type', 'application/json');
    });

    // 9. Add new food (POST) - WITH DUPLICATE CHECKER
    $group->post('/foods', function (Request $request, Response $response) use ($db) {
        $data = $request->getParsedBody();

        $required = ['food_name', 'category_id', 'origin_id', 'instructions', 'ingredient_ids'];
        foreach ($required as $field) {
            if (!isset($data[$field]) || empty($data[$field])) {
                $response->getBody()->write(json_encode([
                    'status'  => 'error',
                    'message' => "Missing field: $field"
                ]));
                return $response->withStatus(400)->withHeader('Content-Type', 'application/json');
            }
        }

        // Input Sanitization
        $food_name = trim(htmlspecialchars($data['food_name']));
        $instructions = trim(htmlspecialchars($data['instructions']));
        $category_id = (int)$data['category_id'];
        $origin_id = (int)$data['origin_id'];

        // ============================================================
        // DUPLICATE CHECKER - Prevent duplicate food names
        // ============================================================
        $checkStmt = $db->prepare("SELECT food_id FROM foods WHERE LOWER(food_name) = LOWER(?)");
        $checkStmt->execute([$food_name]);
        if ($checkStmt->fetch()) {
            $response->getBody()->write(json_encode([
                'status'  => 'error',
                'message' => 'A food with this name already exists. Please use a different name.'
            ]));
            return $response->withStatus(409)->withHeader('Content-Type', 'application/json');
        }

        // Validate category_id
        $catStmt = $db->prepare("SELECT category_id FROM categories WHERE category_id = ?");
        $catStmt->execute([$category_id]);
        if (!$catStmt->fetch()) {
            $response->getBody()->write(json_encode([
                'status'  => 'error',
                'message' => 'Invalid category_id'
            ]));
            return $response->withStatus(400)->withHeader('Content-Type', 'application/json');
        }

        // Validate origin_id
        $origStmt = $db->prepare("SELECT origin_id FROM origins WHERE origin_id = ?");
        $origStmt->execute([$origin_id]);
        if (!$origStmt->fetch()) {
            $response->getBody()->write(json_encode([
                'status'  => 'error',
                'message' => 'Invalid origin_id'
            ]));
            return $response->withStatus(400)->withHeader('Content-Type', 'application/json');
        }

        // Generate next food_id
        $maxIdStmt = $db->query("SELECT MAX(food_id) AS max_id FROM foods");
        $maxId = $maxIdStmt->fetch(PDO::FETCH_ASSOC)['max_id'];
        $newFoodId = $maxId + 1;

        // Insert new food
        $insertStmt = $db->prepare("
            INSERT INTO foods (food_id, food_name, category_id, origin_id, instructions)
            VALUES (?, ?, ?, ?, ?)
        ");
        $insertStmt->execute([
            $newFoodId,
            $food_name,
            $category_id,
            $origin_id,
            $instructions
        ]);

        // Validate and insert ingredient IDs
        $ingredientIds = $data['ingredient_ids'];
        if (!is_array($ingredientIds)) {
            $response->getBody()->write(json_encode([
                'status'  => 'error',
                'message' => 'ingredient_ids must be an array'
            ]));
            return $response->withStatus(400)->withHeader('Content-Type', 'application/json');
        }

        foreach ($ingredientIds as $ingId) {
            $checkIng = $db->prepare("SELECT ingredient_id FROM ingredients WHERE ingredient_id = ?");
            $checkIng->execute([$ingId]);
            if (!$checkIng->fetch()) {
                $response->getBody()->write(json_encode([
                    'status'  => 'error',
                    'message' => "Ingredient ID $ingId does not exist"
                ]));
                return $response->withStatus(400)->withHeader('Content-Type', 'application/json');
            }

            $linkStmt = $db->prepare("INSERT INTO food_ingredients (food_id, ingredient_id) VALUES (?, ?)");
            $linkStmt->execute([$newFoodId, $ingId]);
        }

        $response->getBody()->write(json_encode([
            'status'  => 'success',
            'message' => 'Food added successfully.'
        ]));
        return $response->withStatus(201)->withHeader('Content-Type', 'application/json');
    });

})->add($tokenMiddleware);

// ---------- Run the app ----------
$app->run();