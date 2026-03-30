
### 1. README.md (O Manual de Instruções)

Este arquivo será a vitrine do seu GitHub. Ele explica a filosofia "zero-disk" e como o usuário pode rodar isso em 10 segundos.

```markdown
# 🎨 Pollin-Gen (Pollinations Studio)

A lightweight, standalone PHP interface for generating high-quality AI images using the **Pollinations.ai** infrastructure. 

### 🌟 Key Features
- **Zero Storage:** Images are handled in memory (Base64). No disk space is used on your server.
- **Privacy Focused:** Your API Key is stored only in your current session.
- **Multi-Model Fallback:** Starts with `Flux` and automatically falls back to `Zimage`, `Klein`, or `GPTImage` if the primary is busy.
- **Responsive Resolutions:** Supports 1:1 (Square) and 16:9 (Widescreen) out of the box.

---

### 🚀 Quick Start (Web Server)

1. Clone this repository:
   ```bash
   git clone [https://github.com/samucastudent/pollin-gen.git](https://github.com/samucastudent/pollin-gen.git)
   ```
2. Upload to your PHP-enabled server (Apache/Nginx).
3. Open in your browser, enter your **Pollinations API Key**, and start creating!

---

### 🐳 Docker Installation (Local)

If you want to run this locally without installing PHP/Apache on your machine:

1. **Build the image:**
   ```bash
   docker build -t pollin-gen .
   ```
2. **Run the container:**
   ```bash
   docker run -d -p 8080:80 --name pollin-ai pollin-gen
   ```
3. Access: `http://localhost:8080`

---

### 🔑 How to get an API Key?
Visit [Pollinations.ai](https://pollinations.ai) to get your credentials. This project is **Powered by Pollinations**.

---

*Developed by [Samuel](https://github.com/samucastudent)*
```

---

### 2. Dockerfile (Versão Local)

Crie um arquivo chamado `Dockerfile` na mesma pasta do `index.php`. Ele usa uma imagem oficial do PHP com Apache, extremamente leve.

```dockerfile
# Usa a imagem oficial do PHP com Apache
FROM php:8.2-apache

# Instala extensões necessárias para o cURL funcionar corretamente
RUN apt-get update && apt-get install -y \
    libcurl4-openssl-dev \
    && docker-php-ext-install curl

# Copia os arquivos do projeto para o diretório do servidor
COPY . /var/www/html/

# Ajusta as permissões
RUN chown -R www-data:www-data /var/www/html/

# Expõe a porta 80
EXPOSE 80
```

---

   
