```markdown
# Filipino Cookbook API

A secure RESTful API for Filipino recipes built with **Slim Framework**, **MySQL**, and **Token-Based Authentication**.

---

##  API Description

**Purpose:** Provide a secure API for retrieving, searching, and adding Filipino food recipes.

**Type of Information:** Filipino food names, categories, origins, instructions, and ingredients.

**Intended Users:** Developers building Filipino food applications, students learning API development.

**Main Functions:**
- Retrieve all foods with their ingredients
- Get specific food details by ID
- Search foods by name
- Filter foods by category or origin
- Add new Filipino food recipes
- Get a random food suggestion

**Technologies Used:** PHP, Slim Framework, MySQL, JSON, Composer

---

##  Features

-  Retrieve all Filipino foods
-  Get food by ID
-  Search food by name
-  Get all categories
-  Get all ingredients
-  Add new food (POST)
-  Get random food
-  Get foods by category
-  Get foods by origin
-  Token-based authentication
-  Input sanitization
-  JSON responses
-  Secure error handling

---

##  Technologies Used

| Technology | Purpose |
|------------|---------|
| **PHP** | Backend programming language |
| **Slim Framework 4** | PHP micro-framework for routing |
| **MySQL** | Relational database |
| **Composer** | Dependency management |
| **PDO** | Database access layer |
| **JSON** | Data format for API responses |
| **XAMPP** | Local development server |
| **Thunder Client** | API testing |
| **Git/GitHub** | Version control |

---

##  Installation Instructions

### 1. Clone the Repository

```bash
git clone https://github.com/Miguel-Frago/filipino-cookbook-api-frago.git
cd filipino-cookbook-api-frago
```

### 2. Install Composer Dependencies

```bash
composer install
```

### 3. Setup Database

**Option A: Command Line**
```bash
mysql -u root -p < filipino_foods_relational.sql
```

**Option B: phpMyAdmin**
1. Open phpMyAdmin
2. Click **"Import"**
3. Select `filipino_foods_relational.sql`
4. Click **"Go"**

**Database Name:** `filipino_cookbook_api`

**Tables:**
- `categories` – Food categories (Appetizer, Main Dish, etc.)
- `origins` – Food origins (Bicol, Ilocos, etc.)
- `foods` – Main food records
- `ingredients` – All ingredients
- `food_ingredients` – Junction table linking foods and ingredients

**Database Relationship:**
```
categories → foods ← origins
foods → food_ingredients ← ingredients
```

### 4. Configure Database Connection

Open `public/index.php` and update these lines:

```php
$dbHost = 'localhost';
$dbName = 'filipino_cookbook_api';
$dbUser = 'root';       // Your MySQL username
$dbPass = '';           // Your MySQL password
```

### 5. Start the Server

**Option A: PHP Built-in Server**
```bash
php -S localhost:8080 -t public
```

**Option B: XAMPP**
1. Place project in `C:\xampp\htdocs\`
2. Start Apache and MySQL
3. Access at: `http://localhost/filipino-cookbook-api-frago/public/`

---

##  Authentication

### Method: Bearer Token Authentication

**Token:** `dmmmsu-cookbook-token-2026`

All protected endpoints require this header:

```http
Authorization: Bearer dmmmsu-cookbook-token-2026
```

### Missing or Invalid Token Response (401):

```json
{
  "status": "error",
  "message": "Unauthorized access. Valid API token is required."
}
```

---

##  API Endpoints

### Public Routes (No Token Required)

| Method | Endpoint | Description |
|--------|----------|-------------|
| `GET` | `/` | Welcome message |

### Protected Routes (Token Required)

| Method | Endpoint | Description |
|--------|----------|-------------|
| `GET` | `/api/foods` | Get all foods with ingredients |
| `GET` | `/api/foods/{id}` | Get food by ID |
| `GET` | `/api/foods/search/{name}` | Search foods by name |
| `GET` | `/api/foods/random` | Get a random Filipino food |
| `GET` | `/api/categories` | Get all categories |
| `GET` | `/api/categories/{id}/foods` | Get foods by category |
| `GET` | `/api/ingredients` | Get all ingredients |
| `GET` | `/api/origins/{id}/foods` | Get foods by origin |
| `POST` | `/api/foods` | Add a new food |

---

##  Endpoint Documentation

### 1. Get All Foods

**Endpoint:** `GET /api/foods`

**Description:** Returns all Filipino foods with their ingredients, categories, and origins.

**Headers:**
```
Authorization: Bearer dmmmsu-cookbook-token-2026
```

**Example Request:**
```http
GET http://localhost:8080/api/foods
Authorization: Bearer dmmmsu-cookbook-token-2026
```

