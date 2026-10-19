CREATE DATABASE IF NOT EXISTS kpop_album_store;

USE kpop_album_store;

CREATE TABLE stores (
    store_id INT AUTO_INCREMENT PRIMARY KEY,
    store_name VARCHAR(100) NOT NULL,
    location VARCHAR(150) NOT NULL,
    contact_number VARCHAR(20)
);

CREATE TABLE albums (
    album_id INT AUTO_INCREMENT PRIMARY KEY,
    store_id INT NOT NULL,
    album_title VARCHAR(150) NOT NULL,
    artist VARCHAR(100) NOT NULL,
    album_version VARCHAR(100),
    price DECIMAL(10, 2) NOT NULL DEFAULT 0.00,
    stock INT NOT NULL DEFAULT 0,
    FOREIGN KEY (store_id)
        REFERENCES stores(store_id)
        ON UPDATE CASCADE
        ON DELETE CASCADE
);
