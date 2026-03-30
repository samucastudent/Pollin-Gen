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
