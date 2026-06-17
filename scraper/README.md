# 🕷️ SoundScape Web Scraper

Python-based web scraper for automatically collecting concert data from multiple ticket vendors using Selenium WebDriver.

---

## 📋 Overview

The SoundScape scraper is a Python-based automation tool that:
- Automatically extracts concert information from multiple ticket vendor websites
- Matches artist information using intelligent text processing
- Communicates with the Laravel backend to update concert data
- Provides detailed logging for debugging and monitoring
- Supports job cancellation and progress tracking

### Supported Websites
- **All Ticket** (allticket.com)
- **The Concert** (theconcert.com)
- **Ticketier** (ticketier.com)

---

## 🛠️ Tech Stack

- **Python 3.10+**
- **Selenium WebDriver** - Browser automation
- **BeautifulSoup** - HTML parsing
- **Requests** - HTTP requests
- **Microsoft Edge WebDriver** - For Selenium automation

---

## 📁 Project Structure

```
scraper/
├── allticket/
│   ├── master.py           # AllTicket scraper main script
│   ├── tester.py           # Concert data extraction and parsing
│   └── __init__.py
├── theconcert/
│   ├── master.py           # TheConcert scraper main script
│   ├── tester.py           # Concert data extraction and parsing
│   └── __init__.py
├── ticketier/
│   ├── master.py           # Ticketier scraper main script
│   ├── tester.py           # Concert data extraction and parsing
│   └── __init__.py
├── utils/
│   ├── artist_matcher.py   # Artist extraction and matching
│   ├── geocoder.py         # Location/coordinate utilities
│   ├── constants.py        # Constants and configuration
│   └── __init__.py
├── master_runner.py        # Main entry point for scraper jobs
├── .venv/                  # Python virtual environment
├── requirements.txt        # Python dependencies
├── .env.example            # Example environment file
└── README.md               # This file
```

---

## 🚀 Installation

### Prerequisites
- Python 3.10 or higher
- Git
- Microsoft Edge browser
- Edge WebDriver (included in `msedgedriver.exe`)

### Setup Steps

1. **Navigate to scraper directory**
   ```bash
   cd scraper
   ```

2. **Create virtual environment**
   ```bash
   python -m venv .venv
   ```

3. **Activate virtual environment**
   
   **On Windows:**
   ```bash
   .venv\Scripts\activate
   ```
   
   **On Linux/Mac:**
   ```bash
   source .venv/bin/activate
   ```

4. **Install dependencies**
   ```bash
   pip install -r requirements.txt
   ```

5. **Setup environment file**
   ```bash
   cp .env.example .env
   ```

6. **Verify Edge WebDriver**
   - Ensure `msedgedriver.exe` is in the scraper root directory
   - Download from: https://developer.microsoft.com/microsoft-edge/tools/webdriver/

---

## 📦 Dependencies

Core dependencies installed from `requirements.txt`:

```
selenium>=4.10.0          # Web browser automation
beautifulsoup4>=4.11.0    # HTML parsing
requests>=2.31.0          # HTTP requests
python-dotenv>=1.0.0      # Environment variable management
```

---

## 📖 Usage

### Via Laravel Admin Dashboard

1. Navigate to **Admin > Scraper Management**
2. Select target website (allticket, theconcert, or ticketier)
3. Click **"Start Scraping"**
4. Monitor progress in real-time
5. View logs in **Storage > Logs > scraper_debug.log**

### Via Command Line

```bash
python master_runner.py <type> <website> [job_id] [file_path]
```

**Parameters:**
- `type` - `concert` or `artist`
- `website` - `allticket`, `theconcert`, `ticketier`, or `none`
- `job_id` - (Optional) Job ID for tracking
- `file_path` - (Optional) Path to data file

**Examples:**

```bash
# Scrape all concerts from AllTicket
python master_runner.py concert allticket

# Scrape with job tracking
python master_runner.py concert ticketier 19 "none"

# Scrape artists from API
python master_runner.py artist none 1 "none"
```

---

## 🔍 Key Components

### master_runner.py
- Entry point for scraper jobs
- Handles job arguments and execution
- Routes to appropriate scraper module

### allticket/master.py, theconcert/master.py, ticketier/master.py
- Site-specific scraper implementations
- Extracts concert links from listing pages
- Calls `get_page_destination_data()` for detailed information
- Handles WebDriver setup and browser automation
- Updates Laravel backend with progress

### tester.py (in each site folder)
- Implements `get_page_destination_data()` - Extracts concert details
- Implements `save_concert()` - Saves concert to database
- Handles HTML parsing and data extraction
- Converts raw concert data to standardized format

### utils/artist_matcher.py
- Extracts artist names from concert descriptions
- Matches artists with existing database records
- Fuzzy matching for typos and variations

---

## 📝 Logging

All scraper activities are logged to: `../storage/logs/scraper_debug.log`

**Log Format:**
```
[YYYY-MM-DD HH:MM:SS] [LEVEL] Message
```

**Log Levels:**
- `INFO` - General information
- `WARN` - Warnings and recoverable errors
- `ERROR` - Serious errors

---

## 🐛 Troubleshooting

**Issue: "msedgedriver.exe not found"**
```
Solution: Place Edge WebDriver in scraper root directory
Download: https://developer.microsoft.com/microsoft-edge/tools/webdriver/
```

**Issue: "Cannot find the path specified"**
```
Solution: Check that .venv directory exists and paths are correct
Verify: .venv directory is created with 'python -m venv .venv'
```

**Issue: Timeouts during scraping**
```
Solution: Increase wait times in master.py (WebDriverWait timeout values)
Check: Website might be blocking automation
```

---

## 🔒 Security Considerations

- Validate all input paths and URLs
- Use environment variables for sensitive configuration
- Don't expose sensitive URLs or API keys in logs
- Handle user-agent properly to avoid being blocked

---

## 📚 Additional Resources

- [Selenium Python Documentation](https://selenium-python.readthedocs.io/)
- [BeautifulSoup Documentation](https://www.crummy.com/software/BeautifulSoup/bs4/doc/)
- [Edge WebDriver Documentation](https://learn.microsoft.com/microsoft-edge/webdriver-chromium/)

---

## 📄 License

Proprietary - All rights reserved

---

**Last Updated:** June 2026

