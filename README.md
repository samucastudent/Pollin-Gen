# 🎨 Pollin-Gen (Pollinations Studio)

Uma interface PHP leve e independente para gerar imagens de IA de alta qualidade usando a infraestrutura da **Pollinations.ai**. 

### 🌟 Principais Recursos
- **Armazenamento Zero:** As imagens são processadas em memória (Base64). Nenhum espaço em disco é utilizado no seu servidor.
- **Foco em Privacidade:** Sua Chave de API é armazenada apenas na sua sessão atual do navegador.
- **Fallback Multi-Modelo:** Inicia com o `Flux` e alterna automaticamente para `Zimage`, `Klein` ou `GPTImage` caso o principal esteja ocupado.
- **Resoluções Adaptáveis:** Suporte nativo para 1:1 (Quadrado) e 16:9 (Widescreen).

---

### 🌐 Demonstração Online
<a href="https://image-ai.testes.eu.org/" target="_blank">Acesse o link oficial de teste aqui</a>

### 🖼️ Estilos Disponíveis

<div align="center">
  <table border="0">
    <tr>
      <td align="center">
        <img src="https://cdn.jsdelivr.net/gh/samucastudent/GitGallery@main/imagens/imagem-realista-teacher_1774889035.jpg" width="300px" alt="Imagem Realista"/><br/>
        <b>Foto Realista</b>
      </td>
      <td align="center">
        <img src="https://cdn.jsdelivr.net/gh/samucastudent/GitGallery@main/imagens/ilustracao-digital-teacher_1774889024.jpg" width="300px" alt="Ilustração Digital"/><br/>
        <b>Ilustração Digital</b>
      </td>
      <td align="center">
        <img src="https://cdn.jsdelivr.net/gh/samucastudent/GitGallery@main/imagens/cartoon-teacher_1774889047.jpg" width="300px" alt="Cartoon"/><br/>
        <b>Estilo Cartoon</b>
      </td>
    </tr>
  </table>
</div>

---

### 🚀 Início Rápido (Servidor Web)

1. Clone este repositório:
   ```bash
   git clone [https://github.com/samucastudent/pollin-gen.git](https://github.com/samucastudent/pollin-gen.git)
   ```
2. Envie os arquivos para seu servidor com suporte a PHP (Apache/Nginx).
3. Abra no seu navegador, insira sua **Chave de API da Pollinations** e comece a criar!

---

### 📂 Instalação Manual (Para todos os usuários)

1. **Baixe o Projeto:** [Clique aqui para baixar o ZIP](https://github.com/samucastudent/Pollin-Gen/archive/refs/heads/main.zip).
2. **Descompactar:** Extraia o arquivo no seu computador. Você verá uma pasta chamada `Pollin-Gen-main`.
3. **Subir para o Servidor:**
   * Entre na pasta descompactada.
   * Envie o arquivo `index.php` para o seu servidor (via Gerenciador de Arquivos do cPanel ou FTP).
   * Coloque-o dentro de uma pasta (ex: `/public_html/fotos-ia`).
4. **Acesse:** Abra no seu navegador: `seusite.com.br/fotos-ia`

### 🐳 Instalação via Docker (Local)

Se você deseja rodar o projeto localmente sem instalar PHP ou Apache na sua máquina:

1. **Construir a imagem:**
   ```bash
   docker build -t pollin-gen .
   ```
2. **Rodar o container:**
   ```bash
   docker run -d -p 8080:80 --name pollin-ai pollin-gen
   ```
3. Acesse: `http://localhost:8080`

---

### 🔑 Como obter uma Chave de API?
Visite [Pollinations.ai](https://pollinations.ai) para obter suas credenciais. Este projeto é **Powered by Pollinations**.

---

*Desenvolvido por [Samuel](https://github.com/samucastudent)*
```

---

### 2. Dockerfile (Comentários em Português)

```dockerfile
# Usa a imagem oficial do PHP com Apache
FROM php:8.2-apache

# Instala extensões necessárias para o cURL e SSL funcionarem corretamente
RUN apt-get update && apt-get install -y \
    libcurl4-openssl-dev \
    pkg-config \
    libssl-dev \
    && docker-php-ext-install curl

# Copia os arquivos do projeto para o diretório padrão do Apache
COPY . /var/www/html/

# Garante que as permissões de pasta estejam corretas para o servidor web
RUN chown -R www-data:www-data /var/www/html/

# Expõe a porta 80 para acesso externo
EXPOSE 80
```

---

### Comandos de Automação para o seu Terminal (SSH) (opcional)

Se quiser criar os dois arquivos de uma vez agora via SSH na pasta `/home/usuario/public_html/gerador`, basta rodar este bloco de comandos:

```bash
# Criar README.md
cat << 'EOF' > /home/usuario/public_html/gerador/README.md
# 🎨 Pollin-Gen (Pollinations Studio)
Uma interface PHP leve e independente para gerar imagens de IA de alta qualidade usando a infraestrutura da **Pollinations.ai**. 
... (cole o conteúdo do README acima aqui) ...
EOF

# Criar Dockerfile
cat << 'EOF' > /home/usuario/public_html/gerador/Dockerfile
FROM php:8.2-apache
RUN apt-get update && apt-get install -y libcurl4-openssl-dev && docker-php-ext-install curl
COPY . /var/www/html/
RUN chown -R www-data:www-data /var/www/html/
EXPOSE 80
EOF

# Enviar para o Github
git add README.md Dockerfile
git commit -m "docs: Adicionando README em PT-BR e Dockerfile"
git push origin main
```

