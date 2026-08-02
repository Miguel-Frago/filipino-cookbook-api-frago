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

### Core Features
-  Retrieve all Filipino foods
-  Get food by ID
-  Search food by name
-  Get all categories
-  Get all ingredients
-  Add new food (POST)
-  Token-based authentication
-  JSON responses
-  Secure error handling

### Enhancements (Added)
-  Get random food
-  Get foods by category
-  Get foods by origin
-  Input sanitization
-  Input validation
-  Environment variable support (`.env`)

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
| **vlucas/phpdotenv** | Environment variable management |

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

 **IMPORTANT:** This API uses environment variables for secure configuration. **DO NOT edit `public/index.php` directly!**

**Step 1:** Copy `.env.example` to `.env`:
```bash
cp .env.example .env
```

**Step 2:** Open `.env` and update your credentials:
```env
# Database Configuration
DB_HOST=localhost
DB_NAME=filipino_cookbook_api
DB_USER=root          # Your MySQL username
DB_PASS=              # Your MySQL password (leave blank if none)

# API Security
API_TOKEN=dmmmsu-cookbook-token-2026  # Default token (you can change this)
```

**Step 3:** Save the file

**What each variable means:**

| Variable | Description | Example |
|----------|-------------|---------|
| `DB_HOST` | MySQL server address | `localhost` |
| `DB_NAME` | Database name | `filipino_cookbook_api` |
| `DB_USER` | MySQL username | `root` |
| `DB_PASS` | MySQL password | `password123` (or blank) |
| `API_TOKEN` | Bearer token for API access | `dmmmsu-cookbook-token-2026` |

**Security Notes:**
-  The `.env` file is already in `.gitignore` – it will NOT be committed to GitHub
-  Your credentials are now secure and NOT exposed in the code
-  `public/index.php` reads from `.env` automatically – no editing needed
-  **NEVER** commit `.env` to GitHub
-  **NEVER** share your `.env` file publicly

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

**Default Token (For Testing):** `dmmmsu-cookbook-token-2026`

All protected endpoints require this header:

```http
Authorization: Bearer dmmmsu-cookbook-token-2026
```

### How to Change the Token

You can change the token to anything you want:

**1. Open `.env` file:**
```bash
notepad .env   # Windows
nano .env      # Linux/Mac
```

**2. Change the token:**
```env
# Change this line
API_TOKEN=dmmmsu-cookbook-token-2026

# To anything you want
API_TOKEN=my-custom-token-2026
```

**3. Restart the server:**
```bash
php -S localhost:8080 -t public
```

**4. Use your new token:**
```http
Authorization: Bearer my-custom-token-2026
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

| Method | Endpoint | Description | Type |
|--------|----------|-------------|------|
| `GET` | `/api/foods` | Get all foods with ingredients | Core |
| `GET` | `/api/foods/{id}` | Get food by ID | Core |
| `GET` | `/api/foods/search/{name}` | Search foods by name | Core |
| `GET` | `/api/categories` | Get all categories | Core |
| `GET` | `/api/ingredients` | Get all ingredients | Core |
| `POST` | `/api/foods` | Add a new food | Core |
| `GET` | `/api/foods/random` | Get a random Filipino food |  Enhancement |
| `GET` | `/api/categories/{id}/foods` | Get foods by category |  Enhancement |
| `GET` | `/api/origins/{id}/foods` | Get foods by origin |  Enhancement |

---

##  Endpoint Documentation

### 1. Get All Foods

**Endpoint:** `GET /api/foods`

**Description:** Returns all Filipino foods with their ingredients, categories, and origins.

**Headers:**
```
Authorization: Bearer YOUR_TOKEN
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
Authorization: Bearer YOUR_TOKEN
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
Authorization: Bearer YOUR_TOKEN
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

### 4. Get Random Food  ENHANCEMENT

**Endpoint:** `GET /api/foods/random`

**Description:** Returns a randomly selected Filipino food.

**Headers:**
```
Authorization: Bearer YOUR_TOKEN
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

**Example Error Response (404 Not Found):**
```json
{
  "status": "error",
  "message": "No foods found"
}
```

---

### 5. Get All Categories

**Endpoint:** `GET /api/categories`

**Description:** Returns all food categories.

**Headers:**
```
Authorization: Bearer YOUR_TOKEN
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

### 6. Get Foods by Category  ENHANCEMENT

**Endpoint:** `GET /api/categories/{id}/foods`

**Description:** Returns all foods in a specific category.

**Headers:**
```
Authorization: Bearer YOUR_TOKEN
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
Authorization: Bearer YOUR_TOKEN
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

### 8. Get Foods by Origin  ENHANCEMENT

**Endpoint:** `GET /api/origins/{id}/foods`

**Description:** Returns all foods from a specific origin.

**Headers:**
```
Authorization: Bearer YOUR_TOKEN
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
Authorization: Bearer YOUR_TOKEN
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
| **Environment Variables** | Credentials stored in `.env` (excluded from GitHub) |

