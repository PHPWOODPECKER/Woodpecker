```markdown
# Woodpecker Database System 🗃️

A secure, fluent database abstraction layer for MySQL with table management, CRUD operations, and query building.

![Database Architecture](https://example.com/db-architecture.png) *(optional diagram)*

## Table of Contents
- [Components](#components-)
  - [DataBase](#database-)
  - [Table](#table-)
  - [Tools](#tools-)
- [Installation](#installation-)
- [Basic Usage](#basic-usage-)
- [Advanced Features](#advanced-features-)
- [Security](#security-)
- [Best Practices](#best-practices-)

---

## Components 🧩

### DataBase 🏗️
Core database connection and schema management.

**Features:**
- Secure PDO connection management
- Schema operations (create/alter/drop tables)
- Column management (add/rename/drop)
- Strict input validation

**Key Methods:**
```php
// Initialize connection
DataBase::init();

// Table operations
DataBase::createTable('users', [
    'id' => ['type' => 'INT', 'primary_key' => true, 'auto_increment' => true],
    'name' => ['type' => 'VARCHAR', 'length' => 255]
]);

DataBase::addColumn('users', 'email', ['type' => 'VARCHAR', 'length' => 255]);
```

### Table 🏷️
Active Record-style table operations.

**Features:**
- Fluent CRUD operations
- Advanced query building
- Automatic parameter binding
- Result chaining

**Key Methods:**
```php
// Basic CRUD
Table::insert('users', ['name' => 'John']);
Table::update('users', ['id' => 1, 'name' => 'John Updated']);
Table::delete('users', 1); // by primary key

// Query building
$results = Table::find('users', 'name', 'John')
    ->where('active', 1)
    ->orderBy('created_at', 'DESC')
    ->limit(10);
```

### Tools 🛠️
Result set processor with chainable operations.

**Features:**
- Collection-style result processing
- JSON conversion
- Batch operations
- Method chaining

**Key Methods:**
```php
$users = Table::getAll('users')
    ->filter(fn($user) => $user['age'] > 18)
    ->map(fn($user) => [
        'id' => $user['id'],
        'name' => strtoupper($user['name'])
    ])
    ->getJson();
```

---

2. Configure database in `config.php`:
```php
return [
    'Database' => [
        'driver' => 'mysql',
        'host' => 'localhost',
        'dbname' => 'myapp',
        'username' => 'root',
        'password' => '',
        'charset' => 'utf8mb4',
        'primarykey' => 'id' // Default primary key
    ]
];
```

3. Initialize connection:
```php
use Woodpecker\DataBase\DataBase;
use Woodpecker\DataBase\Table;

DataBase::init(); // Or Table::init() for Table operations
```

---

## Basic Usage 🚀

### Creating Tables
```php
DataBase::createTable('posts', [
    'id' => [
        'type' => 'INT',
        'primary_key' => true,
        'auto_increment' => true
    ],
    'title' => [
        'type' => 'VARCHAR', 
        'length' => 255,
        'notnull' => true
    ],
    'content' => ['type' => 'TEXT']
]);
```

### CRUD Operations
```php
// Create
Table::insert('users', [
    'name' => 'Alice',
    'email' => 'alice@example.com'
]);

// Read
$user = Table::find('users', 'email', 'alice@example.com')->first();

// Update
Table::update('users', [
    'id' => 1,
    'name' => 'Alice Smith'
]);

// Delete
Table::delete('users', 1); // By primary key
```

### Query Building
```php
$activeUsers = Table::select('users', '*')
    ->where('active', 1)
    ->whereBetween('created_at', '2023-01-01', '2023-12-31')
    ->orderBy('name', 'ASC')
    ->limit(10)
    ->getAll();
```

---

## Advanced Features 🔥

### Transactions
```php
Table::transaction(function() {
    Table::insert('orders', [...]);
    Table::update('inventory', [...]);
    // Rolls back if any operation fails
});
```

### Batch Operations
```php
// Insert multiple
Table::insertBatch('users', [
    ['name' => 'Bob', 'email' => 'bob@example.com'],
    ['name' => 'Carol', 'email' => 'carol@example.com']
]);

// Update with conditions
Table::updateWhere('products', 
    ['price' => 19.99],
    ['category' => 'books']
);
```

### Schema Migrations
```php
// Add column
DataBase::addColumn('users', 'last_login', [
    'type' => 'DATETIME',
    'notnull' => false
]);

// Modify column
DataBase::renameColumn('users', 'name', 'full_name', [
    'type' => 'VARCHAR',
    'length' => 255
]);
```

---

## Security 🛡️

**Built-in Protections:**
- Automatic PDO parameter binding
- Strict input sanitization
- Restricted table/column naming (alphanumeric + underscores only)
- HTML special chars encoding for all values

**Validation Example:**
```php
// All inputs are automatically sanitized:
Table::find('users', 'email', 'user@example.com<script>');
// The script tag is neutralized before query execution
```

---

## Best Practices ✅

1. **Always Use Parameterized Queries**
   ```php
   // Good
   Table::find('users', 'email', $input);
   
   // Bad (vulnerable to SQL injection)
   $unsafeQuery = "SELECT * FROM users WHERE email = '$input'";
   ```

2. **Index Frequently Queried Columns**
   ```php
   DataBase::addColumn('logs', 'user_id', [
       'type' => 'INT',
       'index' => true // Add index for performance
   ]);
   ```

3. **Use Transactions for Critical Operations**
   ```php
   Table::transaction(function() use ($orderData, $inventoryUpdate) {
       Table::insert('orders', $orderData);
       Table::update('inventory', $inventoryUpdate);
   });
   ```

4. **Limit Result Sets**
   ```php
   // Always limit large datasets
   Table::getAll('logs')
       ->orderBy('created_at', 'DESC')
       ->limit(100);
   ```

---

## Performance Tips ⚡

**Indexing:**
```php
// Add index when creating table
DataBase::createTable('orders', [
    'user_id' => [
        'type' => 'INT',
        'index' => true // Creates index
    ],
    // ...
]);
```

**Batch Inserting:**
```php
// Much faster than individual inserts
Table::insertBatch('products', $thousandsOfProducts);
```

**Select Only Needed Columns:**
```php
// Better than SELECT *
Table::select('users', ['id', 'name'])
    ->where('active', 1)
    ->getAll();
```

---

## License 📜
MIT License - See [LICENSE](LICENSE) for details.

---
**Part of Woodpecker Framework** 🌳  
**Crafted with care by Woodpecker**
```
