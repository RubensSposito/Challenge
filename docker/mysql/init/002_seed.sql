INSERT INTO users (id, full_name, cpf_cnpj, email, password_hash, is_merchant) VALUES
  (4,  'Cliente A', '11111111111', 'clienteA@email.com', 'hash', 0),
  (15, 'Cliente B', '22222222222', 'clienteB@email.com', 'hash', 0),
  (20, 'Lojista X', '33333333333333', 'lojista@email.com', 'hash', 1)
ON DUPLICATE KEY UPDATE
  full_name = VALUES(full_name),
  cpf_cnpj = VALUES(cpf_cnpj),
  email = VALUES(email),
  password_hash = VALUES(password_hash),
  is_merchant = VALUES(is_merchant);

INSERT INTO wallets (user_id, balance_cents) VALUES
  (4,  100000), -- R$ 1000,00
  (15,  5000),  -- R$ 50,00
  (20,  0)
ON DUPLICATE KEY UPDATE
  balance_cents = VALUES(balance_cents);