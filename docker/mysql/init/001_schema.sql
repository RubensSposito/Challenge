CREATE TABLE IF NOT EXISTS users (
  id INT UNSIGNED PRIMARY KEY,
  full_name VARCHAR(150) NOT NULL,
  cpf_cnpj VARCHAR(20) NOT NULL,
  email VARCHAR(180) NOT NULL,
  password_hash VARCHAR(255) NOT NULL,
  is_merchant TINYINT(1) NOT NULL DEFAULT 0,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  UNIQUE KEY uq_users_cpf (cpf_cnpj),
  UNIQUE KEY uq_users_email (email)
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS wallets (
  user_id INT UNSIGNED PRIMARY KEY,
  balance_cents BIGINT NOT NULL DEFAULT 0,
  updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  CONSTRAINT fk_wallet_user
    FOREIGN KEY (user_id) REFERENCES users(id)
    ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS transfers (
  id INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
  payer_id INT UNSIGNED NOT NULL,
  payee_id INT UNSIGNED NOT NULL,
  amount_cents BIGINT NOT NULL,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT fk_transfers_payer FOREIGN KEY (payer_id) REFERENCES users(id),
  CONSTRAINT fk_transfers_payee FOREIGN KEY (payee_id) REFERENCES users(id),
  INDEX idx_transfers_payer (payer_id),
  INDEX idx_transfers_payee (payee_id)
) ENGINE=InnoDB;