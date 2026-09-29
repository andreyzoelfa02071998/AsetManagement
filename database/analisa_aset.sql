CREATE DATABASE IF NOT EXISTS analisa_aset CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE analisa_aset;

DROP TABLE IF EXISTS transactions;
DROP TABLE IF EXISTS gold_prices;
DROP TABLE IF EXISTS portfolio_import_items;
DROP TABLE IF EXISTS portfolio_imports;
DROP TABLE IF EXISTS ai_settings;
DROP TABLE IF EXISTS assets;

CREATE TABLE assets (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(120) NOT NULL,
    type ENUM('emas','saham','reksa_dana','crypto','properti','kas','rdn','lainnya') NOT NULL DEFAULT 'lainnya',
    symbol VARCHAR(30) NULL,
    platform VARCHAR(80) NULL,
    portfolio_name VARCHAR(120) NULL,
    quantity_current DECIMAL(18,4) NOT NULL DEFAULT 0,
    unit VARCHAR(20) NOT NULL DEFAULT 'unit',
    avg_price DECIMAL(18,4) NOT NULL DEFAULT 0,
    market_price DECIMAL(18,4) NOT NULL DEFAULT 0,
    market_value DECIMAL(18,2) NOT NULL DEFAULT 0,
    invested_amount DECIMAL(18,2) NOT NULL DEFAULT 0,
    target_buy_price DECIMAL(18,4) NULL,
    lot_size INT UNSIGNED NOT NULL DEFAULT 1,
    min_purchase_amount DECIMAL(18,2) NOT NULL DEFAULT 0,
    target_allocation DECIMAL(6,2) NOT NULL DEFAULT 0,
    is_planned TINYINT(1) NOT NULL DEFAULT 1,
    price_alert_enabled TINYINT(1) NOT NULL DEFAULT 0,
    price_alert_target DECIMAL(18,4) NULL,
    price_alert_direction ENUM('below','above') NOT NULL DEFAULT 'below',
    price_alert_triggered_at DATETIME NULL,
    price_alert_last_price DECIMAL(18,4) NULL,
    notes TEXT NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE transactions (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    asset_id INT UNSIGNED NOT NULL,
    transaction_date DATE NOT NULL,
    transaction_type ENUM('buy','sell') NOT NULL,
    quantity DECIMAL(18,4) NOT NULL DEFAULT 0,
    price DECIMAL(18,4) NOT NULL DEFAULT 0,
    fee DECIMAL(18,2) NOT NULL DEFAULT 0,
    notes TEXT NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_transactions_asset FOREIGN KEY (asset_id) REFERENCES assets(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE gold_prices (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    price_date DATE NOT NULL,
    price DECIMAL(18,2) NOT NULL,
    buy_price DECIMAL(18,2) NULL,
    unit VARCHAR(20) NOT NULL DEFAULT '0.01',
    source VARCHAR(120) NULL,
    notes TEXT NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY uq_gold_price_date (price_date)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE ai_settings (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id INT UNSIGNED NOT NULL,
    enabled TINYINT(1) NOT NULL DEFAULT 0,
    provider ENUM('chatgpt','gemini','claude','qwen','llama','custom') NOT NULL DEFAULT 'chatgpt',
    api_key VARCHAR(255) NULL,
    base_url VARCHAR(255) NULL,
    model VARCHAR(120) NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY uq_ai_settings_user (user_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE portfolio_imports (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    source_type ENUM('screenshot','manual') NOT NULL DEFAULT 'manual',
    platform VARCHAR(80) NULL,
    file_path VARCHAR(255) NULL,
    processor_type ENUM('ai','ocr') NOT NULL DEFAULT 'ocr',
    processor_name VARCHAR(120) NULL,
    processor_model VARCHAR(120) NULL,
    processor_notes VARCHAR(255) NULL,
    status ENUM('review','saved') NOT NULL DEFAULT 'review',
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE portfolio_import_items (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    import_id INT UNSIGNED NOT NULL,
    asset_type ENUM('emas','saham','reksa_dana','crypto','properti','kas','rdn','lainnya') NOT NULL DEFAULT 'lainnya',
    platform VARCHAR(80) NULL,
    portfolio_name VARCHAR(120) NULL,
    name VARCHAR(120) NOT NULL,
    symbol VARCHAR(30) NULL,
    quantity DECIMAL(18,4) NOT NULL DEFAULT 0,
    unit VARCHAR(20) NOT NULL DEFAULT 'unit',
    avg_price DECIMAL(18,4) NOT NULL DEFAULT 0,
    market_price DECIMAL(18,4) NOT NULL DEFAULT 0,
    invested_amount DECIMAL(18,2) NOT NULL DEFAULT 0,
    confidence TINYINT UNSIGNED NOT NULL DEFAULT 0,
    notes TEXT NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_import_items_batch FOREIGN KEY (import_id) REFERENCES portfolio_imports(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT INTO assets (name, type, symbol, platform, quantity_current, unit, avg_price, market_price, invested_amount, target_buy_price, lot_size, min_purchase_amount, target_allocation, notes) VALUES
('Emas Tring', 'emas', 'XAU', 'Tring', 1.6468, 'gram', 29147, 24570, 4800000, 24000, 1, 24570, 20, 'Sinyal beli aktif saat hargaJual Tring <= 24000 per 0,01g'),
('Kas Rupiah', 'kas', 'IDR', 'Bank', 0, 'idr', 1, 1, 0, NULL, 1, 10000, 10, 'Saldo kas untuk peluang beli aset');

INSERT INTO gold_prices (price_date, price, buy_price, unit, source, notes) VALUES
(CURDATE(), 24570, 23580, '0.01', 'Pegadaian/Tring manual', 'hargaJual = harga user beli; target <= 24000 per 0,01g');