**Example Response (200 OK):**
```json
[
  {
    "food_id": 1,
    "food_name": "Adobo",
    "instructions": "Marinate the meat with soy sauce, vinegar, garlic, bay leaves, and peppercorn...",
    "category_name": "Main Dish",
    "origin_name": "Philippines",
    "ingredients": ["Chicken or pork", "Soy sauce", "Vinegar", "Garlic", "Bay leaves", "Peppercorn", "Cooking oil"]
  }
]
```

---

### 2. Get Food by ID

**Endpoint:** `GET /api/foods/{id}`

**Description:** Returns a single food record by its ID.

**Headers:**
```
Authorization: Bearer dmmmsu-cookbook-token-2026
```

**Example Request:**
```http
GET http://localhost:8080/api/foods/1
Authorization: Bearer dmmmsu-cookbook-token-2026
```

**Example Response (200 OK):**
```json
{
  "food_id": 1,
  "food_name": "Adobo",
  "instructions": "Marinate the meat with soy sauce...",
  "category_name": "Main Dish",
  "origin_name": "Philippines",
  "ingredients": ["Chicken or pork", "Soy sauce", "Vinegar", "Garlic", "Bay leaves", "Peppercorn", "Cooking oil"]
}
```

**Example Error Response (404 Not Found):**
```json
{
  "status": "error",
  "message": "Food not found"
}
```

---

### 3. Search Food by Name

**Endpoint:** `GET /api/foods/search/{name}`

**Description:** Searches for foods with a partial name match.

**Headers:**
```
Authorization: Bearer dmmmsu-cookbook-token-2026
```

**Example Request:**
```http
GET http://localhost:8080/api/foods/search/adobo
Authorization: Bearer dmmmsu-cookbook-token-2026
```

**Example Response (200 OK):**
```json
[
  {
    "food_id": 1,
    "food_name": "Adobo",
    "instructions": "...",
    "category_name": "Main Dish",
    "origin_name": "Philippines",
    "ingredients": ["Chicken or pork", "Soy sauce", ...]
  }
]
```

---

### 4. Get Random Food

**Endpoint:** `GET /api/foods/random`

**Description:** Returns a randomly selected Filipino food.

**Headers:**
```
Authorization: Bearer dmmmsu-cookbook-token-2026
```

**Example Request:**
```http
GET http://localhost:8080/api/foods/random
Authorization: Bearer dmmmsu-cookbook-token-2026
```

**Example Response (200 OK):**
```json
{
  "status": "success",
  "data": {
    "food_id": 7,
    "food_name": "Laing",
    "instructions": "Cook dried taro leaves in coconut milk...",
    "category_name": "Vegetable Dish",
    "origin_name": "Bicol Region",
    "ingredients": ["Dried taro leaves", "Coconut milk", ...]
  }
}
```

---

### 5. Get All Categories

**Endpoint:** `GET /api/categories`

**Description:** Returns all food categories.

**Headers:**
```
Authorization: Bearer dmmmsu-cookbook-token-2026
```

**Example Request:**
```http
GET http://localhost:8080/api/categories
Authorization: Bearer dmmmsu-cookbook-token-2026
```

**Example Response (200 OK):**
```json
[
  {"category_id": 1, "category_name": "Appetizer"},
  {"category_id": 2, "category_name": "Dessert"},
  {"category_id": 3, "category_name": "Grilled Dish"},
  {"category_id": 4, "category_name": "Main Dish"},
  {"category_id": 5, "category_name": "Noodle Dish"},
  {"category_id": 6, "category_name": "Soup"},
  {"category_id": 7, "category_name": "Vegetable Dish"}
]
```

---

### 6. Get Foods by Category

**Endpoint:** `GET /api/categories/{id}/foods`

**Description:** Returns all foods in a specific category.

**Headers:**
```
Authorization: Bearer dmmmsu-cookbook-token-2026
```

**Example Request:**
```http
GET http://localhost:8080/api/categories/4/foods
Authorization: Bearer dmmmsu-cookbook-token-2026
```

**Example Response (200 OK):**
```json
{
  "status": "success",
  "data": [
    {
      "food_id": 1,
      "food_name": "Adobo",
      "instructions": "...",
      "category_name": "Main Dish",
      "origin_name": "Philippines",
      "ingredients": [...]
    }
  ]
}
```

**Example Error Response (404 Not Found):**
```json
{
  "status": "error",
  "message": "Category not found"
}
```

---

### 7. Get All Ingredients

**Endpoint:** `GET /api/ingredients`

**Description:** Returns all ingredients.

**Headers:**
```
Authorization: Bearer dmmmsu-cookbook-token-2026
```

**Example Request:**
```http
GET http://localhost:8080/api/ingredients
Authorization: Bearer dmmmsu-cookbook-token-2026
```

**Example Response (200 OK):**
```json
[
  {"ingredient_id": 1, "ingredient_name": "Annatto oil"},
  {"ingredient_id": 2, "ingredient_name": "Bagoong"},
  {"ingredient_id": 3, "ingredient_name": "Banana blossom"}
]
```

