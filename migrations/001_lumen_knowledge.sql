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
