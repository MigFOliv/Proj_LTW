
DROP TABLE IF EXISTS favorites;
DROP TABLE IF EXISTS reviews;
DROP TABLE IF EXISTS messages;
DROP TABLE IF EXISTS transactions;
DROP TABLE IF EXISTS services;
DROP TABLE IF EXISTS categories;
DROP TABLE IF EXISTS profiles;
DROP TABLE IF EXISTS users;

-- Tabela de utilizadores
CREATE TABLE users (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    username TEXT NOT NULL UNIQUE,
    email TEXT NOT NULL UNIQUE,
    password_hash TEXT NOT NULL,
    is_admin INTEGER DEFAULT 0
);

-- Perfil do utilizador
CREATE TABLE profiles (
    user_id INTEGER PRIMARY KEY,
    name TEXT,
    bio TEXT,
    profile_image TEXT,
    FOREIGN KEY(user_id) REFERENCES users(id)
);

-- Categorias de serviços
CREATE TABLE categories (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    name TEXT NOT NULL UNIQUE,
    approved INTEGER DEFAULT 0
);

-- Serviços
CREATE TABLE services (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    freelancer_id INTEGER NOT NULL,
    title TEXT NOT NULL,
    description TEXT,
    price REAL NOT NULL,
    currency TEXT DEFAULT 'USD',
    delivery_time TEXT NOT NULL,
    category TEXT,
    media_path TEXT,
    is_promoted INTEGER DEFAULT 0,
    status TEXT DEFAULT 'pendente',
    FOREIGN KEY(freelancer_id) REFERENCES users(id)
);

-- Transações (pedidos de serviços)
CREATE TABLE transactions (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    client_id INTEGER NOT NULL,
    service_id INTEGER NOT NULL,
    amount REAL,
    currency TEXT DEFAULT 'USD',
    status TEXT DEFAULT 'pending',
    created_at TEXT DEFAULT CURRENT_TIMESTAMP,
    completed_at TEXT,
    FOREIGN KEY(client_id) REFERENCES users(id),
    FOREIGN KEY(service_id) REFERENCES services(id)
);

-- Mensagens entre utilizadores
CREATE TABLE messages (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    sender_id INTEGER NOT NULL,
    receiver_id INTEGER NOT NULL,
    content TEXT,
    timestamp TEXT DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY(sender_id) REFERENCES users(id),
    FOREIGN KEY(receiver_id) REFERENCES users(id)
);

-- Avaliações de serviços
CREATE TABLE reviews (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    transaction_id INTEGER,
    rating INTEGER CHECK(rating BETWEEN 1 AND 5),
    comment TEXT,
    FOREIGN KEY(transaction_id) REFERENCES transactions(id)
);

-- Serviços favoritos
CREATE TABLE favorites (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    user_id INTEGER NOT NULL,
    service_id INTEGER NOT NULL,
    created_at TEXT DEFAULT CURRENT_TIMESTAMP,
    UNIQUE(user_id, service_id),
    FOREIGN KEY(user_id) REFERENCES users(id),
    FOREIGN KEY(service_id) REFERENCES services(id)
);
