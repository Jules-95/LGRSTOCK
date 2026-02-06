-- Supprimer la table si elle existe déjà (pour réexécuter le script)
DROP TABLE IF EXISTS products;

-- Creation de la table products
CREATE TABLE products (
    id INT AUTO_INCREMENT PRIMARY KEY, 
    ean VARCHAR(13) UNIQUE NOT NULL,
    libelle VARCHAR(250) NOT NULL,
    fournisseur VARCHAR(100) NULL,
    quantite INT DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Creation des index pour optimiser les recherches
CREATE INDEX idx_ean ON products(ean);
CREATE INDEX idx_libelle ON products(libelle);
CREATE INDEX idx_fournisseur ON products(fournisseur);