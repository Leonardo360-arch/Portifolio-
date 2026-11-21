-- Banco: portfolio_lmc (MySQL, utf8mb4)
CREATE TABLE projetos (
  id INT AUTO_INCREMENT PRIMARY KEY,
  titulo VARCHAR(120) NOT NULL,
  descricao TEXT NOT NULL,
  imagem_print VARCHAR(255) NOT NULL,
  link_testar VARCHAR(255) NOT NULL,
  link_github VARCHAR(255) NULL,
  tecnologias VARCHAR(255) NOT NULL,
  ordem INT DEFAULT 0,
  ativo TINYINT(1) DEFAULT 1,
  criado_em TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  INDEX idx_ordem (ativo, ordem)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE mensagens (
  id INT AUTO_INCREMENT PRIMARY KEY,
  nome VARCHAR(120) NOT NULL,
  email VARCHAR(150) NOT NULL,
  assunto VARCHAR(150) NULL,
  mensagem TEXT NOT NULL,
  lida TINYINT(1) DEFAULT 0,
  criado_em TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Adições do Arquiteto/DBA (a spec cita cursos, login e PDF do currículo, mas não os modela)
CREATE TABLE admin_users (
  id INT AUTO_INCREMENT PRIMARY KEY,
  email VARCHAR(150) NOT NULL UNIQUE,
  senha_hash VARCHAR(255) NOT NULL,
  criado_em TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE cursos (
  id INT AUTO_INCREMENT PRIMARY KEY,
  titulo VARCHAR(150) NOT NULL,
  instituicao VARCHAR(150) NULL,
  carga_horaria INT NULL,
  ordem INT DEFAULT 0,
  ativo TINYINT(1) DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE configuracoes (
  chave VARCHAR(60) PRIMARY KEY,
  valor VARCHAR(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
-- Ex.: INSERT INTO configuracoes VALUES ('curriculo_pdf','uploads/curriculo.pdf');

-- Criar o admin (gere o hash com: php -r "echo password_hash('SUA_SENHA', PASSWORD_BCRYPT);")
-- INSERT INTO admin_users (email, senha_hash) VALUES ('seu@email.com', '<hash>');
