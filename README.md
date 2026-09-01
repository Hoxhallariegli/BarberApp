# 🍎 E4ProTech Starterkit Laravel

The ultimate **Laravel 12 & Livewire 3** starter kit, architected for scalability, speed, and a premium **Apple-style** user experience. This template automates the heavy lifting while strictly following **Enterprise Best Practices (DDD, Actions, DTOs, Repository-less Queries)**.

---

## 🚀 Key Features

- **Architectural Excellence**: Implements a clean Domain-Driven Design (DDD) inspired structure.
- **Layered Logic**: Complete separation of concerns using **Actions**, **DTOs**, and **Dedicated Query Classes**.
- **Centralized Validation**: All business rules are defined once in the Model's `rules()` method and shared across the entire UI.
- **Apple UI/UX**: Ultra-minimalist design, responsive grids, rounded-3xl corners, and full **Dark Mode** support.
- **Smart Data Components**: 
    - **Searchable Dropdowns**: Native `x-form.dropdown-search` with live filtering.
    - **Sortable Headers**: High-contrast blue headers with visual feedback and Model-controlled sorting.
    - **Create-on-the-fly**: Create related records directly from dropdowns without leaving the current form.
- **One-Command Scaffolding**: Generate a production-ready feature in seconds.
- **Surgical API Generation**: One flag to build a complete JSON API layer for any module.

---

## 🛠️ The Power of `new:view`

The core of this starter kit is the `php artisan new:view` command. It is an interactive wizard that builds a complete, layered module based on your database schema.

### 1. Run the Command
```bash
php artisan new:view {ModelName}
```

### 2. Advanced Options
*   `--api`: Generates a fully-functional API layer (Controllers, Resources, FormRequests).
*   `--firebase`: Automatically integrates Firebase Push Notifications into the Create Action.

### 3. Supported Field Types
The wizard supports the following database types for automatic scaffolding:
*   `string`: Standard short text (VARCHAR).
*   `text`: Long text for descriptions or content.
*   `integer` / `bigInteger`: Numeric values.
*   `boolean`: Toggle/Switch (True/False).
*   `decimal`: Monetary or precise numeric values (supports `step="0.01"`).
*   `date` / `datetime`: Date and time pickers.
*   `foreignId`: Automated relationship builder (creates constraints & dropdowns).
*   `enum`: Predefined selection list.

---

## 📡 Surgical API Automation

By appending the `--api` flag, the starter kit generates a high-performance RESTful API layer for your module. Unlike generic scaffolders, this system ensures **100% Logic Parity** between your Web UI and API.

### 1. What is Generated?
*   **`app/Http/Controllers/Api/{Model}Controller.php`**: Standardized controller using Domain Actions.
*   **`app/Http/Resources/{Model}Resource.php`**: Transform your models into clean JSON structures.
*   **`app/Http/Requests/Api/{Model}/Store{Model}Request.php`**: Dedicated API validation.
*   **`app/Http/Requests/Api/{Model}/Update{Model}Request.php`**: Partial-update (PATCH) support.

### 2. Standardized Responses
The API layer uses the `ApiResponse` class and the `to_api()` helper to ensure consistent JSON outputs:
```json
{
    "status": "success",
    "message": "Record created.",
    "data": { "id": 1, "name": "..." },
    "meta": null
}
```

### 3. Shared Domain Logic
The API Controller **reuses** the same `DTOs`, `Actions`, and `Queries` as the Livewire components. This means:
- If you change a rule in an Action, both the Website and the Mobile App reflect it instantly.
- Zero code duplication.

---

## 🗑️ How to Remove a Module

If you need to completely delete a module and all its generated files, use the `remove:view` command. It will perform a surgical cleanup of the entire ecosystem.

### 1. Run the Cleanup
```bash
php artisan remove:view {ModelName}
```

### 2. What is Removed?
The command automatically deletes:
1.  **Domain Layer**: The entire `app/Domain/{Model}` folder (Actions, DTOs, Queries).
2.  **Infrastructure**: The Model and the Migration file.
3.  **UI Layer**: Livewire components and all Blade views.
4.  **API Layer**: Controllers, Resources, and FormRequests.
5.  **Ecosystem**: Route files (Web & API), sidebar navigation links, and permissions from the database.

---

## 🏗️ Architectural Output (What is Generated?)

When you run `new:view`, the system generates a structured ecosystem across four layers:

### A. Domain Layer (`app/Domain/{Model}`)
*   **`DTOs/{Model}DTO.php`**: A readonly object that ensures data integrity between the UI and the Database.
*   **`Actions/`**: Pure logic classes for data mutation:
    - `Create{Model}Action.php`
    - `Update{Model}Action.php`
    - `Delete{Model}Action.php`
*   **`Queries/{Model}ListQuery.php`**: Encapsulates all filtering, searching, and eager-loading logic. No more messy queries in your Livewire components!

