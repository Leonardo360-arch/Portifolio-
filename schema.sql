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

-- Lumen Training Center
CREATE TABLE IF NOT EXISTS lumen_knowledge (
 id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
 category ENUM('personalidade','perfil','tecnico','projeto','metodologia','correcao') NOT NULL,
 project_id INT NULL,
 title VARCHAR(150) NOT NULL,
 content TEXT NOT NULL,
 source VARCHAR(500) NOT NULL DEFAULT '',
 published TINYINT(1) NOT NULL DEFAULT 0,
 updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
 CONSTRAINT fk_lumen_project FOREIGN KEY (project_id) REFERENCES projetos(id) ON DELETE CASCADE,
 INDEX idx_lumen_public (published,category)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS lumen_ai_usage (
 usage_day DATE PRIMARY KEY,
 requests INT UNSIGNED NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
