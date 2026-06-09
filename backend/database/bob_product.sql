DROP TABLE bob_products;

CREATE TABLE bob_products (
  id INT AUTO_INCREMENT PRIMARY KEY,
  libelle VARCHAR(255),
  ean VARCHAR(20),
  fournisseur VARCHAR(100),
  ref_fournisseur VARCHAR(100),
  stock_local INT,
  prix VARCHAR(20),
  code_article VARCHAR(50),
  millesime VARCHAR(50),
  activite VARCHAR(100),
  rayon VARCHAR(100),
  famille VARCHAR(100),
  sous_famille VARCHAR(100),
  code_recreaclub VARCHAR(50),
  code_fournisseur VARCHAR(50),
  imported_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);