### B. Infrastructure Layer
*   **Migration**: Production-ready schema with correct types (Enum, ForeignId constraints).
*   **Model (`app/Models/{Model}.php`)**:
    - `rules()`: Centralized validation array.
    - `sortable()`: Defined list of fields that the UI is allowed to sort.
    - `casts()`: Auto-casting for dates, booleans, and enums.

### C. UI Layer (`app/Livewire/Admin` & `resources/views/livewire`)
*   **Index**: Data table with Pagination, Sortable Headers, and the "Smart Filter" panel.
*   **Create/Edit**: Forms with auto-filling relationship logic and "Create-on-the-fly" inputs.
*   **Row**: Modular table rows for easy maintenance.

---

## 🎨 Professional UI Components

| Component | Description |
| :--- | :--- |
| `<x-table.th>` | **Pro Sortable Header**. Changes color to Blue if sortable, includes Apple-style chevron indicators. |
| `<x-form.dropdown-search>` | **Searchable Select**. Handles large datasets with live search, `wire:ignore` for stability, and "+ Add New" integration. |
| `<x-form.input>` | **Smart Input**. Automatically adapts to `type="number"`, `type="date"`, or `type="text"` with high-contrast labels. |
| `<x-form.textarea>` | **Auto-spanning Area**. Occupies full width in grids for shippable notes/descriptions. |

---

## 🚀 Enterprise-grade Examples

You can use PowerShell one-liners to skip the interactive wizard and scaffold complex modules instantly.

### 1. Scaffold Categories (Standard Module)
```powershell
"name`n0`nno`nslug`n0`nno`n`ntag" | php artisan new:view Category
```

### 2. Scaffold BlogPosts (Module with API Support)
```powershell
"title`n0`nno`nslug`n0`nno`ncategory_id`n8`ncategories`nno`ncontent`n1`nyes`npublished`n4`nno`n`nnewspaper" | php artisan new:view BlogPost --api
```

---

## 💡 Quick Demo Data (Tinker)

If you want to quickly populate your database with demo categories and blog posts, you can run the following script inside `php artisan tinker`:

```php
// Run php artisan tinker
use App\Models\Category;
use App\Models\BlogPost;
use App\Models\Tag;

$categories = collect([
    ['name' => 'Laravel', 'slug' => 'laravel'],
    ['name' => 'Livewire', 'slug' => 'livewire'],
    ['name' => 'PHP', 'slug' => 'php'],
    ['name' => 'Architecture', 'slug' => 'architecture'],
])->map(fn($category) => Category::create($category));

foreach(range(1, 50) as $i) {
    BlogPost::create([
        'title'       => "Laravel 12 Starter Kit Article {$i}",
        'slug'        => "laravel-12-starter-kit-article-{$i}",
        'category_id' => $categories->random()->id,
        'content'     => "Demo blog content for article {$i}. Testing E4ProTech Starterkit.",
        'published'   => true,
    ]);
}
```

---

## 🔗 Advanced Relationship Rules (Dot Notation)

When scaffolding a `foreignId` field, you can pull data from **nested tables** (e.g., getting an Employee's name for a Mechanic record) using **Dot Notation**.

### 1. The Format
When the wizard asks for the **"Display field"**, use: `RELATION.FIELD` (e.g., `employee.name`).

### 2. What Happens Under the Hood?
- **Auto Eager Loading**: The generator adds `->with('relation.subRelation')` to your ListQuery class automatically to prevent Lazy Loading errors.
- **Deep Resolution**: The UI renders `$item->relation?->subRelation?->field` safely.
- **Global Dispatch**: When you create a new record in a nested modal, a global JS event is dispatched (`Livewire.dispatch(...)`) ensuring that even the deepest dropdowns refresh instantly.

---

## ⚡ Smart UI Features

### 1. Auto-fill (Cascading Dropdowns)
The UI components are aware of their relationships. If you have two related dropdowns in a form (e.g., **Brand** and **Model**), simply place the **Model** field after the **Brand** field in the wizard. 
- When you select a **Model**, the system will automatically detect the linked `brand_id` and populate the **Brand** dropdown for you.

### 2. Infinite Nested Modals
Thanks to the **Global Dispatch** system and `MutationObserver` integration in our dropdowns, you can open a modal, from a modal, from a modal.
- `JobCard Form` -> Add `Vehicle` (Modal 1) -> Add `Model` (Modal 2) -> Add `Brand` (Modal 3).
- Saving the **Brand** will instantly update the list in **Modal 2**, and so on.

---

## 📦 Deployment
1.  **Clone**: `git clone ...`
2.  **Setup**: `composer install && npm install && npm run build`
3.  **Database**: `php artisan migrate --seed`
4.  **Admin Access**: Default credentials provided in `DatabaseSeeder`.

Developed by **E4ProTech**. This kit is designed to turn hours of manual coding into a single command. 🚀
#   F l e e t M a n a g m e n t D e m o  
 #   B a r b e r A p p  
 