---

##  Optional API Enhancements

### Overview

The following enhancements were added to the original Filipino Cookbook API as part of the optional enhancement requirements.

### 1. New Endpoints Added

| Endpoint | Method | Purpose |
|----------|--------|---------|
| `/api/foods/random` | GET | Returns a random Filipino food with all details |
| `/api/categories/{id}/foods` | GET | Returns all foods in a specific category |
| `/api/origins/{id}/foods` | GET | Returns all foods from a specific origin |

### 2. Security Features Added

| Feature | Description | Implementation |
|---------|-------------|----------------|
| **Input Sanitization** | Prevents XSS attacks | Uses `trim()` and `htmlspecialchars()` on user inputs |
| **Input Validation** | Ensures data integrity | Validates required fields, foreign key existence, and data types |
| **Environment Variables** | Protects sensitive data | Uses `.env` file for configuration (excluded from GitHub) |

### 3. Validation and Error Handling Improvements

| Improvement | Description |
|-------------|-------------|
| **Required Field Validation** | Checks all required fields in POST requests |
| **Foreign Key Validation** | Validates `category_id`, `origin_id`, and `ingredient_ids` exist |
| **Clear Error Messages** | Returns specific, user-friendly error messages |
| **Appropriate Status Codes** | Uses correct HTTP status codes (400, 404, 201, 401, etc.) |

### 4. File Modifications

| File | Changes Made |
|------|--------------|
| `public/index.php` | Added 3 new endpoints, input sanitization, input validation |
| `.env.example` | Added environment variable support for secure configuration |
| `.gitignore` | Added `.env` to prevent sensitive data exposure |
| `composer.json` | Added `vlucas/phpdotenv` dependency |

### 5. How to Test the Enhancements

#### Test Random Food Endpoint
```http
GET http://localhost:8080/api/foods/random
Authorization: Bearer dmmmsu-cookbook-token-2026
```

#### Test Foods by Category
```http
GET http://localhost:8080/api/categories/4/foods
Authorization: Bearer dmmmsu-cookbook-token-2026
```

#### Test Foods by Origin
```http
GET http://localhost:8080/api/origins/2/foods
Authorization: Bearer dmmmsu-cookbook-token-2026
```

#### Test Input Sanitization
Try adding a food with HTML tags:
```json
{
  "food_name": "<script>alert('test')</script>Dinengdeng",
  "category_id": 7,
  "origin_id": 3,
  "instructions": "Boil vegetables...",
  "ingredient_ids": [10, 15, 22]
}
```
The API will sanitize the input, converting `<script>` to `&lt;script&gt;`.

#### Test Validation
Try adding a food with invalid `category_id`:
```json
{
  "food_name": "Test Food",
  "category_id": 99,
  "origin_id": 3,
  "instructions": "Test instructions",
  "ingredient_ids": [10, 15, 22]
}
```
Expected Response:
```json
{
  "status": "error",
  "message": "Invalid category_id"
}
```

### 6. Screenshots of Enhancements

#### Random Food Endpoint
![Random Food](screenshots/get-random-food.png)

#### Foods by Category
![Foods by Category](screenshots/get-food-by-categories.png)

#### Foods by Origin
![Foods by Origin](screenshots/get-food-by-origin.png)

#### Validation Error
![Validation Error](screenshots/validation-error.png)

---

##  Testing Evidence

### Welcome Route (No Token)
![Welcome Route](screenshots/welcome.png)

### Get All Foods (With Token)
![Get All Foods](screenshots/get-all-foods.png)

### Get Food by ID
![Get Food by ID](screenshots/get-food-by-ID.png)

### Search Food by Name
![Search Food](screenshots/get-food-by-NAME.png)

### Get All Categories
![Get All Categories](screenshots/get-all-categories.png)

### Get All Ingredients
![Get All Ingredients](screenshots/get-all-ingredients.png)

### Unauthorized Access (No Token)
![Unauthorized](screenshots/unauthorized.png)

### Add New Food (POST)
![Add New Food](screenshots/post-food.png)

### Food Not Found (404)
![Food Not Found](screenshots/food-not-found.png)

---

##  Project Structure

```
filipino-cookbook-api-frago/
├── public/
│   └── index.php              # Main API entry point
├── screenshots/               # Testing evidence screenshots
│   ├── welcome.png
│   ├── get-all-foods.png
│   ├── get-food-by-ID.png
│   ├── get-food-by-NAME.png
│   ├── get-all-categories.png
│   ├── get-food-by-categories.png
│   ├── get-all-ingredients.png
│   ├── get-food-by-origin.png
│   ├── get-random-food.png
│   ├── post-food.png
│   ├── unauthorized.png
│   ├── food-not-found.png
│   └── validation-error.png
├── vendor/                    # Composer dependencies
├── .env                       # Environment variables (local - NOT on GitHub)
├── .env.example               # Environment variables template
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
- **vlucas/phpdotenv** – Environment variable management