---

### 8. Get Foods by Origin

**Endpoint:** `GET /api/origins/{id}/foods`

**Description:** Returns all foods from a specific origin.

**Headers:**
```
Authorization: Bearer dmmmsu-cookbook-token-2026
```

**Example Request:**
```http
GET http://localhost:8080/api/origins/2/foods
Authorization: Bearer dmmmsu-cookbook-token-2026
```

**Example Response (200 OK):**
```json
{
  "status": "success",
  "data": [
    {
      "food_id": 5,
      "food_name": "Bicol Express",
      "instructions": "...",
      "category_name": "Main Dish",
      "origin_name": "Bicol Region",
      "ingredients": [...]
    }
  ]
}
```

**Example Error Response (404 Not Found):**
```json
{
  "status": "error",
  "message": "Origin not found"
}
```

---

### 9. Add New Food

**Endpoint:** `POST /api/foods`

**Description:** Adds a new Filipino food record.

**Headers:**
```
Authorization: Bearer dmmmsu-cookbook-token-2026
Content-Type: application/json
```

**Example Request:**
```http
POST http://localhost:8080/api/foods
Authorization: Bearer dmmmsu-cookbook-token-2026
Content-Type: application/json

{
  "food_name": "Dinengdeng",
  "category_id": 7,
  "origin_id": 3,
  "instructions": "Boil vegetables with bagoong-based broth and add grilled fish before serving.",
  "ingredient_ids": [10, 15, 22]
}
```

**Example Response (201 Created):**
```json
{
  "status": "success",
  "message": "Food added successfully."
}
```

**Example Error Response (400 Bad Request):**
```json
{
  "status": "error",
  "message": "Missing field: food_name"
}
```

---

##  HTTP Status Codes

| Status | Meaning | Description |
|--------|---------|-------------|
| `200` | OK | Request completed successfully |
| `201` | Created | Resource created successfully |
| `400` | Bad Request | Invalid request or parameter |
| `401` | Unauthorized | Missing or invalid authentication |
| `404` | Not Found | Requested resource was not found |
| `500` | Internal Server Error | Server encountered an error |

---

##  Security Features

| Feature | Description |
|---------|-------------|
| **Bearer Token Authentication** | All `/api` endpoints require a valid token |
| **Input Sanitization** | Uses `trim()` and `htmlspecialchars()` to prevent XSS |
| **Input Validation** | Validates required fields and foreign key existence |
| **Prepared SQL Statements** | Prevents SQL injection attacks |
| **Secure Error Handling** | No sensitive database/error details exposed |

---

##  Testing Evidence

### Welcome Route (No Token)
![Welcome Route](screenshots/welcome.png)

### Get All Foods (With Token)
![Get All Foods](screenshots/get-all-foods.png)

### Get Food by ID
![Get Food by ID](screenshots/get-food-by-ID.png)

### Get Food by NAME
![Get Food by NAME](screenshots/get-food-by-NAME.png)

### Get All Categories
![Get All Categories](screenshots/get-all-categories.png)

### Get Food by Categories
![Get Food by Categories](screenshots/get-food-by-categories.png)

### Get All Ingredients
![Get All Ingredients](screenshots/get-all-ingredients.png)

### Get Food by Origin
![Get Food by Origin](screenshots/get-food-by-origin.png)

### Get Random Food
![Get Random Food](screenshots/get-random-food.png)

### Unauthorized Access (No Token)
![Unauthorized](screenshots/unauthorized.png)

### Add New Food (POST)
![Add New Food](screenshots/post-food.png)

### Food Not Found -404 (GET)
![Food Not Found](screenshots/food-not-found.png)

### Validation Error -400 (POST)
![Validation Error](screenshots/validation-error.png)

---

##  Project Structure

```
filipino-cookbook-api-frago/
├── public/
│   └── index.php              # Main API entry point
├── vendor/                    # Composer dependencies
├── .gitignore                 # Git ignore rules
├── composer.json              # Composer configuration
├── composer.lock              # Composer lock file
├── filipino_foods_relational.sql  # Database dump
└── README.md                  # Documentation
```

---

##  Developer Information

**Developer:** Miguelito Frago  
**Course:** Bachelor of Science in Information Technology  
**Year and Section:** 4-B
**GitHub:** [Miguel-Frago](https://github.com/Miguel-Frago)  
**Repository:** [filipino-cookbook-api-frago](https://github.com/Miguel-Frago/filipino-cookbook-api-frago)  
**Date Completed:** July 2026

---

##  License

This project is for educational purposes only.

---

##  Acknowledgments

- **Slim Framework** – PHP micro-framework
- **MySQL** – Database management
- **Thunder Client** – API testing tool
- **Composer** – Dependency management
```
