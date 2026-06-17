# 🎶 SoundScape - Concert Management Platform

A comprehensive web application for discovering, managing, and promoting concerts with integrated web scraping capabilities.

> ⚠️ **Note:** This is a student project created for educational and learning purposes only. It is not intended for profit or commercial use.

---

## ✨ Key Features

### 🗺️ Interactive Concert Map
- View concert locations on an interactive map using Leaflet.js
- Search concerts by province and location
- View detailed information directly from map markers

### 🎫 Promoter Management System
- Dedicated Promoter Dashboard for managing concerts
- Create, edit, and delete concert information
- Update concert status and artist details
- Manage concert events independently

### 🤖 Automated Web Scraper
- Python-based scraper automatically collects concert data from multiple ticket vendors:
  - All Ticket (allticket.com)
  - The Concert (theconcert.com)
  - Ticketier (ticketier.com)
- Admin panel to monitor and control scraper jobs
- Real-time progress tracking

### 👥 User Interactions
- User registration and profile management
- Follow concerts and receive updates
- Comment and engage with concert information

### 👑 Admin Panel
- Comprehensive dashboard for system management
- Manage users, promoters, concerts, artists, and highlights
- Monitor scraper jobs and system logs

---

## 🛠️ Tech Stack

**Backend:**
- [Laravel 11](https://laravel.com/) (PHP Framework)
- MySQL / MariaDB (Database)

**Frontend:**
- [Vue.js 3](https://vuejs.org/) (JavaScript Framework)
- [Inertia.js](https://inertiajs.com/) (Server-Driven SPA)
- [Tailwind CSS](https://tailwindcss.com/) (Styling)

**Web Scraping:**
- [Python 3](https://www.python.org/)
- Selenium WebDriver
- BeautifulSoup

---

## 🚀 Installation & Setup

### Prerequisites
- PHP >= 8.2
- Composer
- Node.js 18+ & npm
- MySQL 8.0+
- Python 3.10+
- Git

### Step-by-Step Installation

1. **Clone the repository**
   ```bash
   git clone <repository-url>
   cd SoundScape
   ```

2. **Install PHP dependencies**
   ```bash
   composer install
   ```

3. **Install Node dependencies**
   ```bash
   npm install
   ```

4. **Setup environment file**
   ```bash
   cp .env.example .env
   php artisan key:generate
   ```

5. **Configure database in `.env`**
   ```
   DB_CONNECTION=mysql
   DB_HOST=127.0.0.1
   DB_PORT=3306
   DB_DATABASE=soundscape
   DB_USERNAME=root
   DB_PASSWORD=
   ```

6. **Run migrations**
   ```bash
   php artisan migrate --seed
   ```

7. **Build frontend assets**
   ```bash
   npm run build
   ```

8. **Storage Link**
   ```bash
   php artisan storage:link
   ```

### Running the Application

**Terminal 1 - Backend Server:**
```bash
php artisan serve
```

**Terminal 2 - Frontend Dev Server:**
```bash
npm run dev
```

Access the application at: `http://localhost:8000`

---

## 🐍 Scraper Installation & Usage

### Setup Python Environment

```bash
cd scraper
python -m venv .venv

# On Windows:
.venv\Scripts\activate
# On Linux/Mac:
source .venv/bin/activate

pip install -r requirements.txt
```

### Running the Scraper

**Via Admin Dashboard:**
1. Navigate to Admin > Scraper
2. Select target website (allticket, theconcert, or ticketier)
3. Click "Start Scraping"
4. Monitor progress in real-time

**Via Command Line:**
```bash
cd scraper
python master_runner.py concert ticketier
```

---

## 📁 Project Structure

```
SoundScape/
├── app/
│   ├── Http/Controllers/       # Application controllers
│   ├── Models/                 # Eloquent models
│   └── Services/               # Business logic
├── resources/
│   ├── js/Components/          # Vue components
│   ├── js/Pages/               # Page components
│   ├── css/                    # Tailwind styles
│   └── views/                  # Blade templates
├── scraper/
│   ├── allticket/              # AllTicket scraper
│   ├── theconcert/             # TheConcert scraper
│   ├── ticketier/              # Ticketier scraper
│   ├── utils/                  # Shared utilities
│   ├── .venv/                  # Python virtual environment
│   └── requirements.txt        # Python dependencies
├── storage/
│   └── logs/                   # Application and scraper logs
├── database/
│   └── migrations/             # Database migrations
├── routes/                     # API and web routes
└── README.md                   # This file
```

---

## 📊 Database Schema

### Main Tables
- `concerts` - Concert information
- `artists` - Artist details
- `scraper_jobs` - Scraper job tracking
- `users` - User accounts
- `provinces` - Province/location data

---

## 🔌 API Endpoints

### Concerts
- `GET /api/concerts` - List all concerts
- `GET /api/concerts/{id}` - Get concert details
- `GET /api/concerts/province/{province}` - Filter by province

### Scraper Management
- `POST /api/scraper/run` - Start scraper job
- `GET /api/scraper/status/{job_id}` - Check job status
- `POST /api/scraper/cancel/{job_id}` - Cancel running job
- `POST /api/scraper/update/{job_id}` - Update job status

---

## 📝 Configuration

### Environment Variables

```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=soundscape
DB_USERNAME=root
DB_PASSWORD=
```

### Python Scraper Configuration

The Python scraper requires:
- `.venv` directory with Python dependencies
- `msedgedriver.exe` in scraper root directory
- Proper path configuration in Laravel's `.env`

---

## 📋 Logging

- **Application Logs:** `storage/logs/laravel.log`
- **Scraper Debug Logs:** `storage/logs/scraper_debug.log`
- **Job Control Files:** `storage/logs/cancel_*.txt`

---

## 🐛 Troubleshooting

### Scraper Not Starting
- Verify `.venv` directory exists in scraper folder
- Check Python path in `.env` file
- Review `storage/logs/scraper_debug.log` for detailed errors
- Ensure `msedgedriver.exe` is in the scraper root directory

### Database Connection Failed
- Verify MySQL is running
- Check database credentials in `.env`
- Run `php artisan migrate` to setup tables

### Frontend Not Loading
- Run `npm run dev` for development mode
- Clear browser cache
- Check that Vite server is running

### Path Issues on Different Computers
- Update paths in `.env` file
- Laravel automatically handles path conversion
- Python scraper uses relative paths from project root

---

## 🔒 Security Considerations

- Always validate user input
- Keep dependencies updated: `composer update`, `npm update`, `pip install --upgrade -r requirements.txt`
- Use environment variables for sensitive data
- Configure HTTPS in production
- Implement proper CORS settings
- Use rate limiting for API endpoints

---

## 📚 Additional Resources

- [Laravel Documentation](https://laravel.com/docs)
- [Vue.js Guide](https://vuejs.org/guide/)
- [Inertia.js Documentation](https://inertiajs.com/)
- [Selenium Python Documentation](https://selenium-python.readthedocs.io/)

---

## 📄 License

Proprietary - All rights reserved

---

## 👨‍💼 Support

For issues or questions:
- Check the troubleshooting section above
- Review logs in `storage/logs/`
- Consult project documentation
- Contact the development team

---

**Last Updated:** June 2026